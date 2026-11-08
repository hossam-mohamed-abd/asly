<?php
/**
 * ASLY | أصلي TECH STORE
 * Public API: Maintenance QR Lookup & Customer Stage Comments
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'track') {
    $code = strtoupper(trim((string)($_GET['code'] ?? '')));
    if ($code === '') {
        jsonResponse(['ok' => false, 'error' => 'يرجى إدخال كود تذكرة الصيانة.'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT id, ticket_code, qr_token, customer_name, customer_phone,
               device_type, device_model, device_specs, physical_condition,
               problem_description, estimated_cost, deposit_paid,
               current_stage, created_at, updated_at
        FROM maintenance_tickets
        WHERE UPPER(ticket_code) = ?
        LIMIT 1
    ");
    $stmt->execute([$code]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        // Check if this ticket was already delivered & wiped
        $revStmt = $pdo->prepare("SELECT ticket_code_ref, revenue_date FROM maintenance_revenues WHERE UPPER(ticket_code_ref) = ? LIMIT 1");
        $revStmt->execute([$code]);
        $deliveredRef = $revStmt->fetch();
        if ($deliveredRef) {
            jsonResponse([
                'ok' => true,
                'delivered_and_wiped' => true,
                'ticket_code' => $deliveredRef['ticket_code_ref'],
                'delivered_date' => $deliveredRef['revenue_date'],
                'message' => 'تم تسليم هذا الجهاز للعميل بنجاح وإغلاق وحذف كافة البيانات التفصيلية للتذكرة حفاظاً على الخصوصية.',
            ]);
        }
        jsonResponse(['ok' => false, 'error' => 'لم يتم العثور على تذكرة صيانة بهذا الكود. يرجى التأكد من الكود المطبوع على الإيصال.'], 404);
    }

    $notesStmt = $pdo->prepare("
        SELECT id, stage_key, sender_type, sender_name, note_text, is_stage_change, created_at
        FROM maintenance_notes
        WHERE ticket_id = ?
        ORDER BY id ASC
    ");
    $notesStmt->execute([(int)$ticket['id']]);
    $notes = $notesStmt->fetchAll();

    // Mask phone number for public privacy (only last 3 digits shown)
    $rawPhone = (string)$ticket['customer_phone'];
    $maskedPhone = strlen($rawPhone) > 4
        ? str_repeat('*', strlen($rawPhone) - 3) . substr($rawPhone, -3)
        : $rawPhone;

    unset($ticket['qr_token'], $ticket['customer_phone']);
    $ticket['masked_phone'] = $maskedPhone;

    jsonResponse([
        'ok' => true,
        'delivered_and_wiped' => false,
        'ticket' => $ticket,
        'stages' => getMaintenanceStages(),
        'notes' => $notes,
    ]);
}

if ($method === 'POST') {
    $input = readJsonInput();
    $postAction = $input['action'] ?? $action;

    if ($postAction === 'customer_note') {
        $code = strtoupper(trim((string)($input['ticket_code'] ?? '')));
        $token = trim((string)($input['qr_token'] ?? ''));
        $phoneVerify = preg_replace('/\D+/', '', (string)($input['phone_verify'] ?? '')) ?? '';
        $stageKey = trim((string)($input['stage_key'] ?? ''));
        $noteText = trim((string)($input['note_text'] ?? ''));

        if ($code === '' || $noteText === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى كتابة الملاحظة أو الاستفسار أولاً.'], 400);
        }
        if (mb_strlen($noteText) > 1000) {
            jsonResponse(['ok' => false, 'error' => 'النص طويل جداً (الحد الأقصى 1000 حرف).'], 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE UPPER(ticket_code) = ? LIMIT 1");
        $stmt->execute([$code]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            jsonResponse(['ok' => false, 'error' => 'التذكرة غير موجودة أو تم تسليم الجهاز بالفعل.'], 404);
        }

        // Verify ownership either via QR token (from scanned QR link) or matching customer phone last 4 digits / full phone
        $ticketDigits = preg_replace('/\D+/', '', (string)$ticket['customer_phone']) ?? '';
        $tokenValid = ($token !== '' && hash_equals((string)$ticket['qr_token'], $token));
        $phoneValid = ($phoneVerify !== '' && strlen($phoneVerify) >= 4 && substr($ticketDigits, -4) === substr($phoneVerify, -4));

        if (!$tokenValid && !$phoneValid) {
            jsonResponse(['ok' => false, 'error' => 'لتأكيد هويتك كصاحب الجهاز، يرجى إدخال آخر 4 أرقام من رقم هاتفك المسجل بالتذكرة.'], 403);
        }

        $stages = getMaintenanceStages();
        if (!isset($stages[$stageKey])) {
            $stageKey = (string)$ticket['current_stage'];
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare("
            INSERT INTO maintenance_notes (ticket_id, stage_key, sender_type, sender_name, note_text, is_stage_change, created_at)
            VALUES (?, ?, 'customer', ?, ?, 0, ?)
        ");
        $ins->execute([
            (int)$ticket['id'],
            $stageKey,
            (string)$ticket['customer_name'],
            $noteText,
            $now,
        ]);

        $upd = $pdo->prepare("
            UPDATE maintenance_tickets
            SET unread_customer_notes = unread_customer_notes + 1, updated_at = ?
            WHERE id = ?
        ");
        $upd->execute([$now, (int)$ticket['id']]);

        jsonResponse([
            'ok' => true,
            'message' => 'تم إرسال ملاحظتك لمهندس الصيانة بنجاح!',
            'note' => [
                'stage_key' => $stageKey,
                'sender_type' => 'customer',
                'sender_name' => $ticket['customer_name'],
                'note_text' => $noteText,
                'created_at' => $now,
            ],
        ]);
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
