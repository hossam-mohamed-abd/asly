<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = trim((string)($_GET['action'] ?? $_POST['action'] ?? 'list'));
$en = isEn();
$isAdmin = ($user['role'] ?? '') === 'admin';
$GLOBALS['ASLY_IS_ADMIN'] = $isAdmin;
$GLOBALS['ASLY_PDO'] = $pdo;
$writeActions = ['save', 'adjust', 'delete', 'move', 'save_model', 'save_type', 'delete_type', 'link_devices', 'unlink_device', 'save_size', 'add_existing_size', 'delete_size'];
if (in_array($action, $writeActions, true)) {
    requireAdminApi();
}

/* ------------------------------------------------------------------ helpers */

/** words people type that carry no model information */
function modelNoiseWords(): array
{
    return [
        // Arabic
        'شاشه', 'شاشة', 'شاشات', 'زجاج', 'زجاجه', 'زجاجة', 'حمايه', 'حماية', 'سكرين', 'سكرينة', 'سكرينه',
        'اسكرينة', 'اسكرينه', 'اسكرينات', 'فيلم', 'فيلمه', 'فيلمه', 'واقي', 'واقى', 'غطاء', 'كفر', 'سعر', 'اسعار',
        'منتج', 'مقاس', 'موديل', 'جهاز', 'موبايل', 'تليفون', 'محمول', 'بتاع', 'على', 'علي', 'عشان', 'عايز', 'عاوز',
        // English
        'screen', 'screens', 'protector', 'protectors', 'protection', 'glass', 'tempered', 'guard', 'for', 'the',
        'of', 'an', 'price', 'cover', 'film', 'mobile', 'phone', 'model', 'size', 'new', 'original', 'copy',
        '9h', '11d', '2.5d', 'nano',
    ];
}

/** Arabic / spoken-Egyptian brand and family spellings → the canonical latin token */
function modelAliases(): array
{
    return [
        // brands
        'جلاكسي' => 'galaxy', 'جالاكسي' => 'galaxy', 'جلاكسى' => 'galaxy', 'جالكسي' => 'galaxy',
        'جلاكسى' => 'galaxy', 'قلاشي' => 'galaxy', 'جالكسي' => 'galaxy',
        'سامسونج' => 'samsung', 'سامسونغ' => 'samsung', 'سامسونق' => 'samsung', 'سامسونك' => 'samsung',
        'سامسنغ' => 'samsung', 'سامسون' => 'samsung',
        'ايفون' => 'iphone', 'ايفوون' => 'iphone', 'افون' => 'iphone', 'ايفن' => 'iphone',
        'ابل' => 'apple', 'أبل' => 'apple', 'اببل' => 'apple', 'أبل' => 'apple', 'ابل' => 'apple',
        'شاومي' => 'xiaomi', 'شياومي' => 'xiaomi', 'شومي' => 'xiaomi', 'كسياومي' => 'xiaomi',
        'ريدمي' => 'redmi', 'ريدمى' => 'redmi', 'ريدماي' => 'redmi', 'ريديمى' => 'redmi',
        'بوكو' => 'poco', 'بوكوو' => 'poco', 'بوكو' => 'poco',
        'هواوي' => 'huawei', 'هوواوي' => 'huawei', 'هواوى' => 'huawei',
        'اوبو' => 'oppo', 'أوبو' => 'oppo', 'اوبو' => 'oppo', 'اوبوه' => 'oppo',
        'انفينكس' => 'infinix', 'انفنكس' => 'infinix', 'انفينيكس' => 'infinix', 'انفنيكس' => 'infinix',
        'فيفو' => 'vivo', 'فيڤو' => 'vivo', 'فيفوو' => 'vivo',
        'ريلمي' => 'realme', 'ريلمى' => 'realme', 'ريل مي' => 'realme', 'ريالمي' => 'realme',
        'نوكيا' => 'nokia', 'نوكياا' => 'nokia',
        'هونر' => 'honor', 'هونور' => 'honor', 'هنر' => 'honor',
        'تكنو' => 'tecno', 'تكنوو' => 'tecno', 'تيكno' => 'tecno',
        'موتورولا' => 'motorola', 'موتورلا' => 'motorola', 'موتو' => 'moto',
        'بيكسل' => 'pixel', 'بيكسيل' => 'pixel',
        'نوثينج' => 'nothing', 'نو ثينج' => 'nothing',
        'ايتل' => 'itel', 'أيتل' => 'itel',
        'لينوفو' => 'lenovo', 'لينوفوو' => 'lenovo',
        'ون بلس' => 'oneplus', 'ونبلس' => 'oneplus', 'ون بلس' => 'oneplus',
        'جوجل' => 'google',
        'جرينفون' => 'green', 'جرين' => 'green',
        // families / edition words
        'نوت' => 'note', 'برو' => 'pro', 'ماكس' => 'max', 'بلس' => 'plus', 'بلص' => 'plus',
        'لايت' => 'lite', 'الترا' => 'ultra', 'التيمت' => 'ultimate', 'ميني' => 'mini', 'مينى' => 'mini',
        'ايدج' => 'edge', 'فولد' => 'fold', 'فليب' => 'flip', 'نارزو' => 'narzo', 'ريدمي' => 'redmi',
        'بوكو' => 'poco', 'بافا' => 'pova', 'سبارك' => 'spark', 'كامون' => 'camon', 'هوت' => 'hot',
        'ايه' => 'a', 'ايج' => 'a', 'بي' => 'b', 'سي' => 'c', 'دي' => 'd', 'اكس' => 'x', 'اس' => 's',
        'جي' => 'g', 'ام' => 'm', 'تي' => 't', 'زد' => 'z', 'كيه' => 'k', 'في' => 'v', 'واي' => 'y',
        'كي' => 'q', 'اتش' => 'h', 'ان' => 'n', 'ار' => 'r', 'دبليو' => 'w',
        // Arabic letter names spoken in sequence: جالاكسي ايه 21 اس → galaxy a 21 s
        'ايه اس' => 'as', 'ايه اس ايه' => 'asa', 'اس ايه' => 'sa', 'بي اس' => 'bs',
        'اس' => 's', 'اكس اس' => 'xs', 'برو ماكس' => 'pro max',
    ];
}

function norm(string $value): string
{
    $value = \trim($value);
    $digits = ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
                '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9'];
    $value = \strtr($value, $digits);
    $value = \mb_strtolower($value, 'UTF-8');
    // unify Arabic letters that people type interchangeably
    $value = \str_replace(
        ['أ', 'إ', 'آ', 'ٱ', 'ى', 'ئ', 'ؤ', 'ة', 'ﻻ', 'ﻷ', 'ﻹ', 'ﻵ'],
        ['ا', 'ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', 'لا', 'لا', 'لا', 'لا'],
        $value
    );
    // "5.5" / "6,5" → "5 5" so numbers survive tokenisation
    $value = \preg_replace('/(?<=\d)[.,](?=\d)/u', ' ', $value) ?: $value;
    $value = \preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?: '';
    return \trim(\preg_replace('/\s+/u', ' ', $value) ?: '');
}

/** compact key used for typo tolerance: no spaces, no latin vowels, doubled letters collapsed */
function searchSkeleton(string $value): string
{
    $v = \preg_replace('/[^\p{L}\p{N}]+/u', '', norm($value)) ?: '';
    $v = \preg_replace('/(.)\1+/u', '$1', $v) ?: $v;
    return \str_replace(['a', 'e', 'i', 'o', 'u'], '', $v);
}

function levenshteinUtf8(string $a, string $b): int
{
    $la = \mb_strlen($a, 'UTF-8');
    $lb = \mb_strlen($b, 'UTF-8');
    if ($la === 0) {
        return $lb;
    }
    if ($lb === 0) {
        return $la;
    }
    $prev = \range(0, $lb);
    for ($i = 1; $i <= $la; $i++) {
        $cur = [$i];
        $ca = \mb_substr($a, $i - 1, 1, 'UTF-8');
        for ($j = 1; $j <= $lb; $j++) {
            $cost = $ca === \mb_substr($b, $j - 1, 1, 'UTF-8') ? 0 : 1;
            $cur[$j] = \min($prev[$j] + 1, $cur[$j - 1] + 1, $prev[$j - 1] + $cost);
        }
        $prev = $cur;
    }
    return (int)$prev[$lb];
}

/**
 * tolerant word comparison: exact / prefix / substring / one-or-two typos / missing vowels.
 * Returns a weight in 0..1 (0 = no match).
 */
