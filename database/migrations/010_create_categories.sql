-- Self-referencing tree of unlimited depth. `path` and `depth` are maintained
-- by the application so a breadcrumb or a whole subtree is one indexed read
-- rather than a recursive walk.
CREATE TABLE `categories` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`       BIGINT UNSIGNED NULL,
    `name`            VARCHAR(128) NOT NULL,
    `slug`            VARCHAR(160) NOT NULL,
    `path`            VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'Materialised ancestor ids, e.g. /1/7/23/',
    `depth`           TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `description`     TEXT         NULL,
    `banner_path`     VARCHAR(255) NULL,
    `icon_path`       VARCHAR(255) NULL,
    `seo_title`       VARCHAR(191) NULL,
    `seo_description` VARCHAR(320) NULL,
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_nav`     TINYINT(1) NOT NULL DEFAULT 1,
    `product_count`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Denormalised, refreshed on write',
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `ix_categories_parent` (`parent_id`, `sort_order`),
    KEY `ix_categories_active` (`is_active`, `deleted_at`),
    KEY `ix_categories_path` (`path`(191)),
    CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
