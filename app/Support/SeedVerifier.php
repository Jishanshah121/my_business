<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * Data-integrity checks over the seeded database.
 *
 * These are assertions about the DATA, not the code — the kind of thing that
 * breaks silently when someone edits a seeder or imports a real catalog. Run
 * with `php bin/console db:verify` after any seed or catalog import.
 */
final class SeedVerifier
{
    /** @var list<array{0:bool,1:string,2:string}> passed, name, detail */
    private array $results = [];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array{0:bool,1:string,2:string}> */
    public function run(): array
    {
        $this->checkEventCalculatorMath();
        $this->checkTierMonotonicity();
        $this->checkDefaults();
        $this->checkTaxCoverage();
        $this->checkItemRoleCoverage();
        $this->checkBundleIntegrity();
        $this->checkPackDerivation();
        $this->checkInventory();
        $this->checkDemoFlagging();
        $this->checkReadOnlyViews();

        return $this->results;
    }

    private function assert(bool $passed, string $name, string $detail = ''): void
    {
        $this->results[] = [$passed, $name, $detail];
    }

    /**
     * The wedding rule set must reproduce the worked example in the brief for
     * 500 guests. If someone retunes the margins, this is what catches it.
     */
    private function checkEventCalculatorMath(): void
    {
        $expected = [
            'DINNER_PLATE' => 600,  'SIDE_PLATE' => 600, 'BOWL'   => 600,
            'SPOON'        => 650,  'FORK'       => 650, 'GLASS'  => 750,
            'NAPKIN'       => 2000, 'GARBAGE_BAG' => 50,
        ];

        $rows = $this->pdo->query(
            "SELECT ir.`code`, er.`qty_per_guest`, er.`safety_margin_pct`,
                    er.`rounding_step`, er.`min_qty`
             FROM `event_rules` er
             JOIN `event_rule_sets` ers ON ers.`id` = er.`rule_set_id`
             JOIN `item_roles` ir       ON ir.`id` = er.`item_role_id`
             WHERE ers.`event_type` = 'wedding' AND ers.`version` = 1 AND ers.`is_active` = 1"
        )->fetchAll();

        $guests = 500;
        $mismatches = [];

        foreach ($rows as $row) {
            if (!isset($expected[$row['code']])) {
                continue;
            }
            $qty = $this->calculate(
                $guests,
                (float) $row['qty_per_guest'],
                (float) $row['safety_margin_pct'],
                (int) $row['rounding_step'],
                (int) $row['min_qty']
            );
            if ($qty !== $expected[$row['code']]) {
                $mismatches[] = sprintf('%s expected %d got %d', $row['code'], $expected[$row['code']], $qty);
            }
        }

        $checked = count(array_intersect(array_column($rows, 'code'), array_keys($expected)));

        $this->assert(
            $mismatches === [] && $checked === count($expected),
            'Event calculator — wedding, 500 guests',
            $mismatches === []
                ? "all {$checked} roles match the brief (600/600/600/650/650/750/2,000/50)"
                : implode('; ', $mismatches)
        );
    }

    /** The one true quantity formula. Mirrored by EventRuleEngine in Phase 6. */
    private function calculate(int $guests, float $perGuest, float $marginPct, int $step, int $minQty): int
    {
        $padded = $guests * $perGuest * (1 + $marginPct / 100);
        $rounded = (int) (ceil($padded / $step) * $step);

        return max($rounded, $minQty);
    }

    /** A larger quantity must never cost more per unit. */
    private function checkTierMonotonicity(): void
    {
        $rows = $this->pdo->query(
            "SELECT a.`scope_id`, a.`customer_group_id`, a.`min_base_qty` AS q1, a.`unit_price` AS p1,
                    b.`min_base_qty` AS q2, b.`unit_price` AS p2
             FROM `quantity_price_tiers` a
             JOIN `quantity_price_tiers` b
               ON b.`scope_type` = a.`scope_type`
              AND b.`scope_id` = a.`scope_id`
              AND b.`customer_group_id` <=> a.`customer_group_id`
              AND b.`min_base_qty` > a.`min_base_qty`
             WHERE a.`scope_type` = 'variant' AND b.`unit_price` > a.`unit_price`
             LIMIT 5"
        )->fetchAll();

        $this->assert(
            $rows === [],
            'Quantity tiers are monotonic',
            $rows === []
                ? 'no tier charges more per unit at a higher quantity'
                : count($rows) . ' inverted tier pairs found'
        );
    }

