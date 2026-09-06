<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

final class AuthorizationTest extends TestCase
{
    private function makeUser(array $roles, array $permissions, string $status = 'active'): User
    {
        $user = User::fromRow([
            'id' => 1, 'uuid' => 'u', 'customer_group_id' => 1,
            'first_name' => 'Test', 'last_name' => null, 'email' => 't@example.test',
            'phone' => null, 'password_hash' => 'x', 'status' => $status,
            'email_verified_at' => null, 'phone_verified_at' => null, 'last_login_at' => null,
            'marketing_opt_in' => 0,
        ]);
        $user->setRoles($roles);
        $user->setPermissions($permissions);

        return $user;
    }

    public function testPermissionCheckIsExactAndNotPrefixBased(): void
    {
        $user = $this->makeUser(['order_manager'], ['orders.view', 'orders.edit']);

        self::assertTrue($user->can('orders.view'));
        self::assertTrue($user->can('orders.edit'));
        self::assertFalse($user->can('orders.refund'), 'holding orders.* siblings must not grant refund');
        self::assertFalse($user->can('orders'), 'a permission prefix is not a permission');
        self::assertFalse($user->can('products.edit'));
    }

    /** Super Admin is the break-glass role and is deliberately not enumerable. */
    public function testSuperAdminPassesEveryPermission(): void
    {
        $user = $this->makeUser(['super_admin'], []);

        self::assertTrue($user->can('orders.refund'));
        self::assertTrue($user->can('anything.at.all'));
    }

    public function testCustomerIsNotStaff(): void
    {
        self::assertFalse($this->makeUser(['customer'], [])->isStaff());
        self::assertTrue($this->makeUser(['customer', 'sales_manager'], [])->isStaff());
        self::assertTrue($this->makeUser(['inventory_manager'], [])->isStaff());
    }

    public function testUserWithNoRolesCanDoNothing(): void
    {
        $user = $this->makeUser([], []);

        self::assertFalse($user->can('orders.view'));
        self::assertFalse($user->isStaff());
    }

    /**
     * A registered-but-unverified account must stay usable — verification
     * gates specific actions, not the front door. A suspended one must not.
     */
    public function testOnlyActiveAndPendingAccountsMayHoldASession(): void
    {
        self::assertTrue($this->makeUser([], [], 'active')->canAuthenticate());
        self::assertTrue($this->makeUser([], [], 'pending')->canAuthenticate());
        self::assertFalse($this->makeUser([], [], 'suspended')->canAuthenticate());
        self::assertFalse($this->makeUser([], [], 'closed')->canAuthenticate());
    }

    public function testB2bPricingRequiresAnApprovedProfile(): void
    {
        $user = $this->makeUser(['customer'], []);
        self::assertFalse($user->hasApprovedBusiness(), 'no profile means no trade pricing');

        $user->setBusinessProfile($this->profile('pending'));
        self::assertFalse($user->hasApprovedBusiness(), 'pending must not unlock trade pricing');

        $user->setBusinessProfile($this->profile('rejected'));
        self::assertFalse($user->hasApprovedBusiness());

        $user->setBusinessProfile($this->profile('approved'));
        self::assertTrue($user->hasApprovedBusiness());
    }

    private function profile(string $status): \App\Models\BusinessProfile
    {
        return \App\Models\BusinessProfile::fromRow([
            'id' => 1, 'user_id' => 1, 'business_type_id' => null, 'company_name' => 'Test Ltd',
            'contact_person' => 'A', 'contact_phone' => '919999999999', 'contact_email' => null,
            'gstin' => null, 'pan' => null, 'fssai_licence' => null, 'expected_monthly_spend' => null,
            'status' => $status, 'rejection_reason' => null, 'approved_at' => null,
            'credit_enabled' => 0, 'credit_limit' => '0.00', 'credit_days' => 0, 'credit_used' => '0.00',
        ]);
    }

    public function testCreditAvailableNeverGoesNegative(): void
    {
        $profile = \App\Models\BusinessProfile::fromRow([
            'id' => 1, 'user_id' => 1, 'business_type_id' => null, 'company_name' => 'Test Ltd',
            'contact_person' => 'A', 'contact_phone' => '919999999999', 'contact_email' => null,
            'gstin' => null, 'pan' => null, 'fssai_licence' => null, 'expected_monthly_spend' => null,
            'status' => 'approved', 'rejection_reason' => null, 'approved_at' => null,
            'credit_enabled' => 1, 'credit_limit' => '10000.00', 'credit_days' => 30, 'credit_used' => '12000.00',
        ]);

        self::assertSame('0.00', $profile->creditAvailable(), 'over-limit must clamp to zero, not report negative credit');
    }
}
