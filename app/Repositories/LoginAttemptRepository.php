<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Audit trail of authentication attempts.
 *
 * Separate from the rate limiter: the limiter decides whether to allow the
 * next attempt, this records what happened for the admin security log. Both
 * are needed — one is a control, the other is evidence.
 */
final class LoginAttemptRepository extends Repository
{
    public function record(string $identifier, string $ipBinary, string $userAgent, bool $successful): void
    {
        $this->run(
            'INSERT INTO `login_attempts` (`identifier`, `ip_address`, `user_agent`, `successful`)
             VALUES (:i, :ip, :ua, :s)',
            [
                // Store the identifier as submitted but capped, so a huge
                // payload cannot bloat the table.
                'i'  => mb_substr($identifier, 0, 191),
                'ip' => $ipBinary,
                'ua' => mb_substr($userAgent, 0, 255),
                's'  => $successful ? 1 : 0,
            ]
        );
    }

    public function recentFailures(string $identifier, int $minutes = 15): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM `login_attempts`
             WHERE `identifier` = :i AND `successful` = 0
               AND `created_at` > DATE_SUB(NOW(), INTERVAL :m MINUTE)',
            ['i' => $identifier, 'm' => $minutes]
        );
    }

    /** @return list<array<string,mixed>> */
    public function recentForIdentifier(string $identifier, int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT `successful`, `created_at`, INET6_NTOA(`ip_address`) AS ip, `user_agent`
             FROM `login_attempts` WHERE `identifier` = :i
             ORDER BY `created_at` DESC LIMIT :lim',
            ['i' => $identifier, 'lim' => $limit]
        );
    }

    public function prune(int $days = 90): int
    {
        return $this->run(
            'DELETE FROM `login_attempts` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL :d DAY)',
            ['d' => $days]
        )->rowCount();
    }
}
