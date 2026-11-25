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

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Apple', 'iPhone 6', 'ايفون 6', 'apple iphone 6 ايفون 6 ابل'),
('Apple', 'iPhone 6s', 'ايفون 6s', 'apple iphone 6s ايفون 6s ابل'),
('Apple', 'iPhone 7', 'ايفون 7', 'apple iphone 7 ايفون 7 ابل'),
('Apple', 'iPhone 8', 'ايفون 8', 'apple iphone 8 ايفون 8 ابل'),
('Apple', 'iPhone SE 2020', 'ايفون SE 2020', 'apple iphone se 2020 ايفون se 2020 ابل'),
('Apple', 'iPhone SE 2022', 'ايفون SE 2022', 'apple iphone se 2022 ايفون se 2022 ابل'),
('Apple', 'iPhone 6 Plus', 'ايفون 6 بلس', 'apple iphone 6 plus ايفون 6 بلس ابل'),
('Apple', 'iPhone 6s Plus', 'ايفون 6s بلس', 'apple iphone 6s plus ايفون 6s بلس ابل'),
('Apple', 'iPhone 7 Plus', 'ايفون 7 بلس', 'apple iphone 7 plus ايفون 7 بلس ابل'),
('Apple', 'iPhone 8 Plus', 'ايفون 8 بلس', 'apple iphone 8 plus ايفون 8 بلس ابل'),
('Apple', 'iPhone X', 'ايفون X', 'apple iphone x ايفون x ابل'),
('Apple', 'iPhone XS', 'ايفون XS', 'apple iphone xs ايفون xs ابل'),
('Apple', 'iPhone 11 Pro', 'ايفون 11 برو', 'apple iphone 11 pro ايفون 11 برو ابل'),
('Apple', 'iPhone XR', 'ايفون XR', 'apple iphone xr ايفون xr ابل'),
('Apple', 'iPhone 11', 'ايفون 11', 'apple iphone 11 ايفون 11 ابل'),
('Apple', 'iPhone XS Max', 'ايفون XS ماكس', 'apple iphone xs max ايفون xs ماكس ابل'),
('Apple', 'iPhone 11 Pro Max', 'ايفون 11 برو ماكس', 'apple iphone 11 pro max ايفون 11 برو ماكس ابل'),
('Apple', 'iPhone 12 mini', 'ايفون 12 ميني', 'apple iphone 12 mini ايفون 12 ميني ابل'),
('Apple', 'iPhone 13 mini', 'ايفون 13 ميني', 'apple iphone 13 mini ايفون 13 ميني ابل'),
('Apple', 'iPhone 12', 'ايفون 12', 'apple iphone 12 ايفون 12 ابل'),
('Apple', 'iPhone 12 Pro', 'ايفون 12 برو', 'apple iphone 12 pro ايفون 12 برو ابل'),
('Apple', 'iPhone 12 Pro Max', 'ايفون 12 برو ماكس', 'apple iphone 12 pro max ايفون 12 برو ماكس ابل'),
('Apple', 'iPhone 13', 'ايفون 13', 'apple iphone 13 ايفون 13 ابل'),
('Apple', 'iPhone 13 Pro', 'ايفون 13 برو', 'apple iphone 13 pro ايفون 13 برو ابل'),
('Apple', 'iPhone 14', 'ايفون 14', 'apple iphone 14 ايفون 14 ابل'),
('Apple', 'iPhone 16e', 'ايفون 16e', 'apple iphone 16e ايفون 16e ابل'),
('Apple', 'iPhone 13 Pro Max', 'ايفون 13 برو ماكس', 'apple iphone 13 pro max ايفون 13 برو ماكس ابل'),
('Apple', 'iPhone 14 Plus', 'ايفون 14 بلس', 'apple iphone 14 plus ايفون 14 بلس ابل'),
('Apple', 'iPhone 14 Pro', 'ايفون 14 برو', 'apple iphone 14 pro ايفون 14 برو ابل'),
('Apple', 'iPhone 15', 'ايفون 15', 'apple iphone 15 ايفون 15 ابل'),
('Apple', 'iPhone 14 Pro Max', 'ايفون 14 برو ماكس', 'apple iphone 14 pro max ايفون 14 برو ماكس ابل'),
('Apple', 'iPhone 15 Plus', 'ايفون 15 بلس', 'apple iphone 15 plus ايفون 15 بلس ابل'),
('Apple', 'iPhone 15 Pro', 'ايفون 15 برو', 'apple iphone 15 pro ايفون 15 برو ابل'),
('Apple', 'iPhone 16', 'ايفون 16', 'apple iphone 16 ايفون 16 ابل'),
('Apple', 'iPhone 15 Pro Max', 'ايفون 15 برو ماكس', 'apple iphone 15 pro max ايفون 15 برو ماكس ابل'),
('Apple', 'iPhone 16 Plus', 'ايفون 16 بلس', 'apple iphone 16 plus ايفون 16 بلس ابل'),
('Apple', 'iPhone 16 Pro', 'ايفون 16 برو', 'apple iphone 16 pro ايفون 16 برو ابل'),
('Apple', 'iPhone 17', 'ايفون 17', 'apple iphone 17 ايفون 17 ابل'),
('Apple', 'iPhone 17 Pro', 'ايفون 17 برو', 'apple iphone 17 pro ايفون 17 برو ابل'),
('Apple', 'iPhone 16 Pro Max', 'ايفون 16 برو ماكس', 'apple iphone 16 pro max ايفون 16 برو ماكس ابل'),
('Apple', 'iPhone 17 Pro Max', 'ايفون 17 برو ماكس', 'apple iphone 17 pro max ايفون 17 برو ماكس ابل'),
('Apple', 'iPhone 17 Air', 'ايفون 17 اير', 'apple iphone 17 air ايفون 17 اير ابل'),
('Samsung', 'Galaxy A01', 'سامسونج A01', 'samsung galaxy a01 سامسونج a01 سامسونج'),
('Samsung', 'Galaxy A02', 'سامسونج A02', 'samsung galaxy a02 سامسونج a02 سامسونج'),
('Samsung', 'Galaxy A02s', 'سامسونج A02s', 'samsung galaxy a02s سامسونج a02s سامسونج'),
('Samsung', 'Galaxy A03', 'سامسونج A03', 'samsung galaxy a03 سامسونج a03 سامسونج'),
('Samsung', 'Galaxy A03s', 'سامسونج A03s', 'samsung galaxy a03s سامسونج a03s سامسونج'),
('Samsung', 'Galaxy A04', 'سامسونج A04', 'samsung galaxy a04 سامسونج a04 سامسونج'),
('Samsung', 'Galaxy A04s', 'سامسونج A04s', 'samsung galaxy a04s سامسونج a04s سامسونج'),
('Samsung', 'Galaxy A04e', 'سامسونج A04e', 'samsung galaxy a04e سامسونج a04e سامسونج'),
('Samsung', 'Galaxy A05', 'سامسونج A05', 'samsung galaxy a05 سامسونج a05 سامسونج'),
('Samsung', 'Galaxy A05s', 'سامسونج A05s', 'samsung galaxy a05s سامسونج a05s سامسونج'),
('Samsung', 'Galaxy A06', 'سامسونج A06', 'samsung galaxy a06 سامسونج a06 سامسونج'),
('Samsung', 'Galaxy A10', 'سامسونج A10', 'samsung galaxy a10 سامسونج a10 سامسونج'),
('Samsung', 'Galaxy A10s', 'سامسونج A10s', 'samsung galaxy a10s سامسونج a10s سامسونج'),
('Samsung', 'Galaxy A11', 'سامسونج A11', 'samsung galaxy a11 سامسونج a11 سامسونج'),
('Samsung', 'Galaxy A12', 'سامسونج A12', 'samsung galaxy a12 سامسونج a12 سامسونج'),
('Samsung', 'Galaxy A13', 'سامسونج A13', 'samsung galaxy a13 سامسونج a13 سامسونج'),
('Samsung', 'Galaxy A14', 'سامسونج A14', 'samsung galaxy a14 سامسونج a14 سامسونج'),
('Samsung', 'Galaxy A15', 'سامسونج A15', 'samsung galaxy a15 سامسونج a15 سامسونج');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Samsung', 'Galaxy A16', 'سامسونج A16', 'samsung galaxy a16 سامسونج a16 سامسونج'),
('Samsung', 'Galaxy A17', 'سامسونج A17', 'samsung galaxy a17 سامسونج a17 سامسونج'),
('Samsung', 'Galaxy A20', 'سامسونج A20', 'samsung galaxy a20 سامسونج a20 سامسونج'),
('Samsung', 'Galaxy A20s', 'سامسونج A20s', 'samsung galaxy a20s سامسونج a20s سامسونج'),
('Samsung', 'Galaxy A21s', 'سامسونج A21s', 'samsung galaxy a21s سامسونج a21s سامسونج'),
('Samsung', 'Galaxy A22', 'سامسونج A22', 'samsung galaxy a22 سامسونج a22 سامسونج'),
('Samsung', 'Galaxy A23', 'سامسونج A23', 'samsung galaxy a23 سامسونج a23 سامسونج'),
('Samsung', 'Galaxy A24', 'سامسونج A24', 'samsung galaxy a24 سامسونج a24 سامسونج'),
('Samsung', 'Galaxy A25', 'سامسونج A25', 'samsung galaxy a25 سامسونج a25 سامسونج'),
('Samsung', 'Galaxy A26', 'سامسونج A26', 'samsung galaxy a26 سامسونج a26 سامسونج'),
('Samsung', 'Galaxy A30', 'سامسونج A30', 'samsung galaxy a30 سامسونج a30 سامسونج'),
('Samsung', 'Galaxy A30s', 'سامسونج A30s', 'samsung galaxy a30s سامسونج a30s سامسونج'),
('Samsung', 'Galaxy A31', 'سامسونج A31', 'samsung galaxy a31 سامسونج a31 سامسونج'),
('Samsung', 'Galaxy A32', 'سامسونج A32', 'samsung galaxy a32 سامسونج a32 سامسونج'),
('Samsung', 'Galaxy A33', 'سامسونج A33', 'samsung galaxy a33 سامسونج a33 سامسونج'),
('Samsung', 'Galaxy A34', 'سامسونج A34', 'samsung galaxy a34 سامسونج a34 سامسونج'),
('Samsung', 'Galaxy A35', 'سامسونج A35', 'samsung galaxy a35 سامسونج a35 سامسونج'),
('Samsung', 'Galaxy A36', 'سامسونج A36', 'samsung galaxy a36 سامسونج a36 سامسونج'),
('Samsung', 'Galaxy A50', 'سامسونج A50', 'samsung galaxy a50 سامسونج a50 سامسونج'),
('Samsung', 'Galaxy A50s', 'سامسونج A50s', 'samsung galaxy a50s سامسونج a50s سامسونج'),
('Samsung', 'Galaxy A51', 'سامسونج A51', 'samsung galaxy a51 سامسونج a51 سامسونج'),
('Samsung', 'Galaxy A52', 'سامسونج A52', 'samsung galaxy a52 سامسونج a52 سامسونج'),
('Samsung', 'Galaxy A52s', 'سامسونج A52s', 'samsung galaxy a52s سامسونج a52s سامسونج'),
('Samsung', 'Galaxy A53', 'سامسونج A53', 'samsung galaxy a53 سامسونج a53 سامسونج'),
('Samsung', 'Galaxy A54', 'سامسونج A54', 'samsung galaxy a54 سامسونج a54 سامسونج'),
('Samsung', 'Galaxy A55', 'سامسونج A55', 'samsung galaxy a55 سامسونج a55 سامسونج'),
('Samsung', 'Galaxy A56', 'سامسونج A56', 'samsung galaxy a56 سامسونج a56 سامسونج'),
('Samsung', 'Galaxy A70', 'سامسونج A70', 'samsung galaxy a70 سامسونج a70 سامسونج'),
('Samsung', 'Galaxy A71', 'سامسونج A71', 'samsung galaxy a71 سامسونج a71 سامسونج'),
('Samsung', 'Galaxy A72', 'سامسونج A72', 'samsung galaxy a72 سامسونج a72 سامسونج'),
('Samsung', 'Galaxy A73', 'سامسونج A73', 'samsung galaxy a73 سامسونج a73 سامسونج'),
('Samsung', 'Galaxy M10', 'سامسونج M10', 'samsung galaxy m10 سامسونج m10 سامسونج'),
('Samsung', 'Galaxy M11', 'سامسونج M11', 'samsung galaxy m11 سامسونج m11 سامسونج'),
('Samsung', 'Galaxy M12', 'سامسونج M12', 'samsung galaxy m12 سامسونج m12 سامسونج'),
('Samsung', 'Galaxy M13', 'سامسونج M13', 'samsung galaxy m13 سامسونج m13 سامسونج'),
('Samsung', 'Galaxy M14', 'سامسونج M14', 'samsung galaxy m14 سامسونج m14 سامسونج'),
('Samsung', 'Galaxy M15', 'سامسونج M15', 'samsung galaxy m15 سامسونج m15 سامسونج'),
('Samsung', 'Galaxy M21', 'سامسونج M21', 'samsung galaxy m21 سامسونج m21 سامسونج'),
('Samsung', 'Galaxy M22', 'سامسونج M22', 'samsung galaxy m22 سامسونج m22 سامسونج'),
('Samsung', 'Galaxy M31', 'سامسونج M31', 'samsung galaxy m31 سامسونج m31 سامسونج'),
('Samsung', 'Galaxy M32', 'سامسونج M32', 'samsung galaxy m32 سامسونج m32 سامسونج'),
('Samsung', 'Galaxy M33', 'سامسونج M33', 'samsung galaxy m33 سامسونج m33 سامسونج'),
('Samsung', 'Galaxy M34', 'سامسونج M34', 'samsung galaxy m34 سامسونج m34 سامسونج'),
('Samsung', 'Galaxy M52', 'سامسونج M52', 'samsung galaxy m52 سامسونج m52 سامسونج'),
('Samsung', 'Galaxy M53', 'سامسونج M53', 'samsung galaxy m53 سامسونج m53 سامسونج'),
('Samsung', 'Galaxy S8', 'سامسونج S8', 'samsung galaxy s8 سامسونج s8 سامسونج'),
('Samsung', 'Galaxy S9', 'سامسونج S9', 'samsung galaxy s9 سامسونج s9 سامسونج'),
('Samsung', 'Galaxy S10', 'سامسونج S10', 'samsung galaxy s10 سامسونج s10 سامسونج'),
('Samsung', 'Galaxy S10e', 'سامسونج S10e', 'samsung galaxy s10e سامسونج s10e سامسونج'),
('Samsung', 'Galaxy S10+', 'سامسونج S10 بلس', 'samsung galaxy s10+ سامسونج s10 بلس سامسونج'),
('Samsung', 'Galaxy S10 Lite', 'سامسونج S10 لايت', 'samsung galaxy s10 lite سامسونج s10 لايت سامسونج'),
('Samsung', 'Galaxy S20', 'سامسونج S20', 'samsung galaxy s20 سامسونج s20 سامسونج'),
('Samsung', 'Galaxy S20 5G', 'سامسونج S20 5G', 'samsung galaxy s20 5g سامسونج s20 5g سامسونج'),
('Samsung', 'Galaxy S20 FE', 'سامسونج S20 FE', 'samsung galaxy s20 fe سامسونج s20 fe سامسونج'),
('Samsung', 'Galaxy S20+', 'سامسونج S20 بلس', 'samsung galaxy s20+ سامسونج s20 بلس سامسونج'),
('Samsung', 'Galaxy S20 Ultra', 'سامسونج S20 الترا', 'samsung galaxy s20 ultra سامسونج s20 الترا سامسونج'),
('Samsung', 'Galaxy S21', 'سامسونج S21', 'samsung galaxy s21 سامسونج s21 سامسونج'),
('Samsung', 'Galaxy S21 FE', 'سامسونج S21 FE', 'samsung galaxy s21 fe سامسونج s21 fe سامسونج'),
('Samsung', 'Galaxy S22', 'سامسونج S22', 'samsung galaxy s22 سامسونج s22 سامسونج'),
('Samsung', 'Galaxy S23', 'سامسونج S23', 'samsung galaxy s23 سامسونج s23 سامسونج');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Samsung', 'Galaxy S24', 'سامسونج S24', 'samsung galaxy s24 سامسونج s24 سامسونج'),
('Samsung', 'Galaxy S25', 'سامسونج S25', 'samsung galaxy s25 سامسونج s25 سامسونج'),
('Samsung', 'Galaxy S21+', 'سامسونج S21 بلس', 'samsung galaxy s21+ سامسونج s21 بلس سامسونج'),
('Samsung', 'Galaxy S22+', 'سامسونج S22 بلس', 'samsung galaxy s22+ سامسونج s22 بلس سامسونج'),
('Samsung', 'Galaxy S23+', 'سامسونج S23 بلس', 'samsung galaxy s23+ سامسونج s23 بلس سامسونج'),
('Samsung', 'Galaxy S24+', 'سامسونج S24 بلس', 'samsung galaxy s24+ سامسونج s24 بلس سامسونج'),
('Samsung', 'Galaxy S25+', 'سامسونج S25 بلس', 'samsung galaxy s25+ سامسونج s25 بلس سامسونج'),
('Samsung', 'Galaxy S21 Ultra', 'سامسونج S21 الترا', 'samsung galaxy s21 ultra سامسونج s21 الترا سامسونج'),
('Samsung', 'Galaxy S22 Ultra', 'سامسونج S22 الترا', 'samsung galaxy s22 ultra سامسونج s22 الترا سامسونج'),
('Samsung', 'Galaxy S23 Ultra', 'سامسونج S23 الترا', 'samsung galaxy s23 ultra سامسونج s23 الترا سامسونج'),
('Samsung', 'Galaxy S24 Ultra', 'سامسونج S24 الترا', 'samsung galaxy s24 ultra سامسونج s24 الترا سامسونج'),
('Samsung', 'Galaxy S25 Ultra', 'سامسونج S25 الترا', 'samsung galaxy s25 ultra سامسونج s25 الترا سامسونج'),
('Samsung', 'Galaxy S25 Edge', 'سامسونج S25 ايدج', 'samsung galaxy s25 edge سامسونج s25 ايدج سامسونج'),
('Samsung', 'Galaxy S23 FE', 'سامسونج S23 FE', 'samsung galaxy s23 fe سامسونج s23 fe سامسونج'),
('Samsung', 'Galaxy S24 FE', 'سامسونج S24 FE', 'samsung galaxy s24 fe سامسونج s24 fe سامسونج'),
('Samsung', 'Galaxy Note 8', 'سامسونج نوت 8', 'samsung galaxy note 8 سامسونج نوت 8 سامسونج'),
('Samsung', 'Galaxy Note 9', 'سامسونج نوت 9', 'samsung galaxy note 9 سامسونج نوت 9 سامسونج'),
('Samsung', 'Galaxy Note 10', 'سامسونج نوت 10', 'samsung galaxy note 10 سامسونج نوت 10 سامسونج'),
('Samsung', 'Galaxy Note 10+', 'سامسونج نوت 10 بلس', 'samsung galaxy note 10+ سامسونج نوت 10 بلس سامسونج'),
('Samsung', 'Galaxy Note 20', 'سامسونج نوت 20', 'samsung galaxy note 20 سامسونج نوت 20 سامسونج'),
('Samsung', 'Galaxy Note 20 Ultra', 'سامسونج نوت 20 الترا', 'samsung galaxy note 20 ultra سامسونج نوت 20 الترا سامسونج'),
('Samsung', 'Galaxy Z Flip3', 'سامسونج Z فليب 3', 'samsung galaxy z flip3 سامسونج z فليب 3 سامسونج'),
('Samsung', 'Galaxy Z Flip4', 'سامسونج Z فليب 4', 'samsung galaxy z flip4 سامسونج z فليب 4 سامسونج'),
('Samsung', 'Galaxy Z Flip5', 'سامسونج Z فليب 5', 'samsung galaxy z flip5 سامسونج z فليب 5 سامسونج'),
('Samsung', 'Galaxy Z Flip6', 'سامسونج Z فليب 6', 'samsung galaxy z flip6 سامسونج z فليب 6 سامسونج'),
('Samsung', 'Galaxy Z Fold3', 'سامسونج Z فولد 3', 'samsung galaxy z fold3 سامسونج z فولد 3 سامسونج'),
('Samsung', 'Galaxy Z Fold4', 'سامسونج Z فولد 4', 'samsung galaxy z fold4 سامسونج z فولد 4 سامسونج'),
('Samsung', 'Galaxy Z Fold5', 'سامسونج Z فولد 5', 'samsung galaxy z fold5 سامسونج z فولد 5 سامسونج'),
('Samsung', 'Galaxy Z Fold6', 'سامسونج Z فولد 6', 'samsung galaxy z fold6 سامسونج z فولد 6 سامسونج'),
('Xiaomi', 'Redmi 8A', 'ريدمي 8A', 'xiaomi redmi 8a ريدمي 8a شاومي'),
('Xiaomi', 'Redmi 9A', 'ريدمي 9A', 'xiaomi redmi 9a ريدمي 9a شاومي'),
('Xiaomi', 'Redmi 10A', 'ريدمي 10A', 'xiaomi redmi 10a ريدمي 10a شاومي'),
('Xiaomi', 'Redmi A1', 'ريدمي A1', 'xiaomi redmi a1 ريدمي a1 شاومي'),
('Xiaomi', 'Redmi A2', 'ريدمي A2', 'xiaomi redmi a2 ريدمي a2 شاومي'),
('Xiaomi', 'Redmi A3', 'ريدمي A3', 'xiaomi redmi a3 ريدمي a3 شاومي'),
('Xiaomi', 'Redmi A5', 'ريدمي A5', 'xiaomi redmi a5 ريدمي a5 شاومي'),
('Xiaomi', 'Redmi 9', 'ريدمي 9', 'xiaomi redmi 9 ريدمي 9 شاومي'),
('Xiaomi', 'Redmi 9C', 'ريدمي 9C', 'xiaomi redmi 9c ريدمي 9c شاومي'),
('Xiaomi', 'Redmi 10', 'ريدمي 10', 'xiaomi redmi 10 ريدمي 10 شاومي'),
('Xiaomi', 'Redmi 10C', 'ريدمي 10C', 'xiaomi redmi 10c ريدمي 10c شاومي'),
('Xiaomi', 'Redmi 12', 'ريدمي 12', 'xiaomi redmi 12 ريدمي 12 شاومي'),
('Xiaomi', 'Redmi 12C', 'ريدمي 12C', 'xiaomi redmi 12c ريدمي 12c شاومي'),
('Xiaomi', 'Redmi 13C', 'ريدمي 13C', 'xiaomi redmi 13c ريدمي 13c شاومي'),
('Xiaomi', 'Redmi 14C', 'ريدمي 14C', 'xiaomi redmi 14c ريدمي 14c شاومي'),
('Xiaomi', 'Redmi 15C', 'ريدمي 15C', 'xiaomi redmi 15c ريدمي 15c شاومي'),
('Xiaomi', 'Redmi Note 7', 'ريدمي نوت 7', 'xiaomi redmi note 7 ريدمي نوت 7 شاومي'),
('Xiaomi', 'Redmi Note 8', 'ريدمي نوت 8', 'xiaomi redmi note 8 ريدمي نوت 8 شاومي'),
('Xiaomi', 'Redmi Note 8 Pro', 'ريدمي نوت 8 برو', 'xiaomi redmi note 8 pro ريدمي نوت 8 برو شاومي'),
('Xiaomi', 'Redmi Note 9', 'ريدمي نوت 9', 'xiaomi redmi note 9 ريدمي نوت 9 شاومي'),
('Xiaomi', 'Redmi Note 9S', 'ريدمي نوت 9s', 'xiaomi redmi note 9s ريدمي نوت 9s شاومي'),
('Xiaomi', 'Redmi Note 10', 'ريدمي نوت 10', 'xiaomi redmi note 10 ريدمي نوت 10 شاومي'),
('Xiaomi', 'Redmi Note 10S', 'ريدمي نوت 10s', 'xiaomi redmi note 10s ريدمي نوت 10s شاومي'),
('Xiaomi', 'Redmi Note 11', 'ريدمي نوت 11', 'xiaomi redmi note 11 ريدمي نوت 11 شاومي'),
('Xiaomi', 'Redmi Note 11S', 'ريدمي نوت 11s', 'xiaomi redmi note 11s ريدمي نوت 11s شاومي'),
('Xiaomi', 'Redmi Note 12', 'ريدمي نوت 12', 'xiaomi redmi note 12 ريدمي نوت 12 شاومي'),
('Xiaomi', 'Redmi Note 12S', 'ريدمي نوت 12s', 'xiaomi redmi note 12s ريدمي نوت 12s شاومي'),
('Xiaomi', 'Redmi Note 13', 'ريدمي نوت 13', 'xiaomi redmi note 13 ريدمي نوت 13 شاومي'),
('Xiaomi', 'Redmi Note 14', 'ريدمي نوت 14', 'xiaomi redmi note 14 ريدمي نوت 14 شاومي'),
('Xiaomi', 'Redmi Note 14 Pro', 'ريدمي نوت 14 برو', 'xiaomi redmi note 14 pro ريدمي نوت 14 برو شاومي'),
('Xiaomi', 'Redmi Note 15', 'ريدمي نوت 15', 'xiaomi redmi note 15 ريدمي نوت 15 شاومي');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Xiaomi', 'Xiaomi 11', 'شاومي 11', 'xiaomi xiaomi 11 شاومي 11 شاومي'),
('Xiaomi', 'Xiaomi 11 Lite', 'شاومي 11 لايت', 'xiaomi xiaomi 11 lite شاومي 11 لايت شاومي'),
('Xiaomi', 'Xiaomi 12', 'شاومي 12', 'xiaomi xiaomi 12 شاومي 12 شاومي'),
('Xiaomi', 'Xiaomi 12 Lite', 'شاومي 12 لايت', 'xiaomi xiaomi 12 lite شاومي 12 لايت شاومي'),
('Xiaomi', 'Xiaomi 12T', 'شاومي 12T', 'xiaomi xiaomi 12t شاومي 12t شاومي'),
('Xiaomi', 'Xiaomi 13', 'شاومي 13', 'xiaomi xiaomi 13 شاومي 13 شاومي'),
('Xiaomi', 'Xiaomi 13 Lite', 'شاومي 13 لايت', 'xiaomi xiaomi 13 lite شاومي 13 لايت شاومي'),
('Xiaomi', 'Xiaomi 13T', 'شاومي 13T', 'xiaomi xiaomi 13t شاومي 13t شاومي'),
('Xiaomi', 'Xiaomi 14', 'شاومي 14', 'xiaomi xiaomi 14 شاومي 14 شاومي'),
('Xiaomi', 'Xiaomi 14T', 'شاومي 14T', 'xiaomi xiaomi 14t شاومي 14t شاومي'),
('Xiaomi', 'Xiaomi 15', 'شاومي 15', 'xiaomi xiaomi 15 شاومي 15 شاومي'),
('Xiaomi', 'Poco X3', 'بوكو X3', 'xiaomi poco x3 بوكو x3 شاومي'),
('Xiaomi', 'Poco X4', 'بوكو X4', 'xiaomi poco x4 بوكو x4 شاومي'),
('Xiaomi', 'Poco X5', 'بوكو X5', 'xiaomi poco x5 بوكو x5 شاومي'),
('Xiaomi', 'Poco X6', 'بوكو X6', 'xiaomi poco x6 بوكو x6 شاومي'),
('Xiaomi', 'Poco X7', 'بوكو X7', 'xiaomi poco x7 بوكو x7 شاومي'),
('Xiaomi', 'Poco M3', 'بوكو M3', 'xiaomi poco m3 بوكو m3 شاومي'),
('Xiaomi', 'Poco M4', 'بوكو M4', 'xiaomi poco m4 بوكو m4 شاومي'),
('Xiaomi', 'Poco M5', 'بوكو M5', 'xiaomi poco m5 بوكو m5 شاومي'),
('Xiaomi', 'Poco M6', 'بوكو M6', 'xiaomi poco m6 بوكو m6 شاومي'),
('Xiaomi', 'Poco F3', 'بوكو F3', 'xiaomi poco f3 بوكو f3 شاومي'),
('Xiaomi', 'Poco F4', 'بوكو F4', 'xiaomi poco f4 بوكو f4 شاومي'),
('Xiaomi', 'Poco F5', 'بوكو F5', 'xiaomi poco f5 بوكو f5 شاومي'),
('Xiaomi', 'Poco F6', 'بوكو F6', 'xiaomi poco f6 بوكو f6 شاومي'),
('Xiaomi', 'Poco F7', 'بوكو F7', 'xiaomi poco f7 بوكو f7 شاومي'),
('Xiaomi', 'Poco C40', 'بوكو C40', 'xiaomi poco c40 بوكو c40 شاومي'),
('Xiaomi', 'Poco C55', 'بوكو C55', 'xiaomi poco c55 بوكو c55 شاومي'),
('Xiaomi', 'Poco C65', 'بوكو C65', 'xiaomi poco c65 بوكو c65 شاومي'),
('Xiaomi', 'Poco C75', 'بوكو C75', 'xiaomi poco c75 بوكو c75 شاومي'),
('Realme', 'C11', 'ريلمي C11', 'realme c11 ريلمي c11 ريلمي'),
('Realme', 'C12', 'ريلمي C12', 'realme c12 ريلمي c12 ريلمي'),
('Realme', 'C15', 'ريلمي C15', 'realme c15 ريلمي c15 ريلمي'),
('Realme', 'C17', 'ريلمي C17', 'realme c17 ريلمي c17 ريلمي'),
('Realme', 'C18', 'ريلمي C18', 'realme c18 ريلمي c18 ريلمي'),
('Realme', 'C20', 'ريلمي C20', 'realme c20 ريلمي c20 ريلمي'),
('Realme', 'C21', 'ريلمي C21', 'realme c21 ريلمي c21 ريلمي'),
('Realme', 'C21Y', 'ريلمي C21Y', 'realme c21y ريلمي c21y ريلمي'),
('Realme', 'C22', 'ريلمي C22', 'realme c22 ريلمي c22 ريلمي'),
('Realme', 'C23', 'ريلمي C23', 'realme c23 ريلمي c23 ريلمي'),
('Realme', 'C24', 'ريلمي C24', 'realme c24 ريلمي c24 ريلمي'),
('Realme', 'C25', 'ريلمي C25', 'realme c25 ريلمي c25 ريلمي'),
('Realme', 'C25Y', 'ريلمي C25Y', 'realme c25y ريلمي c25y ريلمي'),
('Realme', 'C30', 'ريلمي C30', 'realme c30 ريلمي c30 ريلمي'),
('Realme', 'C31', 'ريلمي C31', 'realme c31 ريلمي c31 ريلمي'),
('Realme', 'C33', 'ريلمي C33', 'realme c33 ريلمي c33 ريلمي'),
('Realme', 'C35', 'ريلمي C35', 'realme c35 ريلمي c35 ريلمي'),
('Realme', 'C51', 'ريلمي C51', 'realme c51 ريلمي c51 ريلمي'),
('Realme', 'C53', 'ريلمي C53', 'realme c53 ريلمي c53 ريلمي'),
('Realme', 'C55', 'ريلمي C55', 'realme c55 ريلمي c55 ريلمي'),
('Realme', 'C61', 'ريلمي C61', 'realme c61 ريلمي c61 ريلمي'),
('Realme', 'C63', 'ريلمي C63', 'realme c63 ريلمي c63 ريلمي'),
('Realme', 'C65', 'ريلمي C65', 'realme c65 ريلمي c65 ريلمي'),
('Realme', 'C67', 'ريلمي C67', 'realme c67 ريلمي c67 ريلمي'),
('Realme', 'C75', 'ريلمي C75', 'realme c75 ريلمي c75 ريلمي'),
('Realme', 'Realme 7', 'ريلمي 7', 'realme realme 7 ريلمي 7 ريلمي'),
('Realme', 'Realme 8', 'ريلمي 8', 'realme realme 8 ريلمي 8 ريلمي'),
('Realme', 'Realme 9', 'ريلمي 9', 'realme realme 9 ريلمي 9 ريلمي'),
('Realme', 'Realme 10', 'ريلمي 10', 'realme realme 10 ريلمي 10 ريلمي'),
('Realme', 'Realme 11', 'ريلمي 11', 'realme realme 11 ريلمي 11 ريلمي'),
('Realme', 'Realme 12', 'ريلمي 12', 'realme realme 12 ريلمي 12 ريلمي');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Realme', 'Narzo 30', 'ريلمي نارزو 30', 'realme narzo 30 ريلمي نارزو 30 ريلمي'),
('Realme', 'Narzo 50', 'ريلمي نارزو 50', 'realme narzo 50 ريلمي نارزو 50 ريلمي'),
('Realme', 'Narzo 60', 'ريلمي نارزو 60', 'realme narzo 60 ريلمي نارزو 60 ريلمي'),
('Realme', 'Narzo 70', 'ريلمي نارزو 70', 'realme narzo 70 ريلمي نارزو 70 ريلمي'),
('Realme', 'Realme GT 2', 'ريلمي GT 2', 'realme realme gt 2 ريلمي gt 2 ريلمي'),
('Realme', 'Realme GT 6', 'ريلمي GT 6', 'realme realme gt 6 ريلمي gt 6 ريلمي'),
('Infinix', 'Hot 9', 'انفينكس هوت 9', 'infinix hot 9 انفينكس هوت 9 انفينكس'),
('Infinix', 'Hot 10', 'انفينكس هوت 10', 'infinix hot 10 انفينكس هوت 10 انفينكس'),
('Infinix', 'Hot 11', 'انفينكس هوت 11', 'infinix hot 11 انفينكس هوت 11 انفينكس'),
('Infinix', 'Hot 12', 'انفينكس هوت 12', 'infinix hot 12 انفينكس هوت 12 انفينكس'),
('Infinix', 'Hot 20', 'انفينكس هوت 20', 'infinix hot 20 انفينكس هوت 20 انفينكس'),
('Infinix', 'Hot 30', 'انفينكس هوت 30', 'infinix hot 30 انفينكس هوت 30 انفينكس'),
('Infinix', 'Hot 40', 'انفينكس هوت 40', 'infinix hot 40 انفينكس هوت 40 انفينكس'),
('Infinix', 'Hot 50', 'انفينكس هوت 50', 'infinix hot 50 انفينكس هوت 50 انفينكس'),
('Infinix', 'Hot 60', 'انفينكس هوت 60', 'infinix hot 60 انفينكس هوت 60 انفينكس'),
('Infinix', 'Note 7', 'انفينكس نوت 7', 'infinix note 7 انفينكس نوت 7 انفينكس'),
('Infinix', 'Note 8', 'انفينكس نوت 8', 'infinix note 8 انفينكس نوت 8 انفينكس'),
('Infinix', 'Note 10', 'انفينكس نوت 10', 'infinix note 10 انفينكس نوت 10 انفينكس'),
('Infinix', 'Note 11', 'انفينكس نوت 11', 'infinix note 11 انفينكس نوت 11 انفينكس'),
('Infinix', 'Note 12', 'انفينكس نوت 12', 'infinix note 12 انفينكس نوت 12 انفينكس'),
('Infinix', 'Note 30', 'انفينكس نوت 30', 'infinix note 30 انفينكس نوت 30 انفينكس'),
('Infinix', 'Note 40', 'انفينكس نوت 40', 'infinix note 40 انفينكس نوت 40 انفينكس'),
('Infinix', 'Note 50', 'انفينكس نوت 50', 'infinix note 50 انفينكس نوت 50 انفينكس'),
('Infinix', 'Smart 4', 'انفينكس سمارت 4', 'infinix smart 4 انفينكس سمارت 4 انفينكس'),
('Infinix', 'Smart 5', 'انفينكس سمارت 5', 'infinix smart 5 انفينكس سمارت 5 انفينكس'),
('Infinix', 'Smart 6', 'انفينكس سمارت 6', 'infinix smart 6 انفينكس سمارت 6 انفينكس'),
('Infinix', 'Smart 7', 'انفينكس سمارت 7', 'infinix smart 7 انفينكس سمارت 7 انفينكس'),
('Infinix', 'Smart 8', 'انفينكس سمارت 8', 'infinix smart 8 انفينكس سمارت 8 انفينكس'),
('Infinix', 'Smart 9', 'انفينكس سمارت 9', 'infinix smart 9 انفينكس سمارت 9 انفينكس'),
('Infinix', 'Smart 10', 'انفينكس سمارت 10', 'infinix smart 10 انفينكس سمارت 10 انفينكس'),
('Infinix', 'Smart HD', 'انفينكس سمارت HD', 'infinix smart hd انفينكس سمارت hd انفينكس'),
('Infinix', 'Zero 30', 'انفينكس زيرو 30', 'infinix zero 30 انفينكس زيرو 30 انفينكس'),
('Infinix', 'Zero 40', 'انفينكس زيرو 40', 'infinix zero 40 انفينكس زيرو 40 انفينكس'),
('Tecno', 'Spark 5', 'تكنو سبارك 5', 'tecno spark 5 تكنو سبارك 5 تكنو'),
('Tecno', 'Spark 6', 'تكنو سبارك 6', 'tecno spark 6 تكنو سبارك 6 تكنو'),
('Tecno', 'Spark 7', 'تكنو سبارك 7', 'tecno spark 7 تكنو سبارك 7 تكنو'),
('Tecno', 'Spark 8', 'تكنو سبارك 8', 'tecno spark 8 تكنو سبارك 8 تكنو'),
('Tecno', 'Spark 9', 'تكنو سبارك 9', 'tecno spark 9 تكنو سبارك 9 تكنو'),
('Tecno', 'Spark 10', 'تكنو سبارك 10', 'tecno spark 10 تكنو سبارك 10 تكنو'),
('Tecno', 'Spark 20', 'تكنو سبارك 20', 'tecno spark 20 تكنو سبارك 20 تكنو'),
('Tecno', 'Spark 30', 'تكنو سبارك 30', 'tecno spark 30 تكنو سبارك 30 تكنو'),
('Tecno', 'Spark Go 2023', 'تكنو سبارك جو 2023', 'tecno spark go 2023 تكنو سبارك جو 2023 تكنو'),
('Tecno', 'Spark Go 2024', 'تكنو سبارك جو 2024', 'tecno spark go 2024 تكنو سبارك جو 2024 تكنو'),
('Tecno', 'Spark Go 2025', 'تكنو سبارك جو 2025', 'tecno spark go 2025 تكنو سبارك جو 2025 تكنو'),
('Tecno', 'Camon 15', 'تكنو كامون 15', 'tecno camon 15 تكنو كامون 15 تكنو'),
('Tecno', 'Camon 16', 'تكنو كامون 16', 'tecno camon 16 تكنو كامون 16 تكنو'),
('Tecno', 'Camon 17', 'تكنو كامون 17', 'tecno camon 17 تكنو كامون 17 تكنو'),
('Tecno', 'Camon 18', 'تكنو كامون 18', 'tecno camon 18 تكنو كامون 18 تكنو'),
('Tecno', 'Camon 19', 'تكنو كامون 19', 'tecno camon 19 تكنو كامون 19 تكنو'),
('Tecno', 'Camon 20', 'تكنو كامون 20', 'tecno camon 20 تكنو كامون 20 تكنو'),
('Tecno', 'Camon 30', 'تكنو كامون 30', 'tecno camon 30 تكنو كامون 30 تكنو'),
('Tecno', 'Pova 2', 'تكنو بوفا 2', 'tecno pova 2 تكنو بوفا 2 تكنو'),
('Tecno', 'Pova 3', 'تكنو بوفا 3', 'tecno pova 3 تكنو بوفا 3 تكنو'),
('Tecno', 'Pova 4', 'تكنو بوفا 4', 'tecno pova 4 تكنو بوفا 4 تكنو'),
('Tecno', 'Pova 5', 'تكنو بوفا 5', 'tecno pova 5 تكنو بوفا 5 تكنو'),
('Tecno', 'Pova 6', 'تكنو بوفا 6', 'tecno pova 6 تكنو بوفا 6 تكنو'),
('Tecno', 'Pop 5', 'تكنو بوب 5', 'tecno pop 5 تكنو بوب 5 تكنو'),
('Tecno', 'Pop 6', 'تكنو بوب 6', 'tecno pop 6 تكنو بوب 6 تكنو'),
('Tecno', 'Pop 7', 'تكنو بوب 7', 'tecno pop 7 تكنو بوب 7 تكنو'),
('Tecno', 'Pop 8', 'تكنو بوب 8', 'tecno pop 8 تكنو بوب 8 تكنو');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Tecno', 'Pop 9', 'تكنو بوب 9', 'tecno pop 9 تكنو بوب 9 تكنو'),
('Oppo', 'A3s', 'اوبو A3s', 'oppo a3s اوبو a3s اوبو'),
('Oppo', 'A5s', 'اوبو A5s', 'oppo a5s اوبو a5s اوبو'),
('Oppo', 'A12', 'اوبو A12', 'oppo a12 اوبو a12 اوبو'),
('Oppo', 'A15', 'اوبو A15', 'oppo a15 اوبو a15 اوبو'),
('Oppo', 'A16', 'اوبو A16', 'oppo a16 اوبو a16 اوبو'),
('Oppo', 'A17', 'اوبو A17', 'oppo a17 اوبو a17 اوبو'),
('Oppo', 'A18', 'اوبو A18', 'oppo a18 اوبو a18 اوبو'),
('Oppo', 'A31', 'اوبو A31', 'oppo a31 اوبو a31 اوبو'),
('Oppo', 'A33', 'اوبو A33', 'oppo a33 اوبو a33 اوبو'),
('Oppo', 'A53', 'اوبو A53', 'oppo a53 اوبو a53 اوبو'),
('Oppo', 'A54', 'اوبو A54', 'oppo a54 اوبو a54 اوبو'),
('Oppo', 'A57', 'اوبو A57', 'oppo a57 اوبو a57 اوبو'),
('Oppo', 'A58', 'اوبو A58', 'oppo a58 اوبو a58 اوبو'),
('Oppo', 'A76', 'اوبو A76', 'oppo a76 اوبو a76 اوبو'),
('Oppo', 'A77', 'اوبو A77', 'oppo a77 اوبو a77 اوبو'),
('Oppo', 'A78', 'اوبو A78', 'oppo a78 اوبو a78 اوبو'),
('Oppo', 'A79', 'اوبو A79', 'oppo a79 اوبو a79 اوبو'),
('Oppo', 'A96', 'اوبو A96', 'oppo a96 اوبو a96 اوبو'),
('Oppo', 'A98', 'اوبو A98', 'oppo a98 اوبو a98 اوبو'),
('Oppo', 'Reno 2', 'اوبو رينو 2', 'oppo reno 2 اوبو رينو 2 اوبو'),
('Oppo', 'Reno 3', 'اوبو رينو 3', 'oppo reno 3 اوبو رينو 3 اوبو'),
('Oppo', 'Reno 4', 'اوبو رينو 4', 'oppo reno 4 اوبو رينو 4 اوبو'),
('Oppo', 'Reno 5', 'اوبو رينو 5', 'oppo reno 5 اوبو رينو 5 اوبو'),
('Oppo', 'Reno 6', 'اوبو رينو 6', 'oppo reno 6 اوبو رينو 6 اوبو'),
('Oppo', 'Reno 7', 'اوبو رينو 7', 'oppo reno 7 اوبو رينو 7 اوبو'),
('Oppo', 'Reno 8', 'اوبو رينو 8', 'oppo reno 8 اوبو رينو 8 اوبو'),
('Oppo', 'Reno 8T', 'اوبو رينو 8T', 'oppo reno 8t اوبو رينو 8t اوبو'),
('Oppo', 'Reno 10', 'اوبو رينو 10', 'oppo reno 10 اوبو رينو 10 اوبو'),
('Oppo', 'Reno 11', 'اوبو رينو 11', 'oppo reno 11 اوبو رينو 11 اوبو'),
('Oppo', 'Reno 12', 'اوبو رينو 12', 'oppo reno 12 اوبو رينو 12 اوبو'),
('Oppo', 'Reno 13', 'اوبو رينو 13', 'oppo reno 13 اوبو رينو 13 اوبو'),
('Oppo', 'F9', 'اوبو F9', 'oppo f9 اوبو f9 اوبو'),
('Oppo', 'F11', 'اوبو F11', 'oppo f11 اوبو f11 اوبو'),
('Oppo', 'F19', 'اوبو F19', 'oppo f19 اوبو f19 اوبو'),
('Oppo', 'F21', 'اوبو F21', 'oppo f21 اوبو f21 اوبو'),
('Vivo', 'Y11', 'فيفو Y11', 'vivo y11 فيفو y11 فيفو'),
('Vivo', 'Y12', 'فيفو Y12', 'vivo y12 فيفو y12 فيفو'),
('Vivo', 'Y15', 'فيفو Y15', 'vivo y15 فيفو y15 فيفو'),
('Vivo', 'Y16', 'فيفو Y16', 'vivo y16 فيفو y16 فيفو'),
('Vivo', 'Y17', 'فيفو Y17', 'vivo y17 فيفو y17 فيفو'),
('Vivo', 'Y19', 'فيفو Y19', 'vivo y19 فيفو y19 فيفو'),
('Vivo', 'Y20', 'فيفو Y20', 'vivo y20 فيفو y20 فيفو'),
('Vivo', 'Y21', 'فيفو Y21', 'vivo y21 فيفو y21 فيفو'),
('Vivo', 'Y22', 'فيفو Y22', 'vivo y22 فيفو y22 فيفو'),
('Vivo', 'Y27', 'فيفو Y27', 'vivo y27 فيفو y27 فيفو'),
('Vivo', 'Y28', 'فيفو Y28', 'vivo y28 فيفو y28 فيفو'),
('Vivo', 'Y30', 'فيفو Y30', 'vivo y30 فيفو y30 فيفو'),
('Vivo', 'Y33', 'فيفو Y33', 'vivo y33 فيفو y33 فيفو'),
('Vivo', 'Y36', 'فيفو Y36', 'vivo y36 فيفو y36 فيفو'),
('Vivo', 'Y50', 'فيفو Y50', 'vivo y50 فيفو y50 فيفو'),
('Vivo', 'Y51', 'فيفو Y51', 'vivo y51 فيفو y51 فيفو'),
('Vivo', 'Y53', 'فيفو Y53', 'vivo y53 فيفو y53 فيفو'),
('Vivo', 'Y55', 'فيفو Y55', 'vivo y55 فيفو y55 فيفو'),
('Vivo', 'Y56', 'فيفو Y56', 'vivo y56 فيفو y56 فيفو'),
('Vivo', 'V20', 'فيفو V20', 'vivo v20 فيفو v20 فيفو'),
('Vivo', 'V21', 'فيفو V21', 'vivo v21 فيفو v21 فيفو'),
('Vivo', 'V23', 'فيفو V23', 'vivo v23 فيفو v23 فيفو'),
('Vivo', 'V25', 'فيفو V25', 'vivo v25 فيفو v25 فيفو'),
('Vivo', 'V27', 'فيفو V27', 'vivo v27 فيفو v27 فيفو');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Vivo', 'V29', 'فيفو V29', 'vivo v29 فيفو v29 فيفو'),
('Vivo', 'V30', 'فيفو V30', 'vivo v30 فيفو v30 فيفو'),
('Vivo', 'X60', 'فيفو X60', 'vivo x60 فيفو x60 فيفو'),
('Vivo', 'X70', 'فيفو X70', 'vivo x70 فيفو x70 فيفو'),
('Vivo', 'X80', 'فيفو X80', 'vivo x80 فيفو x80 فيفو'),
('Vivo', 'X90', 'فيفو X90', 'vivo x90 فيفو x90 فيفو'),
('Huawei', 'Y5p', 'هواوي Y5p', 'huawei y5p هواوي y5p هواوي'),
('Huawei', 'Y6p', 'هواوي Y6p', 'huawei y6p هواوي y6p هواوي'),
('Huawei', 'Y7a', 'هواوي Y7a', 'huawei y7a هواوي y7a هواوي'),
('Huawei', 'Y7p', 'هواوي Y7p', 'huawei y7p هواوي y7p هواوي'),
('Huawei', 'Y9 Prime', 'هواوي Y9 برايم', 'huawei y9 prime هواوي y9 برايم هواوي'),
('Huawei', 'Y9s', 'هواوي Y9s', 'huawei y9s هواوي y9s هواوي'),
('Huawei', 'Nova 5T', 'هواوي نوفا 5T', 'huawei nova 5t هواوي نوفا 5t هواوي'),
('Huawei', 'Nova 7i', 'هواوي نوفا 7i', 'huawei nova 7i هواوي نوفا 7i هواوي'),
('Huawei', 'Nova 8i', 'هواوي نوفا 8i', 'huawei nova 8i هواوي نوفا 8i هواوي'),
('Huawei', 'Nova 9', 'هواوي نوفا 9', 'huawei nova 9 هواوي نوفا 9 هواوي'),
('Huawei', 'Nova 10', 'هواوي نوفا 10', 'huawei nova 10 هواوي نوفا 10 هواوي'),
('Huawei', 'P30', 'هواوي P30', 'huawei p30 هواوي p30 هواوي'),
('Huawei', 'P30 Lite', 'هواوي P30 لايت', 'huawei p30 lite هواوي p30 لايت هواوي'),
('Huawei', 'P40', 'هواوي P40', 'huawei p40 هواوي p40 هواوي'),
('Huawei', 'P40 Lite', 'هواوي P40 لايت', 'huawei p40 lite هواوي p40 لايت هواوي'),
('Huawei', 'P50', 'هواوي P50', 'huawei p50 هواوي p50 هواوي'),
('Huawei', 'P60', 'هواوي P60', 'huawei p60 هواوي p60 هواوي'),
('Huawei', 'Mate 30', 'هواوي ميت 30', 'huawei mate 30 هواوي ميت 30 هواوي'),
('Huawei', 'Mate 40', 'هواوي ميت 40', 'huawei mate 40 هواوي ميت 40 هواوي'),
('Huawei', 'Mate 50', 'هواوي ميت 50', 'huawei mate 50 هواوي ميت 50 هواوي'),
('Honor', '8A', 'هونر 8A', 'honor 8a هونر 8a هونر'),
('Honor', '8X', 'هونر 8X', 'honor 8x هونر 8x هونر'),
('Honor', '9X', 'هونر 9X', 'honor 9x هونر 9x هونر'),
('Honor', '10', 'هونر 10', 'honor 10 هونر 10 هونر'),
('Honor', '20', 'هونر 20', 'honor 20 هونر 20 هونر'),
('Honor', 'X6', 'هونر X6', 'honor x6 هونر x6 هونر'),
('Honor', 'X7', 'هونر X7', 'honor x7 هونر x7 هونر'),
('Honor', 'X8', 'هونر X8', 'honor x8 هونر x8 هونر'),
('Honor', 'X9', 'هونر X9', 'honor x9 هونر x9 هونر'),
('Honor', '50', 'هونر 50', 'honor 50 هونر 50 هونر'),
('Honor', '70', 'هونر 70', 'honor 70 هونر 70 هونر'),
('Honor', '90', 'هونر 90', 'honor 90 هونر 90 هونر'),
('Honor', 'Magic 5', 'هونر ماجيك 5', 'honor magic 5 هونر ماجيك 5 هونر'),
('Honor', 'Magic 6', 'هونر ماجيك 6', 'honor magic 6 هونر ماجيك 6 هونر'),
('Nokia', 'G10', 'نوكيا G10', 'nokia g10 نوكيا g10 نوكيا'),
('Nokia', 'G20', 'نوكيا G20', 'nokia g20 نوكيا g20 نوكيا'),
('Nokia', 'G21', 'نوكيا G21', 'nokia g21 نوكيا g21 نوكيا'),
('Nokia', 'C21', 'نوكيا C21', 'nokia c21 نوكيا c21 نوكيا'),
('Nokia', 'C31', 'نوكيا C31', 'nokia c31 نوكيا c31 نوكيا'),
('Nokia', 'G42', 'نوكيا G42', 'nokia g42 نوكيا g42 نوكيا'),
('Nokia', 'X20', 'نوكيا X20', 'nokia x20 نوكيا x20 نوكيا'),
('Nokia', 'X30', 'نوكيا X30', 'nokia x30 نوكيا x30 نوكيا'),
('Nokia', '8.3', 'نوكيا 8.3', 'nokia 8.3 نوكيا 8.3 نوكيا'),
('Motorola', 'Moto G10', 'موتورولا G10', 'motorola moto g10 موتورولا g10 موتورولا'),
('Motorola', 'Moto G20', 'موتورولا G20', 'motorola moto g20 موتورولا g20 موتورولا'),
('Motorola', 'Moto G22', 'موتورولا G22', 'motorola moto g22 موتورولا g22 موتورولا'),
('Motorola', 'Moto G30', 'موتورولا G30', 'motorola moto g30 موتورولا g30 موتورولا'),
('Motorola', 'Moto G32', 'موتورولا G32', 'motorola moto g32 موتورولا g32 موتورولا'),
('Motorola', 'Moto G52', 'موتورولا G52', 'motorola moto g52 موتورولا g52 موتورولا'),
('Motorola', 'Moto G54', 'موتورولا G54', 'motorola moto g54 موتورولا g54 موتورولا'),
('Motorola', 'Moto G62', 'موتورولا G62', 'motorola moto g62 موتورولا g62 موتورولا'),
('Motorola', 'Moto G72', 'موتورولا G72', 'motorola moto g72 موتورولا g72 موتورولا'),
('Motorola', 'Moto G82', 'موتورولا G82', 'motorola moto g82 موتورولا g82 موتورولا'),
('Motorola', 'Moto G84', 'موتورولا G84', 'motorola moto g84 موتورولا g84 موتورولا');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Motorola', 'Moto E7', 'موتورولا E7', 'motorola moto e7 موتورولا e7 موتورولا'),
('Motorola', 'Moto E13', 'موتورولا E13', 'motorola moto e13 موتورولا e13 موتورولا'),
('Motorola', 'Moto E22', 'موتورولا E22', 'motorola moto e22 موتورولا e22 موتورولا'),
('OnePlus', 'Nord 2', 'ون بلس نورد 2', 'oneplus nord 2 ون بلس نورد 2 ون بلس'),
('OnePlus', 'Nord 3', 'ون بلس نورد 3', 'oneplus nord 3 ون بلس نورد 3 ون بلس'),
('OnePlus', 'Nord CE 2', 'ون بلس نورد CE2', 'oneplus nord ce 2 ون بلس نورد ce2 ون بلس'),
('OnePlus', 'Nord CE 3', 'ون بلس نورد CE3', 'oneplus nord ce 3 ون بلس نورد ce3 ون بلس'),
('OnePlus', '9', 'ون بلس 9', 'oneplus 9 ون بلس 9 ون بلس'),
('OnePlus', '9R', 'ون بلس 9R', 'oneplus 9r ون بلس 9r ون بلس'),
('OnePlus', '10 Pro', 'ون بلس 10 برو', 'oneplus 10 pro ون بلس 10 برو ون بلس'),
('OnePlus', '10T', 'ون بلس 10T', 'oneplus 10t ون بلس 10t ون بلس'),
('OnePlus', '11', 'ون بلس 11', 'oneplus 11 ون بلس 11 ون بلس'),
('OnePlus', '12', 'ون بلس 12', 'oneplus 12 ون بلس 12 ون بلس'),
('OnePlus', '12R', 'ون بلس 12R', 'oneplus 12r ون بلس 12r ون بلس'),
('OnePlus', '13', 'ون بلس 13', 'oneplus 13 ون بلس 13 ون بلس'),
('Google', 'Pixel 6', 'بيكسل 6', 'google pixel 6 بيكسل 6 جوجل بيكسل'),
('Google', 'Pixel 6a', 'بيكسل 6a', 'google pixel 6a بيكسل 6a جوجل بيكسل'),
('Google', 'Pixel 7', 'بيكسل 7', 'google pixel 7 بيكسل 7 جوجل بيكسل'),
('Google', 'Pixel 7a', 'بيكسل 7a', 'google pixel 7a بيكسل 7a جوجل بيكسل'),
('Google', 'Pixel 8', 'بيكسل 8', 'google pixel 8 بيكسل 8 جوجل بيكسل'),
('Google', 'Pixel 8a', 'بيكسل 8a', 'google pixel 8a بيكسل 8a جوجل بيكسل'),
('Google', 'Pixel 9', 'بيكسل 9', 'google pixel 9 بيكسل 9 جوجل بيكسل'),
('Google', 'Pixel 9 Pro', 'بيكسل 9 برو', 'google pixel 9 pro بيكسل 9 برو جوجل بيكسل'),
('Nothing', 'Phone (1)', 'نوثينج فون 1', 'nothing phone (1) نوثينج فون 1 نوثينج'),
('Nothing', 'Phone (2)', 'نوثينج فون 2', 'nothing phone (2) نوثينج فون 2 نوثينج'),
('Nothing', 'Phone (2a)', 'نوثينج فون 2a', 'nothing phone (2a) نوثينج فون 2a نوثينج'),
('Itel', 'A23', 'ايتل A23', 'itel a23 ايتل a23 ايتل'),
('Itel', 'A48', 'ايتل A48', 'itel a48 ايتل a48 ايتل'),
('Itel', 'A56', 'ايتل A56', 'itel a56 ايتل a56 ايتل'),
('Itel', 'P38', 'ايتل P38', 'itel p38 ايتل p38 ايتل'),
('Itel', 'S23', 'ايتل S23', 'itel s23 ايتل s23 ايتل'),
('Itel', 'Vision 1', 'ايتل فيجن 1', 'itel vision 1 ايتل فيجن 1 ايتل'),
('Itel', 'Vision 2', 'ايتل فيجن 2', 'itel vision 2 ايتل فيجن 2 ايتل'),
('Itel', 'A60', 'ايتل A60', 'itel a60 ايتل a60 ايتل'),
('Itel', 'A70', 'ايتل A70', 'itel a70 ايتل a70 ايتل'),
('Lenovo', 'K13', 'لينوفو K13', 'lenovo k13 لينوفو k13 لينوفو'),
('Lenovo', 'K14 Plus', 'لينوفو K14 بلس', 'lenovo k14 plus لينوفو k14 بلس لينوفو'),
('TCL', 'TCL 20 SE', 'تي سي ال 20 SE', 'tcl tcl 20 se تي سي ال 20 se تي سي ال'),
('TCL', 'TCL 30 SE', 'تي سي ال 30 SE', 'tcl tcl 30 se تي سي ال 30 se تي سي ال'),
('TCL', 'TCL 40 SE', 'تي سي ال 40 SE', 'tcl tcl 40 se تي سي ال 40 se تي سي ال'),
('Lava', 'Blaze 2', 'لافا بليز 2', 'lava blaze 2 لافا بليز 2 لافا'),
('Lava', 'Agni 2', 'لافا اجني 2', 'lava agni 2 لافا اجني 2 لافا');

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3001', 'qr', 'ايفون 6 / ايفون 6s / ايفون 7 / ايفون 8 / ايفون SE 2020 / ايفون SE 2022', 'iPhone 6 / iPhone 6s / iPhone 7 / iPhone 8 / iPhone SE 2020 / iPhone SE 2022', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3002', 'qr', 'ايفون 6 بلس / ايفون 6s بلس / ايفون 7 بلس / ايفون 8 بلس', 'iPhone 6 Plus / iPhone 6s Plus / iPhone 7 Plus / iPhone 8 Plus', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3003', 'qr', 'ايفون X / ايفون XS / ايفون 11 برو', 'iPhone X / iPhone XS / iPhone 11 Pro', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3004', 'qr', 'ايفون XR / ايفون 11', 'iPhone XR / iPhone 11', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3005', 'qr', 'ايفون XS ماكس / ايفون 11 برو ماكس', 'iPhone XS Max / iPhone 11 Pro Max', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3006', 'qr', 'ايفون 12 ميني / ايفون 13 ميني', 'iPhone 12 mini / iPhone 13 mini', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3007', 'qr', 'ايفون 12 / ايفون 12 برو', 'iPhone 12 / iPhone 12 Pro', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3008', 'qr', 'ايفون 12 برو ماكس', 'iPhone 12 Pro Max', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3009', 'qr', 'ايفون 13 / ايفون 13 برو / ايفون 14 / ايفون 16e', 'iPhone 13 / iPhone 13 Pro / iPhone 14 / iPhone 16e', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3010', 'qr', 'ايفون 13 برو ماكس / ايفون 14 بلس', 'iPhone 13 Pro Max / iPhone 14 Plus', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3011', 'qr', 'ايفون 14 برو / ايفون 15', 'iPhone 14 Pro / iPhone 15', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3012', 'qr', 'ايفون 14 برو ماكس / ايفون 15 بلس', 'iPhone 14 Pro Max / iPhone 15 Plus', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3013', 'qr', 'ايفون 15 برو / ايفون 16', 'iPhone 15 Pro / iPhone 16', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3014', 'qr', 'ايفون 15 برو ماكس / ايفون 16 بلس', 'iPhone 15 Pro Max / iPhone 16 Plus', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3015', 'qr', 'ايفون 16 برو / ايفون 17 / ايفون 17 برو', 'iPhone 16 Pro / iPhone 17 / iPhone 17 Pro', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3016', 'qr', 'ايفون 16 برو ماكس / ايفون 17 برو ماكس', 'iPhone 16 Pro Max / iPhone 17 Pro Max', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3017', 'qr', 'ايفون 17 اير', 'iPhone 17 Air', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3018', 'qr', 'سامسونج A01 / سامسونج A02 / سامسونج A02s / سامسونج A03 / سامسونج A03s / سامسونج A04 / سامسونج A04s / سامسونج A04e / سامسونج A05 / سامسونج A05s / سامسونج A06', 'Galaxy A01 / Galaxy A02 / Galaxy A02s / Galaxy A03 / Galaxy A03s / Galaxy A04 / Galaxy A04s / Galaxy A04e / Galaxy A05 / Galaxy A05s / Galaxy A06', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3019', 'qr', 'سامسونج A10 / سامسونج A10s / سامسونج A11 / سامسونج A12 / سامسونج A13 / سامسونج A14 / سامسونج A15 / سامسونج A16 / سامسونج A17', 'Galaxy A10 / Galaxy A10s / Galaxy A11 / Galaxy A12 / Galaxy A13 / Galaxy A14 / Galaxy A15 / Galaxy A16 / Galaxy A17', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3020', 'qr', 'سامسونج A20 / سامسونج A20s / سامسونج A21s / سامسونج A22 / سامسونج A23 / سامسونج A24 / سامسونج A25 / سامسونج A26', 'Galaxy A20 / Galaxy A20s / Galaxy A21s / Galaxy A22 / Galaxy A23 / Galaxy A24 / Galaxy A25 / Galaxy A26', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3021', 'qr', 'سامسونج A30 / سامسونج A30s / سامسونج A31 / سامسونج A32 / سامسونج A33 / سامسونج A34 / سامسونج A35 / سامسونج A36', 'Galaxy A30 / Galaxy A30s / Galaxy A31 / Galaxy A32 / Galaxy A33 / Galaxy A34 / Galaxy A35 / Galaxy A36', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3022', 'qr', 'سامسونج A50 / سامسونج A50s / سامسونج A51 / سامسونج A52 / سامسونج A52s / سامسونج A53 / سامسونج A54 / سامسونج A55 / سامسونج A56', 'Galaxy A50 / Galaxy A50s / Galaxy A51 / Galaxy A52 / Galaxy A52s / Galaxy A53 / Galaxy A54 / Galaxy A55 / Galaxy A56', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3023', 'qr', 'سامسونج A70 / سامسونج A71 / سامسونج A72 / سامسونج A73', 'Galaxy A70 / Galaxy A71 / Galaxy A72 / Galaxy A73', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3024', 'qr', 'سامسونج M10 / سامسونج M11 / سامسونج M12 / سامسونج M13 / سامسونج M14 / سامسونج M15', 'Galaxy M10 / Galaxy M11 / Galaxy M12 / Galaxy M13 / Galaxy M14 / Galaxy M15', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3025', 'qr', 'سامسونج M21 / سامسونج M22 / سامسونج M31 / سامسونج M32 / سامسونج M33 / سامسونج M34 / سامسونج M52 / سامسونج M53', 'Galaxy M21 / Galaxy M22 / Galaxy M31 / Galaxy M32 / Galaxy M33 / Galaxy M34 / Galaxy M52 / Galaxy M53', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3026', 'qr', 'سامسونج S8 / سامسونج S9', 'Galaxy S8 / Galaxy S9', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3027', 'qr', 'سامسونج S10 / سامسونج S10e', 'Galaxy S10 / Galaxy S10e', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3028', 'qr', 'سامسونج S10 بلس / سامسونج S10 لايت', 'Galaxy S10+ / Galaxy S10 Lite', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3029', 'qr', 'سامسونج S20 / سامسونج S20 5G / سامسونج S20 FE', 'Galaxy S20 / Galaxy S20 5G / Galaxy S20 FE', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3030', 'qr', 'سامسونج S20 بلس / سامسونج S20 الترا', 'Galaxy S20+ / Galaxy S20 Ultra', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3031', 'qr', 'سامسونج S21 / سامسونج S21 FE / سامسونج S22 / سامسونج S23 / سامسونج S24 / سامسونج S25', 'Galaxy S21 / Galaxy S21 FE / Galaxy S22 / Galaxy S23 / Galaxy S24 / Galaxy S25', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3032', 'qr', 'سامسونج S21 بلس / سامسونج S22 بلس / سامسونج S23 بلس / سامسونج S24 بلس / سامسونج S25 بلس', 'Galaxy S21+ / Galaxy S22+ / Galaxy S23+ / Galaxy S24+ / Galaxy S25+', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3033', 'qr', 'سامسونج S21 الترا / سامسونج S22 الترا / سامسونج S23 الترا / سامسونج S24 الترا', 'Galaxy S21 Ultra / Galaxy S22 Ultra / Galaxy S23 Ultra / Galaxy S24 Ultra', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3034', 'qr', 'سامسونج S25 الترا / سامسونج S25 ايدج / سامسونج S23 FE / سامسونج S24 FE', 'Galaxy S25 Ultra / Galaxy S25 Edge / Galaxy S23 FE / Galaxy S24 FE', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3035', 'qr', 'سامسونج نوت 8 / سامسونج نوت 9 / سامسونج نوت 10', 'Galaxy Note 8 / Galaxy Note 9 / Galaxy Note 10', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3036', 'qr', 'سامسونج نوت 10 بلس / سامسونج نوت 20 / سامسونج نوت 20 الترا', 'Galaxy Note 10+ / Galaxy Note 20 / Galaxy Note 20 Ultra', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3037', 'qr', 'سامسونج Z فليب 3 / سامسونج Z فليب 4 / سامسونج Z فليب 5 / سامسونج Z فليب 6', 'Galaxy Z Flip3 / Galaxy Z Flip4 / Galaxy Z Flip5 / Galaxy Z Flip6', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3038', 'qr', 'سامسونج Z فولد 3 / سامسونج Z فولد 4 / سامسونج Z فولد 5 / سامسونج Z فولد 6', 'Galaxy Z Fold3 / Galaxy Z Fold4 / Galaxy Z Fold5 / Galaxy Z Fold6', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3039', 'qr', 'ريدمي 8A / ريدمي 9A / ريدمي 10A / ريدمي A1 / ريدمي A2 / ريدمي A3 / ريدمي A5', 'Redmi 8A / Redmi 9A / Redmi 10A / Redmi A1 / Redmi A2 / Redmi A3 / Redmi A5', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3040', 'qr', 'ريدمي 9 / ريدمي 9C / ريدمي 10 / ريدمي 10C / ريدمي 12 / ريدمي 12C / ريدمي 13C / ريدمي 14C / ريدمي 15C', 'Redmi 9 / Redmi 9C / Redmi 10 / Redmi 10C / Redmi 12 / Redmi 12C / Redmi 13C / Redmi 14C / Redmi 15C', '9h', '45', '100', '85', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3041', 'qr', 'ريدمي نوت 7 / ريدمي نوت 8 / ريدمي نوت 8 برو', 'Redmi Note 7 / Redmi Note 8 / Redmi Note 8 Pro', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3042', 'qr', 'ريدمي نوت 9 / ريدمي نوت 9s / ريدمي نوت 10 / ريدمي نوت 10s', 'Redmi Note 9 / Redmi Note 9S / Redmi Note 10 / Redmi Note 10S', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3043', 'qr', 'ريدمي نوت 11 / ريدمي نوت 11s / ريدمي نوت 12 / ريدمي نوت 12s / ريدمي نوت 13', 'Redmi Note 11 / Redmi Note 11S / Redmi Note 12 / Redmi Note 12S / Redmi Note 13', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3044', 'qr', 'ريدمي نوت 14 / ريدمي نوت 14 برو / ريدمي نوت 15', 'Redmi Note 14 / Redmi Note 14 Pro / Redmi Note 15', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3045', 'qr', 'شاومي 11 / شاومي 11 لايت / شاومي 12 / شاومي 12 لايت / شاومي 12T', 'Xiaomi 11 / Xiaomi 11 Lite / Xiaomi 12 / Xiaomi 12 Lite / Xiaomi 12T', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3046', 'qr', 'شاومي 13 / شاومي 13 لايت / شاومي 13T / شاومي 14 / شاومي 14T / شاومي 15', 'Xiaomi 13 / Xiaomi 13 Lite / Xiaomi 13T / Xiaomi 14 / Xiaomi 14T / Xiaomi 15', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3047', 'qr', 'بوكو X3 / بوكو X4 / بوكو X5 / بوكو X6 / بوكو X7', 'Poco X3 / Poco X4 / Poco X5 / Poco X6 / Poco X7', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3048', 'qr', 'بوكو M3 / بوكو M4 / بوكو M5 / بوكو M6', 'Poco M3 / Poco M4 / Poco M5 / Poco M6', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3049', 'qr', 'بوكو F3 / بوكو F4 / بوكو F5 / بوكو F6 / بوكو F7', 'Poco F3 / Poco F4 / Poco F5 / Poco F6 / Poco F7', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3050', 'qr', 'بوكو C40 / بوكو C55 / بوكو C65 / بوكو C75', 'Poco C40 / Poco C55 / Poco C65 / Poco C75', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3051', 'qr', 'ريلمي C11 / ريلمي C12 / ريلمي C15 / ريلمي C17 / ريلمي C18', 'C11 / C12 / C15 / C17 / C18', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3052', 'qr', 'ريلمي C20 / ريلمي C21 / ريلمي C21Y / ريلمي C22 / ريلمي C23 / ريلمي C24 / ريلمي C25', 'C20 / C21 / C21Y / C22 / C23 / C24 / C25', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3053', 'qr', 'ريلمي C25Y / ريلمي C30 / ريلمي C31 / ريلمي C33 / ريلمي C35', 'C25Y / C30 / C31 / C33 / C35', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3054', 'qr', 'ريلمي C51 / ريلمي C53 / ريلمي C55 / ريلمي C61', 'C51 / C53 / C55 / C61', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3055', 'qr', 'ريلمي C63 / ريلمي C65 / ريلمي C67 / ريلمي C75', 'C63 / C65 / C67 / C75', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3056', 'qr', 'ريلمي 7 / ريلمي 8 / ريلمي 9 / ريلمي 10 / ريلمي 11 / ريلمي 12', 'Realme 7 / Realme 8 / Realme 9 / Realme 10 / Realme 11 / Realme 12', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3057', 'qr', 'ريلمي نارزو 30 / ريلمي نارزو 50 / ريلمي نارزو 60 / ريلمي نارزو 70', 'Narzo 30 / Narzo 50 / Narzo 60 / Narzo 70', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3058', 'qr', 'ريلمي GT 2 / ريلمي GT 6', 'Realme GT 2 / Realme GT 6', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3059', 'qr', 'انفينكس هوت 9 / انفينكس هوت 10 / انفينكس هوت 11 / انفينكس هوت 12', 'Hot 9 / Hot 10 / Hot 11 / Hot 12', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3060', 'qr', 'انفينكس هوت 20 / انفينكس هوت 30 / انفينكس هوت 40 / انفينكس هوت 50 / انفينكس هوت 60', 'Hot 20 / Hot 30 / Hot 40 / Hot 50 / Hot 60', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3061', 'qr', 'انفينكس نوت 7 / انفينكس نوت 8 / انفينكس نوت 10 / انفينكس نوت 11 / انفينكس نوت 12', 'Note 7 / Note 8 / Note 10 / Note 11 / Note 12', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3062', 'qr', 'انفينكس نوت 30 / انفينكس نوت 40 / انفينكس نوت 50', 'Note 30 / Note 40 / Note 50', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3063', 'qr', 'انفينكس سمارت 4 / انفينكس سمارت 5 / انفينكس سمارت 6 / انفينكس سمارت 7 / انفينكس سمارت 8 / انفينكس سمارت 9', 'Smart 4 / Smart 5 / Smart 6 / Smart 7 / Smart 8 / Smart 9', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3064', 'qr', 'انفينكس سمارت 10 / انفينكس سمارت HD', 'Smart 10 / Smart HD', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3065', 'qr', 'انفينكس زيرو 30 / انفينكس زيرو 40', 'Zero 30 / Zero 40', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3066', 'qr', 'تكنو سبارك 5 / تكنو سبارك 6 / تكنو سبارك 7 / تكنو سبارك 8', 'Spark 5 / Spark 6 / Spark 7 / Spark 8', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3067', 'qr', 'تكنو سبارك 9 / تكنو سبارك 10 / تكنو سبارك 20 / تكنو سبارك 30', 'Spark 9 / Spark 10 / Spark 20 / Spark 30', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3068', 'qr', 'تكنو سبارك جو 2023 / تكنو سبارك جو 2024 / تكنو سبارك جو 2025', 'Spark Go 2023 / Spark Go 2024 / Spark Go 2025', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3069', 'qr', 'تكنو كامون 15 / تكنو كامون 16 / تكنو كامون 17 / تكنو كامون 18', 'Camon 15 / Camon 16 / Camon 17 / Camon 18', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3070', 'qr', 'تكنو كامون 19 / تكنو كامون 20 / تكنو كامون 30', 'Camon 19 / Camon 20 / Camon 30', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3071', 'qr', 'تكنو بوفا 2 / تكنو بوفا 3 / تكنو بوفا 4 / تكنو بوفا 5 / تكنو بوفا 6', 'Pova 2 / Pova 3 / Pova 4 / Pova 5 / Pova 6', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3072', 'qr', 'تكنو بوب 5 / تكنو بوب 6 / تكنو بوب 7 / تكنو بوب 8 / تكنو بوب 9', 'Pop 5 / Pop 6 / Pop 7 / Pop 8 / Pop 9', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3073', 'qr', 'اوبو A3s / اوبو A5s / اوبو A12 / اوبو A15 / اوبو A16 / اوبو A17 / اوبو A18', 'A3s / A5s / A12 / A15 / A16 / A17 / A18', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3074', 'qr', 'اوبو A31 / اوبو A33 / اوبو A53 / اوبو A54 / اوبو A57 / اوبو A58', 'A31 / A33 / A53 / A54 / A57 / A58', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3075', 'qr', 'اوبو A76 / اوبو A77 / اوبو A78 / اوبو A79 / اوبو A96 / اوبو A98', 'A76 / A77 / A78 / A79 / A96 / A98', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3076', 'qr', 'اوبو رينو 2 / اوبو رينو 3 / اوبو رينو 4 / اوبو رينو 5', 'Reno 2 / Reno 3 / Reno 4 / Reno 5', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3077', 'qr', 'اوبو رينو 6 / اوبو رينو 7 / اوبو رينو 8 / اوبو رينو 8T', 'Reno 6 / Reno 7 / Reno 8 / Reno 8T', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3078', 'qr', 'اوبو رينو 10 / اوبو رينو 11 / اوبو رينو 12 / اوبو رينو 13', 'Reno 10 / Reno 11 / Reno 12 / Reno 13', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3079', 'qr', 'اوبو F9 / اوبو F11 / اوبو F19 / اوبو F21', 'F9 / F11 / F19 / F21', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3080', 'qr', 'فيفو Y11 / فيفو Y12 / فيفو Y15 / فيفو Y16 / فيفو Y17', 'Y11 / Y12 / Y15 / Y16 / Y17', '9h', '45', '100', '85', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3081', 'qr', 'فيفو Y19 / فيفو Y20 / فيفو Y21 / فيفو Y22', 'Y19 / Y20 / Y21 / Y22', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3082', 'qr', 'فيفو Y27 / فيفو Y28 / فيفو Y30 / فيفو Y33 / فيفو Y36', 'Y27 / Y28 / Y30 / Y33 / Y36', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3083', 'qr', 'فيفو Y50 / فيفو Y51 / فيفو Y53 / فيفو Y55 / فيفو Y56', 'Y50 / Y51 / Y53 / Y55 / Y56', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3084', 'qr', 'فيفو V20 / فيفو V21 / فيفو V23 / فيفو V25 / فيفو V27', 'V20 / V21 / V23 / V25 / V27', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3085', 'qr', 'فيفو V29 / فيفو V30', 'V29 / V30', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3086', 'qr', 'فيفو X60 / فيفو X70 / فيفو X80 / فيفو X90', 'X60 / X70 / X80 / X90', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3087', 'qr', 'هواوي Y5p / هواوي Y6p / هواوي Y7a / هواوي Y7p / هواوي Y9 برايم / هواوي Y9s', 'Y5p / Y6p / Y7a / Y7p / Y9 Prime / Y9s', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3088', 'qr', 'هواوي نوفا 5T / هواوي نوفا 7i / هواوي نوفا 8i / هواوي نوفا 9 / هواوي نوفا 10', 'Nova 5T / Nova 7i / Nova 8i / Nova 9 / Nova 10', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3089', 'qr', 'هواوي P30 / هواوي P30 لايت / هواوي P40 / هواوي P40 لايت / هواوي P50 / هواوي P60', 'P30 / P30 Lite / P40 / P40 Lite / P50 / P60', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3090', 'qr', 'هواوي ميت 30 / هواوي ميت 40 / هواوي ميت 50', 'Mate 30 / Mate 40 / Mate 50', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3091', 'qr', 'هونر 8A / هونر 8X / هونر 9X / هونر 10 / هونر 20', '8A / 8X / 9X / 10 / 20', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3092', 'qr', 'هونر X6 / هونر X7 / هونر X8 / هونر X9', 'X6 / X7 / X8 / X9', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3093', 'qr', 'هونر 50 / هونر 70 / هونر 90', '50 / 70 / 90', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3094', 'qr', 'هونر ماجيك 5 / هونر ماجيك 6', 'Magic 5 / Magic 6', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3095', 'qr', 'نوكيا G10 / نوكيا G20 / نوكيا G21 / نوكيا C21 / نوكيا C31', 'G10 / G20 / G21 / C21 / C31', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3096', 'qr', 'نوكيا G42 / نوكيا X20 / نوكيا X30 / نوكيا 8.3', 'G42 / X20 / X30 / 8.3', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3097', 'qr', 'موتورولا G10 / موتورولا G20 / موتورولا G22 / موتورولا G30', 'Moto G10 / Moto G20 / Moto G22 / Moto G30', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3098', 'qr', 'موتورولا G32 / موتورولا G52 / موتورولا G54 / موتورولا G62', 'Moto G32 / Moto G52 / Moto G54 / Moto G62', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3099', 'qr', 'موتورولا G72 / موتورولا G82 / موتورولا G84', 'Moto G72 / Moto G82 / Moto G84', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3100', 'qr', 'موتورولا E7 / موتورولا E13 / موتورولا E22', 'Moto E7 / Moto E13 / Moto E22', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3101', 'qr', 'ون بلس نورد 2 / ون بلس نورد 3 / ون بلس نورد CE2 / ون بلس نورد CE3', 'Nord 2 / Nord 3 / Nord CE 2 / Nord CE 3', '9h', '60', '130', '110', 0, 3),
('ASLYSCR3102', 'qr', 'ون بلس 9 / ون بلس 9R / ون بلس 10 برو / ون بلس 10T', '9 / 9R / 10 Pro / 10T', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3103', 'qr', 'ون بلس 11 / ون بلس 12 / ون بلس 12R / ون بلس 13', '11 / 12 / 12R / 13', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3104', 'qr', 'بيكسل 6 / بيكسل 6a / بيكسل 7 / بيكسل 7a', 'Pixel 6 / Pixel 6a / Pixel 7 / Pixel 7a', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3105', 'qr', 'بيكسل 8 / بيكسل 8a / بيكسل 9 / بيكسل 9 برو', 'Pixel 8 / Pixel 8a / Pixel 9 / Pixel 9 Pro', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3106', 'qr', 'نوثينج فون 1 / نوثينج فون 2 / نوثينج فون 2a', 'Phone (1) / Phone (2) / Phone (2a)', '9h', '95', '180', '153', 0, 3),
('ASLYSCR3107', 'qr', 'ايتل A23 / ايتل A48 / ايتل A56 / ايتل P38 / ايتل S23', 'A23 / A48 / A56 / P38 / S23', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3108', 'qr', 'ايتل فيجن 1 / ايتل فيجن 2 / ايتل A60 / ايتل A70', 'Vision 1 / Vision 2 / A60 / A70', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3109', 'qr', 'لينوفو K13 / لينوفو K14 بلس', 'K13 / K14 Plus', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3110', 'qr', 'تي سي ال 20 SE / تي سي ال 30 SE / تي سي ال 40 SE', 'TCL 20 SE / TCL 30 SE / TCL 40 SE', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3111', 'qr', 'لافا بليز 2 / لافا اجني 2', 'Blaze 2 / Agni 2', '9h', '45', '100', '85', 0, 3);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3001', 'qr', 'ايفون 6 / ايفون 6s / ايفون 7 / ايفون 8 / ايفون SE 2020 / ايفون SE 2022', 'iPhone 6 / iPhone 6s / iPhone 7 / iPhone 8 / iPhone SE 2020 / iPhone SE 2022', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3002', 'qr', 'ايفون 6 بلس / ايفون 6s بلس / ايفون 7 بلس / ايفون 8 بلس', 'iPhone 6 Plus / iPhone 6s Plus / iPhone 7 Plus / iPhone 8 Plus', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3003', 'qr', 'ايفون X / ايفون XS / ايفون 11 برو', 'iPhone X / iPhone XS / iPhone 11 Pro', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3004', 'qr', 'ايفون XR / ايفون 11', 'iPhone XR / iPhone 11', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3005', 'qr', 'ايفون XS ماكس / ايفون 11 برو ماكس', 'iPhone XS Max / iPhone 11 Pro Max', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3006', 'qr', 'ايفون 12 ميني / ايفون 13 ميني', 'iPhone 12 mini / iPhone 13 mini', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3007', 'qr', 'ايفون 12 / ايفون 12 برو', 'iPhone 12 / iPhone 12 Pro', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3008', 'qr', 'ايفون 12 برو ماكس', 'iPhone 12 Pro Max', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3009', 'qr', 'ايفون 13 / ايفون 13 برو / ايفون 14 / ايفون 16e', 'iPhone 13 / iPhone 13 Pro / iPhone 14 / iPhone 16e', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3010', 'qr', 'ايفون 13 برو ماكس / ايفون 14 بلس', 'iPhone 13 Pro Max / iPhone 14 Plus', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3011', 'qr', 'ايفون 14 برو / ايفون 15', 'iPhone 14 Pro / iPhone 15', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3012', 'qr', 'ايفون 14 برو ماكس / ايفون 15 بلس', 'iPhone 14 Pro Max / iPhone 15 Plus', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3013', 'qr', 'ايفون 15 برو / ايفون 16', 'iPhone 15 Pro / iPhone 16', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3014', 'qr', 'ايفون 15 برو ماكس / ايفون 16 بلس', 'iPhone 15 Pro Max / iPhone 16 Plus', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3015', 'qr', 'ايفون 16 برو / ايفون 17 / ايفون 17 برو', 'iPhone 16 Pro / iPhone 17 / iPhone 17 Pro', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3016', 'qr', 'ايفون 16 برو ماكس / ايفون 17 برو ماكس', 'iPhone 16 Pro Max / iPhone 17 Pro Max', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3017', 'qr', 'ايفون 17 اير', 'iPhone 17 Air', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3018', 'qr', 'سامسونج A01 / سامسونج A02 / سامسونج A02s / سامسونج A03 / سامسونج A03s / سامسونج A04 / سامسونج A04s / سامسونج A04e / سامسونج A05 / سامسونج A05s / سامسونج A06', 'Galaxy A01 / Galaxy A02 / Galaxy A02s / Galaxy A03 / Galaxy A03s / Galaxy A04 / Galaxy A04s / Galaxy A04e / Galaxy A05 / Galaxy A05s / Galaxy A06', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3019', 'qr', 'سامسونج A10 / سامسونج A10s / سامسونج A11 / سامسونج A12 / سامسونج A13 / سامسونج A14 / سامسونج A15 / سامسونج A16 / سامسونج A17', 'Galaxy A10 / Galaxy A10s / Galaxy A11 / Galaxy A12 / Galaxy A13 / Galaxy A14 / Galaxy A15 / Galaxy A16 / Galaxy A17', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3020', 'qr', 'سامسونج A20 / سامسونج A20s / سامسونج A21s / سامسونج A22 / سامسونج A23 / سامسونج A24 / سامسونج A25 / سامسونج A26', 'Galaxy A20 / Galaxy A20s / Galaxy A21s / Galaxy A22 / Galaxy A23 / Galaxy A24 / Galaxy A25 / Galaxy A26', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3021', 'qr', 'سامسونج A30 / سامسونج A30s / سامسونج A31 / سامسونج A32 / سامسونج A33 / سامسونج A34 / سامسونج A35 / سامسونج A36', 'Galaxy A30 / Galaxy A30s / Galaxy A31 / Galaxy A32 / Galaxy A33 / Galaxy A34 / Galaxy A35 / Galaxy A36', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3022', 'qr', 'سامسونج A50 / سامسونج A50s / سامسونج A51 / سامسونج A52 / سامسونج A52s / سامسونج A53 / سامسونج A54 / سامسونج A55 / سامسونج A56', 'Galaxy A50 / Galaxy A50s / Galaxy A51 / Galaxy A52 / Galaxy A52s / Galaxy A53 / Galaxy A54 / Galaxy A55 / Galaxy A56', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3023', 'qr', 'سامسونج A70 / سامسونج A71 / سامسونج A72 / سامسونج A73', 'Galaxy A70 / Galaxy A71 / Galaxy A72 / Galaxy A73', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3024', 'qr', 'سامسونج M10 / سامسونج M11 / سامسونج M12 / سامسونج M13 / سامسونج M14 / سامسونج M15', 'Galaxy M10 / Galaxy M11 / Galaxy M12 / Galaxy M13 / Galaxy M14 / Galaxy M15', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3025', 'qr', 'سامسونج M21 / سامسونج M22 / سامسونج M31 / سامسونج M32 / سامسونج M33 / سامسونج M34 / سامسونج M52 / سامسونج M53', 'Galaxy M21 / Galaxy M22 / Galaxy M31 / Galaxy M32 / Galaxy M33 / Galaxy M34 / Galaxy M52 / Galaxy M53', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3026', 'qr', 'سامسونج S8 / سامسونج S9', 'Galaxy S8 / Galaxy S9', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3027', 'qr', 'سامسونج S10 / سامسونج S10e', 'Galaxy S10 / Galaxy S10e', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3028', 'qr', 'سامسونج S10 بلس / سامسونج S10 لايت', 'Galaxy S10+ / Galaxy S10 Lite', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3029', 'qr', 'سامسونج S20 / سامسونج S20 5G / سامسونج S20 FE', 'Galaxy S20 / Galaxy S20 5G / Galaxy S20 FE', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3030', 'qr', 'سامسونج S20 بلس / سامسونج S20 الترا', 'Galaxy S20+ / Galaxy S20 Ultra', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3031', 'qr', 'سامسونج S21 / سامسونج S21 FE / سامسونج S22 / سامسونج S23 / سامسونج S24 / سامسونج S25', 'Galaxy S21 / Galaxy S21 FE / Galaxy S22 / Galaxy S23 / Galaxy S24 / Galaxy S25', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3032', 'qr', 'سامسونج S21 بلس / سامسونج S22 بلس / سامسونج S23 بلس / سامسونج S24 بلس / سامسونج S25 بلس', 'Galaxy S21+ / Galaxy S22+ / Galaxy S23+ / Galaxy S24+ / Galaxy S25+', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3033', 'qr', 'سامسونج S21 الترا / سامسونج S22 الترا / سامسونج S23 الترا / سامسونج S24 الترا', 'Galaxy S21 Ultra / Galaxy S22 Ultra / Galaxy S23 Ultra / Galaxy S24 Ultra', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3034', 'qr', 'سامسونج S25 الترا / سامسونج S25 ايدج / سامسونج S23 FE / سامسونج S24 FE', 'Galaxy S25 Ultra / Galaxy S25 Edge / Galaxy S23 FE / Galaxy S24 FE', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3035', 'qr', 'سامسونج نوت 8 / سامسونج نوت 9 / سامسونج نوت 10', 'Galaxy Note 8 / Galaxy Note 9 / Galaxy Note 10', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3036', 'qr', 'سامسونج نوت 10 بلس / سامسونج نوت 20 / سامسونج نوت 20 الترا', 'Galaxy Note 10+ / Galaxy Note 20 / Galaxy Note 20 Ultra', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3037', 'qr', 'سامسونج Z فليب 3 / سامسونج Z فليب 4 / سامسونج Z فليب 5 / سامسونج Z فليب 6', 'Galaxy Z Flip3 / Galaxy Z Flip4 / Galaxy Z Flip5 / Galaxy Z Flip6', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3038', 'qr', 'سامسونج Z فولد 3 / سامسونج Z فولد 4 / سامسونج Z فولد 5 / سامسونج Z فولد 6', 'Galaxy Z Fold3 / Galaxy Z Fold4 / Galaxy Z Fold5 / Galaxy Z Fold6', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3039', 'qr', 'ريدمي 8A / ريدمي 9A / ريدمي 10A / ريدمي A1 / ريدمي A2 / ريدمي A3 / ريدمي A5', 'Redmi 8A / Redmi 9A / Redmi 10A / Redmi A1 / Redmi A2 / Redmi A3 / Redmi A5', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3040', 'qr', 'ريدمي 9 / ريدمي 9C / ريدمي 10 / ريدمي 10C / ريدمي 12 / ريدمي 12C / ريدمي 13C / ريدمي 14C / ريدمي 15C', 'Redmi 9 / Redmi 9C / Redmi 10 / Redmi 10C / Redmi 12 / Redmi 12C / Redmi 13C / Redmi 14C / Redmi 15C', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3041', 'qr', 'ريدمي نوت 7 / ريدمي نوت 8 / ريدمي نوت 8 برو', 'Redmi Note 7 / Redmi Note 8 / Redmi Note 8 Pro', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3042', 'qr', 'ريدمي نوت 9 / ريدمي نوت 9s / ريدمي نوت 10 / ريدمي نوت 10s', 'Redmi Note 9 / Redmi Note 9S / Redmi Note 10 / Redmi Note 10S', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3043', 'qr', 'ريدمي نوت 11 / ريدمي نوت 11s / ريدمي نوت 12 / ريدمي نوت 12s / ريدمي نوت 13', 'Redmi Note 11 / Redmi Note 11S / Redmi Note 12 / Redmi Note 12S / Redmi Note 13', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3044', 'qr', 'ريدمي نوت 14 / ريدمي نوت 14 برو / ريدمي نوت 15', 'Redmi Note 14 / Redmi Note 14 Pro / Redmi Note 15', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3045', 'qr', 'شاومي 11 / شاومي 11 لايت / شاومي 12 / شاومي 12 لايت / شاومي 12T', 'Xiaomi 11 / Xiaomi 11 Lite / Xiaomi 12 / Xiaomi 12 Lite / Xiaomi 12T', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3046', 'qr', 'شاومي 13 / شاومي 13 لايت / شاومي 13T / شاومي 14 / شاومي 14T / شاومي 15', 'Xiaomi 13 / Xiaomi 13 Lite / Xiaomi 13T / Xiaomi 14 / Xiaomi 14T / Xiaomi 15', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3047', 'qr', 'بوكو X3 / بوكو X4 / بوكو X5 / بوكو X6 / بوكو X7', 'Poco X3 / Poco X4 / Poco X5 / Poco X6 / Poco X7', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3048', 'qr', 'بوكو M3 / بوكو M4 / بوكو M5 / بوكو M6', 'Poco M3 / Poco M4 / Poco M5 / Poco M6', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3049', 'qr', 'بوكو F3 / بوكو F4 / بوكو F5 / بوكو F6 / بوكو F7', 'Poco F3 / Poco F4 / Poco F5 / Poco F6 / Poco F7', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3050', 'qr', 'بوكو C40 / بوكو C55 / بوكو C65 / بوكو C75', 'Poco C40 / Poco C55 / Poco C65 / Poco C75', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3051', 'qr', 'ريلمي C11 / ريلمي C12 / ريلمي C15 / ريلمي C17 / ريلمي C18', 'C11 / C12 / C15 / C17 / C18', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3052', 'qr', 'ريلمي C20 / ريلمي C21 / ريلمي C21Y / ريلمي C22 / ريلمي C23 / ريلمي C24 / ريلمي C25', 'C20 / C21 / C21Y / C22 / C23 / C24 / C25', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3053', 'qr', 'ريلمي C25Y / ريلمي C30 / ريلمي C31 / ريلمي C33 / ريلمي C35', 'C25Y / C30 / C31 / C33 / C35', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3054', 'qr', 'ريلمي C51 / ريلمي C53 / ريلمي C55 / ريلمي C61', 'C51 / C53 / C55 / C61', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3055', 'qr', 'ريلمي C63 / ريلمي C65 / ريلمي C67 / ريلمي C75', 'C63 / C65 / C67 / C75', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3056', 'qr', 'ريلمي 7 / ريلمي 8 / ريلمي 9 / ريلمي 10 / ريلمي 11 / ريلمي 12', 'Realme 7 / Realme 8 / Realme 9 / Realme 10 / Realme 11 / Realme 12', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3057', 'qr', 'ريلمي نارزو 30 / ريلمي نارزو 50 / ريلمي نارزو 60 / ريلمي نارزو 70', 'Narzo 30 / Narzo 50 / Narzo 60 / Narzo 70', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3058', 'qr', 'ريلمي GT 2 / ريلمي GT 6', 'Realme GT 2 / Realme GT 6', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3059', 'qr', 'انفينكس هوت 9 / انفينكس هوت 10 / انفينكس هوت 11 / انفينكس هوت 12', 'Hot 9 / Hot 10 / Hot 11 / Hot 12', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3060', 'qr', 'انفينكس هوت 20 / انفينكس هوت 30 / انفينكس هوت 40 / انفينكس هوت 50 / انفينكس هوت 60', 'Hot 20 / Hot 30 / Hot 40 / Hot 50 / Hot 60', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3061', 'qr', 'انفينكس نوت 7 / انفينكس نوت 8 / انفينكس نوت 10 / انفينكس نوت 11 / انفينكس نوت 12', 'Note 7 / Note 8 / Note 10 / Note 11 / Note 12', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3062', 'qr', 'انفينكس نوت 30 / انفينكس نوت 40 / انفينكس نوت 50', 'Note 30 / Note 40 / Note 50', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3063', 'qr', 'انفينكس سمارت 4 / انفينكس سمارت 5 / انفينكس سمارت 6 / انفينكس سمارت 7 / انفينكس سمارت 8 / انفينكس سمارت 9', 'Smart 4 / Smart 5 / Smart 6 / Smart 7 / Smart 8 / Smart 9', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3064', 'qr', 'انفينكس سمارت 10 / انفينكس سمارت HD', 'Smart 10 / Smart HD', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3065', 'qr', 'انفينكس زيرو 30 / انفينكس زيرو 40', 'Zero 30 / Zero 40', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3066', 'qr', 'تكنو سبارك 5 / تكنو سبارك 6 / تكنو سبارك 7 / تكنو سبارك 8', 'Spark 5 / Spark 6 / Spark 7 / Spark 8', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3067', 'qr', 'تكنو سبارك 9 / تكنو سبارك 10 / تكنو سبارك 20 / تكنو سبارك 30', 'Spark 9 / Spark 10 / Spark 20 / Spark 30', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3068', 'qr', 'تكنو سبارك جو 2023 / تكنو سبارك جو 2024 / تكنو سبارك جو 2025', 'Spark Go 2023 / Spark Go 2024 / Spark Go 2025', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3069', 'qr', 'تكنو كامون 15 / تكنو كامون 16 / تكنو كامون 17 / تكنو كامون 18', 'Camon 15 / Camon 16 / Camon 17 / Camon 18', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3070', 'qr', 'تكنو كامون 19 / تكنو كامون 20 / تكنو كامون 30', 'Camon 19 / Camon 20 / Camon 30', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3071', 'qr', 'تكنو بوفا 2 / تكنو بوفا 3 / تكنو بوفا 4 / تكنو بوفا 5 / تكنو بوفا 6', 'Pova 2 / Pova 3 / Pova 4 / Pova 5 / Pova 6', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3072', 'qr', 'تكنو بوب 5 / تكنو بوب 6 / تكنو بوب 7 / تكنو بوب 8 / تكنو بوب 9', 'Pop 5 / Pop 6 / Pop 7 / Pop 8 / Pop 9', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3073', 'qr', 'اوبو A3s / اوبو A5s / اوبو A12 / اوبو A15 / اوبو A16 / اوبو A17 / اوبو A18', 'A3s / A5s / A12 / A15 / A16 / A17 / A18', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3074', 'qr', 'اوبو A31 / اوبو A33 / اوبو A53 / اوبو A54 / اوبو A57 / اوبو A58', 'A31 / A33 / A53 / A54 / A57 / A58', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3075', 'qr', 'اوبو A76 / اوبو A77 / اوبو A78 / اوبو A79 / اوبو A96 / اوبو A98', 'A76 / A77 / A78 / A79 / A96 / A98', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3076', 'qr', 'اوبو رينو 2 / اوبو رينو 3 / اوبو رينو 4 / اوبو رينو 5', 'Reno 2 / Reno 3 / Reno 4 / Reno 5', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3077', 'qr', 'اوبو رينو 6 / اوبو رينو 7 / اوبو رينو 8 / اوبو رينو 8T', 'Reno 6 / Reno 7 / Reno 8 / Reno 8T', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3078', 'qr', 'اوبو رينو 10 / اوبو رينو 11 / اوبو رينو 12 / اوبو رينو 13', 'Reno 10 / Reno 11 / Reno 12 / Reno 13', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3079', 'qr', 'اوبو F9 / اوبو F11 / اوبو F19 / اوبو F21', 'F9 / F11 / F19 / F21', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3080', 'qr', 'فيفو Y11 / فيفو Y12 / فيفو Y15 / فيفو Y16 / فيفو Y17', 'Y11 / Y12 / Y15 / Y16 / Y17', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3081', 'qr', 'فيفو Y19 / فيفو Y20 / فيفو Y21 / فيفو Y22', 'Y19 / Y20 / Y21 / Y22', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3082', 'qr', 'فيفو Y27 / فيفو Y28 / فيفو Y30 / فيفو Y33 / فيفو Y36', 'Y27 / Y28 / Y30 / Y33 / Y36', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3083', 'qr', 'فيفو Y50 / فيفو Y51 / فيفو Y53 / فيفو Y55 / فيفو Y56', 'Y50 / Y51 / Y53 / Y55 / Y56', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3084', 'qr', 'فيفو V20 / فيفو V21 / فيفو V23 / فيفو V25 / فيفو V27', 'V20 / V21 / V23 / V25 / V27', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3085', 'qr', 'فيفو V29 / فيفو V30', 'V29 / V30', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3086', 'qr', 'فيفو X60 / فيفو X70 / فيفو X80 / فيفو X90', 'X60 / X70 / X80 / X90', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3087', 'qr', 'هواوي Y5p / هواوي Y6p / هواوي Y7a / هواوي Y7p / هواوي Y9 برايم / هواوي Y9s', 'Y5p / Y6p / Y7a / Y7p / Y9 Prime / Y9s', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3088', 'qr', 'هواوي نوفا 5T / هواوي نوفا 7i / هواوي نوفا 8i / هواوي نوفا 9 / هواوي نوفا 10', 'Nova 5T / Nova 7i / Nova 8i / Nova 9 / Nova 10', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3089', 'qr', 'هواوي P30 / هواوي P30 لايت / هواوي P40 / هواوي P40 لايت / هواوي P50 / هواوي P60', 'P30 / P30 Lite / P40 / P40 Lite / P50 / P60', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3090', 'qr', 'هواوي ميت 30 / هواوي ميت 40 / هواوي ميت 50', 'Mate 30 / Mate 40 / Mate 50', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3091', 'qr', 'هونر 8A / هونر 8X / هونر 9X / هونر 10 / هونر 20', '8A / 8X / 9X / 10 / 20', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3092', 'qr', 'هونر X6 / هونر X7 / هونر X8 / هونر X9', 'X6 / X7 / X8 / X9', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3093', 'qr', 'هونر 50 / هونر 70 / هونر 90', '50 / 70 / 90', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3094', 'qr', 'هونر ماجيك 5 / هونر ماجيك 6', 'Magic 5 / Magic 6', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3095', 'qr', 'نوكيا G10 / نوكيا G20 / نوكيا G21 / نوكيا C21 / نوكيا C31', 'G10 / G20 / G21 / C21 / C31', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3096', 'qr', 'نوكيا G42 / نوكيا X20 / نوكيا X30 / نوكيا 8.3', 'G42 / X20 / X30 / 8.3', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3097', 'qr', 'موتورولا G10 / موتورولا G20 / موتورولا G22 / موتورولا G30', 'Moto G10 / Moto G20 / Moto G22 / Moto G30', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3098', 'qr', 'موتورولا G32 / موتورولا G52 / موتورولا G54 / موتورولا G62', 'Moto G32 / Moto G52 / Moto G54 / Moto G62', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3099', 'qr', 'موتورولا G72 / موتورولا G82 / موتورولا G84', 'Moto G72 / Moto G82 / Moto G84', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3100', 'qr', 'موتورولا E7 / موتورولا E13 / موتورولا E22', 'Moto E7 / Moto E13 / Moto E22', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3101', 'qr', 'ون بلس نورد 2 / ون بلس نورد 3 / ون بلس نورد CE2 / ون بلس نورد CE3', 'Nord 2 / Nord 3 / Nord CE 2 / Nord CE 3', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3102', 'qr', 'ون بلس 9 / ون بلس 9R / ون بلس 10 برو / ون بلس 10T', '9 / 9R / 10 Pro / 10T', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3103', 'qr', 'ون بلس 11 / ون بلس 12 / ون بلس 12R / ون بلس 13', '11 / 12 / 12R / 13', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3104', 'qr', 'بيكسل 6 / بيكسل 6a / بيكسل 7 / بيكسل 7a', 'Pixel 6 / Pixel 6a / Pixel 7 / Pixel 7a', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3105', 'qr', 'بيكسل 8 / بيكسل 8a / بيكسل 9 / بيكسل 9 برو', 'Pixel 8 / Pixel 8a / Pixel 9 / Pixel 9 Pro', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3106', 'qr', 'نوثينج فون 1 / نوثينج فون 2 / نوثينج فون 2a', 'Phone (1) / Phone (2) / Phone (2a)', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3107', 'qr', 'ايتل A23 / ايتل A48 / ايتل A56 / ايتل P38 / ايتل S23', 'A23 / A48 / A56 / P38 / S23', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3108', 'qr', 'ايتل فيجن 1 / ايتل فيجن 2 / ايتل A60 / ايتل A70', 'Vision 1 / Vision 2 / A60 / A70', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3109', 'qr', 'لينوفو K13 / لينوفو K14 بلس', 'K13 / K14 Plus', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3110', 'qr', 'تي سي ال 20 SE / تي سي ال 30 SE / تي سي ال 40 SE', 'TCL 20 SE / TCL 30 SE / TCL 40 SE', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3111', 'qr', 'لافا بليز 2 / لافا اجني 2', 'Blaze 2 / Agni 2', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0);

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3001' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3002' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3003' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3004' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3005' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3006' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3007' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3008' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3009' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3010' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3011' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3012' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3013' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3014' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3015' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3016' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3017' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3018' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A01', 'Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3019' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A10', 'Galaxy A10s', 'Galaxy A11', 'Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16', 'Galaxy A17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3020' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A20', 'Galaxy A20s', 'Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25', 'Galaxy A26');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3021' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A30', 'Galaxy A30s', 'Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35', 'Galaxy A36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3022' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A50', 'Galaxy A50s', 'Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55', 'Galaxy A56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3023' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A70', 'Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3024' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M10', 'Galaxy M11', 'Galaxy M12', 'Galaxy M13', 'Galaxy M14', 'Galaxy M15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3025' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M21', 'Galaxy M22', 'Galaxy M31', 'Galaxy M32', 'Galaxy M33', 'Galaxy M34', 'Galaxy M52', 'Galaxy M53');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3026' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S8', 'Galaxy S9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3027' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S10', 'Galaxy S10e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3028' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S10+', 'Galaxy S10 Lite');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3029' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3030' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20+', 'Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3031' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21 FE', 'Galaxy S22', 'Galaxy S23', 'Galaxy S24', 'Galaxy S25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3032' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21+', 'Galaxy S22+', 'Galaxy S23+', 'Galaxy S24+', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3033' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra', 'Galaxy S22 Ultra', 'Galaxy S23 Ultra', 'Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3034' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge', 'Galaxy S23 FE', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3035' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 8', 'Galaxy Note 9', 'Galaxy Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3036' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3037' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3038' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3039' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 8A', 'Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3040' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3041' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 7', 'Redmi Note 8', 'Redmi Note 8 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3042' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10', 'Redmi Note 10S');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3043' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3044' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3045' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3046' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T', 'Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3047' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3048' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3049' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3050' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3051' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C12', 'C15', 'C17', 'C18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3052' AND m.brand = 'Realme' AND m.model_name IN ('C20', 'C21', 'C21Y', 'C22', 'C23', 'C24', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3053' AND m.brand = 'Realme' AND m.model_name IN ('C25Y', 'C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3054' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3055' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3056' AND m.brand = 'Realme' AND m.model_name IN ('Realme 7', 'Realme 8', 'Realme 9', 'Realme 10', 'Realme 11', 'Realme 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3057' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3058' AND m.brand = 'Realme' AND m.model_name IN ('Realme GT 2', 'Realme GT 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3059' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 9', 'Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3060' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50', 'Hot 60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3061' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3062' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40', 'Note 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3063' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 4', 'Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3064' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 10', 'Smart HD');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3065' AND m.brand = 'Infinix' AND m.model_name IN ('Zero 30', 'Zero 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3066' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 5', 'Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3067' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3068' AND m.brand = 'Tecno' AND m.model_name IN ('Spark Go 2023', 'Spark Go 2024', 'Spark Go 2025');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3069' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3070' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3071' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3072' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8', 'Pop 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3073' AND m.brand = 'Oppo' AND m.model_name IN ('A3s', 'A5s', 'A12', 'A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3074' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3075' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3076' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3077' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3078' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3079' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3080' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3081' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3082' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3083' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3084' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3085' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3086' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3087' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3088' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3089' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3090' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3091' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3092' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3093' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3094' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3095' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3096' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3097' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3098' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3099' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3100' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3101' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3102' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3103' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3104' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3105' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3106' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3107' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3108' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3109' AND m.brand = 'Lenovo' AND m.model_name IN ('K13', 'K14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3110' AND m.brand = 'TCL' AND m.model_name IN ('TCL 20 SE', 'TCL 30 SE', 'TCL 40 SE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3111' AND m.brand = 'Lava' AND m.model_name IN ('Blaze 2', 'Agni 2');

UPDATE `screen_protectors` SET `product_id` = (SELECT p.`id` FROM `products` p WHERE p.`barcode` = `screen_protectors`.`barcode` LIMIT 1) WHERE `product_id` IS NULL;

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
