<?php
/**
 * ASLY | أصلي TECH STORE
 * Admin-Only Staff & Admin Users Management API
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$admin = requireAdminApi();
$pdo = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query("
        SELECT id, full_name, username, role, phone, is_active, created_at
        FROM users
        ORDER BY role ASC, id ASC
    ")->fetchAll();
    jsonResponse(['ok' => true, 'users' => $rows]);
}

if ($method === 'POST') {
    requireCsrfJson();
    $input = readJsonInput();
    $action = $input['action'] ?? 'save';

    if ($action === 'save') {
        $id = (int)($input['id'] ?? 0);
        $fullName = trim((string)($input['full_name'] ?? ''));
        $username = strtolower(trim((string)($input['username'] ?? '')));
        $password = (string)($input['password'] ?? '');
        $role = in_array(($input['role'] ?? 'staff'), ['admin', 'staff'], true) ? $input['role'] : 'staff';
        $phone = trim((string)($input['phone'] ?? ''));
        $isActive = isset($input['is_active']) ? ((int)$input['is_active'] ? 1 : 0) : 1;

        if ($fullName === '' || $username === '') {
            jsonResponse(['ok' => false, 'error' => 'يرجى إدخال الاسم الكامل واسم المستخدم.'], 400);
        }

        $dup = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
        $dup->execute([$username, $id]);
        if ($dup->fetch()) {
            jsonResponse(['ok' => false, 'error' => 'اسم المستخدم مستخدم بالفعل لحساب آخر.'], 409);
        }

        if ($id > 0) {
            if ($id === $admin['id'] && ($role !== 'admin' || $isActive === 0)) {
                jsonResponse(['ok' => false, 'error' => 'لا يمكنك إلغاء صلاحية أو تعطيل حسابك الحالي!'], 400);
            }
            if ($password !== '') {
                if (strlen($password) < 6) {
                    jsonResponse(['ok' => false, 'error' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.'], 400);
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $upd = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password_hash = ?, role = ?, phone = ?, is_active = ? WHERE id = ?");
                $upd->execute([$fullName, $username, $hash, $role, $phone, $isActive, $id]);
            } else {
                $upd = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, phone = ?, is_active = ? WHERE id = ?");
                $upd->execute([$fullName, $username, $role, $phone, $isActive, $id]);
            }
            jsonResponse(['ok' => true, 'message' => 'تم تحديث بيانات الحساب بنجاح.']);
        } else {
            if (strlen($password) < 6) {
                jsonResponse(['ok' => false, 'error' => 'يرجى إدخال كلمة مرور لا تقل عن 6 أحرف للحساب الجديد.'], 400);
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $pdo->prepare("
                INSERT INTO users (full_name, username, password_hash, role, phone, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$fullName, $username, $hash, $role, $phone, $isActive, date('Y-m-d H:i:s')]);
            jsonResponse(['ok' => true, 'message' => 'تم إنشاء حساب الموظف / المدير بنجاح.']);
        }
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if ($id === $admin['id']) {
            jsonResponse(['ok' => false, 'error' => 'لا يمكنك حذف حسابك الشخصي الحالي!'], 400);
        }
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        jsonResponse(['ok' => true, 'message' => 'تم حذف الحساب بنجاح.']);
    }
}

jsonResponse(['ok' => false, 'error' => 'طلب غير صالح.'], 400);
