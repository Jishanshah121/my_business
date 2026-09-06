<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Session\Session;

/**
 * Per-session CSRF token.
 *
 * One token per session rather than per form: rotating per form breaks the
 * back button and multiple tabs, and buys nothing against the threat this
 * defends (a third-party site submitting a form on the user's behalf).
 * The token is rotated on login and logout with the session ID.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->put(self::KEY, $token);
        }

        return $token;
    }

    public function rotate(): void
    {
        $this->session->put(self::KEY, bin2hex(random_bytes(32)));
    }

    /** Timing-safe comparison. Never use == here. */
    public function verify(?string $candidate): bool
    {
        if (!is_string($candidate) || $candidate === '') {
            return false;
        }

        $token = $this->session->get(self::KEY);

        return is_string($token) && $token !== '' && hash_equals($token, $candidate);
    }

    /** Hidden input for forms. */
    public function field(): string
    {
        $name = (string) Config::get('security.csrf.field', '_token');

        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->token(), ENT_QUOTES, 'UTF-8')
        );
    }
}
