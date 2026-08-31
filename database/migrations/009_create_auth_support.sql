CREATE TABLE `password_resets` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(191) NOT NULL,
    `token_hash` CHAR(64)     NOT NULL COMMENT 'SHA-256 of the emailed token; the raw token is never stored',
    `expires_at` DATETIME     NOT NULL,
    `used_at`    DATETIME     NULL DEFAULT NULL,
    `ip_address` VARBINARY(16) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_password_resets_token` (`token_hash`),
    KEY `ix_password_resets_email` (`email`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `email_verifications` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at`    DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email_verifications_token` (`token_hash`),
    KEY `ix_email_verifications_user` (`user_id`),
    CONSTRAINT `fk_email_verifications_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Login throttling. Keyed by both identifier and IP so neither a single
-- account nor a single source can be brute-forced.
CREATE TABLE `login_attempts` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(191) NOT NULL COMMENT 'Email or phone as submitted',
    `ip_address` VARBINARY(16) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `successful` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_login_attempts_identifier` (`identifier`, `created_at`),
    KEY `ix_login_attempts_ip` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Server-side session index. Lets an admin (or a password change) invalidate
-- every live session for a user.
CREATE TABLE `sessions` (
    `id`             VARCHAR(128) NOT NULL,
    `user_id`        BIGINT UNSIGNED NULL,
    `ip_address`     VARBINARY(16) NULL,
    `user_agent`     VARCHAR(255) NULL,
    `payload`        MEDIUMTEXT NULL,
    `last_activity`  DATETIME NOT NULL,
    `expires_at`     DATETIME NOT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_sessions_user` (`user_id`),
    KEY `ix_sessions_expiry` (`expires_at`),
    CONSTRAINT `fk_sessions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
