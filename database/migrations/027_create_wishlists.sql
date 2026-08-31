CREATE TABLE `wishlists` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `name`       VARCHAR(96) NOT NULL DEFAULT 'Saved items',
    `is_default` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_wishlists_user` (`user_id`),
    CONSTRAINT `fk_wishlists_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `wishlist_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `wishlist_id`     BIGINT UNSIGNED NOT NULL,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `variant_pack_id` BIGINT UNSIGNED NULL COMMENT 'NULL = product-level save',
    `note`            VARCHAR(255) NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_wishlist_items` (`wishlist_id`, `product_id`, `variant_pack_id`),
    KEY `ix_wishlist_items_product` (`product_id`),
    CONSTRAINT `fk_wishlist_items_wishlist`
        FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlist_items_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlist_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
