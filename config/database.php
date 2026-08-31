<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'default' => 'mysql',

    'connections' => [
        // Primary read/write connection. PHP is the source of truth.
        'mysql' => [
            'host'      => Env::get('DB_HOST', '127.0.0.1'),
            'port'      => Env::int('DB_PORT', 3306),
            'database'  => Env::get('DB_DATABASE', 'supplykaro'),
            'username'  => Env::get('DB_USERNAME', 'root'),
            'password'  => Env::get('DB_PASSWORD', ''),
            'charset'   => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATION', 'utf8mb4_0900_ai_ci'),
        ],

        // Read-only credentials handed to the Python AI service. Kept here so
        // `db:grants` can generate the GRANT statements; PHP never uses it.
        'readonly' => [
            'host'      => Env::get('DB_HOST', '127.0.0.1'),
            'port'      => Env::int('DB_PORT', 3306),
            'database'  => Env::get('DB_DATABASE', 'supplykaro'),
            'username'  => Env::get('DB_READONLY_USERNAME', 'supplykaro_ai'),
            'password'  => Env::get('DB_READONLY_PASSWORD', ''),
            'charset'   => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATION', 'utf8mb4_0900_ai_ci'),
        ],
    ],

    'migrations' => [
        'path'  => dirname(__DIR__) . '/database/migrations',
        'table' => 'migrations',
    ],

    'seeders' => [
        'path' => dirname(__DIR__) . '/database/Seeders',
    ],
];
