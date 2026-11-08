<?php
/**
 * ASLY | أصلي TECH STORE
 * Products Management API (Bilingual Inventory CRUD, Dual Images, 3-Tier Pricing, QR/Barcode)
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
        $where[] = '(name LIKE ? OR COALESCE(name_en, \'\') LIKE ? OR barcode LIKE ? OR brand LIKE ? OR specs LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like, $like);
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
    $langKey = isEn() ? 'en' : 'ar';

    foreach ($rows as &$row) {
        $row['id'] = (int)$row['id'];
        $row['sell_price'] = (float)$row['sell_price'];
        $row['min_sell_price'] = (float)$row['min_sell_price'];
        $row['quantity'] = (int)$row['quantity'];
        $row['low_stock_threshold'] = (int)$row['low_stock_threshold'];
        $row['show_on_landing'] = (int)$row['show_on_landing'];
        $row['is_featured'] = (int)$row['is_featured'];
        $row['category_label'] = $cats[$row['category']][$langKey] ?? $row['category'];
        $row['condition_label'] = $conds[$row['condition_status']][$langKey] ?? $row['condition_status'];
        $row['display_name'] = pickLangField($row, 'name');
        $row['display_specs'] = pickLangField($row, 'specs');

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
    requireAdminApi();
    requireCsrfJson();

    $isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
    $input = $isMultipart ? $_POST : readJsonInput();
    $action = $input['action'] ?? 'save';

    if ($action === 'save') {
        $id = (int)($input['id'] ?? 0);
        $name = trim((string)($input['name'] ?? ''));
        $nameEn = trim((string)($input['name_en'] ?? ''));
        $category = trim((string)($input['category'] ?? 'laptops'));
        $conditionStatus = trim((string)($input['condition_status'] ?? 'new'));
        $brand = trim((string)($input['brand'] ?? ''));
        $specs = trim((string)($input['specs'] ?? ''));
        $specsEn = trim((string)($input['specs_en'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $descriptionEn = trim((string)($input['description_en'] ?? ''));
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

        if ($name === '' && $nameEn !== '') {
            $name = $nameEn;
        }
        if ($name === '') {
            jsonResponse(['ok' => false, 'error' => isEn() ? 'Please enter the product name.' : 'يرجى إدخال اسم المنتج.'], 400);
        }
        if ($sellPrice <= 0) {
            jsonResponse(['ok' => false, 'error' => isEn() ? 'Please enter a valid selling price.' : 'يرجى إدخال سعر البيع بشكل صحيح.'], 400);
        }
        if ($minSellPrice <= 0) {
            $minSellPrice = $sellPrice;
        }
        if ($minSellPrice > $sellPrice) {
            jsonResponse(['ok' => false, 'error' => isEn() ? 'Minimum floor price cannot exceed list selling price.' : 'أقل سعر للبيع لا يمكن أن يكون أكبر من سعر البيع الأساسي!'], 400);
        }

        if ($barcode === '') {
            $barcode = generateUniqueProductCode($pdo, $category);
            $codeType = 'qr';
        }

        $dupStmt = $pdo->prepare("SELECT id, name FROM products WHERE barcode = ? AND id != ? LIMIT 1");
        $dupStmt->execute([$barcode, $id]);
        $dup = $dupStmt->fetch();
        if ($dup) {
            jsonResponse([
                'ok' => false,
                'error' => isEn()
                    ? "Code ({$barcode}) is already registered to: {$dup['name']}"
                    : "هذا الكود ({$barcode}) مسجل بالفعل للمنتج: {$dup['name']}",
                'existing_id' => (int)$dup['id'],
            ], 409);
        }

        $uploadedPath = null;
        $uploadedSecondaryPath = null;
        try {
            if (!empty($_FILES['image'])) {
                $uploadedPath = handleProductImageUpload($_FILES['image']);
            }
            if (!empty($_FILES['secondary_image'])) {
                $uploadedSecondaryPath = handleProductImageUpload($_FILES['secondary_image']);
            }
        } catch (Throwable $e) {
            jsonResponse(['ok' => false, 'error' => $e->getMessage()], 400);
        }

        $now = date('Y-m-d H:i:s');

        if ($id > 0) {
            $existingStmt = $pdo->prepare("SELECT image_path, secondary_image_path FROM products WHERE id = ?");
            $existingStmt->execute([$id]);
            $existing = $existingStmt->fetch();
            if (!$existing) {
                jsonResponse(['ok' => false, 'error' => isEn() ? 'Product not found.' : 'المنتج غير موجود.'], 404);
            }
            $finalImage = $uploadedPath ?? ($existing['image_path'] ?: null);
            $finalSecondary = $uploadedSecondaryPath ?? ($existing['secondary_image_path'] ?: null);

            $upd = $pdo->prepare("
                UPDATE products SET
                    barcode = ?, code_type = ?, name = ?, name_en = ?, category = ?, condition_status = ?,
                    brand = ?, specs = ?, specs_en = ?, description = ?, description_en = ?,
                    image_path = ?, secondary_image_path = ?,
                    purchase_price = ?, sell_price = ?, min_sell_price = ?,
                    quantity = ?, low_stock_threshold = ?, warranty_default = ?,
                    show_on_landing = ?, is_featured = ?, updated_at = ?
                WHERE id = ?
            ");
            $upd->execute([
                $barcode, $codeType, $name, $nameEn ?: null, $category, $conditionStatus,
                $brand, $specs, $specsEn ?: null, $description, $descriptionEn ?: null,
                $finalImage, $finalSecondary,
                $purchasePrice, $sellPrice, $minSellPrice,
                $quantity, $lowStock, $warranty,
                $showOnLanding, $isFeatured, $now,
                $id,
            ]);

            jsonResponse([
                'ok' => true,
                'message' => isEn() ? 'Product updated successfully.' : 'تم تحديث بيانات المنتج بنجاح.',
                'id' => $id,
                'barcode' => $barcode,
            ]);
        } else {
            $defaultSamples = [
                'laptops'     => ['uploads/products/laptop-studio.jpg', 'uploads/products/laptop-lifestyle.jpg'],
                'mobiles'     => ['uploads/products/mobile-studio.jpg', 'uploads/products/mobile-lifestyle.jpg'],
                'accessories' => ['uploads/products/headphones-studio.jpg', 'uploads/products/headphones-lifestyle.jpg'],
                'wearables'   => ['uploads/products/watch-studio.jpg', 'uploads/products/watch-lifestyle.jpg'],
            ];
            $pair = $defaultSamples[$category] ?? ['uploads/products/laptop-studio.jpg', 'uploads/products/laptop-lifestyle.jpg'];
            $finalImage = $uploadedPath ?? $pair[0];
            $finalSecondary = $uploadedSecondaryPath ?? ($uploadedPath ? null : $pair[1]);

            $ins = $pdo->prepare("
                INSERT INTO products (
                    barcode, code_type, name, name_en, category, condition_status,
                    brand, specs, specs_en, description, description_en,
                    image_path, secondary_image_path,
                    purchase_price, sell_price, min_sell_price,
                    quantity, low_stock_threshold, warranty_default,
                    show_on_landing, is_featured, created_by, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $barcode, $codeType, $name, $nameEn ?: null, $category, $conditionStatus,
                $brand, $specs, $specsEn ?: null, $description, $descriptionEn ?: null,
                $finalImage, $finalSecondary,
                $purchasePrice, $sellPrice, $minSellPrice,
                $quantity, $lowStock, $warranty,
                $showOnLanding, $isFeatured, $user['id'], $now, $now,
            ]);
            $newId = (int)$pdo->lastInsertId();

            jsonResponse([
                'ok' => true,
                'message' => isEn() ? 'Product registered and code generated!' : 'تم تسجيل المنتج وتوليد الكود بنجاح.',
                'id' => $newId,
                'barcode' => $barcode,
            ]);
        }
    }

    if ($action === 'toggle_landing') {
        $id = (int)($input['id'] ?? 0);
        $show = (int)($input['show_on_landing'] ?? 0) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE products SET show_on_landing = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$show, date('Y-m-d H:i:s'), $id]);
        jsonResponse([
            'ok' => true,
            'message' => $show
                ? (isEn() ? 'Product enabled on public storefront.' : 'تم إظهار المنتج في صفحة العرض الخارجية.')
                : (isEn() ? 'Product hidden from public storefront.' : 'تم إخفاء المنتج من صفحة العرض الخارجية.'),
        ]);
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['ok' => true, 'message' => isEn() ? 'Product removed from inventory.' : 'تم حذف المنتج من المخزن.']);
    }
}

jsonResponse(['ok' => false, 'error' => 'Invalid request.'], 400);
