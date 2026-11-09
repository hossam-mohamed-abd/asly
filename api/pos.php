<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'invoice') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            jsonResponse(['ok' => false, 'error' => 'الفاتورة غير موجودة.'], 404);
        }

        $itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
        $itemStmt->execute([$id]);
        $items = $itemStmt->fetchAll();

        if ($user['role'] !== 'admin') {
            unset($invoice['total_cost'], $invoice['total_profit']);
            foreach ($items as &$it) {
                unset($it['unit_purchase_price'], $it['item_profit']);
            }
            unset($it);
        }

        $settings = getAllSettings($pdo);
        $invoice['public_url'] = publicAbsoluteUrl(
            'invoice_view.php?token=' . urlencode((string)$invoice['public_token']),
            $settings['public_base_url'] ?? null
        );

        jsonResponse(['ok' => true, 'invoice' => $invoice, 'items' => $items]);
    }

    
    requireAdminApi();
    $dateFrom = trim((string)($_GET['date_from'] ?? date('Y-m-d')));
    $dateTo = trim((string)($_GET['date_to'] ?? $dateFrom));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $dateFrom = date('Y-m-d');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $dateTo = $dateFrom;
    }

    $invStmt = $pdo->prepare("
        SELECT * FROM invoices
        WHERE sale_date >= ? AND sale_date <= ?
        ORDER BY id DESC
    ");
    $invStmt->execute([$dateFrom, $dateTo]);
    $invoices = $invStmt->fetchAll();

    
    $itemsByInvoice = [];
    if ($invoices) {
        $ids = array_map(static fn($r) => (int)$r['id'], $invoices);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $iStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id IN ({$placeholders}) ORDER BY id ASC");
        $iStmt->execute($ids);
        foreach ($iStmt->fetchAll() as $it) {
            $invId = (int)$it['invoice_id'];
            if ($user['role'] !== 'admin') {
                unset($it['unit_purchase_price'], $it['item_profit']);
            }
            $itemsByInvoice[$invId][] = $it;
        }
    }

    $settings = getAllSettings($pdo);
    $salesRevenue = 0.0;
    $salesProfit = 0.0;
    $itemsSoldCount = 0;

    foreach ($invoices as &$inv) {
        $invId = (int)$inv['id'];
        $inv['total_amount'] = (float)$inv['total_amount'];
        $salesRevenue += $inv['total_amount'];
        $invItems = $itemsByInvoice[$invId] ?? [];
        foreach ($invItems as $ii) {
            $itemsSoldCount += (int)$ii['quantity'];
        }
        $inv['items'] = $invItems;
        $inv['public_url'] = publicAbsoluteUrl(
            'invoice_view.php?token=' . urlencode((string)$inv['public_token']),
            $settings['public_base_url'] ?? null
        );

        if ($user['role'] === 'admin') {
            $inv['total_cost'] = (float)$inv['total_cost'];
            $inv['total_profit'] = (float)$inv['total_profit'];
            $salesProfit += $inv['total_profit'];
        } else {
            unset($inv['total_cost'], $inv['total_profit']);
        }
    }
    unset($inv);

    $maintRevenues = [];
    $maintRevenueTotal = 0.0;
    $maintProfitTotal = 0.0;

    if ($user['role'] === 'admin') {
        $mRevStmt = $pdo->prepare("
            SELECT * FROM maintenance_revenues
            WHERE revenue_date >= ? AND revenue_date <= ?
            ORDER BY id DESC
        ");
        $mRevStmt->execute([$dateFrom, $dateTo]);
        $maintRevenues = $mRevStmt->fetchAll();
        foreach ($maintRevenues as $mr) {
            $maintRevenueTotal += (float)$mr['amount_paid'];
            $maintProfitTotal += (float)$mr['net_profit'];
        }
    }

    $summary = [
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'invoices_count' => count($invoices),
        'items_sold_count' => $itemsSoldCount,
        'sales_revenue' => round($salesRevenue, 2),
    ];

    if ($user['role'] === 'admin') {
        $summary['sales_profit'] = round($salesProfit, 2);
        $summary['maintenance_revenue'] = round($maintRevenueTotal, 2);
        $summary['maintenance_profit'] = round($maintProfitTotal, 2);
        $summary['maintenance_delivered_count'] = count($maintRevenues);
        $summary['combined_revenue'] = round($salesRevenue + $maintRevenueTotal, 2);
        $summary['combined_net_profit'] = round($salesProfit + $maintProfitTotal, 2);
    }

    jsonResponse([
        'ok' => true,
        'summary' => $summary,
        'invoices' => $invoices,
        'maintenance_revenues' => $maintRevenues,
    ]);
}

if ($method === 'POST') {
    requireCsrfJson();
    $input = readJsonInput();
    $action = $input['action'] ?? 'checkout';

    if ($action === 'checkout') {
        $customerName = trim((string)($input['customer_name'] ?? '')) ?: 'عميل نقدي';
        $customerPhone = trim((string)($input['customer_phone'] ?? ''));
        $paymentMethod = trim((string)($input['payment_method'] ?? 'cash'));
        $notes = trim((string)($input['notes'] ?? ''));
        $cartItems = $input['items'] ?? [];

        if (!is_array($cartItems) || count($cartItems) === 0) {
            jsonResponse(['ok' => false, 'error' => 'السلة فارغة! يرجى إضافة منتج واحد على الأقل لإصدار الفاتورة.'], 400);
        }

        try {
            $pdo->beginTransaction();

            $validatedItems = [];
            $totalAmount = 0.0;
            $totalCost = 0.0;
            $totalProfit = 0.0;

            $prodStmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
            $deductStmt = $pdo->prepare("UPDATE products SET quantity = quantity - ?, updated_at = ? WHERE id = ? AND quantity >= ?");

            foreach ($cartItems as $idx => $item) {
                $prodId = (int)($item['product_id'] ?? 0);
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $soldUnitPrice = round((float)($item['unit_sold_price'] ?? 0), 2);
                $serialImei = trim((string)($item['serial_imei'] ?? ''));
                $warrantyText = trim((string)($item['warranty_text'] ?? ''));

                $prodStmt->execute([$prodId]);
                $product = $prodStmt->fetch();
                if (!$product) {
                    throw new RuntimeException('أحد المنتجات المختارة غير موجود في قاعدة البيانات.');
                }

                $availableQty = (int)$product['quantity'];
                if ($qty > $availableQty) {
                    throw new RuntimeException(sprintf(
                        'الكمية المتاحة من "%s" هي (%d) فقط ولا تكفي لبيع (%d).',
                        $product['name'],
                        $availableQty,
                        $qty
                    ));
                }

                $minSellPrice = round((float)$product['min_sell_price'], 2);
                
                if ($soldUnitPrice + 0.001 < $minSellPrice) {
                    throw new RuntimeException(sprintf(
                        'مرفوض! سعر البيع المدخل للمنتج "%s" (%s) أقل من أقل سعر بيع مسموح به (%s).',
                        $product['name'],
                        formatMoney($soldUnitPrice),
                        formatMoney($minSellPrice)
                    ));
                }

                $purchasePrice = round((float)$product['purchase_price'], 2);
                $listPrice = round((float)$product['sell_price'], 2);
                $subtotal = round($soldUnitPrice * $qty, 2);
                $itemCost = round($purchasePrice * $qty, 2);
                $itemProfit = round($subtotal - $itemCost, 2);

                $now = date('Y-m-d H:i:s');
                $deductStmt->execute([$qty, $now, $prodId, $qty]);
                if ($deductStmt->rowCount() === 0) {
                    throw new RuntimeException("تعذر خصم المخزون للمنتج: {$product['name']}");
                }

                $totalAmount += $subtotal;
                $totalCost += $itemCost;
                $totalProfit += $itemProfit;

                $validatedItems[] = [
                    'product_id' => $prodId,
                    'product_name' => (string)$product['name'],
                    'product_barcode' => (string)$product['barcode'],
                    'serial_imei' => $serialImei,
                    'warranty_text' => $warrantyText !== '' ? $warrantyText : (string)($product['warranty_default'] ?? ''),
                    'quantity' => $qty,
                    'unit_purchase_price' => $purchasePrice,
                    'unit_list_price' => $listPrice,
                    'unit_min_price' => $minSellPrice,
                    'unit_sold_price' => $soldUnitPrice,
                    'subtotal' => $subtotal,
                    'item_profit' => $itemProfit,
                ];
            }

            $invoiceNumber = generateInvoiceNumber($pdo);
            $publicToken = bin2hex(random_bytes(20));
            $saleDate = date('Y-m-d');
            $createdAt = date('Y-m-d H:i:s');

            $insInv = $pdo->prepare("
                INSERT INTO invoices (
                    invoice_number, public_token, customer_name, customer_phone,
                    total_amount, total_cost, total_profit,
                    payment_method, notes, sold_by, sold_by_name, sale_date, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insInv->execute([
                $invoiceNumber,
                $publicToken,
                $customerName,
                $customerPhone,
                round($totalAmount, 2),
                round($totalCost, 2),
                round($totalProfit, 2),
                $paymentMethod,
                $notes,
                $user['id'],
                $user['full_name'],
                $saleDate,
                $createdAt,
            ]);
            $invoiceId = (int)$pdo->lastInsertId();

            $insItem = $pdo->prepare("
                INSERT INTO invoice_items (
                    invoice_id, product_id, product_name, product_barcode, serial_imei, warranty_text,
                    quantity, unit_purchase_price, unit_list_price, unit_min_price, unit_sold_price,
                    subtotal, item_profit
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($validatedItems as $vi) {
                $insItem->execute([
                    $invoiceId,
                    $vi['product_id'],
                    $vi['product_name'],
                    $vi['product_barcode'],
                    $vi['serial_imei'],
                    $vi['warranty_text'],
                    $vi['quantity'],
                    $vi['unit_purchase_price'],
                    $vi['unit_list_price'],
                    $vi['unit_min_price'],
                    $vi['unit_sold_price'],
                    $vi['subtotal'],
                    $vi['item_profit'],
                ]);
            }

            $pdo->commit();

            $settings = getAllSettings($pdo);
            $publicUrl = publicAbsoluteUrl(
                'invoice_view.php?token=' . urlencode($publicToken),
                $settings['public_base_url'] ?? null
            );

            
            $responseItems = $validatedItems;
            if ($user['role'] !== 'admin') {
                foreach ($responseItems as &$ri) {
                    unset($ri['unit_purchase_price'], $ri['item_profit']);
                }
                unset($ri);
            }

            jsonResponse([
                'ok' => true,
                'message' => 'تم إتمام عملية البيع وخصم المخزون وإصدار الفاتورة بنجاح!',
                'invoice' => [
                    'id' => $invoiceId,
                    'invoice_number' => $invoiceNumber,
                    'public_token' => $publicToken,
                    'public_url' => $publicUrl,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'total_amount' => round($totalAmount, 2),
                    'payment_method' => $paymentMethod,
                    'notes' => $notes,
                    'sold_by_name' => $user['full_name'],
                    'sale_date' => $saleDate,
                    'created_at' => $createdAt,
                ],
                'items' => $responseItems,
            ]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['ok' => false, 'error' => $e->getMessage()], 400);
        }
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
