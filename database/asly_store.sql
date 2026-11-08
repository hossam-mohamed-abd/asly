-- ============================================================
-- ASLY | أصلي TECH STORE - Complete MySQL Database Schema & Seed
-- Compatible with XAMPP (MySQL / MariaDB) & phpMyAdmin
-- Bilingual (Arabic + English) • Dual-Image Lookbook Catalog
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+02:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `asly_store`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `asly_store`;

-- ------------------------------------------------------------
-- 1. Users Table (Admin & Staff Roles)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 2. Settings Table (Store Identity, Contacts, QR URL, SMTP)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Dynamic Contact & Social Channels Table
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 4. Products Table (Bilingual + Dual Hover Images + 3-Tier Pricing)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 5. Sales Invoices Table (Single & Multi-Item POS Invoices)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 6. Invoice Items Table
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 7. Active Maintenance Tickets Table
--    (Wiped automatically upon final delivery to customer!)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 8. Maintenance Stage Notes (Admin & Customer Mutual Notes)
--    (Cascades / Wiped automatically upon final delivery!)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 9. Maintenance Completed Revenues Ledger
--    (Preserves ONLY financial figures after device data wipe!)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- 10. Email Outbox / Notification Logs
--     (Linked ticket logs wiped when device is delivered)
-- ------------------------------------------------------------
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

-- ============================================================
-- SEED DATA: Default Accounts, Bilingual Identity & Lookbook Catalog
-- ============================================================

-- Default Users:
-- 1) admin / admin123   (Admin - م. حسام محمد)
-- 2) ramadan / admin123 (Admin - م. رمضان سلام)
-- 3) staff1 / staff123  (Staff Employee - موظف المبيعات)
INSERT IGNORE INTO `users` (`id`, `full_name`, `username`, `password_hash`, `role`, `phone`, `is_active`) VALUES
(1, 'م. حسام محمد', 'admin', '$2y$12$jBd7WV5q5n2oxJwsLwmnWumXxQ/XFovzZi3XTFZXfT9UDXKNPj3ai', 'admin', '01015981677', 1),
(2, 'م. رمضان سلام', 'ramadan', '$2y$12$jBd7WV5q5n2oxJwsLwmnWumXxQ/XFovzZi3XTFZXfT9UDXKNPj3ai', 'admin', '01154639718', 1),
(3, 'موظف المبيعات والاستقبال', 'staff1', '$2y$12$UkWLoTXD2hP4D4Kl/0Rb1OsusUMq5LYO0IH3QmLk69XdN3U0ZsZOi', 'staff', '01015981677', 1);

-- Default Bilingual Brand & Store Settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name_ar', 'أصلي تك ستور'),
('store_name_en', 'ASLY TECH STORE'),
('store_tagline', 'المركز المتكامل لبيع وصيانة اللابتوبات والهواتف الذكية والساعات والإكسسوارات الأصلية'),
('store_tagline_en', 'Curated Laptops, Flagship Smartphones, Original Accessories & Precision QR-Tracked Repair Lab'),
('founders_line', 'إشراف وإدارة: م. حسام محمد (Eng. Hossam Mohamed)  •  م. رمضان سلام (Eng. Ramadan Sallam)'),
('founders_line_en', 'Managed & Supervised by Eng. Hossam Mohamed  •  Eng. Ramadan Sallam'),
('primary_whatsapp', '201015981677'),
('primary_phone', '01015981677'),
('secondary_phone', '01154639718'),
('store_address', 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا'),
('store_address_en', 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum'),
('working_hours', 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً'),
('working_hours_en', 'Daily from 11:00 AM to 11:00 PM'),
('primary_email', 'contact@aslystore.com'),
('google_maps_url', 'https://www.google.com/maps/search/?api=1&query=%D8%A7%D9%84%D9%81%D9%8A%D9%88%D9%85+%D8%B3%D9%86%D9%88%D8%B1%D8%B3+%D8%AA%D8%B1%D8%B3%D8%A7+%D8%B4%D8%A7%D8%B1%D8%B9+%D8%A7%D9%84%D8%B3%D9%88%D9%82+%D9%83%D9%86%D9%8A%D8%B3%D8%A9+%D8%AA%D8%B1%D8%B3%D8%A7'),
('social_facebook', 'https://facebook.com/aslytechstore'),
('social_instagram', 'https://instagram.com/aslytechstore'),
('social_tiktok', 'https://tiktok.com/@aslytechstore'),
('social_telegram', 'https://t.me/aslytechstore'),
('social_youtube', 'https://youtube.com/@aslytechstore'),
('social_x', 'https://x.com/aslytechstore'),
('about_story_ar', 'أصلي تك ستور ليس مجرد معرض تقني تقليدي؛ بل استوديو هندسي ومعمل فحص وصيانة دقيقة تأسس ليضع معياراً حقيقياً للثقة والأصالة. كل جهاز يمر عبر بروتوكول فحص متعدد النقاط، وكل عملية صيانة موثقة لحظة بلحظة عبر نظام تتبع QR شفاف يحمي خصوصيتك وبياناتك.'),
('about_story_en', 'ASLY TECH STORE is not an ordinary retail counter — it is an engineering-first technology studio and precision diagnostic lab built to set an uncompromising standard for hardware authenticity. Every device undergoes multi-point verification, and every repair is documented in real time via transparent QR telemetry.'),
('invoice_footer_note', 'جميع الأجهزة تخضع لضمان الفحص المعتمد من مركز أصلي ASLY TECH STORE. يرجى الاحتفاظ بالفاتورة عند المراجعة.'),
('invoice_footer_note_en', 'All devices include ASLY TECH STORE certified hardware inspection & warranty. Please retain this invoice for service.'),
('public_base_url', ''),
('smtp_enabled', '0'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_encryption', 'tls'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_from_email', 'noreply@aslystore.com'),
('smtp_from_name', 'ASLY | أصلي TECH STORE');

-- Default Contact Channels (Editable & Extensible from Admin Dashboard)
INSERT IGNORE INTO `contact_channels` (`id`, `channel_type`, `label`, `value`, `url`, `sort_order`, `is_active`) VALUES
(1, 'phone', 'مبيعات وصيانة - م. حسام محمد', '01015981677', 'tel:01015981677', 1, 1),
(2, 'phone', 'مبيعات وصيانة - م. رمضان سلام', '01154639718', 'tel:01154639718', 2, 1),
(3, 'whatsapp', 'واتساب الاستفسارات والمبيعات', '01015981677', 'https://wa.me/201015981677', 3, 1),
(4, 'whatsapp', 'واتساب قسم الصيانة والدعم الفني', '01154639718', 'https://wa.me/201154639718', 4, 1);