function tokenWeight(string $word, string $token): float
{
    if ($word === $token) {
        return 1.0;
    }
    if ($token === '') {
        return 0.0;
    }
    $wl = \mb_strlen($word, 'UTF-8');
    $tl = \mb_strlen($token, 'UTF-8');
    if (\mb_strpos($word, $token, 0, 'UTF-8') === 0) {
        return 0.92;
    }
    if (\mb_strpos($word, $token, 0, 'UTF-8') !== false) {
        return 0.8;
    }
    // "a 5 4" vs "a54", "s 2 1" vs "s21": letters+digits typed apart
    $wp = \str_replace(' ', '', $word);
    if ($wp === $token) {
        return 0.95;
    }
    // pure numbers are never typos: 10 ≠ 11
    if (\ctype_digit($token) && \ctype_digit($word)) {
        return 0.0;
    }
    // short letter+digit codes ("a1s" for "a15", "c5x" for "c53"): one wrong character is still a hit
    if ($tl >= 3 && $tl <= 4 && \preg_match('/^[a-z]?\d+[a-z]?$/', $token) && \preg_match('/^[a-z]?\d+[a-z]?$/', $wp)) {
        if (levenshteinUtf8($wp, $token) <= 1) {
            return 0.3;
        }
    }
    // model codes ("a15", "c53") tolerate one wrong character but never outrank an exact hit
    $alnum = \preg_match("/\d/", $token) && \preg_match("/[a-z]/", $token);
    $allowed = $tl <= 2 ? 0 : ($tl <= 5 ? 1 : 2);
    if ($allowed > 0 && \abs($wl - $tl) <= $allowed && levenshteinUtf8($word, $token) <= $allowed) {
        return $alnum ? 0.45 : 0.7;
    }
    $ws = searchSkeleton($word);
    $ts = searchSkeleton($token);
    if ($ts !== '' && $ws !== '' && \mb_strlen($ts, 'UTF-8') >= 3
        && ($ws === $ts || ($tl >= 5 && \mb_strpos($ws, $ts, 0, 'UTF-8') !== false))) {
        return 0.6;
    }
    return 0.0;
}

function modelTokens(string $value): array
{
    $clean = norm($value);
    $noise = modelNoiseWords();
    $aliases = modelAliases();
    $tokens = [];
    foreach (\explode(' ', $clean) as $word) {
        if ($word === '' || \in_array($word, $noise, true)) {
            continue;
        }
        $word = $aliases[$word] ?? $word;
        if ($word === '' || \in_array($word, $noise, true)) {
            continue;
        }
        $tokens[] = $word;
    }
    // people type codes with spaces: "a 1 5", "s 2 1 u l t r a" → glue single characters back
    $merged = [];
    $run = '';
    foreach ($tokens as $token) {
        if (\mb_strlen($token, 'UTF-8') === 1) {
            $run .= $token;
            continue;
        }
        if ($run !== '') {
            $merged[] = $run;
            $run = '';
        }
        $merged[] = $token;
    }
    if ($run !== '') {
        $merged[] = $run;
    }
    // and the other direction: "ايه 21 اس" arrives as a · 21 · s → a21s, the way the code is written
    $glued = [];
    foreach ($merged as $token) {
        $last = $glued ? $glued[count($glued) - 1] : null;
        if ($last !== null && \preg_match('/^\d+$/', $token) && \preg_match('/^[a-z]$/', $last)) {
            $glued[count($glued) - 1] = $last . $token;
            continue;
        }
        if ($last !== null && \preg_match('/^[a-z]$/', $token)
            && \preg_match('/^[a-z]+\d+$/', $last) && \mb_strlen($last, 'UTF-8') <= 5) {
            $glued[count($glued) - 1] = $last . $token;
            continue;
        }
        $glued[] = $token;
    }
    $tokens = $glued;
    if (!$tokens && $clean !== '') {
        $tokens[] = $clean;
    }
    return \array_values(\array_unique($tokens));
}

/**
 * how well one model answers a query: every token must hit something,
 * the score rewards order, brand hits and whole-phrase equality.
 */
function modelMatchScore(string $haystack, array $tokens, string $rawQuery = ''): float
{
    if (!$tokens) {
        return 0.0;
    }
    $words = \explode(' ', $haystack);
    $words = \array_values(\array_filter($words, fn($w) => $w !== ''));
    $compact = \str_replace(' ', '', $haystack);
    $total = 0.0;
    $hits = 0;
    foreach ($tokens as $token) {
        $best = 0.0;
        foreach ($words as $word) {
            $w = tokenWeight($word, $token);
            if ($w > $best) {
                $best = $w;
                if ($best >= 1.0) {
                    break;
                }
            }
        }
        if ($best === 0.0 && \mb_strlen($token, 'UTF-8') >= 3 && \mb_strpos($compact, \str_replace(' ', '', $token), 0, 'UTF-8') !== false) {
            $best = 0.55;
        }
        if ($best === 0.0) {
            continue;
        }
        $total += $best;
        $hits++;
    }
    // something has to land, and the longer the word the more it must actually match
    if ($hits === 0) {
        return 0.0;
    }
    $score = $total / \count($tokens);
    if ($hits < \count($tokens)) {
        $score *= 0.55 + 0.45 * ($hits / \count($tokens));
    }
    // phrase bonus: "galaxy a54" typed in that order inside the name
    $query = \implode(' ', $tokens);
    if ($query !== '') {
        if ($haystack === $query) {
            $score += 0.6;
        } elseif (\mb_strpos($haystack, $query, 0, 'UTF-8') !== false) {
            $score += 0.25;
        } elseif (\mb_strpos($compact, \str_replace(' ', '', $query), 0, 'UTF-8') !== false) {
            $score += 0.15;
        }
    }
    // precision: leftover words in the model name ("pro", "5G") mean it is a different phone
    $extra = 0;
    foreach ($words as $word) {
        $covered = false;
        foreach ($tokens as $token) {
            if (tokenWeight($word, $token) >= 0.8) {
                $covered = true;
                break;
            }
        }
        if (!$covered) {
            $extra++;
        }
    }
    $score -= \min(0.18, 0.035 * $extra);
    // whole-token equality anywhere (the "A15" case)
    foreach ($tokens as $token) {
        if (\in_array($token, $words, true)) {
            $score += 0.12;
            break;
        }
    }
    return $score + \min(0.3, $hits * 0.02);
}

/** all models ordered by how well they answer the query (typos and word order included) */
function searchModels(PDO $pdo, string $q, int $limit = 20): array
{
    $bare = trim($q);
    if (\mb_strlen($bare, 'UTF-8') < 2 && !\preg_match('/\d/', $bare)) {
        return [];
    }
    $tokens = modelTokens($q);
    if (!$tokens) {
        return [];
    }
    $all = $pdo->query('SELECT * FROM device_models WHERE is_active = 1 ORDER BY brand ASC, model_name ASC')->fetchAll();
    $hits = [];
    foreach ($all as $m) {
        $hay = norm($m['brand'] . ' ' . $m['model_name'] . ' ' . (string)($m['model_name_ar'] ?? ''));
        if ($hay === '') {
            continue;
        }
        $score = modelMatchScore($hay, $tokens, $q);
        if ($score <= 0) {
            continue;
        }
        $hits[] = [
            'score' => $score,
            'len' => \mb_strlen((string)$m['model_name'], 'UTF-8'),
            'model' => $m,
        ];
    }
    if (!$hits) {
        return [];
    }
    // a model the shop already stocks glass for is the more useful answer
    $stocked = [];
    try {
        foreach ($pdo->query('SELECT DISTINCT model_id FROM screen_compatibility') as $r) {
            $stocked[(int)$r['model_id']] = true;
        }
    } catch (Throwable $e) {
        $stocked = [];
    }
    \usort($hits, function ($a, $b) use ($stocked) {
        if (\abs($a['score'] - $b['score']) >= 0.0001) {
            return $b['score'] <=> $a['score'];
        }
        $sa = isset($stocked[(int)$a['model']['id']]) ? 0 : 1;
        $sb = isset($stocked[(int)$b['model']['id']]) ? 0 : 1;
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        return $a['len'] <=> $b['len'];
    });
    $out = [];
    foreach (\array_slice($hits, 0, $limit) as $row) {
        $m = $row['model'];
        $out[] = [
            'id' => (int)$m['id'],
            'brand' => $m['brand'],
            'model_name' => $m['model_name'],
            'model_name_ar' => $m['model_name_ar'],
            'label' => \trim($m['brand'] . ' ' . $m['model_name']),
            'screen_inch' => $m['screen_inch'] !== null ? (float)$m['screen_inch'] : null,
            'score' => \round((float)$row['score'], 3),
        ];
    }
    return $out;
}

function findModel(PDO $pdo, string $q): ?array
{
    $list = searchModels($pdo, $q, 1);
    if (!$list) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM device_models WHERE id = ?');
    $stmt->execute([(int)$list[0]['id']]);
    return $stmt->fetch() ?: null;
}

function suggestModels(PDO $pdo, string $q, int $limit = 8): array
{
    return searchModels($pdo, $q, $limit);
}

/** live stock expression: the linked product is the source of truth */
function screenQtyExpr(string $alias = ''): string
{
    $p = $alias !== '' ? $alias . '.' : '';
    return "CASE WHEN {$p}product_id IS NOT NULL THEN COALESCE((SELECT p.quantity FROM products p WHERE p.id = {$p}product_id), {$p}quantity) ELSE {$p}quantity END";
}

function typeOrderExpr(string $alias = ''): string
{
    $p = $alias !== '' ? $alias . '.' : '';
    return "CASE {$p}type_slug WHEN 'hd' THEN 1 WHEN 'banda' THEN 2 WHEN 'privacy' THEN 3 WHEN '9h' THEN 4 WHEN 'clear' THEN 5 ELSE 9 END";
}

function screenTypes(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $rows = $pdo->query('SELECT * FROM screen_types WHERE is_active = 1 ORDER BY sort_order ASC, slug ASC')->fetchAll();
    } catch (Throwable $e) {
        $rows = [];
    }
    $cache = $rows ?: [];
    return $cache;
}

function screenTypePrices(PDO $pdo): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    try {
        $rows = $pdo->query('SELECT * FROM screen_type_prices ORDER BY type_slug ASC, size_tier ASC')->fetchAll();
    } catch (Throwable $e) {
        $rows = [];
    }
    $rows = $rows ?: [];
    return $rows;
}

