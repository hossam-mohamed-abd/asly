SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+02:00";
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `username` VARCHAR(60) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'staff',
  `phone` VARCHAR(30) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_channels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `channel_type` VARCHAR(30) NOT NULL DEFAULT 'phone',
  `label` VARCHAR(120) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `barcode` VARCHAR(100) NOT NULL,
  `code_type` VARCHAR(20) NOT NULL DEFAULT 'qr',
  `name` VARCHAR(200) NOT NULL,
  `name_en` VARCHAR(200) DEFAULT NULL,
  `category` VARCHAR(30) NOT NULL DEFAULT 'laptops',
  `condition_status` VARCHAR(20) NOT NULL DEFAULT 'new',
  `brand` VARCHAR(80) DEFAULT NULL,
  `specs` TEXT DEFAULT NULL,
  `specs_en` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `secondary_image_path` VARCHAR(255) DEFAULT NULL,
  `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `min_sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `quantity` INT NOT NULL DEFAULT 1,
  `low_stock_threshold` INT NOT NULL DEFAULT 2,
  `warranty_default` VARCHAR(120) DEFAULT 'ضمان الفحص والتجربة - أصلي تك ستور',
  `show_on_landing` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_products_barcode` (`barcode`),
  KEY `idx_products_category` (`category`),
  KEY `idx_products_landing` (`show_on_landing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `device_models` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `brand` VARCHAR(60) NOT NULL,
  `model_name` VARCHAR(120) NOT NULL,
  `model_name_ar` VARCHAR(120) DEFAULT NULL,
  `search_key` VARCHAR(220) NOT NULL DEFAULT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_device_model` (`brand`, `model_name`),
  KEY `idx_device_search` (`search_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shelves` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shelf_code` VARCHAR(20) NOT NULL,
  `label` VARCHAR(120) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_shelf_code` (`shelf_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `screen_protectors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `barcode` VARCHAR(100) NOT NULL,
  `code_type` VARCHAR(20) NOT NULL DEFAULT 'qr',
  `product_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `name_en` VARCHAR(200) DEFAULT NULL,
  `quality` VARCHAR(40) DEFAULT 'standard',
  `shelf_code` VARCHAR(20) DEFAULT NULL,
  `drawer_label` VARCHAR(40) DEFAULT NULL,
  `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `min_sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `quantity` INT NOT NULL DEFAULT 0,
  `low_stock_threshold` INT NOT NULL DEFAULT 3,
  `notes` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_screen_barcode` (`barcode`),
  KEY `idx_screen_shelf` (`shelf_code`),
  KEY `idx_screen_qty` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `screen_compatibility` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `screen_id` INT UNSIGNED NOT NULL,
  `model_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_screen_model` (`screen_id`, `model_id`),
  KEY `idx_compat_model` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `screen_movements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `screen_id` INT UNSIGNED NOT NULL,
  `movement_type` VARCHAR(20) NOT NULL DEFAULT 'adjust',
  `qty_before` INT NOT NULL DEFAULT 0,
  `qty_after` INT NOT NULL DEFAULT 0,
  `note` VARCHAR(200) DEFAULT NULL,
  `user_name` VARCHAR(120) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_screen_move` (`screen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `shelves` (`id`, `shelf_code`, `label`, `sort_order`) VALUES
(1, 'A', 'رف A', 1),
(2, 'B', 'رف B', 2),
(3, 'C', 'رف C', 3),
(4, 'D', 'رف D', 4);

