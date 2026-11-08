<?php
/**
 * ASLY | أصلي TECH STORE
 * Admin-Only Settings, Brand Identity, Dynamic Contact Channels & SMTP Email Config API
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

requireAdminApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'all';
    if ($action === 'email_logs') {
        $logs = $pdo->query("SELECT id, ticket_id, recipient_email, subject, stage_key, status, error_message, created_at, body_preview FROM email_logs ORDER BY id DESC LIMIT 40")->fetchAll();
        jsonResponse(['ok' => true, 'logs' => $logs]);
    }

    $settings = getAllSettings($pdo);
    $channels = getActiveContactChannels($pdo, true);
    jsonResponse([
        'ok' => true,
        'settings' => $settings,
        'channels' => $channels,
    ]);
}

if ($method === 'POST') {
    requireCsrfJson();
    $input = readJsonInput();
    $action = $input['action'] ?? 'save_settings';

    if ($action === 'save_settings') {
        $allowedKeys = [
            'store_name_ar', 'store_name_en', 'store_tagline', 'founders_line',
            'primary_whatsapp', 'primary_phone', 'secondary_phone',
            'store_address', 'working_hours', 'google_maps_url',
            'invoice_footer_note', 'public_base_url',
            'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name',
        ];

        $incoming = $input['settings'] ?? $input;
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $incoming)) {
                $val = trim((string)$incoming[$key]);
                if ($key === 'primary_whatsapp' && $val !== '') {
                    $val = normalizeWhatsappNumber($val);
                }
                saveSetting($pdo, $key, $val);
            }
        }
        jsonResponse(['ok' => true, 'message' => 'تم حفظ إعدادات الهوية والتواصل والبريد بنجاح!']);
    }

    if ($action === 'save_channel') {
        $id = (int)($input['id'] ?? 0);
        $type = trim((string)($input['channel_type'] ?? 'phone'));
        $label = trim((string)($input['label'] ?? ''));
        $value = trim((string)($input['value'] ?? ''));
        $url = trim((string)($input['url'] ?? ''));
        $sortOrder = (int)($input['sort_order'] ?? 0);
        $isActive = isset($input['is_active']) ? ((int)$input['is_active'] ? 1 : 0) : 1;

        if ($label === '' || $value === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى إدخال عنوان الوسيلة والرقم أو الرابط.'], 400);
        }

        // Auto-build smart clickable URL if left blank
        if ($url === '') {
            if ($type === 'phone') {
                $url = 'tel:' . preg_replace('/\s+/', '', $value);
            } elseif ($type === 'whatsapp') {
                $url = 'https://wa.me/' . normalizeWhatsappNumber($value);
            } elseif ($type === 'email') {
                $url = 'mailto:' . $value;
            } elseif (preg_match('#^https?://#i', $value)) {
                $url = $value;
            }
        }

        if ($id > 0) {
            $upd = $pdo->prepare("
                UPDATE contact_channels
                SET channel_type = ?, label = ?, value = ?, url = ?, sort_order = ?, is_active = ?
                WHERE id = ?
            ");
            $upd->execute([$type, $label, $value, $url, $sortOrder, $isActive, $id]);
            jsonResponse(['ok' => true, 'message' => 'تم تعديل وسيلة التواصل بنجاح.']);
        } else {
            $ins = $pdo->prepare("
                INSERT INTO contact_channels (channel_type, label, value, url, sort_order, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$type, $label, $value, $url, $sortOrder, $isActive, date('Y-m-d H:i:s')]);
            jsonResponse(['ok' => true, 'message' => 'تمت إضافة وسيلة التواصل الجديدة وستظهر فوراً في الموقع!']);
        }
    }

    if ($action === 'delete_channel') {
        $id = (int)($input['id'] ?? 0);
        $pdo->prepare("DELETE FROM contact_channels WHERE id = ?")->execute([$id]);
        jsonResponse(['ok' => true, 'message' => 'تم حذف وسيلة التواصل.']);
    }

    if ($action === 'test_email') {
        $to = trim((string)($input['to_email'] ?? ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['ok' => false, 'error' => 'يرجى إدخال بريد إلكتروني صحيح للتجربة.'], 400);
        }
        $demoTicket = [
            'id' => null,
            'ticket_code' => 'MNT-TEST-01',
            'qr_token' => 'demo_token',
            'customer_name' => 'عميل تجريبي - أصلي تك ستور',
            'customer_email' => $to,
            'device_model' => 'MacBook Pro 16 M3 Max (اختبار البريد)',
            'problem_description' => 'رسالة اختبارية للتأكد من إعدادات إشعارات مراحل الصيانة',
            'estimated_cost' => 1200,
            'deposit_paid' => 300,
        ];
        $res = AslyMailer::sendMaintenanceStageEmail($pdo, $demoTicket, 'diagnosing', 'هذه رسالة اختبارية من لوحة تحكم أصلي ASLY TECH STORE للتأكد من وصول تنبيهات المراحل للعملاء.', true);
        jsonResponse([
            'ok' => true,
            'result' => $res,
            'message' => $res['status'] === 'sent_smtp'
                ? 'تم إرسال البريد التجريبي عبر SMTP بنجاح!'
                : ($res['status'] === 'smtp_error'
                    ? ('تعذر الإرسال عبر SMTP: ' . $res['error'] . ' (تم حفظ المعاينة في سجل البريد)')
                    : 'تم توليد وحفظ البريد التجريبي في سجل البريد بنجاح (لتفعيل الإرسال الفعلي للإنترنت فعّل SMTP وأدخل بيانات الحساب).'),
        ]);
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
