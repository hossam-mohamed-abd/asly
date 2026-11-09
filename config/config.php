<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', APP_ROOT . '/uploads/products');

function aslyEnvMap(): array
{
    static $loaded = null;
    if ($loaded !== null) {
        return $loaded;
    }
    $loaded = [];
    $envPath = APP_ROOT . '/.env';
    if (is_file($envPath) && is_readable($envPath)) {
        $lines = preg_split('/\r\n|\r|\n/', (string)file_get_contents($envPath)) ?: [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $trimmed, 2);
            $key = trim($k);
            $val = trim($v);
            if ($key === '') {
                continue;
            }
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $loaded[$key] = $val;
            if (getenv($key) === false) {
                $_ENV[$key] = $val;
            }
        }
    }
    return $loaded;
}

function aslyEnv(string $key, string $default = ''): string
{
    $legacyMap = [
        'DB_DRIVER' => 'ASLY_DB_DRIVER',
        'DB_HOST'   => 'ASLY_DB_HOST',
        'DB_PORT'   => 'ASLY_DB_PORT',
        'DB_NAME'   => 'ASLY_DB_NAME',
        'DB_USER'   => 'ASLY_DB_USER',
        'DB_PASS'   => 'ASLY_DB_PASS',
    ];
    if (isset($legacyMap[$key])) {
        $legacyVal = getenv($legacyMap[$key]);
        if ($legacyVal !== false && $legacyVal !== '') {
            return (string)$legacyVal;
        }
    }
    $osVal = getenv($key);
    if ($osVal !== false) {
        return (string)$osVal;
    }
    $map = aslyEnvMap();
    return array_key_exists($key, $map) ? $map[$key] : $default;
}

aslyEnvMap();

$appDebug = in_array(strtolower(aslyEnv('APP_DEBUG', 'false')), ['1', 'true', 'yes', 'on'], true);
ini_set('display_errors', $appDebug ? '1' : '0');
error_reporting($appDebug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_STRICT));

$tz = aslyEnv('APP_TIMEZONE', 'Africa/Cairo');
date_default_timezone_set($tz !== '' ? $tz : 'Africa/Cairo');

define('APP_URL_ENV', trim(aslyEnv('APP_URL', '')));
define('DB_DRIVER', strtolower(aslyEnv('DB_DRIVER', 'mysql')));
define('DB_HOST', aslyEnv('DB_HOST', '127.0.0.1'));
define('DB_PORT', aslyEnv('DB_PORT', '3306'));
define('DB_NAME', aslyEnv('DB_NAME', 'asly_store'));
define('DB_USER', aslyEnv('DB_USER', 'root'));
define('DB_PASS', aslyEnv('DB_PASS', ''));
define('DB_CHARSET', aslyEnv('DB_CHARSET', 'utf8mb4'));

define('MAIL_ENABLED', aslyEnv('MAIL_ENABLED', '0'));
define('MAIL_HOST', aslyEnv('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', aslyEnv('MAIL_PORT', '587'));
define('MAIL_ENCRYPTION', aslyEnv('MAIL_ENCRYPTION', 'tls'));
define('MAIL_USERNAME', aslyEnv('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', aslyEnv('MAIL_PASSWORD', ''));
define('MAIL_FROM_ADDRESS', aslyEnv('MAIL_FROM_ADDRESS', 'noreply@aslystore.com'));
define('MAIL_FROM_NAME', aslyEnv('MAIL_FROM_NAME', 'ASLY | أصلي TECH STORE'));
define('MAIL_EHLO_DOMAIN', aslyEnv('MAIL_EHLO_DOMAIN', 'aslystore.com'));

if (session_status() === PHP_SESSION_NONE) {
    $isHttpsReq = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $sessName = preg_replace('/[^a-zA-Z0-9_]/', '', aslyEnv('SESSION_NAME', 'ASLY_SESSID')) ?: 'ASLY_SESSID';
    session_name($sessName);
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

function appBasePath(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = preg_replace('#/(admin|api)(/.*)?$#', '', dirname($scriptName));
    if ($base === '/' || $base === '\\' || $base === '.') {
        return '';
    }
    return rtrim((string)$base, '/');
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
    if (APP_URL_ENV !== '') {
        return rtrim(APP_URL_ENV, '/') . '/' . ltrim($path, '/');
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . appUrl($path);
}
