<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;

/**
 * Starter kits and bundles. pricing_mode = percentage_off means the bundle
 * price is computed from its components by PriceResolver and then discounted —
 * so a component price change flows through automatically instead of leaving a
 * stale fixed price behind.
 */
final class BundleSeeder extends Seeder
{
    public function order(): int
    {
        return 120;
    }

    /**
     * @var array<string,array{name:string,type:?string,occasion:?string,discount:float,
     *      tagline:string,description:string,items:array<string,int>}>
     */
    private const BUNDLES = [
        'KIT-CAFE' => [
            'name' => 'Cafe Starter Kit', 'type' => 'cafe', 'occasion' => null, 'discount' => 7.0,
            'tagline' => 'Open the counter tomorrow morning',
            'description' => 'Everything a coffee counter needs on day one: ripple cups in both sizes, matched lids, stirrers, napkins and carry bags. Sized for roughly a fortnight at 40 covers a day.',
            'items' => ['RC-CUP-250-500' => 1, 'RC-CUP-350-500' => 1, 'CL-LID-080-500' => 1,
                        'STR-WD-140-1000' => 1, 'TN-1P-WHT-500' => 2, 'PB-HDL-MED-500' => 1],
        ],
        'KIT-CLOUD' => [
            'name' => 'Cloud Kitchen Starter Kit', 'type' => 'cloud_kitchen', 'occasion' => null, 'discount' => 8.0,
            'tagline' => 'Packaging that survives the ride',
            'description' => 'Leak-locked biryani containers, foil containers, cutlery, carry bags and foil — the delivery-first list, in one order.',
            'items' => ['CN-BRY-750-300' => 1, 'AL-CNT-450-500' => 1, 'CT-SPN-MED-1000' => 1,
                        'PB-HDL-MED-500' => 1, 'AF-ROL-018M-24' => 1, 'TN-1P-WHT-500' => 2],
        ],
        'KIT-RESTAURANT' => [
            'name' => 'Restaurant Essentials', 'type' => 'restaurant', 'occasion' => null, 'discount' => 7.0,
            'tagline' => 'The weekly consumables list, pre-built',
            'description' => 'Napkins, kitchen towels, foil, cling film, gloves and garbage bags — the things that run out on a Friday night.',
            'items' => ['TN-1P-WHT-5000' => 1, 'KT-60-060P-24' => 1, 'AF-ROL-072M-12' => 1,
                        'CF-ROL-300M-6' => 1, 'GL-NIT-M-1000' => 1, 'GB-BAG-2432-300' => 1],
        ],
        'KIT-BAKERY' => [
            'name' => 'Bakery Packaging Kit', 'type' => 'bakery', 'occasion' => null, 'discount' => 6.0,
            'tagline' => 'Protect what you baked',
            'description' => 'Cake boxes and boards in the popular sizes, cupcake boxes and liners, butter paper and bakery bags.',
            'items' => ['CB-BOX-1KG-100' => 1, 'CB-BRD-10IN-100' => 1, 'CC-BOX-6CV-100' => 1,
                        'CC-LNR-50MM-1000' => 1, 'BP-SHT-0911-500' => 2, 'BK-BAG-0609-1000' => 1],
        ],
        'KIT-OFFICE' => [
            'name' => 'Office Pantry Kit', 'type' => 'office', 'occasion' => null, 'discount' => 6.0,
            'tagline' => 'One order, one month, one invoice',
            'description' => 'Paper cups, stirrers, facial tissue, kitchen towels, toilet rolls, sanitiser and bin liners. Set it as a repeating monthly order and stop thinking about it.',
            'items' => ['PC-PLN-150-1000' => 1, 'STR-WD-140-1000' => 1, 'FT-BOX-100P-24' => 1,
                        'KT-60-060P-24' => 1, 'TR-200-200P-48' => 1, 'CL-SAN-500-12' => 1,
                        'GB-BAG-1921-300' => 1],
        ],
        'KIT-WEDDING' => [
            'name' => 'Wedding Starter Kit — 250 Guests', 'type' => null, 'occasion' => 'wedding', 'discount' => 10.0,
            'tagline' => 'Counted for 250, before you even open the calculator',
            'description' => 'A ready-made basket for a 250-guest wedding: plates, side plates, bowls, cutlery, glasses, napkins and garbage bags, with the safety margin already built in. Need a different headcount? Use Plan Your Event and we will size it exactly.',
            'items' => ['PP-SLV-11IN-500' => 1, 'PP-PLT-06IN-1000' => 1, 'PP-BWL-250-1000' => 1,
                        'CT-SPN-MED-1000' => 1, 'CT-FRK-MED-1000' => 1, 'PET-GLS-300-1000' => 1,
                        'TN-1P-WHT-5000' => 1, 'GB-BAG-2432-30' => 2],
        ],
        'KIT-PARTY' => [
            'name' => 'House Party Kit — 25 Guests', 'type' => null, 'occasion' => 'house_party', 'discount' => 8.0,
            'tagline' => 'Enough for the evening, nothing left over',
            'description' => 'Plates, bowls, glasses, spoons, straws, napkins and bin liners for 25 guests, with a margin for the friends who bring friends.',
            'items' => ['PP-PLT-09IN-50' => 1, 'PP-BWL-250-50' => 1, 'PET-GLS-300-50' => 1,
                        'CT-SPN-MED-50' => 1, 'STR-PPR-06MM-100' => 1, 'TN-1P-WHT-100' => 2,
                        'GB-BAG-1921-30' => 1],
        ],
        'KIT-ECO' => [
            'name' => 'Eco Event Kit — 100 Guests', 'type' => null, 'occasion' => 'corporate', 'discount' => 9.0,
            'tagline' => 'A hundred guests, nothing that outlives the event',
            'description' => 'Bagasse plates and bowls, birchwood cutlery, paper straws and compostable bin liners. Every item in this kit composts.',
            'items' => ['BG-PLT-10IN-500' => 1, 'BG-BWL-250-500' => 1, 'WD-SPN-160-1000' => 1,
                        'WD-FRK-160-1000' => 1, 'STR-PPR-06MM-1000' => 1, 'TN-1P-WHT-500' => 1,
                        'GB-BIO-1921-30' => 2],
        ],
    ];

