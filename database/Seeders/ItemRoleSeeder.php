<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Item roles decouple the event calculator's rules from specific SKUs.
 *
 * A rule says "1.0 dinner plates per guest". Which product fills DINNER_PLATE
 * depends on the serving style the customer picked and on what is actually in
 * stock — so the admin can swap a supplier without touching a single rule.
 */
final class ItemRoleSeeder extends Seeder
{
    public function order(): int
    {
        return 100;
    }

    /** @var array<string,array{0:string,1:string,2:string|null}> code => [name, description, fallback category slug] */
    private const ROLES = [
        'DINNER_PLATE'    => ['Dinner Plate', 'The main plate each guest eats from', 'paper-plates'],
        'SIDE_PLATE'      => ['Side Plate', 'Starters, snacks and dessert', 'paper-plates'],
        'BOWL'            => ['Bowl', 'Curry, dal, dessert and soup', 'disposable-bowls'],
        'SERVING_TRAY'    => ['Serving Tray', 'Compartment trays for a full meal', 'serving-trays'],
        'SPOON'           => ['Spoon', 'One per guest, plus breakage', 'spoons'],
        'FORK'            => ['Fork', 'For plated and continental service', 'forks-knives'],
        'KNIFE'           => ['Knife', 'Only where the menu needs cutting', 'forks-knives'],
        'GLASS'           => ['Glass', 'Water and cold drinks', 'cold-drink-cups'],
        'CUP'             => ['Tea / Coffee Cup', 'Hot beverage service', 'paper-cups'],
        'NAPKIN'          => ['Napkin', 'The most under-ordered item at every event', 'table-napkins'],
        'TISSUE'          => ['Facial Tissue', 'Boxes for tables and washrooms', 'facial-tissues'],
        'GARBAGE_BAG'     => ['Garbage Bag', 'Clean-up, sized to the venue', 'garbage-bags'],
        'STRAW'           => ['Straw', 'With cold drinks and shakes', 'straws-stirrers'],
        'DONA'            => ['Dona', 'Chaat, prasad and snack service', 'dona-thali'],
        'ICE_CREAM_SPOON' => ['Dessert Spoon', 'Ice cream, kulfi and mousse', 'ice-cream-spoons'],
        'GLOVES'          => ['Gloves', 'For the serving staff', 'gloves-safety'],
    ];

