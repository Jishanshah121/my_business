<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Authorize;
use App\Http\Middleware\EnsureApprovedBusiness;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\Middleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StartSession;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\VerifyCsrf;
use App\Support\Config;
use App\Support\Container;
use App\Support\Logger;
use App\Support\Session\Session;
use App\Support\View;
use Closure;
use Throwable;

/**
 * Turns a Request into a Response: match a route, run the middleware pipeline,
 * call the controller, and convert anything thrown into a safe response.
 */
final class Kernel
{
    /**
     * Middleware that runs on every request, in order.
     *
     * @var list<class-string<Middleware>>
     */
    private array $global = [
        SecurityHeaders::class,
        StartSession::class,
    ];

    /**
     * Route middleware aliases. `throttle:login` and `can:products.edit` pass
     * the part after the colon to the middleware constructor.
     *
     * @var array<string,class-string<Middleware>>
     */
    private array $aliases = [
        'auth'      => Authenticate::class,
        'guest'     => RedirectIfAuthenticated::class,
        'csrf'      => VerifyCsrf::class,
        'throttle'  => ThrottleRequests::class,
        'can'       => Authorize::class,
        'verified'  => EnsureEmailIsVerified::class,
        'b2b'       => EnsureApprovedBusiness::class,
    ];

    public function __construct(
        private readonly Container $container,
        private readonly Router $router,
        private readonly Logger $logger,
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            // Global middleware wraps EVERYTHING, including routing and error
            // rendering. That matters twice over: error pages still get the
            // security headers, and a validation failure can flash to the
            // session because StartSession is still on the stack when the
            // exception is turned into a redirect.
            return $this->runPipeline($request, $this->global, function (Request $request): Response {
                try {
                    [$route, $parameters] = $this->router->match($request);
                    $request->setAttribute('route_parameters', $parameters);
                    $request->setAttribute('route_name', $route->routeName());

                    return $this->runPipeline(
                        $request,
                        $route->middlewareNames(),
                        function (Request $request) use ($route, $parameters): Response {
                            [$class, $method] = $route->action();
                            $controller = $this->container->get($class);

                            $result = $this->container->call(
                                $controller,
                                $method,
                                ['request' => $request] + $parameters
                            );

                            return $result instanceof Response ? $result : new Response((string) $result);
                        }
                    );
                } catch (Throwable $e) {
                    return $this->renderException($request, $e);
                }
            });
        } catch (Throwable $e) {
            // The global middleware itself failed — render without it.
            return $this->renderException($request, $e);
        }
    }

    /**
     * @param list<string> $middleware
     * @param Closure(Request): Response $destination
     */
    private function runPipeline(Request $request, array $middleware, Closure $destination): Response
    {
        $next = $destination;

        foreach (array_reverse($middleware) as $definition) {
            $current = $next;
            $next = function (Request $request) use ($definition, $current): Response {
                return $this->resolveMiddleware($definition)->handle($request, $current);
            };
        }

        return $next($request);
    }

    private function resolveMiddleware(string $definition): Middleware
    {
        [$alias, $argument] = array_pad(explode(':', $definition, 2), 2, null);

        $class = $this->aliases[$alias] ?? $alias;

        if (!class_exists($class)) {
            throw new \RuntimeException("Unknown middleware [{$definition}].");
        }

        $instance = $this->container->get($class);

        if ($argument !== null && method_exists($instance, 'withArgument')) {
            // Middleware are shared instances, so hand back a configured clone
            // rather than mutating the one every other route uses.
            $instance = $instance->withArgument($argument);
        }

        return $instance;
    }

    private function renderException(Request $request, Throwable $e): Response
    {
        // A failed validation is an expected outcome, not an error: send the
        // user back to the form with their input and the messages.
        if ($e instanceof ValidationException) {
            if ($request->expectsJson()) {
                return JsonResponse::error($e->getMessage(), $e->errors(), 422);
            }

            $session = $this->container->get(Session::class);
            $session->flash('errors', $e->errors());
            $session->flash('old', $e->old());
            $session->flash('error', $e->getMessage());

            $back = $request->header('Referer') ?? '/';

            return new RedirectResponse($back, 303);
        }

        $status = $e instanceof HttpException ? $e->statusCode() : 500;
        $headers = $e instanceof HttpException ? $e->headers() : [];

        if ($status >= 500) {
            $this->logger->exception($e, 'error', [
                'path'   => $request->path(),
                'method' => $request->method(),
                'ip'     => $request->ip(),
            ]);
        } else {
            $this->logger->info('HTTP ' . $status, [
                'path'    => $request->path(),
                'method'  => $request->method(),
                'message' => $e->getMessage(),
            ]);
        }

        $debug = (bool) Config::get('app.debug', false);

        $message = match (true) {
            $e instanceof HttpException && $e->getMessage() !== '' => $e->getMessage(),
            $status === 404 => 'We could not find that page.',
            $status === 403 => 'You do not have access to that.',
            $status === 419 => 'Your session expired. Please try again.',
            $status === 429 => 'Too many attempts. Please wait a moment and try again.',
            default => 'Something went wrong at our end. We have been notified.',
        };

        if ($request->expectsJson()) {
            $payload = ['message' => $message];
            if ($debug && $status >= 500) {
                $payload['debug'] = [
                    'exception' => $e::class,
                    'at'        => $e->getFile() . ':' . $e->getLine(),
                ];
            }

            return new JsonResponse(
                ['data' => null, 'meta' => (object) [], 'errors' => $payload],
                $status,
                $headers
            );
        }

        try {
            $view = $this->container->get(View::class);
            $html = $view->render('errors/error', [
                'status'    => $status,
                'message'   => $message,
                'exception' => $debug && $status >= 500 ? $e : null,
            ]);
        } catch (Throwable) {
            // The error page itself failed — fall back to plain text rather
            // than looping.
            $html = '<!doctype html><meta charset="utf-8"><title>' . $status . '</title>'
                . '<p style="font:16px/1.5 system-ui;padding:2rem">'
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        return new Response($html, $status, $headers + ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
