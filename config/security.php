<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'password' => [
        'algo'    => PASSWORD_ARGON2ID,
        'options' => ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2],
        'min_length' => 10,
    ],

    'session' => [
        'driver'            => Env::get('SESSION_DRIVER', 'file'),
        'cookie'            => Env::get('SESSION_COOKIE', 'supplykaro_session'),
        'lifetime'          => Env::int('SESSION_LIFETIME', 120),
        'absolute_lifetime' => Env::int('SESSION_ABSOLUTE_LIFETIME', 720),
        'secure'            => Env::bool('SESSION_SECURE_COOKIE', false),
        'http_only'         => true,
        'same_site'         => 'Lax',
        'path'              => dirname(__DIR__) . '/storage/cache/sessions',
    ],

    'csrf' => [
        'lifetime' => Env::int('CSRF_TOKEN_LIFETIME', 120),
        'field'    => '_token',
        'header'   => 'X-CSRF-Token',
    ],

    // Requests allowed per window (seconds) keyed by route class.
    'rate_limits' => [
        'login'          => ['max' => 5,   'window' => 300,  'by' => 'ip+account'],
        'register'       => ['max' => 5,   'window' => 3600, 'by' => 'ip'],
        'password_reset' => ['max' => 3,   'window' => 3600, 'by' => 'ip+account'],
        'coupon_apply'   => ['max' => 15,  'window' => 300,  'by' => 'session'],
        'search'         => ['max' => 60,  'window' => 60,   'by' => 'ip'],
        'quote_submit'   => ['max' => 10,  'window' => 3600, 'by' => 'user'],
        'ai'             => ['max' => 30,  'window' => 60,   'by' => 'ip'],
        'api_default'    => ['max' => 120, 'window' => 60,   'by' => 'ip'],
    ],

    'uploads' => [
        'max_bytes'          => Env::int('UPLOAD_MAX_BYTES', 5_242_880),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'avif'],
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/avif'],
        // Uploads live outside the web root and are served through a controller.
        'path'               => dirname(__DIR__) . '/storage/uploads',
        're_encode'          => true,
    ],

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'DENY',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        'Permissions-Policy'     => 'geolocation=(), microphone=(), camera=()',
    ],
];
