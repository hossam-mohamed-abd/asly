<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

function extractCodeFromPossibleUrl(string $raw): string
{
    $val = trim($raw);
    if ($val === '') {
        return '';
    }
    $val = preg_replace('/^\](?:E0|E4|C0|C1|Q1|Q3|d2)/', '', $val) ?? $val;
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
    return trim($val);
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

function expandBarcodeCandidates(array $seeds): array
{
    $out = [];
    $add = static function (string $v) use (&$out): void {
        if ($v !== '' && !in_array($v, $out, true)) {
            $out[] = $v;
        }
    };

    foreach ($seeds as $seed) {
        $add($seed);
        $digitsCompact = preg_replace('/\s+/', '', $seed) ?? $seed;
        $add($digitsCompact);

        if ($digitsCompact !== '' && ctype_digit($digitsCompact)) {
            $core = ltrim($digitsCompact, '0');
            if ($core !== '') {
                $add($core);
                $add('0' . $core);
                $add('00' . $core);
                foreach ([8, 12, 13, 14] as $gtinLen) {
                    if (strlen($core) < $gtinLen) {
                        $add(str_pad($core, $gtinLen, '0', STR_PAD_LEFT));
                    }
                }
            }
        }
    }
    return $out;
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

    $seeds = [];
    $primary = extractCodeFromPossibleUrl($rawInput);
    if ($primary !== '') {
        $seeds[] = $primary;
    }
    $asciiConverted = extractCodeFromPossibleUrl(mapArabicKeymapToAscii($rawInput));
    if ($asciiConverted !== '' && !in_array($asciiConverted, $seeds, true)) {
        $seeds[] = $asciiConverted;
    }

    $candidates = expandBarcodeCandidates($seeds);

    $product = false;
    $matchedCode = $primary;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ? OR UPPER(barcode) = UPPER(?) LIMIT 1");
    foreach ($candidates as $cand) {
        $stmt->execute([$cand, $cand]);
        $row = $stmt->fetch();
        if ($row) {
            $product = $row;
            $matchedCode = (string)$row['barcode'];
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

        $displayCode = $primary;
        if ($asciiConverted !== '' && preg_match('/[\x{0600}-\x{06FF}]/u', $primary)) {
            $displayCode = strtoupper($asciiConverted);
        } elseif (strlen($displayCode) === 13 && str_starts_with($displayCode, '0') && ctype_digit($displayCode)) {
            $displayCode = substr($displayCode, 1);
        }

        jsonResponse([
            'ok' => true,
            'found' => false,
            'scanned_code' => $displayCode,
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
