<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $driver = DB_DRIVER;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($driver === 'sqlite') {
            $sqlitePath = getenv('ASLY_SQLITE_PATH') ?: (sys_get_temp_dir() . '/asly_store_preview.sqlite');
            $isNew = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
            self::$pdo = new PDO('sqlite:' . $sqlitePath, null, null, $options);
            self::$pdo->exec('PRAGMA foreign_keys = ON;');
            if ($isNew) {
                self::initializeSqliteSchema(self::$pdo);
            } else {
                self::ensureProductColumnsSqlite(self::$pdo);
                self::ensureScreensSqlite(self::$pdo);
                self::migrateScreensColumns(self::$pdo);
                self::rebuildLegacyProtectors(self::$pdo);
            }
            self::seedScreenCatalog(self::$pdo);
            self::migrateLegacyProtectors(self::$pdo);
            return self::$pdo;
        }

        $dsnWithDb = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        try {
            self::$pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
            self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $check = self::$pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
            if (!$check) {
                self::importMysqlDump(self::$pdo);
            } else {
                self::ensureProductColumnsMysql(self::$pdo);
                self::ensureScreensMysql(self::$pdo);
                self::migrateScreensColumns(self::$pdo);
                self::rebuildLegacyProtectors(self::$pdo);
            }
            self::seedScreenCatalog(self::$pdo);
            self::migrateLegacyProtectors(self::$pdo);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1049') !== false || stripos($e->getMessage(), 'Unknown database') !== false) {
                $dsnServer = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
                $rootPdo = new PDO($dsnServer, DB_USER, DB_PASS, $options);
                $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', DB_NAME);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
                self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::importMysqlDump(self::$pdo);
            } else {
                throw $e;
            }
        }

        return self::$pdo;
    }

    private static function ensureProductColumnsMysql(PDO $pdo): void
    {
        $cols = [];
        foreach ($pdo->query("SHOW COLUMNS FROM `products`")->fetchAll() as $c) {
            $cols[$c['Field']] = true;
        }
        $alterations = [
            'screen_type'          => "ALTER TABLE `products` ADD COLUMN `screen_type` VARCHAR(40) DEFAULT NULL AFTER `brand`",
            'width_mm'             => "ALTER TABLE `products` ADD COLUMN `width_mm` DECIMAL(6,2) DEFAULT NULL AFTER `screen_type`",
            'height_mm'            => "ALTER TABLE `products` ADD COLUMN `height_mm` DECIMAL(6,2) DEFAULT NULL AFTER `width_mm`",
            'name_en'              => "ALTER TABLE `products` ADD COLUMN `name_en` VARCHAR(200) DEFAULT NULL AFTER `name`",
            'specs_en'             => "ALTER TABLE `products` ADD COLUMN `specs_en` TEXT DEFAULT NULL AFTER `specs`",
            'description_en'       => "ALTER TABLE `products` ADD COLUMN `description_en` TEXT DEFAULT NULL AFTER `description`",
            'secondary_image_path' => "ALTER TABLE `products` ADD COLUMN `secondary_image_path` VARCHAR(255) DEFAULT NULL AFTER `image_path`",
        ];
        foreach ($alterations as $col => $sql) {
            if (!isset($cols[$col])) {
                $pdo->exec($sql);
            }
        }
    }

    private static function ensureScreensMysql(PDO $pdo): void
    {
        $needed = [
            "CREATE TABLE IF NOT EXISTS `screen_types` (
              `slug` VARCHAR(40) NOT NULL,
              `name_ar` VARCHAR(80) NOT NULL,
              `name_en` VARCHAR(80) NOT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `notes` VARCHAR(200) DEFAULT NULL,
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              PRIMARY KEY (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `screen_type_prices` (
              `type_slug` VARCHAR(40) NOT NULL,
              `size_tier` TINYINT NOT NULL DEFAULT 1,
              `sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
              `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
              `min_sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
              PRIMARY KEY (`type_slug`, `size_tier`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `device_models` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `brand` VARCHAR(60) NOT NULL,
              `model_name` VARCHAR(120) NOT NULL,
              `model_name_ar` VARCHAR(120) DEFAULT NULL,
              `search_key` VARCHAR(220) NOT NULL DEFAULT '',
              `screen_inch` DECIMAL(4,2) DEFAULT NULL,
              `width_mm` DECIMAL(6,2) DEFAULT NULL,
              `height_mm` DECIMAL(6,2) DEFAULT NULL,
              `device_kind` VARCHAR(20) NOT NULL DEFAULT 'phone',
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_device_model` (`brand`, `model_name`),
              KEY `idx_device_search` (`search_key`),
              KEY `idx_device_size` (`width_mm`, `height_mm`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `shelves` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `shelf_code` VARCHAR(20) NOT NULL,
              `label` VARCHAR(120) DEFAULT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_shelf_code` (`shelf_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `screen_protectors` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `barcode` VARCHAR(100) NOT NULL,
              `code_type` VARCHAR(20) NOT NULL DEFAULT 'qr',
              `product_id` INT UNSIGNED DEFAULT NULL,
              `type_slug` VARCHAR(40) NOT NULL DEFAULT 'clear',
              `width_mm` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
              `height_mm` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
              `diag_inch` DECIMAL(4,2) DEFAULT NULL,
              `size_tier` TINYINT NOT NULL DEFAULT 1,
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `screen_compatibility` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `screen_id` INT UNSIGNED NOT NULL,
              `model_id` INT UNSIGNED NOT NULL,
              `link_type` VARCHAR(20) NOT NULL DEFAULT 'manual',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_screen_model` (`screen_id`, `model_id`),
              KEY `idx_compat_model` (`model_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS `screen_movements` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
        foreach ($needed as $sql) {
            $pdo->exec($sql);
        }
        $hasShelves = (int)$pdo->query("SELECT COUNT(*) FROM `shelves`")->fetchColumn();
        if ($hasShelves === 0) {
            $pdo->exec("INSERT IGNORE INTO `shelves` (`shelf_code`, `label`, `sort_order`) VALUES ('A','رف A',1),('B','رف B',2),('C','رف C',3),('D','رف D',4)");
        }
    }

    private static function ensureProductColumnsSqlite(PDO $pdo): void
    {
        $cols = [];
        foreach ($pdo->query("PRAGMA table_info(products)")->fetchAll() as $c) {
            $cols[$c['name']] = true;
        }
        $alterations = [
            'screen_type'          => "ALTER TABLE products ADD COLUMN screen_type TEXT DEFAULT NULL",
            'width_mm'             => "ALTER TABLE products ADD COLUMN width_mm REAL DEFAULT NULL",
            'height_mm'            => "ALTER TABLE products ADD COLUMN height_mm REAL DEFAULT NULL",
            'name_en'              => "ALTER TABLE products ADD COLUMN name_en TEXT DEFAULT NULL",
            'specs_en'             => "ALTER TABLE products ADD COLUMN specs_en TEXT DEFAULT NULL",
            'description_en'       => "ALTER TABLE products ADD COLUMN description_en TEXT DEFAULT NULL",
            'secondary_image_path' => "ALTER TABLE products ADD COLUMN secondary_image_path TEXT DEFAULT NULL",
        ];
        foreach ($alterations as $col => $sql) {
            if (!isset($cols[$col])) {
                $pdo->exec($sql);
            }
        }
    }

    private static function ensureScreensSqlite(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS screen_types (
                slug TEXT PRIMARY KEY,
                name_ar TEXT NOT NULL,
                name_en TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                notes TEXT DEFAULT NULL,
                is_active INTEGER NOT NULL DEFAULT 1
            );
            CREATE TABLE IF NOT EXISTS screen_type_prices (
                type_slug TEXT NOT NULL,
                size_tier INTEGER NOT NULL DEFAULT 1,
                sell_price REAL NOT NULL DEFAULT 0,
                cost_price REAL NOT NULL DEFAULT 0,
                min_sell_price REAL NOT NULL DEFAULT 0,
                PRIMARY KEY (type_slug, size_tier)
            );
            CREATE TABLE IF NOT EXISTS device_models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                brand TEXT NOT NULL,
                model_name TEXT NOT NULL,
                model_name_ar TEXT DEFAULT NULL,
                search_key TEXT NOT NULL DEFAULT '',
                screen_inch REAL DEFAULT NULL,
                width_mm REAL DEFAULT NULL,
                height_mm REAL DEFAULT NULL,
                device_kind TEXT NOT NULL DEFAULT 'phone',
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                UNIQUE (brand, model_name)
            );
            CREATE TABLE IF NOT EXISTS shelves (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                shelf_code TEXT NOT NULL UNIQUE,
                label TEXT DEFAULT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS screen_protectors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                barcode TEXT NOT NULL UNIQUE,
                code_type TEXT NOT NULL DEFAULT 'qr',
                product_id INTEGER DEFAULT NULL,
                type_slug TEXT NOT NULL DEFAULT 'clear',
                width_mm REAL NOT NULL DEFAULT 0,
                height_mm REAL NOT NULL DEFAULT 0,
                diag_inch REAL DEFAULT NULL,
                size_tier INTEGER NOT NULL DEFAULT 1,
                shelf_code TEXT DEFAULT NULL,
                drawer_label TEXT DEFAULT NULL,
                purchase_price REAL NOT NULL DEFAULT 0,
                sell_price REAL NOT NULL DEFAULT 0,
                min_sell_price REAL NOT NULL DEFAULT 0,
                quantity INTEGER NOT NULL DEFAULT 0,
                low_stock_threshold INTEGER NOT NULL DEFAULT 3,
                notes TEXT DEFAULT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_by INTEGER DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS screen_compatibility (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                screen_id INTEGER NOT NULL,
                model_id INTEGER NOT NULL,
                link_type TEXT NOT NULL DEFAULT 'manual',
                UNIQUE (screen_id, model_id)
            );
            CREATE TABLE IF NOT EXISTS screen_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                screen_id INTEGER NOT NULL,
                movement_type TEXT NOT NULL DEFAULT 'adjust',
                qty_before INTEGER NOT NULL DEFAULT 0,
                qty_after INTEGER NOT NULL DEFAULT 0,
                note TEXT DEFAULT NULL,
                user_name TEXT DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
        ");
    }

    /** brings an older install up to the current screens schema */
    private static function migrateScreensColumns(PDO $pdo): void
    {
        $specs = [
            'device_models' => [
                'screen_inch' => ['sqlite' => 'REAL DEFAULT NULL', 'mysql' => 'DECIMAL(4,2) DEFAULT NULL'],
                'width_mm'    => ['sqlite' => 'REAL DEFAULT NULL', 'mysql' => 'DECIMAL(6,2) DEFAULT NULL'],
                'height_mm'   => ['sqlite' => 'REAL DEFAULT NULL', 'mysql' => 'DECIMAL(6,2) DEFAULT NULL'],
                'device_kind' => ['sqlite' => "TEXT NOT NULL DEFAULT 'phone'", 'mysql' => "VARCHAR(20) NOT NULL DEFAULT 'phone'"],
            ],
            'screen_protectors' => [
                'type_slug'   => ['sqlite' => "TEXT NOT NULL DEFAULT 'clear'", 'mysql' => "VARCHAR(40) NOT NULL DEFAULT 'clear'"],
                'width_mm'    => ['sqlite' => 'REAL NOT NULL DEFAULT 0', 'mysql' => 'DECIMAL(6,2) NOT NULL DEFAULT 0.00'],
                'height_mm'   => ['sqlite' => 'REAL NOT NULL DEFAULT 0', 'mysql' => 'DECIMAL(6,2) NOT NULL DEFAULT 0.00'],
                'diag_inch'   => ['sqlite' => 'REAL DEFAULT NULL', 'mysql' => 'DECIMAL(4,2) DEFAULT NULL'],
                'size_tier'   => ['sqlite' => 'INTEGER NOT NULL DEFAULT 1', 'mysql' => 'TINYINT NOT NULL DEFAULT 1'],
            ],
            'screen_compatibility' => [
                'link_type' => ['sqlite' => "TEXT NOT NULL DEFAULT 'manual'", 'mysql' => "VARCHAR(20) NOT NULL DEFAULT 'manual'"],
            ],
        ];
        foreach ($specs as $table => $columns) {
            try {
                if (DB_DRIVER === 'sqlite') {
                    $have = [];
                    foreach ($pdo->query("PRAGMA table_info({$table})")->fetchAll() as $c) {
                        $have[$c['name']] = true;
                    }
                } else {
                    $have = [];
                    foreach ($pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll() as $c) {
                        $have[$c['Field']] = true;
                    }
                }
                foreach ($columns as $col => $def) {
                    if (isset($have[$col])) {
                        continue;
                    }
                    $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $col . ' ' . $def[DB_DRIVER === 'sqlite' ? 'sqlite' : 'mysql']);
                }
            } catch (Throwable $e) {
                // table not there yet or no permission: the schema creation step handles it
            }
        }
    }

    /** older installs carry NOT NULL legacy columns (name/quality) that block the new inserts */
    private static function rebuildLegacyProtectors(PDO $pdo): void
    {
        try {
            if (DB_DRIVER === 'sqlite') {
                $cols = [];
                foreach ($pdo->query('PRAGMA table_info(screen_protectors)')->fetchAll() as $c) {
                    $cols[$c['name']] = true;
                }
                if (!isset($cols['name'])) {
                    return;
                }
                $pdo->exec('
                    CREATE TABLE screen_protectors_mig (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        barcode TEXT NOT NULL UNIQUE,
                        code_type TEXT NOT NULL DEFAULT \'qr\',
                        product_id INTEGER DEFAULT NULL,
                        type_slug TEXT NOT NULL DEFAULT \'clear\',
                        width_mm REAL NOT NULL DEFAULT 0,
                        height_mm REAL NOT NULL DEFAULT 0,
                        diag_inch REAL DEFAULT NULL,
                        size_tier INTEGER NOT NULL DEFAULT 1,
                        shelf_code TEXT DEFAULT NULL,
                        drawer_label TEXT DEFAULT NULL,
                        purchase_price REAL NOT NULL DEFAULT 0,
                        sell_price REAL NOT NULL DEFAULT 0,
                        min_sell_price REAL NOT NULL DEFAULT 0,
                        quantity INTEGER NOT NULL DEFAULT 0,
                        low_stock_threshold INTEGER NOT NULL DEFAULT 3,
                        notes TEXT DEFAULT NULL,
                        is_active INTEGER NOT NULL DEFAULT 1,
                        created_by INTEGER DEFAULT NULL,
                        created_at TEXT DEFAULT NULL,
                        updated_at TEXT DEFAULT NULL,
                        name TEXT DEFAULT NULL,
                        name_en TEXT DEFAULT NULL,
                        quality TEXT DEFAULT NULL
                    )
                ');
                $pdo->exec('
                    INSERT INTO screen_protectors_mig
                        (id, barcode, code_type, product_id, type_slug, width_mm, height_mm, diag_inch, size_tier,
                         shelf_code, drawer_label, purchase_price, sell_price, min_sell_price, quantity,
                         low_stock_threshold, notes, is_active, created_by, created_at, updated_at, name, name_en, quality)
                    SELECT id, barcode, code_type, product_id, COALESCE(type_slug, \'clear\'), COALESCE(width_mm, 0),
                           COALESCE(height_mm, 0), diag_inch, COALESCE(size_tier, 1), shelf_code, drawer_label,
                           purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, notes,
                           is_active, created_by, created_at, updated_at, name, name_en, quality
                    FROM screen_protectors
                ');
                $pdo->exec('DROP TABLE screen_protectors');
                $pdo->exec('ALTER TABLE screen_protectors_mig RENAME TO screen_protectors');
                return;
            }
            // mysql: just relax the legacy NOT NULL columns
            $pdo->exec('ALTER TABLE `screen_protectors` MODIFY `name` VARCHAR(200) NULL DEFAULT NULL');
            $pdo->exec('ALTER TABLE `screen_protectors` MODIFY `name_en` VARCHAR(200) NULL DEFAULT NULL');
            $pdo->exec('ALTER TABLE `screen_protectors` MODIFY `quality` VARCHAR(40) NULL DEFAULT NULL');
        } catch (Throwable $e) {
            // nothing to migrate
        }
    }

    /** folds protectors created before the size-based catalog into the new rows, keeping their stock */
    private static function migrateLegacyProtectors(PDO $pdo): void
    {
        try {
            $legacy = $pdo->query('SELECT * FROM screen_protectors WHERE width_mm IS NULL OR width_mm <= 0')->fetchAll();
        } catch (Throwable $e) {
            return;
        }
        if (!$legacy) {
            return;
        }
        $validTypes = [];
        try {
            foreach ($pdo->query('SELECT slug FROM screen_types')->fetchAll() as $row) {
                $validTypes[$row['slug']] = true;
            }
        } catch (Throwable $e) {
            return;
        }
        $avgStmt = $pdo->prepare('
            SELECT AVG(m.width_mm) AS w, AVG(m.height_mm) AS h, COUNT(*) AS total
            FROM screen_compatibility c
            INNER JOIN device_models m ON m.id = c.model_id
            WHERE c.screen_id = ? AND m.width_mm IS NOT NULL
        ');
        $findStmt = $pdo->prepare('
            SELECT id FROM screen_protectors
            WHERE is_active = 1 AND width_mm > 0 AND type_slug = ? AND width_mm BETWEEN ? AND ? AND height_mm BETWEEN ? AND ?
            ORDER BY ABS(width_mm - ?) + ABS(height_mm - ?) ASC LIMIT 1
        ');
        $qtyStmt = $pdo->prepare('SELECT quantity FROM screen_protectors WHERE id = ?');
        $off = $pdo->prepare('UPDATE screen_protectors SET is_active = 0, updated_at = ? WHERE id = ?');
        $merge = $pdo->prepare('UPDATE screen_protectors SET quantity = ?, shelf_code = COALESCE(shelf_code, ?), drawer_label = COALESCE(drawer_label, ?), updated_at = ? WHERE id = ?');
        $resize = $pdo->prepare('UPDATE screen_protectors SET width_mm = ?, height_mm = ?, diag_inch = ?, size_tier = ?, updated_at = ? WHERE id = ?');
        $now = date('Y-m-d H:i:s');
        foreach ($legacy as $row) {
            $type = (string)($row['type_slug'] ?? '');
            if (!isset($validTypes[$type])) {
                $type = isset($validTypes['9h']) ? '9h' : array_key_first($validTypes);
            }
            $avgStmt->execute([(int)$row['id']]);
            $avg = $avgStmt->fetch() ?: null;
            $width = $avg && $avg['w'] !== null ? round((float)$avg['w'], 2) : 0.0;
            $height = $avg && $avg['h'] !== null ? round((float)$avg['h'], 2) : 0.0;
            if ($width <= 0 || $height <= 0) {
                $off->execute([$now, (int)$row['id']]);
                continue;
            }
            $diag = round(sqrt($width ** 2 + $height ** 2) / 25.4, 2);
            $tier = 0;
            $findStmt->execute([$type, $width - 1, $width + 1, $height - 1.5, $height + 1.5, $width, $height]);
            $targetId = (int)$findStmt->fetchColumn();
            if ($targetId > 0) {
                $qtyStmt->execute([$targetId]);
                $targetQty = (int)$qtyStmt->fetchColumn();
                $merge->execute([$targetQty + (int)$row['quantity'], $row['shelf_code'] ?? null, $row['drawer_label'] ?? null, $now, $targetId]);
                $off->execute([$now, (int)$row['id']]);
            } else {
                $resize->execute([$width, $height, $diag, $tier, $now, (int)$row['id']]);
            }
        }
    }

    /** retires the old 8-type / size-tier catalog: only HD, Banda and Privacy stay on sale */
    private static function retireLegacyCatalog(PDO $pdo): void
    {
        try {
            $keep = ['hd', 'banda', 'privacy'];
            $pdo->exec("UPDATE screen_types SET is_active = 0 WHERE slug NOT IN ('hd', 'banda', 'privacy')");
            // one price per type: keep the smallest tier as the single row and drop the rest
            $seen = [];
            $prices = $pdo->query('SELECT type_slug, size_tier, sell_price, cost_price, min_sell_price FROM screen_type_prices ORDER BY type_slug ASC, size_tier ASC')->fetchAll();
            $ins = $pdo->prepare('INSERT INTO screen_type_prices (type_slug, size_tier, sell_price, cost_price, min_sell_price) VALUES (?, 0, ?, ?, ?)');
            $del = $pdo->prepare('DELETE FROM screen_type_prices WHERE type_slug = ?');
            foreach ($prices as $row) {
                $slug = (string)$row['type_slug'];
                if (isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;
                $del->execute([$slug]);
                $ins->execute([$slug, (float)$row['sell_price'], (float)$row['cost_price'], (float)$row['min_sell_price']]);
            }
            $pdo->exec("DELETE FROM screen_type_prices WHERE type_slug NOT IN ('hd', 'banda', 'privacy')");
            // old size rows whose type is gone, or that carry a barcode from the retired catalogs
            $find = $pdo->prepare('
                SELECT id FROM screen_protectors
                WHERE is_active = 1 AND barcode NOT LIKE \'ZS-%\' AND type_slug = ?
                  AND width_mm BETWEEN ? AND ? AND height_mm BETWEEN ? AND ?
                ORDER BY ABS(width_mm - ?) + ABS(height_mm - ?) ASC LIMIT 1
            ');
            $add = $pdo->prepare('UPDATE screen_protectors SET quantity = quantity + ?, updated_at = ? WHERE id = ?');
            $off = $pdo->prepare('UPDATE screen_protectors SET is_active = 0, updated_at = ? WHERE id = ?');
            $now = date('Y-m-d H:i:s');
            $pdo->exec("UPDATE screen_protectors SET is_active = 0 WHERE is_active = 1 AND type_slug NOT IN ('hd', 'banda', 'privacy') AND barcode NOT LIKE 'ZS-%'");
            $old = $pdo->query("SELECT id, type_slug, width_mm, height_mm, quantity FROM screen_protectors WHERE is_active = 1 AND barcode NOT LIKE 'ZS-%'")->fetchAll();
            foreach ($old as $row) {
                $type = (string)$row['type_slug'];
                $qty = (int)$row['quantity'];
                $w = (float)$row['width_mm'];
                $h = (float)$row['height_mm'];
                if ($qty > 0 && in_array($type, $keep, true) && $w > 0 && $h > 0) {
                    $find->execute([$type, $w - 1.2, $w + 1.2, $h - 1.8, $h + 1.8, $w, $h]);
                    $target = (int)$find->fetchColumn();
                    if ($target > 0) {
                        $add->execute([$qty, $now, $target]);
                    }
                }
                $off->execute([$now, (int)$row['id']]);
            }
        } catch (Throwable $e) {
            // nothing to retire
        }
    }

    private static function seedScreenCatalog(PDO $pdo): void
    {
        try {
            $pdo->query("SELECT COUNT(*) FROM `device_models`")->fetchColumn();
        } catch (Throwable $e) {
            return;
        }
        $sqlFile = APP_ROOT . '/database/asly_store.sql';
        if (!is_file($sqlFile)) {
            return;
        }
        // Cheap marker: skip the whole replay once the shipped catalog is in place and intact.
        try {
            $marker = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'screens_catalog_seeded'")->fetchColumn();
            if ((string)$marker === '3') {
                $models = (int)$pdo->query('SELECT COUNT(*) FROM `device_models`')->fetchColumn();
                $screens = (int)$pdo->query('SELECT COUNT(*) FROM `screen_protectors`')->fetchColumn();
                $types = (int)$pdo->query('SELECT COUNT(*) FROM `screen_types`')->fetchColumn();
                if ($models >= 2000 && $screens > 0 && $types === 3) {
                    return;
                }
            }
        } catch (Throwable $e) {
            // settings table missing: fall through to the replay
        }
        // already fully shipped (fresh installs come from the dump): remember it and skip the replay
        $pricesReady = false;
        try {
            $haveModels = (int)$pdo->query('SELECT COUNT(*) FROM `device_models`')->fetchColumn();
            $haveScreens = (int)$pdo->query('SELECT COUNT(*) FROM `screen_protectors`')->fetchColumn();
            $haveTypes = (int)$pdo->query('SELECT COUNT(*) FROM `screen_types`')->fetchColumn();
            $haveLinks = (int)$pdo->query('SELECT COUNT(*) FROM `screen_compatibility`')->fetchColumn();
            $pricesReady = (int)$pdo->query('SELECT COUNT(*) FROM `screen_protectors` WHERE `sell_price` > 0')->fetchColumn() > 0;
            if ($haveModels >= 2000 && $haveScreens > 0 && $haveTypes === 3 && $haveLinks > 0 && $pricesReady) {
                $pdo->exec("DELETE FROM settings WHERE setting_key = 'screens_catalog_seeded'");
                $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('screens_catalog_seeded', '3')");
                return;
            }
        } catch (Throwable $e) {
            // fall through to the replay
        }
        self::retireLegacyCatalog($pdo);
        $raw = (string)file_get_contents($sqlFile);
        // the shipped dump is minified into one line; statements are split on ';' only
        $statements = preg_split('/;\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $inserted = false;
        $modelCount = (int)$pdo->query('SELECT COUNT(*) FROM `device_models`')->fetchColumn();
        $wanted = [
            'INSERT IGNORE INTO `screen_types`',
            'INSERT IGNORE INTO `screen_type_prices`',
            'INSERT IGNORE INTO `device_models`',
            'INSERT IGNORE INTO `screen_protectors`',
            'INSERT IGNORE INTO `screen_compatibility`',
            'INSERT OR IGNORE INTO `screen_compatibility`',
            'INSERT IGNORE INTO `products`',
            'UPDATE `screen_protectors`',
            'UPDATE `products`',
            'SET SQL_MODE',
        ];
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }
            $ok = false;
            foreach ($wanted as $prefix) {
                if (strpos($stmt, $prefix) === 0) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            if (DB_DRIVER === 'sqlite') {
                $stmt = preg_replace('/^INSERT\s+IGNORE\s+INTO/i', 'INSERT OR IGNORE INTO', $stmt) ?: $stmt;
                $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT OR IGNORE INTO', $stmt) ?: $stmt;
            }
            $before = (int)$pdo->query('SELECT COUNT(*) FROM `device_models`')->fetchColumn();
            try {
                $pdo->exec($stmt);
            } catch (Throwable $e) {
                if (DB_DRIVER !== 'sqlite' && stripos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
            $after = (int)$pdo->query('SELECT COUNT(*) FROM `device_models`')->fetchColumn();
            if ($after > $before) {
                $inserted = true;
            }
        }

        try {
            if ($inserted || $modelCount >= 2000) {
                $pdo->exec("DELETE FROM settings WHERE setting_key = 'screens_catalog_seeded'");
                $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('screens_catalog_seeded', '3')");
            }
        } catch (Throwable $e) {
            // marker is an optimisation only
        }
    }

    public static function importMysqlDump(PDO $pdo): void
    {
        $sqlFile = APP_ROOT . '/database/asly_store.sql';
        if (!is_file($sqlFile)) {
            return;
        }
        $sql = (string)file_get_contents($sqlFile);
        $lines = preg_split('/\r\n|\r|\n/', $sql) ?: [];
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $cleanSql = implode("\n", $cleanLines);
        $statements = preg_split('/;\s*/', $cleanSql) ?: [];
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || stripos($stmt, 'CREATE DATABASE') === 0 || stripos($stmt, 'USE ') === 0) {
                continue;
            }
            $pdo->exec($stmt);
        }
    }

    private static function initializeSqliteSchema(PDO $pdo): void
    {
        self::ensureScreensSqlite($pdo);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'staff',
                phone TEXT DEFAULT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT DEFAULT NULL
            );
            CREATE TABLE IF NOT EXISTS contact_channels (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                channel_type TEXT NOT NULL DEFAULT 'phone',
                label TEXT NOT NULL,
                value TEXT NOT NULL,
                url TEXT DEFAULT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                barcode TEXT NOT NULL UNIQUE,
                code_type TEXT NOT NULL DEFAULT 'qr',
                name TEXT NOT NULL,
                name_en TEXT DEFAULT NULL,
                category TEXT NOT NULL DEFAULT 'laptops',
                condition_status TEXT NOT NULL DEFAULT 'new',
                brand TEXT DEFAULT NULL,
                screen_type TEXT DEFAULT NULL,
                width_mm REAL DEFAULT NULL,
                height_mm REAL DEFAULT NULL,
                specs TEXT DEFAULT NULL,
                specs_en TEXT DEFAULT NULL,
                description TEXT DEFAULT NULL,
                description_en TEXT DEFAULT NULL,
                image_path TEXT DEFAULT NULL,
                secondary_image_path TEXT DEFAULT NULL,
                purchase_price REAL NOT NULL DEFAULT 0,
                sell_price REAL NOT NULL DEFAULT 0,
                min_sell_price REAL NOT NULL DEFAULT 0,
                quantity INTEGER NOT NULL DEFAULT 1,
                low_stock_threshold INTEGER NOT NULL DEFAULT 2,
                warranty_default TEXT DEFAULT 'ضمان الفحص والتجربة - أصلي تك ستور',
                show_on_landing INTEGER NOT NULL DEFAULT 1,
                is_featured INTEGER NOT NULL DEFAULT 0,
                created_by INTEGER DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS invoices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_number TEXT NOT NULL UNIQUE,
                public_token TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL DEFAULT 'عميل نقدي',
                customer_phone TEXT DEFAULT NULL,
                total_amount REAL NOT NULL DEFAULT 0,
                total_cost REAL NOT NULL DEFAULT 0,
                total_profit REAL NOT NULL DEFAULT 0,
                payment_method TEXT NOT NULL DEFAULT 'cash',
                notes TEXT DEFAULT NULL,
                sold_by INTEGER NOT NULL,
                sold_by_name TEXT NOT NULL,
                sale_date TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL,
                product_id INTEGER DEFAULT NULL,
                product_name TEXT NOT NULL,
                product_barcode TEXT DEFAULT NULL,
                serial_imei TEXT DEFAULT NULL,
                warranty_text TEXT DEFAULT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                unit_purchase_price REAL NOT NULL DEFAULT 0,
                unit_list_price REAL NOT NULL DEFAULT 0,
                unit_min_price REAL NOT NULL DEFAULT 0,
                unit_sold_price REAL NOT NULL DEFAULT 0,
                subtotal REAL NOT NULL DEFAULT 0,
                item_profit REAL NOT NULL DEFAULT 0,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS maintenance_tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_code TEXT NOT NULL UNIQUE,
                qr_token TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL,
                customer_phone TEXT NOT NULL,
                customer_alt_phone TEXT DEFAULT NULL,
                customer_email TEXT DEFAULT NULL,
                customer_details TEXT DEFAULT NULL,
                device_type TEXT NOT NULL DEFAULT 'mobile',
                device_model TEXT NOT NULL,
                serial_imei TEXT DEFAULT NULL,
                device_specs TEXT DEFAULT NULL,
                device_passcode TEXT DEFAULT NULL,
                physical_condition TEXT DEFAULT NULL,
                problem_description TEXT NOT NULL,
                estimated_cost REAL NOT NULL DEFAULT 0,
                parts_cost REAL NOT NULL DEFAULT 0,
                deposit_paid REAL NOT NULL DEFAULT 0,
                current_stage TEXT NOT NULL DEFAULT 'received',
                unread_customer_notes INTEGER NOT NULL DEFAULT 0,
                received_by INTEGER DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS maintenance_notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL,
                stage_key TEXT NOT NULL,
                sender_type TEXT NOT NULL DEFAULT 'admin',
                sender_name TEXT NOT NULL,
                note_text TEXT NOT NULL,
                is_stage_change INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                FOREIGN KEY (ticket_id) REFERENCES maintenance_tickets(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS maintenance_revenues (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_code_ref TEXT NOT NULL,
                device_category TEXT NOT NULL DEFAULT 'mobile',
                amount_paid REAL NOT NULL DEFAULT 0,
                parts_cost REAL NOT NULL DEFAULT 0,
                net_profit REAL NOT NULL DEFAULT 0,
                payment_method TEXT NOT NULL DEFAULT 'cash',
                delivered_by INTEGER DEFAULT NULL,
                delivered_by_name TEXT DEFAULT NULL,
                revenue_date TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER DEFAULT NULL,
                recipient_email TEXT NOT NULL,
                subject TEXT NOT NULL,
                stage_key TEXT DEFAULT NULL,
                status TEXT NOT NULL DEFAULT 'logged',
                body_preview TEXT DEFAULT NULL,
                error_message TEXT DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS device_models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                brand TEXT NOT NULL,
                model_name TEXT NOT NULL,
                model_name_ar TEXT DEFAULT NULL,
                search_key TEXT NOT NULL DEFAULT '',
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                UNIQUE (brand, model_name)
            );
            CREATE TABLE IF NOT EXISTS shelves (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                shelf_code TEXT NOT NULL UNIQUE,
                label TEXT DEFAULT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS screen_protectors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                barcode TEXT NOT NULL UNIQUE,
                code_type TEXT NOT NULL DEFAULT 'qr',
                product_id INTEGER DEFAULT NULL,
                name TEXT NOT NULL,
                name_en TEXT DEFAULT NULL,
                quality TEXT DEFAULT 'standard',
                shelf_code TEXT DEFAULT NULL,
                drawer_label TEXT DEFAULT NULL,
                purchase_price REAL NOT NULL DEFAULT 0,
                sell_price REAL NOT NULL DEFAULT 0,
                min_sell_price REAL NOT NULL DEFAULT 0,
                quantity INTEGER NOT NULL DEFAULT 0,
                low_stock_threshold INTEGER NOT NULL DEFAULT 3,
                notes TEXT DEFAULT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_by INTEGER DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
            CREATE TABLE IF NOT EXISTS screen_compatibility (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                screen_id INTEGER NOT NULL,
                model_id INTEGER NOT NULL,
                link_type TEXT NOT NULL DEFAULT 'manual',
                UNIQUE (screen_id, model_id)
            );
            CREATE TABLE IF NOT EXISTS screen_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                screen_id INTEGER NOT NULL,
                movement_type TEXT NOT NULL DEFAULT 'adjust',
                qty_before INTEGER NOT NULL DEFAULT 0,
                qty_after INTEGER NOT NULL DEFAULT 0,
                note TEXT DEFAULT NULL,
                user_name TEXT DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
            );
        ");

        $sqlFile = APP_ROOT . '/database/asly_store.sql';
        if (is_file($sqlFile)) {
            $sql = (string)file_get_contents($sqlFile);
            $statements = preg_split('/;\s*/', $sql) ?: [];
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (stripos($stmt, 'INSERT IGNORE INTO') === 0) {
                    $sqliteStmt = preg_replace('/^INSERT\s+IGNORE\s+INTO/i', 'INSERT OR IGNORE INTO', $stmt);
                    if ($sqliteStmt) {
                        $pdo->exec($sqliteStmt);
                    }
                } elseif (stripos($stmt, 'INSERT INTO') === 0) {
                    $sqliteStmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT OR IGNORE INTO', $stmt);
                    if ($sqliteStmt) {
                        $pdo->exec($sqliteStmt);
                    }
                }
            }
        }
    }
}