/** one price per type — size tiers belong to the old catalog */
function typePrice(PDO $pdo, string $slug): ?array
{
    $fallback = null;
    foreach (screenTypePrices($pdo) as $row) {
        if ($row['type_slug'] !== $slug) {
            continue;
        }
        if ((int)$row['size_tier'] === 0) {
            return $row;
        }
        if ($fallback === null) {
            $fallback = $row;
        }
    }
    return $fallback;
}

function typeLabel(array $type, bool $en): string
{
    return trim((string)($en ? ($type['name_en'] ?? '') : ($type['name_ar'] ?? ''))) ?: (string)($type['slug'] ?? '');
}

function sizeLabel(array $row): string
{
    $w = (float)($row['width_mm'] ?? 0);
    $h = (float)($row['height_mm'] ?? 0);
    if ($w <= 0 || $h <= 0) {
        return '';
    }
    $trim = function (float $v): string {
        $s = number_format($v, 1, '.', '');
        return rtrim(rtrim($s, '0'), '.');
    };
    return $trim($w) . ' × ' . $trim($h) . ' mm';
}

function screenRow(array $row, bool $en): array
{
    $types = screenTypes($GLOBALS['ASLY_PDO']);
    $typeMap = [];
    foreach ($types as $t) {
        $typeMap[$t['slug']] = $t;
    }
    $type = $typeMap[$row['type_slug'] ?? ''] ?? null;
    $row['id'] = (int)$row['id'];
    $row['quantity'] = (int)$row['quantity'];
    $row['low_stock_threshold'] = (int)$row['low_stock_threshold'];
    $row['purchase_price'] = (float)$row['purchase_price'];
    if (empty($GLOBALS['ASLY_IS_ADMIN'])) {
        $row['purchase_price'] = 0.0;
        $row['no_cost'] = true;
    }
    $row['sell_price'] = (float)$row['sell_price'];
    $row['min_sell_price'] = (float)$row['min_sell_price'];
    $row['type_slug'] = (string)($row['type_slug'] ?? '');
    $row['type_ar'] = $type ? (string)$type['name_ar'] : $row['type_slug'];
    $row['type_en'] = $type ? (string)$type['name_en'] : $row['type_slug'];
    $row['type_label'] = $type ? typeLabel($type, $en) : $row['type_slug'];
    $row['sort_order'] = $type ? (int)$type['sort_order'] : 99;
    $row['size_label'] = sizeLabel($row);
    $row['width_mm'] = round((float)($row['width_mm'] ?? 0), 2);
    $row['height_mm'] = round((float)($row['height_mm'] ?? 0), 2);
    $row['diag_inch'] = round((float)($row['diag_inch'] ?? 0), 2);
    $row['size_tier'] = (int)($row['size_tier'] ?? 0);
    $price = typePrice($GLOBALS['ASLY_PDO'], (string)$row['type_slug']);
    if ($price) {
        $row['sell_price'] = (float)$price['sell_price'];
        $row['purchase_price'] = empty($GLOBALS['ASLY_IS_ADMIN']) ? 0.0 : (float)$price['cost_price'];
        $row['min_sell_price'] = (float)$price['min_sell_price'];
    }
    $row = applyInventoryStock($GLOBALS['ASLY_PDO'], $row);
    // the card headline: type + physical size
    $row['display_name'] = trim($row['type_label'] . ' · ' . $row['size_label']);
    $row['is_low'] = $row['quantity'] <= $row['low_stock_threshold'];
    $row['is_out'] = $row['quantity'] <= 0;
    $row['shelf_label'] = trim((string)($row['shelf_code'] ?? ''));
    if (!empty($row['drawer_label'])) {
        $row['shelf_label'] = $row['shelf_label'] !== ''
            ? $row['shelf_label'] . ' - ' . $row['drawer_label']
            : (string)$row['drawer_label'];
    }
    if ($row['shelf_label'] === '') {
        $row['shelf_label'] = $en ? 'Not placed' : 'غير محدد';
    }
    $row['place_label'] = $row['shelf_label'];
    return $row;
}

/** devices (models) linked to each protector, with the label used on the cards */
function modelsForScreens(PDO $pdo, array $ids, int $limitPer = 400): array
{
    if (!$ids) {
        return [];
    }
    $safe = implode(',', array_map('intval', $ids));
    $rows = $pdo->query("
        SELECT c.screen_id, c.link_type, m.id, m.brand, m.model_name, m.model_name_ar, m.screen_inch
        FROM screen_compatibility c
        INNER JOIN device_models m ON m.id = c.model_id
        WHERE c.screen_id IN ({$safe})
        ORDER BY m.brand ASC, m.model_name ASC
    ")->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $sid = (int)$r['screen_id'];
        if (!isset($map[$sid])) {
            $map[$sid] = ['list' => [], 'brands' => []];
        }
        $label = trim($r['brand'] . ' ' . $r['model_name']);
        $map[$sid]['list'][] = [
            'id' => (int)$r['id'],
            'brand' => $r['brand'],
            'model_name' => $r['model_name'],
            'label' => $label,
            'source' => (string)($r['link_type'] ?? 'manual'),
        ];
        $map[$sid]['brands'][$r['brand']] = true;
    }
    $out = [];
    foreach ($map as $sid => $data) {
        $list = $data['list'];
        $out[$sid] = [
            'models' => array_slice($list, 0, $limitPer),
            'count' => count($list),
            'brands' => array_keys($data['brands']),
            'first' => $list[0]['label'] ?? '',
        ];
    }
    return $out;
}

/** attach models/count/price meta to a list of screen rows */
function decorateScreens(PDO $pdo, array $rows): array
{
    $meta = modelsForScreens($pdo, array_column($rows, 'id'));
    foreach ($rows as &$r) {
        $m = $meta[$r['id']] ?? ['models' => [], 'count' => 0, 'brands' => [], 'first' => ''];
        $r['models'] = $m['models'];
        $r['model_count'] = $m['count'];
        $r['model_brands'] = $m['brands'];
        $r['first_model'] = $m['first'];
    }
    unset($r);
    return $rows;
}

function labelForModel(array $model): string
{
    return trim($model['brand'] . ' ' . $model['model_name']);
}

/** two protectors belong on one card when they are the same sheet / same glass */
function screenGroupKey(array $row): string
{
    $barcode = (string)($row['barcode'] ?? '');
    // the ZEUS sheets carry their code in the barcode: ZS-A15-HD → the A15 sheet
    if (preg_match('/^(ZS-[A-Z0-9]+)-/', $barcode, $m)) {
        return $m[1];
    }
    // otherwise: the same device list means the same piece of glass
    $models = $row['models'] ?? [];
    if ($models) {
        $ids = array_map(fn($m) => (int)$m['id'], $models);
        sort($ids);
        return 'set:' . md5(implode(',', $ids));
    }
    return 'size:' . number_format(round((float)($row['width_mm'] ?? 0), 1), 1, '.', '') . 'x'
        . number_format(round((float)($row['height_mm'] ?? 0), 1), 1, '.', '');
}

/**
 * Screens grouped the way a shop shows them: one card per glass (same mm size),
 * with every type it comes in — HD / Banda / Privacy — inside that single card.
 */
