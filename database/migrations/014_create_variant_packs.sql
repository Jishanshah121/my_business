-- Level 3. The sellable presentation and the real SKU. Every cart line, order
-- line, quote line and kit line points at a variant_pack.
--
-- pieces_per_pack converts to base units for inventory. is_breakable = 0 means
-- the pack cannot be split from the loose pool and gets its own stock row.
CREATE TABLE `variant_packs` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `variant_id`       BIGINT UNSIGNED NOT NULL,
    `sku`              VARCHAR(64)  NOT NULL,
    `barcode`          VARCHAR(64)  NULL,
    `pack_label`       VARCHAR(96)  NOT NULL COMMENT 'e.g. "Pack of 50", "Carton of 1000"',
    `pieces_per_pack`  INT UNSIGNED NOT NULL,

    -- Pack price. NULL means "derive from variant.base_unit_price x pieces".
    `base_price`       DECIMAL(12,2) NULL,
    `mrp`              DECIMAL(12,2) NULL,

    -- Order quantity limits are expressed in PACKS, not pieces.
    `min_order_qty`    INT UNSIGNED NOT NULL DEFAULT 1,
    `max_order_qty`    INT UNSIGNED NULL,
    `qty_step`         INT UNSIGNED NOT NULL DEFAULT 1,

    -- Shipping. Filled in per pack because a carton is not 20x a sleeve.
    `pack_weight_g`    DECIMAL(10,3) NULL,
    `pack_length_mm`   INT UNSIGNED NULL,
    `pack_width_mm`    INT UNSIGNED NULL,
    `pack_height_mm`   INT UNSIGNED NULL,

    `is_breakable`     TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = sealed, gets its own stock pool',
    `is_default`       TINYINT(1) NOT NULL DEFAULT 0,
    `is_b2b_only`      TINYINT(1) NOT NULL DEFAULT 0,
    `status`           ENUM('active','inactive','discontinued') NOT NULL DEFAULT 'active',
    `sort_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_variant_packs_sku` (`sku`),
    UNIQUE KEY `uq_variant_packs_barcode` (`barcode`),
    KEY `ix_variant_packs_variant` (`variant_id`, `status`, `sort_order`),
    KEY `ix_variant_packs_default` (`variant_id`, `is_default`),
    CONSTRAINT `fk_variant_packs_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_variant_packs_pieces`
        CHECK (`pieces_per_pack` > 0 AND `min_order_qty` > 0 AND `qty_step` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
