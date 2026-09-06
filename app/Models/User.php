<?php

declare(strict_types=1);

namespace App\Models;

/**
 * A customer or staff member. Deliberately thin: it holds the row and answers
 * questions about itself, and knows nothing about how it was loaded.
 */
final class User
{
    /** @var list<string>|null Lazily filled by UserRepository. */
    private ?array $permissions = null;

    /** @var list<string>|null */
    private ?array $roles = null;

    private ?BusinessProfile $businessProfile = null;

    private function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $customerGroupId,
        public readonly string $firstName,
        public readonly ?string $lastName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly string $passwordHash,
        public readonly string $status,
        public readonly ?string $emailVerifiedAt,
        public readonly ?string $phoneVerifiedAt,
        public readonly ?string $lastLoginAt,
        public readonly bool $marketingOptIn,
        public readonly string $customerGroupCode = 'retail',
        public readonly bool $isB2b = false,
        public readonly bool $pricesIncludeTax = true,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id:                (int) $row['id'],
            uuid:              (string) $row['uuid'],
            customerGroupId:   (int) $row['customer_group_id'],
            firstName:         (string) $row['first_name'],
            lastName:          $row['last_name'] !== null ? (string) $row['last_name'] : null,
            email:             (string) $row['email'],
            phone:             $row['phone'] !== null ? (string) $row['phone'] : null,
            passwordHash:      (string) $row['password_hash'],
            status:            (string) $row['status'],
            emailVerifiedAt:   $row['email_verified_at'] !== null ? (string) $row['email_verified_at'] : null,
            phoneVerifiedAt:   $row['phone_verified_at'] !== null ? (string) $row['phone_verified_at'] : null,
            lastLoginAt:       $row['last_login_at'] !== null ? (string) $row['last_login_at'] : null,
            marketingOptIn:    (bool) ($row['marketing_opt_in'] ?? false),
            customerGroupCode: (string) ($row['customer_group_code'] ?? 'retail'),
            isB2b:             (bool) ($row['is_b2b'] ?? false),
            pricesIncludeTax:  (bool) ($row['prices_include_tax'] ?? true),
        );
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . ($this->lastName ?? ''));
    }

    public function initials(): string
    {
        $first = mb_substr($this->firstName, 0, 1);
        $last = $this->lastName !== null && $this->lastName !== '' ? mb_substr($this->lastName, 0, 1) : '';

        return mb_strtoupper($first . $last);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * May this account hold a session?
     *
     * 'pending' means registered but not yet email-verified. Those accounts are
     * fully usable on purpose — verification is not a gate on the front door,
     * only on the actions that genuinely need a confirmed address. Only
     * suspended and closed accounts are turned away.
     */
    public function canAuthenticate(): bool
    {
        return in_array($this->status, ['active', 'pending'], true);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /** @param list<string> $permissions */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function setBusinessProfile(?BusinessProfile $profile): void
    {
        $this->businessProfile = $profile;
    }

    public function businessProfile(): ?BusinessProfile
    {
        return $this->businessProfile;
    }

    /** @return list<string> */
    public function roles(): array
    {
        return $this->roles ?? [];
    }

    /** @return list<string> */
    public function permissions(): array
    {
        return $this->permissions ?? [];
    }

    public function hasRole(string $code): bool
    {
        return in_array($code, $this->roles(), true);
    }

    /**
     * Permission check. Super Admin passes everything by design — it is the
     * break-glass role and is deliberately not enumerable.
     */
    public function can(string $permission): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return in_array($permission, $this->permissions(), true);
    }

    /** Any staff role at all — the gate for /admin. */
    public function isStaff(): bool
    {
        foreach ($this->roles() as $role) {
            if ($role !== 'customer') {
                return true;
            }
        }

        return false;
    }

    /** B2B pricing applies only once an admin has approved the business. */
    public function hasApprovedBusiness(): bool
    {
        return $this->businessProfile !== null && $this->businessProfile->isApproved();
    }
}
