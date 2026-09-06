<?php

declare(strict_types=1);

namespace App\Models;

final class BusinessProfile
{
    private function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly ?int $businessTypeId,
        public readonly ?string $businessTypeName,
        public readonly string $companyName,
        public readonly string $contactPerson,
        public readonly string $contactPhone,
        public readonly ?string $contactEmail,
        public readonly ?string $gstin,
        public readonly ?string $pan,
        public readonly ?string $fssaiLicence,
        public readonly ?string $expectedMonthlySpend,
        public readonly string $status,
        public readonly ?string $rejectionReason,
        public readonly ?string $approvedAt,
        public readonly bool $creditEnabled,
        public readonly string $creditLimit,
        public readonly int $creditDays,
        public readonly string $creditUsed,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id:                   (int) $row['id'],
            userId:               (int) $row['user_id'],
            businessTypeId:       $row['business_type_id'] !== null ? (int) $row['business_type_id'] : null,
            businessTypeName:     isset($row['business_type_name']) ? (string) $row['business_type_name'] : null,
            companyName:          (string) $row['company_name'],
            contactPerson:        (string) $row['contact_person'],
            contactPhone:         (string) $row['contact_phone'],
            contactEmail:         $row['contact_email'] !== null ? (string) $row['contact_email'] : null,
            gstin:                $row['gstin'] !== null ? (string) $row['gstin'] : null,
            pan:                  $row['pan'] !== null ? (string) $row['pan'] : null,
            fssaiLicence:         $row['fssai_licence'] !== null ? (string) $row['fssai_licence'] : null,
            expectedMonthlySpend: $row['expected_monthly_spend'] !== null ? (string) $row['expected_monthly_spend'] : null,
            status:               (string) $row['status'],
            rejectionReason:      $row['rejection_reason'] !== null ? (string) $row['rejection_reason'] : null,
            approvedAt:           $row['approved_at'] !== null ? (string) $row['approved_at'] : null,
            creditEnabled:        (bool) $row['credit_enabled'],
            creditLimit:          (string) $row['credit_limit'],
            creditDays:           (int) $row['credit_days'],
            creditUsed:           (string) $row['credit_used'],
        );
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Awaiting approval',
            'approved'  => 'Approved',
            'rejected'  => 'Not approved',
            'suspended' => 'Suspended',
            default     => ucfirst($this->status),
        };
    }

    /** Credit still available, in rupees as a decimal string. */
    public function creditAvailable(): string
    {
        if (!$this->creditEnabled) {
            return '0.00';
        }

        return number_format(max(0, (float) $this->creditLimit - (float) $this->creditUsed), 2, '.', '');
    }
}