function groupScreenRows(PDO $pdo, array $rows, bool $en, ?array $matchedModel = null): array
{
    $bySize = [];
    foreach ($rows as $row) {
        $key = screenGroupKey($row);
        if (!isset($bySize[$key])) {
            $bySize[$key] = [
                'key' => $key,
                'size_key' => number_format(round((float)($row['width_mm'] ?? 0), 1), 1, '.', '') . 'x'
                    . number_format(round((float)($row['height_mm'] ?? 0), 1), 1, '.', ''),
                'width_mm' => round((float)($row['width_mm'] ?? 0), 1),
                'height_mm' => round((float)($row['height_mm'] ?? 0), 1),
                'diag_inch' => round((float)($row['diag_inch'] ?? 0), 2),
                'size_label' => sizeLabel($row),
                'variants' => [],
            ];
        }
        $bySize[$key]['variants'][] = $row;
        $bySize[$key]['diag_inch'] = max($bySize[$key]['diag_inch'], round((float)($row['diag_inch'] ?? 0), 2));
    }
    $out = [];
    $matchedId = $matchedModel ? (int)$matchedModel['id'] : 0;
    foreach ($bySize as $group) {
        usort($group['variants'], fn($a, $b) => [$a['sort_order'] ?? 99, $a['type_slug']] <=> [$b['sort_order'] ?? 99, $b['type_slug']]);
        $first = $group['variants'][0];
        $allModels = [];
        $types = [];
        foreach ($group['variants'] as $v) {
            foreach (($v['models'] ?? []) as $m) {
                $allModels[(int)$m['id']] = $m;
            }
            $types[] = [
                'id' => (int)$v['id'],
                'type_slug' => (string)$v['type_slug'],
                'type_label' => (string)$v['type_label'],
                'sell_price' => (float)$v['sell_price'],
                'min_sell_price' => (float)$v['min_sell_price'],
                'quantity' => (int)$v['quantity'],
                'is_low' => !empty($v['is_low']),
                'is_out' => !empty($v['is_out']),
                'barcode' => (string)$v['barcode'],
                'shelf_label' => (string)($v['shelf_label'] ?? ''),
                'place_label' => (string)($v['place_label'] ?? ''),
                'product_id' => (int)($v['product_id'] ?? 0),
                'barcode_code' => (string)($v['barcode'] ?? ''),
            ];
        }
        $models = array_values($allModels);
        usort($models, function ($a, $b) use ($matchedId) {
            if ($matchedId > 0) {
                if ((int)$a['id'] === $matchedId) {
                    return -1;
                }
                if ((int)$b['id'] === $matchedId) {
                    return 1;
                }
            }
            return strcmp((string)$a['label'], (string)$b['label']);
        });
        $prices = array_map(fn($t) => (float)$t['sell_price'], $types);
        $anyLow = false;
        $anyStock = false;
        foreach ($types as $t) {
            $anyLow = $anyLow || $t['is_low'];
            $anyStock = $anyStock || $t['quantity'] > 0;
        }
        $source = 'manual';
        foreach (($first['models'] ?? []) as $m) {
            if (($m['source'] ?? '') === 'zeus') {
                $source = 'zeus';
                break;
            }
        }
        $card = $first;
        $card['group_key'] = $group['key'];
        foreach ($types as $i => $t) {
            $orig = $group['variants'][$i] ?? null;
            if ($orig) {
                $types[$i]['width_mm'] = round((float)($orig['width_mm'] ?? 0), 2);
                $types[$i]['height_mm'] = round((float)($orig['height_mm'] ?? 0), 2);
                $types[$i]['low_stock_threshold'] = (int)($orig['low_stock_threshold'] ?? 3);
                $types[$i]['product_id'] = (int)($orig['product_id'] ?? 0);
            }
        }
        $card['types'] = $types;
        $card['size_label'] = $group['size_label'];
        $card['diag_inch'] = $group['diag_inch'];
        $card['types'] = $types;
        $card['type_slugs'] = array_column($types, 'type_slug');
        $card['prices'] = $prices;
        $card['sell_price'] = $prices ? min($prices) : 0.0;
        $card['price_max'] = $prices ? max($prices) : 0.0;
        $card['has_multi_price'] = count(array_unique($prices)) > 1;
        $card['price_label'] = $card['has_multi_price']
            ? implode(' · ', array_map(fn($t) => $t['type_label'] . ' ' . number_format((float)$t['sell_price'], 0, '.', ''), $types))
            : ($prices ? (string)round((float)$prices[0], 2) : '0');
        $card['sources'] = $source;
        $card['models'] = $models;
        $card['model_count'] = count($models);
        $card['first_model'] = $models[0]['label'] ?? '';
        // the card answers "which glass is this?": the sheet/group name, then the widest device family
        $sheet = '';
        if (preg_match('/^(ZS-[A-Z0-9]+)-/', (string)($first['barcode'] ?? ''), $mm)) {
            $sheet = $mm[1];
        }
        if ($matchedModel) {
            $card['card_title'] = labelForModel($matchedModel);
        } elseif ($sheet !== '') {
            // the ZEUS sheets are named after the phone they are cut for: A15, C53, A21s…
            $card['card_title'] = ($en ? 'ZEUS ' : 'زجاج ') . preg_replace('/^ZS-/', '', $sheet);
        } else {
            $brands = [];
            foreach ($models as $m) {
                $brands[(string)$m['brand']] = true;
            }
            $card['card_title'] = count($brands) === 1
                ? (string)array_key_first($brands)
                : ($models[0]['label'] ?? $group['size_label']);
        }
        $card['quantity'] = array_sum(array_map(fn($t) => (int)$t['quantity'], $types));
        $card['is_low'] = $anyLow;
        $card['is_out'] = !$anyStock;
        $card['display_name'] = trim($group['size_label']);
        $out[] = $card;
    }
    usort($out, function ($a, $b) {
        if ($a['model_count'] === $b['model_count']) {
            return $b['height_mm'] <=> $a['height_mm'];
        }
        return $b['model_count'] <=> $a['model_count'];
    });
    return $out;
}

/** ranks each card by the best position of its devices in the search results */
function rankCardsByHits(array $cards, array $hits): array
{
    $rank = [];
    foreach ($hits as $i => $h) {
        $rank[(int)$h['id']] = $i;
    }
    foreach ($cards as &$card) {
        $best = PHP_INT_MAX;
        foreach (($card['models'] ?? []) as $m) {
            $r = $rank[(int)$m['id']] ?? PHP_INT_MAX;
            if ($r < $best) {
                $best = $r;
            }
        }
        $card['rank'] = $best;
    }
    unset($card);
    // re-sort: the card of the best-ranked device comes first
    usort($cards, function ($a, $b) {
        $ra = $a['rank'] ?? PHP_INT_MAX;
        $rb = $b['rank'] ?? PHP_INT_MAX;
        if ($ra !== $rb) {
            return $ra <=> $rb;
        }
        return $b['model_count'] <=> $a['model_count'];
    });
    return $cards;
}

/* -------------------------------------------------------------- GET actions */

