<?php
/**
 * ASLY | أصلي TECH STORE
 * Scanner API: Lookup Barcode / QR & Generate New Product QR Codes
 * Role-aware: Staff NEVER receives real purchase_price!
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginApi();
$pdo = Database::getConnection();
$action = $_GET['action'] ?? '';

if ($action === 'generate_code') {
    $category = trim((string)($_GET['category'] ?? 'laptops'));
    $code = generateUniqueProductCode($pdo, $category);
    jsonResponse([
        'ok' => true,
        'code' => $code,
    ]);
}

if ($action === 'lookup') {
    $code = trim((string)($_GET['code'] ?? ''));
    if ($code === '') {
        jsonResponse(['ok' => false, 'error' => 'الكود الممسوح فارغ.'], 400);
    }

    // If a full URL was scanned (e.g. from a custom QR), extract code parameter if present
    if (strpos($code, 'code=') !== false) {
        $parts = parse_url($code);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            if (!empty($q['code'])) {
                $code = trim((string)$q['code']);
            }
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? OR UPPER(barcode) = UPPER(?) LIMIT 1");
    $stmt->execute([$code, $code]);
    $product = $stmt->fetch();

    if (!$product) {
        // Also check if scanned QR is a maintenance ticket!
        $mStmt = $pdo->prepare("SELECT id, ticket_code, device_model, customer_name, current_stage FROM maintenance_tickets WHERE UPPER(ticket_code) = UPPER(?) LIMIT 1");
        $mStmt->execute([$code]);
        $mTicket = $mStmt->fetch();
        if ($mTicket) {
            jsonResponse([
                'ok' => true,
                'found' => false,
                'is_maintenance_ticket' => true,
                'ticket' => $mTicket,
                'scanned_code' => $code,
            ]);
        }

        jsonResponse([
            'ok' => true,
            'found' => false,
            'scanned_code' => $code,
        ]);
    }

    $cats = getCategoryMeta();
    $conds = getConditionMeta();
    $langKey = isEn() ? 'en' : 'ar';
    $product['category_label'] = $cats[$product['category']][$langKey] ?? $product['category'];
    $product['condition_label'] = $conds[$product['condition_status']][$langKey] ?? $product['condition_status'];
    $product['display_name'] = pickLangField($product, 'name');
    $product['display_specs'] = pickLangField($product, 'specs');

    // Decoy "Show Purchase Price" button ALWAYS displays min_sell_price in Quick Scanner!
    $product['display_floor_price'] = (float)$product['min_sell_price'];

    // Strip real purchase_price if staff!
    if ($user['role'] !== 'admin') {
        unset($product['purchase_price']);
    } else {
        $product['purchase_price'] = (float)$product['purchase_price'];
    }
    $product['sell_price'] = (float)$product['sell_price'];
    $product['min_sell_price'] = (float)$product['min_sell_price'];
    $product['quantity'] = (int)$product['quantity'];

    jsonResponse([
        'ok' => true,
        'found' => true,
        'product' => $product,
    ]);
}

jsonResponse(['ok' => false, 'error' => 'إجراء غير معروف.'], 400);
