<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\HttpException;

/**
 * Authorisation checks.
 *
 * Always asked about a permission code, never a role name — so roles stay
 * editable data and the code does not encode who is allowed to do what.
 */
final class Gate
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function allows(string $permission): bool
    {
        return $this->auth->user()?->can($permission) ?? false;
    }

    public function denies(string $permission): bool
    {
        return !$this->allows($permission);
    }

    /** @param list<string> $permissions */
    public function any(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($permission)) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $permissions */
    public function all(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->denies($permission)) {
                return false;
            }
        }

        return true;
    }

    /** @throws HttpException 403 */
    public function authorize(string $permission): void
    {
        if ($this->denies($permission)) {
            throw new HttpException(403, 'You do not have permission to do that.');
        }
    }

    /** Can this user reach the admin area at all? */
    public function isStaff(): bool
    {
        return $this->auth->user()?->isStaff() ?? false;
    }

    /**
     * Ownership check for customer-facing resources: staff with the right
     * permission may act on anyone's record, a customer only on their own.
     */
    public function owns(?int $ownerId, string $staffPermission): bool
    {
        $user = $this->auth->user();

        if ($user === null || $ownerId === null) {
            return false;
        }

        return $user->id === $ownerId || $user->can($staffPermission);
    }
}
