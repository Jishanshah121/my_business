-- B2B approval workflow. A pending profile still shops at B2C prices; B2B
-- pricing, quotes and credit unlock only when an admin approves.
CREATE TABLE `business_profiles` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`               BIGINT UNSIGNED NOT NULL,
    `business_type_id`      BIGINT UNSIGNED NULL,
    `company_name`          VARCHAR(191) NOT NULL,
    `contact_person`        VARCHAR(128) NOT NULL,
    `contact_phone`         VARCHAR(20)  NOT NULL,
    `contact_email`         VARCHAR(191) NULL,
    `gstin`                 VARCHAR(15)  NULL COMMENT '15-char GSTIN; first 2 chars are the state code',
    `pan`                   VARCHAR(10)  NULL,
    `fssai_licence`         VARCHAR(20)  NULL,
    `gstin_verified_at`     DATETIME NULL DEFAULT NULL,
    `expected_monthly_spend` DECIMAL(12,2) NULL,
    `status`                ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
    `rejection_reason`      VARCHAR(500) NULL,
    `approved_by`           BIGINT UNSIGNED NULL,
    `approved_at`           DATETIME NULL DEFAULT NULL,
    `credit_enabled`        TINYINT(1)    NOT NULL DEFAULT 0,
    `credit_limit`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `credit_days`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `credit_used`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`            DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_business_profiles_user` (`user_id`),
    UNIQUE KEY `uq_business_profiles_gstin` (`gstin`),
    KEY `ix_business_profiles_status` (`status`, `created_at`),
    KEY `ix_business_profiles_type` (`business_type_id`),
    CONSTRAINT `fk_business_profiles_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_business_profiles_type`
        FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_business_profiles_approved_by`
        FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_business_profiles_credit`
        CHECK (`credit_limit` >= 0 AND `credit_used` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
