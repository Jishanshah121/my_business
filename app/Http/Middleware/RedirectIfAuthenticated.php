<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\RedirectResponse;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use Closure;

/** Keeps signed-in users off the login and registration pages. */
final class RedirectIfAuthenticated implements Middleware
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            return new RedirectResponse('/account', 302);
        }

        return $next($request);
    }
}
