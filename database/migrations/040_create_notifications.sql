CREATE TABLE `notification_templates` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event`        VARCHAR(64) NOT NULL COMMENT 'order.placed, quote.sent, stock.low',
    `channel`      ENUM('email','sms','whatsapp','in_app') NOT NULL,
    `subject`      VARCHAR(191) NULL,
    `body`         TEXT NOT NULL COMMENT 'Supports {{placeholders}}',
    `provider_template_id` VARCHAR(96) NULL COMMENT 'DLT / WhatsApp approved template id',
    `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_notification_templates` (`event`, `channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `notifications` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        BIGINT UNSIGNED NULL,
    `channel`        ENUM('email','sms','whatsapp','in_app') NOT NULL,
    `event`          VARCHAR(64) NOT NULL,
    `recipient`      VARCHAR(191) NOT NULL COMMENT 'Email address or phone number',
    `subject`        VARCHAR(191) NULL,
    `body`           TEXT NULL,
    `reference_type` VARCHAR(32) NULL,
    `reference_id`   BIGINT UNSIGNED NULL,
    `status`         ENUM('queued','sending','sent','failed','read') NOT NULL DEFAULT 'queued',
    `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `error_message`  VARCHAR(500) NULL,
    `sent_at`        DATETIME NULL,
    `read_at`        DATETIME NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_notifications_user` (`user_id`, `status`, `created_at`),
    KEY `ix_notifications_queue` (`status`, `created_at`),
    KEY `ix_notifications_reference` (`reference_type`, `reference_id`),
    CONSTRAINT `fk_notifications_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