if ($method === 'GET') {
    if ($action === 'list') {
        $missing = ($_GET['missing'] ?? '') === '1';
        $typeSlug = trim((string)($_GET['type'] ?? ''));
        $brand = trim((string)($_GET['brand'] ?? ''));
        $withTablets = ($_GET['tablets'] ?? '') === '1';
        $where = ['is_active = 1', 'width_mm >= 50'];
        if (!$withTablets) {
            $where[] = '(diag_inch IS NULL OR diag_inch < 7.2)';
        }
        $params = [];
        if ($missing) {
            $where[] = screenQtyExpr() . ' <= low_stock_threshold';
        }
        if ($typeSlug !== '') {
            $where[] = 'type_slug = ?';
            $params[] = $typeSlug;
        }
        if ($brand !== '') {
            $where[] = 'id IN (SELECT c.screen_id FROM screen_compatibility c INNER JOIN device_models m ON m.id = c.model_id WHERE m.brand = ?)';
            $params[] = $brand;
        }
        $perPage = (int)($_GET['per_page'] ?? 24);
        if ($perPage < 6) {
            $perPage = 24;
        }
        if ($perPage > 120) {
            $perPage = 120;
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        // one card per piece of glass: build every card, then paginate the cards
        $sql = 'SELECT * FROM screen_protectors' . $whereSql
            . ' ORDER BY (SELECT COUNT(*) FROM screen_compatibility c WHERE c.screen_id = screen_protectors.id) DESC, height_mm DESC, width_mm DESC, ' . typeOrderExpr();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
        $rows = decorateScreens($pdo, $rows);
        $rows = groupScreenRows($pdo, $rows, $en);
        $totalCount = count($rows);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $rows = array_slice($rows, $offset, $perPage);

        $stats = [
            'cards' => $totalCount,
            'sizes' => (int)$pdo->query('SELECT COUNT(*) FROM (SELECT width_mm, height_mm FROM screen_protectors WHERE is_active = 1 AND width_mm >= 50 AND (diag_inch IS NULL OR diag_inch < 7.2) GROUP BY width_mm, height_mm) t')->fetchColumn(),
            'types' => count(screenTypes($pdo)),
            'total' => (int)$pdo->query('SELECT COUNT(*) FROM screen_protectors WHERE is_active = 1')->fetchColumn(),
            'units' => (int)$pdo->query('SELECT COALESCE(SUM(' . screenQtyExpr() . '), 0) FROM screen_protectors')->fetchColumn(),
            'missing' => (int)$pdo->query('SELECT COUNT(*) FROM screen_protectors WHERE is_active = 1 AND ' . screenQtyExpr() . ' <= low_stock_threshold')->fetchColumn(),
            'devices' => (int)$pdo->query('SELECT COUNT(*) FROM device_models WHERE is_active = 1')->fetchColumn(),
        ];
        $typeOut = [];
        foreach (screenTypes($pdo) as $t) {
            $typeOut[] = [
                'slug' => $t['slug'],
                'name_ar' => $t['name_ar'],
                'name_en' => $t['name_en'],
                'label' => typeLabel($t, $en),
                'sort_order' => (int)$t['sort_order'],
            ];
        }
        jsonResponse([
            'ok' => true,
            'screens' => $rows,
            'total' => $totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'stats' => $stats,
            'types' => $typeOut,
            'is_admin' => $isAdmin,
        ]);
    }

    /** free-text search: any spelling, order, typo or Arabic transliteration finds the device */
    if ($action === 'search') {
        $q = trim((string)($_GET['q'] ?? ''));
        $typeFilter = trim((string)($_GET['type'] ?? ''));
        $limit = min(30, max(4, (int)($_GET['limit'] ?? 12)));
        if ($q === '') {
            jsonResponse(['ok' => true, 'query' => '', 'hits' => [], 'screens' => [], 'is_admin' => $isAdmin]);
        }
        $hits = searchModels($pdo, $q, $limit);
        $screens = [];
        $withProtector = 0;
        if ($hits) {
            $ids = array_map(fn($h) => (int)$h['id'], $hits);
            $safe = implode(',', $ids);
            $where = ['s.is_active = 1', "c.model_id IN ({$safe})"];
            $params = [];
            if ($typeFilter !== '') {
                $where[] = 's.type_slug = ?';
                $params[] = $typeFilter;
            }
            $stmt = $pdo->prepare('
                SELECT DISTINCT s.* FROM screen_protectors s
                INNER JOIN screen_compatibility c ON c.screen_id = s.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY (s.diag_inch >= 7) ASC, s.height_mm DESC, s.width_mm DESC, ' . typeOrderExpr('s') . '
            ');
            $stmt->execute($params);
            $rows = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
            if ($rows) {
                $rows = decorateScreens($pdo, $rows);
                $screens = groupScreenRows($pdo, $rows, $en, null);
                $screens = rankCardsByHits($screens, $hits);
                $hitRank = [];
                foreach ($hits as $i => $h) {
                    $hitRank[(int)$h['id']] = $i;
                }
                // each card is titled by its own best-ranked device; ties break alphabetically
                foreach ($screens as &$sc) {
                    $opts = $sc['models'] ?? [];
                    usort($opts, function ($a, $b) use ($hitRank) {
                        $ra = $hitRank[(int)$a['id']] ?? PHP_INT_MAX;
                        $rb = $hitRank[(int)$b['id']] ?? PHP_INT_MAX;
                        if ($ra !== $rb) {
                            return $ra <=> $rb;
                        }
                        return strcmp((string)$a['label'], (string)$b['label']);
                    });
                    $sc['models'] = $opts;
                    $top = $opts[0] ?? null;
                    if ($top) {
                        $sc['first_model'] = $top['label'];
                        $sc['card_title'] = $top['label'];
                        $sc['device_label'] = $top['label'];
                        $sc['matched_model'] = $top;
                    }
                }
                unset($sc);
                $withProtector = count($screens);
            }
        }
        jsonResponse([
            'ok' => true,
            'query' => $q,
            'hits' => $hits,
            'screens' => $screens,
            'with_protector' => $withProtector,
            'model' => $hits[0] ?? null,
            'is_admin' => $isAdmin,
        ]);
    }

    if ($action === 'search_by_model') {
        $q = trim((string)($_GET['q'] ?? ''));
        $modelId = (int)($_GET['model_id'] ?? 0);
        $typeFilter = trim((string)($_GET['type'] ?? ''));
        $model = null;
        if ($modelId > 0) {
            $ms = $pdo->prepare('SELECT * FROM device_models WHERE id = ?');
            $ms->execute([$modelId]);
            $model = $ms->fetch() ?: null;
        } elseif ($q !== '') {
            $model = findModel($pdo, $q);
        }
        if (!$model) {
            jsonResponse([
                'ok' => true,
                'model' => null,
                'suggestions' => $q !== '' ? suggestModels($pdo, $q) : [],
                'screens' => [],
                'is_admin' => $isAdmin,
            ]);
        }
        $where = ['s.is_active = 1', 'c.model_id = ?'];
        $params = [(int)$model['id']];
        if ($typeFilter !== '') {
            $where[] = 's.type_slug = ?';
            $params[] = $typeFilter;
        }
        $stmt = $pdo->prepare('
            SELECT s.* FROM screen_protectors s
            INNER JOIN screen_compatibility c ON c.screen_id = s.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY (s.diag_inch >= 7) ASC, s.width_mm DESC, s.height_mm DESC, ' . typeOrderExpr('s') . '
        ');
        $stmt->execute($params);
        $rows = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
        $rows = decorateScreens($pdo, $rows);
        // one card per glass: the three types of the same size are variants of a single card
        $deviceLabel = labelForModel($model);
        $rows = groupScreenRows($pdo, $rows, $en, $model);
        $modelId = (int)$model['id'];
        foreach ($rows as &$r) {
            $r['device_label'] = $deviceLabel;
            $r['matched_model'] = [
                'id' => (int)$model['id'],
                'label' => $deviceLabel,
                'screen_inch' => $model['screen_inch'] !== null ? (float)$model['screen_inch'] : null,
            ];
        }
        unset($r);
        $alts = [];
        if ($q !== '') {
            foreach (suggestModels($pdo, $q, 7) as $alt) {
                if ((int)$alt['id'] === (int)$model['id']) {
                    continue;
                }
                $alts[] = $alt;
            }
        }
        jsonResponse([
            'ok' => true,
            'model' => [
                'id' => (int)$model['id'],
                'brand' => $model['brand'],
                'model_name' => $model['model_name'],
                'model_name_ar' => $model['model_name_ar'],
                'screen_inch' => $model['screen_inch'] !== null ? (float)$model['screen_inch'] : null,
                'width_mm' => $model['width_mm'] !== null ? (float)$model['width_mm'] : null,
                'height_mm' => $model['height_mm'] !== null ? (float)$model['height_mm'] : null,
                'label' => $deviceLabel,
            ],
            'suggestions' => $alts,
            'screens' => $rows,
            'is_admin' => $isAdmin,
        ]);
    }

    if ($action === 'models') {
        $q = trim((string)($_GET['q'] ?? ''));
        $limit = min(60, max(5, (int)($_GET['limit'] ?? 30)));
        $brand = trim((string)($_GET['brand'] ?? ''));
        if ($q !== '') {
            $list = suggestModels($pdo, $q, $limit);
            jsonResponse(['ok' => true, 'models' => $list]);
        }
        $sql = 'SELECT * FROM device_models WHERE is_active = 1';
        $params = [];
        if ($brand !== '') {
            $sql .= ' AND brand = ?';
            $params[] = $brand;
        }
        $sql .= ' ORDER BY brand ASC, model_name ASC LIMIT ' . $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $out = [];
        foreach ($stmt->fetchAll() as $m) {
            $out[] = [
                'id' => (int)$m['id'],
                'brand' => $m['brand'],
                'model_name' => $m['model_name'],
                'label' => labelForModel($m),
                'screen_inch' => $m['screen_inch'] !== null ? (float)$m['screen_inch'] : null,
            ];
        }
        jsonResponse(['ok' => true, 'models' => $out]);
    }

    if ($action === 'brands') {
        $rows = $pdo->query('SELECT brand, COUNT(*) AS total FROM device_models WHERE is_active = 1 GROUP BY brand ORDER BY brand ASC')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[] = ['brand' => $r['brand'], 'total' => (int)$r['total']];
        }
        jsonResponse(['ok' => true, 'brands' => $out]);
    }

    if ($action === 'shelves') {
        $rows = $pdo->query('SELECT * FROM shelves ORDER BY sort_order ASC, shelf_code ASC')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[] = ['id' => (int)$r['id'], 'shelf_code' => $r['shelf_code'], 'label' => $r['label'] ?: $r['shelf_code']];
        }
        jsonResponse(['ok' => true, 'shelves' => $out]);
    }

    if ($action === 'types') {
        $out = [];
        foreach (screenTypes($pdo) as $t) {
            $p = typePrice($pdo, (string)$t['slug']);
            $row = [
                'slug' => $t['slug'],
                'name_ar' => $t['name_ar'],
                'name_en' => $t['name_en'],
                'label' => typeLabel($t, $en),
                'sort_order' => (int)$t['sort_order'],
                'is_active' => (int)$t['is_active'],
                'sell_price' => $p ? (float)$p['sell_price'] : 0.0,
                'cost_price' => $p && $isAdmin ? (float)$p['cost_price'] : 0.0,
                'min_sell_price' => $p ? (float)$p['min_sell_price'] : 0.0,
                'screens' => 0,
                'units' => 0,
            ];
            $stmt = $pdo->prepare('SELECT COUNT(*) AS c, COALESCE(SUM(' . screenQtyExpr() . '), 0) AS u FROM screen_protectors WHERE type_slug = ? AND is_active = 1');
            $stmt->execute([$t['slug']]);
            $agg = $stmt->fetch() ?: ['c' => 0, 'u' => 0];
            $row['screens'] = (int)$agg['c'];
            $row['units'] = (int)$agg['u'];
            $out[] = $row;
        }
        jsonResponse(['ok' => true, 'types' => $out, 'is_admin' => $isAdmin]);
    }

    if ($action === 'missing') {
        $rows = $pdo->query('
            SELECT * FROM screen_protectors
            WHERE is_active = 1 AND ' . screenQtyExpr() . ' <= low_stock_threshold
            ORDER BY ' . screenQtyExpr() . ' ASC, height_mm DESC, ' . typeOrderExpr() . '
        ')->fetchAll();
        $list = array_map(fn($r) => screenRow($r, $en), $rows);
        $list = decorateScreens($pdo, $list);
        jsonResponse(['ok' => true, 'screens' => $list, 'count' => count($list)]);
    }

    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            jsonResponse(['ok' => false, 'error' => $en ? 'Screen not found.' : 'الاسكرينة غير موجودة.'], 404);
        }
        $data = screenRow($row, $en);
        $data = decorateScreens($pdo, [$data])[0];
        jsonResponse(['ok' => true, 'screen' => $data]);
    }

    if ($action === 'lookup') {
        $code = trim((string)($_GET['code'] ?? ''));
        $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE barcode = ? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) {
            jsonResponse(['ok' => true, 'found' => false, 'scanned_code' => $code]);
        }
        $data = screenRow($row, $en);
        $data = decorateScreens($pdo, [$data])[0];
        jsonResponse(['ok' => true, 'found' => true, 'screen' => $data, 'scanned_code' => $code]);
    }

    jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);
}

$input = $_POST;
$raw = file_get_contents('php://input');
if ($raw && empty($_POST)) {
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    }
}

if ($method !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);
}

/* ------------------------------------------------------------- POST actions */

