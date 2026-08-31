-- Generic event stream, so new metrics are new event_type values rather than
-- new tables (brief section 45).
CREATE TABLE `analytics_events` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type`     VARCHAR(64) NOT NULL COMMENT 'product_view, add_to_cart, checkout_start, purchase',
    `user_id`        BIGINT UNSIGNED NULL,
    `session_token`  CHAR(64) NULL,
    `reference_type` VARCHAR(32) NULL,
    `reference_id`   BIGINT UNSIGNED NULL,
    `value`          DECIMAL(12,2) NULL,
    `properties`     JSON NULL,
    `channel`        VARCHAR(24) NOT NULL DEFAULT 'web',
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_analytics_events_type_date` (`event_type`, `created_at`),
    KEY `ix_analytics_events_user` (`user_id`, `created_at`),
    KEY `ix_analytics_events_reference` (`reference_type`, `reference_id`),
    CONSTRAINT `fk_analytics_events_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Feeds the search synonym dictionary and tells us what people ask for that
-- we do not stock.
CREATE TABLE `search_queries` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `query`         VARCHAR(255) NOT NULL,
    `normalised`    VARCHAR(255) NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `session_token` CHAR(64) NULL,
    `result_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `clicked_product_id` BIGINT UNSIGNED NULL,
    `driver`        VARCHAR(16) NOT NULL DEFAULT 'mysql' COMMENT 'mysql | ai',
    `duration_ms`   INT UNSIGNED NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_search_queries_normalised` (`normalised`, `created_at`),
    KEY `ix_search_queries_zero_results` (`result_count`, `created_at`),
    CONSTRAINT `fk_search_queries_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_search_queries_product`
        FOREIGN KEY (`clicked_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `product_views` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`    BIGINT UNSIGNED NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `session_token` CHAR(64) NULL,
    `source`        VARCHAR(32) NULL COMMENT 'search | category | recommendation | direct',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_product_views_product_date` (`product_id`, `created_at`),
    KEY `ix_product_views_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_product_views_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_views_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Every admin mutation. Never contains passwords, tokens or payment secrets.
CREATE TABLE `admin_logs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      BIGINT UNSIGNED NULL,
    `action`       VARCHAR(64)  NOT NULL COMMENT 'product.update, order.refund, price.override',
    `target_type`  VARCHAR(48)  NULL,
    `target_id`    BIGINT UNSIGNED NULL,
    `description`  VARCHAR(500) NULL,
    `changes`      JSON NULL COMMENT 'Before/after diff of changed columns',
    `ip_address`   VARBINARY(16) NULL,
    `user_agent`   VARCHAR(255) NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ix_admin_logs_user` (`user_id`, `created_at`),
    KEY `ix_admin_logs_target` (`target_type`, `target_id`),
    KEY `ix_admin_logs_action` (`action`, `created_at`),
    CONSTRAINT `fk_admin_logs_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
