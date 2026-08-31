<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Config;
use Database\Seeder;

/**
 * Runtime configuration rows.
 *
 * Anything a business owner might reasonably want to change without calling a
 * developer belongs here — seller identity, GST behaviour, event safety
 * margins, order limits, free-shipping thresholds. config/*.php only supplies
 * the bootstrap defaults these are seeded from.
 */
final class SettingsSeeder extends Seeder
{
    public function order(): int
    {
        return 10;
    }

    public function run(): void
    {
        $seller = Config::get('tax.seller');

        $settings = [
            // --- Seller identity (ADR: env/admin configurable, never hardcoded) ---
            ['seller', 'legal_name',  $seller['legal_name'],  'string',  'Legal entity name', 'Name printed on GST invoices', 1],
            ['seller', 'trade_name',  $seller['trade_name'],  'string',  'Trade name', 'Brand name shown to customers', 1],
            ['seller', 'gstin',       $seller['gstin'],       'string',  'Seller GSTIN', 'PLACEHOLDER — replace before going live', 0],
            ['seller', 'pan',         $seller['pan'],         'string',  'Seller PAN', 'PLACEHOLDER', 0],
            ['seller', 'state_code',  $seller['state_code'],  'string',  'Seller state code', 'Drives GST place of supply. 27 = Maharashtra', 0],
            ['seller', 'state_name',  $seller['state_name'],  'string',  'Seller state', '', 1],
            ['seller', 'support_email', 'support@supplykaro.test', 'string', 'Support email', '', 1],
            ['seller', 'support_phone', '+91 99999 99999', 'string', 'Support phone', 'PLACEHOLDER', 1],
            ['seller', 'whatsapp_number', '919999999999', 'string', 'WhatsApp number', 'Digits with country code, no plus. PLACEHOLDER', 1],

            // --- Tax ---
            ['tax', 'gst_enabled',        '1',  'boolean', 'GST enabled', '', 0],
            ['tax', 'prices_include_tax', '0',  'boolean', 'Store prices inclusive of tax', 'Storage convention. Display is per customer group.', 0],
            ['tax', 'default_gst_rate',   '18.00', 'decimal', 'Default GST rate', 'Used when an HSN code has no tax_rates row', 0],
            ['tax', 'invoice_prefix',     'INV', 'string', 'Invoice prefix', '', 0],
            ['tax', 'fy_start_month',     '4',   'integer', 'Financial year start month', 'April in India', 0],
            ['tax', 'enable_round_off',   '1',   'boolean', 'Round invoice totals to the nearest rupee', '', 0],

            // --- Ordering ---
            ['order', 'min_order_value',        '0.00',   'decimal', 'Minimum order value (B2C)', 'Rupees. 0 = no minimum', 1],
            ['order', 'min_order_value_b2b',    '2000.00','decimal', 'Minimum order value (B2B)', '', 1],
            ['order', 'guest_checkout_enabled', '1',      'boolean', 'Allow guest checkout', '', 1],
            ['order', 'cart_expiry_days',       '30',     'integer', 'Days before an inactive cart expires', '', 0],
            ['order', 'unpaid_order_expiry_minutes', '60','integer', 'Release reserved stock after this long unpaid', '', 0],
            ['order', 'number_prefix',          'SK',     'string',  'Order number prefix', '', 0],

            // --- Shipping ---
            ['shipping', 'free_shipping_threshold', '2500.00', 'decimal', 'Free shipping above', 'Rupees, ex-GST', 1],
            ['shipping', 'volumetric_divisor',      '5000',    'integer', 'Volumetric weight divisor', '(L x W x H cm) / divisor = kg. Critical for bulky disposables.', 0],
            ['shipping', 'origin_pincode',          '400001',  'string',  'Dispatch pincode', '', 0],
            ['shipping', 'cod_enabled',             '1',       'boolean', 'Cash on delivery available', '', 1],
            ['shipping', 'cod_max_order_value',     '25000.00','decimal', 'Maximum COD order value', '', 1],

            // --- Event calculator (see EventRuleSeeder for the per-role rules) ---
            ['events', 'default_safety_margin_pct', '15.00', 'decimal', 'Default safety margin', 'Applied when a rule does not set its own', 0],
            ['events', 'max_guest_count',           '10000', 'integer', 'Maximum guests the calculator accepts', '', 1],
            ['events', 'default_rounding_step',     '10',    'integer', 'Round quantities up to a multiple of', '', 0],

            // --- B2B ---
            ['b2b', 'auto_approve',            '0',   'boolean', 'Auto-approve business accounts', 'Leave off: approval gates B2B pricing and credit', 0],
            ['b2b', 'require_gstin',           '1',   'boolean', 'GSTIN required to register as a business', '', 1],
            ['b2b', 'quote_validity_days',     '15',  'integer', 'Default quote validity', '', 0],
            ['b2b', 'credit_enabled_globally', '0',   'boolean', 'Allow credit terms at all', 'Per-account credit still needs approval', 0],

            // --- Catalog & search ---
            ['catalog', 'products_per_page',      '24', 'integer', 'Products per listing page', '', 1],
            ['catalog', 'low_stock_threshold',    '100','integer', 'Default low-stock threshold (base units)', '', 0],
            ['catalog', 'show_out_of_stock',      '1',  'boolean', 'Show out-of-stock products in listings', '', 1],
            ['catalog', 'reviews_require_approval','1', 'boolean', 'Moderate reviews before publishing', '', 0],

            // --- Homepage merchandising ---
            ['homepage', 'hero_heading',  'Everything you need to serve, pack & celebrate.', 'string', 'Hero heading', '', 1],
            ['homepage', 'hero_subtext',  'From everyday takeaway supplies to 10,000-guest celebrations.', 'string', 'Hero subtext', '', 1],
            ['homepage', 'bestseller_count', '8',  'integer', 'Bestsellers shown on the home page', '', 1],

            // --- AI service (off until Phase 8) ---
            ['ai', 'search_enabled',     '0', 'boolean', 'Use the AI search service', 'Falls back to MySQL search when off or unreachable', 0],
            ['ai', 'recommend_enabled',  '0', 'boolean', 'Use AI recommendations', '', 0],
        ];

        $count = 0;
        foreach ($settings as [$group, $key, $value, $type, $label, $description, $isPublic]) {
            $this->upsert(
                'settings',
                ['group' => $group, 'key' => $key],
                [
                    'value'       => (string) $value,
                    'type'        => $type,
                    'label'       => $label,
                    'description' => $description !== '' ? $description : null,
                    'is_public'   => $isPublic,
                ]
            );
            $count++;
        }

        $this->info("{$count} settings across " . count(array_unique(array_column($settings, 0))) . ' groups');
    }
}
