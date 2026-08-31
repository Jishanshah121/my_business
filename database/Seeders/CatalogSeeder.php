<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeder;
use RuntimeException;

/**
 * Builds the DEMO catalog from database/Seeders/data/catalog/*.php.
 *
 * Everything written here carries is_demo_data = 1, so the whole placeholder
 * catalog can be removed with `php bin/console db:wipe-demo` when real supplier
 * data arrives — without touching reference data or application code.
 *
 * The seeder is idempotent: re-running it updates rows in place rather than
 * duplicating them, keyed on sku_root, variant_code and sku.
 */
final class CatalogSeeder extends Seeder
{
    public function order(): int
    {
        return 90;
    }

    /**
     * Quantity tier curve, expressed as multiples of the smallest pack.
     *
     * A product whose smallest pack is 100 pieces therefore gets tiers at
     * 100 / 500 / 1,000 / 5,000 / 10,000 pieces — matching the pricing example
     * in the architecture document. Pack prices are NOT discounted separately;
     * all volume pricing comes from here, so a bulk buyer is never discounted
     * twice for the same quantity.
     *
     * @var array<int,float> multiplier => share of list unit price
     */
    private const TIER_CURVE = [1 => 1.00, 5 => 0.95, 10 => 0.90, 50 => 0.84, 100 => 0.78];

    /** Approved B2B accounts start one step better than the public curve. */
    private const B2B_FACTOR = 0.96;

    /** @var array<string,int> */
    private array $categories = [];
    /** @var array<string,int> */
    private array $brands = [];
    /** @var array<string,int> */
    private array $tags = [];
    /** @var array<string,array{id:int,type:string}> */
    private array $attributes = [];
    /** @var array<string,int> */
    private array $attributeValues = [];
    /** @var array<string,int> */
    private array $customerGroups = [];

    private int $warehouseId = 0;

    public function run(): void
    {
        $this->loadReferences();

        $products = $this->loadCatalogData();
        if ($products === []) {
            throw new RuntimeException('No catalog data files found in database/Seeders/data/catalog/.');
        }

        $counts = ['products' => 0, 'variants' => 0, 'packs' => 0, 'tiers' => 0, 'inventory' => 0];

        foreach ($products as $data) {
            $productId = $this->upsertProduct($data);
            $counts['products']++;

            $this->syncTags($productId, $data['tags'] ?? []);
            $this->syncAttributes($productId, $data['attrs'] ?? []);
            $this->upsertPlaceholderImage($productId, $data);

            $hasDefaultVariant = false;
            foreach ($data['variants'] as $index => $variant) {
                $isDefault = ($variant['default'] ?? false) || (!$hasDefaultVariant && $index === count($data['variants']) - 1 && !$this->anyDefault($data['variants']));
                if ($isDefault) {
                    $hasDefaultVariant = true;
                }

                $variantId = $this->upsertVariant($productId, $data, $variant, $index, $isDefault);
                $counts['variants']++;

                $smallestPack = min(array_column($variant['packs'], 0));

                foreach ($variant['packs'] as $packIndex => [$pieces, $label]) {
                    $this->upsertPack($variantId, $data, $variant, $pieces, $label, $packIndex);
                    $counts['packs']++;
                }

                $counts['tiers'] += $this->generateTiers($variantId, (float) $variant['price'], $smallestPack);
                $counts['inventory'] += $this->seedInventory($variantId, $data['sku_root'] . $variant['code'], $smallestPack);
            }
        }

        $this->refreshCategoryCounts();

        $this->info(sprintf(
            '%d products, %d variants, %d pack SKUs, %d price tiers, %d stock rows',
            $counts['products'], $counts['variants'], $counts['packs'], $counts['tiers'], $counts['inventory']
        ));
        $this->info('All rows tagged is_demo_data = 1 — remove with: php bin/console db:wipe-demo');
    }

    // ---------------------------------------------------------------- data

    /** @return list<array<string,mixed>> */
    private function loadCatalogData(): array
    {
        $dir = __DIR__ . '/data/catalog';
        $files = glob($dir . '/*.php') ?: [];
        sort($files);

        $products = [];
        foreach ($files as $file) {
            /** @var list<array<string,mixed>> $chunk */
            $chunk = require $file;
            $products = array_merge($products, $chunk);
        }

        return $products;
    }

