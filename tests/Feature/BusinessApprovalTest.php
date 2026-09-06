<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Repositories\BusinessProfileRepository;
use App\Repositories\UserRepository;

/**
 * The B2B approval workflow decides who gets trade pricing, so these are the
 * tests that matter most in Phase 2: an unapproved account must never be
 * priced as a business, and approval must move both records together.
 */
final class BusinessApprovalTest extends DatabaseTestCase
{
    private BusinessProfileRepository $businesses;
    private UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->businesses = new BusinessProfileRepository($this->pdo);
        $this->users = new UserRepository($this->pdo);
    }

    /**
     * A distinct, checksum-valid GSTIN per call — the column is unique, so
     * fixtures must not share one.
     */
    private function uniqueGstin(): string
    {
        static $sequence = 0;
        $sequence++;

        $base = sprintf('27AAPFU%04dF1Z', 1000 + $sequence);
        $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $factor = 2;
        $sum = 0;

        for ($i = 13; $i >= 0; $i--) {
            $digit = $factor * strpos($charset, $base[$i]);
            $factor = $factor === 2 ? 1 : 2;
            $sum += intdiv($digit, 36) + $digit % 36;
        }

        return $base . $charset[(36 - $sum % 36) % 36];
    }

    private function createBusiness(string $status = 'pending', ?string $gstin = null): array
    {
        $gstin ??= $this->uniqueGstin();

        $userId = $this->createUser(['group' => 'retail']);
        $profileId = $this->businesses->create([
            'user_id'        => $userId,
            'company_name'   => 'Spice Route Restaurant',
            'contact_person' => 'Rohan Mehta',
            'contact_phone'  => '919820011223',
            'gstin'          => $gstin,
            'status'         => $status,
        ]);

        return [$userId, $profileId];
    }

    public function testNewBusinessStartsPendingAndOnRetailPricing(): void
    {
        [$userId, $profileId] = $this->createBusiness();

        $profile = $this->businesses->find($profileId);
        self::assertNotNull($profile);
        self::assertTrue($profile->isPending());
        self::assertSame(
            'retail',
            $this->customerGroupOf($userId),
            'a pending business must not receive trade pricing'
        );

        $user = $this->users->find($userId);
        self::assertNotNull($user);
        self::assertFalse($user->hasApprovedBusiness());
        self::assertFalse($user->isB2b);
    }

    public function testApprovalMovesTheUserOntoTheChosenB2bGroup(): void
    {
        [$userId, $profileId] = $this->createBusiness();
        $adminId = $this->createUser();

        $this->businesses->approve($profileId, $adminId, 'b2b_gold');

        $profile = $this->businesses->find($profileId);
        self::assertNotNull($profile);
        self::assertTrue($profile->isApproved());
        self::assertNotNull($profile->approvedAt);
        self::assertSame('b2b_gold', $this->customerGroupOf($userId));

        $user = $this->users->find($userId);
        self::assertNotNull($user);
        self::assertTrue($user->hasApprovedBusiness());
        self::assertTrue($user->isB2b, 'the group must carry the B2B flag');
    }

    public function testRejectionReturnsTheCustomerToRetailPricing(): void
    {
        [$userId, $profileId] = $this->createBusiness();
        $adminId = $this->createUser();

        $this->businesses->approve($profileId, $adminId, 'b2b_standard');
        self::assertSame('b2b_standard', $this->customerGroupOf($userId));

        $this->businesses->reject($profileId, $adminId, 'GSTIN does not match the company name on record.');

        $profile = $this->businesses->find($profileId);
        self::assertNotNull($profile);
        self::assertTrue($profile->isRejected());
        self::assertSame('GSTIN does not match the company name on record.', $profile->rejectionReason);
        self::assertSame(
            'retail',
            $this->customerGroupOf($userId),
            'a rejected business is still a customer, on retail pricing'
        );
    }

    public function testApprovingAfterRejectionClearsTheReason(): void
    {
        [$userId, $profileId] = $this->createBusiness();
        $adminId = $this->createUser();

        $this->businesses->reject($profileId, $adminId, 'Awaiting a readable copy of the GST certificate.');
        $this->businesses->approve($profileId, $adminId, 'b2b_standard');

        $profile = $this->businesses->find($profileId);
        self::assertNotNull($profile);
        self::assertTrue($profile->isApproved());
        self::assertNull($profile->rejectionReason, 'a stale rejection reason must not survive approval');
    }

    public function testGstinCannotBeRegisteredTwice(): void
    {
        $this->createBusiness('approved', '27AAPFU0939F1ZV');

        self::assertTrue($this->businesses->gstinExists('27AAPFU0939F1ZV'));
        self::assertTrue($this->businesses->gstinExists('27aapfu0939f1zv'), 'the check is case-insensitive');
        self::assertFalse($this->businesses->gstinExists('29AAGCB7383J1Z4'));

        // The database is the last line of defence: even if the controller's
        // duplicate check were bypassed, the unique index refuses the row.
        $this->expectException(\PDOException::class);
        $this->createBusiness('pending', '27AAPFU0939F1ZV');
    }

    public function testPendingQueueIsOldestFirst(): void
    {
        $this->createBusiness('pending');
        $this->createBusiness('pending');
        $this->createBusiness('approved');

        $pending = $this->businesses->paginateByStatus('pending');

        self::assertCount(2, $pending);
        self::assertSame(2, $this->businesses->countByStatus('pending'));
        self::assertSame(1, $this->businesses->countByStatus('approved'));
        self::assertLessThanOrEqual($pending[1]['created_at'], $pending[0]['created_at']);
    }
}
