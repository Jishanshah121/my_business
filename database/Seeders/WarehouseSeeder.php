<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Config;
use Database\Seeder;

final class WarehouseSeeder extends Seeder
{
    public function order(): int
    {
        return 55;
    }

    public function run(): void
    {
        $seller = Config::get('tax.seller');

        // Single warehouse for now. state_code here is the place of supply for
        // anything dispatched from it, so multi-warehouse later just works.
        $this->upsert('warehouses', ['code' => 'MAIN'], [
            'name'       => 'Main Warehouse — Mumbai',
            'line1'      => 'PLACEHOLDER address, replace before go-live',
            'city'       => 'Mumbai',
            'state_code' => (string) $seller['state_code'],
            'pincode'    => (string) ($seller['pincode'] ?: '400001'),
            'gstin'      => $seller['gstin'] !== '' ? $seller['gstin'] : null,
            'is_default' => 1,
            'is_active'  => 1,
        ]);

        $this->info('1 warehouse (Maharashtra, state code ' . $seller['state_code'] . ')');
    }
}
