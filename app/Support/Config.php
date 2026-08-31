<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Read-only configuration repository backed by the PHP files in config/.
 * Access is dot-notated: Config::get('database.connections.mysql.host').
 */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];

    private static bool $loaded = false;

    public static function load(string $configDir): void
    {
        if (!is_dir($configDir)) {
            throw new RuntimeException("Config directory not found at {$configDir}.");
        }

        foreach (glob($configDir . '/*.php') ?: [] as $file) {
            $key = basename($file, '.php');
            /** @psalm-suppress UnresolvableInclude */
            self::$items[$key] = require $file;
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            throw new RuntimeException('Config::load() must be called before Config::get().');
        }

        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** @return array<string,mixed> */
    public static function all(): array
    {
        return self::$items;
    }
}
