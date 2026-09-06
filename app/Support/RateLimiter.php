<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * Fixed-window rate limiter.
 *
 * Used to slow down credential stuffing, password-reset probing, coupon
 * brute-forcing and search scraping. Limits are configured per action in
 * config/security.php rather than hard-coded at call sites.
 *
 * Identifiers are hashed before storage so this table never holds a raw email
 * address or IP.
 */
final class RateLimiter
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * Register one hit. Returns false when the caller is over the limit.
     *
     * @param string $action     Key into config('security.rate_limits').
     * @param string $identifier IP, email, user id — whatever the limit is per.
     */
    public function hit(string $action, string $identifier): bool
    {
        [$max, $window] = $this->limitFor($action);
        $key = $this->key($action, $identifier);
        $now = time();

        // Join an outer transaction rather than starting a second one. MySQL
        // has no nested transactions, and this is called from inside larger
        // units of work (checkout, quote acceptance) as well as standalone.
        $owns = !$this->pdo->inTransaction();
        if ($owns) {
            $this->pdo->beginTransaction();
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT `id`, `attempts`, UNIX_TIMESTAMP(`window_start`) AS started,
                        UNIX_TIMESTAMP(`blocked_until`) AS blocked
                 FROM `rate_limits` WHERE `key_hash` = :k'
                . ($this->pdo->inTransaction() ? ' FOR UPDATE' : '')
            );
            $stmt->execute(['k' => $key]);
            $row = $stmt->fetch();

            if ($row === false) {
                $this->pdo->prepare(
                    'INSERT INTO `rate_limits` (`key_hash`, `action`, `attempts`, `window_start`, `expires_at`)
                     VALUES (:k, :a, 1, FROM_UNIXTIME(:now), FROM_UNIXTIME(:exp))'
                )->execute(['k' => $key, 'a' => $action, 'now' => $now, 'exp' => $now + $window]);

                $this->commitIfOwned($owns);

                return true;
            }

            // Still inside a penalty period.
            if ($row['blocked'] !== null && (int) $row['blocked'] > $now) {
                $this->commitIfOwned($owns);

                return false;
            }

            // Window elapsed: start a new one.
            if ($now - (int) $row['started'] >= $window) {
                $this->pdo->prepare(
                    'UPDATE `rate_limits`
                     SET `attempts` = 1, `window_start` = FROM_UNIXTIME(:now),
                         `expires_at` = FROM_UNIXTIME(:exp), `blocked_until` = NULL
                     WHERE `id` = :id'
                )->execute(['now' => $now, 'exp' => $now + $window, 'id' => $row['id']]);

                $this->commitIfOwned($owns);

                return true;
            }

            $attempts = (int) $row['attempts'] + 1;

            if ($attempts > $max) {
                // Block for the remainder of the window.
                $blockedUntil = (int) $row['started'] + $window;
                $this->pdo->prepare(
                    'UPDATE `rate_limits` SET `attempts` = :n, `blocked_until` = FROM_UNIXTIME(:until) WHERE `id` = :id'
                )->execute(['n' => $attempts, 'until' => $blockedUntil, 'id' => $row['id']]);

                $this->commitIfOwned($owns);

                return false;
            }

            $this->pdo->prepare('UPDATE `rate_limits` SET `attempts` = :n WHERE `id` = :id')
                ->execute(['n' => $attempts, 'id' => $row['id']]);

            $this->commitIfOwned($owns);

            return true;
        } catch (\Throwable $e) {
            if ($owns && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function commitIfOwned(bool $owns): void
    {
        if ($owns && $this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    /** Check without consuming an attempt. */
    public function tooManyAttempts(string $action, string $identifier): bool
    {
        [$max, $window] = $this->limitFor($action);

        $stmt = $this->pdo->prepare(
            'SELECT `attempts`, UNIX_TIMESTAMP(`window_start`) AS started, UNIX_TIMESTAMP(`blocked_until`) AS blocked
             FROM `rate_limits` WHERE `key_hash` = :k LIMIT 1'
        );
        $stmt->execute(['k' => $this->key($action, $identifier)]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        $now = time();

        if ($row['blocked'] !== null && (int) $row['blocked'] > $now) {
            return true;
        }

        if ($now - (int) $row['started'] >= $window) {
            return false;
        }

        return (int) $row['attempts'] >= $max;
    }

    /** Seconds until the caller may try again. */
    public function availableIn(string $action, string $identifier): int
    {
        [, $window] = $this->limitFor($action);

        $stmt = $this->pdo->prepare(
            'SELECT UNIX_TIMESTAMP(`window_start`) AS started, UNIX_TIMESTAMP(`blocked_until`) AS blocked
             FROM `rate_limits` WHERE `key_hash` = :k LIMIT 1'
        );
        $stmt->execute(['k' => $this->key($action, $identifier)]);
        $row = $stmt->fetch();

        if ($row === false) {
            return 0;
        }

        $until = $row['blocked'] !== null ? (int) $row['blocked'] : (int) $row['started'] + $window;

        return max(0, $until - time());
    }

    /** Clear the counter — called after a successful login. */
    public function clear(string $action, string $identifier): void
    {
        $this->pdo->prepare('DELETE FROM `rate_limits` WHERE `key_hash` = :k')
            ->execute(['k' => $this->key($action, $identifier)]);
    }

    /** Remove expired counters. Called from the scheduled cleanup. */
    public function prune(): int
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM `rate_limits` WHERE `expires_at` < NOW() AND (`blocked_until` IS NULL OR `blocked_until` < NOW())'
        );
        $stmt->execute();

        return $stmt->rowCount();
    }

    /** @return array{0:int,1:int} max attempts, window seconds */
    private function limitFor(string $action): array
    {
        $config = Config::get("security.rate_limits.{$action}");

        if (!is_array($config)) {
            $config = Config::get('security.rate_limits.api_default', ['max' => 60, 'window' => 60]);
        }

        return [(int) ($config['max'] ?? 60), (int) ($config['window'] ?? 60)];
    }

    private function key(string $action, string $identifier): string
    {
        return hash('sha256', $action . '|' . strtolower(trim($identifier)));
    }
}
