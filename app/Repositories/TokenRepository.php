<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Single-use tokens for password reset and email verification.
 *
 * Only the SHA-256 of a token is ever stored. A database dump therefore does
 * not let anyone reset an account, and a token can be verified without the
 * plaintext existing anywhere but the email that carried it.
 */
final class TokenRepository extends Repository
{
    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    // -------------------------------------------------------- password reset

    public function createPasswordReset(string $email, string $token, int $ttlMinutes, string $ipBinary): void
    {
        // One live reset per address: issuing a new link invalidates the old.
        $this->run('DELETE FROM `password_resets` WHERE `email` = :e AND `used_at` IS NULL', ['e' => $email]);

        $this->run(
            'INSERT INTO `password_resets` (`email`, `token_hash`, `expires_at`, `ip_address`)
             VALUES (:e, :t, DATE_ADD(NOW(), INTERVAL :ttl MINUTE), :ip)',
            ['e' => $email, 't' => $this->hash($token), 'ttl' => $ttlMinutes, 'ip' => $ipBinary]
        );
    }

    /** @return array<string,mixed>|null */
    public function findValidPasswordReset(string $token): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM `password_resets`
             WHERE `token_hash` = :t AND `used_at` IS NULL AND `expires_at` > NOW() LIMIT 1',
            ['t' => $this->hash($token)]
        );
    }

    public function consumePasswordReset(int $id): void
    {
        $this->run('UPDATE `password_resets` SET `used_at` = NOW() WHERE `id` = :id', ['id' => $id]);
    }

    // ---------------------------------------------------- email verification

    public function createEmailVerification(int $userId, string $token, int $ttlMinutes): void
    {
        $this->run('DELETE FROM `email_verifications` WHERE `user_id` = :u AND `used_at` IS NULL', ['u' => $userId]);

        $this->run(
            'INSERT INTO `email_verifications` (`user_id`, `token_hash`, `expires_at`)
             VALUES (:u, :t, DATE_ADD(NOW(), INTERVAL :ttl MINUTE))',
            ['u' => $userId, 't' => $this->hash($token), 'ttl' => $ttlMinutes]
        );
    }

    /** @return array<string,mixed>|null */
    public function findValidEmailVerification(string $token): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM `email_verifications`
             WHERE `token_hash` = :t AND `used_at` IS NULL AND `expires_at` > NOW() LIMIT 1',
            ['t' => $this->hash($token)]
        );
    }

    public function consumeEmailVerification(int $id): void
    {
        $this->run('UPDATE `email_verifications` SET `used_at` = NOW() WHERE `id` = :id', ['id' => $id]);
    }

    public function pruneExpired(): int
    {
        $a = $this->run('DELETE FROM `password_resets` WHERE `expires_at` < NOW()')->rowCount();
        $b = $this->run('DELETE FROM `email_verifications` WHERE `expires_at` < NOW()')->rowCount();

        return $a + $b;
    }
}