    /** Every product needs one default variant; every variant one default pack. */
    private function checkDefaults(): void
    {
        $noVariantDefault = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM `products` p
             WHERE p.`deleted_at` IS NULL
               AND NOT EXISTS (SELECT 1 FROM `product_variants` v
                               WHERE v.`product_id` = p.`id` AND v.`is_default` = 1)"
        )->fetchColumn();

        $noPackDefault = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM `product_variants` v
             WHERE v.`deleted_at` IS NULL
               AND NOT EXISTS (SELECT 1 FROM `variant_packs` vp
                               WHERE vp.`variant_id` = v.`id` AND vp.`is_default` = 1)"
        )->fetchColumn();

        $multiVariantDefault = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM (
                 SELECT `product_id` FROM `product_variants` WHERE `is_default` = 1
                 GROUP BY `product_id` HAVING COUNT(*) > 1
             ) x"
        )->fetchColumn();

        $this->assert(
            $noVariantDefault === 0 && $noPackDefault === 0 && $multiVariantDefault === 0,
            'Default variant and pack per product',
            $noVariantDefault === 0 && $noPackDefault === 0 && $multiVariantDefault === 0
                ? 'every product has exactly one default variant, every variant a default pack'
                : "{$noVariantDefault} products without a default variant, {$noPackDefault} variants without a default pack, {$multiVariantDefault} with more than one"
        );
    }

    /** Every HSN code used by a product must have an active tax rate. */
    private function checkTaxCoverage(): void
    {
        $rows = $this->pdo->query(
            "SELECT DISTINCT p.`hsn_code` FROM `products` p
             WHERE NOT EXISTS (
                 SELECT 1 FROM `tax_rates` t
                 WHERE t.`hsn_code` = p.`hsn_code` AND t.`is_active` = 1
             )"
        )->fetchAll(PDO::FETCH_COLUMN);

        $this->assert(
            $rows === [],
            'GST rate exists for every product HSN',
            $rows === []
                ? (int) $this->pdo->query('SELECT COUNT(DISTINCT `hsn_code`) FROM `products`')->fetchColumn()
                  . ' distinct HSN codes, all covered'
                : 'uncovered: ' . implode(', ', $rows)
        );
    }

    /** Every item role must be fillable at every serving tier. */
    private function checkItemRoleCoverage(): void
    {
        $gaps = $this->pdo->query(
            "SELECT ir.`code`, t.`tier`
             FROM `item_roles` ir
             CROSS JOIN (SELECT 'standard' AS tier UNION SELECT 'premium' UNION SELECT 'eco') t
             WHERE ir.`is_active` = 1
               AND NOT EXISTS (
                   SELECT 1 FROM `item_role_products` irp
                   WHERE irp.`item_role_id` = ir.`id` AND irp.`tier` = t.`tier` AND irp.`is_active` = 1
               )"
        )->fetchAll();

        $labels = array_map(static fn (array $r): string => $r['code'] . '/' . $r['tier'], $gaps);

        $this->assert(
            $gaps === [],
            'Every item role fillable at every tier',
            $gaps === [] ? '16 roles x 3 tiers all mapped' : 'gaps: ' . implode(', ', $labels)
        );
    }

    /** Bundle components must point at active, in-catalog packs. */
    private function checkBundleIntegrity(): void
    {
        $empty = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM `bundles` b
             WHERE b.`status` = 'published'
               AND NOT EXISTS (SELECT 1 FROM `bundle_items` bi WHERE bi.`bundle_id` = b.`id`)"
        )->fetchColumn();

        $inactive = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM `bundle_items` bi
             JOIN `variant_packs` vp ON vp.`id` = bi.`variant_pack_id`
             WHERE vp.`status` <> 'active' OR vp.`deleted_at` IS NOT NULL"
        )->fetchColumn();

        $this->assert(
            $empty === 0 && $inactive === 0,
            'Bundle components resolve',
            $empty === 0 && $inactive === 0
                ? (int) $this->pdo->query('SELECT COUNT(*) FROM `bundle_items`')->fetchColumn() . ' components, all active'
                : "{$empty} empty bundles, {$inactive} components pointing at inactive packs"
        );
    }

    /** Pack list price must equal pieces x variant list unit price. */
    private function checkPackDerivation(): void
    {
        $rows = $this->pdo->query(
            "SELECT vp.`sku`, vp.`base_price`, vp.`pieces_per_pack`, v.`base_unit_price`
             FROM `variant_packs` vp
             JOIN `product_variants` v ON v.`id` = vp.`variant_id`
             WHERE ABS(vp.`base_price` - (vp.`pieces_per_pack` * v.`base_unit_price`)) > 0.01
             LIMIT 5"
        )->fetchAll();

        $this->assert(
            $rows === [],
            'Pack price = pieces x unit price',
            $rows === []
                ? 'no volume discount is baked into a pack price (tiers own that)'
                : count($rows) . ' packs diverge, e.g. ' . $rows[0]['sku']
        );
    }

    /** Available stock must never be negative. */
    private function checkInventory(): void
    {
        $negative = (int) $this->pdo->query(
            'SELECT COUNT(*) FROM `inventory` WHERE `quantity_on_hand` - `quantity_reserved` < 0'
        )->fetchColumn();

        $missing = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM `product_variants` v
             WHERE v.`status` = 'active'
               AND NOT EXISTS (SELECT 1 FROM `inventory` i WHERE i.`variant_id` = v.`id`)"
        )->fetchColumn();

        $this->assert(
            $negative === 0 && $missing === 0,
            'Inventory rows sane',
            $negative === 0 && $missing === 0
                ? (int) $this->pdo->query('SELECT COUNT(*) FROM `inventory`')->fetchColumn()
                  . ' stock rows, none negative, every active variant covered'
                : "{$negative} negative, {$missing} active variants without a stock row"
        );
    }

    /** All demo catalog rows must be removable in one step. */
    private function checkDemoFlagging(): void
    {
        $unflagged = [];
        foreach (['products', 'product_variants', 'variant_packs', 'quantity_price_tiers', 'inventory'] as $table) {
            $count = (int) $this->pdo->query("SELECT COUNT(*) FROM `{$table}` WHERE `is_demo_data` = 0")->fetchColumn();
            if ($count > 0) {
                $unflagged[] = "{$table}:{$count}";
            }
        }

        $this->assert(
            $unflagged === [],
            'Demo data is fully flagged',
            $unflagged === []
                ? 'every catalog row carries is_demo_data = 1'
                : 'unflagged rows in ' . implode(', ', $unflagged)
        );
    }

    /** The views the AI service reads must exist and return rows. */
    private function checkReadOnlyViews(): void
    {
        $searchable = (int) $this->pdo->query('SELECT COUNT(*) FROM `v_searchable_products`')->fetchColumn();
        $availability = (int) $this->pdo->query('SELECT COUNT(*) FROM `v_variant_availability`')->fetchColumn();
        $this->pdo->query('SELECT COUNT(*) FROM `v_order_item_facts`')->fetchColumn();

        // The AI must never be able to read a price.
        $columns = $this->pdo->query(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v_searchable_products'
               AND (COLUMN_NAME LIKE '%price%' OR COLUMN_NAME LIKE '%mrp%')"
        )->fetchAll(PDO::FETCH_COLUMN);

        $this->assert(
            $searchable > 0 && $availability > 0 && $columns === [],
            'AI read-only views expose no prices',
            $columns === []
                ? "{$searchable} searchable SKUs, {$availability} stock rows, zero price columns exposed"
                : 'price columns leaked: ' . implode(', ', $columns)
        );
    }
}
