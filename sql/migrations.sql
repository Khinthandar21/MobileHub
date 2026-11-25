-- =======================================================
--  E-commerce migration — safe to re-run.
--  Creates: storefront catalog, orders, and customer account tables.
--  Adds:    currency settings and direct customer ownership on orders.
-- =======================================================
USE `mobile_hub`;
-- ---------------------------------------------------------
-- Categories
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150) NOT NULL,
  `slug`        VARCHAR(160) NOT NULL UNIQUE,
  `description` TEXT         DEFAULT NULL,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Products
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `category_id`      INT UNSIGNED  DEFAULT NULL,
  `name`             VARCHAR(200)  NOT NULL,
  `slug`             VARCHAR(220)  NOT NULL UNIQUE,
  `sku`              VARCHAR(80)   DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `description`      TEXT          DEFAULT NULL,
  `price`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sale_price`       DECIMAL(10,2) DEFAULT NULL,
  `stock`            INT           NOT NULL DEFAULT 0,
  `image`            VARCHAR(255)  DEFAULT NULL,
  `status`           ENUM('active','inactive','draft') NOT NULL DEFAULT 'active',
  `featured`         TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_status`   (`status`),
  KEY `idx_products_featured` (`featured`),
  CONSTRAINT `fk_products_category`
      FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Additional product photos, including color options.
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `label`      VARCHAR(80) DEFAULT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product` (`product_id`, `sort_order`),
  CONSTRAINT `fk_product_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Make SKU unique only when not null.
-- (Older MySQL versions don't support partial indexes, so we use a regular UNIQUE.)
-- We intentionally don't add a UNIQUE constraint on SKU so blank SKUs are allowed.

-- ---------------------------------------------------------
-- Customer accounts (created before orders so orders can reference them)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_users` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(150) NOT NULL,
  `email`            VARCHAR(150) NOT NULL,
  `phone`            VARCHAR(40) DEFAULT NULL,
  `password_hash`    VARCHAR(255) NOT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `shipping_city`    VARCHAR(100) DEFAULT NULL,
  `shipping_zip`     VARCHAR(20) DEFAULT NULL,
  `shipping_country` VARCHAR(100) DEFAULT NULL,
  `avatar`           VARCHAR(255) DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Orders
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `customer_id`       INT UNSIGNED  DEFAULT NULL,
  `order_number`      VARCHAR(40)   NOT NULL UNIQUE,
  `customer_name`     VARCHAR(150)  NOT NULL,
  `customer_email`    VARCHAR(150)  NOT NULL,
  `customer_phone`    VARCHAR(40)   DEFAULT NULL,
  `shipping_address`  TEXT          NOT NULL,
  `shipping_city`     VARCHAR(100)  DEFAULT NULL,
  `shipping_zip`      VARCHAR(20)   DEFAULT NULL,
  `shipping_country`  VARCHAR(100)  DEFAULT NULL,
  `subtotal`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method`    VARCHAR(40)   NOT NULL DEFAULT 'cod',
  `fulfillment_method` ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery',
  `status`            ENUM('pending','processing','shipped','ready_for_pickup','completed','cancelled')
                      NOT NULL DEFAULT 'pending',
  `notes`             TEXT          DEFAULT NULL,
  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_customer_id` (`customer_id`),
  KEY `idx_orders_status`     (`status`),
  KEY `idx_orders_email`      (`customer_email`),
  KEY `idx_orders_created_at` (`created_at`),
  CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `fulfillment_method` ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery'
  AFTER `payment_method`;

-- Keep customer photos directly on the customer record. The legacy table is
-- created if missing so this migration section remains safe to rerun.
ALTER TABLE `customer_users`
  ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL AFTER `shipping_country`;

CREATE TABLE IF NOT EXISTS `customer_profile_photos` (
  `customer_id` INT UNSIGNED NOT NULL,
  `image`       VARCHAR(255) NOT NULL,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  CONSTRAINT `fk_customer_profile_photos_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `customer_users` c
JOIN `customer_profile_photos` p ON p.`customer_id` = c.`id`
SET c.`avatar` = p.`image`
WHERE c.`avatar` IS NULL OR c.`avatar` = '';

DROP TABLE `customer_profile_photos`;

-- ---------------------------------------------------------
-- Order items (line items, with product snapshot)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`       INT UNSIGNED  NOT NULL,
  `product_id`     INT UNSIGNED  DEFAULT NULL,
  `product_name`   VARCHAR(200)  NOT NULL,
  `product_sku`    VARCHAR(80)   DEFAULT NULL,
  `product_image`  VARCHAR(255)  DEFAULT NULL,
  `product_color`  VARCHAR(80)   DEFAULT NULL,
  `unit_price`     DECIMAL(10,2) NOT NULL,
  `quantity`       INT           NOT NULL,
  `line_total`     DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oi_order`   (`order_id`),
  KEY `idx_oi_product` (`product_id`),
  CONSTRAINT `fk_oi_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Default settings additions
-- ---------------------------------------------------------
INSERT INTO `settings` (`key_name`, `value`) VALUES
  ('stock_report_per_page', '10'),
  ('currency_code',   'USD'),
  ('currency_symbol', '$'),
  ('shipping_fee',    '0.00'),
  ('store_address',   'Open Daily (9:00 AM – 6:30 PM)\nNo.289, Pyay Road, Sanchaung Township, Yangon\nTel & Viber: 09450742359. Please contact the phone number if you have any inquiries.')
ON DUPLICATE KEY UPDATE `key_name` = `key_name`;
