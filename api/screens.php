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

function norm(string $value): string
{
    $digits = ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9'];
    $value = strtr($value, $digits);
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = str_replace(['أ', 'إ', 'آ', 'ى', 'ة'], ['ا', 'ا', 'ا', 'ي', 'ه'], $value);
    $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?: '';
    return trim(preg_replace('/\s+/u', ' ', $value) ?: '');
}

function modelNoiseWords(): array
{
    return ['شاشه', 'شاشات', 'زجاج', 'حمايه', 'سكرين', 'سكرينة', 'اسكرينة', 'اسكرينه', 'سكرينه', 'screen', 'screens', 'protector', 'protection', 'glass', 'tempered', 'guard', 'for', 'the', '9h', 'فيلم', 'فيلمه', 'سعر', 'price'];
}

function modelAliases(): array
{
    return [
        'جلاكسي' => 'galaxy', 'جالاكسي' => 'galaxy', 'جلاكسى' => 'galaxy', 'جالكسي' => 'galaxy',
        'سامسونج' => 'samsung', 'سامسونغ' => 'samsung', 'سامسونق' => 'samsung',
        'ايفون' => 'iphone', 'ايفوون' => 'iphone', 'ابل' => 'apple',
        'شاومي' => 'xiaomi', 'شياومي' => 'xiaomi', 'ريدمي' => 'redmi', 'ريدمى' => 'redmi',
        'هواوي' => 'huawei', 'هوواوي' => 'huawei', 'اوبو' => 'oppo', 'انفينكس' => 'infinix',
        'انفنكس' => 'infinix', 'فيفو' => 'vivo', 'ريلمي' => 'realme', 'ريلمى' => 'realme',
        'نوكيا' => 'nokia', 'هونر' => 'honor', 'تكنو' => 'tecno', 'موتورولا' => 'motorola',
        'بيكسل' => 'pixel', 'نوثينج' => 'nothing', 'ايتل' => 'itel', 'لينوفو' => 'lenovo',
        'ون' => 'one', 'بوكو' => 'poco', 'نوت' => 'note', 'برو' => 'pro', 'ماكس' => 'max',
        'بلس' => 'plus', 'بلص' => 'plus', 'لايت' => 'lite', 'ايه' => 'a', 'ايج' => 'a',
        'بي' => 'b', 'سي' => 'c', 'دي' => 'd', 'اكس' => 'x', 'اس' => 's', 'جي' => 'g',
        'ام' => 'm', 'تي' => 't', 'زد' => 'z', 'كيه' => 'k', 'في' => 'v', 'واي' => 'y',
    ];
}

function modelTokens(string $value): array
{
    $clean = norm($value);
    $noise = modelNoiseWords();
    $aliases = modelAliases();
    $tokens = [];
    foreach (explode(' ', $clean) as $word) {
        if ($word === '' || in_array($word, $noise, true)) {
            continue;
        }
        $tokens[] = $aliases[$word] ?? $word;
    }
    return $tokens;
}