    public function run(): void
    {
        $packs = [];
        foreach ($this->pdo->query('SELECT `id`, `sku` FROM `variant_packs`') as $row) {
            $packs[$row['sku']] = (int) $row['id'];
        }
        $businessTypes = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `business_types`') as $row) {
            $businessTypes[$row['code']] = (int) $row['id'];
        }
        $occasions = [];
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `occasions`') as $row) {
            $occasions[$row['code']] = (int) $row['id'];
        }

        $itemCount = 0;
        $missing = [];
        $sort = 0;

        foreach (self::BUNDLES as $sku => $bundle) {
            $bundleId = $this->upsert('bundles', ['sku' => $sku], [
                'name'             => $bundle['name'],
                'slug'             => $this->slug($bundle['name']),
                'tagline'          => $bundle['tagline'],
                'description'      => $bundle['description'],
                'business_type_id' => $bundle['type'] !== null ? ($businessTypes[$bundle['type']] ?? null) : null,
                'occasion_id'      => $bundle['occasion'] !== null ? ($occasions[$bundle['occasion']] ?? null) : null,
                'pricing_mode'     => 'percentage_off',
                'discount_pct'     => number_format($bundle['discount'], 2, '.', ''),
                'show_components'  => 1,
                'allow_partial'    => 0,
                'status'           => 'published',
                'is_featured'      => (int) in_array($sku, ['KIT-CAFE', 'KIT-WEDDING', 'KIT-ECO'], true),
                'seo_title'        => $bundle['name'] . ' | SupplyKaro',
                'seo_description'  => $bundle['tagline'],
                'sort_order'       => $sort,
                'is_demo_data'     => 1,
            ]);
            $sort += 10;

            $index = 0;
            foreach ($bundle['items'] as $itemSku => $qty) {
                if (!isset($packs[$itemSku])) {
                    $missing[] = $itemSku;
                    continue;
                }
                $this->upsert(
                    'bundle_items',
                    ['bundle_id' => $bundleId, 'variant_pack_id' => $packs[$itemSku]],
                    ['pack_qty' => $qty, 'is_required' => 1, 'sort_order' => $index * 10, 'is_demo_data' => 1]
                );
                $index++;
                $itemCount++;
            }
        }

        $this->info(count(self::BUNDLES) . " bundles, {$itemCount} components");
        if ($missing !== []) {
            $this->info('WARNING — unmatched SKUs: ' . implode(', ', array_unique($missing)));
        }
    }
}
