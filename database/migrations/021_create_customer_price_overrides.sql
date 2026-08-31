-- Step 2 of the waterfall: a negotiated contract rate for one customer or one
-- group. Exactly one of customer_id / customer_group_id is set.
CREATE TABLE `customer_price_overrides` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id`       BIGINT UNSIGNED NULL,
    `customer_group_id` BIGINT UNSIGNED NULL,
    `variant_pack_id`   BIGINT UNSIGNED NOT NULL,
    `unit_price`        DECIMAL(12,4) NOT NULL COMMENT 'Per base unit',
    `min_qty`           INT UNSIGNED NOT NULL DEFAULT 1,
    `is_exclusive`      TINYINT(1) NOT NULL DEFAULT 0,
    `valid_from`        DATE NULL,
    `valid_to`          DATE NULL,
    `note`              VARCHAR(255) NULL COMMENT 'Why this rate was agreed',
    `created_by`        BIGINT UNSIGNED NULL,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_cpo_customer` (`customer_id`, `variant_pack_id`, `is_active`),
    KEY `ix_cpo_group` (`customer_group_id`, `variant_pack_id`, `is_active`),
    KEY `ix_cpo_pack` (`variant_pack_id`),
    CONSTRAINT `fk_cpo_customer`
        FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cpo_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cpo_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cpo_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_cpo_target`
        CHECK ((`customer_id` IS NULL) <> (`customer_group_id` IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
