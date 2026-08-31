-- Level 2. The physical thing. Inventory is held here, in BASE UNITS
-- (pieces), never at pack level — this is what makes split-stock impossible.
CREATE TABLE `product_variants` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `name`            VARCHAR(128) NOT NULL COMMENT 'e.g. "150 ml" or "9 inch / White"',
    `variant_code`    VARCHAR(48)  NOT NULL COMMENT 'Suffix appended to sku_root',

    -- Physical spec
    `size_label`      VARCHAR(48)  NULL COMMENT '9 inch, 27x27 cm, 300 m',
    `capacity_ml`     INT UNSIGNED NULL,
    `diameter_mm`     INT UNSIGNED NULL,
    `length_mm`       INT UNSIGNED NULL,
    `width_mm`        INT UNSIGNED NULL,
    `height_mm`       INT UNSIGNED NULL,
    `unit_weight_g`   DECIMAL(10,3) NULL COMMENT 'Weight of ONE base unit',
    `colour`          VARCHAR(48)  NULL,
    `ply`             TINYINT UNSIGNED NULL COMMENT 'For tissue and napkin products',
    `gsm`             SMALLINT UNSIGNED NULL COMMENT 'Paper weight',

    -- Base-unit list price, before any tier, contract or campaign is applied.
    -- 4 decimals because sub-paise per-piece rates are real at wholesale.
    `base_unit_price` DECIMAL(12,4) NOT NULL,
    `mrp_unit_price`  DECIMAL(12,4) NULL COMMENT 'Struck-through reference price',

    `is_default`      TINYINT(1) NOT NULL DEFAULT 0,
    `status`          ENUM('active','inactive','discontinued') NOT NULL DEFAULT 'active',
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_variants_code` (`product_id`, `variant_code`),
    KEY `ix_product_variants_product` (`product_id`, `status`, `sort_order`),
    KEY `ix_product_variants_default` (`product_id`, `is_default`),
    CONSTRAINT `fk_product_variants_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_product_variants_price`
        CHECK (`base_unit_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
