-- Idempotency guard for checkout. A duplicate submit returns the original
-- order rather than creating a second one.
CREATE TABLE `checkout_attempts` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idempotency_key` CHAR(64) NOT NULL,
    `cart_id`         BIGINT UNSIGNED NULL,
    `user_id`         BIGINT UNSIGNED NULL,
    `request_hash`    CHAR(64) NOT NULL COMMENT 'Hash of the checkout payload',
    `order_id`        BIGINT UNSIGNED NULL,
    `status`          ENUM('in_progress','succeeded','failed') NOT NULL DEFAULT 'in_progress',
    `error_code`      VARCHAR(64) NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_checkout_attempts_key` (`idempotency_key`),
    KEY `ix_checkout_attempts_order` (`order_id`),
    CONSTRAINT `fk_checkout_attempts_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_checkout_attempts_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `order_payments` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`         BIGINT UNSIGNED NOT NULL,
    `gateway`          VARCHAR(32)  NOT NULL COMMENT 'razorpay | cod | bank_transfer | credit_terms',
    `method`           VARCHAR(32)  NULL COMMENT 'upi | card | netbanking | neft | wallet',
    `gateway_order_id` VARCHAR(128) NULL,
    `gateway_payment_id` VARCHAR(128) NULL,
    `amount`           DECIMAL(12,2) NOT NULL,
    `currency`         CHAR(3) NOT NULL DEFAULT 'INR',
    `status`           ENUM('created','pending','authorised','captured','failed','cancelled','refunded')
                       NOT NULL DEFAULT 'created',
    `failure_code`     VARCHAR(64)  NULL,
    `failure_message`  VARCHAR(500) NULL,
    -- Gateway response minus anything secret. Card numbers, CVVs and tokens
    -- are never persisted.
    `gateway_meta`     JSON NULL,
    `authorised_at`    DATETIME NULL,
    `captured_at`      DATETIME NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_order_payments_gateway_payment` (`gateway`, `gateway_payment_id`),
    KEY `ix_order_payments_order` (`order_id`, `status`),
    KEY `ix_order_payments_gateway_order` (`gateway_order_id`),
    CONSTRAINT `fk_order_payments_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Webhooks are the authority on payment success, never the browser redirect.
-- The unique key makes replayed deliveries harmless.
CREATE TABLE `payment_webhook_events` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `gateway`         VARCHAR(32)  NOT NULL,
    `event_id`        VARCHAR(191) NOT NULL COMMENT 'Provider event id',
    `event_type`      VARCHAR(96)  NOT NULL,
    `signature_valid` TINYINT(1)   NOT NULL DEFAULT 0,
    `order_id`        BIGINT UNSIGNED NULL,
    `payload`         JSON NOT NULL,
    `status`          ENUM('received','processed','ignored','failed') NOT NULL DEFAULT 'received',
    `error_message`   VARCHAR(500) NULL,
    `processed_at`    DATETIME NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payment_webhook_events` (`gateway`, `event_id`),
    KEY `ix_payment_webhook_events_order` (`order_id`),
    KEY `ix_payment_webhook_events_status` (`status`, `created_at`),
    CONSTRAINT `fk_payment_webhook_events_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `refunds` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`          BIGINT UNSIGNED NOT NULL,
    `order_payment_id`  BIGINT UNSIGNED NULL,
    `amount`            DECIMAL(12,2) NOT NULL,
    `reason`            VARCHAR(255) NOT NULL,
    `gateway_refund_id` VARCHAR(128) NULL,
    `status`            ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    `requested_by`      BIGINT UNSIGNED NULL,
    `processed_at`      DATETIME NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_refunds_order` (`order_id`),
    CONSTRAINT `fk_refunds_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_refunds_payment`
        FOREIGN KEY (`order_payment_id`) REFERENCES `order_payments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_refunds_requested_by`
        FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `ck_refunds_amount` CHECK (`amount` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
