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
('Apple', 'iPhone 12', 'ايفون 12', 'apple iphone 12 ايفون 12 ابل'),
('Apple', 'iPhone 12 Pro', 'ايفون 12 برو', 'apple iphone 12 pro ايفون 12 برو ابل'),
('Apple', 'iPhone 12 mini', 'ايفون 12 ميني', 'apple iphone 12 mini ايفون 12 ميني ابل'),
('Apple', 'iPhone 13 mini', 'ايفون 13 ميني', 'apple iphone 13 mini ايفون 13 ميني ابل'),
('Apple', 'iPhone 13', 'ايفون 13', 'apple iphone 13 ايفون 13 ابل'),
('Apple', 'iPhone 13 Pro', 'ايفون 13 برو', 'apple iphone 13 pro ايفون 13 برو ابل'),
('Apple', 'iPhone 14', 'ايفون 14', 'apple iphone 14 ايفون 14 ابل'),
('Apple', 'iPhone 16e', 'ايفون 16e', 'apple iphone 16e ايفون 16e ابل'),
('Apple', 'iPhone 12 Pro Max', 'ايفون 12 برو ماكس', 'apple iphone 12 pro max ايفون 12 برو ماكس ابل'),
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
('Samsung', 'Galaxy A12', 'سامسونج A12', 'samsung galaxy a12 سامسونج a12 سامسونج'),
('Samsung', 'Galaxy A13', 'سامسونج A13', 'samsung galaxy a13 سامسونج a13 سامسونج'),
('Samsung', 'Galaxy A14', 'سامسونج A14', 'samsung galaxy a14 سامسونج a14 سامسونج'),
('Samsung', 'Galaxy A15', 'سامسونج A15', 'samsung galaxy a15 سامسونج a15 سامسونج'),
('Samsung', 'Galaxy A16', 'سامسونج A16', 'samsung galaxy a16 سامسونج a16 سامسونج'),
('Samsung', 'Galaxy A21s', 'سامسونج A21s', 'samsung galaxy a21s سامسونج a21s سامسونج'),
('Samsung', 'Galaxy A22', 'سامسونج A22', 'samsung galaxy a22 سامسونج a22 سامسونج'),
('Samsung', 'Galaxy A23', 'سامسونج A23', 'samsung galaxy a23 سامسونج a23 سامسونج');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Samsung', 'Galaxy A24', 'سامسونج A24', 'samsung galaxy a24 سامسونج a24 سامسونج'),
('Samsung', 'Galaxy A25', 'سامسونج A25', 'samsung galaxy a25 سامسونج a25 سامسونج'),
('Samsung', 'Galaxy A31', 'سامسونج A31', 'samsung galaxy a31 سامسونج a31 سامسونج'),
('Samsung', 'Galaxy A32', 'سامسونج A32', 'samsung galaxy a32 سامسونج a32 سامسونج'),
('Samsung', 'Galaxy A33', 'سامسونج A33', 'samsung galaxy a33 سامسونج a33 سامسونج'),
('Samsung', 'Galaxy A34', 'سامسونج A34', 'samsung galaxy a34 سامسونج a34 سامسونج'),
('Samsung', 'Galaxy A35', 'سامسونج A35', 'samsung galaxy a35 سامسونج a35 سامسونج'),
('Samsung', 'Galaxy A51', 'سامسونج A51', 'samsung galaxy a51 سامسونج a51 سامسونج'),
('Samsung', 'Galaxy A52', 'سامسونج A52', 'samsung galaxy a52 سامسونج a52 سامسونج'),
('Samsung', 'Galaxy A52s', 'سامسونج A52s', 'samsung galaxy a52s سامسونج a52s سامسونج'),
('Samsung', 'Galaxy A53', 'سامسونج A53', 'samsung galaxy a53 سامسونج a53 سامسونج'),
('Samsung', 'Galaxy A54', 'سامسونج A54', 'samsung galaxy a54 سامسونج a54 سامسونج'),
('Samsung', 'Galaxy A55', 'سامسونج A55', 'samsung galaxy a55 سامسونج a55 سامسونج'),
('Samsung', 'Galaxy A71', 'سامسونج A71', 'samsung galaxy a71 سامسونج a71 سامسونج'),
('Samsung', 'Galaxy A72', 'سامسونج A72', 'samsung galaxy a72 سامسونج a72 سامسونج'),
('Samsung', 'Galaxy A73', 'سامسونج A73', 'samsung galaxy a73 سامسونج a73 سامسونج'),
('Samsung', 'Galaxy M12', 'سامسونج M12', 'samsung galaxy m12 سامسونج m12 سامسونج'),
('Samsung', 'Galaxy M22', 'سامسونج M22', 'samsung galaxy m22 سامسونج m22 سامسونج'),
('Samsung', 'Galaxy M32', 'سامسونج M32', 'samsung galaxy m32 سامسونج m32 سامسونج'),
('Samsung', 'Galaxy M33', 'سامسونج M33', 'samsung galaxy m33 سامسونج m33 سامسونج'),
('Samsung', 'Galaxy M52', 'سامسونج M52', 'samsung galaxy m52 سامسونج m52 سامسونج'),
('Samsung', 'Galaxy S20', 'سامسونج S20', 'samsung galaxy s20 سامسونج s20 سامسونج'),
('Samsung', 'Galaxy S20 5G', 'سامسونج S20 5G', 'samsung galaxy s20 5g سامسونج s20 5g سامسونج'),
('Samsung', 'Galaxy S20+', 'سامسونج S20 بلاس', 'samsung galaxy s20+ سامسونج s20 بلاس سامسونج'),
('Samsung', 'Galaxy S20 FE', 'سامسونج S20 FE', 'samsung galaxy s20 fe سامسونج s20 fe سامسونج'),
('Samsung', 'Galaxy S20 Ultra', 'سامسونج S20 الترا', 'samsung galaxy s20 ultra سامسونج s20 الترا سامسونج'),
('Samsung', 'Galaxy S21', 'سامسونج S21', 'samsung galaxy s21 سامسونج s21 سامسونج'),
('Samsung', 'Galaxy S21+', 'سامسونج S21 بلاس', 'samsung galaxy s21+ سامسونج s21 بلاس سامسونج'),
('Samsung', 'Galaxy S21 FE', 'سامسونج S21 FE', 'samsung galaxy s21 fe سامسونج s21 fe سامسونج'),
('Samsung', 'Galaxy S21 Ultra', 'سامسونج S21 الترا', 'samsung galaxy s21 ultra سامسونج s21 الترا سامسونج'),
('Samsung', 'Galaxy S22', 'سامسونج S22', 'samsung galaxy s22 سامسونج s22 سامسونج'),
('Samsung', 'Galaxy S22+', 'سامسونج S22 بلاس', 'samsung galaxy s22+ سامسونج s22 بلاس سامسونج'),
('Samsung', 'Galaxy S22 Ultra', 'سامسونج S22 الترا', 'samsung galaxy s22 ultra سامسونج s22 الترا سامسونج'),
('Samsung', 'Galaxy S23', 'سامسونج S23', 'samsung galaxy s23 سامسونج s23 سامسونج'),
('Samsung', 'Galaxy S23+', 'سامسونج S23 بلاس', 'samsung galaxy s23+ سامسونج s23 بلاس سامسونج'),
('Samsung', 'Galaxy S23 FE', 'سامسونج S23 FE', 'samsung galaxy s23 fe سامسونج s23 fe سامسونج'),
('Samsung', 'Galaxy S23 Ultra', 'سامسونج S23 الترا', 'samsung galaxy s23 ultra سامسونج s23 الترا سامسونج'),
('Samsung', 'Galaxy S24', 'سامسونج S24', 'samsung galaxy s24 سامسونج s24 سامسونج'),
('Samsung', 'Galaxy S24+', 'سامسونج S24 بلاس', 'samsung galaxy s24+ سامسونج s24 بلاس سامسونج'),
('Samsung', 'Galaxy S24 FE', 'سامسونج S24 FE', 'samsung galaxy s24 fe سامسونج s24 fe سامسونج'),
('Samsung', 'Galaxy S24 Ultra', 'سامسونج S24 الترا', 'samsung galaxy s24 ultra سامسونج s24 الترا سامسونج'),
('Samsung', 'Galaxy S25', 'سامسونج S25', 'samsung galaxy s25 سامسونج s25 سامسونج'),
('Samsung', 'Galaxy S25+', 'سامسونج S25 بلاس', 'samsung galaxy s25+ سامسونج s25 بلاس سامسونج'),
('Samsung', 'Galaxy S25 Ultra', 'سامسونج S25 الترا', 'samsung galaxy s25 ultra سامسونج s25 الترا سامسونج'),
('Samsung', 'Galaxy S25 Edge', 'سامسونج S25 ايدج', 'samsung galaxy s25 edge سامسونج s25 ايدج سامسونج'),
('Samsung', 'Galaxy Note 10', 'سامسونج نوت 10', 'samsung galaxy note 10 سامسونج نوت 10 سامسونج'),
('Samsung', 'Galaxy Note 10+', 'سامسونج نوت 10 بلاس', 'samsung galaxy note 10+ سامسونج نوت 10 بلاس سامسونج'),
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
('Xiaomi', 'Redmi 9A', 'ريدمي 9A', 'xiaomi redmi 9a ريدمي 9a شاومي'),
('Xiaomi', 'Redmi 10A', 'ريدمي 10A', 'xiaomi redmi 10a ريدمي 10a شاومي'),
('Xiaomi', 'Redmi A1', 'ريدمي A1', 'xiaomi redmi a1 ريدمي a1 شاومي');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
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
('Xiaomi', 'Redmi Note 8', 'ريدمي نوت 8', 'xiaomi redmi note 8 ريدمي نوت 8 شاومي'),
('Xiaomi', 'Redmi Note 9', 'ريدمي نوت 9', 'xiaomi redmi note 9 ريدمي نوت 9 شاومي'),
('Xiaomi', 'Redmi Note 9S', 'ريدمي نوت 9s', 'xiaomi redmi note 9s ريدمي نوت 9s شاومي'),
('Xiaomi', 'Redmi Note 10', 'ريدمي نوت 10', 'xiaomi redmi note 10 ريدمي نوت 10 شاومي'),
('Xiaomi', 'Redmi Note 11', 'ريدمي نوت 11', 'xiaomi redmi note 11 ريدمي نوت 11 شاومي'),
('Xiaomi', 'Redmi Note 11S', 'ريدمي نوت 11s', 'xiaomi redmi note 11s ريدمي نوت 11s شاومي'),
('Xiaomi', 'Redmi Note 12', 'ريدمي نوت 12', 'xiaomi redmi note 12 ريدمي نوت 12 شاومي'),
('Xiaomi', 'Redmi Note 12S', 'ريدمي نوت 12s', 'xiaomi redmi note 12s ريدمي نوت 12s شاومي'),
('Xiaomi', 'Redmi Note 13', 'ريدمي نوت 13', 'xiaomi redmi note 13 ريدمي نوت 13 شاومي'),
('Xiaomi', 'Redmi Note 14', 'ريدمي نوت 14', 'xiaomi redmi note 14 ريدمي نوت 14 شاومي'),
('Xiaomi', 'Redmi Note 14 Pro', 'ريدمي نوت 14 برو', 'xiaomi redmi note 14 pro ريدمي نوت 14 برو شاومي'),
('Xiaomi', 'Redmi Note 15', 'ريدمي نوت 15', 'xiaomi redmi note 15 ريدمي نوت 15 شاومي'),
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
('Xiaomi', 'Xiaomi 14T Pro', 'شاومي 14T برو', 'xiaomi xiaomi 14t pro شاومي 14t برو شاومي'),
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
('Infinix', 'Hot 10', 'انفينكس هوت 10', 'infinix hot 10 انفينكس هوت 10 انفينكس'),
('Infinix', 'Hot 11', 'انفينكس هوت 11', 'infinix hot 11 انفينكس هوت 11 انفينكس'),
('Infinix', 'Hot 12', 'انفينكس هوت 12', 'infinix hot 12 انفينكس هوت 12 انفينكس'),
('Infinix', 'Hot 20', 'انفينكس هوت 20', 'infinix hot 20 انفينكس هوت 20 انفينكس'),
('Infinix', 'Hot 30', 'انفينكس هوت 30', 'infinix hot 30 انفينكس هوت 30 انفينكس'),
('Infinix', 'Hot 40', 'انفينكس هوت 40', 'infinix hot 40 انفينكس هوت 40 انفينكس');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Infinix', 'Hot 50', 'انفينكس هوت 50', 'infinix hot 50 انفينكس هوت 50 انفينكس'),
('Infinix', 'Note 7', 'انفينكس نوت 7', 'infinix note 7 انفينكس نوت 7 انفينكس'),
('Infinix', 'Note 8', 'انفينكس نوت 8', 'infinix note 8 انفينكس نوت 8 انفينكس'),
('Infinix', 'Note 10', 'انفينكس نوت 10', 'infinix note 10 انفينكس نوت 10 انفينكس'),
('Infinix', 'Note 11', 'انفينكس نوت 11', 'infinix note 11 انفينكس نوت 11 انفينكس'),
('Infinix', 'Note 12', 'انفينكس نوت 12', 'infinix note 12 انفينكس نوت 12 انفينكس'),
('Infinix', 'Note 30', 'انفينكس نوت 30', 'infinix note 30 انفينكس نوت 30 انفينكس'),
('Infinix', 'Note 40', 'انفينكس نوت 40', 'infinix note 40 انفينكس نوت 40 انفينكس'),
('Infinix', 'Smart 5', 'انفينكس سمارت 5', 'infinix smart 5 انفينكس سمارت 5 انفينكس'),
('Infinix', 'Smart 6', 'انفينكس سمارت 6', 'infinix smart 6 انفينكس سمارت 6 انفينكس'),
('Infinix', 'Smart 7', 'انفينكس سمارت 7', 'infinix smart 7 انفينكس سمارت 7 انفينكس'),
('Infinix', 'Smart 8', 'انفينكس سمارت 8', 'infinix smart 8 انفينكس سمارت 8 انفينكس'),
('Infinix', 'Smart 9', 'انفينكس سمارت 9', 'infinix smart 9 انفينكس سمارت 9 انفينكس'),
('Tecno', 'Spark 6', 'تكنو سبارك 6', 'tecno spark 6 تكنو سبارك 6 تكنو'),
('Tecno', 'Spark 7', 'تكنو سبارك 7', 'tecno spark 7 تكنو سبارك 7 تكنو'),
('Tecno', 'Spark 8', 'تكنو سبارك 8', 'tecno spark 8 تكنو سبارك 8 تكنو'),
('Tecno', 'Spark 9', 'تكنو سبارك 9', 'tecno spark 9 تكنو سبارك 9 تكنو'),
('Tecno', 'Spark 10', 'تكنو سبارك 10', 'tecno spark 10 تكنو سبارك 10 تكنو'),
('Tecno', 'Spark 20', 'تكنو سبارك 20', 'tecno spark 20 تكنو سبارك 20 تكنو'),
('Tecno', 'Spark 30', 'تكنو سبارك 30', 'tecno spark 30 تكنو سبارك 30 تكنو'),
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
('Tecno', 'Pop 8', 'تكنو بوب 8', 'tecno pop 8 تكنو بوب 8 تكنو'),
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
('Oppo', 'Reno 8T', 'اوبو رينو 8T', 'oppo reno 8t اوبو رينو 8t اوبو');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
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
('Vivo', 'V27', 'فيفو V27', 'vivo v27 فيفو v27 فيفو'),
('Vivo', 'V29', 'فيفو V29', 'vivo v29 فيفو v29 فيفو'),
('Vivo', 'V30', 'فيفو V30', 'vivo v30 فيفو v30 فيفو'),
('Vivo', 'X60', 'فيفو X60', 'vivo x60 فيفو x60 فيفو'),
('Vivo', 'X70', 'فيفو X70', 'vivo x70 فيفو x70 فيفو'),
('Vivo', 'X80', 'فيفو X80', 'vivo x80 فيفو x80 فيفو'),
('Vivo', 'X90', 'فيفو X90', 'vivo x90 فيفو x90 فيفو'),
('Realme', 'C11', 'ريلمي C11', 'realme c11 ريلمي c11 ريلمي'),
('Realme', 'C15', 'ريلمي C15', 'realme c15 ريلمي c15 ريلمي'),
('Realme', 'C20', 'ريلمي C20', 'realme c20 ريلمي c20 ريلمي'),
('Realme', 'C21', 'ريلمي C21', 'realme c21 ريلمي c21 ريلمي'),
('Realme', 'C25', 'ريلمي C25', 'realme c25 ريلمي c25 ريلمي'),
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
('Realme', '8', 'ريلمي 8', 'realme 8 ريلمي 8 ريلمي'),
('Realme', '9', 'ريلمي 9', 'realme 9 ريلمي 9 ريلمي'),
('Realme', '10', 'ريلمي 10', 'realme 10 ريلمي 10 ريلمي'),
('Realme', '11', 'ريلمي 11', 'realme 11 ريلمي 11 ريلمي'),
('Realme', '12', 'ريلمي 12', 'realme 12 ريلمي 12 ريلمي');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
('Realme', 'Narzo 30', 'ريلمي نارزو 30', 'realme narzo 30 ريلمي نارزو 30 ريلمي'),
('Realme', 'Narzo 50', 'ريلمي نارزو 50', 'realme narzo 50 ريلمي نارزو 50 ريلمي'),
('Realme', 'Narzo 60', 'ريلمي نارزو 60', 'realme narzo 60 ريلمي نارزو 60 ريلمي'),
('Realme', 'Narzo 70', 'ريلمي نارزو 70', 'realme narzo 70 ريلمي نارزو 70 ريلمي'),
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
('Honor', 'X6', 'هونر X6', 'honor x6 هونر x6 هونر'),
('Honor', 'X7', 'هونر X7', 'honor x7 هونر x7 هونر'),
('Honor', 'X8', 'هونر X8', 'honor x8 هونر x8 هونر'),
('Honor', 'X9', 'هونر X9', 'honor x9 هونر x9 هونر'),
('Honor', '8A', 'هونر 8A', 'honor 8a هونر 8a هونر'),
('Honor', '8X', 'هونر 8X', 'honor 8x هونر 8x هونر'),
('Honor', '9X', 'هونر 9X', 'honor 9x هونر 9x هونر'),
('Honor', '10', 'هونر 10', 'honor 10 هونر 10 هونر'),
('Honor', '20', 'هونر 20', 'honor 20 هونر 20 هونر'),
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
('Motorola', 'Moto G84', 'موتورولا G84', 'motorola moto g84 موتورولا g84 موتورولا'),
('Motorola', 'Moto E7', 'موتورولا E7', 'motorola moto e7 موتورولا e7 موتورولا'),
('Motorola', 'Moto E13', 'موتورولا E13', 'motorola moto e13 موتورولا e13 موتورولا');

