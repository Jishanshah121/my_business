<?php

declare(strict_types=1);

use App\Support\Env;

return [
    // Phase 1-7 use the MySQL driver. The AI driver arrives in Phase 8 and
    // always falls back to MySQL when the circuit breaker is open.
    'driver'   => 'mysql',
    'per_page' => 24,

    'ai_service' => [
        'enabled'    => Env::bool('AI_SERVICE_ENABLED', false),
        'url'        => Env::get('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
        'secret'     => Env::get('AI_SERVICE_SHARED_SECRET', ''),
        'timeout_ms' => Env::int('AI_SERVICE_TIMEOUT_MS', 300),
        'circuit_breaker' => [
            'threshold' => Env::int('AI_CIRCUIT_BREAKER_THRESHOLD', 5),
            'cooldown'  => Env::int('AI_CIRCUIT_BREAKER_COOLDOWN', 30),
        ],
    ],
];
