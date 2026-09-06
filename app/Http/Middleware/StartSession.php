<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Support\Config;
use App\Support\Session\Session;
use Closure;

final class StartSession implements Middleware
{
    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Secure flag follows the actual connection, so local HTTP still works
        // while production over HTTPS always gets it.
        $secure = (bool) Config::get('security.session.secure', false) || $request->isSecure();

        $this->session->start($secure);

        $response = $next($request);

        // Write and close before the response is sent, so the row is committed
        // even if output is slow.
        $this->session->save();

        return $response;
    }
}
