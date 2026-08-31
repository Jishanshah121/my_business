-- GST rates keyed by HSN code and DATED, so a rate change never rewrites
-- history: an order placed under the old rate keeps its own snapshot, and the
-- rate table simply gains a new row with a later valid_from.
CREATE TABLE `tax_rates` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `hsn_code`    VARCHAR(10)  NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `gst_rate`    DECIMAL(5,2) NOT NULL COMMENT 'Total GST %, split into CGST+SGST or charged as IGST',
    `cess_rate`   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `valid_from`  DATE NOT NULL,
    `valid_to`    DATE NULL DEFAULT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tax_rates_hsn_from` (`hsn_code`, `valid_from`),
    KEY `ix_tax_rates_lookup` (`hsn_code`, `is_active`, `valid_from`),
    CONSTRAINT `ck_tax_rates_rate` CHECK (`gst_rate` >= 0 AND `gst_rate` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
