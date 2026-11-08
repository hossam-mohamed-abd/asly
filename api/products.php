<?php
/**
 * ASLY | أصلي TECH STORE
 * Products Management API (Inventory CRUD, 3-Tier Pricing, Image Upload, QR/Barcode)
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim((string)($_GET['q'] ?? ''));
    $category = trim((string)($_GET['category'] ?? ''));
    $onlyInStock = ($_GET['in_stock'] ?? '') === '1';

    $where = [];
    $params = [];

    if ($category !== '' && $category !== 'all') {
        $where[] = 'category = ?';
        $params[] = $category;
    }
    if ($onlyInStock) {
        $where[] = 'quantity > 0';
    }
    if ($search !== '') {
        $where[] = '(name LIKE ? OR barcode LIKE ? OR brand LIKE ? OR specs LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $sql = 'SELECT * FROM products';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $cats = getCategoryMeta();
    $conds = getConditionMeta();

    foreach ($rows as &$row) {
        $row['id'] = (int)$row['id'];
        $row['sell_price'] = (float)$row['sell_price'];
        $row['min_sell_price'] = (float)$row['min_sell_price'];
        $row['quantity'] = (int)$row['quantity'];
        $row['low_stock_threshold'] = (int)$row['low_stock_threshold'];
        $row['show_on_landing'] = (int)$row['show_on_landing'];
        $row['is_featured'] = (int)$row['is_featured'];
        $row['category_label'] = $cats[$row['category']]['ar'] ?? $row['category'];
        $row['condition_label'] = $conds[$row['condition_status']]['ar'] ?? $row['condition_status'];

        if ($user['role'] === 'admin') {
            $row['purchase_price'] = (float)$row['purchase_price'];
        } else {
            unset($row['purchase_price']);
        }
    }
    unset($row);

    jsonResponse(['ok' => true, 'products' => $rows]);
}

if ($method === 'POST') {
    // All write operations on products require Admin privileges
    requireAdminApi();
    requireCsrfJson();

    $isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
    $input = $isMultipart ? $_POST : readJsonInput();
    $action = $input['action'] ?? 'save';

    if ($action === 'save') {
        $id = (int)($input['id'] ?? 0);
        $name = trim((string)($input['name'] ?? ''));
        $category = trim((string)($input['category'] ?? 'laptops'));
        $conditionStatus = trim((string)($input['condition_status'] ?? 'new'));
        $brand = trim((string)($input['brand'] ?? ''));
        $specs = trim((string)($input['specs'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $barcode = trim((string)($input['barcode'] ?? ''));
        $codeType = trim((string)($input['code_type'] ?? 'qr'));
        $purchasePrice = round((float)($input['purchase_price'] ?? 0), 2);
        $sellPrice = round((float)($input['sell_price'] ?? 0), 2);
        $minSellPrice = round((float)($input['min_sell_price'] ?? 0), 2);
        $quantity = max(0, (int)($input['quantity'] ?? 0));
        $lowStock = max(0, (int)($input['low_stock_threshold'] ?? 2));
        $warranty = trim((string)($input['warranty_default'] ?? 'ضمان الفحص والتجربة - أصلي تك ستور'));
        $showOnLanding = isset($input['show_on_landing']) ? ((int)$input['show_on_landing'] ? 1 : 0) : 1;
        $isFeatured = isset($input['is_featured']) ? ((int)$input['is_featured'] ? 1 : 0) : 0;

        if ($name === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى إدخال اسم المنتج.'], 400);
        }
        if ($sellPrice <= 0) {
            jsonResponse(['ok' => false, 'error' => 'يرجى إدخال سعر البيع بشكل صحيح.'], 400);
        }
        if ($minSellPrice <= 0) {
            $minSellPrice = $sellPrice;
        }
        if ($minSellPrice > $sellPrice) {
            jsonResponse(['ok' => false, 'error' => 'أقل سعر للبيع لا يمكن أن يكون أكبر من سعر البيع الأساسي!'], 400);
        }

        if ($barcode === '') {
            $barcode = generateUniqueProductCode($pdo, $category);
            $codeType = 'qr';
        }

        // Check duplicate barcode
        $dupStmt = $pdo->prepare("SELECT id, name FROM products WHERE barcode = ? AND id != ? LIMIT 1");
        $dupStmt->execute([$barcode, $id]);
        $dup = $dupStmt->fetch();
        if ($dup) {
            jsonResponse([
                'ok' => false,
                'error' => "هذا الكود ({$barcode}) مسجل بالفعل للمنتج: {$dup['name']}",
                'existing_id' => (int)$dup['id'],
            ], 409);
        }

        $uploadedPath = null;
        if (!empty($_FILES['image'])) {
            try {
                $uploadedPath = handleProductImageUpload($_FILES['image']);
            } catch (Throwable $e) {
                jsonResponse(['ok' => false, 'error' => $e->getMessage()], 400);
            }
        }

        $now = date('Y-m-d H:i:s');

        if ($id > 0) {
            $existingStmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
            $existingStmt->execute([$id]);
            $existing = $existingStmt->fetch();
            if (!$existing) {
                jsonResponse(['ok' => false, 'error' => 'المنتج غير موجود.'], 404);
            }
            $finalImage = $uploadedPath ?? ($existing['image_path'] ?: null);

            $upd = $pdo->prepare("
                UPDATE products SET
                    barcode = ?, code_type = ?, name = ?, category = ?, condition_status = ?,
                    brand = ?, specs = ?, description = ?, image_path = ?,
                    purchase_price = ?, sell_price = ?, min_sell_price = ?,
                    quantity = ?, low_stock_threshold = ?, warranty_default = ?,
                    show_on_landing = ?, is_featured = ?, updated_at = ?
                WHERE id = ?
            ");
            $upd->execute([
                $barcode, $codeType, $name, $category, $conditionStatus,
                $brand, $specs, $description, $finalImage,
                $purchasePrice, $sellPrice, $minSellPrice,
                $quantity, $lowStock, $warranty,
                $showOnLanding, $isFeatured, $now,
                $id,
            ]);

            jsonResponse(['ok' => true, 'message' => 'تم تحديث بيانات المنتج بنجاح.', 'id' => $id, 'barcode' => $barcode]);
        } else {
            $defaultSamples = [
                'laptops'     => 'uploads/products/sample-laptop.jpg',
                'mobiles'     => 'uploads/products/sample-mobile.jpg',
                'accessories' => 'uploads/products/sample-accessories.jpg',
                'wearables'   => 'uploads/products/sample-wearable.jpg',
            ];
            $finalImage = $uploadedPath ?? ($defaultSamples[$category] ?? null);

            $ins = $pdo->prepare("
                INSERT INTO products (
                    barcode, code_type, name, category, condition_status,
                    brand, specs, description, image_path,
                    purchase_price, sell_price, min_sell_price,
                    quantity, low_stock_threshold, warranty_default,
                    show_on_landing, is_featured, created_by, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $barcode, $codeType, $name, $category, $conditionStatus,
                $brand, $specs, $description, $finalImage,
                $purchasePrice, $sellPrice, $minSellPrice,
                $quantity, $lowStock, $warranty,
                $showOnLanding, $isFeatured, $user['id'], $now, $now,
            ]);
            $newId = (int)$pdo->lastInsertId();

            jsonResponse(['ok' => true, 'message' => 'تم تسجيل المنتج وتوليد الكود بنجاح.', 'id' => $newId, 'barcode' => $barcode]);
        }
    }

    if ($action === 'toggle_landing') {
        $id = (int)($input['id'] ?? 0);
        $show = (int)($input['show_on_landing'] ?? 0) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE products SET show_on_landing = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$show, date('Y-m-d H:i:s'), $id]);
        jsonResponse(['ok' => true, 'message' => $show ? 'تم إظهار المنتج في صفحة العرض الخارجية.' : 'تم إخفاء المنتج من صفحة العرض الخارجية.']);
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['ok' => true, 'message' => 'تم حذف المنتج من المخزن.']);
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
