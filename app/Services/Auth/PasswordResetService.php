<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Http\Request;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use App\Services\Notification\NotificationDispatcher;
use App\Support\Config;
use App\Support\Logger;
use App\Support\RateLimiter;
use App\Support\Session\Session;

final class PasswordResetService
{
    private const TTL_MINUTES = 60;

    public function __construct(
        private readonly TokenRepository $tokens,
        private readonly UserRepository $users,
        private readonly AuthService $auth,
        private readonly NotificationDispatcher $notifications,
        private readonly RateLimiter $limiter,
        private readonly Session $session,
        private readonly Logger $logger,
    ) {
    }

    /**
     * Send a reset link.
     *
     * Returns the same result whether or not the address is registered — the
     * caller must show an identical message either way, or this page becomes
     * a way to enumerate accounts.
     *
     * @return array{0:bool,1:string} allowed, message
     */
    public function requestLink(Request $request, string $email): array
    {
        $email = strtolower(trim($email));

        if ($this->limiter->tooManyAttempts('password_reset', $email)
            || $this->limiter->tooManyAttempts('password_reset', 'ip:' . $request->ip())) {
            return [false, 'Too many reset requests. Please wait a while before trying again.'];
        }

        $this->limiter->hit('password_reset', $email);
        $this->limiter->hit('password_reset', 'ip:' . $request->ip());

        $user = $this->users->findByEmail($email);

        if ($user !== null && $user->status !== 'closed') {
            $token = bin2hex(random_bytes(32));
            $this->tokens->createPasswordReset($email, $token, self::TTL_MINUTES, $request->ipBinary());

            $url = rtrim((string) Config::get('app.url'), '/') . '/reset-password/' . $token;

            $this->notifications->email(
                event: 'user.password_reset',
                recipient: $email,
                subject: 'Reset your SupplyKaro password',
                template: 'reset-password',
                data: ['user' => $user, 'url' => $url, 'expiresMinutes' => self::TTL_MINUTES],
                userId: $user->id,
                referenceType: 'user',
                referenceId: $user->id,
            );

            $this->logger->info('Password reset requested', ['user_id' => $user->id]);
        } else {
            $this->logger->info('Password reset requested for unknown address', ['ip' => $request->ip()]);
        }

        return [true, 'If that address is registered, a reset link is on its way. Check your inbox and spam folder.'];
    }

    /** @return array<string,mixed>|null */
    public function findValidToken(string $token): ?array
    {
        return $this->tokens->findValidPasswordReset($token);
    }

    /**
     * @return array{0:bool,1:string} success, message
     */
    public function reset(string $token, string $password): array
    {
        $record = $this->tokens->findValidPasswordReset($token);

        if ($record === null) {
            return [false, 'That reset link has expired or has already been used. Request a new one.'];
        }

        $user = $this->users->findByEmail((string) $record['email']);
        if ($user === null) {
            return [false, 'That reset link is no longer valid.'];
        }

        $this->users->updatePassword($user->id, $this->auth->hashPassword($password));
        $this->tokens->consumePasswordReset((int) $record['id']);

        // Every existing session for this user is now invalid: whoever forced
        // the reset should not stay signed in anywhere.
        $destroyed = $this->session->handler()->destroyForUser($user->id);

        $this->limiter->clear('login', $user->email);
        $this->limiter->clear('password_reset', $user->email);

        $this->notifications->email(
            event: 'user.password_changed',
            recipient: $user->email,
            subject: 'Your SupplyKaro password was changed',
            template: 'password-changed',
            data: ['user' => $user],
            userId: $user->id,
            referenceType: 'user',
            referenceId: $user->id,
        );

        $this->logger->warning('Password reset completed', [
            'user_id'            => $user->id,
            'sessions_destroyed' => $destroyed,
        ]);

        return [true, 'Your password has been changed. You can sign in now.'];
    }
}
