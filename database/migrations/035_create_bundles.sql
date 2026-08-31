-- Cafe Starter Kit, Restaurant Essentials, Bakery Packaging Kit and so on.
-- pricing_mode decides whether the bundle has a fixed price, a percentage off
-- the computed component total, or simply sells components at their own price.
CREATE TABLE `bundles` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(191) NOT NULL,
    `slug`             VARCHAR(200) NOT NULL,
    `sku`              VARCHAR(48)  NOT NULL,
    `tagline`          VARCHAR(255) NULL,
    `description`      TEXT NULL,
    `banner_path`      VARCHAR(255) NULL,
    `business_type_id` BIGINT UNSIGNED NULL,
    `occasion_id`      BIGINT UNSIGNED NULL,
    `pricing_mode`     ENUM('fixed_price','percentage_off','component_sum') NOT NULL DEFAULT 'percentage_off',
    `fixed_price`      DECIMAL(12,2) NULL,
    `discount_pct`     DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `show_components`  TINYINT(1) NOT NULL DEFAULT 1,
    `allow_partial`    TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Can the customer drop an item?',
    `status`           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `is_featured`      TINYINT(1) NOT NULL DEFAULT 0,
    `seo_title`        VARCHAR(191) NULL,
    `seo_description`  VARCHAR(320) NULL,
    `sort_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bundles_slug` (`slug`),
    UNIQUE KEY `uq_bundles_sku` (`sku`),
    KEY `ix_bundles_status` (`status`, `sort_order`),
    KEY `ix_bundles_business_type` (`business_type_id`),
    CONSTRAINT `fk_bundles_business_type`
        FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bundle_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bundle_id`       BIGINT UNSIGNED NOT NULL,
    `variant_pack_id` BIGINT UNSIGNED NOT NULL,
    `pack_qty`        INT UNSIGNED NOT NULL DEFAULT 1,
    `is_required`     TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bundle_items` (`bundle_id`, `variant_pack_id`),
    KEY `ix_bundle_items_pack` (`variant_pack_id`),
    CONSTRAINT `fk_bundle_items_bundle`
        FOREIGN KEY (`bundle_id`) REFERENCES `bundles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bundle_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `ck_bundle_items_qty` CHECK (`pack_qty` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