    private function loadReferences(): void
    {
        foreach ($this->pdo->query('SELECT `id`, `slug` FROM `categories`') as $row) {
            $this->categories[$row['slug']] = (int) $row['id'];
        }
        foreach ($this->pdo->query('SELECT `id`, `slug` FROM `brands`') as $row) {
            $this->brands[$row['slug']] = (int) $row['id'];
        }
        foreach ($this->pdo->query('SELECT `id`, `slug` FROM `tags`') as $row) {
            $this->tags[$row['slug']] = (int) $row['id'];
        }
        foreach ($this->pdo->query('SELECT `id`, `code`, `input_type` FROM `attributes`') as $row) {
            $this->attributes[$row['code']] = ['id' => (int) $row['id'], 'type' => $row['input_type']];
        }
        foreach ($this->pdo->query('SELECT `id`, `attribute_id`, `value` FROM `attribute_values`') as $row) {
            $this->attributeValues[$row['attribute_id'] . ':' . $row['value']] = (int) $row['id'];
        }
        foreach ($this->pdo->query('SELECT `id`, `code` FROM `customer_groups`') as $row) {
            $this->customerGroups[$row['code']] = (int) $row['id'];
        }

        $warehouse = $this->pdo->query('SELECT `id` FROM `warehouses` WHERE `is_default` = 1 LIMIT 1')->fetchColumn();
        if ($warehouse === false) {
            throw new RuntimeException('No default warehouse. Run WarehouseSeeder first.');
        }
        $this->warehouseId = (int) $warehouse;
    }

    // ------------------------------------------------------------- writers

    /** @param array<string,mixed> $data */
    private function upsertProduct(array $data): int
    {
        $categorySlug = $data['category'];
        if (!isset($this->categories[$categorySlug])) {
            throw new RuntimeException("Unknown category slug '{$categorySlug}' for product {$data['sku_root']}.");
        }

        $flags = $data['flags'] ?? [];

        return $this->upsert('products', ['sku_root' => $data['sku_root']], [
            'uuid'              => $this->uuidFor($data['sku_root']),
            'category_id'       => $this->categories[$categorySlug],
            'brand_id'          => $this->brands[$data['brand'] ?? 'generic'] ?? null,
            'name'              => $data['name'],
            'slug'              => $this->slug($data['name']),
            'short_description' => $data['short'] ?? null,
            'long_description'  => $data['long'] ?? null,
            'search_keywords'   => $data['keywords'] ?? null,
            'material'          => $data['material'] ?? null,
            'unit_type'         => $data['unit_type'] ?? 'piece',
            'hsn_code'          => $data['hsn'],
            'is_eco'            => (int) in_array('eco', $flags, true),
            'is_compostable'    => (int) in_array('compostable', $flags, true),
            'is_recyclable'     => (int) in_array('recyclable', $flags, true),
            'is_biodegradable'  => (int) in_array('biodegradable', $flags, true),
            'is_featured'       => (int) in_array('featured', $flags, true),
            'is_bestseller'     => (int) in_array('bestseller', $flags, true),
            'is_new_arrival'    => (int) in_array('new', $flags, true),
            'is_b2b_only'       => (int) in_array('b2b_only', $flags, true),
            'status'            => 'published',
            'published_at'      => date('Y-m-d H:i:s'),
            'seo_title'         => $data['name'] . ' — Buy Online in Bulk | SupplyKaro',
            'seo_description'   => $data['short'] ?? $data['name'],
            'is_demo_data'      => 1,
        ]);
    }

    /**
     * @param array<string,mixed> $product
     * @param array<string,mixed> $variant
     */
    private function upsertVariant(int $productId, array $product, array $variant, int $index, bool $isDefault): int
    {
        return $this->upsert(
            'product_variants',
            ['product_id' => $productId, 'variant_code' => $variant['code']],
            [
                'name'            => $variant['name'],
                'size_label'      => $variant['size'] ?? $variant['name'],
                'capacity_ml'     => $variant['capacity'] ?? null,
                'diameter_mm'     => $variant['diameter'] ?? null,
                'length_mm'       => $variant['length'] ?? null,
                'width_mm'        => $variant['width'] ?? null,
                'height_mm'       => $variant['height'] ?? null,
                'unit_weight_g'   => $variant['weight'] ?? null,
                'colour'          => $variant['colour'] ?? null,
                'ply'             => $variant['ply'] ?? null,
                'gsm'             => $variant['gsm'] ?? null,
                'base_unit_price' => number_format((float) $variant['price'], 4, '.', ''),
                'mrp_unit_price'  => isset($variant['mrp']) ? number_format((float) $variant['mrp'], 4, '.', '') : null,
                'is_default'      => (int) $isDefault,
                'status'          => 'active',
                'sort_order'      => $index * 10,
                'is_demo_data'    => 1,
            ]
        );
    }

