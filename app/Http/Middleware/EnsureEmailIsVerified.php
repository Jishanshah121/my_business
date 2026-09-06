<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\JsonResponse;
use App\Http\RedirectResponse;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Support\Session\Session;
use Closure;

/**
 * Guards actions that need a confirmed address.
 *
 * Applied narrowly. Browsing and buying do not require verification — pushing
 * an unverified customer through a confirmation wall before they can order
 * costs more than it protects.
 */
final class EnsureEmailIsVerified implements Middleware
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();

        if ($user !== null && $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return JsonResponse::error('Please confirm your email address first.', [], 403);
        }

        $this->session->flash('warning', 'Please confirm your email address to continue.');

        return new RedirectResponse('/email/verify', 302);
    }
}