if ($action === 'save_type') {
    $slug = strtolower(trim((string)($input['slug'] ?? '')));
    $nameAr = trim((string)($input['name_ar'] ?? ''));
    $nameEn = trim((string)($input['name_en'] ?? ''));
    $sort = (int)($input['sort_order'] ?? 100);
    if ($slug === '') {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($nameEn ?: $nameAr)) ?: '';
        $slug = trim((string)$slug, '_');
    }
    if ($slug === '' || ($nameAr === '' && $nameEn === '')) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Type name is required.' : 'اسم النوع مطلوب.'], 400);
    }
    $exists = $pdo->prepare('SELECT slug FROM screen_types WHERE slug = ?');
    $exists->execute([$slug]);
    if ($exists->fetchColumn()) {
        $pdo->prepare('UPDATE screen_types SET name_ar = ?, name_en = ?, sort_order = ?, notes = ? WHERE slug = ?')
            ->execute([$nameAr ?: $nameEn, $nameEn ?: $nameAr, $sort, $input['notes'] ?? null, $slug]);
    } else {
        $pdo->prepare('INSERT INTO screen_types (slug, name_ar, name_en, sort_order, notes) VALUES (?, ?, ?, ?, ?)')
            ->execute([$slug, $nameAr ?: $nameEn, $nameEn ?: $nameAr, $sort, $input['notes'] ?? null]);
    }
    $sell = round((float)($input['sell_price'] ?? -1), 2);
    $cost = round((float)($input['cost_price'] ?? 0), 2);
    $minSell = round((float)($input['min_sell_price'] ?? 0), 2);
    if ($sell >= 0) {
        if ($minSell <= 0) {
            $minSell = round($sell * 0.9, 2);
        }
        $pdo->prepare('DELETE FROM screen_type_prices WHERE type_slug = ?')->execute([$slug]);
        $pdo->prepare('INSERT INTO screen_type_prices (type_slug, size_tier, sell_price, cost_price, min_sell_price) VALUES (?, 0, ?, ?, ?)')
            ->execute([$slug, $sell, $cost, $minSell]);
    }
    // reprice every screen of this type
    $pdo->prepare('UPDATE screen_protectors SET sell_price = COALESCE((SELECT t.sell_price FROM screen_type_prices t WHERE t.type_slug = screen_protectors.type_slug ORDER BY t.size_tier ASC LIMIT 1), sell_price), purchase_price = COALESCE((SELECT t.cost_price FROM screen_type_prices t WHERE t.type_slug = screen_protectors.type_slug ORDER BY t.size_tier ASC LIMIT 1), purchase_price), min_sell_price = COALESCE((SELECT t.min_sell_price FROM screen_type_prices t WHERE t.type_slug = screen_protectors.type_slug ORDER BY t.size_tier ASC LIMIT 1), min_sell_price) WHERE type_slug = ?')
        ->execute([$slug]);
    $ids = $pdo->prepare('SELECT id FROM screen_protectors WHERE type_slug = ?');
    $ids->execute([$slug]);
    foreach ($ids->fetchAll() as $row) {
        syncScreenToProduct($pdo, (int)$row['id'], $en);
    }
    // compatibility never comes from the screen size: the ZEUS sheets and the admin's own links own it
    jsonResponse(['ok' => true, 'message' => $en ? 'Type saved and all prices updated.' : 'تم حفظ النوع وتحديث أسعار كل الاسكرينات.']);
}

if ($action === 'delete_type') {
    $slug = trim((string)($input['slug'] ?? ''));
    if ($slug === '') {
        jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM screen_protectors WHERE type_slug = ?');
    $count->execute([$slug]);
    if ((int)$count->fetchColumn() > 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Remove the protectors of this type first.' : 'احذف اسكرينات النوع ده الأول.'], 409);
    }
    $pdo->prepare('DELETE FROM screen_type_prices WHERE type_slug = ?')->execute([$slug]);
    $pdo->prepare('DELETE FROM screen_types WHERE slug = ?')->execute([$slug]);
    jsonResponse(['ok' => true, 'message' => $en ? 'Type deleted.' : 'تم حذف النوع.']);
}

if ($action === 'save') {
    $id = (int)($input['id'] ?? 0);
    $typeSlug = trim((string)($input['type_slug'] ?? ''));
    $width = round((float)($input['width_mm'] ?? 0), 2);
    $height = round((float)($input['height_mm'] ?? 0), 2);
    $shelfCode = trim((string)($input['shelf_code'] ?? ''));
    $drawer = trim((string)($input['drawer_label'] ?? ''));
    $qty = max(0, (int)($input['quantity'] ?? 0));
    $lowStock = max(0, (int)($input['low_stock_threshold'] ?? 3));
    $notes = trim((string)($input['notes'] ?? ''));
    $barcode = trim((string)($input['barcode'] ?? ''));
    $codeType = trim((string)($input['code_type'] ?? 'qr'));

    $typeStmt = $pdo->prepare('SELECT * FROM screen_types WHERE slug = ?');
    $typeStmt->execute([$typeSlug]);
    if (!$typeStmt->fetch()) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Choose the protector type.' : 'اختر نوع الاسكرينة.'], 400);
    }
    if ($width <= 0 || $height <= 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Enter the glass size in mm.' : 'اكتب مقاس الاسكرينة بالمليمتر.'], 400);
    }
    $diag = round(sqrt($width ** 2 + $height ** 2) / 25.4, 2);
    $tier = 0;
    $price = typePrice($pdo, $typeSlug);
    $sell = (float)($price['sell_price'] ?? 0);
    $cost = (float)($price['cost_price'] ?? 0);
    $minSell = (float)($price['min_sell_price'] ?? 0);
    if ($sell <= 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Set the price of this type first.' : 'حدّد سعر النوع ده الأول من قسم الأنواع.'], 400);
    }

    $now = date('Y-m-d H:i:s');
    if ($id > 0) {
        $cur = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
        $cur->execute([$id]);
        $existing = $cur->fetch();
        if (!$existing) {
            jsonResponse(['ok' => false, 'error' => $en ? 'Screen not found.' : 'الاسكرينة غير موجودة.'], 404);
        }
        $barcode = $barcode !== '' ? $barcode : (string)$existing['barcode'];
    } else {
        $dup = $pdo->prepare('SELECT id FROM screen_protectors WHERE type_slug = ? AND width_mm = ? AND height_mm = ? LIMIT 1');
        $dup->execute([$typeSlug, $width, $height]);
        if ($dup->fetchColumn()) {
            jsonResponse(['ok' => false, 'error' => $en ? 'This type already exists for this size.' : 'النوع ده موجود بالفعل للمقاس ده.'], 409);
        }
        if ($barcode === '') {
            $barcode = generateUniqueScreenCode($pdo);
            $codeType = 'qr';
        }
    }
    $dupCode = $pdo->prepare('SELECT id FROM screen_protectors WHERE barcode = ? AND id != ? LIMIT 1');
    $dupCode->execute([$barcode, $id]);
    if ($dupCode->fetch()) {
        jsonResponse(['ok' => false, 'error' => $en ? 'This code is already used.' : 'هذا الكود مستخدم بالفعل.'], 409);
    }

    if ($id > 0) {
        $pdo->prepare('
            UPDATE screen_protectors SET barcode = ?, code_type = ?, type_slug = ?, width_mm = ?, height_mm = ?,
                diag_inch = ?, size_tier = ?, shelf_code = ?, drawer_label = ?, purchase_price = ?, sell_price = ?,
                min_sell_price = ?, quantity = ?, low_stock_threshold = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ')->execute([
            $barcode, $codeType, $typeSlug, $width, $height, $diag, $tier, $shelfCode ?: null, $drawer ?: null,
            $cost, $sell, $minSell, $qty, $lowStock, $notes ?: null, $now, $id,
        ]);
    } else {
        $pdo->prepare('
            INSERT INTO screen_protectors
                (barcode, code_type, type_slug, width_mm, height_mm, diag_inch, size_tier, shelf_code, drawer_label,
                 purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ')->execute([
            $barcode, $codeType, $typeSlug, $width, $height, $diag, $tier, $shelfCode ?: null, $drawer ?: null,
            $cost, $sell, $minSell, $qty, $lowStock, $notes ?: null, $now, $now,
        ]);
        $id = (int)$pdo->lastInsertId();
    }

    // a protector of the same size shares its device list (one sheet = one size, three types)
    copySiblingLinks($pdo, $id);
    $modelIds = $input['model_ids'] ?? [];
    if (is_string($modelIds)) {
        $modelIds = array_filter(array_map('intval', explode(',', $modelIds)));
    }
    if (is_array($modelIds) && $modelIds) {
        $linkSql = DB_DRIVER === 'sqlite'
            ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)'
            : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)';
        $link = $pdo->prepare($linkSql);
        foreach (array_unique(array_map('intval', $modelIds)) as $mid) {
            if ($mid > 0) {
                $link->execute([$id, $mid]);
            }
        }
    }

    syncScreenToProduct($pdo, $id, $en);
    $count = $pdo->prepare('SELECT COUNT(*) FROM screen_compatibility WHERE screen_id = ?');
    $count->execute([$id]);
    jsonResponse([
        'ok' => true,
        'message' => $en ? 'Screen saved.' : 'تم حفظ الاسكرينة.',
        'id' => $id,
        'barcode' => $barcode,
        'devices' => (int)$count->fetchColumn(),
    ]);
}

if ($action === 'link_devices') {
    $id = (int)($input['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
    $stmt->execute([$id]);
    $screen = $stmt->fetch();
    if (!$screen) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Screen not found.' : 'الاسكرينة غير موجودة.'], 404);
    }
    $modelIds = $input['model_ids'] ?? [];
    if (is_string($modelIds)) {
        $modelIds = array_filter(array_map('intval', explode(',', $modelIds)));
    }
    $linkSql = DB_DRIVER === 'sqlite'
        ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)'
        : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)';
    $link = $pdo->prepare($linkSql);
    $added = 0;
    // the three types of one glass share one device list: link them all at once
    foreach (siblingScreenIds($pdo, $screen) as $sid) {
        foreach (array_unique(array_map('intval', (array)$modelIds)) as $mid) {
            if ($mid > 0) {
                $link->execute([$sid, $mid, 'manual']);
                $added++;
            }
        }
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM screen_compatibility WHERE screen_id = ?');
    $count->execute([$id]);
    jsonResponse([
        'ok' => true,
        'message' => $en ? 'Devices linked.' : 'تم ربط الأجهزة.',
        'devices' => (int)$count->fetchColumn(),
    ]);
}

