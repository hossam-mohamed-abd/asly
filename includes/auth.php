<?php
/**
 * ASLY | أصلي TECH STORE
 * Session Authentication, Role-Based Access & CSRF Protection
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function verifyCsrf(?string $token = null): bool
{
    $provided = $token ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));
    if ($provided === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], (string)$provided);
}

function requireCsrfJson(): void
{
    $input = readJsonInput();
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? '');
    if (!verifyCsrf((string)$token)) {
        jsonResponse(['ok' => false, 'error' => 'انتهت صلاحية الجلسة الأمنية، يرجى تحديث الصفحة والمحاولة مجدداً.'], 403);
    }
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'        => (int)$_SESSION['user_id'],
        'full_name' => (string)($_SESSION['full_name'] ?? ''),
        'username'  => (string)($_SESSION['username'] ?? ''),
        'role'      => (string)($_SESSION['role'] ?? 'staff'),
    ];
}

function isAdmin(): bool
{
    $u = currentUser();
    return $u !== null && $u['role'] === 'admin';
}

function requireLoginPage(): array
{
    $u = currentUser();
    if ($u === null) {
        header('Location: ' . appUrl('admin/login.php'));
        exit;
    }
    return $u;
}

function requireLoginApi(): array
{
    $u = currentUser();
    if ($u === null) {
        jsonResponse(['ok' => false, 'error' => 'يرجى تسجيل الدخول أولاً.'], 401);
    }
    return $u;
}

function requireAdminApi(): array
{
    $u = requireLoginApi();
    if ($u['role'] !== 'admin') {
        jsonResponse(['ok' => false, 'error' => 'هذه الصلاحية مخصصة لمدير النظام (Admin) فقط.'], 403);
    }
    return $u;
}
