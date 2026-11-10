<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$root = __DIR__;
$files = [
    'index.php',
    'admin/index.php',
    'includes/helpers.php',
    'assets/css/landing.css',
    'assets/css/dashboard.css',
    'assets/js/landing.js',
    'assets/js/dashboard.js',
    'assets/js/turbo-scanner.js',
];

$rev = 0;
foreach ($files as $rel) {
    $full = $root . '/' . $rel;
    if (is_file($full)) {
        $rev = max($rev, (int)filemtime($full));
        $rev = max($rev, (int)filesize($full));
    }
}

echo (string)$rev;
