<?php
/**
 * ASLY | أصلي TECH STORE
 * Core Application Configuration
 */

declare(strict_types=1);

date_default_timezone_set('Africa/Cairo');

if (session_status() === PHP_SESSION_NONE) {
    $isHttpsReq = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('ASLY_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttpsReq,
        'httponly' => true,
        'samesite' => $isHttpsReq ? 'None' : 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// ============================================================
// XAMPP / MySQL Default Connection Settings (phpMyAdmin)
// ============================================================
define('DB_HOST', getenv('ASLY_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('ASLY_DB_PORT') ?: '3306');
define('DB_NAME', getenv('ASLY_DB_NAME') ?: 'asly_store');
define('DB_USER', getenv('ASLY_DB_USER') ?: 'root');
define('DB_PASS', getenv('ASLY_DB_PASS') !== false ? getenv('ASLY_DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

define('APP_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', APP_ROOT . '/uploads/products');

/**
 * Compute base URL dynamically so the project works seamlessly whether placed in:
 * - http://localhost/asly/  (standard XAMPP htdocs/asly)
 * - http://192.168.1.x/asly/ (Wi-Fi LAN mobile camera testing on XAMPP)
 * - https://example.com/    (root domain hosting)
 */
function appBasePath(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    // Strip /admin/... or /api/... to always point to project root
    $base = preg_replace('#/(admin|api)(/.*)?$#', '', dirname($scriptName));
    if ($base === '/' || $base === '\\' || $base === '.') {
        return '';
    }
    return rtrim($base, '/');
}

function appUrl(string $path = ''): string
{
    $base = appBasePath();
    $clean = ltrim($path, '/');
    return ($base !== '' ? $base : '') . '/' . $clean;
}

function publicAbsoluteUrl(string $path = '', ?string $configuredBase = null): string
{
    if ($configuredBase !== null && trim($configuredBase) !== '') {
        return rtrim(trim($configuredBase), '/') . '/' . ltrim($path, '/');
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . appUrl($path);
}
