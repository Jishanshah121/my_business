<?php

declare(strict_types=1);

/**
 * DEMO / PLACEHOLDER CATALOG DATA — see database/Seeders/data/catalog/README.md
 * Prices are realistic Indian-market placeholders, quoted EXCLUSIVE of GST.
 * Every row seeded from this file carries is_demo_data = 1 and can be removed
 * with:  php bin/console db:wipe-demo
 */

return [
        // ========================== TISSUES & HYGIENE ==========================
    [
        'sku_root' => 'TN-1P', 'name' => 'Table Napkin 1 Ply 27 x 27 cm',
        'category' => 'table-napkins', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'piece',
        'keywords' => 'napkin, tissue, table napkin, paper napkin, tissue paper, servette, rumaal tissue',
        'short' => 'The standard single-ply table napkin, by the thousand.',
        'long' => 'Virgin-pulp tissue, quarter folded. The volume napkin for restaurants, canteens and event catering where a napkin is used once and binned.',
        'flags' => ['bestseller', 'recyclable'], 'tags' => ['catering', 'wedding', 'party', 'best-value', 'takeaway'],
        'attrs' => ['material_type' => 'paper', 'ply' => 1, 'food_grade' => true],
        'variants' => [
            ['name' => 'White', 'code' => 'WHT', 'price' => 0.16, 'mrp' => 0.28, 'size' => '27 x 27 cm', 'weight' => 1.1, 'ply' => 1, 'colour' => 'White', 'default' => true,
             'packs' => [[100, 'Pack of 100'], [500, 'Pack of 500'], [5000, 'Carton of 5,000']]],
            ['name' => 'Coloured', 'code' => 'CLR', 'price' => 0.19, 'mrp' => 0.32, 'size' => '27 x 27 cm', 'weight' => 1.1, 'ply' => 1, 'colour' => 'Printed',
             'packs' => [[100, 'Pack of 100'], [500, 'Pack of 500']]],
        ],
    ],
    [
        'sku_root' => 'TN-2P', 'name' => 'Table Napkin 2 Ply 30 x 30 cm',
        'category' => 'table-napkins', 'hsn' => '4818', 'brand' => 'supplykaro-pro',
        'material' => 'Virgin tissue paper', 'unit_type' => 'piece',
        'keywords' => '2 ply napkin, double ply tissue, premium napkin, dinner napkin',
        'short' => 'Two-ply and larger — the one you put on a laid table.',
        'long' => 'Softer, more absorbent and noticeably better on a set table than single ply. Embossed border.',
        'flags' => ['featured'], 'tags' => ['wedding', 'corporate', 'catering', 'dine-in'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '2 Ply White', 'code' => 'WHT', 'price' => 0.42, 'mrp' => 0.68, 'size' => '30 x 30 cm', 'weight' => 2.4, 'ply' => 2, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'TN-CKT', 'name' => 'Cocktail Napkin 2 Ply 20 x 20 cm',
        'category' => 'table-napkins', 'hsn' => '4818', 'brand' => 'supplykaro-pro',
        'material' => 'Virgin tissue paper', 'unit_type' => 'piece',
        'keywords' => 'cocktail napkin, small napkin, bar napkin, snack tissue',
        'short' => 'Small two-ply napkin for bars, snacks and canapes.',
        'long' => 'Sized for a glass base or a canape plate. Standard behind a bar and at standing receptions.',
        'flags' => [], 'tags' => ['party', 'corporate', 'beverage-service'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '2 Ply', 'code' => '2PLY', 'price' => 0.24, 'mrp' => 0.40, 'size' => '20 x 20 cm', 'weight' => 1.3, 'ply' => 2, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'FT-BOX', 'name' => 'Facial Tissue Box 100 Pulls 2 Ply',
        'category' => 'facial-tissues', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'box',
        'keywords' => 'facial tissue, tissue box, face tissue, soft tissue, tissue paper box',
        'short' => 'Counter-top box of 100 two-ply pulls.',
        'long' => 'The box that sits on a reception desk, a restaurant counter or a hotel room table. Interfold, so the next sheet comes up with the last.',
        'flags' => ['bestseller'], 'tags' => ['pantry', 'housekeeping', 'dine-in'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '100 Pulls', 'code' => '100P', 'price' => 52.00, 'mrp' => 80.00, 'weight' => 145, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Box'], [24, 'Carton of 24 Boxes']]],
        ],
    ],
    [
        'sku_root' => 'FT-SFT', 'name' => 'Facial Tissue Soft Pack 200 Pulls',
        'category' => 'facial-tissues', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'pack',
        'keywords' => 'soft pack tissue, facial tissue pack, refill tissue, 200 pulls tissue',
        'short' => 'Soft pack refill — cheaper per pull than a box.',
        'long' => 'The same two-ply tissue without the carton. Works as a dispenser refill and stores in far less space.',
        'flags' => [], 'tags' => ['pantry', 'housekeeping', 'best-value'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '200 Pulls', 'code' => '200P', 'price' => 74.00, 'mrp' => 110.00, 'weight' => 270, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Pack'], [20, 'Carton of 20 Packs']]],
        ],
    ],
    [
        'sku_root' => 'FT-PKT', 'name' => 'Pocket Tissue 3 Ply',
        'category' => 'facial-tissues', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'pack',
        'keywords' => 'pocket tissue, hanky tissue, travel tissue, small tissue pack',
        'short' => 'Ten three-ply sheets in a sealed pocket pack.',
        'long' => 'Handed out with a meal, dropped into a delivery bag, or stocked at a reception desk.',
        'flags' => [], 'tags' => ['delivery', 'takeaway', 'pantry'],
        'attrs' => ['material_type' => 'paper', 'ply' => 3],
        'variants' => [
            ['name' => '10 Pulls', 'code' => '010P', 'price' => 4.50, 'mrp' => 7.00, 'weight' => 12, 'ply' => 3, 'colour' => 'White',
             'packs' => [[24, 'Pack of 24'], [240, 'Carton of 240']]],
        ],
    ],
    [
        'sku_root' => 'KT-60', 'name' => 'Kitchen Towel Roll 2 Ply 60 Pulls',
        'category' => 'kitchen-towels', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'roll',
        'keywords' => 'kitchen towel, kitchen tissue roll, paper towel, kitchen roll, absorbent towel, kitchen paper',
        'short' => 'Absorbent two-ply kitchen roll, 60 perforated sheets.',
        'long' => 'Thick enough to soak oil off a pakora and strong enough to wipe a counter without shredding. The everyday kitchen roll.',
        'flags' => ['bestseller'], 'tags' => ['housekeeping', 'pantry', 'dine-in'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '60 Pulls', 'code' => '060P', 'price' => 38.00, 'mrp' => 58.00, 'weight' => 150, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Roll'], [2, 'Twin Pack'], [24, 'Carton of 24 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'KT-100', 'name' => 'Kitchen Towel Roll 2 Ply 100 Pulls',
        'category' => 'kitchen-towels', 'hsn' => '4818', 'brand' => 'supplykaro-pro',
        'material' => 'Virgin tissue paper', 'unit_type' => 'roll',
        'keywords' => 'kitchen towel 100, large kitchen roll, paper towel big roll, kitchen tissue',
        'short' => 'Longer roll — fewer changes in a busy kitchen.',
        'long' => 'Same two-ply sheet, 100 pulls to the roll. Works out cheaper per sheet and needs replacing less often on a hot line.',
        'flags' => [], 'tags' => ['housekeeping', 'best-value'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '100 Pulls', 'code' => '100P', 'price' => 58.00, 'mrp' => 88.00, 'weight' => 245, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Roll'], [24, 'Carton of 24 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'HT-MFD', 'name' => 'M-Fold Hand Towel',
        'category' => 'kitchen-towels', 'hsn' => '4818', 'brand' => 'supplykaro-pro',
        'material' => 'Virgin tissue paper', 'unit_type' => 'piece',
        'keywords' => 'hand towel, m fold tissue, multifold towel, washroom tissue, hand drying paper',
        'short' => 'Interfolded hand towels for a wall dispenser.',
        'long' => 'M-fold so one sheet pulls the next into place. Standard washroom drying for offices, hotels and restaurants.',
        'flags' => [], 'tags' => ['housekeeping', 'corporate'],
        'attrs' => ['material_type' => 'paper', 'ply' => 1],
        'variants' => [
            ['name' => 'Single Ply', 'code' => '1PLY', 'price' => 0.34, 'mrp' => 0.55, 'size' => '23 x 23 cm', 'weight' => 1.8, 'ply' => 1, 'colour' => 'White',
             'packs' => [[150, 'Pack of 150'], [3000, 'Carton of 3,000']]],
        ],
    ],
    [
        'sku_root' => 'TR-200', 'name' => 'Toilet Roll 2 Ply 200 Pulls',
        'category' => 'toilet-rolls', 'hsn' => '4818', 'brand' => 'supplykaro-essentials',
        'material' => 'Virgin tissue paper', 'unit_type' => 'roll',
        'keywords' => 'toilet roll, toilet paper, bathroom tissue, tissue roll, washroom roll',
        'short' => 'Two-ply toilet roll, 200 sheets.',
        'long' => 'Standard core, fits every common holder. Bought by the carton for washroom stocking.',
        'flags' => ['bestseller'], 'tags' => ['housekeeping', 'corporate'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '200 Pulls', 'code' => '200P', 'price' => 19.00, 'mrp' => 30.00, 'weight' => 105, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Roll'], [4, 'Pack of 4'], [48, 'Carton of 48 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'TR-JMB', 'name' => 'Jumbo Toilet Roll 2 Ply 300 m',
        'category' => 'toilet-rolls', 'hsn' => '4818', 'brand' => 'supplykaro-pro',
        'material' => 'Virgin tissue paper', 'unit_type' => 'roll',
        'keywords' => 'jumbo roll, jumbo toilet roll, big toilet roll, commercial washroom roll',
        'short' => '300 metres — for high-traffic commercial washrooms.',
        'long' => 'Fits a standard jumbo dispenser. One roll lasts a public washroom far longer than a stack of standard rolls, which is the point.',
        'flags' => ['b2b_only'], 'tags' => ['housekeeping', 'corporate'],
        'attrs' => ['material_type' => 'paper', 'ply' => 2],
        'variants' => [
            ['name' => '300 m', 'code' => '300M', 'price' => 185.00, 'mrp' => 280.00, 'weight' => 850, 'ply' => 2, 'colour' => 'White',
             'packs' => [[1, 'Single Roll'], [6, 'Carton of 6 Rolls']]],
        ],
    ],
    [
        'sku_root' => 'WW-SCH', 'name' => 'Wet Wipe Sachet',
        'category' => 'wet-wipes', 'hsn' => '5603', 'brand' => 'supplykaro-essentials',
        'material' => 'Non-woven fabric', 'unit_type' => 'piece',
        'keywords' => 'wet wipe, wet tissue, refreshing wipe, hand wipe sachet, gila tissue',
        'short' => 'Individually sealed wipes to hand out after a meal.',
        'long' => 'Lightly fragranced, individually sealed so they stay wet in storage. Handed out after a meal or dropped into a delivery bag.',
        'flags' => [], 'tags' => ['delivery', 'dine-in', 'takeaway'],
        'attrs' => ['material_type' => 'non-woven'],
        'variants' => [
            ['name' => 'Standard', 'code' => 'STD', 'price' => 0.85, 'mrp' => 1.40, 'weight' => 3.5, 'colour' => 'White',
             'packs' => [[100, 'Pack of 100'], [1000, 'Carton of 1,000']]],
        ],
    ],
    [
        'sku_root' => 'WW-CAN', 'name' => 'Alcohol Wet Wipes Canister 80 Pulls',
        'category' => 'wet-wipes', 'hsn' => '5603', 'brand' => 'supplykaro-pro',
        'material' => 'Non-woven fabric', 'unit_type' => 'canister',
        'keywords' => 'alcohol wipes, sanitising wipes, disinfectant wipes, surface wipes canister',
        'short' => 'Pull-top canister for surface sanitising.',
        'long' => 'Alcohol-based wipes in a resealable canister — for tables, counters and equipment between covers.',
        'flags' => [], 'tags' => ['housekeeping', 'dine-in'],
        'attrs' => ['material_type' => 'non-woven'],
        'variants' => [
            ['name' => '80 Pulls', 'code' => '080P', 'price' => 145.00, 'mrp' => 220.00, 'weight' => 480, 'colour' => 'White',
             'packs' => [[1, 'Single Canister'], [12, 'Carton of 12']]],
        ],
    ],
];
