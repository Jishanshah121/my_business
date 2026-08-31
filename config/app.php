<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name'      => Env::get('APP_NAME', 'SupplyKaro'),
    'env'       => Env::get('APP_ENV', 'production'),
    'debug'     => Env::bool('APP_DEBUG', false),
    'url'       => Env::get('APP_URL', 'http://localhost:8000'),
    'key'       => Env::get('APP_KEY', ''),
    'timezone'  => Env::get('APP_TIMEZONE', 'Asia/Kolkata'),
    'locale'    => Env::get('APP_LOCALE', 'en_IN'),
    'currency'  => [
        'code'   => 'INR',
        'symbol' => '₹',
        // Money is held as integer paise everywhere in the application.
        'minor_units' => 100,
    ],
    'log' => [
        'channel' => Env::get('LOG_CHANNEL', 'daily'),
        'level'   => Env::get('LOG_LEVEL', 'warning'),
        'path'    => dirname(__DIR__) . '/storage/logs',
    ],
];
