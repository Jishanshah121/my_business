-- Customer groups drive pricing tiers and tax display (ADR-004). B2C sees
-- GST-inclusive prices; B2B sees exclusive plus a GST line. Storage is always
-- exclusive — only the presentation differs.
CREATE TABLE `customer_groups` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`                 VARCHAR(48)  NOT NULL,
    `name`                 VARCHAR(128) NOT NULL,
    `description`          VARCHAR(255) NULL,
    `is_b2b`               TINYINT(1)   NOT NULL DEFAULT 0,
    `is_default`           TINYINT(1)   NOT NULL DEFAULT 0,
    `prices_include_tax`   TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'Display only',
    `default_discount_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `min_order_value`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `requires_approval`    TINYINT(1)   NOT NULL DEFAULT 0,
    `sort_order`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`            TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_customer_groups_code` (`code`),
    KEY `ix_customer_groups_b2b` (`is_b2b`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
