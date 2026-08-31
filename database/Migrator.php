<?php

declare(strict_types=1);

namespace Database;

use App\Support\Config;
use App\Support\Database;
use App\Support\SqlSplitter;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Forward-only SQL migration runner.
 *
 * Migrations are numbered .sql files. Once applied, a migration's checksum is
 * recorded; editing an applied file afterwards aborts the next run rather than
 * letting environments silently diverge.
 *
 * Note: MySQL implicitly commits DDL, so a failed migration cannot be rolled
 * back automatically. The runner therefore stops at the first failure and
 * reports exactly which file and statement failed, so the fix is a new
 * migration rather than a guess about partial state.
 */
final class Migrator
{
    private PDO $pdo;
    private string $path;
    private string $table;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
        $this->path = (string) Config::get('database.migrations.path');
        $this->table = (string) Config::get('database.migrations.table', 'migrations');
    }

    public function ensureRepository(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration`  VARCHAR(255) NOT NULL,
                `checksum`   CHAR(64)     NOT NULL,
                `batch`      INT UNSIGNED NOT NULL,
                `statements` INT UNSIGNED NOT NULL DEFAULT 0,
                `ran_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_migrations_migration` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
        );
    }

    /** @return list<string> */
    public function files(): array
    {
        $files = glob($this->path . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        return array_map('basename', $files);
    }

    /** @return array<string,array{checksum:string,batch:int,ran_at:string}> */
    public function applied(): array
    {
        $rows = $this->pdo
            ->query("SELECT `migration`, `checksum`, `batch`, `ran_at` FROM `{$this->table}` ORDER BY `id`")
            ->fetchAll();

        $applied = [];
        foreach ($rows as $row) {
            $applied[$row['migration']] = [
                'checksum' => $row['checksum'],
                'batch'    => (int) $row['batch'],
                'ran_at'   => $row['ran_at'],
            ];
        }

        return $applied;
    }

    /** @return list<string> Names of migrations not yet applied. */
    public function pending(): array
    {
        $applied = $this->applied();

        return array_values(array_filter(
            $this->files(),
            static fn (string $file): bool => !isset($applied[$file])
        ));
    }

    /**
     * Verify that no already-applied migration has been edited on disk.
     *
     * @return list<string> Names of tampered migrations.
     */
    public function drifted(): array
    {
        $drifted = [];
        foreach ($this->applied() as $name => $meta) {
            $file = $this->path . '/' . $name;
            if (!is_file($file)) {
                $drifted[] = "{$name} (applied but missing from disk)";
                continue;
            }
            if (hash_file('sha256', $file) !== $meta['checksum']) {
                $drifted[] = "{$name} (checksum mismatch — file edited after it was applied)";
            }
        }

        return $drifted;
    }

    /**
     * Run every pending migration.
     *
     * @param callable(string,int):void|null $onMigration Progress callback.
     * @return list<string> Names of migrations that ran.
     */
    public function run(?callable $onMigration = null): array
    {
        $this->ensureRepository();

        $drifted = $this->drifted();
        if ($drifted !== []) {
            throw new RuntimeException(
                "Refusing to migrate. Applied migrations were modified:\n  - "
                . implode("\n  - ", $drifted)
                . "\nCreate a new migration instead of editing an applied one."
            );
        }

        $pending = $this->pending();
        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatch();
        $ran = [];

        foreach ($pending as $name) {
            $file = $this->path . '/' . $name;
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new RuntimeException("Could not read migration {$name}.");
            }

            $statements = SqlSplitter::split($sql);
            $index = 0;

            foreach ($statements as $statement) {
                $index++;
                try {
                    $this->pdo->exec($statement);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "Migration {$name} failed at statement {$index}:\n"
                        . substr($statement, 0, 400) . "\n\n"
                        . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO `{$this->table}` (`migration`, `checksum`, `batch`, `statements`)
                 VALUES (:migration, :checksum, :batch, :statements)"
            );
            $insert->execute([
                'migration'  => $name,
                'checksum'   => hash_file('sha256', $file),
                'batch'      => $batch,
                'statements' => count($statements),
            ]);

            $ran[] = $name;
            if ($onMigration !== null) {
                $onMigration($name, count($statements));
            }
        }

        return $ran;
    }

    /**
     * Drop every table in the database. Development only — refuses to run
     * against APP_ENV=production.
     */
    public function fresh(): int
    {
        if (Config::get('app.env') === 'production') {
            throw new RuntimeException('migrate:fresh is disabled when APP_ENV=production.');
        }

        $database = (string) Config::get('database.connections.mysql.database');
        $tables = $this->pdo->query(
            'SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ' . $this->pdo->quote($database) . " AND TABLE_TYPE = 'BASE TABLE'"
        )->fetchAll(PDO::FETCH_COLUMN);

        $views = $this->pdo->query(
            'SELECT TABLE_NAME FROM information_schema.VIEWS
             WHERE TABLE_SCHEMA = ' . $this->pdo->quote($database)
        )->fetchAll(PDO::FETCH_COLUMN);

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($views as $view) {
            $this->pdo->exec("DROP VIEW IF EXISTS `{$view}`");
        }
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        return count($tables) + count($views);
    }

    private function nextBatch(): int
    {
        $max = $this->pdo->query("SELECT COALESCE(MAX(`batch`), 0) FROM `{$this->table}`")->fetchColumn();

        return (int) $max + 1;
    }
}
