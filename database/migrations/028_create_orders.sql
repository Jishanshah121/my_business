CREATE TABLE `orders` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`               CHAR(36) NOT NULL,
    `order_number`       VARCHAR(32) NOT NULL COMMENT 'Customer-facing, e.g. SK-2627-000123',
    `user_id`            BIGINT UNSIGNED NULL COMMENT 'NULL for guest checkout',
    `customer_group_id`  BIGINT UNSIGNED NOT NULL,
    `business_profile_id` BIGINT UNSIGNED NULL,
    `quote_id`           BIGINT UNSIGNED NULL COMMENT 'Set when converted from a quote',

    `guest_email`        VARCHAR(191) NULL,
    `guest_phone`        VARCHAR(20)  NULL,

    `status`             ENUM('pending','confirmed','processing','packed','shipped',
                              'out_for_delivery','delivered','cancelled','returned','refunded')
                         NOT NULL DEFAULT 'pending',
    `payment_status`     ENUM('unpaid','authorised','paid','partially_refunded','refunded','failed')
                         NOT NULL DEFAULT 'unpaid',
    `fulfilment_status`  ENUM('unfulfilled','partial','fulfilled') NOT NULL DEFAULT 'unfulfilled',
    `channel`            ENUM('web','mobile','admin','whatsapp','phone') NOT NULL DEFAULT 'web',

    -- GST context, snapshotted so a later settings change cannot alter history
    `seller_gstin`       VARCHAR(15) NULL,
    `seller_state_code`  CHAR(2) NOT NULL,
    `place_of_supply`    CHAR(2) NOT NULL COMMENT 'Ship-to state code',
    `is_interstate`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = IGST, 0 = CGST+SGST',
    `buyer_gstin`        VARCHAR(15) NULL,

    -- Money. All DECIMAL(12,2); computed server-side, never from the client.
    `currency`           CHAR(3) NOT NULL DEFAULT 'INR',
    `items_subtotal`     DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Sum of line taxable values before order discount',
    `discount_total`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `coupon_id`          BIGINT UNSIGNED NULL,
    `coupon_code`        VARCHAR(48) NULL,
    `taxable_value`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cgst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sgst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `igst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cess_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tax_total`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_total`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_tax`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `round_off`          DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `grand_total`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `amount_paid`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `amount_refunded`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    `total_base_units`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total pieces, for reporting',
    `total_weight_g`     DECIMAL(12,3) NOT NULL DEFAULT 0.000,

    `payment_method`     VARCHAR(32) NULL,
    `shipping_method`    VARCHAR(64) NULL,
    `customer_note`      VARCHAR(1000) NULL,
    `internal_note`      VARCHAR(1000) NULL,
    `requires_gst_invoice` TINYINT(1) NOT NULL DEFAULT 0,

    `placed_at`          DATETIME NULL,
    `confirmed_at`       DATETIME NULL,
    `shipped_at`         DATETIME NULL,
    `delivered_at`       DATETIME NULL,
    `cancelled_at`       DATETIME NULL,
    `cancellation_reason` VARCHAR(255) NULL,

    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_uuid` (`uuid`),
    UNIQUE KEY `uq_orders_number` (`order_number`),
    KEY `ix_orders_user_created` (`user_id`, `created_at`),
    KEY `ix_orders_status` (`status`, `created_at`),
    KEY `ix_orders_payment_status` (`payment_status`),
    KEY `ix_orders_business` (`business_profile_id`, `created_at`),
    KEY `ix_orders_placed` (`placed_at`),
    CONSTRAINT `fk_orders_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_business_profile`
        FOREIGN KEY (`business_profile_id`) REFERENCES `business_profiles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_orders_coupon`
        FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
