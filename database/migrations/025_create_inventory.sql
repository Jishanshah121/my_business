-- Stock is held in BASE UNITS at the variant (ADR-003).
--   available = quantity_on_hand - quantity_reserved
-- pack_id is NULL for the normal loose pool; a non-null pack_id is the
-- separate pool for a sealed, unbreakable pack.
CREATE TABLE `inventory` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `variant_id`          BIGINT UNSIGNED NOT NULL,
    `warehouse_id`        BIGINT UNSIGNED NOT NULL,
    `pack_id`             BIGINT UNSIGNED NULL COMMENT 'NULL = loose base-unit pool',
    `quantity_on_hand`    INT NOT NULL DEFAULT 0 COMMENT 'Base units physically held',
    `quantity_reserved`   INT NOT NULL DEFAULT 0 COMMENT 'Committed to unshipped orders',
    `quantity_incoming`   INT NOT NULL DEFAULT 0 COMMENT 'On a purchase order',
    `low_stock_threshold` INT NOT NULL DEFAULT 0,
    `reorder_point`       INT NOT NULL DEFAULT 0,
    `reorder_quantity`    INT NOT NULL DEFAULT 0,
    `bin_location`        VARCHAR(48) NULL,
    `last_counted_at`     DATETIME NULL DEFAULT NULL,
    `is_demo_data`        TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_inventory_variant_warehouse_pack` (`variant_id`, `warehouse_id`, `pack_id`),
    KEY `ix_inventory_warehouse` (`warehouse_id`),
    KEY `ix_inventory_low_stock` (`quantity_on_hand`, `low_stock_threshold`),
    CONSTRAINT `fk_inventory_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inventory_warehouse`
        FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_inventory_pack`
        FOREIGN KEY (`pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_inventory_reserved` CHECK (`quantity_reserved` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Append-only ledger. EVERY stock movement writes a row here, with the actor
-- and the reason, so a discrepancy can always be traced to a decision.
CREATE TABLE `inventory_transactions` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `inventory_id`   BIGINT UNSIGNED NOT NULL,
    `variant_id`     BIGINT UNSIGNED NOT NULL,
    `type`           ENUM('purchase','sale','reservation','release','adjustment',
                          'damage','return','transfer_in','transfer_out','stock_count') NOT NULL,
    `quantity_delta` INT NOT NULL COMMENT 'Signed change in base units',
    `balance_after`  INT NOT NULL COMMENT 'quantity_on_hand after this movement',
    `reference_type` VARCHAR(32) NULL COMMENT 'order | quote | purchase_order | manual',
    `reference_id`   BIGINT UNSIGNED NULL,
    `reason`         VARCHAR(255) NULL,
    `performed_by`   BIGINT UNSIGNED NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_inventory_transactions_inventory` (`inventory_id`, `created_at`),
    KEY `ix_inventory_transactions_variant` (`variant_id`, `created_at`),
    KEY `ix_inventory_transactions_reference` (`reference_type`, `reference_id`),
    CONSTRAINT `fk_inventory_transactions_inventory`
        FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inventory_transactions_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inventory_transactions_user`
        FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
