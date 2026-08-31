<?php

declare(strict_types=1);

use App\Support\Env;

/**
 * Gateway registry (ADR-005). Adding a provider means adding a class that
 * implements PaymentGateway and one row here — nothing else changes.
 */
return [
    'default' => Env::get('PAYMENT_DEFAULT_GATEWAY', 'razorpay'),

    'gateways' => [
        'razorpay' => [
            'class'          => \App\Services\Payment\RazorpayGateway::class,
            'key_id'         => Env::get('RAZORPAY_KEY_ID', ''),
            'key_secret'     => Env::get('RAZORPAY_KEY_SECRET', ''),
            'webhook_secret' => Env::get('RAZORPAY_WEBHOOK_SECRET', ''),
            'enabled'        => Env::get('RAZORPAY_KEY_ID', '') !== '',
        ],
        'cod' => [
            'class'       => \App\Services\Payment\CodGateway::class,
            'enabled'     => true,
            'max_order'   => 25000_00, // paise
            'b2b_allowed' => false,
        ],
        'bank_transfer' => [
            'class'   => \App\Services\Payment\BankTransferGateway::class,
            'enabled' => true,
            'b2b_only' => true,
        ],
        'credit_terms' => [
            'class'    => \App\Services\Payment\CreditTermsGateway::class,
            'enabled'  => true,
            'b2b_only' => true,
            'requires_approval' => true,
        ],
    ],
];
