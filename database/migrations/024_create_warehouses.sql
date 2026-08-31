-- Single warehouse today; the table exists now so multi-location needs no
-- migration of live inventory later.
CREATE TABLE `warehouses` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(32)  NOT NULL,
    `name`        VARCHAR(128) NOT NULL,
    `line1`       VARCHAR(191) NULL,
    `city`        VARCHAR(96)  NULL,
    `state_code`  CHAR(2)      NOT NULL COMMENT 'Place of supply for orders shipped from here',
    `pincode`     CHAR(6)      NULL,
    `gstin`       VARCHAR(15)  NULL,
    `is_default`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_warehouses_code` (`code`),
    KEY `ix_warehouses_default` (`is_default`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
