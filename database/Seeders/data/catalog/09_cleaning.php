<?php

declare(strict_types=1);

/** DEMO / PLACEHOLDER CATALOG DATA — see README.md in this directory. */

return [
    [
        'sku_root' => 'CL-SAN', 'name' => 'Hand Sanitiser 500 ml',
        'category' => 'sanitiser-cleaners', 'hsn' => '3808', 'brand' => 'supplykaro-pro',
        'material' => 'Alcohol-based gel', 'unit_type' => 'bottle',
        'keywords' => 'sanitizer, hand sanitiser, sanitiser gel, hand rub, alcohol gel',
        'short' => '70% alcohol gel with a pump top.',
        'long' => 'Meets the 70% alcohol threshold, with a glycerine base so hands do not crack over a full shift. Pump top fits a counter or a wall bracket.',
        'flags' => [], 'tags' => ['housekeeping', 'pantry'],
        'attrs' => [],
        'variants' => [
            ['name' => '500 ml', 'code' => '500', 'price' => 95.00, 'mrp' => 145.00, 'capacity' => 500, 'weight' => 560, 'colour' => 'Natural',
             'packs' => [[1, 'Single Bottle'], [12, 'Carton of 12']]],
        ],
    ],
    [
        'sku_root' => 'CL-MFC', 'name' => 'Microfibre Cleaning Cloth 40 x 40 cm',
        'category' => 'cleaning-cloth', 'hsn' => '5603', 'brand' => 'supplykaro-essentials',
        'material' => 'Microfibre', 'unit_type' => 'piece',
        'keywords' => 'cleaning cloth, microfiber cloth, duster, wiping cloth, pochha, kitchen cloth',
        'short' => 'Lint-free microfibre that cleans glass without streaks.',
        'long' => 'Washable and reusable through a few hundred cycles. Colour-code them by zone — one colour for tables, another for washrooms — and cross-contamination stops being a worry.',
        'flags' => [], 'tags' => ['housekeeping'],
        'attrs' => ['reusable' => true],
        'variants' => [
            ['name' => '40 x 40 cm', 'code' => '4040', 'price' => 18.00, 'mrp' => 28.00, 'size' => '40 x 40 cm', 'weight' => 42, 'colour' => 'Natural',
             'packs' => [[5, 'Pack of 5'], [50, 'Carton of 50']]],
        ],
    ],
    [
        'sku_root' => 'CL-SCB', 'name' => 'Scrub Pad',
        'category' => 'cleaning-cloth', 'hsn' => '6805', 'brand' => 'supplykaro-essentials',
        'material' => 'Abrasive nylon', 'unit_type' => 'piece',
        'keywords' => 'scrub pad, scrubber, dish scrubber, kitchen scrub, jhaadu pad, cleaning pad',
        'short' => 'Heavy-duty abrasive pad for utensils and griddles.',
        'long' => 'Coarse nylon abrasive bonded to a foam back. Cuts through burnt-on residue on a tawa without shredding after two uses.',
        'flags' => [], 'tags' => ['housekeeping'],
        'attrs' => [],
        'variants' => [
            ['name' => 'Standard', 'code' => 'STD', 'price' => 7.50, 'mrp' => 12.00, 'size' => '10 x 7 cm', 'weight' => 18, 'colour' => 'Natural',
             'packs' => [[10, 'Pack of 10'], [100, 'Carton of 100']]],
        ],
    ],
    [
        'sku_root' => 'DS-TIS', 'name' => 'Wall Mount Tissue Dispenser',
        'category' => 'dispensers', 'hsn' => '3926', 'brand' => 'supplykaro-pro',
        'material' => 'ABS plastic', 'unit_type' => 'piece',
        'keywords' => 'tissue dispenser, towel dispenser, wall dispenser, napkin dispenser, hand towel holder',
        'short' => 'Lockable ABS dispenser for M-fold hand towels.',
        'long' => 'Takes a full pack of M-fold hand towels. Lockable front so the stock is not walked off with, and a level window so housekeeping can see when to refill.',
        'flags' => ['b2b_only'], 'tags' => ['housekeeping', 'corporate'],
        'attrs' => ['reusable' => true],
        'variants' => [
            ['name' => 'M-Fold', 'code' => 'MFD', 'price' => 420.00, 'mrp' => 640.00, 'length' => 280, 'width' => 130, 'height' => 260, 'weight' => 720, 'colour' => 'White',
             'packs' => [[1, 'Single Unit'], [6, 'Carton of 6']]],
        ],
    ],
];
