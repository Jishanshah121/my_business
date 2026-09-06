<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Runtime settings, read from the database with a config fallback.
 *
 * Application code asks for a setting here, never from config directly, so an
 * admin change takes effect without a deploy (architecture section 12).
 */
final class SettingsRepository extends Repository
{
    /** @var array<string,mixed>|null Loaded once per request. */
    private ?array $cache = null;

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $this->load();

        return $this->cache["{$group}.{$key}"] ?? $default;
    }

    public function bool(string $group, string $key, bool $default = false): bool
    {
        $value = $this->get($group, $key);

        return $value === null ? $default : (bool) $value;
    }

    public function int(string $group, string $key, int $default = 0): int
    {
        $value = $this->get($group, $key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public function string(string $group, string $key, string $default = ''): string
    {
        $value = $this->get($group, $key);

        return is_scalar($value) ? (string) $value : $default;
    }

    /** @return array<string,mixed> Settings safe to expose to the storefront. */
    public function publicSettings(): array
    {
        $this->load();
        $rows = $this->fetchAll('SELECT `group`, `key`, `value`, `type` FROM `settings` WHERE `is_public` = 1');

        $public = [];
        foreach ($rows as $row) {
            $public["{$row['group']}.{$row['key']}"] = $this->cast($row['value'], $row['type']);
        }

        return $public;
    }

    public function set(string $group, string $key, mixed $value): void
    {
        $this->run(
            'UPDATE `settings` SET `value` = :v WHERE `group` = :g AND `key` = :k',
            ['v' => is_bool($value) ? ($value ? '1' : '0') : (string) $value, 'g' => $group, 'k' => $key]
        );

        $this->cache = null;
    }

    private function load(): void
    {
        if ($this->cache !== null) {
            return;
        }

        $this->cache = [];
        foreach ($this->fetchAll('SELECT `group`, `key`, `value`, `type` FROM `settings`') as $row) {
            $this->cache["{$row['group']}.{$row['key']}"] = $this->cast($row['value'], $row['type']);
        }
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'boolean' => $value === '1' || strtolower($value) === 'true',
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