INSERT IGNORE INTO `device_models` (`id`, `brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
(1, 'Apple', 'iPhone 11', 'ايفون 11', 'apple iphone 11 ايفون'),
(2, 'Apple', 'iPhone 11 Pro', 'ايفون 11 برو', 'apple iphone 11 pro ايفون برو'),
(3, 'Apple', 'iPhone 12', 'ايفون 12', 'apple iphone 12 ايفون'),
(4, 'Apple', 'iPhone 12 Pro', 'ايفون 12 برو', 'apple iphone 12 pro ايفون برو'),
(5, 'Apple', 'iPhone 13', 'ايفون 13', 'apple iphone 13 ايفون'),
(6, 'Apple', 'iPhone 13 Pro', 'ايفون 13 برو', 'apple iphone 13 pro ايفون برو'),
(7, 'Apple', 'iPhone 14', 'ايفون 14', 'apple iphone 14 ايفون'),
(8, 'Apple', 'iPhone 14 Pro', 'ايفون 14 برو', 'apple iphone 14 pro ايفون برو'),
(9, 'Apple', 'iPhone 15', 'ايفون 15', 'apple iphone 15 ايفون'),
(10, 'Apple', 'iPhone 15 Pro', 'ايفون 15 برو', 'apple iphone 15 pro ايفون برو'),
(11, 'Apple', 'iPhone XR', 'ايفون XR', 'apple iphone xr ايفون'),
(12, 'Apple', 'iPhone X', 'ايفون X', 'apple iphone x ايفون'),
(13, 'Samsung', 'Galaxy A12', 'سامسونج A12', 'samsung galaxy a12 سامسونج'),
(14, 'Samsung', 'Galaxy A13', 'سامسونج A13', 'samsung galaxy a13 سامسونج'),
(15, 'Samsung', 'Galaxy A23', 'سامسونج A23', 'samsung galaxy a23 سامسونج'),
(16, 'Samsung', 'Galaxy A32', 'سامسونج A32', 'samsung galaxy a32 سامسونج'),
(17, 'Samsung', 'Galaxy A52', 'سامسونج A52', 'samsung galaxy a52 سامسونج'),
(18, 'Samsung', 'Galaxy A72', 'سامسونج A72', 'samsung galaxy a72 سامسونج'),
(19, 'Samsung', 'Galaxy S21', 'سامسونج S21', 'samsung galaxy s21 سامسونج'),
(20, 'Samsung', 'Galaxy S22', 'سامسونج S22', 'samsung galaxy s22 سامسونج'),
(21, 'Xiaomi', 'Redmi Note 10', 'شاومي نوت 10', 'xiaomi redmi note 10 شاومي ريدمي'),
(22, 'Xiaomi', 'Redmi Note 11', 'شاومي نوت 11', 'xiaomi redmi note 11 شاومي ريدمي'),
(23, 'Xiaomi', 'Redmi Note 12', 'شاومي نوت 12', 'xiaomi redmi note 12 شاومي ريدمي'),
(24, 'Xiaomi', 'Redmi 9', 'شاومي 9', 'xiaomi redmi 9 شاومي ريدمي'),
(25, 'Oppo', 'Reno 6', 'اوبو رينو 6', 'oppo reno 6 اوبو'),
(26, 'Oppo', 'Reno 8', 'اوبو رينو 8', 'oppo reno 8 اوبو'),
(27, 'Oppo', 'A53', 'اوبو A53', 'oppo a53 اوبو'),
(28, 'Infinix', 'Hot 11', 'انفينكس هوت 11', 'infinix hot 11 انفينكس'),
(29, 'Infinix', 'Smart 6', 'انفينكس سمارت 6', 'infinix smart 6 انفينكس'),
(30, 'Vivo', 'Y21', 'فيفو Y21', 'vivo y21 فيفو'),
(31, 'Realme', 'C25', 'ريلمي C25', 'realme c25 ريلمي'),
(32, 'Nokia', 'G20', 'نوكيا G20', 'nokia g20 نوكيا'),
(33, 'Huawei', 'Y9 Prime', 'هواوي Y9 برايم', 'huawei y9 prime هواوي'),
(34, 'Honor', 'X7', 'هونر X7', 'honor x7 هونر'),
(35, 'Tecno', 'Spark 8', 'تكنو سبارك 8', 'tecno spark 8 تكنو');

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(40) NOT NULL,
  `public_token` VARCHAR(64) NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL DEFAULT 'عميل نقدي',
  `customer_phone` VARCHAR(40) DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_profit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'cash',
  `notes` TEXT DEFAULT NULL,
  `sold_by` INT UNSIGNED NOT NULL,
  `sold_by_name` VARCHAR(120) NOT NULL,
  `sale_date` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_invoices_number` (`invoice_number`),
  UNIQUE KEY `uniq_invoices_token` (`public_token`),
  KEY `idx_invoices_sale_date` (`sale_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `product_barcode` VARCHAR(100) DEFAULT NULL,
  `serial_imei` VARCHAR(120) DEFAULT NULL,
  `warranty_text` VARCHAR(120) DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit_list_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit_min_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit_sold_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `item_profit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `maintenance_tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_code` VARCHAR(40) NOT NULL,
  `qr_token` VARCHAR(64) NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(40) NOT NULL,
  `customer_alt_phone` VARCHAR(40) DEFAULT NULL,
  `customer_email` VARCHAR(160) DEFAULT NULL,
  `customer_details` TEXT DEFAULT NULL,
  `device_type` VARCHAR(40) NOT NULL DEFAULT 'mobile',
  `device_model` VARCHAR(180) NOT NULL,
  `serial_imei` VARCHAR(100) DEFAULT NULL,
  `device_specs` TEXT DEFAULT NULL,
  `device_passcode` VARCHAR(100) DEFAULT NULL,
  `physical_condition` TEXT DEFAULT NULL,
  `problem_description` TEXT NOT NULL,
  `estimated_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `parts_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `deposit_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `current_stage` VARCHAR(40) NOT NULL DEFAULT 'received',
  `unread_customer_notes` INT NOT NULL DEFAULT 0,
  `received_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_maint_ticket_code` (`ticket_code`),
  UNIQUE KEY `uniq_maint_qr_token` (`qr_token`),
  KEY `idx_maint_stage` (`current_stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `maintenance_notes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `stage_key` VARCHAR(40) NOT NULL,
  `sender_type` VARCHAR(20) NOT NULL DEFAULT 'admin',
  `sender_name` VARCHAR(120) NOT NULL,
  `note_text` TEXT NOT NULL,
  `is_stage_change` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_maint_notes_ticket` (`ticket_id`),
  CONSTRAINT `fk_maint_notes_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `maintenance_revenues` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_code_ref` VARCHAR(40) NOT NULL,
  `device_category` VARCHAR(40) NOT NULL DEFAULT 'mobile',
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `parts_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_profit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'cash',
  `delivered_by` INT UNSIGNED DEFAULT NULL,
  `delivered_by_name` VARCHAR(120) DEFAULT NULL,
  `revenue_date` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_maint_rev_date` (`revenue_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED DEFAULT NULL,
  `recipient_email` VARCHAR(160) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `stage_key` VARCHAR(40) DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'logged',
  `body_preview` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_logs_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`id`, `full_name`, `username`, `password_hash`, `role`, `phone`, `is_active`) VALUES
(1, 'Admin', 'admin', '$2y$12$jBd7WV5q5n2oxJwsLwmnWumXxQ/XFovzZi3XTFZXfT9UDXKNPj3ai', 'admin', NULL, 1),
(2, 'Ramadan Sallam', 'ramadan', '$2y$12$jBd7WV5q5n2oxJwsLwmnWumXxQ/XFovzZi3XTFZXfT9UDXKNPj3ai', 'admin', NULL, 1),
(3, 'Staff', 'staff1', '$2y$12$crDS8MaU/MJRyvgekhKwqO3LlyIuk37XR1vncbdxKH1bkKOyut1g.', 'staff', NULL, 1);
