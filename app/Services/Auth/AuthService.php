<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Http\Request;
use App\Models\User;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\UserRepository;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Logger;
use App\Support\RateLimiter;
use App\Support\Session\Session;

/**
 * Authentication state and the login/logout lifecycle.
 */
final class AuthService
{
    private const SESSION_KEY = '_auth_user_id';
    private const PASSWORD_STAMP = '_auth_password_stamp';

    private ?User $user = null;

    private bool $resolved = false;

    public function __construct(
        private readonly UserRepository $users,
        private readonly LoginAttemptRepository $attempts,
        private readonly Session $session,
        private readonly RateLimiter $limiter,
        private readonly Csrf $csrf,
        private readonly Logger $logger,
    ) {
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function id(): ?int
    {
        return $this->user()?->id;
    }

    /** The signed-in user, loaded once per request. */
    public function user(): ?User
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        $id = $this->session->get(self::SESSION_KEY);
        if (!is_int($id) && !is_numeric($id)) {
            return $this->user = null;
        }

        $user = $this->users->find((int) $id);

        // A suspended or deleted account must not keep a live session.
        // 'pending' (registered, email not yet confirmed) is a valid state.
        if ($user === null || !$user->canAuthenticate()) {
            $this->forgetSession();

            return $this->user = null;
        }

        // Password changed elsewhere? The stamp no longer matches, so this
        // session is stale and must not continue.
        $stamp = $this->session->get(self::PASSWORD_STAMP);
        if (is_string($stamp) && !hash_equals($this->passwordStamp($user), $stamp)) {
            $this->forgetSession();

            return $this->user = null;
        }

        $this->session->handler()->setUserId($user->id);

        return $this->user = $user;
    }

    /**
     * @return array{0:bool,1:?string} success, failure message
     */
    public function attempt(Request $request, string $identifier, string $password, bool $remember = false): array
    {
        $ip = $request->ip();
        $identifier = trim($identifier);

        // Two independent limits: one per account (stops targeting one user)
        // and one per IP (stops spraying many accounts from one source).
        if ($this->limiter->tooManyAttempts('login', $identifier)
            || $this->limiter->tooManyAttempts('login', 'ip:' . $ip)) {
            $seconds = max(
                $this->limiter->availableIn('login', $identifier),
                $this->limiter->availableIn('login', 'ip:' . $ip)
            );

            $this->logger->warning('Login blocked by rate limit', ['identifier' => $identifier, 'ip' => $ip]);

            return [false, 'Too many sign-in attempts. Try again in ' . $this->humanSeconds($seconds) . '.'];
        }

        $this->limiter->hit('login', $identifier);
        $this->limiter->hit('login', 'ip:' . $ip);

        $user = $this->users->findByIdentifier($identifier);

        // Hash even when the account does not exist, so response time does not
        // reveal whether an email is registered.
        $hash = $user?->passwordHash ?? '$argon2id$v=19$m=65536,t=4,p=2$AAAAAAAAAAAAAAAAAAAAAA$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $passwordOk = password_verify($password, $hash);

        if ($user === null || !$passwordOk) {
            $this->attempts->record($identifier, $request->ipBinary(), $request->userAgent(), false);
            $this->logger->info('Failed sign-in', ['identifier' => $identifier, 'ip' => $ip]);

            // Deliberately identical whether the account exists or the password
            // was wrong — anything else is an account-enumeration oracle.
            return [false, 'Those details do not match our records.'];
        }

        if (!$user->canAuthenticate()) {
            $this->attempts->record($identifier, $request->ipBinary(), $request->userAgent(), false);

            return [false, match ($user->status) {
                'suspended' => 'This account is suspended. Contact support@supplykaro.test.',
                'closed'    => 'This account has been closed.',
                default     => 'This account is not available.',
            }];
        }

        // Upgrade the hash if the cost parameters have moved on since signup.
        if (password_needs_rehash($user->passwordHash, Config::get('security.password.algo'), Config::get('security.password.options'))) {
            $this->users->updatePassword($user->id, $this->hashPassword($password));
        }

        $this->login($user);

        $this->attempts->record($identifier, $request->ipBinary(), $request->userAgent(), true);
        $this->users->recordLogin($user->id, $request->ipBinary());
        $this->limiter->clear('login', $identifier);
        $this->limiter->clear('login', 'ip:' . $ip);

        $this->logger->info('Signed in', ['user_id' => $user->id, 'ip' => $ip]);

        return [true, null];
    }

    /** Establish the session for a user. Also used straight after registration. */
    public function login(User $user): void
    {
        // Regenerate BEFORE writing the id, so a fixed session ID cannot be
        // promoted to an authenticated one.
        $this->session->regenerate(true);
        $this->csrf->rotate();

        $this->session->put(self::SESSION_KEY, $user->id);
        $this->session->put(self::PASSWORD_STAMP, $this->passwordStamp($user));
        $this->session->handler()->setUserId($user->id);

        $this->user = $user;
        $this->resolved = true;
    }

    public function logout(): void
    {
        $userId = $this->id();

        $this->forgetSession();
        $this->session->invalidate();
        $this->csrf->rotate();

        if ($userId !== null) {
            $this->logger->info('Signed out', ['user_id' => $userId]);
        }
    }

    /** Sign the user out of every other device. */
    public function logoutOtherSessions(): int
    {
        $userId = $this->id();
        if ($userId === null) {
            return 0;
        }

        return $this->session->handler()->destroyForUser($userId, $this->session->id());
    }

    public function hashPassword(string $password): string
    {
        return password_hash(
            $password,
            Config::get('security.password.algo'),
            Config::get('security.password.options')
        );
    }

    /**
     * A fingerprint of the password hash, kept in the session.
     *
     * Changing the password changes the stamp, which invalidates every other
     * session without needing to reach into the session store.
     */
    private function passwordStamp(User $user): string
    {
        return hash_hmac('sha256', $user->passwordHash, (string) Config::get('app.key', ''));
    }

    private function forgetSession(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->session->forget(self::PASSWORD_STAMP);
        $this->session->handler()->setUserId(null);
        $this->user = null;
    }

    private function humanSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds === 1 ? '' : 's');
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
    }
}
