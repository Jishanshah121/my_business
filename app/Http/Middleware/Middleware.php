<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use Closure;

interface Middleware
{
    /**
     * Handle the request, optionally short-circuiting the pipeline by
     * returning a Response instead of calling $next.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response;
}
