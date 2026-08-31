-- EAV for product specifications. New attributes are rows, so the admin can
-- add "Leak Proof" or "Microwave Safe" without a migration (brief section 4).
CREATE TABLE `attributes` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`           VARCHAR(64)  NOT NULL,
    `name`           VARCHAR(128) NOT NULL,
    `group`          VARCHAR(64)  NOT NULL DEFAULT 'general',
    `input_type`     ENUM('text','number','boolean','select','multiselect') NOT NULL DEFAULT 'text',
    `unit`           VARCHAR(24)  NULL COMMENT 'ml, mm, gsm, g',
    `is_filterable`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_comparable`  TINYINT(1) NOT NULL DEFAULT 0,
    `show_on_page`   TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attributes_code` (`code`),
    KEY `ix_attributes_filterable` (`is_filterable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `attribute_values` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attribute_id` BIGINT UNSIGNED NOT NULL,
    `value`        VARCHAR(191) NOT NULL,
    `label`        VARCHAR(191) NOT NULL,
    `sort_order`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attribute_values` (`attribute_id`, `value`),
    CONSTRAINT `fk_attribute_values_attribute`
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `product_attributes` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`         BIGINT UNSIGNED NOT NULL,
    `variant_id`         BIGINT UNSIGNED NULL COMMENT 'NULL = applies to all variants',
    `attribute_id`       BIGINT UNSIGNED NOT NULL,
    `attribute_value_id` BIGINT UNSIGNED NULL COMMENT 'For select/multiselect',
    `value_text`         VARCHAR(500) NULL,
    `value_number`       DECIMAL(14,4) NULL,
    `value_boolean`      TINYINT(1) NULL,
    `is_demo_data`       TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_attributes` (`product_id`, `variant_id`, `attribute_id`),
    KEY `ix_product_attributes_attribute` (`attribute_id`, `value_number`),
    KEY `ix_product_attributes_value` (`attribute_value_id`),
    CONSTRAINT `fk_product_attributes_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_attributes_variant`
        FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_attributes_attribute`
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_attributes_value`
        FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
