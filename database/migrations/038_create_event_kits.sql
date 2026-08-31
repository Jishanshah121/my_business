-- A curated, named basket. Either admin-authored ("Wedding Starter Kit") or
-- saved by a customer from a calculator run.
CREATE TABLE `event_kits` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(191) NOT NULL,
    `slug`            VARCHAR(200) NOT NULL,
    `occasion_id`     BIGINT UNSIGNED NULL,
    `guest_count`     INT UNSIGNED NULL,
    `serving_style`   ENUM('disposable','premium','eco') NOT NULL DEFAULT 'disposable',
    `description`     TEXT NULL,
    `banner_path`     VARCHAR(255) NULL,
    `owner_user_id`   BIGINT UNSIGNED NULL COMMENT 'NULL = admin-curated, public',
    `is_public`       TINYINT(1) NOT NULL DEFAULT 0,
    `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `seo_title`       VARCHAR(191) NULL,
    `seo_description` VARCHAR(320) NULL,
    `is_demo_data`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_kits_slug` (`slug`),
    KEY `ix_event_kits_occasion` (`occasion_id`, `status`),
    KEY `ix_event_kits_owner` (`owner_user_id`),
    CONSTRAINT `fk_event_kits_occasion`
        FOREIGN KEY (`occasion_id`) REFERENCES `occasions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_event_kits_owner`
        FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `event_kit_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_kit_id`    BIGINT UNSIGNED NOT NULL,
    `item_role_id`    BIGINT UNSIGNED NULL,
    `variant_pack_id` BIGINT UNSIGNED NOT NULL,
    `pack_qty`        INT UNSIGNED NOT NULL,
    `base_qty`        INT UNSIGNED NOT NULL,
    `is_required`     TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_demo_data`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_kit_items` (`event_kit_id`, `variant_pack_id`),
    KEY `ix_event_kit_items_pack` (`variant_pack_id`),
    CONSTRAINT `fk_event_kit_items_kit`
        FOREIGN KEY (`event_kit_id`) REFERENCES `event_kits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_event_kit_items_role`
        FOREIGN KEY (`item_role_id`) REFERENCES `item_roles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_event_kit_items_pack`
        FOREIGN KEY (`variant_pack_id`) REFERENCES `variant_packs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- One saved run of the calculator, so a customer can come back to it and so we
-- can measure which inputs actually convert.
CREATE TABLE `event_plans` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`             CHAR(36) NOT NULL,
    `user_id`          BIGINT UNSIGNED NULL,
    `session_token`    CHAR(64) NULL COMMENT 'For guests',
    `event_type`       VARCHAR(48)  NOT NULL,
    `meal_type`        VARCHAR(48)  NOT NULL DEFAULT 'any',
    `serving_style`    ENUM('disposable','premium','eco') NOT NULL DEFAULT 'disposable',
    `guest_count`      INT UNSIGNED NOT NULL,
    `event_date`       DATE NULL,
    `budget`           DECIMAL(12,2) NULL,
    `rule_set_id`      BIGINT UNSIGNED NULL COMMENT 'Which version produced this plan',
    `computed_items`   JSON NULL COMMENT 'Snapshot of the generated kit',
    `estimated_total`  DECIMAL(12,2) NULL,
    `converted_cart_id` BIGINT UNSIGNED NULL,
    `converted_order_id` BIGINT UNSIGNED NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_plans_uuid` (`uuid`),
    KEY `ix_event_plans_user` (`user_id`, `created_at`),
    KEY `ix_event_plans_type` (`event_type`, `guest_count`),
    CONSTRAINT `fk_event_plans_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_event_plans_rule_set`
        FOREIGN KEY (`rule_set_id`) REFERENCES `event_rule_sets` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_event_plans_cart`
        FOREIGN KEY (`converted_cart_id`) REFERENCES `carts` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_event_plans_order`
        FOREIGN KEY (`converted_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_event_plans_guests` CHECK (`guest_count` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
