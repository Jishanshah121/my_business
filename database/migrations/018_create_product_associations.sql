-- Cross-sell, upsell and frequently-bought-together.
-- is_curated = 1 rows are admin-pinned and ALWAYS outrank learned rows from
-- the nightly co-occurrence job (cup -> lid -> carrier -> stirrer).
CREATE TABLE `product_associations` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`         BIGINT UNSIGNED NOT NULL,
    `related_product_id` BIGINT UNSIGNED NOT NULL,
    `type`               ENUM('cross_sell','upsell','fbt','accessory','substitute') NOT NULL DEFAULT 'cross_sell',
    `rank`               SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `score`              DECIMAL(6,4) NOT NULL DEFAULT 0.0000 COMMENT 'Learned confidence, 0 for curated',
    `is_curated`         TINYINT(1) NOT NULL DEFAULT 0,
    `source`             VARCHAR(32) NOT NULL DEFAULT 'admin' COMMENT 'admin | cooccurrence | category_rule',
    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_associations` (`product_id`, `related_product_id`, `type`),
    KEY `ix_product_associations_lookup` (`product_id`, `type`, `is_curated`, `rank`),
    CONSTRAINT `fk_product_associations_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_associations_related`
        FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_product_associations_self`
        CHECK (`product_id` <> `related_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
