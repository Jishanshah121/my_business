<?php

declare(strict_types=1);

/**
 * DEMO / PLACEHOLDER CATALOG DATA — see database/Seeders/data/catalog/README.md
 * Prices are realistic Indian-market placeholders, quoted EXCLUSIVE of GST.
 * Every row seeded from this file carries is_demo_data = 1 and can be removed
 * with:  php bin/console db:wipe-demo
 */

return [
        // =============================== CUTLERY ===============================
    [
        'sku_root' => 'CT-SPN', 'name' => 'Disposable Plastic Spoon',
        'category' => 'spoons', 'hsn' => '3924', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade polypropylene', 'unit_type' => 'piece',
        'keywords' => 'spoon, plastic spoon, disposable spoon, chamach, chammach, tea spoon, meal spoon, serving spoon',
        'short' => 'The everyday disposable spoon, in three working sizes.',
        'long' => 'Food-grade polypropylene with enough thickness in the neck that it does not snap in thick curd or ice cream. Tea for stirring, Medium for meals, Serving for the buffet line.',
        'flags' => ['bestseller', 'recyclable'], 'tags' => ['catering', 'party', 'takeaway', 'best-value'],
        'attrs' => ['material_type' => 'plastic', 'food_grade' => true, 'recyclable' => true],
        'variants' => [
            ['name' => 'Tea Spoon', 'code' => 'TEA', 'price' => 0.22, 'mrp' => 0.35, 'length' => 108, 'weight' => 0.9, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
            ['name' => 'Medium Spoon', 'code' => 'MED', 'price' => 0.34, 'mrp' => 0.55, 'length' => 140, 'weight' => 1.6, 'colour' => 'White', 'default' => true,
             'packs' => [[50, 'Pack of 50'], [100, 'Pack of 100'], [1000, 'Carton of 1,000'], [5000, 'Carton of 5,000']]],
            ['name' => 'Serving Spoon', 'code' => 'SRV', 'price' => 0.85, 'mrp' => 1.30, 'length' => 200, 'weight' => 4.2, 'colour' => 'White',
             'packs' => [[50, 'Pack of 50'], [500, 'Carton of 500']]],
        ],
    ],
    [
        'sku_root' => 'CT-FRK', 'name' => 'Disposable Plastic Fork Medium',
        'category' => 'forks-knives', 'hsn' => '3924', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade polypropylene', 'unit_type' => 'piece',
        'keywords' => 'fork, plastic fork, disposable fork, kanta',
        'short' => 'Reinforced tines that survive a paneer tikka.',
        'long' => 'Thicker at the base of the tines than the usual bulk fork, which is where cheap ones break.',
        'flags' => ['recyclable'], 'tags' => ['catering', 'party', 'takeaway'],
        'attrs' => ['material_type' => 'plastic', 'food_grade' => true],
        'variants' => [
            ['name' => 'Medium', 'code' => 'MED', 'price' => 0.36, 'mrp' => 0.58, 'length' => 140, 'weight' => 1.7, 'colour' => 'White',
             'packs' => [[50, 'Pack of 50'], [100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'CT-KNF', 'name' => 'Disposable Plastic Knife Medium',
        'category' => 'forks-knives', 'hsn' => '3924', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade polypropylene', 'unit_type' => 'piece',
        'keywords' => 'knife, plastic knife, disposable knife, chaku',
        'short' => 'Serrated edge that actually cuts through a cutlet.',
        'long' => 'Fine serration along the full edge and a stiff spine, so it cuts rather than tears.',
        'flags' => ['recyclable'], 'tags' => ['catering', 'party'],
        'attrs' => ['material_type' => 'plastic', 'food_grade' => true],
        'variants' => [
            ['name' => 'Medium', 'code' => 'MED', 'price' => 0.38, 'mrp' => 0.60, 'length' => 145, 'weight' => 1.8, 'colour' => 'White',
             'packs' => [[50, 'Pack of 50'], [100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'WD-SPN', 'name' => 'Wooden Spoon 160 mm',
        'category' => 'wooden-cutlery', 'hsn' => '4419', 'brand' => 'supplykaro-earth',
        'material' => 'Birchwood', 'unit_type' => 'piece',
        'keywords' => 'wooden spoon, birchwood spoon, eco spoon, compostable spoon, wooden chamach',
        'short' => 'Sanded birchwood, no splinters, no plastic aftertaste.',
        'long' => 'FSC birchwood, sanded on both faces and the edges. The standard replacement wherever single-use plastic cutlery is being phased out.',
        'flags' => ['featured', 'eco', 'compostable', 'biodegradable'],
        'tags' => ['eco-friendly', 'compostable', 'birchwood', 'catering', 'delivery'],
        'attrs' => ['material_type' => 'birchwood', 'compostable' => true, 'food_grade' => true],
        'variants' => [
            ['name' => '160 mm', 'code' => '160', 'price' => 0.95, 'mrp' => 1.50, 'length' => 160, 'weight' => 2.4, 'colour' => 'Natural',
             'packs' => [[50, 'Pack of 50'], [100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'WD-FRK', 'name' => 'Wooden Fork 160 mm',
        'category' => 'wooden-cutlery', 'hsn' => '4419', 'brand' => 'supplykaro-earth',
        'material' => 'Birchwood', 'unit_type' => 'piece',
        'keywords' => 'wooden fork, birchwood fork, eco fork, compostable fork',
        'short' => 'The matching fork for the birchwood range.',
        'long' => 'Same FSC birchwood and finish as the spoon. Pairs into a cutlery set with the knife and a napkin.',
        'flags' => ['eco', 'compostable', 'biodegradable'], 'tags' => ['eco-friendly', 'birchwood', 'catering'],
        'attrs' => ['material_type' => 'birchwood', 'compostable' => true],
        'variants' => [
            ['name' => '160 mm', 'code' => '160', 'price' => 0.95, 'mrp' => 1.50, 'length' => 160, 'weight' => 2.4, 'colour' => 'Natural',
             'packs' => [[50, 'Pack of 50'], [100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'WD-KNF', 'name' => 'Wooden Knife 165 mm',
        'category' => 'wooden-cutlery', 'hsn' => '4419', 'brand' => 'supplykaro-earth',
        'material' => 'Birchwood', 'unit_type' => 'piece',
        'keywords' => 'wooden knife, birchwood knife, eco knife, compostable knife',
        'short' => 'Serrated birchwood knife with a stiff blade.',
        'long' => 'Completes the compostable cutlery set. Rigid enough for a grilled sandwich.',
        'flags' => ['eco', 'compostable', 'biodegradable'], 'tags' => ['eco-friendly', 'birchwood'],
        'attrs' => ['material_type' => 'birchwood', 'compostable' => true],
        'variants' => [
            ['name' => '165 mm', 'code' => '165', 'price' => 0.98, 'mrp' => 1.55, 'length' => 165, 'weight' => 2.6, 'colour' => 'Natural',
             'packs' => [[50, 'Pack of 50'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'WD-ICS', 'name' => 'Wooden Ice Cream Spoon 95 mm',
        'category' => 'ice-cream-spoons', 'hsn' => '4419', 'brand' => 'supplykaro-earth',
        'material' => 'Birchwood', 'unit_type' => 'piece',
        'keywords' => 'ice cream spoon, wooden ice cream spoon, dessert spoon, kulfi spoon, chamach small',
        'short' => 'The little flat wooden spoon for cups and kulfi.',
        'long' => 'Rounded ends and a sanded finish, so it does not catch on the lip. Used for ice cream, kulfi, mousse and dessert cups.',
        'flags' => ['eco', 'compostable', 'biodegradable'], 'tags' => ['eco-friendly', 'birchwood', 'food-stall'],
        'attrs' => ['material_type' => 'birchwood', 'compostable' => true],
        'variants' => [
            ['name' => '95 mm', 'code' => '095', 'price' => 0.42, 'mrp' => 0.68, 'length' => 95, 'weight' => 1.1, 'colour' => 'Natural',
             'packs' => [[100, 'Pack of 100'], [1000, 'Box of 1,000'], [5000, 'Carton of 5,000']]],
        ],
    ],
    [
        'sku_root' => 'CT-ICS', 'name' => 'Plastic Ice Cream Spoon',
        'category' => 'ice-cream-spoons', 'hsn' => '3924', 'brand' => 'supplykaro-essentials',
        'material' => 'Food-grade polystyrene', 'unit_type' => 'piece',
        'keywords' => 'plastic ice cream spoon, small spoon, dessert spoon, kulfi spoon',
        'short' => 'The lowest-cost dessert spoon, by some distance.',
        'long' => 'Where cost matters more than material, this is the volume option for ice cream parlours and dessert counters.',
        'flags' => [], 'tags' => ['food-stall', 'best-value'],
        'attrs' => ['material_type' => 'plastic', 'food_grade' => true],
        'variants' => [
            ['name' => 'Standard', 'code' => 'STD', 'price' => 0.18, 'mrp' => 0.30, 'length' => 92, 'weight' => 0.6, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'BM-SET', 'name' => 'Bamboo Cutlery Set',
        'category' => 'wooden-cutlery', 'hsn' => '4419', 'brand' => 'supplykaro-earth',
        'material' => 'Bamboo', 'unit_type' => 'set',
        'keywords' => 'bamboo cutlery set, cutlery kit, eco cutlery set, spoon fork knife napkin set',
        'short' => 'Spoon, fork, knife and napkin, wrapped and sealed.',
        'long' => 'A pre-wrapped set: one bamboo spoon, fork and knife plus a napkin, sealed in a paper sleeve. Drop one in a delivery bag and the cutlery question is answered.',
        'flags' => ['new', 'eco', 'compostable', 'biodegradable'], 'tags' => ['eco-friendly', 'delivery', 'bamboo'],
        'attrs' => ['material_type' => 'bamboo', 'compostable' => true],
        'variants' => [
            ['name' => '4-Piece Set', 'code' => '4PC', 'price' => 4.80, 'mrp' => 7.20, 'weight' => 11.0, 'colour' => 'Natural',
             'packs' => [[50, 'Pack of 50'], [250, 'Carton of 250']]],
        ],
    ],
];
