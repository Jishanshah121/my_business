CREATE TABLE `brands` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(128) NOT NULL,
    `slug`            VARCHAR(160) NOT NULL,
    `description`     TEXT         NULL,
    `logo_path`       VARCHAR(255) NULL,
    `seo_title`       VARCHAR(191) NULL,
    `seo_description` VARCHAR(320) NULL,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_brands_slug` (`slug`),
    KEY `ix_brands_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
