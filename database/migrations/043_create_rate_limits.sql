-- Generic rate-limit counters.
--
-- The architecture puts throttling in Redis. The Redis extension is not
-- available on the current target, so this table backs the same
-- RateLimiter interface. It is a fixed-window counter keyed on a hashed
-- identifier: swapping in a Redis driver later changes one class and leaves
-- this table unused rather than requiring a data migration.
--
-- `key_hash` is a SHA-256 of "action:identifier" so raw emails and IP
-- addresses are never stored here in the clear.
CREATE TABLE `rate_limits` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key_hash`     CHAR(64)     NOT NULL,
    `action`       VARCHAR(64)  NOT NULL,
    `attempts`     INT UNSIGNED NOT NULL DEFAULT 0,
    `window_start` DATETIME     NOT NULL,
    `expires_at`   DATETIME     NOT NULL,
    `blocked_until` DATETIME    NULL DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rate_limits_key` (`key_hash`),
    KEY `ix_rate_limits_expiry` (`expires_at`),
    KEY `ix_rate_limits_action` (`action`, `window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
