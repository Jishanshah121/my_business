-- Bulk quote request -> admin pricing -> customer acceptance -> order.
-- When a quote is accepted the resulting order carries the QUOTED prices as
-- its snapshot: PriceResolver is deliberately bypassed, and the quote id is
-- stamped on the order for audit.
CREATE TABLE `quotes` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`                CHAR(36) NOT NULL,
    `quote_number`        VARCHAR(32) NOT NULL,
    `user_id`             BIGINT UNSIGNED NULL,
    `business_profile_id` BIGINT UNSIGNED NULL,

    -- Captured even for guests, so sales can follow up
    `company_name`        VARCHAR(191) NULL,
    `contact_name`        VARCHAR(128) NOT NULL,
    `contact_email`       VARCHAR(191) NOT NULL,
    `contact_phone`       VARCHAR(20)  NOT NULL,
    `gstin`               VARCHAR(15)  NULL,

    `status`              ENUM('draft','submitted','under_review','sent','negotiating',
                               'accepted','converted','rejected','expired')
                          NOT NULL DEFAULT 'draft',
    `required_by`         DATE NULL,
    `delivery_pincode`    CHAR(6) NULL,
    `delivery_state_code` CHAR(2) NULL,
    `delivery_city`       VARCHAR(96) NULL,
    `customer_notes`      VARCHAR(1000) NULL,
    `internal_notes`      VARCHAR(1000) NULL,

    `items_subtotal`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount_total`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `taxable_value`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tax_total`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_total`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `grand_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    `payment_terms`       VARCHAR(255) NULL,
    `delivery_terms`      VARCHAR(255) NULL,
    `valid_until`         DATE NULL,
    `pdf_path`            VARCHAR(255) NULL,

    `assigned_to`         BIGINT UNSIGNED NULL COMMENT 'Sales manager handling this quote',
    `sent_at`             DATETIME NULL,
    `accepted_at`         DATETIME NULL,
    `converted_order_id`  BIGINT UNSIGNED NULL,
    `rejection_reason`    VARCHAR(500) NULL,

    `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`          DATETIME NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quotes_uuid` (`uuid`),
    UNIQUE KEY `uq_quotes_number` (`quote_number`),
    KEY `ix_quotes_user` (`user_id`, `created_at`),
    KEY `ix_quotes_status` (`status`, `created_at`),
    KEY `ix_quotes_assigned` (`assigned_to`, `status`),
    CONSTRAINT `fk_quotes_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_quotes_business`
        FOREIGN KEY (`business_profile_id`) REFERENCES `business_profiles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_quotes_assigned`
        FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_quotes_order`
        FOREIGN KEY (`converted_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `quote_items` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quote_id`         BIGINT UNSIGNED NOT NULL,
    `variant_pack_id`  BIGINT UNSIGNED NULL,
    `sku`              VARCHAR(64)  NULL,
    `description`      VARCHAR(500) NOT NULL COMMENT 'Free text for items not yet in the catalog',
    `pack_qty`         INT UNSIGNED NOT NULL,
    `pieces_per_pack`  INT UNSIGNED NOT NULL DEFAULT 1,
    `base_qty`         INT UNSIGNED NOT NULL,
    `system_unit_price` DECIMAL(12,4) NULL COMMENT 'What PriceResolver suggested',
    `quoted_unit_price` DECIMAL(12,4) NOT NULL COMMENT 'What the admin actually offered',
    `price_change_reason` VARCHAR(255) NULL,
    `line_subtotal`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `line_discount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `hsn_code`         VARCHAR(10) NULL,
    `gst_rate`         DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `tax_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `line_total`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sort_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_quote_items_quote` (`quote_id`),
    KEY `ix_quote_items_pack` (`variant_pack_id`),
    CONSTRAINT `fk_quote_items_quote`
        FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quote_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `quote_status_history` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quote_id`    BIGINT UNSIGNED NOT NULL,
    `from_status` VARCHAR(32) NULL,
    `to_status`   VARCHAR(32) NOT NULL,
    `comment`     VARCHAR(500) NULL,
    `actor_type`  ENUM('customer','staff','system') NOT NULL DEFAULT 'system',
    `actor_id`    BIGINT UNSIGNED NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_quote_status_history_quote` (`quote_id`, `created_at`),
    CONSTRAINT `fk_quote_status_history_quote`
        FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quote_status_history_actor`
        FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
