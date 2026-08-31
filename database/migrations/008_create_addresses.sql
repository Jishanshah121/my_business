-- state_code is the GST place-of-supply driver: ship-to state equal to the
-- seller state means CGST+SGST, otherwise IGST.
CREATE TABLE `addresses` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      BIGINT UNSIGNED NOT NULL,
    `type`         ENUM('billing','shipping','both') NOT NULL DEFAULT 'both',
    `label`        VARCHAR(64)  NULL COMMENT 'Home, Warehouse, Head Office',
    `contact_name` VARCHAR(128) NOT NULL,
    `contact_phone` VARCHAR(20) NOT NULL,
    `company_name` VARCHAR(191) NULL,
    `gstin`        VARCHAR(15)  NULL COMMENT 'Per-address GSTIN for multi-branch B2B',
    `line1`        VARCHAR(191) NOT NULL,
    `line2`        VARCHAR(191) NULL,
    `landmark`     VARCHAR(128) NULL,
    `city`         VARCHAR(96)  NOT NULL,
    `state_code`   CHAR(2)      NOT NULL,
    `state_name`   VARCHAR(64)  NOT NULL,
    `pincode`      CHAR(6)      NOT NULL,
    `country_code` CHAR(2)      NOT NULL DEFAULT 'IN',
    `is_default_billing`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_default_shipping` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ix_addresses_user` (`user_id`, `deleted_at`),
    KEY `ix_addresses_pincode` (`pincode`),
    CONSTRAINT `fk_addresses_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
