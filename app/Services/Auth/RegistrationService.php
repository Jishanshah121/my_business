<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\BusinessProfileRepository;
use App\Repositories\UserRepository;
use App\Services\Notification\NotificationDispatcher;
use App\Support\Config;
use App\Support\Logger;
use App\Validators\Validator;
use RuntimeException;

/**
 * Account creation for both B2C and B2B.
 *
 * The B2B path deliberately creates a working B2C account immediately and
 * leaves the business profile pending: making someone wait for approval before
 * they can buy anything loses the sale. Approval unlocks B2B pricing, quotes
 * and credit — it is not a gate on the front door.
 */
final class RegistrationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly BusinessProfileRepository $businesses,
        private readonly AuthService $auth,
        private readonly EmailVerificationService $verification,
        private readonly NotificationDispatcher $notifications,
        private readonly Logger $logger,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public function registerCustomer(array $data): User
    {
        $userId = $this->createUser($data, 'retail');

        $user = $this->users->find($userId);
        if ($user === null) {
            throw new RuntimeException('User creation succeeded but the record could not be read back.');
        }

        $this->verification->send($user);
        $this->sendWelcome($user);

        $this->logger->info('Customer registered', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function registerBusiness(array $data): User
    {
        // Retail group until an admin approves — see the class comment.
        $userId = $this->createUser($data, 'retail');

        $gstin = isset($data['gstin']) && $data['gstin'] !== ''
            ? strtoupper(trim((string) $data['gstin']))
            : null;

        $this->businesses->create([
            'user_id'                => $userId,
            'business_type_id'       => isset($data['business_type_id']) && $data['business_type_id'] !== ''
                                        ? (int) $data['business_type_id'] : null,
            'company_name'           => trim((string) $data['company_name']),
            'contact_person'         => trim((string) $data['contact_person']),
            'contact_phone'          => Validator::normaliseIndianMobile((string) $data['contact_phone']),
            'contact_email'          => strtolower(trim((string) $data['email'])),
            'gstin'                  => $gstin,
            'pan'                    => isset($data['pan']) && $data['pan'] !== ''
                                        ? strtoupper(trim((string) $data['pan'])) : null,
            'fssai_licence'          => isset($data['fssai_licence']) && $data['fssai_licence'] !== ''
                                        ? trim((string) $data['fssai_licence']) : null,
            'expected_monthly_spend' => isset($data['expected_monthly_spend']) && $data['expected_monthly_spend'] !== ''
                                        ? (string) $data['expected_monthly_spend'] : null,
            'status'                 => 'pending',
        ]);

        $user = $this->users->find($userId);
        if ($user === null) {
            throw new RuntimeException('Business registration succeeded but the record could not be read back.');
        }

        $this->verification->send($user);
        $this->notifyStaffOfPendingBusiness($user);

        $this->logger->info('Business registered', [
            'user_id'      => $user->id,
            'company_name' => $data['company_name'],
            'has_gstin'    => $gstin !== null,
        ]);

        return $user;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function createUser(array $data, string $groupCode): int
    {
        $groupId = $this->resolveGroupId($groupCode);

        $phone = isset($data['phone']) && $data['phone'] !== ''
            ? Validator::normaliseIndianMobile((string) $data['phone'])
            : null;

        return $this->users->transaction(function () use ($data, $groupId, $phone): int {
            $userId = $this->users->create([
                'uuid'              => $this->uuid4(),
                'customer_group_id' => $groupId,
                'first_name'        => trim((string) $data['first_name']),
                'last_name'         => isset($data['last_name']) && $data['last_name'] !== ''
                                       ? trim((string) $data['last_name']) : null,
                'email'             => strtolower(trim((string) $data['email'])),
                'phone'             => $phone,
                'password_hash'     => $this->auth->hashPassword((string) $data['password']),
                // 'pending' until the email is verified; the account still works.
                'status'            => 'pending',
                'marketing_opt_in'  => !empty($data['marketing_opt_in']) ? 1 : 0,
            ]);

            $this->users->assignRole($userId, 'customer');

            return $userId;
        });
    }

    private function resolveGroupId(string $code): int
    {
        $id = $this->users->connection()
            ->query('SELECT `id` FROM `customer_groups` WHERE `code` = ' . $this->users->connection()->quote($code))
            ->fetchColumn();

        if ($id === false) {
            throw new RuntimeException("Customer group [{$code}] is missing. Run the seeders.");
        }

        return (int) $id;
    }

    private function sendWelcome(User $user): void
    {
        $this->notifications->email(
            event: 'user.registered',
            recipient: $user->email,
            subject: 'Welcome to SupplyKaro',
            template: 'welcome',
            data: ['user' => $user],
            userId: $user->id,
            referenceType: 'user',
            referenceId: $user->id,
        );
    }

    private function notifyStaffOfPendingBusiness(User $user): void
    {
        $profile = $user->businessProfile();
        if ($profile === null) {
            return;
        }

        $recipient = (string) Config::get('mail.from.address', 'no-reply@supplykaro.test');

        $this->notifications->email(
            event: 'business.pending_approval',
            recipient: $recipient,
            subject: 'New business account awaiting approval: ' . $profile->companyName,
            template: 'business-pending',
            data: ['user' => $user, 'profile' => $profile],
            userId: null,
            referenceType: 'business_profile',
            referenceId: $profile->id,
        );
    }

    private function uuid4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return implode('-', [
            bin2hex(substr($bytes, 0, 4)), bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)), bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6)),
        ]);
    }
}
