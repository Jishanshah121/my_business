<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Curated cross-sells. These are is_curated = 1, which means they ALWAYS
 * outrank whatever the nightly co-occurrence job learns — a buyer looking at
 * coffee cups should be shown lids, not whatever happened to correlate.
 */
final class AssociationSeeder extends Seeder
{
    public function order(): int
    {
        return 125;
    }

    /** @var array<string,array{fbt:list<string>,cross_sell:list<string>}> */
    private const LINKS = [
        'RC-CUP'  => ['fbt' => ['CL-LID', 'CUP-CAR'], 'cross_sell' => ['STR-WD', 'TN-1P', 'PB-HDL']],
        'DW-CUP'  => ['fbt' => ['CL-LID'],            'cross_sell' => ['STR-WD', 'CUP-CAR']],
        'PC-PLN'  => ['fbt' => ['CL-LID'],            'cross_sell' => ['STR-WD', 'TN-1P', 'CT-SPN']],
        'PET-GLS' => ['fbt' => ['STR-PPR'],           'cross_sell' => ['TN-CKT', 'PP-BWL']],
        'MS-CUP'  => ['fbt' => ['STR-PPR'],           'cross_sell' => ['CT-ICS', 'TN-1P']],
        'PP-PLT'  => ['fbt' => ['CT-SPN', 'TN-1P'],   'cross_sell' => ['PP-BWL', 'CT-FRK', 'GB-BAG']],
        'PP-SLV'  => ['fbt' => ['CT-SPN', 'TN-2P'],   'cross_sell' => ['PP-BWL', 'PET-GLS', 'GB-BAG']],
        'BG-PLT'  => ['fbt' => ['WD-SPN', 'BG-BWL'],  'cross_sell' => ['WD-FRK', 'TN-1P', 'GB-BIO']],
        'AR-PLT'  => ['fbt' => ['AR-BWL', 'WD-SPN'],  'cross_sell' => ['AR-DNA', 'TN-1P', 'GB-BIO']],
        'BG-BWL'  => ['fbt' => ['WD-SPN'],            'cross_sell' => ['BG-PLT', 'TN-1P']],
        'PP-BWL'  => ['fbt' => ['CT-SPN'],            'cross_sell' => ['PP-PLT', 'TN-1P']],
        'CN-BRY'  => ['fbt' => ['CT-SPN', 'PB-HDL'],  'cross_sell' => ['TN-1P', 'CN-SOUP', 'AL-CNT']],
        'BX-BRG'  => ['fbt' => ['FW-SHT', 'PB-HDL'],  'cross_sell' => ['TN-1P', 'BK-BAG']],
        'BX-PZA'  => ['fbt' => ['TN-1P'],             'cross_sell' => ['PB-HDL', 'CT-KNF']],
        'AL-CNT'  => ['fbt' => ['PB-HDL'],            'cross_sell' => ['AF-ROL', 'CT-SPN', 'TN-1P']],
        'CB-BOX'  => ['fbt' => ['CB-BRD'],            'cross_sell' => ['CC-LNR', 'BP-SHT', 'PB-HDL']],
        'CC-BOX'  => ['fbt' => ['CC-LNR'],            'cross_sell' => ['CB-BRD', 'BK-BAG']],
        'BP-SHT'  => ['fbt' => ['BK-BAG'],            'cross_sell' => ['CB-BOX', 'AF-ROL', 'FW-SHT']],
        'TN-1P'   => ['fbt' => [],                    'cross_sell' => ['KT-60', 'FT-BOX', 'WW-SCH']],
        'KT-60'   => ['fbt' => [],                    'cross_sell' => ['TR-200', 'CL-MFC', 'GB-BAG']],
        'TR-200'  => ['fbt' => [],                    'cross_sell' => ['HT-MFD', 'DS-TIS', 'CL-SAN']],
        'GB-BAG'  => ['fbt' => [],                    'cross_sell' => ['CL-MFC', 'CL-SCB', 'GL-NIT']],
        'GL-NIT'  => ['fbt' => ['SF-CAP'],            'cross_sell' => ['SF-MSK', 'SF-APR', 'CL-SAN']],
        'CT-SPN'  => ['fbt' => ['CT-FRK'],            'cross_sell' => ['CT-KNF', 'TN-1P', 'PP-PLT']],
        'WD-SPN'  => ['fbt' => ['WD-FRK'],            'cross_sell' => ['WD-KNF', 'BG-PLT', 'BM-SET']],
        'AF-ROL'  => ['fbt' => ['CF-ROL'],            'cross_sell' => ['AL-CNT', 'BP-ROL']],
    ];

    public function run(): void
    {
        $products = [];
        foreach ($this->pdo->query('SELECT `id`, `sku_root` FROM `products`') as $row) {
            $products[$row['sku_root']] = (int) $row['id'];
        }

        $written = 0;
        $missing = [];

        foreach (self::LINKS as $root => $groups) {
            if (!isset($products[$root])) {
                $missing[] = $root;
                continue;
            }

            foreach ($groups as $type => $relatedRoots) {
                foreach ($relatedRoots as $rank => $relatedRoot) {
                    if (!isset($products[$relatedRoot]) || $relatedRoot === $root) {
                        $missing[] = $relatedRoot;
                        continue;
                    }
                    $this->upsert(
                        'product_associations',
                        [
                            'product_id'         => $products[$root],
                            'related_product_id' => $products[$relatedRoot],
                            'type'               => $type,
                        ],
                        ['rank' => $rank, 'is_curated' => 1, 'source' => 'admin']
                    );
                    $written++;
                }
            }
        }

        $this->info("{$written} curated associations across " . count(self::LINKS) . ' products');
        if ($missing !== []) {
            $this->info('WARNING — unmatched sku_roots: ' . implode(', ', array_unique($missing)));
        }
    }
}
