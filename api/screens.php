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
$writeActions = ['save', 'adjust', 'delete', 'move', 'save_model'];
if (in_array($action, $writeActions, true)) {
    requireAdminApi();
}

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
    return ['شاشه', 'شاشات', 'زجاج', 'حمايه', 'سكرين', 'سكرينة', 'اسكرينة', 'اسكرينه', 'سكرينه', 'screen', 'screens', 'protector', 'protection', 'glass', 'tempered', 'guard', 'for', 'the', '9h', 'فيلم', 'فيلمه'];
}

function modelAliases(): array
{
    return [
        'جلاكسي' => 'galaxy', 'جالاكسي' => 'galaxy', 'جلاكسى' => 'galaxy', 'جالكسي' => 'galaxy',
        'سامسونج' => 'samsung', 'سامسونغ' => 'samsung',
        'ايفون' => 'iphone', 'ايفوون' => 'iphone', 'ابل' => 'apple', 'أبل' => 'apple',
        'شاومي' => 'xiaomi', 'شياومي' => 'xiaomi', 'ريدمي' => 'redmi', 'ريدمى' => 'redmi',
        'هواوي' => 'huawei', 'اوبو' => 'oppo', 'أوبو' => 'oppo', 'انفينكس' => 'infinix',
        'فيفو' => 'vivo', 'ريلمي' => 'realme', 'ريلمى' => 'realme', 'نوكيا' => 'nokia',
        'هونر' => 'honor', 'تكنو' => 'tecno', 'نوت' => 'note', 'برو' => 'pro', 'ماكس' => 'max',
        'بلس' => 'plus', 'لايت' => 'lite', 'ايه' => 'a', 'ايج' => 'a', 'بي' => 'b', 'سي' => 'c',
        'دي' => 'd', 'اكس' => 'x', 'اس' => 's', 'جي' => 'g', 'ام' => 'm', 'تي' => 't',
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
    foreach ($tokens as $token) {
        if (mb_strpos($haystack, $token, 0, 'UTF-8') === false) {
            return false;
        }
    }
    return true;
}

function screenRow(array $row, bool $en): array
{
    $langKey = $en ? 'en' : 'ar';
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
    $row['display_name'] = $en && !empty($row['name_en']) ? $row['name_en'] : $row['name'];
    $row['is_low'] = $row['quantity'] <= $row['low_stock_threshold'];
    $row['is_out'] = $row['quantity'] <= 0;
    $row['place_label'] = trim((string)($row['shelf_code'] ?? ''));
    if (!empty($row['drawer_label'])) {
        $row['place_label'] .= ' - ' . $row['drawer_label'];
    }
    if ($row['place_label'] === '') {
        $row['place_label'] = $en ? 'Not placed' : 'غير محدد';
    }
    return $row;
}

function modelsForScreens(PDO $pdo, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $safe = array_map('intval', $ids);
    $in = implode(',', $safe);
    $rows = $pdo->query("
        SELECT c.screen_id, m.id, m.brand, m.model_name, m.model_name_ar
        FROM screen_compatibility c
        INNER JOIN device_models m ON m.id = c.model_id
        WHERE c.screen_id IN ({$in})
        ORDER BY m.brand ASC, m.model_name ASC
    ")->fetchAll();
    $map = [];
    foreach ($rows as $r) {
        $sid = (int)$r['screen_id'];
        if (!isset($map[$sid])) {
            $map[$sid] = [];
        }
        $map[$sid][] = [
            'id' => (int)$r['id'],
            'brand' => $r['brand'],
            'model_name' => $r['model_name'],
            'model_name_ar' => $r['model_name_ar'],
            'label' => trim($r['brand'] . ' ' . $r['model_name']),
        ];
    }
    return $map;
}

if ($method === 'GET') {
    if ($action === 'list') {
        $q = trim((string)($_GET['q'] ?? ''));
        $missing = ($_GET['missing'] ?? '') === '1';
        $shelf = trim((string)($_GET['shelf'] ?? ''));
        $where = [];
        $params = [];
        if ($missing) {
            $where[] = 'quantity <= low_stock_threshold';
        }
        if ($shelf !== '') {
            $where[] = 'shelf_code = ?';
            $params[] = $shelf;
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] = "(name LIKE ? OR COALESCE(name_en,'') LIKE ? OR barcode LIKE ? OR COALESCE(shelf_code,'') LIKE ? OR COALESCE(drawer_label,'') LIKE ?)";
            array_push($params, $like, $like, $like, $like, $like);
        }
        $sql = 'SELECT * FROM screen_protectors';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY (quantity <= low_stock_threshold) DESC, name ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
        $models = modelsForScreens($pdo, array_column($rows, 'id'));
        foreach ($rows as &$r) {
            $r['models'] = $models[$r['id']] ?? [];
        }
        unset($r);
        jsonResponse(['ok' => true, 'screens' => $rows, 'is_admin' => $isAdmin]);
    }

    if ($action === 'search_by_model') {
        $modelId = (int)($_GET['model_id'] ?? 0);
        $q = trim((string)($_GET['q'] ?? ''));
        $stocks = [];

        $model = null;
        if ($modelId > 0) {
            $ms = $pdo->prepare('SELECT * FROM device_models WHERE id = ?');
            $ms->execute([$modelId]);
            $model = $ms->fetch() ?: null;
        }

        if ($q !== '') {
            $tokens = modelTokens($q);
            $all = $pdo->query('SELECT * FROM device_models WHERE is_active = 1 ORDER BY brand ASC, model_name ASC')->fetchAll();
            $matched = [];
            $loose = [];
            foreach ($all as $m) {
                $hay = norm($m['search_key'] . ' ' . $m['brand'] . ' ' . $m['model_name'] . ' ' . (string)$m['model_name_ar']);
                if ($tokens && modelMatchesQuery($hay, $tokens)) {
                    $matched[] = $m;
                    continue;
                }
                foreach ($tokens as $token) {
                    if (mb_strpos($hay, $token, 0, 'UTF-8') !== false) {
                        $loose[] = $m;
                        break;
                    }
                }
            }
            if (!$matched && $loose) {
                $matched = $loose;
            }
            if ($modelId === 0 && count($matched) === 1) {
                $model = $matched[0];
                $modelId = (int)$matched[0]['id'];
            }
            $stocks = [];
            foreach ($matched as $m) {
                $stocks[] = [
                    'id' => (int)$m['id'],
                    'brand' => $m['brand'],
                    'model_name' => $m['model_name'],
                    'model_name_ar' => $m['model_name_ar'],
                    'label' => trim($m['brand'] . ' ' . $m['model_name']),
                ];
            }
        }

        $screens = [];
        if ($modelId === 0 && $stocks) {
            $unionIds = implode(',', array_map('intval', array_column($stocks, 'id')));
            $stmtAll = $pdo->query("
                SELECT DISTINCT s.* FROM screen_protectors s
                INNER JOIN screen_compatibility c ON c.screen_id = s.id
                WHERE c.model_id IN ({$unionIds}) AND s.is_active = 1
                ORDER BY (s.quantity <= s.low_stock_threshold) ASC, s.quantity DESC, s.sell_price ASC
            ");
            $screens = array_map(fn($r) => screenRow($r, $en), $stmtAll->fetchAll());
            $models = modelsForScreens($pdo, array_column($screens, 'id'));
            foreach ($screens as &$r) {
                $r['models'] = $models[$r['id']] ?? [];
            }
            unset($r);
        }
        if ($modelId > 0) {
            $stmt = $pdo->prepare("
                SELECT s.* FROM screen_protectors s
                INNER JOIN screen_compatibility c ON c.screen_id = s.id
                WHERE c.model_id = ? AND s.is_active = 1
                ORDER BY (s.quantity <= s.low_stock_threshold) ASC, s.quantity DESC, s.sell_price ASC
            ");
            $stmt->execute([$modelId]);
            $screens = array_map(fn($r) => screenRow($r, $en), $stmt->fetchAll());
            $models = modelsForScreens($pdo, array_column($screens, 'id'));
            foreach ($screens as &$r) {
                $r['models'] = $models[$r['id']] ?? [];
            }
            unset($r);
        }

        jsonResponse([
            'ok' => true,
            'model' => $model ? [
                'id' => (int)$model['id'],
                'brand' => $model['brand'],
                'model_name' => $model['model_name'],
                'model_name_ar' => $model['model_name_ar'],
                'label' => trim($model['brand'] . ' ' . $model['model_name']),
            ] : null,
            'suggestions' => $stocks,
            'screens' => $screens,
            'is_admin' => $isAdmin,
        ]);
    }

    if ($action === 'models') {
        $q = trim((string)($_GET['q'] ?? ''));
        $stmt = $pdo->query('SELECT * FROM device_models WHERE is_active = 1 ORDER BY brand ASC, model_name ASC');
        $all = $stmt->fetchAll();
        $out = [];
        foreach ($all as $m) {
            if ($q !== '') {
                $needle = norm($q);
                $hay = norm($m['search_key'] . ' ' . $m['brand'] . ' ' . $m['model_name'] . ' ' . (string)$m['model_name_ar']);
                if ($needle !== '' && strpos($hay, $needle) === false) {
                    continue;
                }
            }
            $out[] = [
                'id' => (int)$m['id'],
                'brand' => $m['brand'],
                'model_name' => $m['model_name'],
                'model_name_ar' => $m['model_name_ar'],
                'label' => trim($m['brand'] . ' ' . $m['model_name']),
            ];
        }
        jsonResponse(['ok' => true, 'models' => $out]);
    }

    if ($action === 'shelves') {
        $rows = $pdo->query('SELECT * FROM shelves ORDER BY sort_order ASC, shelf_code ASC')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[] = ['id' => (int)$r['id'], 'shelf_code' => $r['shelf_code'], 'label' => $r['label'] ?: $r['shelf_code']];
        }
        jsonResponse(['ok' => true, 'shelves' => $out]);
    }

    if ($action === 'missing') {
        $rows = $pdo->query("
            SELECT * FROM screen_protectors
            WHERE is_active = 1 AND quantity <= low_stock_threshold
            ORDER BY quantity ASC, name ASC
        ")->fetchAll();
        $list = array_map(fn($r) => screenRow($r, $en), $rows);
        $models = modelsForScreens($pdo, array_column($list, 'id'));
        foreach ($list as &$r) {
            $r['models'] = $models[$r['id']] ?? [];
        }
        unset($r);
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
        $models = modelsForScreens($pdo, [$id]);
        $data['models'] = $models[$id] ?? [];
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
        $models = modelsForScreens($pdo, [(int)$row['id']]);
        $data['models'] = $models[(int)$row['id']] ?? [];
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

if ($action === 'save') {
    $id = (int)($input['id'] ?? 0);
    $name = trim((string)($input['name'] ?? ''));
    $nameEn = trim((string)($input['name_en'] ?? ''));
    $barcode = trim((string)($input['barcode'] ?? ''));
    $codeType = trim((string)($input['code_type'] ?? 'qr'));
    $quality = trim((string)($input['quality'] ?? 'standard'));
    $shelfCode = trim((string)($input['shelf_code'] ?? ''));
    $drawer = trim((string)($input['drawer_label'] ?? ''));
    $purchase = round((float)($input['purchase_price'] ?? 0), 2);
    $sell = round((float)($input['sell_price'] ?? 0), 2);
    $minSell = round((float)($input['min_sell_price'] ?? 0), 2);
    $qty = max(0, (int)($input['quantity'] ?? 0));
    $lowStock = max(0, (int)($input['low_stock_threshold'] ?? 3));
    $notes = trim((string)($input['notes'] ?? ''));

    if ($name === '' && $nameEn !== '') {
        $name = $nameEn;
    }
    if ($name === '') {
        jsonResponse(['ok' => false, 'error' => $en ? 'Please enter the screen name.' : 'يرجى إدخال اسم الاسكرينة.'], 400);
    }
    if ($sell <= 0) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Please enter a valid selling price.' : 'يرجى إدخال سعر بيع صحيح.'], 400);
    }
    if ($minSell <= 0) {
        $minSell = $sell;
    }
    if ($minSell > $sell) {
        jsonResponse(['ok' => false, 'error' => $en ? 'Minimum price cannot exceed the selling price.' : 'أقل سعر لا يمكن أن يكون أكبر من سعر البيع.'], 400);
    }
    if ($barcode === '') {
        $barcode = generateUniqueScreenCode($pdo);
        $codeType = 'qr';
    }

    $dupStmt = $pdo->prepare('SELECT id FROM screen_protectors WHERE barcode = ? AND id != ? LIMIT 1');
    $dupStmt->execute([$barcode, $id]);
    if ($dupStmt->fetch()) {
        jsonResponse(['ok' => false, 'error' => $en ? 'This code is already used by another screen.' : 'هذا الكود مستخدم بالفعل لاسكرينة أخرى.'], 409);
    }

    $now = date('Y-m-d H:i:s');
    $before = 0;
    if ($id > 0) {
        $cur = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
        $cur->execute([$id]);
        $existing = $cur->fetch();
        if (!$existing) {
            jsonResponse(['ok' => false, 'error' => $en ? 'Screen not found.' : 'الاسكرينة غير موجودة.'], 404);
        }
        $before = (int)$existing['quantity'];
        $upd = $pdo->prepare("
            UPDATE screen_protectors SET
                barcode = ?, code_type = ?, name = ?, name_en = ?, quality = ?,
                shelf_code = ?, drawer_label = ?, purchase_price = ?, sell_price = ?, min_sell_price = ?,
                quantity = ?, low_stock_threshold = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ");
        $upd->execute([
            $barcode, $codeType, $name, $nameEn ?: null, $quality,
            $shelfCode ?: null, $drawer ?: null, $purchase, $sell, $minSell,
            $qty, $lowStock, $notes ?: null, $now, $id,
        ]);
        if ($before !== $qty) {
            $mv = $pdo->prepare('INSERT INTO screen_movements (screen_id, movement_type, qty_before, qty_after, note, user_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $mv->execute([$id, 'edit', $before, $qty, $en ? 'Edited from dashboard' : 'تعديل من لوحة التحكم', $user['full_name'] ?? '', $now]);
        }
    } else {
        $ins = $pdo->prepare("
            INSERT INTO screen_protectors
                (barcode, code_type, name, name_en, quality, shelf_code, drawer_label,
                 purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, notes, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $barcode, $codeType, $name, $nameEn ?: null, $quality,
            $shelfCode ?: null, $drawer ?: null, $purchase, $sell, $minSell,
            $qty, $lowStock, $notes ?: null, (int)($user['id'] ?? 0), $now, $now,
        ]);
        $id = (int)$pdo->lastInsertId();
        $mv = $pdo->prepare('INSERT INTO screen_movements (screen_id, movement_type, qty_before, qty_after, note, user_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $mv->execute([$id, 'create', 0, $qty, $en ? 'Created from dashboard' : 'تسجيل جديد', $user['full_name'] ?? '', $now]);
    }

    $modelIds = $input['model_ids'] ?? [];
    if (is_string($modelIds)) {
        $modelIds = array_filter(array_map('intval', explode(',', $modelIds)));
    }
    if (!is_array($modelIds)) {
        $modelIds = [];
    }
    $modelIds = array_values(array_unique(array_map('intval', $modelIds)));
    $pdo->prepare('DELETE FROM screen_compatibility WHERE screen_id = ?')->execute([$id]);
    if ($modelIds) {
        $linkSql = DB_DRIVER === 'sqlite'
            ? 'INSERT OR IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)'
            : 'INSERT IGNORE INTO screen_compatibility (screen_id, model_id) VALUES (?, ?)';
        $link = $pdo->prepare($linkSql);
        foreach ($modelIds as $mid) {
            if ($mid > 0) {
                $link->execute([$id, $mid]);
            }
        }
    }

    syncScreenToProduct($pdo, $id, $en);

    jsonResponse([
        'ok' => true,
        'message' => $en ? 'Screen saved successfully.' : 'تم حفظ الاسكرينة بنجاح.',
        'id' => $id,
        'barcode' => $barcode,
        'quantity' => $qty,
    ]);
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

if ($action === 'save_model') {
    $brand = trim((string)($input['brand'] ?? ''));
    $modelName = trim((string)($input['model_name'] ?? ''));
    $modelAr = trim((string)($input['model_name_ar'] ?? ''));
    if ($brand === '' || $modelName === '') {
        jsonResponse(['ok' => false, 'error' => $en ? 'Brand and model are required.' : 'الماركة والموديل مطلوبين.'], 400);
    }
    $key = norm($brand . ' ' . $modelName . ' ' . $modelAr);
    $dup = $pdo->prepare('SELECT id FROM device_models WHERE brand = ? AND model_name = ? LIMIT 1');
    $dup->execute([$brand, $modelName]);
    $existingId = $dup->fetchColumn();
    if ($existingId) {
        jsonResponse(['ok' => true, 'id' => (int)$existingId, 'message' => $en ? 'Model already exists.' : 'الموديل موجود بالفعل.']);
    }
    $ins = $pdo->prepare('INSERT INTO device_models (brand, model_name, model_name_ar, search_key, created_at) VALUES (?, ?, ?, ?, ?)');
    $ins->execute([$brand, $modelName, $modelAr ?: null, $key, date('Y-m-d H:i:s')]);
    jsonResponse(['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'message' => $en ? 'Model added.' : 'تمت إضافة الموديل.']);
}

jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);

function syncScreenToProduct(PDO $pdo, int $screenId, bool $en): void
{
    $stmt = $pdo->prepare('SELECT * FROM screen_protectors WHERE id = ?');
    $stmt->execute([$screenId]);
    $screen = $stmt->fetch();
    if (!$screen) {
        return;
    }
    $now = date('Y-m-d H:i:s');
    $payload = [
        'barcode' => $screen['barcode'],
        'code_type' => $screen['code_type'] ?: 'qr',
        'name' => $screen['name'],
        'name_en' => $screen['name_en'],
        'category' => 'screens',
        'condition_status' => 'new',
        'purchase_price' => $screen['purchase_price'],
        'sell_price' => $screen['sell_price'],
        'min_sell_price' => $screen['min_sell_price'],
        'quantity' => $screen['quantity'],
        'low_stock_threshold' => $screen['low_stock_threshold'],
        'specs' => $screen['notes'],
    ];
    $productId = (int)($screen['product_id'] ?? 0);
    if ($productId > 0) {
        $check = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $check->execute([$productId]);
        if (!$check->fetch()) {
            $productId = 0;
        }
    }
    if ($productId > 0) {
        $pdo->prepare("
            UPDATE products SET barcode = ?, code_type = ?, name = ?, name_en = ?, category = 'screens',
                purchase_price = ?, sell_price = ?, min_sell_price = ?, quantity = ?, low_stock_threshold = ?,
                specs = ?, show_on_landing = 0, updated_at = ?
            WHERE id = ?
        ")->execute([
            $payload['barcode'], $payload['code_type'], $payload['name'], $payload['name_en'],
            $payload['purchase_price'], $payload['sell_price'], $payload['min_sell_price'],
            $payload['quantity'], $payload['low_stock_threshold'], $payload['specs'], $now, $productId,
        ]);
        return;
    }
    $find = $pdo->prepare('SELECT id FROM products WHERE barcode = ? LIMIT 1');
    $find->execute([$screen['barcode']]);
    $existing = $find->fetchColumn();
    if ($existing) {
        $pdo->prepare('UPDATE screen_protectors SET product_id = ? WHERE id = ?')->execute([(int)$existing, $screenId]);
        $pdo->prepare("
            UPDATE products SET name = ?, name_en = ?, category = 'screens', purchase_price = ?, sell_price = ?,
                min_sell_price = ?, quantity = ?, low_stock_threshold = ?, specs = ?, show_on_landing = 0, updated_at = ?
            WHERE id = ?
        ")->execute([
            $payload['name'], $payload['name_en'], $payload['purchase_price'], $payload['sell_price'],
            $payload['min_sell_price'], $payload['quantity'], $payload['low_stock_threshold'],
            $payload['specs'], $now, (int)$existing,
        ]);
        return;
    }
    $ins = $pdo->prepare("
        INSERT INTO products
            (barcode, code_type, name, name_en, category, condition_status, specs,
             purchase_price, sell_price, min_sell_price, quantity, low_stock_threshold, show_on_landing, is_featured, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'screens', 'new', ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)
    ");
    $ins->execute([
        $payload['barcode'], $payload['code_type'], $payload['name'], $payload['name_en'],
        $payload['specs'], $payload['purchase_price'], $payload['sell_price'], $payload['min_sell_price'],
        $payload['quantity'], $payload['low_stock_threshold'], $now, $now,
    ]);
    $newId = (int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE screen_protectors SET product_id = ? WHERE id = ?')->execute([$newId, $screenId]);
}
