CREATE TABLE `carts` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `token`             CHAR(64) NOT NULL COMMENT 'Opaque guest-cart token stored in an HttpOnly cookie',
    `user_id`           BIGINT UNSIGNED NULL,
    `customer_group_id` BIGINT UNSIGNED NOT NULL,
    `currency`          CHAR(3) NOT NULL DEFAULT 'INR',
    `status`            ENUM('active','converted','abandoned','merged') NOT NULL DEFAULT 'active',
    `coupon_id`         BIGINT UNSIGNED NULL,
    `ship_to_state_code` CHAR(2) NULL COMMENT 'Set once an address is chosen; drives GST preview',
    `ship_to_pincode`   CHAR(6) NULL,
    `notes`             VARCHAR(500) NULL,
    `converted_order_id` BIGINT UNSIGNED NULL,
    `last_activity_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`        DATETIME NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_carts_token` (`token`),
    KEY `ix_carts_user_status` (`user_id`, `status`),
    KEY `ix_carts_abandoned` (`status`, `last_activity_at`),
    CONSTRAINT `fk_carts_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_carts_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_carts_coupon`
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- NOTE: there is deliberately NO price column here.
-- The cart stores identifiers and quantities only; every render recomputes
-- through PriceResolver. A tampered request can change what is in the cart,
-- never what it costs.
CREATE TABLE `cart_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cart_id`         BIGINT UNSIGNED NOT NULL,
    `variant_pack_id` BIGINT UNSIGNED NOT NULL,
    `pack_qty`        INT UNSIGNED NOT NULL,
    `added_via`       ENUM('manual','bundle','event_kit','party_box','reorder','quote','recommendation')
                      NOT NULL DEFAULT 'manual',
    `source_ref`      VARCHAR(64) NULL COMMENT 'bundle id, event plan id, previous order number',
    `note`            VARCHAR(255) NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cart_items` (`cart_id`, `variant_pack_id`),
    KEY `ix_cart_items_pack` (`variant_pack_id`),
    CONSTRAINT `fk_cart_items_cart`
        FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_cart_items_qty` CHECK (`pack_qty` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
