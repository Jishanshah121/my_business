<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'default' => Env::get('SESSION_DRIVER', 'file') === 'redis' ? 'redis' : 'file',

    'stores' => [
        'file'  => ['path' => dirname(__DIR__) . '/storage/cache/data'],
        'redis' => [
            'host'     => Env::get('REDIS_HOST', '127.0.0.1'),
            'port'     => Env::int('REDIS_PORT', 6379),
            'password' => Env::get('REDIS_PASSWORD', ''),
            'database' => Env::int('REDIS_DB', 0),
        ],
    ],

    'ttl' => [
        'category_tree' => 3600,
        'price_rules'   => 600,
        'settings'      => 300,
        'product_card'  => 900,
    ],
];
