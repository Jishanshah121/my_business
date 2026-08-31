-- Addresses are COPIED onto the order, not referenced. A customer editing
-- their saved address must not rewrite where a past order was delivered.
CREATE TABLE `order_addresses` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`      BIGINT UNSIGNED NOT NULL,
    `type`          ENUM('billing','shipping') NOT NULL,
    `contact_name`  VARCHAR(128) NOT NULL,
    `contact_phone` VARCHAR(20)  NOT NULL,
    `company_name`  VARCHAR(191) NULL,
    `gstin`         VARCHAR(15)  NULL,
    `line1`         VARCHAR(191) NOT NULL,
    `line2`         VARCHAR(191) NULL,
    `landmark`      VARCHAR(128) NULL,
    `city`          VARCHAR(96)  NOT NULL,
    `state_code`    CHAR(2)      NOT NULL,
    `state_name`    VARCHAR(64)  NOT NULL,
    `pincode`       CHAR(6)      NOT NULL,
    `country_code`  CHAR(2)      NOT NULL DEFAULT 'IN',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_order_addresses` (`order_id`, `type`),
    CONSTRAINT `fk_order_addresses_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Append-only. Every transition is recorded with who caused it.
CREATE TABLE `order_status_history` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`     BIGINT UNSIGNED NOT NULL,
    `from_status`  VARCHAR(32) NULL,
    `to_status`    VARCHAR(32) NOT NULL,
    `status_type`  ENUM('order','payment','fulfilment') NOT NULL DEFAULT 'order',
    `comment`      VARCHAR(500) NULL,
    `notified_customer` TINYINT(1) NOT NULL DEFAULT 0,
    `actor_type`   ENUM('customer','staff','system','webhook') NOT NULL DEFAULT 'system',
    `actor_id`     BIGINT UNSIGNED NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_order_status_history_order` (`order_id`, `created_at`),
    CONSTRAINT `fk_order_status_history_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_status_history_actor`
        FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