    /**
     * @param array<string,mixed> $product
     * @param array<string,mixed> $variant
     */
    private function upsertPack(int $variantId, array $product, array $variant, int $pieces, string $label, int $index): int
    {
        $sku = sprintf('%s-%s-%d', $product['sku_root'], $variant['code'], $pieces);

        // Pack list price = pieces x list unit price. Volume discounts live in
        // quantity_price_tiers, never here (see TIER_CURVE).
        $packPrice = round($pieces * (float) $variant['price'], 2);
        $packMrp = isset($variant['mrp']) ? round($pieces * (float) $variant['mrp'], 2) : null;
        $unitWeight = (float) ($variant['weight'] ?? 0);

        return $this->upsert('variant_packs', ['sku' => $sku], [
            'variant_id'      => $variantId,
            'pack_label'      => $label,
            'pieces_per_pack' => $pieces,
            'base_price'      => number_format($packPrice, 2, '.', ''),
            'mrp'             => $packMrp !== null ? number_format($packMrp, 2, '.', '') : null,
            'min_order_qty'   => 1,
            'qty_step'        => 1,
            // Packaging adds roughly 6% to the net product weight.
            'pack_weight_g'   => $unitWeight > 0 ? number_format($unitWeight * $pieces * 1.06, 3, '.', '') : null,
            'is_breakable'    => 1,
            'is_default'      => (int) ($index === 0),
            'is_b2b_only'     => (int) in_array('b2b_only', $product['flags'] ?? [], true),
            'status'          => 'active',
            'sort_order'      => $index * 10,
            'is_demo_data'    => 1,
        ]);
    }

    /**
     * Generate the public and B2B quantity tiers for one variant.
     *
     * Tiers are scoped to the variant (not the pack) and keyed on total base
     * units, so 10 sleeves of 100 earn the same rate as 1 carton of 1,000.
     */
    private function generateTiers(int $variantId, float $unitPrice, int $smallestPack): int
    {
        $written = 0;
        $seen = [];

        foreach (self::TIER_CURVE as $multiplier => $share) {
            $minQty = $smallestPack * $multiplier;
            if (isset($seen[$minQty])) {
                continue;
            }
            $seen[$minQty] = true;

            // Public tier — applies to every customer group.
            $this->upsert(
                'quantity_price_tiers',
                ['scope_type' => 'variant', 'scope_id' => $variantId, 'customer_group_id' => null, 'min_base_qty' => $minQty],
                [
                    'unit_price'   => number_format($unitPrice * $share, 4, '.', ''),
                    'label'        => $multiplier === 1 ? null : 'Bulk rate',
                    'is_active'    => 1,
                    'is_demo_data' => 1,
                ]
            );
            $written++;

            // Approved B2B accounts get a slightly better rate at the same
            // quantities. This exists to exercise group-specific tier
            // resolution — replace with your real negotiated rates.
            if (isset($this->customerGroups['b2b_standard'])) {
                $this->upsert(
                    'quantity_price_tiers',
                    [
                        'scope_type'        => 'variant',
                        'scope_id'          => $variantId,
                        'customer_group_id' => $this->customerGroups['b2b_standard'],
                        'min_base_qty'      => $minQty,
                    ],
                    [
                        'unit_price'   => number_format($unitPrice * $share * self::B2B_FACTOR, 4, '.', ''),
                        'label'        => 'Business rate',
                        'is_active'    => 1,
                        'is_demo_data' => 1,
                    ]
                );
                $written++;
            }
        }

        return $written;
    }

