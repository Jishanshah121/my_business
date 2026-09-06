<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Support\RateLimiter;
use Closure;

/** Route-level throttling: `throttle:register`. Limits live in config. */
final class ThrottleRequests implements Middleware
{
    private string $action = 'api_default';

    public function __construct(
        private readonly RateLimiter $limiter,
        private readonly AuthService $auth,
    ) {
    }

    public function withArgument(string $action): self
    {
        $clone = clone $this;
        $clone->action = $action;

        return $clone;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Signed-in users are limited per account, guests per IP.
        //
        // The key is namespaced by route. Services such as AuthService and
        // PasswordResetService keep their own counters under the same action
        // name, and without this prefix both layers would increment one shared
        // counter — making a "5 attempts" limit fire on the third try.
        $subject = $this->auth->check()
            ? 'user:' . $this->auth->id()
            : 'ip:' . $request->ip();

        $identifier = 'route:' . $request->path() . '|' . $subject;

        if (!$this->limiter->hit($this->action, $identifier)) {
            $retryAfter = $this->limiter->availableIn($this->action, $identifier);

            throw new HttpException(
                429,
                'Too many requests. Please wait ' . max(1, $retryAfter) . ' seconds and try again.',
                ['Retry-After' => (string) max(1, $retryAfter)]
            );
        }

        return $next($request);
    }
}
