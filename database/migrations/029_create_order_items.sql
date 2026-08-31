-- Immutable snapshot. Historical orders are reconstructed entirely from these
-- columns and NEVER by joining back to the live catalog, so renaming a product
-- or changing a price can never alter an invoice that has already been issued.
-- The *_id columns exist for reporting joins only and are nullable on purpose.
CREATE TABLE `order_items` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`         BIGINT UNSIGNED NOT NULL,

    -- Soft references, for analytics. Never used to rebuild the line.
    `product_id`       BIGINT UNSIGNED NULL,
    `variant_id`       BIGINT UNSIGNED NULL,
    `variant_pack_id`  BIGINT UNSIGNED NULL,

    -- Snapshot of what was actually sold
    `sku`              VARCHAR(64)  NOT NULL,
    `product_name`     VARCHAR(191) NOT NULL,
    `variant_name`     VARCHAR(128) NULL,
    `pack_label`       VARCHAR(96)  NOT NULL,
    `pieces_per_pack`  INT UNSIGNED NOT NULL,
    `image_path`       VARCHAR(255) NULL,

    `pack_qty`         INT UNSIGNED NOT NULL,
    `base_qty`         INT UNSIGNED NOT NULL COMMENT 'pack_qty * pieces_per_pack',

    -- Money snapshot
    `list_unit_price`  DECIMAL(12,4) NOT NULL COMMENT 'Per base unit, before discounts',
    `unit_price`       DECIMAL(12,4) NOT NULL COMMENT 'Per base unit, price actually charged',
    `pack_price`       DECIMAL(12,2) NOT NULL,
    `line_subtotal`    DECIMAL(12,2) NOT NULL COMMENT 'pack_price * pack_qty',
    `line_discount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Includes pro-rata coupon share',
    `taxable_value`    DECIMAL(12,2) NOT NULL COMMENT 'line_subtotal - line_discount',

    -- Tax snapshot
    `hsn_code`         VARCHAR(10)  NOT NULL,
    `gst_rate`         DECIMAL(5,2) NOT NULL,
    `cgst_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sgst_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `igst_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cess_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `line_total`       DECIMAL(12,2) NOT NULL COMMENT 'taxable_value + all taxes',

    -- Why this price: the PriceResolver trace, for the admin price inspector.
    `applied_rules`    JSON NULL,
    `added_via`        VARCHAR(24) NOT NULL DEFAULT 'manual',

    `fulfilled_qty`    INT UNSIGNED NOT NULL DEFAULT 0,
    `returned_qty`     INT UNSIGNED NOT NULL DEFAULT 0,
    `unit_weight_g`    DECIMAL(10,3) NULL,

    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `ix_order_items_order` (`order_id`),
    KEY `ix_order_items_product` (`product_id`),
    KEY `ix_order_items_pack` (`variant_pack_id`),
    KEY `ix_order_items_sku` (`sku`),
    KEY `ix_order_items_hsn` (`hsn_code`),
    CONSTRAINT `fk_order_items_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_order_items_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_order_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_order_items_qty` CHECK (`pack_qty` > 0 AND `base_qty` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
