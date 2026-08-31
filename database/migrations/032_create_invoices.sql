-- Invoice numbering with a financial-year reset (INV/2026-27/00001). The
-- last_number row is locked with SELECT ... FOR UPDATE at issue time so two
-- concurrent orders cannot take the same number.
CREATE TABLE `invoice_series` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `series_code`    VARCHAR(32) NOT NULL COMMENT 'INV | CRN | QUO',
    `financial_year` CHAR(7)     NOT NULL COMMENT '2026-27',
    `prefix`         VARCHAR(24) NOT NULL,
    `last_number`    INT UNSIGNED NOT NULL DEFAULT 0,
    `padding`        TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_invoice_series` (`series_code`, `financial_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- The e-invoice columns (irn, ack_no, signed_qr_payload, eway_bill_no) are
-- nullable and present from day one. We are not implementing IRP integration
-- now, but adding it later must never require migrating live invoice rows.
CREATE TABLE `order_invoices` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`           BIGINT UNSIGNED NOT NULL,
    `invoice_number`     VARCHAR(48) NOT NULL,
    `invoice_date`       DATE NOT NULL,
    `type`               ENUM('tax_invoice','bill_of_supply','credit_note','proforma')
                         NOT NULL DEFAULT 'tax_invoice',
    `seller_gstin`       VARCHAR(15) NULL,
    `buyer_gstin`        VARCHAR(15) NULL,
    `place_of_supply`    CHAR(2) NOT NULL,
    `taxable_value`      DECIMAL(12,2) NOT NULL,
    `cgst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sgst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `igst_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cess_total`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `round_off`          DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `grand_total`        DECIMAL(12,2) NOT NULL,
    `hsn_summary`        JSON NULL COMMENT 'Per-HSN totals required on a GST invoice',
    `pdf_path`           VARCHAR(255) NULL,

    -- Reserved for e-invoicing / e-way bill (future phase)
    `irn`                VARCHAR(64)  NULL,
    `ack_no`             VARCHAR(32)  NULL,
    `ack_date`           DATETIME     NULL,
    `signed_qr_payload`  TEXT         NULL,
    `eway_bill_no`       VARCHAR(20)  NULL,
    `eway_bill_date`     DATETIME     NULL,

    `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_order_invoices_number` (`invoice_number`),
    KEY `ix_order_invoices_order` (`order_id`),
    KEY `ix_order_invoices_date` (`invoice_date`),
    CONSTRAINT `fk_order_invoices_order`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
