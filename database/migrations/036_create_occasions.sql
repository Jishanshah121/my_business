-- Shop by Occasion: Wedding, Birthday, House Party, Corporate, Catering,
-- Pooja, Festival, Picnic, Office Party, School Event, College Event, Food Stall.
CREATE TABLE `occasions` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`            VARCHAR(48)  NOT NULL,
    `name`            VARCHAR(128) NOT NULL,
    `slug`            VARCHAR(160) NOT NULL,
    `tagline`         VARCHAR(255) NULL,
    `description`     TEXT NULL,
    `banner_path`     VARCHAR(255) NULL,
    `icon_path`       VARCHAR(255) NULL,
    `default_guest_count` INT UNSIGNED NULL COMMENT 'Pre-fills the event calculator',
    `seo_title`       VARCHAR(191) NULL,
    `seo_description` VARCHAR(320) NULL,
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_nav`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_occasions_code` (`code`),
    UNIQUE KEY `uq_occasions_slug` (`slug`),
    KEY `ix_occasions_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `occasion_products` (
    `occasion_id` BIGINT UNSIGNED NOT NULL,
    `product_id`  BIGINT UNSIGNED NOT NULL,
    `rank`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`occasion_id`, `product_id`),
    KEY `ix_occasion_products_product` (`product_id`),
    CONSTRAINT `fk_occasion_products_occasion`
        FOREIGN KEY (`occasion_id`) REFERENCES `occasions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_occasion_products_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `business_type_products` (
    `business_type_id` BIGINT UNSIGNED NOT NULL,
    `product_id`       BIGINT UNSIGNED NOT NULL,
    `rank`             SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_essential`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Shows in the "what you need" list',
    `is_demo_data`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`business_type_id`, `product_id`),
    KEY `ix_business_type_products_product` (`product_id`),
    CONSTRAINT `fk_btp_business_type`
        FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_btp_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Bundles reference occasions; the FK is added here now that occasions exist.
ALTER TABLE `bundles`
    ADD CONSTRAINT `fk_bundles_occasion`
        FOREIGN KEY (`occasion_id`) REFERENCES `occasions` (`id`) ON DELETE SET NULL;