    /**
     * Which SKU fills each role, per serving tier, in preference order.
     *
     * @var array<string,array<string,list<string>>> role => tier => [sku, ...]
     */
    private const FULFILMENT = [
        'DINNER_PLATE' => [
            'standard' => ['PP-PLT-09IN-100', 'PP-PLT-09IN-50'],
            'premium'  => ['PP-SLV-11IN-50', 'PP-SLV-12IN-50'],
            'eco'      => ['BG-PLT-10IN-50', 'AR-PLT-10RD-25', 'BG-PLT-10IN-25'],
        ],
        'SIDE_PLATE' => [
            'standard' => ['PP-PLT-06IN-100', 'PP-PLT-06IN-50'],
            'premium'  => ['PP-SLV-11IN-50'],
            'eco'      => ['BG-PLT-08IN-50', 'AR-PLT-08RD-25'],
        ],
        'BOWL' => [
            'standard' => ['PP-BWL-250-100', 'PP-BWL-250-50'],
            'premium'  => ['PP-BWL-350-50'],
            'eco'      => ['BG-BWL-250-50', 'AR-BWL-04IN-25', 'BG-BWL-250-25'],
        ],
        'SERVING_TRAY' => [
            'standard' => ['PP-4CP-11IN-50'],
            'premium'  => ['PP-4CP-11IN-50'],
            'eco'      => ['BG-5CP-5CP-25'],
        ],
        'SPOON' => [
            'standard' => ['CT-SPN-MED-100', 'CT-SPN-MED-50'],
            'premium'  => ['CT-SPN-MED-100'],
            'eco'      => ['WD-SPN-160-100', 'WD-SPN-160-50'],
        ],
        'FORK' => [
            'standard' => ['CT-FRK-MED-100', 'CT-FRK-MED-50'],
            'premium'  => ['CT-FRK-MED-100'],
            'eco'      => ['WD-FRK-160-100', 'WD-FRK-160-50'],
        ],
        'KNIFE' => [
            'standard' => ['CT-KNF-MED-100', 'CT-KNF-MED-50'],
            'premium'  => ['CT-KNF-MED-100'],
            'eco'      => ['WD-KNF-165-50'],
        ],
        'GLASS' => [
            'standard' => ['PC-PLN-200-100', 'PC-PLN-200-1000'],
            'premium'  => ['PET-GLS-300-100', 'PET-GLS-300-50'],
            'eco'      => ['PC-PLN-200-100'],
        ],
        'CUP' => [
            'standard' => ['PC-PLN-150-100', 'PC-PLN-090-100'],
            'premium'  => ['RC-CUP-250-50', 'RC-CUP-250-25'],
            'eco'      => ['PC-PLN-150-100'],
        ],
        'NAPKIN' => [
            'standard' => ['TN-1P-WHT-500', 'TN-1P-WHT-100'],
            'premium'  => ['TN-2P-WHT-100'],
            'eco'      => ['TN-1P-WHT-500'],
        ],
        'TISSUE' => [
            'standard' => ['FT-BOX-100P-1'],
            'premium'  => ['FT-BOX-100P-1'],
            'eco'      => ['FT-BOX-100P-1'],
        ],
        'GARBAGE_BAG' => [
            'standard' => ['GB-BAG-2432-30', 'GB-BAG-1921-30'],
            'premium'  => ['GB-BAG-2432-30'],
            'eco'      => ['GB-BIO-1921-30'],
        ],
        'STRAW' => [
            'standard' => ['STR-PPR-06MM-100'],
            'premium'  => ['STR-PPR-06MM-100'],
            'eco'      => ['STR-PPR-06MM-100'],
        ],
        'DONA' => [
            'standard' => ['AR-DNA-04IN-50'],
            'premium'  => ['AR-DNA-04IN-50'],
            'eco'      => ['AR-DNA-04IN-50'],
        ],
        'ICE_CREAM_SPOON' => [
            'standard' => ['CT-ICS-STD-100'],
            'premium'  => ['WD-ICS-095-100'],
            'eco'      => ['WD-ICS-095-100'],
        ],
        'GLOVES' => [
            'standard' => ['GL-NIT-M-100'],
            'premium'  => ['GL-NIT-M-100'],
            'eco'      => ['GL-NIT-M-100'],
        ],
    ];

    public function run(): void
    {
        $categories = [];
        foreach ($this->pdo->query('SELECT `id`, `slug` FROM `categories`') as $row) {
            $categories[$row['slug']] = (int) $row['id'];
        }

        $packs = [];
        foreach ($this->pdo->query('SELECT `id`, `sku` FROM `variant_packs`') as $row) {
            $packs[$row['sku']] = (int) $row['id'];
        }

        $roleIds = [];
        $sort = 0;
        foreach (self::ROLES as $code => [$name, $description, $categorySlug]) {
            $roleIds[$code] = $this->upsert('item_roles', ['code' => $code], [
                'name'        => $name,
                'description' => $description,
                'category_id' => $categories[$categorySlug] ?? null,
                'sort_order'  => $sort,
                'is_active'   => 1,
            ]);
            $sort += 10;
        }

        $mapped = 0;
        $missing = [];
        foreach (self::FULFILMENT as $roleCode => $tiers) {
            foreach ($tiers as $tier => $skus) {
                foreach ($skus as $rank => $sku) {
                    if (!isset($packs[$sku])) {
                        $missing[] = $sku;
                        continue;
                    }
                    $this->upsert(
                        'item_role_products',
                        ['item_role_id' => $roleIds[$roleCode], 'variant_pack_id' => $packs[$sku], 'tier' => $tier],
                        ['rank' => $rank, 'is_active' => 1, 'is_demo_data' => 1]
                    );
                    $mapped++;
                }
            }
        }

        $this->info(count($roleIds) . " item roles, {$mapped} SKU mappings across 3 serving tiers");
        if ($missing !== []) {
            $this->info('WARNING — unmatched SKUs (fix data/catalog or FULFILMENT): ' . implode(', ', array_unique($missing)));
        }
    }
}
