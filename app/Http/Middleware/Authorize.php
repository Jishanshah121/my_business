<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use App\Http\JsonResponse;
use App\Http\RedirectResponse;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Logger;
use App\Support\Session\Session;
use Closure;

/**
 * Permission check: `can:products.edit`.
 *
 * Always a permission code, never a role name — so an admin can reshape roles
 * without a deploy.
 */
final class Authorize implements Middleware
{
    private ?string $permission = null;

    public function __construct(
        private readonly Gate $gate,
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Logger $logger,
    ) {
    }

    public function withArgument(string $permission): self
    {
        $clone = clone $this;
        $clone->permission = $permission;

        return $clone;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->permission === null) {
            throw new \RuntimeException('Authorize middleware used without a permission, e.g. can:products.edit');
        }

        if (!$this->auth->check()) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Please sign in to continue.', [], 401);
            }
            $this->session->put('_intended_url', $request->uri());

            return new RedirectResponse('/login', 302);
        }

        if ($this->gate->denies($this->permission)) {
            $this->logger->warning('Authorisation denied', [
                'user_id'    => $this->auth->id(),
                'permission' => $this->permission,
                'path'       => $request->path(),
            ]);

            throw new HttpException(403, 'You do not have permission to do that.');
        }

        return $next($request);
    }
}
