-- One review per order item prevents the same purchase being reviewed twice
-- (brief section 30). order_item_id is NULL only for admin-imported reviews.
CREATE TABLE `reviews` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`     BIGINT UNSIGNED NOT NULL,
    `user_id`        BIGINT UNSIGNED NULL,
    `order_item_id`  BIGINT UNSIGNED NULL,
    `rating`         TINYINT UNSIGNED NOT NULL,
    `title`          VARCHAR(191) NULL,
    `body`           TEXT NULL,
    `author_name`    VARCHAR(128) NOT NULL,
    `is_verified_purchase` TINYINT(1) NOT NULL DEFAULT 0,
    `status`         ENUM('pending','approved','rejected','hidden') NOT NULL DEFAULT 'pending',
    `moderated_by`   BIGINT UNSIGNED NULL,
    `moderated_at`   DATETIME NULL,
    `rejection_reason` VARCHAR(255) NULL,
    `helpful_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `unhelpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `admin_reply`    TEXT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`     DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reviews_order_item` (`order_item_id`),
    KEY `ix_reviews_product_status` (`product_id`, `status`, `created_at`),
    KEY `ix_reviews_user` (`user_id`),
    CONSTRAINT `fk_reviews_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_order_item`
        FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_moderated_by`
        FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `review_images` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `review_id`  BIGINT UNSIGNED NOT NULL,
    `path`       VARCHAR(255) NOT NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_review_images_review` (`review_id`),
    CONSTRAINT `fk_review_images_review`
        FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `review_votes` (
    `review_id`  BIGINT UNSIGNED NOT NULL,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `is_helpful` TINYINT(1) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`review_id`, `user_id`),
    CONSTRAINT `fk_review_votes_review`
        FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_votes_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
