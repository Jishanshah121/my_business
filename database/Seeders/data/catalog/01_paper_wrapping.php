<?php

declare(strict_types=1);

/** DEMO / PLACEHOLDER CATALOG DATA — see README.md in this directory. */

return [
    [
        'sku_root' => 'BP-SHT', 'name' => 'Butter Paper Sheets',
        'category' => 'butter-paper', 'hsn' => '4806', 'brand' => 'supplykaro-essentials',
        'material' => 'Greaseproof paper', 'unit_type' => 'sheet',
        'keywords' => 'butter paper, butter peper, greaseproof paper, baking paper, parchment paper, wrapping paper, roti wrap',
        'short' => 'Food-grade greaseproof sheets for lining, wrapping and baking.',
        'long' => 'Silicone-free greaseproof sheets that resist oil and moisture without sticking. Used for lining bakery trays, wrapping rolls and parathas, separating cheese slices, and keeping fried snacks crisp inside a takeaway box.',
        'flags' => ['bestseller', 'recyclable'], 'tags' => ['bakery', 'takeaway', 'kraft-paper'],
        'attrs' => ['material_type' => 'paper', 'food_grade' => true, 'oil_resistant' => true, 'gsm' => 38],
        'variants' => [
            ['name' => '9 x 11 in', 'code' => '0911', 'price' => 0.32, 'mrp' => 0.50, 'size' => '9 x 11 in', 'weight' => 1.1, 'gsm' => 38, 'colour' => 'White', 'default' => true,
             'packs' => [[100, 'Pack of 100'], [500, 'Pack of 500'], [5000, 'Carton of 5,000']]],
            ['name' => '12 x 12 in', 'code' => '1212', 'price' => 0.48, 'mrp' => 0.75, 'size' => '12 x 12 in', 'weight' => 1.7, 'gsm' => 38, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [500, 'Pack of 500'], [5000, 'Carton of 5,000']]],
        ],
    ],
    [
        'sku_root' => 'BP-ROL', 'name' => 'Butter Paper Roll',
        'category' => 'butter-paper', 'hsn' => '4806', 'brand' => 'supplykaro-pro',
        'material' => 'Greaseproof paper', 'unit_type' => 'roll',
        'keywords' => 'butter paper roll, baking paper roll, parchment roll, greaseproof roll',
        'short' => 'Continuous greaseproof roll for high-volume bakery and kitchen use.',
        'long' => 'A roll works out cheaper per square metre than sheets and lets you cut exactly the length you need. Fits a standard wall dispenser.',
        'flags' => ['recyclable'], 'tags' => ['bakery', 'kraft-paper'],
        'attrs' => ['material_type' => 'paper', 'food_grade' => true, 'oil_resistant' => true],
        'variants' => [
            ['name' => '30 cm x 20 m', 'code' => '3020', 'price' => 78.00, 'mrp' => 120.00, 'size' => '30 cm x 20 m', 'weight' => 240, 'default' => true,
             'packs' => [[1, 'Single Roll'], [12, 'Box of 12 Rolls']]],
            ['name' => '45 cm x 50 m', 'code' => '4550', 'price' => 215.00, 'mrp' => 320.00, 'size' => '45 cm x 50 m', 'weight' => 890,
             'packs' => [[1, 'Single Roll'], [6, 'Box of 6 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'FW-SHT', 'name' => 'Food Wrapping Paper Sheets',
        'category' => 'butter-paper', 'hsn' => '4802', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade paper', 'unit_type' => 'sheet',
        'keywords' => 'food wrapping paper, burger wrap, sandwich wrap, roll wrap paper, kagaz wrap',
        'short' => 'Wrapping sheets for burgers, rolls and sandwiches.',
        'long' => 'Light, food-safe sheets that hold a wrap together without going soggy. Popular with quick-service counters and food stalls. Custom printing available on carton quantities.',
        'flags' => ['recyclable'], 'tags' => ['takeaway', 'food-stall', 'kraft-paper'],
        'attrs' => ['material_type' => 'paper', 'food_grade' => true, 'gsm' => 30],
        'variants' => [
            ['name' => '12 x 12 in', 'code' => '1212', 'price' => 0.30, 'mrp' => 0.48, 'size' => '12 x 12 in', 'weight' => 1.3, 'colour' => 'White',
             'packs' => [[500, 'Pack of 500'], [5000, 'Carton of 5,000']]],
        ],
    ],
    [
        'sku_root' => 'AF-ROL', 'name' => 'Aluminium Foil Roll',
        'category' => 'foil-cling-film', 'hsn' => '7607', 'brand' => 'supplykaro-essentials',
        'material' => 'Aluminium', 'unit_type' => 'roll',
        'keywords' => 'aluminium foil, silver foil, foil roll, kitchen foil, roti foil, chandi kagaz',
        'short' => 'Food-grade aluminium foil in three roll lengths.',
        'long' => 'Standard 11 micron food-grade foil. Keeps rotis warm, seals a container for delivery, and lines a tray for baking. The 72 m roll is the one commercial kitchens buy.',
        'flags' => ['bestseller', 'recyclable'], 'tags' => ['housekeeping', 'takeaway', 'aluminium'],
        'attrs' => ['material_type' => 'aluminium', 'food_grade' => true, 'freezer_safe' => true],
        'variants' => [
            ['name' => '9 m', 'code' => '009M', 'price' => 48.00, 'mrp' => 75.00, 'size' => '9 m', 'weight' => 105,
             'packs' => [[1, 'Single Roll'], [24, 'Carton of 24 Rolls']]],
            ['name' => '18 m', 'code' => '018M', 'price' => 88.00, 'mrp' => 135.00, 'size' => '18 m', 'weight' => 200, 'default' => true,
             'packs' => [[1, 'Single Roll'], [24, 'Carton of 24 Rolls']]],
            ['name' => '72 m', 'code' => '072M', 'price' => 295.00, 'mrp' => 440.00, 'size' => '72 m', 'weight' => 780,
             'packs' => [[1, 'Single Roll'], [12, 'Carton of 12 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'CF-ROL', 'name' => 'Cling Film Roll',
        'category' => 'foil-cling-film', 'hsn' => '3920', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade PVC film', 'unit_type' => 'roll',
        'keywords' => 'cling film, cling wrap, plastic wrap, food wrap film, stretch film',
        'short' => 'Food-grade cling film that clings to the rim, not to itself.',
        'long' => 'Consistent cling on steel, glass and plastic rims. The 300 m roll comes in a cutter box, which is the difference between a two-second job and a two-minute one.',
        'flags' => [], 'tags' => ['housekeeping', 'delivery'],
        'attrs' => ['material_type' => 'plastic', 'food_grade' => true, 'freezer_safe' => true],
        'variants' => [
            ['name' => '100 m', 'code' => '100M', 'price' => 95.00, 'mrp' => 145.00, 'size' => '30 cm x 100 m', 'weight' => 380, 'default' => true,
             'packs' => [[1, 'Single Roll'], [12, 'Carton of 12 Rolls']]],
            ['name' => '300 m', 'code' => '300M', 'price' => 255.00, 'mrp' => 385.00, 'size' => '30 cm x 300 m', 'weight' => 1050,
             'packs' => [[1, 'Single Roll'], [6, 'Carton of 6 Rolls']]],
        ],
    ],
];
