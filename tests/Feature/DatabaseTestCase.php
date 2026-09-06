<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\Container;
use App\Support\Database;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base for tests that touch the database.
 *
 * Each test runs inside a transaction that is rolled back afterwards, so tests
 * are order-independent and leave the reference data seeded by the bootstrap
 * untouched.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = Database::connection();
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        Container::reset();

        parent::tearDown();
    }

    /** @param array<string,mixed> $overrides */
    protected function createUser(array $overrides = []): int
    {
        $groupCode = $overrides['group'] ?? 'retail';
        unset($overrides['group']);

        $groupId = (int) $this->pdo->query(
            'SELECT `id` FROM `customer_groups` WHERE `code` = ' . $this->pdo->quote($groupCode)
        )->fetchColumn();

        $row = array_merge([
            'uuid'              => $this->uuid4(),
            'customer_group_id' => $groupId,
            'first_name'        => 'Test',
            'last_name'         => 'User',
            'email'             => 'user' . bin2hex(random_bytes(4)) . '@example.test',
            'phone'             => null,
            'password_hash'     => password_hash('CorrectHorse42', PASSWORD_ARGON2ID),
            'status'            => 'active',
        ], $overrides);

        $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($row)));
        $placeholders = implode(', ', array_map(static fn (string $c): string => ":{$c}", array_keys($row)));

        $stmt = $this->pdo->prepare("INSERT INTO `users` ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($row);

        return (int) $this->pdo->lastInsertId();
    }

    /** A real 36-character RFC 4122 v4 UUID — the column is CHAR(36). */
    protected function uuid4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return implode('-', [
            bin2hex(substr($bytes, 0, 4)), bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)), bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6)),
        ]);
    }

    protected function assignRole(int $userId, string $roleCode): void
    {
        $roleId = (int) $this->pdo->query(
            'SELECT `id` FROM `roles` WHERE `code` = ' . $this->pdo->quote($roleCode)
        )->fetchColumn();

        $this->pdo->prepare('INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (?, ?)')
            ->execute([$userId, $roleId]);
    }

    protected function customerGroupOf(int $userId): string
    {
        return (string) $this->pdo->query(
            'SELECT cg.`code` FROM `users` u JOIN `customer_groups` cg ON cg.`id` = u.`customer_group_id`
             WHERE u.`id` = ' . $userId
        )->fetchColumn();
    }
}
