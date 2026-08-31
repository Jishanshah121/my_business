-- Runtime configuration. Anything an admin should be able to change without a
-- deploy lives here (GST rates, seller identity, event safety margins, MOQs,
-- shipping thresholds). Application code reads settings first, config second.
CREATE TABLE `settings` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group`       VARCHAR(64)  NOT NULL,
    `key`         VARCHAR(128) NOT NULL,
    `value`       TEXT         NULL,
    `type`        ENUM('string','integer','decimal','boolean','json','date') NOT NULL DEFAULT 'string',
    `label`       VARCHAR(191) NOT NULL,
    `description` VARCHAR(500) NULL,
    `is_public`   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Safe to expose to the storefront',
    `is_editable` TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_group_key` (`group`, `key`),
    KEY `ix_settings_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