if ($action === 'unlink_device') {
    $id = (int)($input['id'] ?? 0);
    $modelId = (int)($input['model_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
    $stmt->execute([$id]);
    $screen = $stmt->fetch();
    // one card = one glass: removing a device removes it from all its types, otherwise the card would still show it
    foreach ($screen ? siblingScreenIds($pdo, $screen) : [$id] as $sid) {
        $pdo->prepare('DELETE FROM screen_compatibility WHERE screen_id = ? AND model_id = ?')->execute([$sid, $modelId]);
    }
    jsonResponse(['ok' => true, 'message' => $en ? 'Device removed from this protector.' : 'تم فصل الجهاز عن الاسكرينة.']);
}

if ($action === 'save_model') {
    $brand = trim((string)($input['brand'] ?? ''));
    $modelName = trim((string)($input['model_name'] ?? ''));
    $inch = (float)($input['screen_inch'] ?? 0);
    $kind = trim((string)($input['device_kind'] ?? 'phone'));
    $linkTo = (int)($input['link_to'] ?? 0);
    $linkScreen = (int)($input['link_screen'] ?? 0);
    if ($brand === '' || $modelName === '') {
        jsonResponse(['ok' => false, 'error' => $en ? 'Brand and model are required.' : 'الماركة والموديل مطلوبين.'], 400);
    }
    $base = null;
    if ($linkTo > 0) {
        $stmt = $pdo->prepare('SELECT id, brand, model_name FROM device_models WHERE id = ? LIMIT 1');
        $stmt->execute([$linkTo]);
        $base = $stmt->fetch();
        if (!$base) {
            jsonResponse(['ok' => false, 'error' => $en ? 'The selected device was not found.' : 'الجهاز اللي اخترته مش موجود.'], 404);
        }
    } elseif ($linkScreen > 0) {
        $stmt = $pdo->prepare('SELECT id FROM screen_protectors WHERE id = ? LIMIT 1');
        $stmt->execute([$linkScreen]);
        if (!$stmt->fetchColumn()) {
            jsonResponse(['ok' => false, 'error' => $en ? 'The selected protector was not found.' : 'الاسكرينة اللي اخترتها مش موجودة.'], 404);
        }
    }
    $dup = $pdo->prepare('SELECT id FROM device_models WHERE brand = ? AND model_name = ? LIMIT 1');
    $dup->execute([$brand, $modelName]);
    $existingId = (int)$dup->fetchColumn();
    $width = 0.0;
    $height = 0.0;
    if ($existingId > 0) {
        $cur = $pdo->prepare('SELECT screen_inch, width_mm, height_mm FROM device_models WHERE id = ?');
        $cur->execute([$existingId]);
        $row = $cur->fetch() ?: [];
        $width = (float)($row['width_mm'] ?? 0);
        $height = (float)($row['height_mm'] ?? 0);
        if ($inch <= 0) {
            $inch = (float)($row['screen_inch'] ?? 0);
        }
    }
    $pdo->beginTransaction();
    if ($existingId > 0) {
        $pdo->prepare('UPDATE device_models SET screen_inch = ?, device_kind = ? WHERE id = ?')
            ->execute([$inch, $kind ?: 'phone', $existingId]);
        $id = $existingId;
    } else {
        $pdo->prepare('INSERT INTO device_models (brand, model_name, screen_inch, width_mm, height_mm, device_kind, is_active, created_at) VALUES (?, ?, ?, 0, 0, ?, 1, ?)')
            ->execute([$brand, $modelName, $inch, $kind ?: 'phone', date('Y-m-d H:i:s')]);
        $id = (int)$pdo->lastInsertId();
    }
    // compatibility comes from an existing device (same glass) or a picked protector — never from the screen size
    $linked = 0;
    if ($base) {
        $rows = $pdo->prepare('SELECT screen_id FROM screen_compatibility WHERE model_id = ?');
        $rows->execute([(int)$base['id']]);
        $insert = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)'
            : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)';
        $ins = $pdo->prepare($insert);
        foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $sid) {
            $ins->execute([(int)$sid, $id, 'same_as']);
        }
        $count = $pdo->prepare('SELECT COUNT(*) FROM screen_compatibility WHERE model_id = ?');
        $count->execute([$id]);
        $linked = (int)$count->fetchColumn();
    } elseif ($linkScreen > 0) {
        $insert = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)'
            : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id, link_type) VALUES (?, ?, ?)';
        $pdo->prepare($insert)->execute([$linkScreen, $id, 'manual']);
        $linked = 1;
    }
    $pdo->commit();
    $msg = $en ? 'Device saved.' : 'تم حفظ الجهاز.';
    if ($linked > 0 && $base) {
        $msg = $en
            ? "Device saved and linked to {$linked} protector(s) like {$base['brand']} {$base['model_name']}."
            : "تم حفظ الجهاز وربطه بـ{$linked} اسكرينة زي {$base['brand']} {$base['model_name']}.";
    } elseif ($linked > 0) {
        $msg = $en ? 'Device saved and linked to its protector.' : 'تم حفظ الجهاز وربطه بالاسكرينة.';
    } else {
        $msg = $en
            ? 'Device saved on its own — link it to a protector from the protector screen.'
            : 'تم حفظ الجهاز لوحده — اربطه باسكرينة من شاشة الاسكرينات.';
    }
    jsonResponse([
        'ok' => true,
        'id' => $id,
        'label' => trim($brand . ' ' . $modelName),
        'screen_inch' => $inch,
        'linked_screens' => $linked,
        'standalone' => $linked === 0,
        'message' => $msg,
    ]);
}

if ($action === 'save_size') {
    $width = round((float)($input['width_mm'] ?? 0), 2);
    $height = round((float)($input['height_mm'] ?? 0), 2);
    if ($width <= 0 || $height <= 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Enter the width and height.' : 'اكتب العرض والطول.'], 400);
    }
    $typeSlugs = $input['types'] ?? [];
    if (is_string($typeSlugs)) {
        $typeSlugs = array_filter(array_map('trim', explode(',', $typeSlugs)));
    }
    if (!is_array($typeSlugs) || !$typeSlugs) {
        $typeSlugs = array_column(screenTypes($pdo), 'slug');
    }
    // snap to an existing glass size so the catalog never fragments into near-duplicates
    $near = $pdo->prepare('SELECT width_mm, height_mm FROM screen_protectors WHERE width_mm BETWEEN ? AND ? AND height_mm BETWEEN ? AND ? ORDER BY ABS(width_mm - ?) + ABS(height_mm - ?) ASC LIMIT 1');
    $near->execute([$width - 1.0, $width + 1.0, $height - 1.5, $height + 1.5, $width, $height]);
    $snapped = $near->fetch();
    if ($snapped) {
        $width = round((float)$snapped['width_mm'], 2);
        $height = round((float)$snapped['height_mm'], 2);
    }
    $diag = round(sqrt($width ** 2 + $height ** 2) / 25.4, 2);
    $tier = 0;
    $created = 0;
    $now = date('Y-m-d H:i:s');
    foreach ($typeSlugs as $slug) {
        $slug = trim((string)$slug);
        if ($slug === '') {
            continue;
        }
        $dup = $pdo->prepare('SELECT id FROM screen_protectors WHERE type_slug = ? AND width_mm = ? AND height_mm = ? LIMIT 1');
        $dup->execute([$slug, $width, $height]);
        if ($dup->fetchColumn()) {
            continue;
        }
        $price = typePrice($pdo, $slug);
        $sell = (float)($price['sell_price'] ?? 0);
        $cost = (float)($price['cost_price'] ?? 0);
        $minSell = (float)($price['min_sell_price'] ?? 0);
        $barcode = generateUniqueScreenCode($pdo);
        $pdo->prepare('
            INSERT INTO screen_protectors
                (barcode, code_type, type_slug, width_mm, height_mm, diag_inch, size_tier,
                 purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 3, ?, ?)
        ')->execute([$barcode, 'qr', $slug, $width, $height, $diag, $tier, $cost, $sell, $minSell, $now, $now]);
        $sid = (int)$pdo->lastInsertId();
        copySiblingLinks($pdo, $sid);
        syncScreenToProduct($pdo, $sid, $en);
        $created++;
    }
    jsonResponse([
        'ok' => true,
        'created' => $created,
        'message' => $en ? "{$created} protector(s) created for this size." : "تم إنشاء {$created} اسكرينة للمقاس ده.",
    ]);
}

if ($action === 'add_existing_size') {
    $width = round((float)($input['width_mm'] ?? 0), 2);
    $height = round((float)($input['height_mm'] ?? 0), 2);
    $typeSlugs = $input['types'] ?? [];
    if (is_string($typeSlugs)) {
        $typeSlugs = array_filter(array_map('trim', explode(',', $typeSlugs)));
    }
    if (!is_array($typeSlugs) || !$typeSlugs || $width <= 0 || $height <= 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Pick a size and at least one type.' : 'اختار مقاس ونوع واحد على الأقل.'], 400);
    }
    $diag = round(sqrt($width ** 2 + $height ** 2) / 25.4, 2);
    $tier = 0;
    $now = date('Y-m-d H:i:s');
    $created = 0;
    $ids = [];
    foreach ($typeSlugs as $slug) {
        $slug = trim((string)$slug);
        $dup = $pdo->prepare('SELECT id FROM screen_protectors WHERE type_slug = ? AND width_mm = ? AND height_mm = ? LIMIT 1');
        $dup->execute([$slug, $width, $height]);
        $existing = (int)$dup->fetchColumn();
        if ($existing > 0) {
            $ids[] = $existing;
            continue;
        }
        $price = typePrice($pdo, $slug);
        $barcode = generateUniqueScreenCode($pdo);
        $pdo->prepare('
            INSERT INTO screen_protectors
                (barcode, code_type, type_slug, width_mm, height_mm, diag_inch, size_tier,
                 purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 3, ?, ?)
        ')->execute([
            $barcode, 'qr', $slug, $width, $height, $diag, $tier,
            (float)($price['cost_price'] ?? 0), (float)($price['sell_price'] ?? 0), (float)($price['min_sell_price'] ?? 0),
            $now, $now,
        ]);
        $ids[] = (int)$pdo->lastInsertId();
        $created++;
    }
    foreach ($ids as $sid) {
        copySiblingLinks($pdo, $sid);
        syncScreenToProduct($pdo, $sid, $en);
    }
    jsonResponse([
        'ok' => true,
        'created' => $created,
        'ids' => $ids,
        'message' => $en ? "{$created} protector(s) added for this size." : "تم إضافة {$created} اسكرينة للمقاس ده.",
    ]);
}

