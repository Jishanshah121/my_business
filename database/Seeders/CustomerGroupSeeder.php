<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

final class CustomerGroupSeeder extends Seeder
{
    public function order(): int
    {
        return 30;
    }

    public function run(): void
    {
        // prices_include_tax is DISPLAY ONLY. Storage is always exclusive.
        // B2C expects an MRP-style inclusive price; B2B expects ex-GST plus a
        // tax line on the invoice.
        $groups = [
            ['retail',      'Retail (B2C)',      'Individual customers and households', 0, 1, 1,  0.00,     0.00, 0],
            ['b2b_standard','Business Standard', 'Approved business accounts',          1, 0, 0,  0.00,  2000.00, 1],
            ['b2b_gold',    'Business Gold',     'High-volume accounts on negotiated rates', 1, 0, 0, 3.00, 5000.00, 1],
            ['distributor', 'Distributor',       'Resellers and sub-distributors',      1, 0, 0,  7.00, 25000.00, 1],
        ];

        foreach ($groups as $i => [$code, $name, $desc, $isB2b, $isDefault, $incTax, $discount, $minOrder, $approval]) {
            $this->upsert('customer_groups', ['code' => $code], [
                'name'                 => $name,
                'description'          => $desc,
                'is_b2b'               => $isB2b,
                'is_default'           => $isDefault,
                'prices_include_tax'   => $incTax,
                'default_discount_pct' => $discount,
                'min_order_value'      => $minOrder,
                'requires_approval'    => $approval,
                'sort_order'           => $i * 10,
                'is_active'            => 1,
            ]);
        }

        $this->info(count($groups) . ' customer groups (1 B2C, 3 B2B)');
    }
}
