<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO connection factory.
 *
 * One shared connection per named config entry. Every query in the
 * application goes through prepared statements on this handle — emulation is
 * off so that placeholders are bound by the server, not string-interpolated
 * by the driver.
 */
final class Database
{
    /** @var array<string,PDO> */
    private static array $connections = [];

    public static function connection(string $name = 'mysql'): PDO
    {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        $config = Config::get("database.connections.{$name}");
        if (!is_array($config)) {
            throw new RuntimeException("Database connection [{$name}] is not configured.");
        }

        return self::$connections[$name] = self::connect($config);
    }

    /**
     * Connect without selecting a database — used by `db:create` before the
     * schema exists.
     */
    public static function serverConnection(string $name = 'mysql'): PDO
    {
        $config = Config::get("database.connections.{$name}");
        if (!is_array($config)) {
            throw new RuntimeException("Database connection [{$name}] is not configured.");
        }

        $config['database'] = null;

        return self::connect($config);
    }

    /** @param array<string,mixed> $config */
    private static function connect(array $config): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d', $config['host'], (int) $config['port']);
        if (!empty($config['database'])) {
            $dsn .= ';dbname=' . $config['database'];
        }
        $dsn .= ';charset=' . ($config['charset'] ?? 'utf8mb4');

        try {
            return new PDO(
                $dsn,
                (string) $config['username'],
                (string) ($config['password'] ?? ''),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ] + self::initCommandOption()
            );
        } catch (PDOException $e) {
            // Never leak credentials into an exception message.
            throw new RuntimeException(
                sprintf(
                    'Could not connect to MySQL at %s:%s (database: %s). %s',
                    $config['host'],
                    $config['port'],
                    $config['database'] ?? '-',
                    $e->getMessage()
                ),
                (int) $e->getCode()
            );
        }
    }

    /**
     * The init-command attribute moved namespace in PHP 8.5 (PDO::MYSQL_* is
     * deprecated in favour of Pdo\Mysql::ATTR_*). We target 8.2 but must not
     * emit deprecations on newer runtimes, so resolve whichever exists.
     *
     * @return array<int,string>
     */
    private static function initCommandOption(): array
    {
        $command = "SET SESSION sql_mode='STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION', time_zone='+05:30'";

        if (class_exists('\\Pdo\\Mysql') && defined('\\Pdo\\Mysql::ATTR_INIT_COMMAND')) {
            return [constant('\\Pdo\\Mysql::ATTR_INIT_COMMAND') => $command];
        }

        return defined('PDO::MYSQL_ATTR_INIT_COMMAND')
            ? [constant('PDO::MYSQL_ATTR_INIT_COMMAND') => $command]
            : [];
    }

    public static function reset(): void
    {
        self::$connections = [];
    }
}
