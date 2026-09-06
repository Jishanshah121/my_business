<?php

declare(strict_types=1);

namespace App\Support\Session;

use App\Support\Database;
use PDO;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * Session storage in the `sessions` table.
 *
 * Chosen over Redis for Phase 2 because the Redis extension is not present on
 * the target machine and untested storage is worse than no abstraction. The
 * table already exists in the schema for exactly this purpose, and it buys
 * something Redis would not give for free: an admin (or a password change) can
 * invalidate every live session for one user with a single DELETE.
 *
 * Swapping to Redis later means one new class implementing the same interface.
 */
final class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private PDO $pdo;

    private ?int $userId = null;

    private string $ipAddress;

    private string $userAgent;

    public function __construct(
        private readonly int $lifetimeMinutes = 120,
        ?PDO $pdo = null,
    ) {
        $this->pdo = $pdo ?? Database::connection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $packed = @inet_pton(is_string($ip) ? $ip : '0.0.0.0');
        $this->ipAddress = $packed === false ? (string) inet_pton('0.0.0.0') : $packed;
        $this->userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    /** Called by AuthService so the row can be attributed to a user. */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $stmt = $this->pdo->prepare(
            'SELECT `payload`, `user_id` FROM `sessions` WHERE `id` = :id AND `expires_at` > NOW() LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return '';
        }

        if ($row['user_id'] !== null) {
            $this->userId = (int) $row['user_id'];
        }

        return (string) ($row['payload'] ?? '');
    }

    public function write(string $id, string $data): bool
    {
        $expires = date('Y-m-d H:i:s', time() + $this->lifetimeMinutes * 60);

        $stmt = $this->pdo->prepare(
            'INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `expires_at`)
             VALUES (:id, :user_id, :ip, :ua, :payload, NOW(), :expires)
             ON DUPLICATE KEY UPDATE
                 `user_id`       = VALUES(`user_id`),
                 `ip_address`    = VALUES(`ip_address`),
                 `user_agent`    = VALUES(`user_agent`),
                 `payload`       = VALUES(`payload`),
                 `last_activity` = NOW(),
                 `expires_at`    = VALUES(`expires_at`)'
        );

        $stmt->bindValue('id', $id);
        $stmt->bindValue('user_id', $this->userId, $this->userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue('ip', $this->ipAddress, PDO::PARAM_LOB);
        $stmt->bindValue('ua', $this->userAgent);
        $stmt->bindValue('payload', $data);
        $stmt->bindValue('expires', $expires);

        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        return $this->pdo->prepare('DELETE FROM `sessions` WHERE `id` = :id')->execute(['id' => $id]);
    }

    /** @return int|false */
    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM `sessions` WHERE `expires_at` < NOW()');
        $stmt->execute();

        return $stmt->rowCount();
    }

    /** Touch without rewriting the payload — cheaper on read-only requests. */
    public function updateTimestamp(string $id, string $data): bool
    {
        $expires = date('Y-m-d H:i:s', time() + $this->lifetimeMinutes * 60);
        $stmt = $this->pdo->prepare(
            'UPDATE `sessions` SET `last_activity` = NOW(), `expires_at` = :expires WHERE `id` = :id'
        );

        return $stmt->execute(['id' => $id, 'expires' => $expires]);
    }

    public function validateId(string $id): bool
    {
        return preg_match('/^[a-zA-Z0-9,\-]{22,128}$/', $id) === 1;
    }

    /** Invalidate every session belonging to a user (password change, admin action). */
    public function destroyForUser(int $userId, ?string $exceptSessionId = null): int
    {
        $sql = 'DELETE FROM `sessions` WHERE `user_id` = :user_id';
        $bindings = ['user_id' => $userId];

        if ($exceptSessionId !== null) {
            $sql .= ' AND `id` <> :except';
            $bindings['except'] = $exceptSessionId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }
}
