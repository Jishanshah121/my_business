<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use App\Services\Notification\NotificationDispatcher;
use App\Support\Config;
use App\Support\Logger;
use App\Support\RateLimiter;

final class EmailVerificationService
{
    private const TTL_MINUTES = 1440; // 24 hours

    public function __construct(
        private readonly TokenRepository $tokens,
        private readonly UserRepository $users,
        private readonly NotificationDispatcher $notifications,
        private readonly RateLimiter $limiter,
        private readonly Logger $logger,
    ) {
    }

    public function send(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if (!$this->limiter->hit('password_reset', 'verify:' . $user->email)) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $this->tokens->createEmailVerification($user->id, $token, self::TTL_MINUTES);

        $url = rtrim((string) Config::get('app.url'), '/') . '/verify-email/' . $token;

        $this->notifications->email(
            event: 'user.email_verification',
            recipient: $user->email,
            subject: 'Confirm your email address',
            template: 'verify-email',
            data: ['user' => $user, 'url' => $url, 'expiresHours' => (int) (self::TTL_MINUTES / 60)],
            userId: $user->id,
            referenceType: 'user',
            referenceId: $user->id,
        );

        return true;
    }

    /**
     * @return array{0:bool,1:string} verified, message
     */
    public function verify(string $token): array
    {
        $record = $this->tokens->findValidEmailVerification($token);

        if ($record === null) {
            return [false, 'That confirmation link has expired or has already been used. Request a new one below.'];
        }

        $userId = (int) $record['user_id'];

        $this->tokens->consumeEmailVerification((int) $record['id']);
        $this->users->markEmailVerified($userId);

        $this->logger->info('Email verified', ['user_id' => $userId]);

        return [true, 'Your email address is confirmed.'];
    }
}
