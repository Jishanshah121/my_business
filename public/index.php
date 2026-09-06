<?php

declare(strict_types=1);

/**
 * Front controller — the only PHP file in the web root.
 *
 * Everything else (app/, config/, database/, storage/, views/) sits outside
 * the document root and is unreachable over HTTP.
 */

$root = require dirname(__DIR__) . '/bootstrap/app.php';

use App\Http\Kernel;
use App\Http\Request;
use App\Http\Router;
use App\Support\Config;
use App\Support\Container;
use App\Support\Csrf;
use App\Support\Logger;
use App\Support\Session\DatabaseSessionHandler;
use App\Support\Session\Session;
use App\Support\View;

$container = Container::getInstance();

$container->singleton(Logger::class, static fn (): Logger => new Logger(
    (string) Config::get('app.log.path'),
    (string) Config::get('app.log.level', 'warning'),
    'app'
));

$container->singleton(DatabaseSessionHandler::class, static fn (): DatabaseSessionHandler =>
    new DatabaseSessionHandler((int) Config::get('security.session.lifetime', 120)));

$container->singleton(Session::class, static fn (Container $c): Session =>
    new Session($c->get(DatabaseSessionHandler::class)));

$container->singleton(Csrf::class, static fn (Container $c): Csrf =>
    new Csrf($c->get(Session::class)));

$container->singleton(View::class, static fn (): View => new View($root . '/views'));

$container->singleton(Router::class, static function () use ($root): Router {
    $router = new Router();
    (static function (Router $router) use ($root): void {
        require $root . '/routes/web.php';
    })($router);
    $router->indexNames();

    return $router;
});

$logger = $container->get(Logger::class);

// Give the whole request one correlation id, and hand it back on the response
// so a customer-reported error can be found in the log.
$incoming = $_SERVER['HTTP_X_CORRELATION_ID'] ?? null;
if (is_string($incoming) && $incoming !== '') {
    $logger->withCorrelationId($incoming);
}

$kernel = new Kernel($container, $container->get(Router::class), $logger);

$request = Request::capture();
$response = $kernel->handle($request);
$response->header('X-Correlation-Id', $logger->correlationId());
$response->send();
