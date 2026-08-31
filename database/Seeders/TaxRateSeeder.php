<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * GST rates by HSN code.
 *
 * PLACEHOLDER RATES FOR DEVELOPMENT. These reflect commonly applied rates for
 * these product families, but GST classification is the seller's legal
 * responsibility — have your CA confirm every HSN and rate before you invoice
 * a real customer. The table is dated so a correction is a new row, never an
 * edit that rewrites past invoices.
 */
final class TaxRateSeeder extends Seeder
{
    public function order(): int
    {
        return 50;
    }

    public function run(): void
    {
        $validFrom = '2024-04-01';

        $rates = [
            ['4823', 'Paper cups, plates, trays and other moulded paper tableware', 18.00],
            ['4818', 'Tissue, napkins, toilet rolls, kitchen towels, facial tissue', 18.00],
            ['4819', 'Cartons, boxes and bags of paper or paperboard',              18.00],
            ['4806', 'Greaseproof and butter paper',                                12.00],
            ['4802', 'Uncoated paper sheets for food wrapping',                     12.00],
            ['4602', 'Areca leaf and other plaited vegetable-material tableware',    5.00],
            ['4419', 'Wooden and bamboo tableware and cutlery',                     12.00],
            ['3924', 'Plastic tableware, cutlery and kitchenware',                  18.00],
            ['3923', 'Plastic containers, garbage bags and packing articles',       18.00],
            ['3920', 'Cling film and other plastic film',                           18.00],
            ['7615', 'Aluminium foil containers and household articles',            18.00],
            ['7607', 'Aluminium foil rolls',                                        18.00],
            ['4015', 'Gloves of vulcanised rubber',                                 18.00],
            ['3926', 'Other plastic articles — aprons, caps, hair nets',            18.00],
            ['6307', 'Face masks and other made-up textile articles',                5.00],
            ['3808', 'Disinfectants and sanitisers',                                18.00],
            ['3401', 'Soap and cleaning preparations',                              18.00],
            ['3402', 'Surface-active cleaning agents',                              18.00],
            ['6805', 'Abrasive scrub pads',                                         18.00],
            ['5603', 'Non-woven wipes and cleaning cloth',                          12.00],
            ['996812', 'Courier and goods transport services (shipping charges)',   18.00],
        ];

        foreach ($rates as [$hsn, $description, $rate]) {
            $this->upsert('tax_rates', ['hsn_code' => $hsn, 'valid_from' => $validFrom], [
                'description' => $description,
                'gst_rate'    => $rate,
                'cess_rate'   => 0.00,
                'is_active'   => 1,
            ]);
        }

        $this->info(count($rates) . ' HSN codes — VERIFY WITH YOUR CA before invoicing');
    }
}
