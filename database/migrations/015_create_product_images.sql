CREATE TABLE `product_images` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`   BIGINT UNSIGNED NOT NULL,
    `variant_id`   BIGINT UNSIGNED NULL COMMENT 'NULL = applies to the whole product',
    `path`         VARCHAR(255) NOT NULL,
    `thumbnail_path` VARCHAR(255) NULL,
    `alt_text`     VARCHAR(191) NOT NULL,
    `width`        SMALLINT UNSIGNED NULL,
    `height`       SMALLINT UNSIGNED NULL,
    `mime_type`    VARCHAR(48)  NULL,
    `bytes`        INT UNSIGNED NULL,
    `is_primary`   TINYINT(1) NOT NULL DEFAULT 0,
    `is_placeholder` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Generated stand-in, not real photography',
    `sort_order`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_product_images_product` (`product_id`, `sort_order`),
    KEY `ix_product_images_variant` (`variant_id`),
    KEY `ix_product_images_primary` (`product_id`, `is_primary`),
    CONSTRAINT `fk_product_images_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_images_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
