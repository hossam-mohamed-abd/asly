<?php
/**
 * ASLY | أصلي TECH STORE
 * Core Helpers, Formatters, Settings & Constants
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function readJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST;
}

function getAllSettings(PDO $pdo): array
{
    $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $map[$r['setting_key']] = (string)($r['setting_value'] ?? '');
    }
    return $map;
}

function saveSetting(PDO $pdo, string $key, string $value): void
{
    $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $check->execute([$key]);
    if ((int)$check->fetchColumn() > 0) {
        $upd = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $upd->execute([$value, $key]);
    } else {
        $ins = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $ins->execute([$key, $value]);
    }
}

function getActiveContactChannels(PDO $pdo, bool $includeInactive = false): array
{
    if ($includeInactive) {
        return $pdo->query("SELECT * FROM contact_channels ORDER BY sort_order ASC, id ASC")->fetchAll();
    }
    return $pdo->query("SELECT * FROM contact_channels WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
}

function getCategoryMeta(): array
{
    return [
        'laptops'     => ['ar' => 'لابتوب', 'en' => 'Laptops', 'prefix' => 'LAP'],
        'mobiles'     => ['ar' => 'هواتف ذكية', 'en' => 'Mobiles', 'prefix' => 'MOB'],
        'accessories' => ['ar' => 'إكسسوارات', 'en' => 'Accessories', 'prefix' => 'ACC'],
        'wearables'   => ['ar' => 'أجهزة قابلة للارتداء', 'en' => 'Wearables', 'prefix' => 'WRB'],
    ];
}

function getConditionMeta(): array
{
    return [
        'new'      => ['ar' => 'جديد متبرشم', 'badge' => 'new'],
        'open_box' => ['ar' => 'كسر زيرو', 'badge' => 'open_box'],
        'used'     => ['ar' => 'مستعمل فرز أول', 'badge' => 'used'],
    ];
}

function getMaintenanceStages(): array
{
    return [
        'received' => [
            'order' => 1,
            'title' => 'تم استلام الجهاز',
            'desc'  => 'تم تسجيل الجهاز واستلامه بمركز الصيانة لبدء الفحص الفني.',
        ],
        'diagnosing' => [
            'order' => 2,
            'title' => 'قيد الفحص والتشخيص',
            'desc'  => 'يقوم مهندسو المركز حالياً بفحص الجهاز وتحديد العطل بدقة.',
        ],
        'waiting_approval' => [
            'order' => 3,
            'title' => 'بانتظار موافقة العميل / قطع الغيار',
            'desc'  => 'تم تحديد متطلبات الصيانة والتكلفة وفي انتظار التأكيد أو تجهيز القطعة الأصلية.',
        ],
        'repairing' => [
            'order' => 4,
            'title' => 'جاري الإصلاح والصيانة',
            'desc'  => 'جاري العمل الفعلي على إصلاح العطل بأحدث أجهزة الصيانة.',
        ],
        'testing' => [
            'order' => 5,
            'title' => 'الفحص والاختبار النهائي',
            'desc'  => 'تم الإصلاح وجاري اختبار كفاءة الجهاز تحت الضغط لضمان الجودة.',
        ],
        'ready' => [
            'order' => 6,
            'title' => 'جاهز للاستلام بالمركز',
            'desc'  => 'اكتملت الصيانة بنجاح والجهاز جاهز الآن لتشريفكم بالاستلام.',
        ],
        'delivered' => [
            'order' => 7,
            'title' => 'تم التسليم النهائي للعميل',
            'desc'  => 'تم تسليم الجهاز للعميل وإغلاق وحذف سجل البيانات الشخصية للتذكرة.',
        ],
    ];
}

function generateUniqueProductCode(PDO $pdo, string $category = 'laptops'): string
{
    $cats = getCategoryMeta();
    $prefix = $cats[$category]['prefix'] ?? 'PRD';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE barcode = ?");
    do {
        $code = 'ASLY-' . $prefix . '-' . random_int(100000, 999999);
        $stmt->execute([$code]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $code;
}

function generateUniqueTicketCode(PDO $pdo): string
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM maintenance_tickets WHERE ticket_code = ?");
    $stmtRev = $pdo->prepare("SELECT COUNT(*) FROM maintenance_revenues WHERE ticket_code_ref = ?");
    do {
        $code = 'MNT-' . date('y') . '-' . random_int(10000, 99999);
        $stmt->execute([$code]);
        $exists1 = (int)$stmt->fetchColumn();
        $stmtRev->execute([$code]);
        $exists2 = (int)$stmtRev->fetchColumn();
    } while ($exists1 > 0 || $exists2 > 0);
    return $code;
}

function generateInvoiceNumber(PDO $pdo): string
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
    do {
        $num = 'ASLY-' . date('Ymd') . '-' . random_int(1000, 9999);
        $stmt->execute([$num]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $num;
}

function formatMoney(float|int|string $amount): string
{
    return number_format((float)$amount, 2, '.', ',') . ' ج.م';
}

function normalizeWhatsappNumber(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, '00') === 0) {
        $digits = substr($digits, 2);
    }
    if (strpos($digits, '01') === 0 && strlen($digits) === 11) {
        return '2' . $digits; // Egypt +20
    }
    return $digits;
}

function handleProductImageUpload(array $file): ?string
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0775, true);
    }
    $maxSize = 8 * 1024 * 1024; // 8 MB
    if (($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException('حجم الصورة يجب ألا يتجاوز 8 ميجابايت.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('صيغة الصورة غير مدعومة. يرجى رفع JPG أو PNG أو WebP.');
    }
    $filename = 'prd_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest = UPLOADS_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        // Fallback copy for environments where move_uploaded_file behaves across temp mounts
        if (!@copy($file['tmp_name'], $dest)) {
            throw new RuntimeException('تعذر حفظ الصورة المرفوعة.');
        }
    }
    return 'uploads/products/' . $filename;
}
