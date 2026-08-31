<?php

declare(strict_types=1);

namespace Database;

use App\Support\Database;
use PDO;

/**
 * Base class for seeders.
 *
 * Seeders are PHP rather than flat .sql because the demo catalog is generated
 * combinatorially (variants x packs x quantity tiers x inventory) and must be
 * idempotent — re-running a seeder updates rather than duplicates. Every row a
 * seeder writes is tagged `is_demo_data = 1` so the entire placeholder catalog
 * can be removed with one statement when real supplier data arrives.
 */
abstract class Seeder
{
    protected PDO $pdo;

    /** @var list<string> */
    protected array $messages = [];

    /** @var array<string,bool> table => has an `id` column */
    private static array $idColumnCache = [];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    abstract public function run(): void;

    /** Human-readable name shown by `php bin/console db:seed`. */
    public function name(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /** Seeders with a lower order run first. */
    public function order(): int
    {
        return 100;
    }

    /** @return list<string> */
    public function messages(): array
    {
        return $this->messages;
    }

    protected function info(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * Insert a row, or update it if the unique key already matches.
     *
     * @param array<string,mixed> $attributes Columns used to find the row.
     * @param array<string,mixed> $values     Columns to write.
     * @return int The row's primary key.
     */
    protected function upsert(string $table, array $attributes, array $values = []): int
    {
        $where = implode(' AND ', array_map(
            static fn (string $c): string => "`{$c}` <=> :w_{$c}",
            array_keys($attributes)
        ));

        // Junction tables (product_tags, user_roles, role_permissions ...) use
        // a composite primary key and have no `id` column, so select a constant
        // for those and return 0 — the caller has nothing to reference anyway.
        $hasId = $this->hasIdColumn($table);
        $select = $hasId ? '`id`' : '1';

        $find = $this->pdo->prepare("SELECT {$select} FROM `{$table}` WHERE {$where} LIMIT 1");
        foreach ($attributes as $column => $value) {
            $find->bindValue(":w_{$column}", $value, $this->typeOf($value));
        }
        $find->execute();
        $id = $find->fetchColumn();

        if ($id !== false) {
            if ($values !== []) {
                $set = implode(', ', array_map(
                    static fn (string $c): string => "`{$c}` = :{$c}",
                    array_keys($values)
                ));
                if ($hasId) {
                    $update = $this->pdo->prepare("UPDATE `{$table}` SET {$set} WHERE `id` = :pk_id");
                    $update->bindValue(':pk_id', (int) $id, PDO::PARAM_INT);
                } else {
                    $update = $this->pdo->prepare("UPDATE `{$table}` SET {$set} WHERE {$where}");
                    foreach ($attributes as $column => $value) {
                        $update->bindValue(":w_{$column}", $value, $this->typeOf($value));
                    }
                }
                foreach ($values as $column => $value) {
                    $update->bindValue(":{$column}", $value, $this->typeOf($value));
                }
                $update->execute();
            }

            return $hasId ? (int) $id : 0;
        }

        $row = $attributes + $values;
        $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($row)));
        $placeholders = implode(', ', array_map(static fn (string $c): string => ":{$c}", array_keys($row)));

        $insert = $this->pdo->prepare("INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})");
        foreach ($row as $column => $value) {
            $insert->bindValue(":{$column}", $value, $this->typeOf($value));
        }
        $insert->execute();

        return $hasId ? (int) $this->pdo->lastInsertId() : 0;
    }

    /** Does this table have an auto-increment `id` column? Cached per process. */
    private function hasIdColumn(string $table): bool
    {
        if (!array_key_exists($table, self::$idColumnCache)) {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = \'id\''
            );
            $stmt->execute(['t' => $table]);
            self::$idColumnCache[$table] = (int) $stmt->fetchColumn() > 0;
        }

        return self::$idColumnCache[$table];
    }

    /** @param array<string,mixed> $row */
    protected function insert(string $table, array $row): int
    {
        $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($row)));
        $placeholders = implode(', ', array_map(static fn (string $c): string => ":{$c}", array_keys($row)));

        $stmt = $this->pdo->prepare("INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})");
        foreach ($row as $column => $value) {
            $stmt->bindValue(":{$column}", $value, $this->typeOf($value));
        }
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    protected function count(string $table, string $where = '1', array $bindings = []): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$where}");
        $stmt->execute($bindings);

        return (int) $stmt->fetchColumn();
    }

    private function typeOf(mixed $value): int
    {
        return match (true) {
            is_int($value)  => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default         => PDO::PARAM_STR,
        };
    }

    /** URL-safe slug generator shared by the catalog seeders. */
    protected function slug(string $value): string
    {
        $value = strtolower(trim($value));
        // Apostrophes vanish; "&" acts as a word separator rather than
        // expanding to "and", so "Foil & Cling Film" -> foil-cling-film.
        $value = str_replace(['"', "'", '&'], ['', '', ' '], $value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;

        return trim($value, '-');
    }
}
