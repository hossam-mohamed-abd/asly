<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

function extractCodeFromPossibleUrl(string $raw): string
{
    $val = trim($raw);
    if ($val === '') {
        return '';
    }
    if (stripos($val, 'code=') !== false) {
        $parts = parse_url($val);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            if (!empty($q['code'])) {
                return trim((string)$q['code']);
            }
        }
        if (preg_match('/[?&]code=([^&#\s]+)/i', $val, $m)) {
            return trim(rawurldecode($m[1]));
        }
    }
    return $val;
}

function mapArabicKeymapToAscii(string $raw): string
{
    $multi = [
        'لا' => 'b',
        'لآ' => 'B',
        'لأ' => 'G',
        'لإ' => 'T',
    ];
    $str = strtr($raw, $multi);
    $map = [
        'ض' => 'q', 'ص' => 'w', 'ث' => 'e', 'ق' => 'r', 'ف' => 't',
        'غ' => 'y', 'ع' => 'u', 'ه' => 'i', 'خ' => 'o', 'ح' => 'p',
        'ج' => '[', 'د' => ']', 'ش' => 'a', 'س' => 's', 'ي' => 'd',
        'ب' => 'f', 'ل' => 'g', 'ا' => 'h', 'أ' => 'H', 'ت' => 'j',
        'ن' => 'k', 'م' => 'l', 'ك' => ';', 'ط' => "'", 'ئ' => 'z',
        'ء' => 'x', 'ؤ' => 'c', 'ر' => 'v', 'ى' => 'n', 'ة' => 'm',
        'و' => ',', 'ز' => '.', 'ظ' => '/', 'ذ' => '`',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];
    return strtr($str, $map);
}

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
    $rawInput = trim((string)($_GET['code'] ?? ''));
    if ($rawInput === '') {
        jsonResponse(['ok' => false, 'error' => 'الكود الممسوح فارغ.'], 400);
    }

    $candidates = [];
    $primary = extractCodeFromPossibleUrl($rawInput);
    if ($primary !== '') {
        $candidates[] = $primary;
    }
    $asciiConverted = extractCodeFromPossibleUrl(mapArabicKeymapToAscii($rawInput));
    if ($asciiConverted !== '' && !in_array($asciiConverted, $candidates, true)) {
        $candidates[] = $asciiConverted;
    }

    $product = false;
    $matchedCode = $primary;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? OR UPPER(barcode) = UPPER(?) LIMIT 1");
    foreach ($candidates as $cand) {
        $stmt->execute([$cand, $cand]);
        $row = $stmt->fetch();
        if ($row) {
            $product = $row;
            $matchedCode = $cand;
            break;
        }
    }

    if (!$product) {
        $mStmt = $pdo->prepare("SELECT id, ticket_code, device_model, customer_name, current_stage FROM maintenance_tickets WHERE UPPER(ticket_code) = UPPER(?) LIMIT 1");
        foreach ($candidates as $cand) {
            $mStmt->execute([$cand]);
            $mTicket = $mStmt->fetch();
            if ($mTicket) {
                jsonResponse([
                    'ok' => true,
                    'found' => false,
                    'is_maintenance_ticket' => true,
                    'ticket' => $mTicket,
                    'scanned_code' => $mTicket['ticket_code'],
                ]);
            }
        }

        jsonResponse([
            'ok' => true,
            'found' => false,
            'scanned_code' => $asciiConverted !== '' && preg_match('/[\x{0600}-\x{06FF}]/u', $primary) ? strtoupper($asciiConverted) : $primary,
        ]);
    }

    $cats = getCategoryMeta();
    $conds = getConditionMeta();
    $langKey = isEn() ? 'en' : 'ar';
    $product['category_label'] = $cats[$product['category']][$langKey] ?? $product['category'];
    $product['condition_label'] = $conds[$product['condition_status']][$langKey] ?? $product['condition_status'];
    $product['display_name'] = pickLangField($product, 'name');
    $product['display_specs'] = pickLangField($product, 'specs');
    $product['display_floor_price'] = (float)$product['min_sell_price'];

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
        'scanned_code' => $matchedCode,
        'product' => $product,
    ]);
}
