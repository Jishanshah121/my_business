-- Step 6: cart-level only. A coupon NEVER modifies a unit price — it is
-- allocated back across lines pro-rata so GST is charged on the actually
-- discounted value per HSN code.
CREATE TABLE `coupons` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`               VARCHAR(48)  NOT NULL,
    `name`               VARCHAR(128) NOT NULL,
    `description`        VARCHAR(500) NULL,
    `discount_type`      ENUM('percentage','fixed_amount','free_shipping') NOT NULL,
    `discount_value`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `max_discount`       DECIMAL(12,2) NULL,
    `min_cart_value`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `applies_to`         ENUM('cart','products','categories') NOT NULL DEFAULT 'cart',
    `audience`           ENUM('all','b2c_only','b2b_only','first_order','specific_users') NOT NULL DEFAULT 'all',
    `usage_limit_total`  INT UNSIGNED NULL,
    `usage_limit_user`   INT UNSIGNED NOT NULL DEFAULT 1,
    `used_count`         INT UNSIGNED NOT NULL DEFAULT 0,
    `stackable`          TINYINT(1) NOT NULL DEFAULT 0,
    `starts_at`          DATETIME NOT NULL,
    `ends_at`            DATETIME NULL,
    `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
    `created_by`         BIGINT UNSIGNED NULL,
    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`         DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_coupons_code` (`code`),
    KEY `ix_coupons_active` (`is_active`, `starts_at`, `ends_at`),
    CONSTRAINT `fk_coupons_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_coupons_value` CHECK (`discount_value` >= 0 AND `min_cart_value` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Include/exclude lists for product-, category- and user-scoped coupons.
CREATE TABLE `coupon_conditions` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id`   BIGINT UNSIGNED NOT NULL,
    `target_type` ENUM('product','category','variant_pack','customer','customer_group','business_type') NOT NULL,
    `target_id`   BIGINT UNSIGNED NOT NULL,
    `mode`        ENUM('include','exclude') NOT NULL DEFAULT 'include',
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_coupon_conditions` (`coupon_id`, `target_type`, `target_id`, `mode`),
    KEY `ix_coupon_conditions_target` (`target_type`, `target_id`),
    CONSTRAINT `fk_coupon_conditions_coupon`
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `coupon_usages` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id`       BIGINT UNSIGNED NOT NULL,
    `user_id`         BIGINT UNSIGNED NULL,
    `order_id`        BIGINT UNSIGNED NULL COMMENT 'Set when the order is created',
    `discount_amount` DECIMAL(12,2) NOT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_coupon_usages_coupon_user` (`coupon_id`, `user_id`),
    KEY `ix_coupon_usages_order` (`order_id`),
    CONSTRAINT `fk_coupon_usages_coupon`
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_coupon_usages_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
