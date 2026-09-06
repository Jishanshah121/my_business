<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;
use PDOStatement;

/**
 * Base for all repositories.
 *
 * Every SQL statement in this application lives in a Repository subclass and
 * goes through a prepared statement. No SQL in controllers, services or views.
 */
abstract class Repository
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * Run a prepared statement. Accepts named bindings (the normal case) or
     * positional ones.
     *
     * @param array<string|int,mixed> $bindings
     */
    protected function run(string $sql, array $bindings = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                match (true) {
                    is_int($value)  => PDO::PARAM_INT,
                    is_bool($value) => PDO::PARAM_BOOL,
                    is_null($value) => PDO::PARAM_NULL,
                    default         => PDO::PARAM_STR,
                }
            );
        }

        $statement->execute();

        return $statement;
    }

    /**
     * @param array<string,mixed> $bindings
     * @return array<string,mixed>|null
     */
    protected function fetchOne(string $sql, array $bindings = []): ?array
    {
        $row = $this->run($sql, $bindings)->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<string,mixed> $bindings
     * @return list<array<string,mixed>>
     */
    protected function fetchAll(string $sql, array $bindings = []): array
    {
        return $this->run($sql, $bindings)->fetchAll();
    }

    /** @param array<string,mixed> $bindings */
    protected function fetchColumn(string $sql, array $bindings = []): mixed
    {
        return $this->run($sql, $bindings)->fetchColumn();
    }

    /** @param array<string,mixed> $data */
    protected function insertRow(string $table, array $data): int
    {
        $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_map(static fn (string $c): string => ":{$c}", array_keys($data)));

        $this->run("INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})", $data);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param non-empty-array<string,mixed> $data
     */
    protected function updateRow(string $table, int $id, array $data): int
    {
        $set = implode(', ', array_map(static fn (string $c): string => "`{$c}` = :{$c}", array_keys($data)));

        return $this->run("UPDATE `{$table}` SET {$set} WHERE `id` = :__id", $data + ['__id' => $id])->rowCount();
    }

    public function transaction(callable $callback): mixed
    {
        // Nested calls join the outer transaction rather than starting a second.
        if ($this->pdo->inTransaction()) {
            return $callback($this->pdo);
        }

        $this->pdo->beginTransaction();

        try {
            $result = $callback($this->pdo);
            $this->pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function connection(): PDO
    {
        return $this->pdo;
    }
}
