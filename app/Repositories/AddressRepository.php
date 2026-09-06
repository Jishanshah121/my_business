<?php

declare(strict_types=1);

namespace App\Repositories;

final class AddressRepository extends Repository
{
    /**
     * Get all active addresses for a user.
     *
     * @return list<array<string,mixed>>
     */
    public function forUser(int $userId): array
    {
        return $this->fetchAll(
            'SELECT * FROM `addresses`
             WHERE `user_id` = :uid AND `deleted_at` IS NULL
             ORDER BY `is_default_shipping` DESC, `id` DESC',
            ['uid' => $userId]
        );
    }

    /**
     * Find a single address for a user.
     *
     * @return array<string,mixed>|null
     */
    public function find(int $addressId, int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM `addresses`
             WHERE `id` = :aid AND `user_id` = :uid AND `deleted_at` IS NULL',
            ['aid' => $addressId, 'uid' => $userId]
        );
    }

    /**
     * Create a new address for a user.
     *
     * @param array<string,mixed> $data
     */
    public function create(int $userId, array $data): int
    {
        $isDefault = !empty($data['is_default_shipping']) ? 1 : 0;

        if ($isDefault) {
            $this->run(
                'UPDATE `addresses` SET `is_default_shipping` = 0, `is_default_billing` = 0
                 WHERE `user_id` = :uid',
                ['uid' => $userId]
            );
        }

        // If this is the user's first address, make it default automatically
        $count = (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM `addresses` WHERE `user_id` = :uid AND `deleted_at` IS NULL',
            ['uid' => $userId]
        );
        if ($count === 0) {
            $isDefault = 1;
        }

        $this->run(
            'INSERT INTO `addresses` (
                `user_id`, `type`, `label`, `contact_name`, `contact_phone`,
                `company_name`, `gstin`, `line1`, `line2`, `landmark`,
                `city`, `state_code`, `state_name`, `pincode`, `country_code`,
                `is_default_shipping`, `is_default_billing`
             ) VALUES (
                :user_id, :type, :label, :contact_name, :contact_phone,
                :company_name, :gstin, :line1, :line2, :landmark,
                :city, :state_code, :state_name, :pincode, :country_code,
                :is_default_shipping, :is_default_billing
             )',
            [
                'user_id'             => $userId,
                'type'                => $data['type'] ?? 'both',
                'label'               => $data['label'] ?? 'Main Outlet',
                'contact_name'        => trim((string) ($data['contact_name'] ?? '')),
                'contact_phone'       => trim((string) ($data['contact_phone'] ?? '')),
                'company_name'        => !empty($data['company_name']) ? trim((string) $data['company_name']) : null,
                'gstin'               => !empty($data['gstin']) ? strtoupper(trim((string) $data['gstin'])) : null,
                'line1'               => trim((string) ($data['line1'] ?? '')),
                'line2'               => !empty($data['line2']) ? trim((string) $data['line2']) : null,
                'landmark'            => !empty($data['landmark']) ? trim((string) $data['landmark']) : null,
                'city'                => trim((string) ($data['city'] ?? 'Bokaro Steel City')),
                'state_code'          => $data['state_code'] ?? '20',
                'state_name'          => $data['state_name'] ?? 'Jharkhand',
                'pincode'             => trim((string) ($data['pincode'] ?? '827001')),
                'country_code'        => 'IN',
                'is_default_shipping' => $isDefault,
                'is_default_billing'  => $isDefault,
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update an address for a user.
     *
     * @param array<string,mixed> $data
     */
    public function update(int $addressId, int $userId, array $data): bool
    {
        $isDefault = !empty($data['is_default_shipping']) ? 1 : 0;

        if ($isDefault) {
            $this->run(
                'UPDATE `addresses` SET `is_default_shipping` = 0, `is_default_billing` = 0
                 WHERE `user_id` = :uid',
                ['uid' => $userId]
            );
        }

        $stmt = $this->run(
            'UPDATE `addresses` SET
                `label` = :label,
                `contact_name` = :contact_name,
                `contact_phone` = :contact_phone,
                `company_name` = :company_name,
                `gstin` = :gstin,
                `line1` = :line1,
                `line2` = :line2,
                `landmark` = :landmark,
                `city` = :city,
                `state_code` = :state_code,
                `state_name` = :state_name,
                `pincode` = :pincode,
                `is_default_shipping` = :is_default_shipping,
                `is_default_billing` = :is_default_billing
             WHERE `id` = :aid AND `user_id` = :uid AND `deleted_at` IS NULL',
            [
                'aid'                 => $addressId,
                'uid'                 => $userId,
                'label'               => $data['label'] ?? 'Main Outlet',
                'contact_name'        => trim((string) ($data['contact_name'] ?? '')),
                'contact_phone'       => trim((string) ($data['contact_phone'] ?? '')),
                'company_name'        => !empty($data['company_name']) ? trim((string) $data['company_name']) : null,
                'gstin'               => !empty($data['gstin']) ? strtoupper(trim((string) $data['gstin'])) : null,
                'line1'               => trim((string) ($data['line1'] ?? '')),
                'line2'               => !empty($data['line2']) ? trim((string) $data['line2']) : null,
                'landmark'            => !empty($data['landmark']) ? trim((string) $data['landmark']) : null,
                'city'                => trim((string) ($data['city'] ?? 'Bokaro Steel City')),
                'state_code'          => $data['state_code'] ?? '20',
                'state_name'          => $data['state_name'] ?? 'Jharkhand',
                'pincode'             => trim((string) ($data['pincode'] ?? '827001')),
                'is_default_shipping' => $isDefault,
                'is_default_billing'  => $isDefault,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Mark an address as the default.
     */
    public function setDefault(int $addressId, int $userId): bool
    {
        $this->run(
            'UPDATE `addresses` SET `is_default_shipping` = 0, `is_default_billing` = 0
             WHERE `user_id` = :uid',
            ['uid' => $userId]
        );

        $stmt = $this->run(
            'UPDATE `addresses` SET `is_default_shipping` = 1, `is_default_billing` = 1
             WHERE `id` = :aid AND `user_id` = :uid AND `deleted_at` IS NULL',
            ['aid' => $addressId, 'uid' => $userId]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Soft delete an address.
     */
    public function delete(int $addressId, int $userId): bool
    {
        $stmt = $this->run(
            'UPDATE `addresses` SET `deleted_at` = NOW()
             WHERE `id` = :aid AND `user_id` = :uid AND `deleted_at` IS NULL',
            ['aid' => $addressId, 'uid' => $userId]
        );

        return $stmt->rowCount() > 0;
    }
}
