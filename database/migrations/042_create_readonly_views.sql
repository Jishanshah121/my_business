-- Views the Python AI service is allowed to read. It gets SELECT on the
-- catalog tables plus these — and no write grant anywhere, so the boundary is
-- enforced by MySQL rather than by discipline.

-- Anonymised co-purchase facts. No customer identity, no addresses, no
-- contact details: just which SKUs appear together in which order.
CREATE OR REPLACE VIEW `v_order_item_facts` AS
SELECT
    oi.`order_id`,
    oi.`product_id`,
    oi.`variant_pack_id`,
    oi.`base_qty`,
    o.`customer_group_id`,
    DATE(o.`created_at`) AS `order_date`
FROM `order_items` oi
JOIN `orders` o ON o.`id` = oi.`order_id`
WHERE o.`status` NOT IN ('cancelled', 'returned', 'refunded')
  AND oi.`product_id` IS NOT NULL;

-- One row per sellable SKU with everything the ranker needs to score it.
-- Prices are deliberately EXCLUDED: the AI never sees or returns a price.
CREATE OR REPLACE VIEW `v_searchable_products` AS
SELECT
    p.`id`               AS `product_id`,
    vp.`id`              AS `variant_pack_id`,
    vp.`sku`,
    p.`name`             AS `product_name`,
    pv.`name`            AS `variant_name`,
    vp.`pack_label`,
    vp.`pieces_per_pack`,
    p.`short_description`,
    p.`search_keywords`,
    p.`material`,
    p.`hsn_code`,
    c.`id`               AS `category_id`,
    c.`name`             AS `category_name`,
    c.`path`             AS `category_path`,
    pv.`capacity_ml`,
    pv.`size_label`,
    pv.`colour`,
    p.`is_eco`,
    p.`is_compostable`,
    p.`is_recyclable`,
    p.`is_bestseller`,
    p.`rating_average`,
    p.`rating_count`,
    p.`view_count`
FROM `products` p
JOIN `product_variants` pv ON pv.`product_id` = p.`id` AND pv.`status` = 'active' AND pv.`deleted_at` IS NULL
JOIN `variant_packs` vp    ON vp.`variant_id` = pv.`id` AND vp.`status` = 'active' AND vp.`deleted_at` IS NULL
JOIN `categories` c        ON c.`id` = p.`category_id`
WHERE p.`status` = 'published'
  AND p.`deleted_at` IS NULL;

-- Stock availability in base units. Exposed to the AI as a boolean-ish signal
-- for ranking only; PHP remains the authority on whether an order can be filled.
CREATE OR REPLACE VIEW `v_variant_availability` AS
SELECT
    i.`variant_id`,
    SUM(i.`quantity_on_hand`)                             AS `on_hand`,
    SUM(i.`quantity_reserved`)                            AS `reserved`,
    SUM(i.`quantity_on_hand` - i.`quantity_reserved`)     AS `available`
FROM `inventory` i
GROUP BY i.`variant_id`;
