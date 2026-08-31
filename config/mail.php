<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'mailer'     => Env::get('MAIL_MAILER', 'smtp'),
    'host'       => Env::get('MAIL_HOST', '127.0.0.1'),
    'port'       => Env::int('MAIL_PORT', 1025),
    'username'   => Env::get('MAIL_USERNAME', ''),
    'password'   => Env::get('MAIL_PASSWORD', ''),
    'encryption' => Env::get('MAIL_ENCRYPTION', ''),
    'from'       => [
        'address' => Env::get('MAIL_FROM_ADDRESS', 'no-reply@supplykaro.test'),
        'name'    => Env::get('MAIL_FROM_NAME', 'SupplyKaro'),
    ],
];