INSERT IGNORE INTO `device_models` (`brand`, `model_name`, `model_name_ar`, `search_key`) VALUES
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
('Lenovo', 'K14 Plus', 'لينوفو K14 بلس', 'lenovo k14 plus لينوفو k14 بلس لينوفو'),
('Lenovo', 'K13', 'لينوفو K13', 'lenovo k13 لينوفو k13 لينوفو');

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3001', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - زجاج مقوي 9H', 'Screen Protector iPhone 6 6s 7 8 SE - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3002', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - زجاج نانو مرن', 'Screen Protector iPhone 6 6s 7 8 SE - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3003', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - برايفسي خصوصية', 'Screen Protector iPhone 6 6s 7 8 SE - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3004', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - سيراميك', 'Screen Protector iPhone 6 6s 7 8 SE - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3005', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - مات ضد البصمة', 'Screen Protector iPhone 6 6s 7 8 SE - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3006', 'qr', 'اسكرينة ايفون بلس 5.5 - زجاج مقوي 9H', 'Screen Protector iPhone Plus 5.5 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3007', 'qr', 'اسكرينة ايفون بلس 5.5 - زجاج نانو مرن', 'Screen Protector iPhone Plus 5.5 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3008', 'qr', 'اسكرينة ايفون بلس 5.5 - برايفسي خصوصية', 'Screen Protector iPhone Plus 5.5 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3009', 'qr', 'اسكرينة ايفون بلس 5.5 - سيراميك', 'Screen Protector iPhone Plus 5.5 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3010', 'qr', 'اسكرينة ايفون بلس 5.5 - مات ضد البصمة', 'Screen Protector iPhone Plus 5.5 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3011', 'qr', 'اسكرينة ايفون X / XS / 11 برو - زجاج مقوي 9H', 'Screen Protector iPhone X XS 11 Pro - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3012', 'qr', 'اسكرينة ايفون X / XS / 11 برو - زجاج نانو مرن', 'Screen Protector iPhone X XS 11 Pro - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3013', 'qr', 'اسكرينة ايفون X / XS / 11 برو - برايفسي خصوصية', 'Screen Protector iPhone X XS 11 Pro - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3014', 'qr', 'اسكرينة ايفون X / XS / 11 برو - سيراميك', 'Screen Protector iPhone X XS 11 Pro - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3015', 'qr', 'اسكرينة ايفون X / XS / 11 برو - مات ضد البصمة', 'Screen Protector iPhone X XS 11 Pro - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3016', 'qr', 'اسكرينة ايفون XR / 11 - زجاج مقوي 9H', 'Screen Protector iPhone XR 11 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3017', 'qr', 'اسكرينة ايفون XR / 11 - زجاج نانو مرن', 'Screen Protector iPhone XR 11 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3018', 'qr', 'اسكرينة ايفون XR / 11 - برايفسي خصوصية', 'Screen Protector iPhone XR 11 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3019', 'qr', 'اسكرينة ايفون XR / 11 - سيراميك', 'Screen Protector iPhone XR 11 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3020', 'qr', 'اسكرينة ايفون XR / 11 - مات ضد البصمة', 'Screen Protector iPhone XR 11 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3021', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone XS Max 11 Pro Max - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3022', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone XS Max 11 Pro Max - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3023', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone XS Max 11 Pro Max - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3024', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - سيراميك', 'Screen Protector iPhone XS Max 11 Pro Max - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3025', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone XS Max 11 Pro Max - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3026', 'qr', 'اسكرينة ايفون 12 / 12 برو - زجاج مقوي 9H', 'Screen Protector iPhone 12 12 Pro - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3027', 'qr', 'اسكرينة ايفون 12 / 12 برو - زجاج نانو مرن', 'Screen Protector iPhone 12 12 Pro - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3028', 'qr', 'اسكرينة ايفون 12 / 12 برو - برايفسي خصوصية', 'Screen Protector iPhone 12 12 Pro - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3029', 'qr', 'اسكرينة ايفون 12 / 12 برو - سيراميك', 'Screen Protector iPhone 12 12 Pro - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3030', 'qr', 'اسكرينة ايفون 12 / 12 برو - مات ضد البصمة', 'Screen Protector iPhone 12 12 Pro - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3031', 'qr', 'اسكرينة ايفون ميني 12/13 - زجاج مقوي 9H', 'Screen Protector iPhone 12 13 mini - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3032', 'qr', 'اسكرينة ايفون ميني 12/13 - زجاج نانو مرن', 'Screen Protector iPhone 12 13 mini - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3033', 'qr', 'اسكرينة ايفون ميني 12/13 - برايفسي خصوصية', 'Screen Protector iPhone 12 13 mini - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3034', 'qr', 'اسكرينة ايفون ميني 12/13 - سيراميك', 'Screen Protector iPhone 12 13 mini - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3035', 'qr', 'اسكرينة ايفون ميني 12/13 - مات ضد البصمة', 'Screen Protector iPhone 12 13 mini - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3036', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - زجاج مقوي 9H', 'Screen Protector iPhone 13 13 Pro 14 16e - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3037', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - زجاج نانو مرن', 'Screen Protector iPhone 13 13 Pro 14 16e - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3038', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - برايفسي خصوصية', 'Screen Protector iPhone 13 13 Pro 14 16e - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3039', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - سيراميك', 'Screen Protector iPhone 13 13 Pro 14 16e - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3040', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - مات ضد البصمة', 'Screen Protector iPhone 13 13 Pro 14 16e - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3041', 'qr', 'اسكرينة ايفون 12 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone 12 Pro Max - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3042', 'qr', 'اسكرينة ايفون 12 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone 12 Pro Max - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3043', 'qr', 'اسكرينة ايفون 12 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone 12 Pro Max - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3044', 'qr', 'اسكرينة ايفون 12 برو ماكس - سيراميك', 'Screen Protector iPhone 12 Pro Max - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3045', 'qr', 'اسكرينة ايفون 12 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone 12 Pro Max - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3046', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 13 Pro Max 14 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3047', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - زجاج نانو مرن', 'Screen Protector iPhone 13 Pro Max 14 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3048', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - برايفسي خصوصية', 'Screen Protector iPhone 13 Pro Max 14 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3049', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - سيراميك', 'Screen Protector iPhone 13 Pro Max 14 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3050', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - مات ضد البصمة', 'Screen Protector iPhone 13 Pro Max 14 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3051', 'qr', 'اسكرينة ايفون 14 برو / 15 - زجاج مقوي 9H', 'Screen Protector iPhone 14 Pro 15 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3052', 'qr', 'اسكرينة ايفون 14 برو / 15 - زجاج نانو مرن', 'Screen Protector iPhone 14 Pro 15 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3053', 'qr', 'اسكرينة ايفون 14 برو / 15 - برايفسي خصوصية', 'Screen Protector iPhone 14 Pro 15 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3054', 'qr', 'اسكرينة ايفون 14 برو / 15 - سيراميك', 'Screen Protector iPhone 14 Pro 15 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3055', 'qr', 'اسكرينة ايفون 14 برو / 15 - مات ضد البصمة', 'Screen Protector iPhone 14 Pro 15 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3056', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 14 Pro Max 15 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3057', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - زجاج نانو مرن', 'Screen Protector iPhone 14 Pro Max 15 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3058', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - برايفسي خصوصية', 'Screen Protector iPhone 14 Pro Max 15 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3059', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - سيراميك', 'Screen Protector iPhone 14 Pro Max 15 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3060', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - مات ضد البصمة', 'Screen Protector iPhone 14 Pro Max 15 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3061', 'qr', 'اسكرينة ايفون 15 برو / 16 - زجاج مقوي 9H', 'Screen Protector iPhone 15 Pro 16 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3062', 'qr', 'اسكرينة ايفون 15 برو / 16 - زجاج نانو مرن', 'Screen Protector iPhone 15 Pro 16 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3063', 'qr', 'اسكرينة ايفون 15 برو / 16 - برايفسي خصوصية', 'Screen Protector iPhone 15 Pro 16 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3064', 'qr', 'اسكرينة ايفون 15 برو / 16 - سيراميك', 'Screen Protector iPhone 15 Pro 16 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3065', 'qr', 'اسكرينة ايفون 15 برو / 16 - مات ضد البصمة', 'Screen Protector iPhone 15 Pro 16 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3066', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 15 Pro Max 16 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3067', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - زجاج نانو مرن', 'Screen Protector iPhone 15 Pro Max 16 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3068', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - برايفسي خصوصية', 'Screen Protector iPhone 15 Pro Max 16 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3069', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - سيراميك', 'Screen Protector iPhone 15 Pro Max 16 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3070', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - مات ضد البصمة', 'Screen Protector iPhone 15 Pro Max 16 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3071', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - زجاج مقوي 9H', 'Screen Protector iPhone 16 Pro 17 17 Pro - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3072', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - زجاج نانو مرن', 'Screen Protector iPhone 16 Pro 17 17 Pro - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3073', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - برايفسي خصوصية', 'Screen Protector iPhone 16 Pro 17 17 Pro - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3074', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - سيراميك', 'Screen Protector iPhone 16 Pro 17 17 Pro - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3075', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - مات ضد البصمة', 'Screen Protector iPhone 16 Pro 17 17 Pro - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3076', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3077', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3078', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3079', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - سيراميك', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3080', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3081', 'qr', 'اسكرينة ايفون 17 اير - زجاج مقوي 9H', 'Screen Protector iPhone 17 Air - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3082', 'qr', 'اسكرينة ايفون 17 اير - زجاج نانو مرن', 'Screen Protector iPhone 17 Air - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3083', 'qr', 'اسكرينة ايفون 17 اير - برايفسي خصوصية', 'Screen Protector iPhone 17 Air - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3084', 'qr', 'اسكرينة ايفون 17 اير - سيراميك', 'Screen Protector iPhone 17 Air - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3085', 'qr', 'اسكرينة ايفون 17 اير - مات ضد البصمة', 'Screen Protector iPhone 17 Air - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3086', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - زجاج مقوي 9H', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3087', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - زجاج نانو مرن', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3088', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - برايفسي خصوصية', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3089', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - سيراميك', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3090', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - مات ضد البصمة', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3091', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - زجاج مقوي 9H', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3092', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - زجاج نانو مرن', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3093', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - برايفسي خصوصية', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3094', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - سيراميك', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3095', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - مات ضد البصمة', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3096', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - زجاج مقوي 9H', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3097', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - زجاج نانو مرن', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3098', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - برايفسي خصوصية', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3099', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - سيراميك', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3100', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - مات ضد البصمة', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3101', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - زجاج مقوي 9H', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3102', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - زجاج نانو مرن', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3103', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - برايفسي خصوصية', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3104', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - سيراميك', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3105', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - مات ضد البصمة', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3106', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - زجاج مقوي 9H', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3107', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - زجاج نانو مرن', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3108', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - برايفسي خصوصية', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3109', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - سيراميك', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3110', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - مات ضد البصمة', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3111', 'qr', 'اسكرينة سامسونج A71/A72/A73 - زجاج مقوي 9H', 'Screen Protector Samsung A71 A72 A73 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3112', 'qr', 'اسكرينة سامسونج A71/A72/A73 - زجاج نانو مرن', 'Screen Protector Samsung A71 A72 A73 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3113', 'qr', 'اسكرينة سامسونج A71/A72/A73 - برايفسي خصوصية', 'Screen Protector Samsung A71 A72 A73 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3114', 'qr', 'اسكرينة سامسونج A71/A72/A73 - سيراميك', 'Screen Protector Samsung A71 A72 A73 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3115', 'qr', 'اسكرينة سامسونج A71/A72/A73 - مات ضد البصمة', 'Screen Protector Samsung A71 A72 A73 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3116', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - زجاج مقوي 9H', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3117', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - زجاج نانو مرن', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3118', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - برايفسي خصوصية', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3119', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - سيراميك', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3120', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - مات ضد البصمة', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3121', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S20 S20 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3122', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S20 S20 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3123', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S20 S20 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3124', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - سيراميك', 'Screen Protector Samsung S20 S20 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3125', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - مات ضد البصمة', 'Screen Protector Samsung S20 S20 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3126', 'qr', 'اسكرينة سامسونج S20 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S20 Ultra - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3127', 'qr', 'اسكرينة سامسونج S20 الترا - زجاج نانو مرن', 'Screen Protector Samsung S20 Ultra - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3128', 'qr', 'اسكرينة سامسونج S20 الترا - برايفسي خصوصية', 'Screen Protector Samsung S20 Ultra - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3129', 'qr', 'اسكرينة سامسونج S20 الترا - سيراميك', 'Screen Protector Samsung S20 Ultra - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3130', 'qr', 'اسكرينة سامسونج S20 الترا - مات ضد البصمة', 'Screen Protector Samsung S20 Ultra - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3131', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S21 S21 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3132', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S21 S21 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3133', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S21 S21 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3134', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - سيراميك', 'Screen Protector Samsung S21 S21 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3135', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - مات ضد البصمة', 'Screen Protector Samsung S21 S21 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3136', 'qr', 'اسكرينة سامسونج S21 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S21 Ultra - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3137', 'qr', 'اسكرينة سامسونج S21 الترا - زجاج نانو مرن', 'Screen Protector Samsung S21 Ultra - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3138', 'qr', 'اسكرينة سامسونج S21 الترا - برايفسي خصوصية', 'Screen Protector Samsung S21 Ultra - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3139', 'qr', 'اسكرينة سامسونج S21 الترا - سيراميك', 'Screen Protector Samsung S21 Ultra - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3140', 'qr', 'اسكرينة سامسونج S21 الترا - مات ضد البصمة', 'Screen Protector Samsung S21 Ultra - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3141', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S22 S22 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3142', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S22 S22 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3143', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S22 S22 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3144', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - سيراميك', 'Screen Protector Samsung S22 S22 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3145', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - مات ضد البصمة', 'Screen Protector Samsung S22 S22 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3146', 'qr', 'اسكرينة سامسونج S22 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S22 Ultra - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3147', 'qr', 'اسكرينة سامسونج S22 الترا - زجاج نانو مرن', 'Screen Protector Samsung S22 Ultra - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3148', 'qr', 'اسكرينة سامسونج S22 الترا - برايفسي خصوصية', 'Screen Protector Samsung S22 Ultra - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3149', 'qr', 'اسكرينة سامسونج S22 الترا - سيراميك', 'Screen Protector Samsung S22 Ultra - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3150', 'qr', 'اسكرينة سامسونج S22 الترا - مات ضد البصمة', 'Screen Protector Samsung S22 Ultra - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3151', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - زجاج مقوي 9H', 'Screen Protector Samsung S23 S23 Plus FE - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3152', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - زجاج نانو مرن', 'Screen Protector Samsung S23 S23 Plus FE - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3153', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - برايفسي خصوصية', 'Screen Protector Samsung S23 S23 Plus FE - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3154', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - سيراميك', 'Screen Protector Samsung S23 S23 Plus FE - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3155', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - مات ضد البصمة', 'Screen Protector Samsung S23 S23 Plus FE - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3156', 'qr', 'اسكرينة سامسونج S23 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S23 Ultra - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3157', 'qr', 'اسكرينة سامسونج S23 الترا - زجاج نانو مرن', 'Screen Protector Samsung S23 Ultra - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3158', 'qr', 'اسكرينة سامسونج S23 الترا - برايفسي خصوصية', 'Screen Protector Samsung S23 Ultra - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3159', 'qr', 'اسكرينة سامسونج S23 الترا - سيراميك', 'Screen Protector Samsung S23 Ultra - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3160', 'qr', 'اسكرينة سامسونج S23 الترا - مات ضد البصمة', 'Screen Protector Samsung S23 Ultra - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3161', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - زجاج مقوي 9H', 'Screen Protector Samsung S24 S24 Plus FE - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3162', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - زجاج نانو مرن', 'Screen Protector Samsung S24 S24 Plus FE - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3163', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - برايفسي خصوصية', 'Screen Protector Samsung S24 S24 Plus FE - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3164', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - سيراميك', 'Screen Protector Samsung S24 S24 Plus FE - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3165', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - مات ضد البصمة', 'Screen Protector Samsung S24 S24 Plus FE - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3166', 'qr', 'اسكرينة سامسونج S24 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S24 Ultra - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3167', 'qr', 'اسكرينة سامسونج S24 الترا - زجاج نانو مرن', 'Screen Protector Samsung S24 Ultra - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3168', 'qr', 'اسكرينة سامسونج S24 الترا - برايفسي خصوصية', 'Screen Protector Samsung S24 Ultra - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3169', 'qr', 'اسكرينة سامسونج S24 الترا - سيراميك', 'Screen Protector Samsung S24 Ultra - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3170', 'qr', 'اسكرينة سامسونج S24 الترا - مات ضد البصمة', 'Screen Protector Samsung S24 Ultra - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3171', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S25 S25 Plus - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3172', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S25 S25 Plus - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3173', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S25 S25 Plus - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3174', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - سيراميك', 'Screen Protector Samsung S25 S25 Plus - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3175', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - مات ضد البصمة', 'Screen Protector Samsung S25 S25 Plus - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3176', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - زجاج مقوي 9H', 'Screen Protector Samsung S25 Ultra Edge - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3177', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - زجاج نانو مرن', 'Screen Protector Samsung S25 Ultra Edge - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3178', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - برايفسي خصوصية', 'Screen Protector Samsung S25 Ultra Edge - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3179', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - سيراميك', 'Screen Protector Samsung S25 Ultra Edge - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3180', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - مات ضد البصمة', 'Screen Protector Samsung S25 Ultra Edge - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3181', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - زجاج مقوي 9H', 'Screen Protector Samsung Note 10 20 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3182', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - زجاج نانو مرن', 'Screen Protector Samsung Note 10 20 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3183', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - برايفسي خصوصية', 'Screen Protector Samsung Note 10 20 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3184', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - سيراميك', 'Screen Protector Samsung Note 10 20 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3185', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - مات ضد البصمة', 'Screen Protector Samsung Note 10 20 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3186', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Samsung Z Flip 3 4 5 6 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3187', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - زجاج نانو مرن', 'Screen Protector Samsung Z Flip 3 4 5 6 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3188', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - برايفسي خصوصية', 'Screen Protector Samsung Z Flip 3 4 5 6 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3189', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - سيراميك', 'Screen Protector Samsung Z Flip 3 4 5 6 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3190', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - مات ضد البصمة', 'Screen Protector Samsung Z Flip 3 4 5 6 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3191', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Samsung Z Fold 3 4 5 6 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3192', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - زجاج نانو مرن', 'Screen Protector Samsung Z Fold 3 4 5 6 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3193', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - برايفسي خصوصية', 'Screen Protector Samsung Z Fold 3 4 5 6 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3194', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - سيراميك', 'Screen Protector Samsung Z Fold 3 4 5 6 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3195', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - مات ضد البصمة', 'Screen Protector Samsung Z Fold 3 4 5 6 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3196', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - زجاج مقوي 9H', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3197', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - زجاج نانو مرن', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3198', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - برايفسي خصوصية', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3199', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - سيراميك', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3200', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - مات ضد البصمة', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3201', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - زجاج مقوي 9H', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3202', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - زجاج نانو مرن', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3203', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - برايفسي خصوصية', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3204', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - سيراميك', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3205', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - مات ضد البصمة', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3206', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - زجاج مقوي 9H', 'Screen Protector Redmi Note 8 9 9S 10 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3207', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - زجاج نانو مرن', 'Screen Protector Redmi Note 8 9 9S 10 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3208', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - برايفسي خصوصية', 'Screen Protector Redmi Note 8 9 9S 10 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3209', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - سيراميك', 'Screen Protector Redmi Note 8 9 9S 10 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3210', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - مات ضد البصمة', 'Screen Protector Redmi Note 8 9 9S 10 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3211', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - زجاج مقوي 9H', 'Screen Protector Redmi Note 11 12 13 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3212', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - زجاج نانو مرن', 'Screen Protector Redmi Note 11 12 13 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3213', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - برايفسي خصوصية', 'Screen Protector Redmi Note 11 12 13 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3214', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - سيراميك', 'Screen Protector Redmi Note 11 12 13 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3215', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - مات ضد البصمة', 'Screen Protector Redmi Note 11 12 13 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3216', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - زجاج مقوي 9H', 'Screen Protector Redmi Note 14 15 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3217', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - زجاج نانو مرن', 'Screen Protector Redmi Note 14 15 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3218', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - برايفسي خصوصية', 'Screen Protector Redmi Note 14 15 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3219', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - سيراميك', 'Screen Protector Redmi Note 14 15 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3220', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - مات ضد البصمة', 'Screen Protector Redmi Note 14 15 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3221', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - زجاج مقوي 9H', 'Screen Protector Xiaomi 11 12 12T 13 13T - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3222', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - زجاج نانو مرن', 'Screen Protector Xiaomi 11 12 12T 13 13T - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3223', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - برايفسي خصوصية', 'Screen Protector Xiaomi 11 12 12T 13 13T - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3224', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - سيراميك', 'Screen Protector Xiaomi 11 12 12T 13 13T - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3225', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - مات ضد البصمة', 'Screen Protector Xiaomi 11 12 12T 13 13T - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3226', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - زجاج مقوي 9H', 'Screen Protector Xiaomi 14 14T 15 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3227', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - زجاج نانو مرن', 'Screen Protector Xiaomi 14 14T 15 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3228', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - برايفسي خصوصية', 'Screen Protector Xiaomi 14 14T 15 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3229', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - سيراميك', 'Screen Protector Xiaomi 14 14T 15 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3230', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - مات ضد البصمة', 'Screen Protector Xiaomi 14 14T 15 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3231', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - زجاج مقوي 9H', 'Screen Protector Poco X3 X4 X5 X6 X7 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3232', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - زجاج نانو مرن', 'Screen Protector Poco X3 X4 X5 X6 X7 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3233', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - برايفسي خصوصية', 'Screen Protector Poco X3 X4 X5 X6 X7 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3234', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - سيراميك', 'Screen Protector Poco X3 X4 X5 X6 X7 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3235', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - مات ضد البصمة', 'Screen Protector Poco X3 X4 X5 X6 X7 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3236', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - زجاج مقوي 9H', 'Screen Protector Poco M3 M4 M5 M6 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3237', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - زجاج نانو مرن', 'Screen Protector Poco M3 M4 M5 M6 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3238', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - برايفسي خصوصية', 'Screen Protector Poco M3 M4 M5 M6 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3239', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - سيراميك', 'Screen Protector Poco M3 M4 M5 M6 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3240', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - مات ضد البصمة', 'Screen Protector Poco M3 M4 M5 M6 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3241', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - زجاج مقوي 9H', 'Screen Protector Poco F3 F4 F5 F6 F7 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3242', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - زجاج نانو مرن', 'Screen Protector Poco F3 F4 F5 F6 F7 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3243', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - برايفسي خصوصية', 'Screen Protector Poco F3 F4 F5 F6 F7 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3244', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - سيراميك', 'Screen Protector Poco F3 F4 F5 F6 F7 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3245', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - مات ضد البصمة', 'Screen Protector Poco F3 F4 F5 F6 F7 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3246', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - زجاج مقوي 9H', 'Screen Protector Poco C40 C55 C65 C75 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3247', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - زجاج نانو مرن', 'Screen Protector Poco C40 C55 C65 C75 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3248', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - برايفسي خصوصية', 'Screen Protector Poco C40 C55 C65 C75 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3249', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - سيراميك', 'Screen Protector Poco C40 C55 C65 C75 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3250', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - مات ضد البصمة', 'Screen Protector Poco C40 C55 C65 C75 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3251', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - زجاج مقوي 9H', 'Screen Protector Infinix Hot 10 11 12 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3252', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - زجاج نانو مرن', 'Screen Protector Infinix Hot 10 11 12 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3253', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - برايفسي خصوصية', 'Screen Protector Infinix Hot 10 11 12 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3254', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - سيراميك', 'Screen Protector Infinix Hot 10 11 12 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3255', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - مات ضد البصمة', 'Screen Protector Infinix Hot 10 11 12 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3256', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - زجاج مقوي 9H', 'Screen Protector Infinix Hot 20 30 40 50 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3257', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - زجاج نانو مرن', 'Screen Protector Infinix Hot 20 30 40 50 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3258', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - برايفسي خصوصية', 'Screen Protector Infinix Hot 20 30 40 50 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3259', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - سيراميك', 'Screen Protector Infinix Hot 20 30 40 50 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3260', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - مات ضد البصمة', 'Screen Protector Infinix Hot 20 30 40 50 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3261', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - زجاج مقوي 9H', 'Screen Protector Infinix Note 7 8 10 11 12 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3262', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - زجاج نانو مرن', 'Screen Protector Infinix Note 7 8 10 11 12 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3263', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - برايفسي خصوصية', 'Screen Protector Infinix Note 7 8 10 11 12 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3264', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - سيراميك', 'Screen Protector Infinix Note 7 8 10 11 12 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3265', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - مات ضد البصمة', 'Screen Protector Infinix Note 7 8 10 11 12 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3266', 'qr', 'اسكرينة انفينكس نوت 30/40 - زجاج مقوي 9H', 'Screen Protector Infinix Note 30 40 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3267', 'qr', 'اسكرينة انفينكس نوت 30/40 - زجاج نانو مرن', 'Screen Protector Infinix Note 30 40 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3268', 'qr', 'اسكرينة انفينكس نوت 30/40 - برايفسي خصوصية', 'Screen Protector Infinix Note 30 40 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3269', 'qr', 'اسكرينة انفينكس نوت 30/40 - سيراميك', 'Screen Protector Infinix Note 30 40 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3270', 'qr', 'اسكرينة انفينكس نوت 30/40 - مات ضد البصمة', 'Screen Protector Infinix Note 30 40 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3271', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - زجاج مقوي 9H', 'Screen Protector Infinix Smart 5 6 7 8 9 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3272', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - زجاج نانو مرن', 'Screen Protector Infinix Smart 5 6 7 8 9 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3273', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - برايفسي خصوصية', 'Screen Protector Infinix Smart 5 6 7 8 9 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3274', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - سيراميك', 'Screen Protector Infinix Smart 5 6 7 8 9 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3275', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - مات ضد البصمة', 'Screen Protector Infinix Smart 5 6 7 8 9 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3276', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - زجاج مقوي 9H', 'Screen Protector Tecno Spark 6 7 8 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3277', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - زجاج نانو مرن', 'Screen Protector Tecno Spark 6 7 8 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3278', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - برايفسي خصوصية', 'Screen Protector Tecno Spark 6 7 8 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3279', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - سيراميك', 'Screen Protector Tecno Spark 6 7 8 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3280', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - مات ضد البصمة', 'Screen Protector Tecno Spark 6 7 8 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3281', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - زجاج مقوي 9H', 'Screen Protector Tecno Spark 9 10 20 30 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3282', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - زجاج نانو مرن', 'Screen Protector Tecno Spark 9 10 20 30 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3283', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - برايفسي خصوصية', 'Screen Protector Tecno Spark 9 10 20 30 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3284', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - سيراميك', 'Screen Protector Tecno Spark 9 10 20 30 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3285', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - مات ضد البصمة', 'Screen Protector Tecno Spark 9 10 20 30 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3286', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - زجاج مقوي 9H', 'Screen Protector Tecno Camon 15 16 17 18 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3287', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - زجاج نانو مرن', 'Screen Protector Tecno Camon 15 16 17 18 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3288', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - برايفسي خصوصية', 'Screen Protector Tecno Camon 15 16 17 18 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3289', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - سيراميك', 'Screen Protector Tecno Camon 15 16 17 18 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3290', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - مات ضد البصمة', 'Screen Protector Tecno Camon 15 16 17 18 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3291', 'qr', 'اسكرينة تكنو كامون 19/20/30 - زجاج مقوي 9H', 'Screen Protector Tecno Camon 19 20 30 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3292', 'qr', 'اسكرينة تكنو كامون 19/20/30 - زجاج نانو مرن', 'Screen Protector Tecno Camon 19 20 30 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3293', 'qr', 'اسكرينة تكنو كامون 19/20/30 - برايفسي خصوصية', 'Screen Protector Tecno Camon 19 20 30 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3294', 'qr', 'اسكرينة تكنو كامون 19/20/30 - سيراميك', 'Screen Protector Tecno Camon 19 20 30 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3295', 'qr', 'اسكرينة تكنو كامون 19/20/30 - مات ضد البصمة', 'Screen Protector Tecno Camon 19 20 30 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3296', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Tecno Pova 2 3 4 5 6 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3297', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - زجاج نانو مرن', 'Screen Protector Tecno Pova 2 3 4 5 6 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3298', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - برايفسي خصوصية', 'Screen Protector Tecno Pova 2 3 4 5 6 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3299', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - سيراميك', 'Screen Protector Tecno Pova 2 3 4 5 6 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3300', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - مات ضد البصمة', 'Screen Protector Tecno Pova 2 3 4 5 6 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3301', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - زجاج مقوي 9H', 'Screen Protector Tecno Pop 5 6 7 8 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3302', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - زجاج نانو مرن', 'Screen Protector Tecno Pop 5 6 7 8 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3303', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - برايفسي خصوصية', 'Screen Protector Tecno Pop 5 6 7 8 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3304', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - سيراميك', 'Screen Protector Tecno Pop 5 6 7 8 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3305', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - مات ضد البصمة', 'Screen Protector Tecno Pop 5 6 7 8 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3306', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - زجاج مقوي 9H', 'Screen Protector Oppo A15 A16 A17 A18 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3307', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - زجاج نانو مرن', 'Screen Protector Oppo A15 A16 A17 A18 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3308', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - برايفسي خصوصية', 'Screen Protector Oppo A15 A16 A17 A18 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3309', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - سيراميك', 'Screen Protector Oppo A15 A16 A17 A18 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3310', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - مات ضد البصمة', 'Screen Protector Oppo A15 A16 A17 A18 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3311', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - زجاج مقوي 9H', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3312', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - زجاج نانو مرن', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3313', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - برايفسي خصوصية', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3314', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - سيراميك', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3315', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - مات ضد البصمة', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3316', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - زجاج مقوي 9H', 'Screen Protector Oppo A76 A77 A78 A79 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3317', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - زجاج نانو مرن', 'Screen Protector Oppo A76 A77 A78 A79 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3318', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - برايفسي خصوصية', 'Screen Protector Oppo A76 A77 A78 A79 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3319', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - سيراميك', 'Screen Protector Oppo A76 A77 A78 A79 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3320', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - مات ضد البصمة', 'Screen Protector Oppo A76 A77 A78 A79 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3321', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - زجاج مقوي 9H', 'Screen Protector Oppo Reno 2 3 4 5 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3322', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - زجاج نانو مرن', 'Screen Protector Oppo Reno 2 3 4 5 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3323', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - برايفسي خصوصية', 'Screen Protector Oppo Reno 2 3 4 5 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3324', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - سيراميك', 'Screen Protector Oppo Reno 2 3 4 5 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3325', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - مات ضد البصمة', 'Screen Protector Oppo Reno 2 3 4 5 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3326', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - زجاج مقوي 9H', 'Screen Protector Oppo Reno 6 7 8 8T - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3327', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - زجاج نانو مرن', 'Screen Protector Oppo Reno 6 7 8 8T - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3328', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - برايفسي خصوصية', 'Screen Protector Oppo Reno 6 7 8 8T - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3329', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - سيراميك', 'Screen Protector Oppo Reno 6 7 8 8T - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3330', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - مات ضد البصمة', 'Screen Protector Oppo Reno 6 7 8 8T - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3331', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - زجاج مقوي 9H', 'Screen Protector Oppo Reno 10 11 12 13 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3332', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - زجاج نانو مرن', 'Screen Protector Oppo Reno 10 11 12 13 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3333', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - برايفسي خصوصية', 'Screen Protector Oppo Reno 10 11 12 13 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3334', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - سيراميك', 'Screen Protector Oppo Reno 10 11 12 13 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3335', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - مات ضد البصمة', 'Screen Protector Oppo Reno 10 11 12 13 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3336', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - زجاج مقوي 9H', 'Screen Protector Oppo F9 F11 F19 F21 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3337', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - زجاج نانو مرن', 'Screen Protector Oppo F9 F11 F19 F21 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3338', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - برايفسي خصوصية', 'Screen Protector Oppo F9 F11 F19 F21 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3339', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - سيراميك', 'Screen Protector Oppo F9 F11 F19 F21 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3340', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - مات ضد البصمة', 'Screen Protector Oppo F9 F11 F19 F21 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3341', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - زجاج مقوي 9H', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3342', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - زجاج نانو مرن', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3343', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - برايفسي خصوصية', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3344', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - سيراميك', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3345', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - مات ضد البصمة', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3346', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - زجاج مقوي 9H', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3347', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - زجاج نانو مرن', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3348', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - برايفسي خصوصية', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3349', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - سيراميك', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3350', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - مات ضد البصمة', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3351', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - زجاج مقوي 9H', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3352', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - زجاج نانو مرن', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3353', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - برايفسي خصوصية', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3354', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - سيراميك', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3355', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - مات ضد البصمة', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3356', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - زجاج مقوي 9H', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3357', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - زجاج نانو مرن', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3358', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - برايفسي خصوصية', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3359', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - سيراميك', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3360', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - مات ضد البصمة', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3361', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - زجاج مقوي 9H', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3362', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - زجاج نانو مرن', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3363', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - برايفسي خصوصية', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3364', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - سيراميك', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3365', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - مات ضد البصمة', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3366', 'qr', 'اسكرينة فيفو V29/V30 - زجاج مقوي 9H', 'Screen Protector Vivo V29 V30 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3367', 'qr', 'اسكرينة فيفو V29/V30 - زجاج نانو مرن', 'Screen Protector Vivo V29 V30 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3368', 'qr', 'اسكرينة فيفو V29/V30 - برايفسي خصوصية', 'Screen Protector Vivo V29 V30 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3369', 'qr', 'اسكرينة فيفو V29/V30 - سيراميك', 'Screen Protector Vivo V29 V30 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3370', 'qr', 'اسكرينة فيفو V29/V30 - مات ضد البصمة', 'Screen Protector Vivo V29 V30 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3371', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - زجاج مقوي 9H', 'Screen Protector Vivo X60 X70 X80 X90 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3372', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - زجاج نانو مرن', 'Screen Protector Vivo X60 X70 X80 X90 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3373', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - برايفسي خصوصية', 'Screen Protector Vivo X60 X70 X80 X90 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3374', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - سيراميك', 'Screen Protector Vivo X60 X70 X80 X90 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3375', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - مات ضد البصمة', 'Screen Protector Vivo X60 X70 X80 X90 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3376', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - زجاج مقوي 9H', 'Screen Protector Realme C11 C15 C20 C21 C25 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3377', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - زجاج نانو مرن', 'Screen Protector Realme C11 C15 C20 C21 C25 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3378', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - برايفسي خصوصية', 'Screen Protector Realme C11 C15 C20 C21 C25 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3379', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - سيراميك', 'Screen Protector Realme C11 C15 C20 C21 C25 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3380', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - مات ضد البصمة', 'Screen Protector Realme C11 C15 C20 C21 C25 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3381', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - زجاج مقوي 9H', 'Screen Protector Realme C30 C31 C33 C35 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3382', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - زجاج نانو مرن', 'Screen Protector Realme C30 C31 C33 C35 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3383', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - برايفسي خصوصية', 'Screen Protector Realme C30 C31 C33 C35 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3384', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - سيراميك', 'Screen Protector Realme C30 C31 C33 C35 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3385', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - مات ضد البصمة', 'Screen Protector Realme C30 C31 C33 C35 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3386', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - زجاج مقوي 9H', 'Screen Protector Realme C51 C53 C55 C61 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3387', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - زجاج نانو مرن', 'Screen Protector Realme C51 C53 C55 C61 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3388', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - برايفسي خصوصية', 'Screen Protector Realme C51 C53 C55 C61 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3389', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - سيراميك', 'Screen Protector Realme C51 C53 C55 C61 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3390', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - مات ضد البصمة', 'Screen Protector Realme C51 C53 C55 C61 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3391', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - زجاج مقوي 9H', 'Screen Protector Realme C63 C65 C67 C75 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3392', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - زجاج نانو مرن', 'Screen Protector Realme C63 C65 C67 C75 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3393', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - برايفسي خصوصية', 'Screen Protector Realme C63 C65 C67 C75 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3394', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - سيراميك', 'Screen Protector Realme C63 C65 C67 C75 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3395', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - مات ضد البصمة', 'Screen Protector Realme C63 C65 C67 C75 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3396', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - زجاج مقوي 9H', 'Screen Protector Realme 8 9 10 11 12 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3397', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - زجاج نانو مرن', 'Screen Protector Realme 8 9 10 11 12 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3398', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - برايفسي خصوصية', 'Screen Protector Realme 8 9 10 11 12 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3399', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - سيراميك', 'Screen Protector Realme 8 9 10 11 12 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3400', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - مات ضد البصمة', 'Screen Protector Realme 8 9 10 11 12 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3401', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - زجاج مقوي 9H', 'Screen Protector Realme Narzo 30 50 60 70 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3402', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - زجاج نانو مرن', 'Screen Protector Realme Narzo 30 50 60 70 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3403', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - برايفسي خصوصية', 'Screen Protector Realme Narzo 30 50 60 70 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3404', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - سيراميك', 'Screen Protector Realme Narzo 30 50 60 70 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3405', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - مات ضد البصمة', 'Screen Protector Realme Narzo 30 50 60 70 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3406', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - زجاج مقوي 9H', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3407', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - زجاج نانو مرن', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3408', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - برايفسي خصوصية', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3409', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - سيراميك', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3410', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - مات ضد البصمة', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3411', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - زجاج مقوي 9H', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3412', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - زجاج نانو مرن', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3413', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - برايفسي خصوصية', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3414', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - سيراميك', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3415', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - مات ضد البصمة', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3416', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - زجاج مقوي 9H', 'Screen Protector Huawei P30 P40 P50 P60 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3417', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - زجاج نانو مرن', 'Screen Protector Huawei P30 P40 P50 P60 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3418', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - برايفسي خصوصية', 'Screen Protector Huawei P30 P40 P50 P60 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3419', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - سيراميك', 'Screen Protector Huawei P30 P40 P50 P60 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3420', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - مات ضد البصمة', 'Screen Protector Huawei P30 P40 P50 P60 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3421', 'qr', 'اسكرينة هواوي ميت 30/40/50 - زجاج مقوي 9H', 'Screen Protector Huawei Mate 30 40 50 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3422', 'qr', 'اسكرينة هواوي ميت 30/40/50 - زجاج نانو مرن', 'Screen Protector Huawei Mate 30 40 50 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3423', 'qr', 'اسكرينة هواوي ميت 30/40/50 - برايفسي خصوصية', 'Screen Protector Huawei Mate 30 40 50 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3424', 'qr', 'اسكرينة هواوي ميت 30/40/50 - سيراميك', 'Screen Protector Huawei Mate 30 40 50 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3425', 'qr', 'اسكرينة هواوي ميت 30/40/50 - مات ضد البصمة', 'Screen Protector Huawei Mate 30 40 50 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3426', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - زجاج مقوي 9H', 'Screen Protector Honor X6 X7 X8 X9 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3427', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - زجاج نانو مرن', 'Screen Protector Honor X6 X7 X8 X9 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3428', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - برايفسي خصوصية', 'Screen Protector Honor X6 X7 X8 X9 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3429', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - سيراميك', 'Screen Protector Honor X6 X7 X8 X9 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3430', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - مات ضد البصمة', 'Screen Protector Honor X6 X7 X8 X9 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3431', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - زجاج مقوي 9H', 'Screen Protector Honor 8A 8X 9X 10 20 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3432', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - زجاج نانو مرن', 'Screen Protector Honor 8A 8X 9X 10 20 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3433', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - برايفسي خصوصية', 'Screen Protector Honor 8A 8X 9X 10 20 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3434', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - سيراميك', 'Screen Protector Honor 8A 8X 9X 10 20 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3435', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - مات ضد البصمة', 'Screen Protector Honor 8A 8X 9X 10 20 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3436', 'qr', 'اسكرينة هونر 50/70/90 - زجاج مقوي 9H', 'Screen Protector Honor 50 70 90 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3437', 'qr', 'اسكرينة هونر 50/70/90 - زجاج نانو مرن', 'Screen Protector Honor 50 70 90 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3438', 'qr', 'اسكرينة هونر 50/70/90 - برايفسي خصوصية', 'Screen Protector Honor 50 70 90 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3439', 'qr', 'اسكرينة هونر 50/70/90 - سيراميك', 'Screen Protector Honor 50 70 90 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3440', 'qr', 'اسكرينة هونر 50/70/90 - مات ضد البصمة', 'Screen Protector Honor 50 70 90 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3441', 'qr', 'اسكرينة هونر ماجيك 5/6 - زجاج مقوي 9H', 'Screen Protector Honor Magic 5 6 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3442', 'qr', 'اسكرينة هونر ماجيك 5/6 - زجاج نانو مرن', 'Screen Protector Honor Magic 5 6 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3443', 'qr', 'اسكرينة هونر ماجيك 5/6 - برايفسي خصوصية', 'Screen Protector Honor Magic 5 6 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3444', 'qr', 'اسكرينة هونر ماجيك 5/6 - سيراميك', 'Screen Protector Honor Magic 5 6 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3445', 'qr', 'اسكرينة هونر ماجيك 5/6 - مات ضد البصمة', 'Screen Protector Honor Magic 5 6 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3446', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - زجاج مقوي 9H', 'Screen Protector Nokia G10 G20 G21 C21 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3447', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - زجاج نانو مرن', 'Screen Protector Nokia G10 G20 G21 C21 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3448', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - برايفسي خصوصية', 'Screen Protector Nokia G10 G20 G21 C21 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3449', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - سيراميك', 'Screen Protector Nokia G10 G20 G21 C21 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3450', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - مات ضد البصمة', 'Screen Protector Nokia G10 G20 G21 C21 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3451', 'qr', 'اسكرينة نوكيا G42/X20/X30 - زجاج مقوي 9H', 'Screen Protector Nokia G42 X20 X30 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3452', 'qr', 'اسكرينة نوكيا G42/X20/X30 - زجاج نانو مرن', 'Screen Protector Nokia G42 X20 X30 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3453', 'qr', 'اسكرينة نوكيا G42/X20/X30 - برايفسي خصوصية', 'Screen Protector Nokia G42 X20 X30 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3454', 'qr', 'اسكرينة نوكيا G42/X20/X30 - سيراميك', 'Screen Protector Nokia G42 X20 X30 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3455', 'qr', 'اسكرينة نوكيا G42/X20/X30 - مات ضد البصمة', 'Screen Protector Nokia G42 X20 X30 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3456', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - زجاج مقوي 9H', 'Screen Protector Motorola G10 G20 G22 G30 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3457', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - زجاج نانو مرن', 'Screen Protector Motorola G10 G20 G22 G30 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3458', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - برايفسي خصوصية', 'Screen Protector Motorola G10 G20 G22 G30 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3459', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - سيراميك', 'Screen Protector Motorola G10 G20 G22 G30 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3460', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - مات ضد البصمة', 'Screen Protector Motorola G10 G20 G22 G30 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3461', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - زجاج مقوي 9H', 'Screen Protector Motorola G32 G52 G54 G62 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3462', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - زجاج نانو مرن', 'Screen Protector Motorola G32 G52 G54 G62 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3463', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - برايفسي خصوصية', 'Screen Protector Motorola G32 G52 G54 G62 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3464', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - سيراميك', 'Screen Protector Motorola G32 G52 G54 G62 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3465', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - مات ضد البصمة', 'Screen Protector Motorola G32 G52 G54 G62 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3466', 'qr', 'اسكرينة موتورولا G72/G82/G84 - زجاج مقوي 9H', 'Screen Protector Motorola G72 G82 G84 - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3467', 'qr', 'اسكرينة موتورولا G72/G82/G84 - زجاج نانو مرن', 'Screen Protector Motorola G72 G82 G84 - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3468', 'qr', 'اسكرينة موتورولا G72/G82/G84 - برايفسي خصوصية', 'Screen Protector Motorola G72 G82 G84 - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3469', 'qr', 'اسكرينة موتورولا G72/G82/G84 - سيراميك', 'Screen Protector Motorola G72 G82 G84 - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3470', 'qr', 'اسكرينة موتورولا G72/G82/G84 - مات ضد البصمة', 'Screen Protector Motorola G72 G82 G84 - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3),
('ASLYSCR3471', 'qr', 'اسكرينة موتورولا E7/E13/E22 - زجاج مقوي 9H', 'Screen Protector Motorola E7 E13 E22 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3472', 'qr', 'اسكرينة موتورولا E7/E13/E22 - زجاج نانو مرن', 'Screen Protector Motorola E7 E13 E22 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3473', 'qr', 'اسكرينة موتورولا E7/E13/E22 - برايفسي خصوصية', 'Screen Protector Motorola E7 E13 E22 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3474', 'qr', 'اسكرينة موتورولا E7/E13/E22 - سيراميك', 'Screen Protector Motorola E7 E13 E22 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3475', 'qr', 'اسكرينة موتورولا E7/E13/E22 - مات ضد البصمة', 'Screen Protector Motorola E7 E13 E22 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3476', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - زجاج مقوي 9H', 'Screen Protector OnePlus Nord 2 3 CE - Tempered Glass 9H', '9h', '54', '115', '98', 0, 3),
('ASLYSCR3477', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - زجاج نانو مرن', 'Screen Protector OnePlus Nord 2 3 CE - Nano Flexible', 'nano', '69', '145', '123', 0, 3),
('ASLYSCR3478', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - برايفسي خصوصية', 'Screen Protector OnePlus Nord 2 3 CE - Privacy Filter', 'privacy', '104', '195', '166', 0, 3),
('ASLYSCR3479', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - سيراميك', 'Screen Protector OnePlus Nord 2 3 CE - Ceramic Glass', 'ceramic', '84', '165', '140', 0, 3),
('ASLYSCR3480', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - مات ضد البصمة', 'Screen Protector OnePlus Nord 2 3 CE - Matte Anti-Fingerprint', 'matte', '79', '155', '132', 0, 3);

INSERT IGNORE INTO `screen_protectors` (`barcode`, `code_type`, `name`, `name_en`, `quality`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`) VALUES
('ASLYSCR3481', 'qr', 'اسكرينة ون بلس 9/9R/10 - زجاج مقوي 9H', 'Screen Protector OnePlus 9 9R 10 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3482', 'qr', 'اسكرينة ون بلس 9/9R/10 - زجاج نانو مرن', 'Screen Protector OnePlus 9 9R 10 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3483', 'qr', 'اسكرينة ون بلس 9/9R/10 - برايفسي خصوصية', 'Screen Protector OnePlus 9 9R 10 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3484', 'qr', 'اسكرينة ون بلس 9/9R/10 - سيراميك', 'Screen Protector OnePlus 9 9R 10 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3485', 'qr', 'اسكرينة ون بلس 9/9R/10 - مات ضد البصمة', 'Screen Protector OnePlus 9 9R 10 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3486', 'qr', 'اسكرينة ون بلس 11/12/13 - زجاج مقوي 9H', 'Screen Protector OnePlus 11 12 13 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3487', 'qr', 'اسكرينة ون بلس 11/12/13 - زجاج نانو مرن', 'Screen Protector OnePlus 11 12 13 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3488', 'qr', 'اسكرينة ون بلس 11/12/13 - برايفسي خصوصية', 'Screen Protector OnePlus 11 12 13 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3489', 'qr', 'اسكرينة ون بلس 11/12/13 - سيراميك', 'Screen Protector OnePlus 11 12 13 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3490', 'qr', 'اسكرينة ون بلس 11/12/13 - مات ضد البصمة', 'Screen Protector OnePlus 11 12 13 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3491', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - زجاج مقوي 9H', 'Screen Protector Pixel 6 6a 7 7a - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3492', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - زجاج نانو مرن', 'Screen Protector Pixel 6 6a 7 7a - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3493', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - برايفسي خصوصية', 'Screen Protector Pixel 6 6a 7 7a - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3494', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - سيراميك', 'Screen Protector Pixel 6 6a 7 7a - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3495', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - مات ضد البصمة', 'Screen Protector Pixel 6 6a 7 7a - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3496', 'qr', 'اسكرينة بيكسل 8/8a/9 - زجاج مقوي 9H', 'Screen Protector Pixel 8 8a 9 - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3497', 'qr', 'اسكرينة بيكسل 8/8a/9 - زجاج نانو مرن', 'Screen Protector Pixel 8 8a 9 - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3498', 'qr', 'اسكرينة بيكسل 8/8a/9 - برايفسي خصوصية', 'Screen Protector Pixel 8 8a 9 - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3499', 'qr', 'اسكرينة بيكسل 8/8a/9 - سيراميك', 'Screen Protector Pixel 8 8a 9 - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3500', 'qr', 'اسكرينة بيكسل 8/8a/9 - مات ضد البصمة', 'Screen Protector Pixel 8 8a 9 - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3501', 'qr', 'اسكرينة نوثينج فون 1/2/2a - زجاج مقوي 9H', 'Screen Protector Nothing Phone 1 2 2a - Tempered Glass 9H', '9h', '75', '150', '128', 0, 3),
('ASLYSCR3502', 'qr', 'اسكرينة نوثينج فون 1/2/2a - زجاج نانو مرن', 'Screen Protector Nothing Phone 1 2 2a - Nano Flexible', 'nano', '90', '180', '153', 0, 3),
('ASLYSCR3503', 'qr', 'اسكرينة نوثينج فون 1/2/2a - برايفسي خصوصية', 'Screen Protector Nothing Phone 1 2 2a - Privacy Filter', 'privacy', '125', '230', '196', 0, 3),
('ASLYSCR3504', 'qr', 'اسكرينة نوثينج فون 1/2/2a - سيراميك', 'Screen Protector Nothing Phone 1 2 2a - Ceramic Glass', 'ceramic', '105', '200', '170', 0, 3),
('ASLYSCR3505', 'qr', 'اسكرينة نوثينج فون 1/2/2a - مات ضد البصمة', 'Screen Protector Nothing Phone 1 2 2a - Matte Anti-Fingerprint', 'matte', '100', '190', '162', 0, 3),
('ASLYSCR3506', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - زجاج مقوي 9H', 'Screen Protector Itel A23 A48 A56 P38 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3507', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - زجاج نانو مرن', 'Screen Protector Itel A23 A48 A56 P38 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3508', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - برايفسي خصوصية', 'Screen Protector Itel A23 A48 A56 P38 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3509', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - سيراميك', 'Screen Protector Itel A23 A48 A56 P38 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3510', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - مات ضد البصمة', 'Screen Protector Itel A23 A48 A56 P38 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3511', 'qr', 'اسكرينة ايتل Vision 1/2 - زجاج مقوي 9H', 'Screen Protector Itel Vision 1 2 - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3512', 'qr', 'اسكرينة ايتل Vision 1/2 - زجاج نانو مرن', 'Screen Protector Itel Vision 1 2 - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3513', 'qr', 'اسكرينة ايتل Vision 1/2 - برايفسي خصوصية', 'Screen Protector Itel Vision 1 2 - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3514', 'qr', 'اسكرينة ايتل Vision 1/2 - سيراميك', 'Screen Protector Itel Vision 1 2 - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3515', 'qr', 'اسكرينة ايتل Vision 1/2 - مات ضد البصمة', 'Screen Protector Itel Vision 1 2 - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3),
('ASLYSCR3516', 'qr', 'اسكرينة لينوفو K14 بلس - زجاج مقوي 9H', 'Screen Protector Lenovo K14 Plus - Tempered Glass 9H', '9h', '45', '100', '85', 0, 3),
('ASLYSCR3517', 'qr', 'اسكرينة لينوفو K14 بلس - زجاج نانو مرن', 'Screen Protector Lenovo K14 Plus - Nano Flexible', 'nano', '60', '130', '110', 0, 3),
('ASLYSCR3518', 'qr', 'اسكرينة لينوفو K14 بلس - برايفسي خصوصية', 'Screen Protector Lenovo K14 Plus - Privacy Filter', 'privacy', '95', '180', '153', 0, 3),
('ASLYSCR3519', 'qr', 'اسكرينة لينوفو K14 بلس - سيراميك', 'Screen Protector Lenovo K14 Plus - Ceramic Glass', 'ceramic', '75', '150', '128', 0, 3),
('ASLYSCR3520', 'qr', 'اسكرينة لينوفو K14 بلس - مات ضد البصمة', 'Screen Protector Lenovo K14 Plus - Matte Anti-Fingerprint', 'matte', '70', '140', '119', 0, 3);

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3001' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3002' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3003' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3004' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3005' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6', 'iPhone 6s', 'iPhone 7', 'iPhone 8', 'iPhone SE 2020', 'iPhone SE 2022');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3006' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3007' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3008' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3009' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3010' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 6 Plus', 'iPhone 6s Plus', 'iPhone 7 Plus', 'iPhone 8 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3011' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3012' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3013' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3014' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3015' AND m.brand = 'Apple' AND m.model_name IN ('iPhone X', 'iPhone XS', 'iPhone 11 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3016' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3017' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3018' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3019' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3020' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XR', 'iPhone 11');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3021' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3022' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3023' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3024' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3025' AND m.brand = 'Apple' AND m.model_name IN ('iPhone XS Max', 'iPhone 11 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3026' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3027' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3028' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3029' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3030' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12', 'iPhone 12 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3031' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3032' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3033' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3034' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3035' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 mini', 'iPhone 13 mini');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3036' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3037' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3038' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3039' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3040' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 16e');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3041' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3042' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3043' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3044' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3045' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 12 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3046' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3047' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3048' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3049' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3050' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 13 Pro Max', 'iPhone 14 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3051' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3052' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3053' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3054' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3055' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro', 'iPhone 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3056' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3057' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3058' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3059' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3060' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 14 Pro Max', 'iPhone 15 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3061' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3062' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3063' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3064' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3065' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro', 'iPhone 16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3066' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3067' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3068' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3069' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3070' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 15 Pro Max', 'iPhone 16 Plus');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3071' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3072' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3073' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3074' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3075' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro', 'iPhone 17', 'iPhone 17 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3076' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3077' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3078' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3079' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3080' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 16 Pro Max', 'iPhone 17 Pro Max');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3081' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3082' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3083' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3084' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3085' AND m.brand = 'Apple' AND m.model_name IN ('iPhone 17 Air');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3086' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3087' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3088' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3089' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3090' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s', 'Galaxy A04', 'Galaxy A04s', 'Galaxy A04e', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3091' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3092' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3093' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3094' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3095' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15', 'Galaxy A16');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3096' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3097' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3098' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3099' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3100' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A21s', 'Galaxy A22', 'Galaxy A23', 'Galaxy A24', 'Galaxy A25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3101' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3102' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3103' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3104' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3105' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A31', 'Galaxy A32', 'Galaxy A33', 'Galaxy A34', 'Galaxy A35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3106' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3107' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3108' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3109' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3110' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A51', 'Galaxy A52', 'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3111' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3112' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3113' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3114' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3115' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy A71', 'Galaxy A72', 'Galaxy A73');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3116' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M33', 'Galaxy M52');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3117' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M33', 'Galaxy M52');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3118' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M33', 'Galaxy M52');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3119' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M33', 'Galaxy M52');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3120' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M33', 'Galaxy M52');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3121' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20+', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3122' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20+', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3123' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20+', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3124' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20+', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3125' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20', 'Galaxy S20 5G', 'Galaxy S20+', 'Galaxy S20 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3126' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3127' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3128' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3129' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3130' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3131' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21+', 'Galaxy S21 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3132' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21+', 'Galaxy S21 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3133' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21+', 'Galaxy S21 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3134' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21+', 'Galaxy S21 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3135' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21', 'Galaxy S21+', 'Galaxy S21 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3136' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3137' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3138' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3139' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3140' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S21 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3141' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22', 'Galaxy S22+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3142' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22', 'Galaxy S22+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3143' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22', 'Galaxy S22+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3144' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22', 'Galaxy S22+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3145' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22', 'Galaxy S22+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3146' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3147' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3148' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3149' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3150' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S22 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3151' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23', 'Galaxy S23+', 'Galaxy S23 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3152' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23', 'Galaxy S23+', 'Galaxy S23 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3153' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23', 'Galaxy S23+', 'Galaxy S23 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3154' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23', 'Galaxy S23+', 'Galaxy S23 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3155' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23', 'Galaxy S23+', 'Galaxy S23 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3156' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3157' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3158' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3159' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3160' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S23 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3161' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24', 'Galaxy S24+', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3162' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24', 'Galaxy S24+', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3163' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24', 'Galaxy S24+', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3164' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24', 'Galaxy S24+', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3165' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24', 'Galaxy S24+', 'Galaxy S24 FE');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3166' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3167' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3168' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3169' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3170' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S24 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3171' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3172' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3173' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3174' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3175' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25', 'Galaxy S25+');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3176' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3177' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3178' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3179' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3180' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy S25 Ultra', 'Galaxy S25 Edge');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3181' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3182' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3183' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3184' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3185' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Note 10', 'Galaxy Note 10+', 'Galaxy Note 20', 'Galaxy Note 20 Ultra');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3186' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3187' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3188' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3189' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3190' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Flip3', 'Galaxy Z Flip4', 'Galaxy Z Flip5', 'Galaxy Z Flip6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3191' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3192' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3193' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3194' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3195' AND m.brand = 'Samsung' AND m.model_name IN ('Galaxy Z Fold3', 'Galaxy Z Fold4', 'Galaxy Z Fold5', 'Galaxy Z Fold6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3196' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3197' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3198' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3199' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3200' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9A', 'Redmi 10A', 'Redmi A1', 'Redmi A2', 'Redmi A3', 'Redmi A5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3201' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3202' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3203' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3204' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3205' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi 9', 'Redmi 9C', 'Redmi 10', 'Redmi 10C', 'Redmi 12', 'Redmi 12C', 'Redmi 13C', 'Redmi 14C', 'Redmi 15C');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3206' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 8', 'Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3207' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 8', 'Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3208' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 8', 'Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3209' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 8', 'Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3210' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 8', 'Redmi Note 9', 'Redmi Note 9S', 'Redmi Note 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3211' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3212' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3213' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3214' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3215' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 11', 'Redmi Note 11S', 'Redmi Note 12', 'Redmi Note 12S', 'Redmi Note 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3216' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3217' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3218' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3219' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3220' AND m.brand = 'Xiaomi' AND m.model_name IN ('Redmi Note 14', 'Redmi Note 14 Pro', 'Redmi Note 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3221' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3222' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3223' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3224' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3225' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 11', 'Xiaomi 11 Lite', 'Xiaomi 12', 'Xiaomi 12 Lite', 'Xiaomi 12T', 'Xiaomi 13', 'Xiaomi 13 Lite', 'Xiaomi 13T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3226' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3227' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3228' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3229' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3230' AND m.brand = 'Xiaomi' AND m.model_name IN ('Xiaomi 14', 'Xiaomi 14T', 'Xiaomi 14T Pro', 'Xiaomi 15');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3231' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3232' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3233' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3234' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3235' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco X3', 'Poco X4', 'Poco X5', 'Poco X6', 'Poco X7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3236' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3237' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3238' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3239' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3240' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco M3', 'Poco M4', 'Poco M5', 'Poco M6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3241' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3242' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3243' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3244' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3245' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco F3', 'Poco F4', 'Poco F5', 'Poco F6', 'Poco F7');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3246' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3247' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3248' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3249' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3250' AND m.brand = 'Xiaomi' AND m.model_name IN ('Poco C40', 'Poco C55', 'Poco C65', 'Poco C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3251' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3252' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3253' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3254' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3255' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 10', 'Hot 11', 'Hot 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3256' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3257' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3258' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3259' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3260' AND m.brand = 'Infinix' AND m.model_name IN ('Hot 20', 'Hot 30', 'Hot 40', 'Hot 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3261' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3262' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3263' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3264' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3265' AND m.brand = 'Infinix' AND m.model_name IN ('Note 7', 'Note 8', 'Note 10', 'Note 11', 'Note 12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3266' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3267' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3268' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3269' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3270' AND m.brand = 'Infinix' AND m.model_name IN ('Note 30', 'Note 40');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3271' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3272' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3273' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3274' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3275' AND m.brand = 'Infinix' AND m.model_name IN ('Smart 5', 'Smart 6', 'Smart 7', 'Smart 8', 'Smart 9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3276' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3277' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3278' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3279' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3280' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 6', 'Spark 7', 'Spark 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3281' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3282' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3283' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3284' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3285' AND m.brand = 'Tecno' AND m.model_name IN ('Spark 9', 'Spark 10', 'Spark 20', 'Spark 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3286' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3287' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3288' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3289' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3290' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 15', 'Camon 16', 'Camon 17', 'Camon 18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3291' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3292' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3293' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3294' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3295' AND m.brand = 'Tecno' AND m.model_name IN ('Camon 19', 'Camon 20', 'Camon 30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3296' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3297' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3298' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3299' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3300' AND m.brand = 'Tecno' AND m.model_name IN ('Pova 2', 'Pova 3', 'Pova 4', 'Pova 5', 'Pova 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3301' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3302' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3303' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3304' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3305' AND m.brand = 'Tecno' AND m.model_name IN ('Pop 5', 'Pop 6', 'Pop 7', 'Pop 8');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3306' AND m.brand = 'Oppo' AND m.model_name IN ('A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3307' AND m.brand = 'Oppo' AND m.model_name IN ('A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3308' AND m.brand = 'Oppo' AND m.model_name IN ('A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3309' AND m.brand = 'Oppo' AND m.model_name IN ('A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3310' AND m.brand = 'Oppo' AND m.model_name IN ('A15', 'A16', 'A17', 'A18');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3311' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3312' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3313' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3314' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3315' AND m.brand = 'Oppo' AND m.model_name IN ('A31', 'A33', 'A53', 'A54', 'A57', 'A58');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3316' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3317' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3318' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3319' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3320' AND m.brand = 'Oppo' AND m.model_name IN ('A76', 'A77', 'A78', 'A79', 'A96', 'A98');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3321' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3322' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3323' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3324' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3325' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 2', 'Reno 3', 'Reno 4', 'Reno 5');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3326' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3327' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3328' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3329' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3330' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 6', 'Reno 7', 'Reno 8', 'Reno 8T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3331' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3332' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3333' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3334' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3335' AND m.brand = 'Oppo' AND m.model_name IN ('Reno 10', 'Reno 11', 'Reno 12', 'Reno 13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3336' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3337' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3338' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3339' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3340' AND m.brand = 'Oppo' AND m.model_name IN ('F9', 'F11', 'F19', 'F21');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3341' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3342' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3343' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3344' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3345' AND m.brand = 'Vivo' AND m.model_name IN ('Y11', 'Y12', 'Y15', 'Y16', 'Y17');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3346' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3347' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3348' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3349' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3350' AND m.brand = 'Vivo' AND m.model_name IN ('Y19', 'Y20', 'Y21', 'Y22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3351' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3352' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3353' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3354' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3355' AND m.brand = 'Vivo' AND m.model_name IN ('Y27', 'Y28', 'Y30', 'Y33', 'Y36');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3356' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3357' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3358' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3359' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3360' AND m.brand = 'Vivo' AND m.model_name IN ('Y50', 'Y51', 'Y53', 'Y55', 'Y56');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3361' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3362' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3363' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3364' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3365' AND m.brand = 'Vivo' AND m.model_name IN ('V20', 'V21', 'V23', 'V25', 'V27');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3366' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3367' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3368' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3369' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3370' AND m.brand = 'Vivo' AND m.model_name IN ('V29', 'V30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3371' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3372' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3373' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3374' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3375' AND m.brand = 'Vivo' AND m.model_name IN ('X60', 'X70', 'X80', 'X90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3376' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C15', 'C20', 'C21', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3377' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C15', 'C20', 'C21', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3378' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C15', 'C20', 'C21', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3379' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C15', 'C20', 'C21', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3380' AND m.brand = 'Realme' AND m.model_name IN ('C11', 'C15', 'C20', 'C21', 'C25');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3381' AND m.brand = 'Realme' AND m.model_name IN ('C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3382' AND m.brand = 'Realme' AND m.model_name IN ('C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3383' AND m.brand = 'Realme' AND m.model_name IN ('C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3384' AND m.brand = 'Realme' AND m.model_name IN ('C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3385' AND m.brand = 'Realme' AND m.model_name IN ('C30', 'C31', 'C33', 'C35');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3386' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3387' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3388' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3389' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3390' AND m.brand = 'Realme' AND m.model_name IN ('C51', 'C53', 'C55', 'C61');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3391' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3392' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3393' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3394' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3395' AND m.brand = 'Realme' AND m.model_name IN ('C63', 'C65', 'C67', 'C75');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3396' AND m.brand = 'Realme' AND m.model_name IN ('8', '9', '10', '11', '12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3397' AND m.brand = 'Realme' AND m.model_name IN ('8', '9', '10', '11', '12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3398' AND m.brand = 'Realme' AND m.model_name IN ('8', '9', '10', '11', '12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3399' AND m.brand = 'Realme' AND m.model_name IN ('8', '9', '10', '11', '12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3400' AND m.brand = 'Realme' AND m.model_name IN ('8', '9', '10', '11', '12');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3401' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3402' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3403' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3404' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3405' AND m.brand = 'Realme' AND m.model_name IN ('Narzo 30', 'Narzo 50', 'Narzo 60', 'Narzo 70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3406' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3407' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3408' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3409' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3410' AND m.brand = 'Huawei' AND m.model_name IN ('Y5p', 'Y6p', 'Y7a', 'Y7p', 'Y9 Prime', 'Y9s');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3411' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3412' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3413' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3414' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3415' AND m.brand = 'Huawei' AND m.model_name IN ('Nova 5T', 'Nova 7i', 'Nova 8i', 'Nova 9', 'Nova 10');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3416' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3417' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3418' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3419' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3420' AND m.brand = 'Huawei' AND m.model_name IN ('P30', 'P30 Lite', 'P40', 'P40 Lite', 'P50', 'P60');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3421' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3422' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3423' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3424' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3425' AND m.brand = 'Huawei' AND m.model_name IN ('Mate 30', 'Mate 40', 'Mate 50');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3426' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3427' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3428' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3429' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3430' AND m.brand = 'Honor' AND m.model_name IN ('X6', 'X7', 'X8', 'X9');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3431' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3432' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3433' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3434' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3435' AND m.brand = 'Honor' AND m.model_name IN ('8A', '8X', '9X', '10', '20');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3436' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3437' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3438' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3439' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3440' AND m.brand = 'Honor' AND m.model_name IN ('50', '70', '90');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3441' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3442' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3443' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3444' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3445' AND m.brand = 'Honor' AND m.model_name IN ('Magic 5', 'Magic 6');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3446' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3447' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3448' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3449' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3450' AND m.brand = 'Nokia' AND m.model_name IN ('G10', 'G20', 'G21', 'C21', 'C31');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3451' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3452' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3453' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3454' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3455' AND m.brand = 'Nokia' AND m.model_name IN ('G42', 'X20', 'X30', '8.3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3456' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3457' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3458' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3459' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3460' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G10', 'Moto G20', 'Moto G22', 'Moto G30');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3461' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3462' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3463' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3464' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3465' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G32', 'Moto G52', 'Moto G54', 'Moto G62');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3466' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3467' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3468' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3469' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3470' AND m.brand = 'Motorola' AND m.model_name IN ('Moto G72', 'Moto G82', 'Moto G84');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3471' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3472' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3473' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3474' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3475' AND m.brand = 'Motorola' AND m.model_name IN ('Moto E7', 'Moto E13', 'Moto E22');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3476' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3477' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3478' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3479' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3480' AND m.brand = 'OnePlus' AND m.model_name IN ('Nord 2', 'Nord 3', 'Nord CE 2', 'Nord CE 3');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3481' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3482' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3483' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3484' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3485' AND m.brand = 'OnePlus' AND m.model_name IN ('9', '9R', '10 Pro', '10T');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3486' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3487' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3488' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3489' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3490' AND m.brand = 'OnePlus' AND m.model_name IN ('11', '12', '12R', '13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3491' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3492' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3493' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3494' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3495' AND m.brand = 'Google' AND m.model_name IN ('Pixel 6', 'Pixel 6a', 'Pixel 7', 'Pixel 7a');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3496' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3497' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3498' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3499' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3500' AND m.brand = 'Google' AND m.model_name IN ('Pixel 8', 'Pixel 8a', 'Pixel 9', 'Pixel 9 Pro');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3501' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3502' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3503' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3504' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3505' AND m.brand = 'Nothing' AND m.model_name IN ('Phone (1)', 'Phone (2)', 'Phone (2a)');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3506' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3507' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3508' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3509' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3510' AND m.brand = 'Itel' AND m.model_name IN ('A23', 'A48', 'A56', 'P38', 'S23');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3511' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3512' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3513' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3514' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3515' AND m.brand = 'Itel' AND m.model_name IN ('Vision 1', 'Vision 2', 'A60', 'A70');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3516' AND m.brand = 'Lenovo' AND m.model_name IN ('K14 Plus', 'K13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3517' AND m.brand = 'Lenovo' AND m.model_name IN ('K14 Plus', 'K13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3518' AND m.brand = 'Lenovo' AND m.model_name IN ('K14 Plus', 'K13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3519' AND m.brand = 'Lenovo' AND m.model_name IN ('K14 Plus', 'K13');

INSERT IGNORE INTO `screen_compatibility` (`screen_id`, `model_id`)
SELECT s.id, m.id FROM `screen_protectors` s, `device_models` m
WHERE s.barcode = 'ASLYSCR3520' AND m.brand = 'Lenovo' AND m.model_name IN ('K14 Plus', 'K13');

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3001', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - زجاج مقوي 9H', 'Screen Protector iPhone 6 6s 7 8 SE - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3002', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - زجاج نانو مرن', 'Screen Protector iPhone 6 6s 7 8 SE - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3003', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - برايفسي خصوصية', 'Screen Protector iPhone 6 6s 7 8 SE - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3004', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - سيراميك', 'Screen Protector iPhone 6 6s 7 8 SE - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3005', 'qr', 'اسكرينة ايفون 6/6s/7/8/SE - مات ضد البصمة', 'Screen Protector iPhone 6 6s 7 8 SE - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3006', 'qr', 'اسكرينة ايفون بلس 5.5 - زجاج مقوي 9H', 'Screen Protector iPhone Plus 5.5 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3007', 'qr', 'اسكرينة ايفون بلس 5.5 - زجاج نانو مرن', 'Screen Protector iPhone Plus 5.5 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3008', 'qr', 'اسكرينة ايفون بلس 5.5 - برايفسي خصوصية', 'Screen Protector iPhone Plus 5.5 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3009', 'qr', 'اسكرينة ايفون بلس 5.5 - سيراميك', 'Screen Protector iPhone Plus 5.5 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3010', 'qr', 'اسكرينة ايفون بلس 5.5 - مات ضد البصمة', 'Screen Protector iPhone Plus 5.5 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3011', 'qr', 'اسكرينة ايفون X / XS / 11 برو - زجاج مقوي 9H', 'Screen Protector iPhone X XS 11 Pro - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3012', 'qr', 'اسكرينة ايفون X / XS / 11 برو - زجاج نانو مرن', 'Screen Protector iPhone X XS 11 Pro - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3013', 'qr', 'اسكرينة ايفون X / XS / 11 برو - برايفسي خصوصية', 'Screen Protector iPhone X XS 11 Pro - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3014', 'qr', 'اسكرينة ايفون X / XS / 11 برو - سيراميك', 'Screen Protector iPhone X XS 11 Pro - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3015', 'qr', 'اسكرينة ايفون X / XS / 11 برو - مات ضد البصمة', 'Screen Protector iPhone X XS 11 Pro - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3016', 'qr', 'اسكرينة ايفون XR / 11 - زجاج مقوي 9H', 'Screen Protector iPhone XR 11 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3017', 'qr', 'اسكرينة ايفون XR / 11 - زجاج نانو مرن', 'Screen Protector iPhone XR 11 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3018', 'qr', 'اسكرينة ايفون XR / 11 - برايفسي خصوصية', 'Screen Protector iPhone XR 11 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3019', 'qr', 'اسكرينة ايفون XR / 11 - سيراميك', 'Screen Protector iPhone XR 11 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3020', 'qr', 'اسكرينة ايفون XR / 11 - مات ضد البصمة', 'Screen Protector iPhone XR 11 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3021', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone XS Max 11 Pro Max - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3022', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone XS Max 11 Pro Max - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3023', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone XS Max 11 Pro Max - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3024', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - سيراميك', 'Screen Protector iPhone XS Max 11 Pro Max - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3025', 'qr', 'اسكرينة ايفون XS ماكس / 11 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone XS Max 11 Pro Max - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3026', 'qr', 'اسكرينة ايفون 12 / 12 برو - زجاج مقوي 9H', 'Screen Protector iPhone 12 12 Pro - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3027', 'qr', 'اسكرينة ايفون 12 / 12 برو - زجاج نانو مرن', 'Screen Protector iPhone 12 12 Pro - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3028', 'qr', 'اسكرينة ايفون 12 / 12 برو - برايفسي خصوصية', 'Screen Protector iPhone 12 12 Pro - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3029', 'qr', 'اسكرينة ايفون 12 / 12 برو - سيراميك', 'Screen Protector iPhone 12 12 Pro - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3030', 'qr', 'اسكرينة ايفون 12 / 12 برو - مات ضد البصمة', 'Screen Protector iPhone 12 12 Pro - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3031', 'qr', 'اسكرينة ايفون ميني 12/13 - زجاج مقوي 9H', 'Screen Protector iPhone 12 13 mini - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3032', 'qr', 'اسكرينة ايفون ميني 12/13 - زجاج نانو مرن', 'Screen Protector iPhone 12 13 mini - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3033', 'qr', 'اسكرينة ايفون ميني 12/13 - برايفسي خصوصية', 'Screen Protector iPhone 12 13 mini - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3034', 'qr', 'اسكرينة ايفون ميني 12/13 - سيراميك', 'Screen Protector iPhone 12 13 mini - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3035', 'qr', 'اسكرينة ايفون ميني 12/13 - مات ضد البصمة', 'Screen Protector iPhone 12 13 mini - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3036', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - زجاج مقوي 9H', 'Screen Protector iPhone 13 13 Pro 14 16e - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3037', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - زجاج نانو مرن', 'Screen Protector iPhone 13 13 Pro 14 16e - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3038', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - برايفسي خصوصية', 'Screen Protector iPhone 13 13 Pro 14 16e - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3039', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - سيراميك', 'Screen Protector iPhone 13 13 Pro 14 16e - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3040', 'qr', 'اسكرينة ايفون 13 / 13 برو / 14 / 16e - مات ضد البصمة', 'Screen Protector iPhone 13 13 Pro 14 16e - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3041', 'qr', 'اسكرينة ايفون 12 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone 12 Pro Max - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3042', 'qr', 'اسكرينة ايفون 12 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone 12 Pro Max - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3043', 'qr', 'اسكرينة ايفون 12 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone 12 Pro Max - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3044', 'qr', 'اسكرينة ايفون 12 برو ماكس - سيراميك', 'Screen Protector iPhone 12 Pro Max - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3045', 'qr', 'اسكرينة ايفون 12 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone 12 Pro Max - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3046', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 13 Pro Max 14 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3047', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - زجاج نانو مرن', 'Screen Protector iPhone 13 Pro Max 14 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3048', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - برايفسي خصوصية', 'Screen Protector iPhone 13 Pro Max 14 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3049', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - سيراميك', 'Screen Protector iPhone 13 Pro Max 14 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3050', 'qr', 'اسكرينة ايفون 13 برو ماكس / 14 بلس - مات ضد البصمة', 'Screen Protector iPhone 13 Pro Max 14 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3051', 'qr', 'اسكرينة ايفون 14 برو / 15 - زجاج مقوي 9H', 'Screen Protector iPhone 14 Pro 15 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3052', 'qr', 'اسكرينة ايفون 14 برو / 15 - زجاج نانو مرن', 'Screen Protector iPhone 14 Pro 15 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3053', 'qr', 'اسكرينة ايفون 14 برو / 15 - برايفسي خصوصية', 'Screen Protector iPhone 14 Pro 15 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3054', 'qr', 'اسكرينة ايفون 14 برو / 15 - سيراميك', 'Screen Protector iPhone 14 Pro 15 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3055', 'qr', 'اسكرينة ايفون 14 برو / 15 - مات ضد البصمة', 'Screen Protector iPhone 14 Pro 15 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3056', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 14 Pro Max 15 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3057', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - زجاج نانو مرن', 'Screen Protector iPhone 14 Pro Max 15 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3058', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - برايفسي خصوصية', 'Screen Protector iPhone 14 Pro Max 15 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3059', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - سيراميك', 'Screen Protector iPhone 14 Pro Max 15 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3060', 'qr', 'اسكرينة ايفون 14 برو ماكس / 15 بلس - مات ضد البصمة', 'Screen Protector iPhone 14 Pro Max 15 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3061', 'qr', 'اسكرينة ايفون 15 برو / 16 - زجاج مقوي 9H', 'Screen Protector iPhone 15 Pro 16 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3062', 'qr', 'اسكرينة ايفون 15 برو / 16 - زجاج نانو مرن', 'Screen Protector iPhone 15 Pro 16 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3063', 'qr', 'اسكرينة ايفون 15 برو / 16 - برايفسي خصوصية', 'Screen Protector iPhone 15 Pro 16 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3064', 'qr', 'اسكرينة ايفون 15 برو / 16 - سيراميك', 'Screen Protector iPhone 15 Pro 16 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3065', 'qr', 'اسكرينة ايفون 15 برو / 16 - مات ضد البصمة', 'Screen Protector iPhone 15 Pro 16 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3066', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - زجاج مقوي 9H', 'Screen Protector iPhone 15 Pro Max 16 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3067', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - زجاج نانو مرن', 'Screen Protector iPhone 15 Pro Max 16 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3068', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - برايفسي خصوصية', 'Screen Protector iPhone 15 Pro Max 16 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3069', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - سيراميك', 'Screen Protector iPhone 15 Pro Max 16 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3070', 'qr', 'اسكرينة ايفون 15 برو ماكس / 16 بلس - مات ضد البصمة', 'Screen Protector iPhone 15 Pro Max 16 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3071', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - زجاج مقوي 9H', 'Screen Protector iPhone 16 Pro 17 17 Pro - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3072', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - زجاج نانو مرن', 'Screen Protector iPhone 16 Pro 17 17 Pro - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3073', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - برايفسي خصوصية', 'Screen Protector iPhone 16 Pro 17 17 Pro - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3074', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - سيراميك', 'Screen Protector iPhone 16 Pro 17 17 Pro - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3075', 'qr', 'اسكرينة ايفون 16 برو / 17 / 17 برو - مات ضد البصمة', 'Screen Protector iPhone 16 Pro 17 17 Pro - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3076', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - زجاج مقوي 9H', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3077', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - زجاج نانو مرن', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3078', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - برايفسي خصوصية', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3079', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - سيراميك', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3080', 'qr', 'اسكرينة ايفون 16 برو ماكس / 17 برو ماكس - مات ضد البصمة', 'Screen Protector iPhone 16 Pro Max 17 Pro Max - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3081', 'qr', 'اسكرينة ايفون 17 اير - زجاج مقوي 9H', 'Screen Protector iPhone 17 Air - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3082', 'qr', 'اسكرينة ايفون 17 اير - زجاج نانو مرن', 'Screen Protector iPhone 17 Air - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3083', 'qr', 'اسكرينة ايفون 17 اير - برايفسي خصوصية', 'Screen Protector iPhone 17 Air - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3084', 'qr', 'اسكرينة ايفون 17 اير - سيراميك', 'Screen Protector iPhone 17 Air - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3085', 'qr', 'اسكرينة ايفون 17 اير - مات ضد البصمة', 'Screen Protector iPhone 17 Air - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3086', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - زجاج مقوي 9H', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3087', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - زجاج نانو مرن', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3088', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - برايفسي خصوصية', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3089', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - سيراميك', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3090', 'qr', 'اسكرينة سامسونج A02/A03/A04/A05/A06 - مات ضد البصمة', 'Screen Protector Samsung A02 A03 A04 A05 A06 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3091', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - زجاج مقوي 9H', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3092', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - زجاج نانو مرن', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3093', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - برايفسي خصوصية', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3094', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - سيراميك', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3095', 'qr', 'اسكرينة سامسونج A12/A13/A14/A15/A16 - مات ضد البصمة', 'Screen Protector Samsung A12 A13 A14 A15 A16 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3096', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - زجاج مقوي 9H', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3097', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - زجاج نانو مرن', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3098', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - برايفسي خصوصية', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3099', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - سيراميك', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3100', 'qr', 'اسكرينة سامسونج A21s/A22/A23/A24/A25 - مات ضد البصمة', 'Screen Protector Samsung A21s A22 A23 A24 A25 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3101', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - زجاج مقوي 9H', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3102', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - زجاج نانو مرن', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3103', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - برايفسي خصوصية', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3104', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - سيراميك', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3105', 'qr', 'اسكرينة سامسونج A31/A32/A33/A34/A35 - مات ضد البصمة', 'Screen Protector Samsung A31 A32 A33 A34 A35 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3106', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - زجاج مقوي 9H', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3107', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - زجاج نانو مرن', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3108', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - برايفسي خصوصية', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3109', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - سيراميك', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3110', 'qr', 'اسكرينة سامسونج A51/A52/A53/A54/A55 - مات ضد البصمة', 'Screen Protector Samsung A51 A52 A53 A54 A55 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3111', 'qr', 'اسكرينة سامسونج A71/A72/A73 - زجاج مقوي 9H', 'Screen Protector Samsung A71 A72 A73 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3112', 'qr', 'اسكرينة سامسونج A71/A72/A73 - زجاج نانو مرن', 'Screen Protector Samsung A71 A72 A73 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3113', 'qr', 'اسكرينة سامسونج A71/A72/A73 - برايفسي خصوصية', 'Screen Protector Samsung A71 A72 A73 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3114', 'qr', 'اسكرينة سامسونج A71/A72/A73 - سيراميك', 'Screen Protector Samsung A71 A72 A73 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3115', 'qr', 'اسكرينة سامسونج A71/A72/A73 - مات ضد البصمة', 'Screen Protector Samsung A71 A72 A73 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3116', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - زجاج مقوي 9H', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3117', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - زجاج نانو مرن', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3118', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - برايفسي خصوصية', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3119', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - سيراميك', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3120', 'qr', 'اسكرينة سامسونج M12/M22/M32/M33/M52 - مات ضد البصمة', 'Screen Protector Samsung M12 M22 M32 M33 M52 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3121', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S20 S20 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3122', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S20 S20 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3123', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S20 S20 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3124', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - سيراميك', 'Screen Protector Samsung S20 S20 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3125', 'qr', 'اسكرينة سامسونج S20 / S20 بلاس - مات ضد البصمة', 'Screen Protector Samsung S20 S20 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3126', 'qr', 'اسكرينة سامسونج S20 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S20 Ultra - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3127', 'qr', 'اسكرينة سامسونج S20 الترا - زجاج نانو مرن', 'Screen Protector Samsung S20 Ultra - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3128', 'qr', 'اسكرينة سامسونج S20 الترا - برايفسي خصوصية', 'Screen Protector Samsung S20 Ultra - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3129', 'qr', 'اسكرينة سامسونج S20 الترا - سيراميك', 'Screen Protector Samsung S20 Ultra - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3130', 'qr', 'اسكرينة سامسونج S20 الترا - مات ضد البصمة', 'Screen Protector Samsung S20 Ultra - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3131', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S21 S21 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3132', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S21 S21 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3133', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S21 S21 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3134', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - سيراميك', 'Screen Protector Samsung S21 S21 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3135', 'qr', 'اسكرينة سامسونج S21 / S21 بلاس - مات ضد البصمة', 'Screen Protector Samsung S21 S21 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3136', 'qr', 'اسكرينة سامسونج S21 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S21 Ultra - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3137', 'qr', 'اسكرينة سامسونج S21 الترا - زجاج نانو مرن', 'Screen Protector Samsung S21 Ultra - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3138', 'qr', 'اسكرينة سامسونج S21 الترا - برايفسي خصوصية', 'Screen Protector Samsung S21 Ultra - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3139', 'qr', 'اسكرينة سامسونج S21 الترا - سيراميك', 'Screen Protector Samsung S21 Ultra - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3140', 'qr', 'اسكرينة سامسونج S21 الترا - مات ضد البصمة', 'Screen Protector Samsung S21 Ultra - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3141', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S22 S22 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3142', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S22 S22 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3143', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S22 S22 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3144', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - سيراميك', 'Screen Protector Samsung S22 S22 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3145', 'qr', 'اسكرينة سامسونج S22 / S22 بلاس - مات ضد البصمة', 'Screen Protector Samsung S22 S22 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3146', 'qr', 'اسكرينة سامسونج S22 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S22 Ultra - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3147', 'qr', 'اسكرينة سامسونج S22 الترا - زجاج نانو مرن', 'Screen Protector Samsung S22 Ultra - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3148', 'qr', 'اسكرينة سامسونج S22 الترا - برايفسي خصوصية', 'Screen Protector Samsung S22 Ultra - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3149', 'qr', 'اسكرينة سامسونج S22 الترا - سيراميك', 'Screen Protector Samsung S22 Ultra - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3150', 'qr', 'اسكرينة سامسونج S22 الترا - مات ضد البصمة', 'Screen Protector Samsung S22 Ultra - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3151', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - زجاج مقوي 9H', 'Screen Protector Samsung S23 S23 Plus FE - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3152', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - زجاج نانو مرن', 'Screen Protector Samsung S23 S23 Plus FE - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3153', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - برايفسي خصوصية', 'Screen Protector Samsung S23 S23 Plus FE - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3154', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - سيراميك', 'Screen Protector Samsung S23 S23 Plus FE - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3155', 'qr', 'اسكرينة سامسونج S23 / S23 بلاس / FE - مات ضد البصمة', 'Screen Protector Samsung S23 S23 Plus FE - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3156', 'qr', 'اسكرينة سامسونج S23 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S23 Ultra - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3157', 'qr', 'اسكرينة سامسونج S23 الترا - زجاج نانو مرن', 'Screen Protector Samsung S23 Ultra - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3158', 'qr', 'اسكرينة سامسونج S23 الترا - برايفسي خصوصية', 'Screen Protector Samsung S23 Ultra - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3159', 'qr', 'اسكرينة سامسونج S23 الترا - سيراميك', 'Screen Protector Samsung S23 Ultra - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3160', 'qr', 'اسكرينة سامسونج S23 الترا - مات ضد البصمة', 'Screen Protector Samsung S23 Ultra - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3161', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - زجاج مقوي 9H', 'Screen Protector Samsung S24 S24 Plus FE - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3162', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - زجاج نانو مرن', 'Screen Protector Samsung S24 S24 Plus FE - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3163', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - برايفسي خصوصية', 'Screen Protector Samsung S24 S24 Plus FE - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3164', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - سيراميك', 'Screen Protector Samsung S24 S24 Plus FE - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3165', 'qr', 'اسكرينة سامسونج S24 / S24 بلاس / FE - مات ضد البصمة', 'Screen Protector Samsung S24 S24 Plus FE - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3166', 'qr', 'اسكرينة سامسونج S24 الترا - زجاج مقوي 9H', 'Screen Protector Samsung S24 Ultra - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3167', 'qr', 'اسكرينة سامسونج S24 الترا - زجاج نانو مرن', 'Screen Protector Samsung S24 Ultra - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3168', 'qr', 'اسكرينة سامسونج S24 الترا - برايفسي خصوصية', 'Screen Protector Samsung S24 Ultra - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3169', 'qr', 'اسكرينة سامسونج S24 الترا - سيراميك', 'Screen Protector Samsung S24 Ultra - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3170', 'qr', 'اسكرينة سامسونج S24 الترا - مات ضد البصمة', 'Screen Protector Samsung S24 Ultra - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3171', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - زجاج مقوي 9H', 'Screen Protector Samsung S25 S25 Plus - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3172', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - زجاج نانو مرن', 'Screen Protector Samsung S25 S25 Plus - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3173', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - برايفسي خصوصية', 'Screen Protector Samsung S25 S25 Plus - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3174', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - سيراميك', 'Screen Protector Samsung S25 S25 Plus - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3175', 'qr', 'اسكرينة سامسونج S25 / S25 بلاس - مات ضد البصمة', 'Screen Protector Samsung S25 S25 Plus - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3176', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - زجاج مقوي 9H', 'Screen Protector Samsung S25 Ultra Edge - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3177', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - زجاج نانو مرن', 'Screen Protector Samsung S25 Ultra Edge - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3178', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - برايفسي خصوصية', 'Screen Protector Samsung S25 Ultra Edge - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3179', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - سيراميك', 'Screen Protector Samsung S25 Ultra Edge - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3180', 'qr', 'اسكرينة سامسونج S25 الترا / ايدج - مات ضد البصمة', 'Screen Protector Samsung S25 Ultra Edge - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3181', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - زجاج مقوي 9H', 'Screen Protector Samsung Note 10 20 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3182', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - زجاج نانو مرن', 'Screen Protector Samsung Note 10 20 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3183', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - برايفسي خصوصية', 'Screen Protector Samsung Note 10 20 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3184', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - سيراميك', 'Screen Protector Samsung Note 10 20 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3185', 'qr', 'اسكرينة سامسونج نوت 10 / 20 - مات ضد البصمة', 'Screen Protector Samsung Note 10 20 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3186', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Samsung Z Flip 3 4 5 6 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3187', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - زجاج نانو مرن', 'Screen Protector Samsung Z Flip 3 4 5 6 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3188', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - برايفسي خصوصية', 'Screen Protector Samsung Z Flip 3 4 5 6 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3189', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - سيراميك', 'Screen Protector Samsung Z Flip 3 4 5 6 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3190', 'qr', 'اسكرينة سامسونج Z فليب 3/4/5/6 - مات ضد البصمة', 'Screen Protector Samsung Z Flip 3 4 5 6 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3191', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Samsung Z Fold 3 4 5 6 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3192', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - زجاج نانو مرن', 'Screen Protector Samsung Z Fold 3 4 5 6 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3193', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - برايفسي خصوصية', 'Screen Protector Samsung Z Fold 3 4 5 6 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3194', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - سيراميك', 'Screen Protector Samsung Z Fold 3 4 5 6 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3195', 'qr', 'اسكرينة سامسونج Z فولد 3/4/5/6 - مات ضد البصمة', 'Screen Protector Samsung Z Fold 3 4 5 6 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3196', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - زجاج مقوي 9H', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3197', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - زجاج نانو مرن', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3198', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - برايفسي خصوصية', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3199', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - سيراميك', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3200', 'qr', 'اسكرينة ريدمي 9A/10A/A1/A2/A3/A5 - مات ضد البصمة', 'Screen Protector Redmi 9A 10A A1 A2 A3 A5 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3201', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - زجاج مقوي 9H', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3202', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - زجاج نانو مرن', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3203', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - برايفسي خصوصية', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3204', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - سيراميك', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3205', 'qr', 'اسكرينة ريدمي 9/10/12C/13C/14C/15C - مات ضد البصمة', 'Screen Protector Redmi 9 10 12C 13C 14C 15C - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3206', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - زجاج مقوي 9H', 'Screen Protector Redmi Note 8 9 9S 10 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3207', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - زجاج نانو مرن', 'Screen Protector Redmi Note 8 9 9S 10 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3208', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - برايفسي خصوصية', 'Screen Protector Redmi Note 8 9 9S 10 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3209', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - سيراميك', 'Screen Protector Redmi Note 8 9 9S 10 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3210', 'qr', 'اسكرينة ريدمي نوت 8/9/9S/10 - مات ضد البصمة', 'Screen Protector Redmi Note 8 9 9S 10 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3211', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - زجاج مقوي 9H', 'Screen Protector Redmi Note 11 12 13 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3212', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - زجاج نانو مرن', 'Screen Protector Redmi Note 11 12 13 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3213', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - برايفسي خصوصية', 'Screen Protector Redmi Note 11 12 13 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3214', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - سيراميك', 'Screen Protector Redmi Note 11 12 13 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3215', 'qr', 'اسكرينة ريدمي نوت 11/12/13 - مات ضد البصمة', 'Screen Protector Redmi Note 11 12 13 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3216', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - زجاج مقوي 9H', 'Screen Protector Redmi Note 14 15 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3217', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - زجاج نانو مرن', 'Screen Protector Redmi Note 14 15 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3218', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - برايفسي خصوصية', 'Screen Protector Redmi Note 14 15 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3219', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - سيراميك', 'Screen Protector Redmi Note 14 15 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3220', 'qr', 'اسكرينة ريدمي نوت 14 / 15 - مات ضد البصمة', 'Screen Protector Redmi Note 14 15 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3221', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - زجاج مقوي 9H', 'Screen Protector Xiaomi 11 12 12T 13 13T - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3222', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - زجاج نانو مرن', 'Screen Protector Xiaomi 11 12 12T 13 13T - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3223', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - برايفسي خصوصية', 'Screen Protector Xiaomi 11 12 12T 13 13T - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3224', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - سيراميك', 'Screen Protector Xiaomi 11 12 12T 13 13T - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3225', 'qr', 'اسكرينة شاومي 11/12/12T/13/13T - مات ضد البصمة', 'Screen Protector Xiaomi 11 12 12T 13 13T - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3226', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - زجاج مقوي 9H', 'Screen Protector Xiaomi 14 14T 15 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3227', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - زجاج نانو مرن', 'Screen Protector Xiaomi 14 14T 15 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3228', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - برايفسي خصوصية', 'Screen Protector Xiaomi 14 14T 15 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3229', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - سيراميك', 'Screen Protector Xiaomi 14 14T 15 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3230', 'qr', 'اسكرينة شاومي 14 / 14T / 15 - مات ضد البصمة', 'Screen Protector Xiaomi 14 14T 15 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3231', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - زجاج مقوي 9H', 'Screen Protector Poco X3 X4 X5 X6 X7 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3232', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - زجاج نانو مرن', 'Screen Protector Poco X3 X4 X5 X6 X7 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3233', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - برايفسي خصوصية', 'Screen Protector Poco X3 X4 X5 X6 X7 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3234', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - سيراميك', 'Screen Protector Poco X3 X4 X5 X6 X7 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3235', 'qr', 'اسكرينة بوكو X3/X4/X5/X6/X7 - مات ضد البصمة', 'Screen Protector Poco X3 X4 X5 X6 X7 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3236', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - زجاج مقوي 9H', 'Screen Protector Poco M3 M4 M5 M6 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3237', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - زجاج نانو مرن', 'Screen Protector Poco M3 M4 M5 M6 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3238', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - برايفسي خصوصية', 'Screen Protector Poco M3 M4 M5 M6 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3239', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - سيراميك', 'Screen Protector Poco M3 M4 M5 M6 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3240', 'qr', 'اسكرينة بوكو M3/M4/M5/M6 - مات ضد البصمة', 'Screen Protector Poco M3 M4 M5 M6 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3241', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - زجاج مقوي 9H', 'Screen Protector Poco F3 F4 F5 F6 F7 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3242', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - زجاج نانو مرن', 'Screen Protector Poco F3 F4 F5 F6 F7 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3243', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - برايفسي خصوصية', 'Screen Protector Poco F3 F4 F5 F6 F7 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3244', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - سيراميك', 'Screen Protector Poco F3 F4 F5 F6 F7 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3245', 'qr', 'اسكرينة بوكو F3/F4/F5/F6/F7 - مات ضد البصمة', 'Screen Protector Poco F3 F4 F5 F6 F7 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3246', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - زجاج مقوي 9H', 'Screen Protector Poco C40 C55 C65 C75 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3247', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - زجاج نانو مرن', 'Screen Protector Poco C40 C55 C65 C75 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3248', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - برايفسي خصوصية', 'Screen Protector Poco C40 C55 C65 C75 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3249', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - سيراميك', 'Screen Protector Poco C40 C55 C65 C75 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3250', 'qr', 'اسكرينة بوكو C40/C55/C65/C75 - مات ضد البصمة', 'Screen Protector Poco C40 C55 C65 C75 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3251', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - زجاج مقوي 9H', 'Screen Protector Infinix Hot 10 11 12 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3252', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - زجاج نانو مرن', 'Screen Protector Infinix Hot 10 11 12 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3253', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - برايفسي خصوصية', 'Screen Protector Infinix Hot 10 11 12 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3254', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - سيراميك', 'Screen Protector Infinix Hot 10 11 12 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3255', 'qr', 'اسكرينة انفينكس هوت 10/11/12 - مات ضد البصمة', 'Screen Protector Infinix Hot 10 11 12 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3256', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - زجاج مقوي 9H', 'Screen Protector Infinix Hot 20 30 40 50 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3257', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - زجاج نانو مرن', 'Screen Protector Infinix Hot 20 30 40 50 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3258', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - برايفسي خصوصية', 'Screen Protector Infinix Hot 20 30 40 50 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3259', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - سيراميك', 'Screen Protector Infinix Hot 20 30 40 50 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3260', 'qr', 'اسكرينة انفينكس هوت 20/30/40/50 - مات ضد البصمة', 'Screen Protector Infinix Hot 20 30 40 50 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3261', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - زجاج مقوي 9H', 'Screen Protector Infinix Note 7 8 10 11 12 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3262', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - زجاج نانو مرن', 'Screen Protector Infinix Note 7 8 10 11 12 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3263', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - برايفسي خصوصية', 'Screen Protector Infinix Note 7 8 10 11 12 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3264', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - سيراميك', 'Screen Protector Infinix Note 7 8 10 11 12 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3265', 'qr', 'اسكرينة انفينكس نوت 7/8/10/11/12 - مات ضد البصمة', 'Screen Protector Infinix Note 7 8 10 11 12 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3266', 'qr', 'اسكرينة انفينكس نوت 30/40 - زجاج مقوي 9H', 'Screen Protector Infinix Note 30 40 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3267', 'qr', 'اسكرينة انفينكس نوت 30/40 - زجاج نانو مرن', 'Screen Protector Infinix Note 30 40 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3268', 'qr', 'اسكرينة انفينكس نوت 30/40 - برايفسي خصوصية', 'Screen Protector Infinix Note 30 40 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3269', 'qr', 'اسكرينة انفينكس نوت 30/40 - سيراميك', 'Screen Protector Infinix Note 30 40 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3270', 'qr', 'اسكرينة انفينكس نوت 30/40 - مات ضد البصمة', 'Screen Protector Infinix Note 30 40 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3271', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - زجاج مقوي 9H', 'Screen Protector Infinix Smart 5 6 7 8 9 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3272', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - زجاج نانو مرن', 'Screen Protector Infinix Smart 5 6 7 8 9 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3273', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - برايفسي خصوصية', 'Screen Protector Infinix Smart 5 6 7 8 9 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3274', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - سيراميك', 'Screen Protector Infinix Smart 5 6 7 8 9 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3275', 'qr', 'اسكرينة انفينكس سمارت 5/6/7/8/9 - مات ضد البصمة', 'Screen Protector Infinix Smart 5 6 7 8 9 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3276', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - زجاج مقوي 9H', 'Screen Protector Tecno Spark 6 7 8 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3277', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - زجاج نانو مرن', 'Screen Protector Tecno Spark 6 7 8 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3278', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - برايفسي خصوصية', 'Screen Protector Tecno Spark 6 7 8 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3279', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - سيراميك', 'Screen Protector Tecno Spark 6 7 8 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3280', 'qr', 'اسكرينة تكنو سبارك 6/7/8 - مات ضد البصمة', 'Screen Protector Tecno Spark 6 7 8 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3281', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - زجاج مقوي 9H', 'Screen Protector Tecno Spark 9 10 20 30 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3282', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - زجاج نانو مرن', 'Screen Protector Tecno Spark 9 10 20 30 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3283', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - برايفسي خصوصية', 'Screen Protector Tecno Spark 9 10 20 30 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3284', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - سيراميك', 'Screen Protector Tecno Spark 9 10 20 30 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3285', 'qr', 'اسكرينة تكنو سبارك 9/10/20/30 - مات ضد البصمة', 'Screen Protector Tecno Spark 9 10 20 30 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3286', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - زجاج مقوي 9H', 'Screen Protector Tecno Camon 15 16 17 18 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3287', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - زجاج نانو مرن', 'Screen Protector Tecno Camon 15 16 17 18 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3288', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - برايفسي خصوصية', 'Screen Protector Tecno Camon 15 16 17 18 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3289', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - سيراميك', 'Screen Protector Tecno Camon 15 16 17 18 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3290', 'qr', 'اسكرينة تكنو كامون 15/16/17/18 - مات ضد البصمة', 'Screen Protector Tecno Camon 15 16 17 18 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3291', 'qr', 'اسكرينة تكنو كامون 19/20/30 - زجاج مقوي 9H', 'Screen Protector Tecno Camon 19 20 30 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3292', 'qr', 'اسكرينة تكنو كامون 19/20/30 - زجاج نانو مرن', 'Screen Protector Tecno Camon 19 20 30 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3293', 'qr', 'اسكرينة تكنو كامون 19/20/30 - برايفسي خصوصية', 'Screen Protector Tecno Camon 19 20 30 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3294', 'qr', 'اسكرينة تكنو كامون 19/20/30 - سيراميك', 'Screen Protector Tecno Camon 19 20 30 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3295', 'qr', 'اسكرينة تكنو كامون 19/20/30 - مات ضد البصمة', 'Screen Protector Tecno Camon 19 20 30 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3296', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - زجاج مقوي 9H', 'Screen Protector Tecno Pova 2 3 4 5 6 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3297', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - زجاج نانو مرن', 'Screen Protector Tecno Pova 2 3 4 5 6 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3298', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - برايفسي خصوصية', 'Screen Protector Tecno Pova 2 3 4 5 6 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3299', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - سيراميك', 'Screen Protector Tecno Pova 2 3 4 5 6 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3300', 'qr', 'اسكرينة تكنو بوفا 2/3/4/5/6 - مات ضد البصمة', 'Screen Protector Tecno Pova 2 3 4 5 6 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3301', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - زجاج مقوي 9H', 'Screen Protector Tecno Pop 5 6 7 8 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3302', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - زجاج نانو مرن', 'Screen Protector Tecno Pop 5 6 7 8 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3303', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - برايفسي خصوصية', 'Screen Protector Tecno Pop 5 6 7 8 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3304', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - سيراميك', 'Screen Protector Tecno Pop 5 6 7 8 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3305', 'qr', 'اسكرينة تكنو بوب 5/6/7/8 - مات ضد البصمة', 'Screen Protector Tecno Pop 5 6 7 8 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3306', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - زجاج مقوي 9H', 'Screen Protector Oppo A15 A16 A17 A18 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3307', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - زجاج نانو مرن', 'Screen Protector Oppo A15 A16 A17 A18 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3308', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - برايفسي خصوصية', 'Screen Protector Oppo A15 A16 A17 A18 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3309', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - سيراميك', 'Screen Protector Oppo A15 A16 A17 A18 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3310', 'qr', 'اسكرينة اوبو A15/A16/A17/A18 - مات ضد البصمة', 'Screen Protector Oppo A15 A16 A17 A18 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3311', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - زجاج مقوي 9H', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3312', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - زجاج نانو مرن', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3313', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - برايفسي خصوصية', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3314', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - سيراميك', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3315', 'qr', 'اسكرينة اوبو A31/A33/A53/A54/A57 - مات ضد البصمة', 'Screen Protector Oppo A31 A33 A53 A54 A57 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3316', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - زجاج مقوي 9H', 'Screen Protector Oppo A76 A77 A78 A79 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3317', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - زجاج نانو مرن', 'Screen Protector Oppo A76 A77 A78 A79 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3318', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - برايفسي خصوصية', 'Screen Protector Oppo A76 A77 A78 A79 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3319', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - سيراميك', 'Screen Protector Oppo A76 A77 A78 A79 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3320', 'qr', 'اسكرينة اوبو A76/A77/A78/A79 - مات ضد البصمة', 'Screen Protector Oppo A76 A77 A78 A79 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3321', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - زجاج مقوي 9H', 'Screen Protector Oppo Reno 2 3 4 5 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3322', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - زجاج نانو مرن', 'Screen Protector Oppo Reno 2 3 4 5 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3323', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - برايفسي خصوصية', 'Screen Protector Oppo Reno 2 3 4 5 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3324', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - سيراميك', 'Screen Protector Oppo Reno 2 3 4 5 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3325', 'qr', 'اسكرينة اوبو رينو 2/3/4/5 - مات ضد البصمة', 'Screen Protector Oppo Reno 2 3 4 5 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3326', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - زجاج مقوي 9H', 'Screen Protector Oppo Reno 6 7 8 8T - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3327', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - زجاج نانو مرن', 'Screen Protector Oppo Reno 6 7 8 8T - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3328', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - برايفسي خصوصية', 'Screen Protector Oppo Reno 6 7 8 8T - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3329', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - سيراميك', 'Screen Protector Oppo Reno 6 7 8 8T - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3330', 'qr', 'اسكرينة اوبو رينو 6/7/8/8T - مات ضد البصمة', 'Screen Protector Oppo Reno 6 7 8 8T - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3331', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - زجاج مقوي 9H', 'Screen Protector Oppo Reno 10 11 12 13 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3332', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - زجاج نانو مرن', 'Screen Protector Oppo Reno 10 11 12 13 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3333', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - برايفسي خصوصية', 'Screen Protector Oppo Reno 10 11 12 13 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3334', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - سيراميك', 'Screen Protector Oppo Reno 10 11 12 13 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3335', 'qr', 'اسكرينة اوبو رينو 10/11/12/13 - مات ضد البصمة', 'Screen Protector Oppo Reno 10 11 12 13 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3336', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - زجاج مقوي 9H', 'Screen Protector Oppo F9 F11 F19 F21 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3337', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - زجاج نانو مرن', 'Screen Protector Oppo F9 F11 F19 F21 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3338', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - برايفسي خصوصية', 'Screen Protector Oppo F9 F11 F19 F21 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3339', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - سيراميك', 'Screen Protector Oppo F9 F11 F19 F21 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3340', 'qr', 'اسكرينة اوبو F9/F11/F19/F21 - مات ضد البصمة', 'Screen Protector Oppo F9 F11 F19 F21 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3341', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - زجاج مقوي 9H', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3342', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - زجاج نانو مرن', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3343', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - برايفسي خصوصية', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3344', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - سيراميك', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3345', 'qr', 'اسكرينة فيفو Y11/Y12/Y15/Y16/Y17 - مات ضد البصمة', 'Screen Protector Vivo Y11 Y12 Y15 Y16 Y17 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3346', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - زجاج مقوي 9H', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3347', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - زجاج نانو مرن', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3348', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - برايفسي خصوصية', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3349', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - سيراميك', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3350', 'qr', 'اسكرينة فيفو Y19/Y20/Y21/Y22 - مات ضد البصمة', 'Screen Protector Vivo Y19 Y20 Y21 Y22 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3351', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - زجاج مقوي 9H', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3352', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - زجاج نانو مرن', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3353', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - برايفسي خصوصية', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3354', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - سيراميك', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3355', 'qr', 'اسكرينة فيفو Y27/Y28/Y30/Y33/Y36 - مات ضد البصمة', 'Screen Protector Vivo Y27 Y28 Y30 Y33 Y36 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3356', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - زجاج مقوي 9H', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3357', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - زجاج نانو مرن', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3358', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - برايفسي خصوصية', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3359', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - سيراميك', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3360', 'qr', 'اسكرينة فيفو Y50/Y51/Y53/Y55/Y56 - مات ضد البصمة', 'Screen Protector Vivo Y50 Y51 Y53 Y55 Y56 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3361', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - زجاج مقوي 9H', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3362', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - زجاج نانو مرن', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3363', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - برايفسي خصوصية', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3364', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - سيراميك', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3365', 'qr', 'اسكرينة فيفو V20/V21/V23/V25/V27 - مات ضد البصمة', 'Screen Protector Vivo V20 V21 V23 V25 V27 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3366', 'qr', 'اسكرينة فيفو V29/V30 - زجاج مقوي 9H', 'Screen Protector Vivo V29 V30 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3367', 'qr', 'اسكرينة فيفو V29/V30 - زجاج نانو مرن', 'Screen Protector Vivo V29 V30 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3368', 'qr', 'اسكرينة فيفو V29/V30 - برايفسي خصوصية', 'Screen Protector Vivo V29 V30 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3369', 'qr', 'اسكرينة فيفو V29/V30 - سيراميك', 'Screen Protector Vivo V29 V30 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3370', 'qr', 'اسكرينة فيفو V29/V30 - مات ضد البصمة', 'Screen Protector Vivo V29 V30 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3371', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - زجاج مقوي 9H', 'Screen Protector Vivo X60 X70 X80 X90 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3372', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - زجاج نانو مرن', 'Screen Protector Vivo X60 X70 X80 X90 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3373', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - برايفسي خصوصية', 'Screen Protector Vivo X60 X70 X80 X90 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3374', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - سيراميك', 'Screen Protector Vivo X60 X70 X80 X90 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3375', 'qr', 'اسكرينة فيفو X60/X70/X80/X90 - مات ضد البصمة', 'Screen Protector Vivo X60 X70 X80 X90 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3376', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - زجاج مقوي 9H', 'Screen Protector Realme C11 C15 C20 C21 C25 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3377', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - زجاج نانو مرن', 'Screen Protector Realme C11 C15 C20 C21 C25 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3378', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - برايفسي خصوصية', 'Screen Protector Realme C11 C15 C20 C21 C25 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3379', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - سيراميك', 'Screen Protector Realme C11 C15 C20 C21 C25 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3380', 'qr', 'اسكرينة ريلمي C11/C15/C20/C21/C25 - مات ضد البصمة', 'Screen Protector Realme C11 C15 C20 C21 C25 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3381', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - زجاج مقوي 9H', 'Screen Protector Realme C30 C31 C33 C35 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3382', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - زجاج نانو مرن', 'Screen Protector Realme C30 C31 C33 C35 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3383', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - برايفسي خصوصية', 'Screen Protector Realme C30 C31 C33 C35 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3384', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - سيراميك', 'Screen Protector Realme C30 C31 C33 C35 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3385', 'qr', 'اسكرينة ريلمي C30/C31/C33/C35 - مات ضد البصمة', 'Screen Protector Realme C30 C31 C33 C35 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3386', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - زجاج مقوي 9H', 'Screen Protector Realme C51 C53 C55 C61 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3387', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - زجاج نانو مرن', 'Screen Protector Realme C51 C53 C55 C61 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3388', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - برايفسي خصوصية', 'Screen Protector Realme C51 C53 C55 C61 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3389', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - سيراميك', 'Screen Protector Realme C51 C53 C55 C61 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3390', 'qr', 'اسكرينة ريلمي C51/C53/C55/C61 - مات ضد البصمة', 'Screen Protector Realme C51 C53 C55 C61 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3391', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - زجاج مقوي 9H', 'Screen Protector Realme C63 C65 C67 C75 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3392', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - زجاج نانو مرن', 'Screen Protector Realme C63 C65 C67 C75 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3393', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - برايفسي خصوصية', 'Screen Protector Realme C63 C65 C67 C75 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3394', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - سيراميك', 'Screen Protector Realme C63 C65 C67 C75 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3395', 'qr', 'اسكرينة ريلمي C63/C65/C67/C75 - مات ضد البصمة', 'Screen Protector Realme C63 C65 C67 C75 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3396', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - زجاج مقوي 9H', 'Screen Protector Realme 8 9 10 11 12 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3397', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - زجاج نانو مرن', 'Screen Protector Realme 8 9 10 11 12 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3398', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - برايفسي خصوصية', 'Screen Protector Realme 8 9 10 11 12 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3399', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - سيراميك', 'Screen Protector Realme 8 9 10 11 12 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3400', 'qr', 'اسكرينة ريلمي 8/9/10/11/12 - مات ضد البصمة', 'Screen Protector Realme 8 9 10 11 12 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3401', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - زجاج مقوي 9H', 'Screen Protector Realme Narzo 30 50 60 70 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3402', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - زجاج نانو مرن', 'Screen Protector Realme Narzo 30 50 60 70 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3403', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - برايفسي خصوصية', 'Screen Protector Realme Narzo 30 50 60 70 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3404', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - سيراميك', 'Screen Protector Realme Narzo 30 50 60 70 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3405', 'qr', 'اسكرينة ريلمي نارزو 30/50/60/70 - مات ضد البصمة', 'Screen Protector Realme Narzo 30 50 60 70 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3406', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - زجاج مقوي 9H', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3407', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - زجاج نانو مرن', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3408', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - برايفسي خصوصية', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3409', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - سيراميك', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3410', 'qr', 'اسكرينة هواوي Y5p/Y6p/Y7a/Y7p/Y9 - مات ضد البصمة', 'Screen Protector Huawei Y5p Y6p Y7a Y7p Y9 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3411', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - زجاج مقوي 9H', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3412', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - زجاج نانو مرن', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3413', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - برايفسي خصوصية', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3414', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - سيراميك', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3415', 'qr', 'اسكرينة هواوي نوفا 5T/7i/8i/9/10 - مات ضد البصمة', 'Screen Protector Huawei Nova 5T 7i 8i 9 10 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3416', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - زجاج مقوي 9H', 'Screen Protector Huawei P30 P40 P50 P60 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3417', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - زجاج نانو مرن', 'Screen Protector Huawei P30 P40 P50 P60 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3418', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - برايفسي خصوصية', 'Screen Protector Huawei P30 P40 P50 P60 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3419', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - سيراميك', 'Screen Protector Huawei P30 P40 P50 P60 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3420', 'qr', 'اسكرينة هواوي P30/P40/P50/P60 - مات ضد البصمة', 'Screen Protector Huawei P30 P40 P50 P60 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3421', 'qr', 'اسكرينة هواوي ميت 30/40/50 - زجاج مقوي 9H', 'Screen Protector Huawei Mate 30 40 50 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3422', 'qr', 'اسكرينة هواوي ميت 30/40/50 - زجاج نانو مرن', 'Screen Protector Huawei Mate 30 40 50 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3423', 'qr', 'اسكرينة هواوي ميت 30/40/50 - برايفسي خصوصية', 'Screen Protector Huawei Mate 30 40 50 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3424', 'qr', 'اسكرينة هواوي ميت 30/40/50 - سيراميك', 'Screen Protector Huawei Mate 30 40 50 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3425', 'qr', 'اسكرينة هواوي ميت 30/40/50 - مات ضد البصمة', 'Screen Protector Huawei Mate 30 40 50 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3426', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - زجاج مقوي 9H', 'Screen Protector Honor X6 X7 X8 X9 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3427', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - زجاج نانو مرن', 'Screen Protector Honor X6 X7 X8 X9 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3428', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - برايفسي خصوصية', 'Screen Protector Honor X6 X7 X8 X9 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3429', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - سيراميك', 'Screen Protector Honor X6 X7 X8 X9 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3430', 'qr', 'اسكرينة هونر X6/X7/X8/X9 - مات ضد البصمة', 'Screen Protector Honor X6 X7 X8 X9 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3431', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - زجاج مقوي 9H', 'Screen Protector Honor 8A 8X 9X 10 20 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3432', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - زجاج نانو مرن', 'Screen Protector Honor 8A 8X 9X 10 20 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3433', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - برايفسي خصوصية', 'Screen Protector Honor 8A 8X 9X 10 20 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3434', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - سيراميك', 'Screen Protector Honor 8A 8X 9X 10 20 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3435', 'qr', 'اسكرينة هونر 8A/8X/9X/10/20 - مات ضد البصمة', 'Screen Protector Honor 8A 8X 9X 10 20 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3436', 'qr', 'اسكرينة هونر 50/70/90 - زجاج مقوي 9H', 'Screen Protector Honor 50 70 90 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3437', 'qr', 'اسكرينة هونر 50/70/90 - زجاج نانو مرن', 'Screen Protector Honor 50 70 90 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3438', 'qr', 'اسكرينة هونر 50/70/90 - برايفسي خصوصية', 'Screen Protector Honor 50 70 90 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3439', 'qr', 'اسكرينة هونر 50/70/90 - سيراميك', 'Screen Protector Honor 50 70 90 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3440', 'qr', 'اسكرينة هونر 50/70/90 - مات ضد البصمة', 'Screen Protector Honor 50 70 90 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3441', 'qr', 'اسكرينة هونر ماجيك 5/6 - زجاج مقوي 9H', 'Screen Protector Honor Magic 5 6 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3442', 'qr', 'اسكرينة هونر ماجيك 5/6 - زجاج نانو مرن', 'Screen Protector Honor Magic 5 6 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3443', 'qr', 'اسكرينة هونر ماجيك 5/6 - برايفسي خصوصية', 'Screen Protector Honor Magic 5 6 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3444', 'qr', 'اسكرينة هونر ماجيك 5/6 - سيراميك', 'Screen Protector Honor Magic 5 6 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3445', 'qr', 'اسكرينة هونر ماجيك 5/6 - مات ضد البصمة', 'Screen Protector Honor Magic 5 6 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3446', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - زجاج مقوي 9H', 'Screen Protector Nokia G10 G20 G21 C21 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3447', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - زجاج نانو مرن', 'Screen Protector Nokia G10 G20 G21 C21 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3448', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - برايفسي خصوصية', 'Screen Protector Nokia G10 G20 G21 C21 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3449', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - سيراميك', 'Screen Protector Nokia G10 G20 G21 C21 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3450', 'qr', 'اسكرينة نوكيا G10/G20/G21/C21 - مات ضد البصمة', 'Screen Protector Nokia G10 G20 G21 C21 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3451', 'qr', 'اسكرينة نوكيا G42/X20/X30 - زجاج مقوي 9H', 'Screen Protector Nokia G42 X20 X30 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3452', 'qr', 'اسكرينة نوكيا G42/X20/X30 - زجاج نانو مرن', 'Screen Protector Nokia G42 X20 X30 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3453', 'qr', 'اسكرينة نوكيا G42/X20/X30 - برايفسي خصوصية', 'Screen Protector Nokia G42 X20 X30 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3454', 'qr', 'اسكرينة نوكيا G42/X20/X30 - سيراميك', 'Screen Protector Nokia G42 X20 X30 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3455', 'qr', 'اسكرينة نوكيا G42/X20/X30 - مات ضد البصمة', 'Screen Protector Nokia G42 X20 X30 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3456', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - زجاج مقوي 9H', 'Screen Protector Motorola G10 G20 G22 G30 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3457', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - زجاج نانو مرن', 'Screen Protector Motorola G10 G20 G22 G30 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3458', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - برايفسي خصوصية', 'Screen Protector Motorola G10 G20 G22 G30 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3459', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - سيراميك', 'Screen Protector Motorola G10 G20 G22 G30 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3460', 'qr', 'اسكرينة موتورولا G10/G20/G22/G30 - مات ضد البصمة', 'Screen Protector Motorola G10 G20 G22 G30 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3461', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - زجاج مقوي 9H', 'Screen Protector Motorola G32 G52 G54 G62 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3462', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - زجاج نانو مرن', 'Screen Protector Motorola G32 G52 G54 G62 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3463', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - برايفسي خصوصية', 'Screen Protector Motorola G32 G52 G54 G62 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3464', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - سيراميك', 'Screen Protector Motorola G32 G52 G54 G62 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3465', 'qr', 'اسكرينة موتورولا G32/G52/G54/G62 - مات ضد البصمة', 'Screen Protector Motorola G32 G52 G54 G62 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3466', 'qr', 'اسكرينة موتورولا G72/G82/G84 - زجاج مقوي 9H', 'Screen Protector Motorola G72 G82 G84 - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3467', 'qr', 'اسكرينة موتورولا G72/G82/G84 - زجاج نانو مرن', 'Screen Protector Motorola G72 G82 G84 - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3468', 'qr', 'اسكرينة موتورولا G72/G82/G84 - برايفسي خصوصية', 'Screen Protector Motorola G72 G82 G84 - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3469', 'qr', 'اسكرينة موتورولا G72/G82/G84 - سيراميك', 'Screen Protector Motorola G72 G82 G84 - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3470', 'qr', 'اسكرينة موتورولا G72/G82/G84 - مات ضد البصمة', 'Screen Protector Motorola G72 G82 G84 - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0),
('ASLYSCR3471', 'qr', 'اسكرينة موتورولا E7/E13/E22 - زجاج مقوي 9H', 'Screen Protector Motorola E7 E13 E22 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3472', 'qr', 'اسكرينة موتورولا E7/E13/E22 - زجاج نانو مرن', 'Screen Protector Motorola E7 E13 E22 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3473', 'qr', 'اسكرينة موتورولا E7/E13/E22 - برايفسي خصوصية', 'Screen Protector Motorola E7 E13 E22 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3474', 'qr', 'اسكرينة موتورولا E7/E13/E22 - سيراميك', 'Screen Protector Motorola E7 E13 E22 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3475', 'qr', 'اسكرينة موتورولا E7/E13/E22 - مات ضد البصمة', 'Screen Protector Motorola E7 E13 E22 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3476', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - زجاج مقوي 9H', 'Screen Protector OnePlus Nord 2 3 CE - Tempered Glass 9H', 'screens', 'new', '54', '115', '98', 0, 3, 0, 0),
('ASLYSCR3477', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - زجاج نانو مرن', 'Screen Protector OnePlus Nord 2 3 CE - Nano Flexible', 'screens', 'new', '69', '145', '123', 0, 3, 0, 0),
('ASLYSCR3478', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - برايفسي خصوصية', 'Screen Protector OnePlus Nord 2 3 CE - Privacy Filter', 'screens', 'new', '104', '195', '166', 0, 3, 0, 0),
('ASLYSCR3479', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - سيراميك', 'Screen Protector OnePlus Nord 2 3 CE - Ceramic Glass', 'screens', 'new', '84', '165', '140', 0, 3, 0, 0),
('ASLYSCR3480', 'qr', 'اسكرينة ون بلس نورد 2/3/CE - مات ضد البصمة', 'Screen Protector OnePlus Nord 2 3 CE - Matte Anti-Fingerprint', 'screens', 'new', '79', '155', '132', 0, 3, 0, 0);

INSERT IGNORE INTO `products` (`barcode`, `code_type`, `name`, `name_en`, `category`, `condition_status`, `purchase_price`, `sell_price`, `min_sell_price`, `quantity`, `low_stock_threshold`, `show_on_landing`, `is_featured`) VALUES
('ASLYSCR3481', 'qr', 'اسكرينة ون بلس 9/9R/10 - زجاج مقوي 9H', 'Screen Protector OnePlus 9 9R 10 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3482', 'qr', 'اسكرينة ون بلس 9/9R/10 - زجاج نانو مرن', 'Screen Protector OnePlus 9 9R 10 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3483', 'qr', 'اسكرينة ون بلس 9/9R/10 - برايفسي خصوصية', 'Screen Protector OnePlus 9 9R 10 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3484', 'qr', 'اسكرينة ون بلس 9/9R/10 - سيراميك', 'Screen Protector OnePlus 9 9R 10 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3485', 'qr', 'اسكرينة ون بلس 9/9R/10 - مات ضد البصمة', 'Screen Protector OnePlus 9 9R 10 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3486', 'qr', 'اسكرينة ون بلس 11/12/13 - زجاج مقوي 9H', 'Screen Protector OnePlus 11 12 13 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3487', 'qr', 'اسكرينة ون بلس 11/12/13 - زجاج نانو مرن', 'Screen Protector OnePlus 11 12 13 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3488', 'qr', 'اسكرينة ون بلس 11/12/13 - برايفسي خصوصية', 'Screen Protector OnePlus 11 12 13 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3489', 'qr', 'اسكرينة ون بلس 11/12/13 - سيراميك', 'Screen Protector OnePlus 11 12 13 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3490', 'qr', 'اسكرينة ون بلس 11/12/13 - مات ضد البصمة', 'Screen Protector OnePlus 11 12 13 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3491', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - زجاج مقوي 9H', 'Screen Protector Pixel 6 6a 7 7a - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3492', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - زجاج نانو مرن', 'Screen Protector Pixel 6 6a 7 7a - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3493', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - برايفسي خصوصية', 'Screen Protector Pixel 6 6a 7 7a - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3494', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - سيراميك', 'Screen Protector Pixel 6 6a 7 7a - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3495', 'qr', 'اسكرينة بيكسل 6/6a/7/7a - مات ضد البصمة', 'Screen Protector Pixel 6 6a 7 7a - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3496', 'qr', 'اسكرينة بيكسل 8/8a/9 - زجاج مقوي 9H', 'Screen Protector Pixel 8 8a 9 - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3497', 'qr', 'اسكرينة بيكسل 8/8a/9 - زجاج نانو مرن', 'Screen Protector Pixel 8 8a 9 - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3498', 'qr', 'اسكرينة بيكسل 8/8a/9 - برايفسي خصوصية', 'Screen Protector Pixel 8 8a 9 - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3499', 'qr', 'اسكرينة بيكسل 8/8a/9 - سيراميك', 'Screen Protector Pixel 8 8a 9 - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3500', 'qr', 'اسكرينة بيكسل 8/8a/9 - مات ضد البصمة', 'Screen Protector Pixel 8 8a 9 - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3501', 'qr', 'اسكرينة نوثينج فون 1/2/2a - زجاج مقوي 9H', 'Screen Protector Nothing Phone 1 2 2a - Tempered Glass 9H', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3502', 'qr', 'اسكرينة نوثينج فون 1/2/2a - زجاج نانو مرن', 'Screen Protector Nothing Phone 1 2 2a - Nano Flexible', 'screens', 'new', '90', '180', '153', 0, 3, 0, 0),
('ASLYSCR3503', 'qr', 'اسكرينة نوثينج فون 1/2/2a - برايفسي خصوصية', 'Screen Protector Nothing Phone 1 2 2a - Privacy Filter', 'screens', 'new', '125', '230', '196', 0, 3, 0, 0),
('ASLYSCR3504', 'qr', 'اسكرينة نوثينج فون 1/2/2a - سيراميك', 'Screen Protector Nothing Phone 1 2 2a - Ceramic Glass', 'screens', 'new', '105', '200', '170', 0, 3, 0, 0),
('ASLYSCR3505', 'qr', 'اسكرينة نوثينج فون 1/2/2a - مات ضد البصمة', 'Screen Protector Nothing Phone 1 2 2a - Matte Anti-Fingerprint', 'screens', 'new', '100', '190', '162', 0, 3, 0, 0),
('ASLYSCR3506', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - زجاج مقوي 9H', 'Screen Protector Itel A23 A48 A56 P38 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3507', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - زجاج نانو مرن', 'Screen Protector Itel A23 A48 A56 P38 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3508', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - برايفسي خصوصية', 'Screen Protector Itel A23 A48 A56 P38 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3509', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - سيراميك', 'Screen Protector Itel A23 A48 A56 P38 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3510', 'qr', 'اسكرينة ايتل A23/A48/A56/P38 - مات ضد البصمة', 'Screen Protector Itel A23 A48 A56 P38 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3511', 'qr', 'اسكرينة ايتل Vision 1/2 - زجاج مقوي 9H', 'Screen Protector Itel Vision 1 2 - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3512', 'qr', 'اسكرينة ايتل Vision 1/2 - زجاج نانو مرن', 'Screen Protector Itel Vision 1 2 - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3513', 'qr', 'اسكرينة ايتل Vision 1/2 - برايفسي خصوصية', 'Screen Protector Itel Vision 1 2 - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3514', 'qr', 'اسكرينة ايتل Vision 1/2 - سيراميك', 'Screen Protector Itel Vision 1 2 - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3515', 'qr', 'اسكرينة ايتل Vision 1/2 - مات ضد البصمة', 'Screen Protector Itel Vision 1 2 - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0),
('ASLYSCR3516', 'qr', 'اسكرينة لينوفو K14 بلس - زجاج مقوي 9H', 'Screen Protector Lenovo K14 Plus - Tempered Glass 9H', 'screens', 'new', '45', '100', '85', 0, 3, 0, 0),
('ASLYSCR3517', 'qr', 'اسكرينة لينوفو K14 بلس - زجاج نانو مرن', 'Screen Protector Lenovo K14 Plus - Nano Flexible', 'screens', 'new', '60', '130', '110', 0, 3, 0, 0),
('ASLYSCR3518', 'qr', 'اسكرينة لينوفو K14 بلس - برايفسي خصوصية', 'Screen Protector Lenovo K14 Plus - Privacy Filter', 'screens', 'new', '95', '180', '153', 0, 3, 0, 0),
('ASLYSCR3519', 'qr', 'اسكرينة لينوفو K14 بلس - سيراميك', 'Screen Protector Lenovo K14 Plus - Ceramic Glass', 'screens', 'new', '75', '150', '128', 0, 3, 0, 0),
('ASLYSCR3520', 'qr', 'اسكرينة لينوفو K14 بلس - مات ضد البصمة', 'Screen Protector Lenovo K14 Plus - Matte Anti-Fingerprint', 'screens', 'new', '70', '140', '119', 0, 3, 0, 0);

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