if ($action === 'delete_size') {
    $width = round((float)($input['width_mm'] ?? 0), 2);
    $height = round((float)($input['height_mm'] ?? 0), 2);
    $stmt = $pdo->prepare('SELECT id FROM screen_protectors WHERE width_mm = ? AND height_mm = ?');
    $stmt->execute([$width, $height]);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    foreach ($ids as $sid) {
        $pdo->prepare('DELETE FROM screen_compatibility WHERE screen_id = ?')->execute([$sid]);
        $pdo->prepare('DELETE FROM screen_protectors WHERE id = ?')->execute([$sid]);
    }
    jsonResponse(['ok' => true, 'deleted' => count($ids), 'message' => $en ? 'Size removed.' : 'تم حذف المقاس.']);
}

if ($action === 'adjust') {
    $id = (int)($input['id'] ?? 0);
    $delta = (int)($input['delta'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Screen not found.' : 'الاسكرينة غير موجودة.'], 404);
    }
    $before = (int)$row['quantity'];
    $after = max(0, $before + $delta);
    $now = date('Y-m-d H:i:s');
    $pdo->prepare('UPDATE screen_protectors SET quantity = ?, updated_at = ? WHERE id = ?')->execute([$after, $now, $id]);
    $mv = $pdo->prepare('INSERT INTO screen_movements (screen_id, movement_type, qty_before, qty_after, note, user_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $mv->execute([$id, 'adjust', $before, $after, $en ? 'Quick stock adjust' : 'تعديل سريع للرصيد', $user['full_name'] ?? '', $now]);
    syncScreenToProduct($pdo, $id, $en);
    jsonResponse(['ok' => true, 'message' => $en ? 'Stock updated.' : 'تم تحديث الرصيد.', 'quantity' => $after]);
}

if ($action === 'delete') {
    $id = (int)($input['id'] ?? 0);
    $pdo->prepare('DELETE FROM screen_compatibility WHERE screen_id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM screen_protectors WHERE id = ?')->execute([$id]);
    jsonResponse(['ok' => true, 'message' => $en ? 'Screen removed.' : 'تم حذف الاسكرينة.']);
}

if ($action === 'move') {
    $id = (int)($input['id'] ?? 0);
    $shelf = trim((string)($input['shelf_code'] ?? ''));
    $drawer = trim((string)($input['drawer_label'] ?? ''));
    $pdo->prepare('UPDATE screen_protectors SET shelf_code = ?, drawer_label = ?, updated_at = ? WHERE id = ?')
        ->execute([$shelf ?: null, $drawer ?: null, date('Y-m-d H:i:s'), $id]);
    jsonResponse(['ok' => true, 'message' => $en ? 'Location updated.' : 'تم تحديث المكان.']);
}

jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);

/* ------------------------------------------------------------ shared helpers */

/** every protector of the same glass (one card = all its types) */
function siblingScreenIds(PDO $pdo, array $screen): array
{
    $stmt = $pdo->prepare('
        SELECT id FROM screen_protectors
        WHERE is_active = 1 AND ROUND(width_mm, 1) = ROUND(?, 1) AND ROUND(height_mm, 1) = ROUND(?, 1)
    ');
    $stmt->execute([(float)($screen['width_mm'] ?? 0), (float)($screen['height_mm'] ?? 0)]);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    if (!in_array((int)$screen['id'], $ids, true)) {
        $ids[] = (int)$screen['id'];
    }
    return $ids;
}

/** a new protector takes the device list of the protectors of the very same size (one ZEUS sheet = one size) */
function copySiblingLinks(PDO $pdo, int $screenId): int
{
    $linkSql = DB_DRIVER === 'sqlite'
        ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id, link_type)'
        : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id, link_type)';
    $stmt = $pdo->prepare($linkSql . '
        SELECT DISTINCT ?, c.model_id, c.link_type FROM screen_protectors me
        INNER JOIN screen_protectors sib
            ON ROUND(sib.width_mm, 1) = ROUND(me.width_mm, 1)
           AND ROUND(sib.height_mm, 1) = ROUND(me.height_mm, 1)
           AND sib.id <> me.id
        INNER JOIN screen_compatibility c ON c.screen_id = sib.id
        WHERE me.id = ?
    ');
    $stmt->execute([$screenId, $screenId]);
    $count = $pdo->prepare('SELECT COUNT(*) FROM screen_compatibility WHERE screen_id = ?');
    $count->execute([$screenId]);
    return (int)$count->fetchColumn();
}

function applyInventoryStock(PDO $pdo, array $row): array
{
    if (empty($row['product_id'])) {
        return $row;
    }
    $stmt = $pdo->prepare('SELECT quantity FROM products WHERE id = ?');
    $stmt->execute([(int)$row['product_id']]);
    $qty = $stmt->fetchColumn();
    if ($qty !== false) {
        $row['quantity'] = (int)$qty;
        $row['is_low'] = $row['quantity'] <= (int)$row['low_stock_threshold'];
        $row['is_out'] = $row['quantity'] <= 0;
    }
    return $row;
}

function syncScreenToProduct(PDO $pdo, int $screenId, bool $en): void
{
    $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
    $stmt->execute([$screenId]);
    $screen = $stmt->fetch();
    if (!$screen) {
        return;
    }
    $typeStmt = $pdo->prepare('SELECT * FROM screen_types WHERE slug = ? LIMIT 1');
    $typeStmt->execute([$screen['type_slug']]);
    $type = $typeStmt->fetch() ?: null;
    $label = $type ? ((string)$type['name_ar'] . ' · ' . sizeLabel($screen)) : sizeLabel($screen);
    $labelEn = $type ? ((string)$type['name_en'] . ' · ' . sizeLabel($screen)) : sizeLabel($screen);
    $now = date('Y-m-d H:i:s');
    $productId = (int)($screen['product_id'] ?? 0);
    if ($productId > 0) {
        $check = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $check->execute([$productId]);
        if (!$check->fetch()) {
            $productId = 0;
        }
    }
    if ($productId === 0) {
        $find = $pdo->prepare('SELECT id FROM products WHERE barcode = ? LIMIT 1');
        $find->execute([$screen['barcode']]);
        $existing = (int)$find->fetchColumn();
        if ($existing > 0) {
            $productId = $existing;
            $pdo->prepare('UPDATE screen_protectors SET product_id = ? WHERE id = ?')->execute([$productId, $screenId]);
        }
    }
    if ($productId > 0) {
        $pdo->prepare("
            UPDATE products SET barcode = ?, code_type = ?, name = ?, name_en = ?, category = 'screens',
                screen_type = ?, width_mm = ?, height_mm = ?,
                purchase_price = ?, sell_price = ?, min_sell_price = ?, quantity = ?, low_stock_threshold = ?,
                show_on_landing = 0, updated_at = ?
            WHERE id = ?
        ")->execute([
            $screen['barcode'], $screen['code_type'] ?: 'qr', $label, $labelEn,
            $screen['type_slug'], $screen['width_mm'], $screen['height_mm'],
            $screen['purchase_price'], $screen['sell_price'], $screen['min_sell_price'],
            $screen['quantity'], $screen['low_stock_threshold'], $now, $productId,
        ]);
        return;
    }
    $ins = $pdo->prepare("
        INSERT INTO products
            (barcode, code_type, name, name_en, category, condition_status, screen_type, width_mm, height_mm,
             purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, show_on_landing, is_featured, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'screens', 'new', ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)
    ");
    $ins->execute([
        $screen['barcode'], $screen['code_type'] ?: 'qr', $label, $labelEn,
        $screen['type_slug'], $screen['width_mm'], $screen['height_mm'],
        $screen['purchase_price'], $screen['sell_price'], $screen['min_sell_price'],
        $screen['quantity'], $screen['low_stock_threshold'], $now, $now,
    ]);
    $newId = (int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE screen_protectors SET product_id = ? WHERE id = ?')->execute([$newId, $screenId]);
}
