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
            }
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
            }
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

    private static function ensureProductColumnsSqlite(PDO $pdo): void
    {
        $cols = [];
        foreach ($pdo->query("PRAGMA table_info(products)")->fetchAll() as $c) {
            $cols[$c['name']] = true;
        }
        $alterations = [
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
