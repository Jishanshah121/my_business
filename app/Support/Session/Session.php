<?php

declare(strict_types=1);

namespace App\Support\Session;

use App\Support\Config;
use RuntimeException;

/**
 * The session API the rest of the application uses.
 *
 * Wraps PHP's native session so that cookie flags, ID regeneration and the
 * absolute-lifetime check happen in exactly one place.
 */
final class Session
{
    private const FLASH_KEY = '_flash';
    private const STARTED_KEY = '_started_at';

    private bool $started = false;

    public function __construct(private readonly DatabaseSessionHandler $handler)
    {
    }

    public function handler(): DatabaseSessionHandler
    {
        return $this->handler;
    }

    public function start(bool $secure): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        if (headers_sent($file, $line)) {
            throw new RuntimeException("Cannot start the session; output already began at {$file}:{$line}.");
        }

        session_set_save_handler($this->handler, true);

        session_set_cookie_params([
            'lifetime' => 0,               // session cookie: dies with the browser
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,            // never readable from JavaScript
            'samesite' => (string) Config::get('security.session.same_site', 'Lax'),
        ]);
        session_name((string) Config::get('security.session.cookie', 'supplykaro_session'));

        session_start();
        $this->started = true;

        $this->enforceAbsoluteLifetime();
        $this->ageFlashData();
    }

    /**
     * Idle timeout is handled by expires_at in the table. This enforces the
     * separate absolute cap, so a session that is kept alive by activity still
     * expires eventually.
     */
    private function enforceAbsoluteLifetime(): void
    {
        $maxMinutes = (int) Config::get('security.session.absolute_lifetime', 720);
        $startedAt = $_SESSION[self::STARTED_KEY] ?? null;

        if ($startedAt === null) {
            $_SESSION[self::STARTED_KEY] = time();
            return;
        }

        if (time() - (int) $startedAt > $maxMinutes * 60) {
            $this->invalidate();
            $_SESSION[self::STARTED_KEY] = time();
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $_SESSION ?? [];
    }

    /** Read and remove in one step. */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    // ------------------------------------------------------------ flash data

    /** Available on the next request only. */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION[self::FLASH_KEY]['new'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION[self::FLASH_KEY]['old'][$key] ?? $default;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY]['old'][$key]);
    }

    /** Keep this request's flash data for one more request. */
    public function reflash(): void
    {
        $old = $_SESSION[self::FLASH_KEY]['old'] ?? [];
        $_SESSION[self::FLASH_KEY]['new'] = ($_SESSION[self::FLASH_KEY]['new'] ?? []) + $old;
    }

    private function ageFlashData(): void
    {
        $_SESSION[self::FLASH_KEY]['old'] = $_SESSION[self::FLASH_KEY]['new'] ?? [];
        $_SESSION[self::FLASH_KEY]['new'] = [];
    }

    // ------------------------------------------------------------- lifecycle

    /**
     * Regenerate the session ID, keeping the data.
     *
     * Called on login and on privilege change — without it, an attacker who
     * fixed a session ID before login would still hold a valid session after.
     */
    public function regenerate(bool $deleteOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOld);
        }
    }

    /** Throw the contents away and start a fresh ID. */
    public function invalidate(): void
    {
        $_SESSION = [];
        $this->regenerate(true);
    }

    public function id(): string
    {
        return session_id() ?: '';
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            $this->started = false;
        }
    }
}
