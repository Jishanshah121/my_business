<?php

declare(strict_types=1);

return [
    'default_provider' => 'zone_weight',

    'providers' => [
        'zone_weight' => \App\Services\Shipping\ZoneWeightRateProvider::class,
    ],

    // Disposables are light and bulky: chargeable weight is the greater of
    // actual and volumetric weight, or large orders ship at a loss.
    'volumetric_divisor' => 5000, // (L x W x H in cm) / divisor = kg

    'free_shipping_threshold' => 250000, // paise = Rs 2,500 (admin-overridable)
    'origin_pincode'          => '400001',
];
