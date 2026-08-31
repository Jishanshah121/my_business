-- Restaurant, Cafe, Cloud Kitchen, Caterer, Hotel, Bakery, Office, Event
-- Planner, Retailer, Distributor. Doubles as the "Shop by Business" landing
-- page entity, so it carries its own content and SEO fields.
CREATE TABLE `business_types` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`             VARCHAR(48)  NOT NULL,
    `name`             VARCHAR(128) NOT NULL,
    `slug`             VARCHAR(160) NOT NULL,
    `tagline`          VARCHAR(255) NULL,
    `description`      TEXT         NULL,
    `what_you_need`    TEXT         NULL COMMENT 'Landing page copy: what this business buys',
    `banner_path`      VARCHAR(255) NULL,
    `icon_path`        VARCHAR(255) NULL,
    `seo_title`        VARCHAR(191) NULL,
    `seo_description`  VARCHAR(320) NULL,
    `sort_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`        TINYINT(1)   NOT NULL DEFAULT 1,
    `show_in_nav`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_business_types_code` (`code`),
    UNIQUE KEY `uq_business_types_slug` (`slug`),
    KEY `ix_business_types_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
