<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Minimal .env reader.
 *
 * Deliberately dependency-free so that migrations and the console can run
 * before `composer install` has ever been executed. When Composer lands in
 * Phase 2 this can be swapped for vlucas/phpdotenv without changing callers.
 */
final class Env
{
    /** @var array<string,string>|null */
    private static ?array $values = null;

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException(
                "Environment file not found at {$path}. Copy .env.example to .env and configure it."
            );
        }

        $values = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = self::parseValue(trim($value));

            $values[$key] = $value;
        }

        self::$values = $values;
    }

    /**
     * Strips surrounding quotes and any unquoted trailing `#` comment.
     */
    private static function parseValue(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        $first = $raw[0];
        if ($first === '"' || $first === "'") {
            $end = strpos($raw, $first, 1);
            if ($end !== false) {
                $inner = substr($raw, 1, $end - 1);
                return $first === '"' ? stripcslashes($inner) : $inner;
            }
        }

        // Unquoted: a `#` preceded by whitespace begins a comment.
        if (preg_match('/^(.*?)\s+#.*$/', $raw, $m) === 1) {
            $raw = $m[1];
        }

        return trim($raw);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$values === null) {
            throw new RuntimeException('Env::load() must be called before Env::get().');
        }

        if (!array_key_exists($key, self::$values)) {
            return $default;
        }

        $value = self::$values[$key];

        return match (strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }

    /** Fetch a value that must be present and non-empty. */
    public static function require(string $key): string
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            throw new RuntimeException("Required environment variable {$key} is missing or empty.");
        }

        return (string) $value;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        return is_bool($value) ? $value : $default;
    }
}
