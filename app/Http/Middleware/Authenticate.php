<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use App\Http\JsonResponse;
use App\Http\RedirectResponse;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Support\Session\Session;
use Closure;

final class Authenticate implements Middleware
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return JsonResponse::error('Please sign in to continue.', [], 401);
        }

        // Remember where they were headed so sign-in can return them there.
        if ($request->isMethod('GET')) {
            $this->session->put('_intended_url', $request->uri());
        }

        $this->session->flash('info', 'Please sign in to continue.');

        return new RedirectResponse('/login', 302);
    }
}
