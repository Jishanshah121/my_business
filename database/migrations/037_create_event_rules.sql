-- The event calculator as DATA, not code (architecture section 4.4).
-- An admin retunes "600 plates for 500 guests" by editing rows, never by a
-- deploy. Rule sets are versioned so a kit generated in March is reproducible
-- in December after the margins have been changed.

-- What role a product plays at an event, independent of which SKU fills it.
CREATE TABLE `item_roles` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(48)  NOT NULL COMMENT 'DINNER_PLATE, SPOON, NAPKIN, GARBAGE_BAG',
    `name`        VARCHAR(128) NOT NULL,
    `description` VARCHAR(255) NULL,
    `category_id` BIGINT UNSIGNED NULL COMMENT 'Fallback source when no product is mapped',
    `icon_path`   VARCHAR(255) NULL,
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_item_roles_code` (`code`),
    KEY `ix_item_roles_category` (`category_id`),
    CONSTRAINT `fk_item_roles_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Which SKU fills a role at each service tier, in preference order.
CREATE TABLE `item_role_products` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_role_id`    BIGINT UNSIGNED NOT NULL,
    `variant_pack_id` BIGINT UNSIGNED NOT NULL,
    `tier`            ENUM('standard','premium','eco') NOT NULL DEFAULT 'standard',
    `rank`            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `is_demo_data`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_item_role_products` (`item_role_id`, `variant_pack_id`, `tier`),
    KEY `ix_item_role_products_lookup` (`item_role_id`, `tier`, `is_active`, `rank`),
    CONSTRAINT `fk_item_role_products_role`
        FOREIGN KEY (`item_role_id`) REFERENCES `item_roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_role_products_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `event_rule_sets` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type`    VARCHAR(48) NOT NULL COMMENT 'wedding, birthday, corporate, party, pooja, catering, festival, picnic, other',
    `meal_type`     VARCHAR(48) NOT NULL DEFAULT 'any' COMMENT 'breakfast, lunch, dinner, snacks, multiple, any',
    `serving_style` ENUM('disposable','premium','eco','any') NOT NULL DEFAULT 'any',
    `name`          VARCHAR(191) NOT NULL,
    `version`       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `notes`         VARCHAR(500) NULL,
    `created_by`    BIGINT UNSIGNED NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_rule_sets` (`event_type`, `meal_type`, `serving_style`, `version`),
    KEY `ix_event_rule_sets_lookup` (`event_type`, `meal_type`, `serving_style`, `is_active`),
    CONSTRAINT `fk_event_rule_sets_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- qty = max( ceil_to_step( guests * qty_per_guest * (1 + safety_margin_pct/100),
--                          rounding_step ), min_qty )
CREATE TABLE `event_rules` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_set_id`       BIGINT UNSIGNED NOT NULL,
    `item_role_id`      BIGINT UNSIGNED NOT NULL,
    `qty_per_guest`     DECIMAL(8,3) NOT NULL COMMENT 'e.g. 1.200 plates per guest',
    `safety_margin_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `min_qty`           INT UNSIGNED NOT NULL DEFAULT 0,
    `max_qty`           INT UNSIGNED NULL,
    `rounding_step`     INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Round up to a multiple of this',
    `is_required`       TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Budget trimming may never drop this role',
    `priority`          SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    `conditions`        JSON NULL COMMENT 'Optional extra predicates, e.g. min guest count',
    `note`              VARCHAR(255) NULL,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_rules` (`rule_set_id`, `item_role_id`),
    KEY `ix_event_rules_role` (`item_role_id`),
    CONSTRAINT `fk_event_rules_set`
        FOREIGN KEY (`rule_set_id`) REFERENCES `event_rule_sets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_event_rules_role`
        FOREIGN KEY (`item_role_id`) REFERENCES `item_roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ck_event_rules` CHECK (`qty_per_guest` >= 0 AND `rounding_step` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
