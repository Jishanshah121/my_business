<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * PLACEHOLDER shipping zones and rates, built out from Mumbai.
 * Replace with your actual courier contract rates before go-live.
 */
final class ShippingSeeder extends Seeder
{
    public function order(): int
    {
        return 60;
    }

    public function run(): void
    {
        $zones = [
            ['LOCAL', 'Mumbai Local', 'Mumbai, Thane and Navi Mumbai', 10, [
                ['pincode_range', '400001', '400104', null],
                ['pincode_range', '400601', '400615', null],
                ['pincode_range', '410206', '410221', null],
            ], [
                ['Standard',       0,     1000, 40.00,  0.00, 1500.00],
                ['Standard',    1001,     5000, 60.00, 10.00, 1500.00],
                ['Standard',    5001,    25000, 90.00,  8.00, 1500.00],
                ['Bulk Freight',25001,     null, 250.00, 6.00, 10000.00],
            ]],
            ['MH', 'Maharashtra', 'Rest of Maharashtra', 20, [
                ['state', null, null, '27'],
            ], [
                ['Standard',       0,     1000,  60.00,  0.00, 2500.00],
                ['Standard',    1001,     5000,  90.00, 14.00, 2500.00],
                ['Standard',    5001,    25000, 140.00, 11.00, 2500.00],
                ['Bulk Freight',25001,     null, 400.00,  8.00, 15000.00],
            ]],
            ['WEST', 'West & Central India', 'Gujarat, MP, Goa, Chhattisgarh, Rajasthan', 30, [
                ['state', null, null, '24'], ['state', null, null, '23'],
                ['state', null, null, '30'], ['state', null, null, '22'],
                ['state', null, null, '08'],
            ], [
                ['Standard',       0,     1000,  90.00,  0.00, 3500.00],
                ['Standard',    1001,     5000, 130.00, 20.00, 3500.00],
                ['Standard',    5001,    25000, 210.00, 16.00, 3500.00],
                ['Bulk Freight',25001,     null, 650.00, 12.00, 20000.00],
            ]],
            ['REST', 'Rest of India', 'All other serviceable states', 90, [
                ['all', null, null, null],
            ], [
                ['Standard',       0,     1000, 120.00,  0.00, 5000.00],
                ['Standard',    1001,     5000, 180.00, 28.00, 5000.00],
                ['Standard',    5001,    25000, 300.00, 22.00, 5000.00],
                ['Bulk Freight',25001,     null, 900.00, 16.00, 30000.00],
            ]],
        ];

        $rateCount = 0;
        foreach ($zones as [$code, $name, $description, $priority, $matches, $rates]) {
            $zoneId = $this->upsert('shipping_zones', ['code' => $code], [
                'name'        => $name,
                'description' => $description,
                'priority'    => $priority,
                'is_active'   => 1,
            ]);

            $this->pdo->prepare('DELETE FROM `zone_pincodes` WHERE `shipping_zone_id` = ?')->execute([$zoneId]);
            foreach ($matches as [$matchType, $from, $to, $state]) {
                $this->insert('zone_pincodes', [
                    'shipping_zone_id' => $zoneId,
                    'match_type'       => $matchType,
                    'pincode_from'     => $from,
                    'pincode_to'       => $to,
                    'state_code'       => $state,
                    'is_serviceable'   => 1,
                    'cod_available'    => $code === 'REST' ? 0 : 1,
                    'eta_days_min'     => match ($code) { 'LOCAL' => 1, 'MH' => 2, 'WEST' => 3, default => 4 },
                    'eta_days_max'     => match ($code) { 'LOCAL' => 2, 'MH' => 4, 'WEST' => 6, default => 8 },
                ]);
            }

            $this->pdo->prepare('DELETE FROM `shipping_rates` WHERE `shipping_zone_id` = ?')->execute([$zoneId]);
            foreach ($rates as $i => [$rateName, $fromG, $toG, $base, $perKg, $freeAbove]) {
                $this->insert('shipping_rates', [
                    'shipping_zone_id' => $zoneId,
                    'name'             => $rateName,
                    'weight_from_g'    => $fromG,
                    'weight_to_g'      => $toG,
                    'base_rate'        => $base,
                    'per_kg_rate'      => $perKg,
                    'free_above_value' => $freeAbove,
                    'min_charge'       => 0.00,
                    'gst_rate'         => 18.00,
                    'hsn_code'         => '996812',
                    'sort_order'       => $i * 10,
                    'is_active'        => 1,
                ]);
                $rateCount++;
            }
        }

        $this->info(count($zones) . " shipping zones, {$rateCount} weight-slab rates (PLACEHOLDER)");
    }
}
