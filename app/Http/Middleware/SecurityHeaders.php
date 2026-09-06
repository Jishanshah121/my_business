<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Support\Config;
use Closure;

/**
 * Baseline response headers, including a CSP.
 *
 * The policy is deliberately strict: no inline scripts, no external script or
 * frame sources. If a future page needs an inline script it gets a nonce,
 * not a relaxed policy.
 */
final class SecurityHeaders implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ((array) Config::get('security.headers', []) as $name => $value) {
            $response->header((string) $name, (string) $value);
        }

        $response->header('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            // Inline styles are still permitted: server-rendered templates set
            // a handful of style attributes. Tighten to a nonce when the
            // Phase 3 asset pipeline lands.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]));

        if ($request->isSecure()) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
