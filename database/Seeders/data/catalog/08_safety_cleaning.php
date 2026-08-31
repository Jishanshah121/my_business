<?php

declare(strict_types=1);

/**
 * DEMO / PLACEHOLDER CATALOG DATA — see database/Seeders/data/catalog/README.md
 * Prices are realistic Indian-market placeholders, quoted EXCLUSIVE of GST.
 * Every row seeded from this file carries is_demo_data = 1 and can be removed
 * with:  php bin/console db:wipe-demo
 */

return [
        // ============================ GLOVES & SAFETY ==========================
    [
        'sku_root' => 'GL-NIT', 'name' => 'Nitrile Examination Gloves',
        'category' => 'gloves-safety', 'hsn' => '4015', 'brand' => 'supplykaro-pro',
        'material' => 'Nitrile', 'unit_type' => 'piece',
        'keywords' => 'gloves, nitrile gloves, kitchen gloves, disposable gloves, hand gloves, food gloves',
        'short' => 'Powder-free nitrile — no latex allergy risk.',
        'long' => 'Powder-free and latex-free, textured at the fingertips for grip when wet. Standard for food handling where a latex allergy in staff or customers is a concern.',
        'flags' => ['bestseller'], 'tags' => ['housekeeping', 'dine-in'],
        'attrs' => ['food_grade' => true],
        'variants' => [
            ['name' => 'Small', 'code' => 'S', 'price' => 2.60, 'mrp' => 4.00, 'weight' => 3.6, 'colour' => 'Natural',
             'packs' => [[100, 'Box of 100'], [1000, 'Carton of 1,000']]],
            ['name' => 'Medium', 'code' => 'M', 'price' => 2.60, 'mrp' => 4.00, 'weight' => 4.0, 'colour' => 'Natural', 'default' => true,
             'packs' => [[100, 'Box of 100'], [1000, 'Carton of 1,000']]],
            ['name' => 'Large', 'code' => 'L', 'price' => 2.75, 'mrp' => 4.20, 'weight' => 4.4, 'colour' => 'Natural',
             'packs' => [[100, 'Box of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'SF-CAP', 'name' => 'Disposable Chef Cap and Hair Net',
        'category' => 'gloves-safety', 'hsn' => '3926', 'brand' => 'supplykaro-essentials',
        'material' => 'Non-woven fabric', 'unit_type' => 'piece',
        'keywords' => 'chef cap, hair net, disposable cap, kitchen cap, bouffant cap, hairnet',
        'short' => 'Bouffant cap with an elasticated band.',
        'long' => 'Breathable non-woven with an elastic band that holds without pinching over a full shift. FSSAI kitchens need these on every head.',
        'flags' => [], 'tags' => ['housekeeping'],
        'attrs' => ['material_type' => 'non-woven'],
        'variants' => [
            ['name' => 'Universal', 'code' => 'UNI', 'price' => 0.95, 'mrp' => 1.55, 'weight' => 2.8, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'SF-APR', 'name' => 'Disposable Apron',
        'category' => 'gloves-safety', 'hsn' => '3926', 'brand' => 'supplykaro-essentials',
        'material' => 'HDPE film', 'unit_type' => 'piece',
        'keywords' => 'disposable apron, plastic apron, kitchen apron, waterproof apron',
        'short' => 'Waterproof film apron for wet prep areas.',
        'long' => 'Full-length film apron with neck loop and waist ties. Used in dishwashing, fish and meat prep and anywhere a cloth apron would soak through.',
        'flags' => [], 'tags' => ['housekeeping'],
        'attrs' => [],
        'variants' => [
            ['name' => 'Universal', 'code' => 'UNI', 'price' => 4.20, 'mrp' => 6.50, 'weight' => 14.0, 'colour' => 'White',
             'packs' => [[50, 'Pack of 50'], [500, 'Carton of 500']]],
        ],
    ],
    [
        'sku_root' => 'SF-MSK', 'name' => '3 Ply Face Mask',
        'category' => 'gloves-safety', 'hsn' => '6307', 'brand' => 'supplykaro-essentials',
        'material' => 'Non-woven fabric', 'unit_type' => 'piece',
        'keywords' => 'face mask, 3 ply mask, surgical mask, disposable mask, kitchen mask',
        'short' => 'Three-ply mask with a nose clip and ear loops.',
        'long' => 'Standard three-layer construction with a moulded nose clip. Kept on hand in kitchens for prep and packing.',
        'flags' => [], 'tags' => ['housekeeping'],
        'attrs' => ['material_type' => 'non-woven', 'ply' => 3],
        'variants' => [
            ['name' => '3 Ply', 'code' => '3PLY', 'price' => 1.10, 'mrp' => 1.80, 'weight' => 3.1, 'ply' => 3, 'colour' => 'Black',
             'packs' => [[50, 'Box of 50'], [1000, 'Carton of 1,000']]],
        ],
    ],
];
