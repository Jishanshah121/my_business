<?php

declare(strict_types=1);

/**
 * Application bootstrap. Loads the autoloader, environment and configuration,
 * then applies runtime settings. Returns the project root path.
 */

use App\Support\Config;
use App\Support\Env;

$root = dirname(__DIR__);

require $root . '/bootstrap/autoload.php';

Env::load($root . '/.env');
Config::load($root . '/config');

date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Kolkata'));

$isDebug = (bool) Config::get('app.debug', false);
$isProduction = Config::get('app.env') === 'production';

// Never render errors to the browser in production (brief §53).
ini_set('display_errors', $isDebug && !$isProduction ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $root . '/storage/logs/php-error.log');
error_reporting($isDebug ? E_ALL : E_ALL & ~E_DEPRECATED);

if ($isProduction && $isDebug) {
    // Fail loudly rather than silently shipping stack traces to customers.
    throw new RuntimeException('APP_DEBUG must be false when APP_ENV=production.');
}

return $root;
