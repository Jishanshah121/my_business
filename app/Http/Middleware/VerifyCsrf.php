<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Logger;
use Closure;

/**
 * CSRF protection on every state-changing request.
 *
 * Read methods are exempt because they must not change state — if a GET route
 * ever does, that route is the bug, not this exemption.
 */
final class VerifyCsrf implements Middleware
{
    private const READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private readonly Csrf $csrf,
        private readonly Logger $logger,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::READ_METHODS, true)) {
            return $next($request);
        }

        $field = (string) Config::get('security.csrf.field', '_token');
        $header = (string) Config::get('security.csrf.header', 'X-CSRF-Token');

        $token = $request->input($field);
        if (!is_string($token) || $token === '') {
            $token = $request->header($header);
        }

        if (!$this->csrf->verify(is_string($token) ? $token : null)) {
            $this->logger->warning('CSRF token mismatch', [
                'path'   => $request->path(),
                'method' => $request->method(),
                'ip'     => $request->ip(),
            ]);

            // 419 rather than 403: this is nearly always an expired session in
            // a tab left open, and the message should say so.
            throw new HttpException(419, 'Your session expired. Please reload the page and try again.');
        }

        return $next($request);
    }
}
