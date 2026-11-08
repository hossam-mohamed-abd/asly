<?php
/**
 * ASLY | أصلي TECH STORE
 * Maintenance Management API:
 * - Ticket Registration + QR Code Generation
 * - Stage Updates + Automatic Branded HTML Email Notifications
 * - Stage-by-Stage Notes (Admin & Customer)
 * - Final Delivery Handover -> Permanent Data Wipe (keeping ONLY financial revenue!)
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

$user = requireLoginApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$settings = getAllSettings($pdo);
$stages = getMaintenanceStages();

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'details') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            jsonResponse(['ok' => false, 'error' => 'التذكرة غير موجودة أو تم تسليم الجهاز بالفعل.'], 404);
        }

        // Clear unread customer notes counter when opened
        if ((int)$ticket['unread_customer_notes'] > 0) {
            $pdo->prepare("UPDATE maintenance_tickets SET unread_customer_notes = 0 WHERE id = ?")->execute([$id]);
            $ticket['unread_customer_notes'] = 0;
        }

        $notesStmt = $pdo->prepare("SELECT * FROM maintenance_notes WHERE ticket_id = ? ORDER BY id ASC");
        $notesStmt->execute([$id]);
        $notes = $notesStmt->fetchAll();

        $ticket['tracking_url'] = publicAbsoluteUrl(
            'track.php?code=' . urlencode((string)$ticket['ticket_code']) . '&t=' . urlencode((string)$ticket['qr_token']),
            $settings['public_base_url'] ?? null
        );
        $ticket['stage_title'] = $stages[$ticket['current_stage']]['title'] ?? $ticket['current_stage'];

        if ($user['role'] !== 'admin') {
            unset($ticket['parts_cost']);
        }

        jsonResponse([
            'ok' => true,
            'ticket' => $ticket,
            'notes' => $notes,
            'stages' => $stages,
        ]);
    }

    // List active maintenance tickets
    $stageFilter = trim((string)($_GET['stage'] ?? ''));
    $q = trim((string)($_GET['q'] ?? ''));

    $where = [];
    $params = [];
    if ($stageFilter !== '' && $stageFilter !== 'all') {
        $where[] = 'current_stage = ?';
        $params[] = $stageFilter;
    }
    if ($q !== '') {
        $where[] = '(ticket_code LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ? OR device_model LIKE ? OR serial_imei LIKE ?)';
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }

    $sql = 'SELECT * FROM maintenance_tickets';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY unread_customer_notes DESC, updated_at DESC, id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

    foreach ($tickets as &$t) {
        $t['id'] = (int)$t['id'];
        $t['estimated_cost'] = (float)$t['estimated_cost'];
        $t['deposit_paid'] = (float)$t['deposit_paid'];
        $t['unread_customer_notes'] = (int)$t['unread_customer_notes'];
        $t['stage_title'] = $stages[$t['current_stage']]['title'] ?? $t['current_stage'];
        $t['tracking_url'] = publicAbsoluteUrl(
            'track.php?code=' . urlencode((string)$t['ticket_code']) . '&t=' . urlencode((string)$t['qr_token']),
            $settings['public_base_url'] ?? null
        );
        if ($user['role'] !== 'admin') {
            unset($t['parts_cost']);
        } else {
            $t['parts_cost'] = (float)$t['parts_cost'];
        }
    }
    unset($t);

    jsonResponse(['ok' => true, 'tickets' => $tickets, 'stages' => $stages]);
}

if ($method === 'POST') {
    requireCsrfJson();
    $input = readJsonInput();
    $action = $input['action'] ?? '';

    // 1. Register New Maintenance Device + Generate QR + Send Stage 1 Email
    if ($action === 'create') {
        $customerName = trim((string)($input['customer_name'] ?? ''));
        $customerPhone = trim((string)($input['customer_phone'] ?? ''));
        $customerAltPhone = trim((string)($input['customer_alt_phone'] ?? ''));
        $customerEmail = trim((string)($input['customer_email'] ?? ''));
        $customerDetails = trim((string)($input['customer_details'] ?? ''));
        $deviceType = trim((string)($input['device_type'] ?? 'mobile'));
        $deviceModel = trim((string)($input['device_model'] ?? ''));
        $serialImei = trim((string)($input['serial_imei'] ?? ''));
        $deviceSpecs = trim((string)($input['device_specs'] ?? ''));
        $devicePasscode = trim((string)($input['device_passcode'] ?? ''));
        $physicalCondition = trim((string)($input['physical_condition'] ?? ''));
        $problemDesc = trim((string)($input['problem_description'] ?? ''));
        $estimatedCost = round(max(0, (float)($input['estimated_cost'] ?? 0)), 2);
        $partsCost = $user['role'] === 'admin' ? round(max(0, (float)($input['parts_cost'] ?? 0)), 2) : 0.0;
        $depositPaid = round(max(0, (float)($input['deposit_paid'] ?? 0)), 2);
        $initialNote = trim((string)($input['initial_note'] ?? '')) ?: 'تم استلام الجهاز وتسجيل بيانات الفحص الظاهري بمركز أصلي تك ستور.';

        if ($customerName === '' || $customerPhone === '' || $deviceModel === '' || $problemDesc === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى استكمال البيانات الأساسية (اسم العميل، الهاتف، موديل الجهاز، ووصف العطل).'], 400);
        }
        if ($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['ok' => false, 'error' => 'صيغة البريد الإلكتروني للعميل غير صحيحة.'], 400);
        }

        $ticketCode = generateUniqueTicketCode($pdo);
        $qrToken = bin2hex(random_bytes(20));
        $now = date('Y-m-d H:i:s');

        $ins = $pdo->prepare("
            INSERT INTO maintenance_tickets (
                ticket_code, qr_token,
                customer_name, customer_phone, customer_alt_phone, customer_email, customer_details,
                device_type, device_model, serial_imei, device_specs, device_passcode,
                physical_condition, problem_description,
                estimated_cost, parts_cost, deposit_paid,
                current_stage, unread_customer_notes, received_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'received', 0, ?, ?, ?)
        ");
        $ins->execute([
            $ticketCode, $qrToken,
            $customerName, $customerPhone, $customerAltPhone, $customerEmail, $customerDetails,
            $deviceType, $deviceModel, $serialImei, $deviceSpecs, $devicePasscode,
            $physicalCondition, $problemDesc,
            $estimatedCost, $partsCost, $depositPaid,
            $user['id'], $now, $now,
        ]);
        $ticketId = (int)$pdo->lastInsertId();

        $noteIns = $pdo->prepare("
            INSERT INTO maintenance_notes (ticket_id, stage_key, sender_type, sender_name, note_text, is_stage_change, created_at)
            VALUES (?, 'received', 'admin', ?, ?, 1, ?)
        ");
        $noteIns->execute([$ticketId, $user['full_name'], $initialNote, $now]);

        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch();

        $emailRes = ['sent' => false, 'status' => 'skipped'];
        if ($customerEmail !== '') {
            $emailRes = AslyMailer::sendMaintenanceStageEmail($pdo, $ticket, 'received', $initialNote, true);
        }

        $trackingUrl = publicAbsoluteUrl(
            'track.php?code=' . urlencode($ticketCode) . '&t=' . urlencode($qrToken),
            $settings['public_base_url'] ?? null
        );
        $ticket['tracking_url'] = $trackingUrl;
        $ticket['stage_title'] = $stages['received']['title'];

        jsonResponse([
            'ok' => true,
            'message' => 'تم تسجيل جهاز الصيانة وتوليد رمز الـ QR للعميل بنجاح!',
            'ticket' => $ticket,
            'email_notification' => $emailRes,
        ]);
    }

    // 2. Update Maintenance Stage + Optional Note + Email Customer
    if ($action === 'update_stage') {
        $id = (int)($input['id'] ?? 0);
        $newStage = trim((string)($input['stage_key'] ?? ''));
        $noteText = trim((string)($input['note_text'] ?? ''));
        $estimatedCost = isset($input['estimated_cost']) ? round(max(0, (float)$input['estimated_cost']), 2) : null;
        $partsCost = ($user['role'] === 'admin' && isset($input['parts_cost'])) ? round(max(0, (float)$input['parts_cost']), 2) : null;

        if (!isset($stages[$newStage]) || $newStage === 'delivered') {
            jsonResponse(['ok' => false, 'error' => 'المرحلة المختارة غير صالحة (للتسليم النهائي استخدم زر تسليم الجهاز وإغلاق التذكرة).'], 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            jsonResponse(['ok' => false, 'error' => 'التذكرة غير موجودة.'], 404);
        }

        $now = date('Y-m-d H:i:s');
        $finalEst = $estimatedCost !== null ? $estimatedCost : (float)$ticket['estimated_cost'];
        $finalParts = $partsCost !== null ? $partsCost : (float)$ticket['parts_cost'];

        $upd = $pdo->prepare("
            UPDATE maintenance_tickets
            SET current_stage = ?, estimated_cost = ?, parts_cost = ?, updated_at = ?
            WHERE id = ?
        ");
        $upd->execute([$newStage, $finalEst, $finalParts, $now, $id]);

        $autoNote = $noteText !== '' ? $noteText : ('تم انتقال الجهاز إلى مرحلة: ' . $stages[$newStage]['title']);
        $insNote = $pdo->prepare("
            INSERT INTO maintenance_notes (ticket_id, stage_key, sender_type, sender_name, note_text, is_stage_change, created_at)
            VALUES (?, ?, 'admin', ?, ?, 1, ?)
        ");
        $insNote->execute([$id, $newStage, $user['full_name'], $autoNote, $now]);

        // Refresh ticket & send email notification if customer registered an email
        $stmt->execute([$id]);
        $updatedTicket = $stmt->fetch();

        $emailRes = ['sent' => false, 'status' => 'skipped'];
        if (!empty($updatedTicket['customer_email'])) {
            $emailRes = AslyMailer::sendMaintenanceStageEmail($pdo, $updatedTicket, $newStage, $autoNote, true);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'تم تحديث مرحلة الصيانة بنجاح' . (!empty($updatedTicket['customer_email']) ? ' وإرسال إشعار بريد إلكتروني للعميل.' : '.'),
            'email_notification' => $emailRes,
        ]);
    }

    // 3. Add Admin Note on Any Specific Stage + Email Customer if registered
    if ($action === 'add_note') {
        $id = (int)($input['id'] ?? 0);
        $stageKey = trim((string)($input['stage_key'] ?? ''));
        $noteText = trim((string)($input['note_text'] ?? ''));

        if ($noteText === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى كتابة نص الملاحظة.'], 400);
        }

        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            jsonResponse(['ok' => false, 'error' => 'التذكرة غير موجودة.'], 404);
        }

        if (!isset($stages[$stageKey])) {
            $stageKey = (string)$ticket['current_stage'];
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare("
            INSERT INTO maintenance_notes (ticket_id, stage_key, sender_type, sender_name, note_text, is_stage_change, created_at)
            VALUES (?, ?, 'admin', ?, ?, 0, ?)
        ");
        $ins->execute([$id, $stageKey, $user['full_name'], $noteText, $now]);
        $pdo->prepare("UPDATE maintenance_tickets SET updated_at = ? WHERE id = ?")->execute([$now, $id]);

        $emailRes = ['sent' => false, 'status' => 'skipped'];
        if (!empty($ticket['customer_email'])) {
            $emailRes = AslyMailer::sendMaintenanceStageEmail($pdo, $ticket, $stageKey, $noteText, true);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'تمت إضافة الملاحظة على المرحلة بنجاح.',
            'email_notification' => $emailRes,
        ]);
    }

    // 4. Final Delivery Handover -> Send Final Email -> Record Paid Revenue -> WIPE ALL DEVICE & PERSONAL DATA!
    if ($action === 'deliver_and_wipe') {
        $id = (int)($input['id'] ?? 0);
        $finalPaidAmount = round(max(0, (float)($input['final_paid_amount'] ?? 0)), 2);
        $partsCostInput = isset($input['parts_cost']) ? round(max(0, (float)$input['parts_cost']), 2) : null;
        $paymentMethod = trim((string)($input['payment_method'] ?? 'cash'));

        $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            jsonResponse(['ok' => false, 'error' => 'التذكرة غير موجودة أو تم تسليمها مسبقاً.'], 404);
        }

        $partsCost = ($user['role'] === 'admin' && $partsCostInput !== null)
            ? $partsCostInput
            : round((float)$ticket['parts_cost'], 2);
        $netProfit = round($finalPaidAmount - $partsCost, 2);

        // Send final handover email BEFORE wiping personal info (with recordInLog = false so no personal log persists!)
        if (!empty($ticket['customer_email'])) {
            AslyMailer::sendMaintenanceStageEmail(
                $pdo,
                $ticket,
                'delivered',
                'تم تسليم جهازكم بنجاح. نشكركم لثقتكم في مركز أصلي ASLY TECH STORE ونتشرف بخدمتكم دائماً.',
                false
            );
        }

        try {
            $pdo->beginTransaction();

            // 1. Preserve ONLY the financial revenue record for Daily Sales/Revenue Dashboard
            $revIns = $pdo->prepare("
                INSERT INTO maintenance_revenues (
                    ticket_code_ref, device_category, amount_paid, parts_cost, net_profit,
                    payment_method, delivered_by, delivered_by_name, revenue_date, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $revIns->execute([
                (string)$ticket['ticket_code'],
                (string)$ticket['device_type'],
                $finalPaidAmount,
                $partsCost,
                $netProfit,
                $paymentMethod,
                $user['id'],
                $user['full_name'],
                date('Y-m-d'),
                date('Y-m-d H:i:s'),
            ]);

            // 2. Permanently DELETE all notes, email logs, and the maintenance ticket itself!
            $pdo->prepare("DELETE FROM maintenance_notes WHERE ticket_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM email_logs WHERE ticket_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM maintenance_tickets WHERE id = ?")->execute([$id]);

            $pdo->commit();

            jsonResponse([
                'ok' => true,
                'message' => sprintf(
                    'تم تسليم الجهاز بنجاح، تسجيل مبلغ (%s) في إيرادات اليوم، ومسح كافة البيانات التفصيلية للجهاز والعميل نهائياً!',
                    formatMoney($finalPaidAmount)
                ),
            ]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['ok' => false, 'error' => 'حدث خطأ أثناء إغلاق التذكرة: ' . $e->getMessage()], 500);
        }
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