    /**
     * Opening stock. Deterministic from the SKU so re-seeding gives the same
     * numbers and test expectations stay stable.
     */
    private function seedInventory(int $variantId, string $seedKey, int $smallestPack): int
    {
        mt_srand(crc32($seedKey));
        $multiplier = mt_rand(8, 220);
        mt_srand();

        $onHand = $smallestPack * $multiplier;

        $this->upsert(
            'inventory',
            ['variant_id' => $variantId, 'warehouse_id' => $this->warehouseId, 'pack_id' => null],
            [
                'quantity_on_hand'    => $onHand,
                'quantity_reserved'   => 0,
                'low_stock_threshold' => $smallestPack * 5,
                'reorder_point'       => $smallestPack * 10,
                'reorder_quantity'    => $smallestPack * 50,
                'is_demo_data'        => 1,
            ]
        );

        return 1;
    }

    /** @param list<string> $tagSlugs */
    private function syncTags(int $productId, array $tagSlugs): void
    {
        foreach ($tagSlugs as $slug) {
            if (!isset($this->tags[$slug])) {
                continue;
            }
            $this->upsert(
                'product_tags',
                ['product_id' => $productId, 'tag_id' => $this->tags[$slug]],
                ['is_demo_data' => 1]
            );
        }
    }

    /** @param array<string,mixed> $attrs */
    private function syncAttributes(int $productId, array $attrs): void
    {
        foreach ($attrs as $code => $value) {
            if (!isset($this->attributes[$code])) {
                continue;
            }

            $attribute = $this->attributes[$code];
            $row = [
                'attribute_value_id' => null,
                'value_text'         => null,
                'value_number'       => null,
                'value_boolean'      => null,
                'is_demo_data'       => 1,
            ];

            if ($attribute['type'] === 'boolean') {
                $row['value_boolean'] = (int) (bool) $value;
            } elseif ($attribute['type'] === 'number') {
                $row['value_number'] = (string) $value;
            } elseif (in_array($attribute['type'], ['select', 'multiselect'], true)) {
                $key = $attribute['id'] . ':' . $value;
                $row['attribute_value_id'] = $this->attributeValues[$key] ?? null;
                $row['value_text'] = (string) $value;
            } else {
                $row['value_text'] = (string) $value;
            }

            $this->upsert(
                'product_attributes',
                ['product_id' => $productId, 'variant_id' => null, 'attribute_id' => $attribute['id']],
                $row
            );
        }
    }

    /**
     * Records a generated SVG placeholder. Real photography replaces the path
     * without any schema change; is_placeholder flags what still needs a photo.
     *
     * @param array<string,mixed> $data
     */
    private function upsertPlaceholderImage(int $productId, array $data): void
    {
        $this->upsert(
            'product_images',
            ['product_id' => $productId, 'variant_id' => null, 'is_primary' => 1],
            [
                'path'           => '/assets/images/placeholder/' . $data['category'] . '.svg',
                'alt_text'       => $data['name'] . ' — SupplyKaro',
                'mime_type'      => 'image/svg+xml',
                'is_placeholder' => 1,
                'sort_order'     => 0,
                'is_demo_data'   => 1,
            ]
        );
    }

    private function refreshCategoryCounts(): void
    {
        // Denormalised count used by the navigation and listing headers.
        $this->pdo->exec(
            "UPDATE `categories` c
             SET c.`product_count` = (
                 SELECT COUNT(*) FROM `products` p
                 WHERE p.`category_id` = c.`id`
                   AND p.`status` = 'published'
                   AND p.`deleted_at` IS NULL
             )"
        );
        // Roll child counts up into parents.
        $this->pdo->exec(
            "UPDATE `categories` parent
             SET parent.`product_count` = parent.`product_count` + COALESCE((
                 SELECT SUM(child.`product_count`) FROM (
                     SELECT `parent_id`, `product_count` FROM `categories`
                 ) child WHERE child.`parent_id` = parent.`id`
             ), 0)
             WHERE parent.`parent_id` IS NULL"
        );
    }

    /** @param list<array<string,mixed>> $variants */
    private function anyDefault(array $variants): bool
    {
        foreach ($variants as $variant) {
            if ($variant['default'] ?? false) {
                return true;
            }
        }

        return false;
    }

    /** Deterministic UUID so re-seeding does not churn identifiers. */
    private function uuidFor(string $key): string
    {
        $hash = md5('supplykaro-demo-' . $key);

        return sprintf(
            '%s-%s-4%s-%s%s-%s',
            substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 13, 3),
            dechex(8 + (hexdec($hash[16]) % 4)), substr($hash, 17, 3), substr($hash, 20, 12)
        );
    }
}
