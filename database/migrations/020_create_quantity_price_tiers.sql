-- Step 3 of the pricing waterfall. min_base_qty is expressed in BASE UNITS
-- (pieces), because a customer buying 10 sleeves of 100 should get the same
-- rate as one buying 1 carton of 1000.
--
-- scope_id is intentionally not foreign-keyed: the scope is polymorphic across
-- four tables. Referential integrity is enforced in PriceResolver and by an
-- admin-side validator, and the composite index below is what makes lookup
-- fast. This is the one place we trade a database constraint for flexibility.
CREATE TABLE `quantity_price_tiers` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_type`        ENUM('pack','variant','product','category') NOT NULL,
    `scope_id`          BIGINT UNSIGNED NOT NULL,
    `customer_group_id` BIGINT UNSIGNED NULL COMMENT 'NULL = applies to every group',
    `min_base_qty`      INT UNSIGNED NOT NULL,
    `max_base_qty`      INT UNSIGNED NULL,
    `unit_price`        DECIMAL(12,4) NOT NULL COMMENT 'Price per BASE UNIT at this tier',
    `label`             VARCHAR(96) NULL COMMENT 'Optional display label, e.g. "Bulk rate"',
    `is_exclusive`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'If matched, wins outright (waterfall step 5)',
    `valid_from`        DATE NULL,
    `valid_to`          DATE NULL,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `is_demo_data`      TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quantity_price_tiers` (`scope_type`, `scope_id`, `customer_group_id`, `min_base_qty`),
    KEY `ix_quantity_price_tiers_lookup`
        (`scope_type`, `scope_id`, `customer_group_id`, `is_active`, `min_base_qty`),
    CONSTRAINT `fk_quantity_price_tiers_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_quantity_price_tiers`
        CHECK (`min_base_qty` > 0 AND `unit_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