function modelMatchesQuery(string $haystack, array $tokens): bool
{
    if (!$tokens) {
        return false;
    }
    $words = preg_split('/\s+/u', trim($haystack)) ?: [];
    foreach ($tokens as $token) {
        $found = false;
        if (mb_strpos($haystack, $token, 0, 'UTF-8') !== false) {
            foreach ($words as $word) {
                if ($word === '') {
                    continue;
                }
                if ($word === $token || mb_strpos($word, $token, 0, 'UTF-8') === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found && mb_strlen($token, 'UTF-8') >= 5) {
                $found = true;
            }
        }
        if (!$found) {
            return false;
        }
    }
    return true;
}

function modelScoreQuery(string $haystack, array $tokens): int
{
    $score = 0;
    foreach ($tokens as $token) {
        if (preg_match('/(^|\s)' . preg_quote($token, '/') . '(\s|$)/u', $haystack)) {
            $score += 3;
        } elseif (mb_strpos($haystack, $token, 0, 'UTF-8') !== false) {
            $score += 1;
        }
    }
    return $score;
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

function findModel(PDO $pdo, string $q): ?array
{
    $tokens = modelTokens($q);
    if (!$tokens) {
        return null;
    }
    $all = $pdo->query('SELECT * FROM device_models WHERE is_active = 1 ORDER BY brand ASC, model_name ASC')->fetchAll();
    $scored = [];
    foreach ($all as $m) {
        $hay = norm($m['brand'] . ' ' . $m['model_name'] . ' ' . (string)($m['model_name_ar'] ?? ''));
        if (modelMatchesQuery($hay, $tokens)) {
            $scored[] = [modelScoreQuery($hay, $tokens), mb_strlen((string)$m['model_name'], 'UTF-8'), $m];
        }
    }
    if (!$scored) {
        return null;
    }
    usort($scored, function ($a, $b) {
        if ($a[0] === $b[0]) {
            return $a[1] <=> $b[1];
        }
        return $b[0] <=> $a[0];
    });
    // exact name wins over prefix matches
    $qNorm = implode(' ', $tokens);
    foreach ($scored as $row) {
        $m = $row[2];
        $short = norm((string)$m['model_name']);
        $full = norm($m['brand'] . ' ' . $m['model_name']);
        if ($qNorm === $short || $qNorm === $full) {
            return $m;
        }
    }
    return $scored[0][2];
}

function suggestModels(PDO $pdo, string $q, int $limit = 8): array
{
    $tokens = modelTokens($q);
    if (!$tokens) {
        return [];
    }
    $all = $pdo->query('SELECT * FROM device_models WHERE is_active = 1 ORDER BY brand ASC, model_name ASC')->fetchAll();
    $out = [];
    foreach ($all as $m) {
        $hay = norm($m['brand'] . ' ' . $m['model_name'] . ' ' . (string)($m['model_name_ar'] ?? ''));
        if (!modelMatchesQuery($hay, $tokens)) {
            continue;
        }
        $out[] = [
            'score' => modelScoreQuery($hay, $tokens),
            'len' => mb_strlen((string)$m['model_name'], 'UTF-8'),
            'model' => $m,
        ];
    }
    $qNorm = implode(' ', $tokens);
    foreach ($out as &$row) {
        $short = norm((string)$row['model']['model_name']);
        $full = norm($row['model']['brand'] . ' ' . $row['model']['model_name']);
        if ($qNorm !== '' && ($qNorm === $short || $qNorm === $full)) {
            $row['score'] += 10;
        }
    }
    unset($row);
    usort($out, function ($a, $b) {
        if ($a['score'] === $b['score']) {
            return $a['len'] <=> $b['len'];
        }
        return $b['score'] <=> $a['score'];
    });
    $list = [];
    foreach (array_slice($out, 0, $limit) as $row) {
        $m = $row['model'];
        $list[] = [
            'id' => (int)$m['id'],
            'brand' => $m['brand'],
            'model_name' => $m['model_name'],
            'model_name_ar' => $m['model_name_ar'],
            'label' => trim($m['brand'] . ' ' . $m['model_name']),
            'screen_inch' => $m['screen_inch'] !== null ? (float)$m['screen_inch'] : null,
        ];
    }
    return $list;
}

function labelForModel(array $model): string
{
    return trim($model['brand'] . ' ' . $model['model_name']);
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
        if ($perPage > 60) {
            $perPage = 60;
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM screen_protectors' . $whereSql);
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();

        $sql = 'SELECT * FROM screen_protectors' . $whereSql
            . ' ORDER BY (SELECT COUNT(*) FROM screen_compatibility c WHERE c.screen_id = screen_protectors.id) DESC, height_mm DESC, width_mm DESC, ' . typeOrderExpr();
        $sql .= DB_DRIVER === 'sqlite' ? ' LIMIT ' . $perPage . ' OFFSET ' . $offset : ' LIMIT ' . $offset . ', ' . $perPage;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
        $rows = decorateScreens($pdo, $rows);

        $stats = [
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
        // the searched device always heads the card and the compatibility list
        $deviceLabel = labelForModel($model);
        $modelId = (int)$model['id'];
        foreach ($rows as &$r) {
            $opts = $r['models'] ?? [];
            usort($opts, function ($a, $b) use ($modelId) {
                if ((int)$a['id'] === $modelId) {
                    return -1;
                }
                if ((int)$b['id'] === $modelId) {
                    return 1;
                }
                return strcmp((string)$a['label'], (string)$b['label']);
            });
            $r['models'] = $opts;
            $r['first_model'] = $opts[0]['label'] ?? $deviceLabel;
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
        ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)'
        : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)';
    $link = $pdo->prepare($linkSql);
    $added = 0;
    foreach (array_unique(array_map('intval', (array)$modelIds)) as $mid) {
        if ($mid > 0) {
            $link->execute([$id, $mid]);
            $added++;
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
    $pdo->prepare('DELETE FROM screen_compatibility WHERE screen_id = ? AND model_id = ?')->execute([$id, $modelId]);
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
