<?php

declare(strict_types=1);

use App\Support\Env;

/**
 * Bootstrap tax configuration.
 *
 * These are DEFAULTS ONLY. At runtime the application reads the `settings`
 * table first and falls back here, so an admin can change the seller GSTIN,
 * state or invoice series without a deploy or a schema change (ADR-004).
 */
return [
    'enabled' => Env::bool('GST_ENABLED', true),

    'seller' => [
        'legal_name' => Env::get('SELLER_LEGAL_NAME', 'Jishan Shah'),
        'trade_name' => Env::get('SELLER_TRADE_NAME', 'SupplyKaro'),
        'gstin'      => Env::get('SELLER_GSTIN', ''),
        'pan'        => Env::get('SELLER_PAN', ''),
        'state_code' => (string) Env::get('SELLER_STATE_CODE', '27'),
        'state_name' => Env::get('SELLER_STATE_NAME', 'Maharashtra'),
        'pincode'    => (string) Env::get('SELLER_PINCODE', ''),
    ],

    // Storage convention: catalog prices are stored EXCLUSIVE of tax.
    // Display inclusivity is decided per customer group, not here.
    'prices_include_tax' => Env::bool('GST_PRICES_INCLUDE_TAX', false),

    'invoice' => [
        'prefix'         => Env::get('INVOICE_PREFIX', 'INV'),
        'fy_start_month' => Env::int('INVOICE_FY_START_MONTH', 4),
        'padding'        => 5,
    ],

    // Rounding is half-up, applied per line, then summed.
    'rounding' => [
        'mode'            => 'half_up',
        'line_precision'  => 2,
        'order_round_off' => true,
    ],

    // GST state codes, used to validate GSTINs and resolve place of supply.
    'state_codes' => [
        '01' => 'Jammu and Kashmir', '02' => 'Himachal Pradesh', '03' => 'Punjab',
        '04' => 'Chandigarh', '05' => 'Uttarakhand', '06' => 'Haryana',
        '07' => 'Delhi', '08' => 'Rajasthan', '09' => 'Uttar Pradesh',
        '10' => 'Bihar', '11' => 'Sikkim', '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland', '14' => 'Manipur', '15' => 'Mizoram',
        '16' => 'Tripura', '17' => 'Meghalaya', '18' => 'Assam',
        '19' => 'West Bengal', '20' => 'Jharkhand', '21' => 'Odisha',
        '22' => 'Chhattisgarh', '23' => 'Madhya Pradesh', '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra', '29' => 'Karnataka', '30' => 'Goa',
        '31' => 'Lakshadweep', '32' => 'Kerala', '33' => 'Tamil Nadu',
        '34' => 'Puducherry', '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana', '37' => 'Andhra Pradesh', '38' => 'Ladakh',
        '97' => 'Other Territory',
    ],
];
