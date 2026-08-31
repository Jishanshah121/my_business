-- Step 4: date-bounded campaigns. Scope is polymorphic like the tier table.
CREATE TABLE `price_rules` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(128) NOT NULL,
    `scope_type`        ENUM('pack','variant','product','category','all') NOT NULL,
    `scope_id`          BIGINT UNSIGNED NULL,
    `customer_group_id` BIGINT UNSIGNED NULL,
    `discount_type`     ENUM('percentage','fixed_amount','fixed_price') NOT NULL,
    `discount_value`    DECIMAL(12,4) NOT NULL,
    `max_discount`      DECIMAL(12,2) NULL COMMENT 'Cap for percentage discounts',
    `min_base_qty`      INT UNSIGNED NOT NULL DEFAULT 1,
    `priority`          SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    `is_exclusive`      TINYINT(1) NOT NULL DEFAULT 0,
    `starts_at`         DATETIME NOT NULL,
    `ends_at`           DATETIME NULL,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_price_rules_lookup` (`scope_type`, `scope_id`, `is_active`, `starts_at`, `ends_at`),
    KEY `ix_price_rules_group` (`customer_group_id`),
    CONSTRAINT `fk_price_rules_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_price_rules_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_price_rules_value` CHECK (`discount_value` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
