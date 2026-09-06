<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BusinessProfile;
use App\Models\User;

final class UserRepository extends Repository
{
    private const SELECT = 'SELECT u.*, cg.`code` AS customer_group_code, cg.`is_b2b`, cg.`prices_include_tax`
                            FROM `users` u
                            JOIN `customer_groups` cg ON cg.`id` = u.`customer_group_id`';

    public function find(int $id): ?User
    {
        $row = $this->fetchOne(self::SELECT . ' WHERE u.`id` = :id AND u.`deleted_at` IS NULL', ['id' => $id]);

        return $row === null ? null : $this->hydrate($row);
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            self::SELECT . ' WHERE u.`email` = :email AND u.`deleted_at` IS NULL',
            ['email' => strtolower(trim($email))]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    public function findByPhone(string $phone): ?User
    {
        $row = $this->fetchOne(
            self::SELECT . ' WHERE u.`phone` = :phone AND u.`deleted_at` IS NULL',
            ['phone' => $phone]
        );

        return $row === null ? null : $this->hydrate($row);
    }

    /**
     * Email or phone — the login form accepts either.
     *
     * Phone numbers are normalised the same way registration normalises them
     * (91XXXXXXXXXX). Without this, a customer who registered with
     * "98765 43210" could never sign in by typing the same thing back.
     */
    public function findByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);

        if (str_contains($identifier, '@')) {
            return $this->findByEmail($identifier);
        }

        if (!\App\Validators\Validator::isValidIndianMobile($identifier)) {
            return null;
        }

        return $this->findByPhone(\App\Validators\Validator::normaliseIndianMobile($identifier));
    }

    public function emailExists(string $email): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM `users` WHERE `email` = :email',
            ['email' => strtolower(trim($email))]
        ) > 0;
    }

    public function phoneExists(string $phone): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM `users` WHERE `phone` = :phone',
            ['phone' => $phone]
        ) > 0;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        return $this->insertRow('users', $data);
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): int
    {
        return $this->updateRow('users', $id, $data);
    }

    public function markEmailVerified(int $id): void
    {
        $this->run(
            "UPDATE `users` SET `email_verified_at` = NOW(), `status` = IF(`status` = 'pending', 'active', `status`)
             WHERE `id` = :id",
            ['id' => $id]
        );
    }

    public function recordLogin(int $id, string $ipBinary): void
    {
        $this->run(
            'UPDATE `users` SET `last_login_at` = NOW(), `last_login_ip` = :ip WHERE `id` = :id',
            ['id' => $id, 'ip' => $ipBinary]
        );
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->run('UPDATE `users` SET `password_hash` = :h WHERE `id` = :id', ['h' => $hash, 'id' => $id]);
    }

    public function assignRole(int $userId, string $roleCode, ?int $assignedBy = null): void
    {
        $roleId = $this->fetchColumn('SELECT `id` FROM `roles` WHERE `code` = :c', ['c' => $roleCode]);
        if ($roleId === false) {
            return;
        }

        $this->run(
            'INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`, `assigned_by`) VALUES (:u, :r, :a)',
            ['u' => $userId, 'r' => (int) $roleId, 'a' => $assignedBy]
        );
    }

    public function setCustomerGroup(int $userId, string $groupCode): void
    {
        $groupId = $this->fetchColumn('SELECT `id` FROM `customer_groups` WHERE `code` = :c', ['c' => $groupCode]);
        if ($groupId === false) {
            return;
        }

        $this->run(
            'UPDATE `users` SET `customer_group_id` = :g WHERE `id` = :id',
            ['g' => (int) $groupId, 'id' => $userId]
        );
    }

    /**
     * Load roles, permissions and the business profile onto a user.
     *
     * Three small queries rather than one join with a cartesian product — the
     * result sets are tiny and the intent stays readable.
     *
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): User
    {
        $user = User::fromRow($row);
        $id = $user->id;

        $roles = $this->fetchAll(
            'SELECT r.`code` FROM `user_roles` ur JOIN `roles` r ON r.`id` = ur.`role_id` WHERE ur.`user_id` = :id',
            ['id' => $id]
        );
        $user->setRoles(array_column($roles, 'code'));

        $permissions = $this->fetchAll(
            'SELECT DISTINCT p.`code`
             FROM `user_roles` ur
             JOIN `role_permissions` rp ON rp.`role_id` = ur.`role_id`
             JOIN `permissions` p       ON p.`id` = rp.`permission_id`
             WHERE ur.`user_id` = :id',
            ['id' => $id]
        );
        $user->setPermissions(array_column($permissions, 'code'));

        $profile = $this->fetchOne(
            'SELECT bp.*, bt.`name` AS business_type_name
             FROM `business_profiles` bp
             LEFT JOIN `business_types` bt ON bt.`id` = bp.`business_type_id`
             WHERE bp.`user_id` = :id AND bp.`deleted_at` IS NULL',
            ['id' => $id]
        );
        $user->setBusinessProfile($profile === null ? null : BusinessProfile::fromRow($profile));

        return $user;
    }
}
