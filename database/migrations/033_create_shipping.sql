CREATE TABLE `shipping_zones` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(32)  NOT NULL,
    `name`        VARCHAR(128) NOT NULL,
    `description` VARCHAR(255) NULL,
    `priority`    SMALLINT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Lower wins when zones overlap',
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_shipping_zones_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A zone is defined by pincode ranges and/or whole states.
CREATE TABLE `zone_pincodes` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shipping_zone_id` BIGINT UNSIGNED NOT NULL,
    `match_type`      ENUM('pincode','pincode_range','state','all') NOT NULL DEFAULT 'pincode',
    `pincode_from`    CHAR(6) NULL,
    `pincode_to`      CHAR(6) NULL,
    `state_code`      CHAR(2) NULL,
    `is_serviceable`  TINYINT(1) NOT NULL DEFAULT 1,
    `cod_available`   TINYINT(1) NOT NULL DEFAULT 1,
    `eta_days_min`    TINYINT UNSIGNED NULL,
    `eta_days_max`    TINYINT UNSIGNED NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_zone_pincodes_zone` (`shipping_zone_id`),
    KEY `ix_zone_pincodes_range` (`pincode_from`, `pincode_to`),
    KEY `ix_zone_pincodes_state` (`state_code`),
    CONSTRAINT `fk_zone_pincodes_zone`
        FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Weight slabs. Chargeable weight is max(actual, volumetric) where volumetric
-- is (L x W x H in cm) / 5000 — critical for disposables, which are light and
-- bulky enough that actual weight alone would price large orders at a loss.
CREATE TABLE `shipping_rates` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shipping_zone_id`   BIGINT UNSIGNED NOT NULL,
    `name`               VARCHAR(96) NOT NULL COMMENT 'Standard, Express, Bulk Freight',
    `customer_group_id`  BIGINT UNSIGNED NULL COMMENT 'NULL = all groups',
    `weight_from_g`      INT UNSIGNED NOT NULL DEFAULT 0,
    `weight_to_g`        INT UNSIGNED NULL,
    `base_rate`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `per_kg_rate`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `free_above_value`   DECIMAL(12,2) NULL COMMENT 'Free shipping threshold for this zone',
    `min_charge`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `max_charge`         DECIMAL(10,2) NULL,
    `gst_rate`           DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    `hsn_code`           VARCHAR(10) NOT NULL DEFAULT '996812',
    `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_shipping_rates_zone` (`shipping_zone_id`, `is_active`, `weight_from_g`),
    KEY `ix_shipping_rates_group` (`customer_group_id`),
    CONSTRAINT `fk_shipping_rates_zone`
        FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_shipping_rates_group`
        FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `shipments` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`        BIGINT UNSIGNED NOT NULL,
    `warehouse_id`    BIGINT UNSIGNED NULL,
    `courier_name`    VARCHAR(96)  NULL,
    `awb_number`      VARCHAR(64)  NULL,
    `tracking_url`    VARCHAR(500) NULL,
    `status`          ENUM('pending','manifested','picked_up','in_transit','out_for_delivery',
                           'delivered','failed','returned') NOT NULL DEFAULT 'pending',
    `actual_weight_g`     DECIMAL(12,3) NULL,
    `volumetric_weight_g` DECIMAL(12,3) NULL,
    `chargeable_weight_g` DECIMAL(12,3) NULL,
    `shipping_cost`   DECIMAL(10,2) NULL COMMENT 'What we paid the courier',
    `shipped_at`      DATETIME NULL,
    `delivered_at`    DATETIME NULL,
    `last_tracked_at` DATETIME NULL,
    `tracking_events` JSON NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_shipments_awb` (`courier_name`, `awb_number`),
    KEY `ix_shipments_order` (`order_id`),
    KEY `ix_shipments_status` (`status`),
    CONSTRAINT `fk_shipments_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_shipments_warehouse`
        FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
