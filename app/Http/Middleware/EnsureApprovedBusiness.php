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
 * Gates B2B-only surfaces: the business dashboard, quotes and credit terms.
 *
 * A pending business is a customer with a working account — they are sent to
 * their profile with an explanation, not shown a 403.
 */
final class EnsureApprovedBusiness implements Middleware
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();

        if ($user !== null && $user->hasApprovedBusiness()) {
            return $next($request);
        }

        $profile = $user?->businessProfile();

        $message = match (true) {
            $profile === null        => 'Register your business to see trade pricing and request quotes.',
            $profile->isPending()    => 'Your business account is still being reviewed. We usually approve within one working day.',
            $profile->isRejected()   => 'Your business registration was not approved. ' . ($profile->rejectionReason ?? ''),
            default                  => 'Your business account is not active.',
        };

        if ($request->expectsJson()) {
            return JsonResponse::error($message, [], 403);
        }

        $this->session->flash('info', $message);

        return new RedirectResponse($profile === null ? '/register/business' : '/account/business', 302);
    }
}
