-- Level 1 of the three-level catalog (ADR-003):
--   products  ->  product_variants (physical spec + inventory)  ->  variant_packs (the SKU)
-- The product itself is the marketing entity and carries no price or stock.
CREATE TABLE `products` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`              CHAR(36)     NOT NULL,
    `sku_root`          VARCHAR(48)  NOT NULL COMMENT 'Prefix shared by all child pack SKUs',
    `category_id`       BIGINT UNSIGNED NOT NULL,
    `brand_id`          BIGINT UNSIGNED NULL,
    `name`              VARCHAR(191) NOT NULL,
    `slug`              VARCHAR(200) NOT NULL,
    `short_description` VARCHAR(500) NULL,
    `long_description`  MEDIUMTEXT   NULL,
    `usage_notes`       TEXT         NULL COMMENT 'Microwave safe, leak proof, oven temp, etc.',
    `search_keywords`   VARCHAR(500) NULL COMMENT 'Hinglish and trade synonyms: dona, pattal, chamach',

    -- Physical / marketing attributes shared by every variant
    `material`          VARCHAR(64)  NULL,
    `unit_type`         VARCHAR(24)  NOT NULL DEFAULT 'piece' COMMENT 'piece | roll | sheet | metre | kg',

    -- Tax
    `hsn_code`          VARCHAR(10)  NOT NULL,

    -- Merchandising flags
    `is_eco`            TINYINT(1) NOT NULL DEFAULT 0,
    `is_compostable`    TINYINT(1) NOT NULL DEFAULT 0,
    `is_recyclable`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_biodegradable`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_featured`       TINYINT(1) NOT NULL DEFAULT 0,
    `is_bestseller`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_new_arrival`    TINYINT(1) NOT NULL DEFAULT 0,
    `is_b2b_only`       TINYINT(1) NOT NULL DEFAULT 0,

    `status`            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`      DATETIME NULL DEFAULT NULL,

    -- Denormalised rollups, refreshed on variant/review write
    `rating_average`    DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `rating_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `view_count`        INT UNSIGNED NOT NULL DEFAULT 0,
    `sort_order`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    -- SEO
    `seo_title`         VARCHAR(191) NULL,
    `seo_description`   VARCHAR(320) NULL,
    `canonical_url`     VARCHAR(255) NULL,

    `is_demo_data`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'DEMO/PLACEHOLDER seed row — safe to delete',
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_uuid` (`uuid`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    UNIQUE KEY `uq_products_sku_root` (`sku_root`),
    KEY `ix_products_category_status` (`category_id`, `status`, `deleted_at`),
    KEY `ix_products_status_published` (`status`, `published_at`),
    KEY `ix_products_featured` (`is_featured`, `status`),
    KEY `ix_products_bestseller` (`is_bestseller`, `status`),
    KEY `ix_products_eco` (`is_eco`, `status`),
    KEY `ix_products_brand` (`brand_id`),
    KEY `ix_products_hsn` (`hsn_code`),
    FULLTEXT KEY `ft_products_search` (`name`, `short_description`, `search_keywords`),
    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_products_brand`
        FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
