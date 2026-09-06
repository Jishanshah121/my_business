<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BusinessProfile;

final class BusinessProfileRepository extends Repository
{
    private const SELECT = 'SELECT bp.*, bt.`name` AS business_type_name
                            FROM `business_profiles` bp
                            LEFT JOIN `business_types` bt ON bt.`id` = bp.`business_type_id`';

    public function find(int $id): ?BusinessProfile
    {
        $row = $this->fetchOne(self::SELECT . ' WHERE bp.`id` = :id AND bp.`deleted_at` IS NULL', ['id' => $id]);

        return $row === null ? null : BusinessProfile::fromRow($row);
    }

    public function findByUser(int $userId): ?BusinessProfile
    {
        $row = $this->fetchOne(
            self::SELECT . ' WHERE bp.`user_id` = :u AND bp.`deleted_at` IS NULL',
            ['u' => $userId]
        );

        return $row === null ? null : BusinessProfile::fromRow($row);
    }

    public function gstinExists(string $gstin, ?int $exceptUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM `business_profiles` WHERE `gstin` = :g AND `deleted_at` IS NULL';
        $bindings = ['g' => strtoupper(trim($gstin))];

        if ($exceptUserId !== null) {
            $sql .= ' AND `user_id` <> :u';
            $bindings['u'] = $exceptUserId;
        }

        return (int) $this->fetchColumn($sql, $bindings) > 0;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        return $this->insertRow('business_profiles', $data);
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): int
    {
        return $this->updateRow('business_profiles', $id, $data);
    }

    /**
     * Approve a business and move the user onto a B2B pricing group.
     *
     * Both happen in one transaction: an approved profile whose user is still
     * on retail pricing would quietly deny the customer the rates they were
     * just told they qualify for.
     */
    public function approve(int $profileId, int $approvedBy, string $customerGroupCode = 'b2b_standard'): void
    {
        $this->transaction(function () use ($profileId, $approvedBy, $customerGroupCode): void {
            $this->run(
                "UPDATE `business_profiles`
                 SET `status` = 'approved', `approved_by` = :by, `approved_at` = NOW(), `rejection_reason` = NULL
                 WHERE `id` = :id",
                ['id' => $profileId, 'by' => $approvedBy]
            );

            $this->run(
                'UPDATE `users` u
                 JOIN `business_profiles` bp ON bp.`user_id` = u.`id`
                 JOIN `customer_groups` cg   ON cg.`code` = :grp
                 SET u.`customer_group_id` = cg.`id`
                 WHERE bp.`id` = :id',
                ['id' => $profileId, 'grp' => $customerGroupCode]
            );
        });
    }

    public function reject(int $profileId, int $rejectedBy, string $reason): void
    {
        $this->transaction(function () use ($profileId, $rejectedBy, $reason): void {
            $this->run(
                "UPDATE `business_profiles`
                 SET `status` = 'rejected', `approved_by` = :by, `approved_at` = NULL, `rejection_reason` = :r
                 WHERE `id` = :id",
                ['id' => $profileId, 'by' => $rejectedBy, 'r' => $reason]
            );

            // Back to retail pricing — a rejected business is still a customer.
            $this->run(
                "UPDATE `users` u
                 JOIN `business_profiles` bp ON bp.`user_id` = u.`id`
                 JOIN `customer_groups` cg   ON cg.`code` = 'retail'
                 SET u.`customer_group_id` = cg.`id`
                 WHERE bp.`id` = :id",
                ['id' => $profileId]
            );
        });
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function paginateByStatus(string $status, int $limit = 25, int $offset = 0): array
    {
        return $this->fetchAll(
            'SELECT bp.*, bt.`name` AS business_type_name, u.`email`, u.`first_name`, u.`last_name`
             FROM `business_profiles` bp
             LEFT JOIN `business_types` bt ON bt.`id` = bp.`business_type_id`
             JOIN `users` u ON u.`id` = bp.`user_id`
             WHERE bp.`status` = :s AND bp.`deleted_at` IS NULL
             ORDER BY bp.`created_at` ASC
             LIMIT :lim OFFSET :off',
            ['s' => $status, 'lim' => $limit, 'off' => $offset]
        );
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM `business_profiles` WHERE `status` = :s AND `deleted_at` IS NULL',
            ['s' => $status]
        );
    }

    /** @return list<array<string,mixed>> */
    public function businessTypes(): array
    {
        return $this->fetchAll(
            'SELECT `id`, `code`, `name` FROM `business_types`
             WHERE `is_active` = 1 AND `deleted_at` IS NULL ORDER BY `sort_order`'
        );
    }
}
