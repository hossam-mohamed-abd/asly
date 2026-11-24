<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$en = isEn();
$otherLang = $en ? 'ar' : 'en';
$statusMsg = '';
$statusOk = false;

try {
    $pdo = Database::getConnection();
    $usersCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $statusOk = true;
    $statusMsg = $en
        ? "Database connected and tables initialized! ({$usersCount} accounts • {$productsCount} inventory items)"
        : "تم الاتصال بقاعدة البيانات بنجاح وتجهيز جميع الجداول! (عدد الحسابات: {$usersCount} • عدد أصناف المخزن: {$productsCount})";
} catch (Throwable $e) {
    $statusOk = false;
    $statusMsg = ($en ? 'MySQL Connection Error on XAMPP: ' : 'تعذر الاتصال بخادم MySQL في XAMPP: ') . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XAMPP Setup Wizard | ASLY TECH STORE</title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body style="background:linear-gradient(135deg,#effafa 0%,#faf9f6 52%,#fffbf0 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
  <div style="max-width:660px;width:100%;background:#ffffff;border-radius:36px 14px 36px 14px;overflow:hidden;border:1.5px solid #d5f2f4;box-shadow:0 24px 60px rgba(20,180,184,0.1);">
    <div style="background:linear-gradient(135deg,#f3fcfc,#ffffff,#fffdf5);padding:26px 28px;text-align:center;border-bottom:2px solid #e4b453;">
      <div style="display:flex;justify-content:flex-end;margin-bottom:4px;">
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill">
          <i class="fa-solid fa-globe"></i>
          <span><?= e(t('lang_switch_label')) ?></span>
        </a>
      </div>
      <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY" style="height:64px;margin:0 auto 8px;object-fit:contain;">
      <div style="color:#0d9499;font-weight:800;"><?= $en ? '1-Click XAMPP & phpMyAdmin Setup Wizard' : 'معالج التشغيل التلقائي لبيئة XAMPP و phpMyAdmin' ?></div>
    </div>
    <div style="padding:28px;">
      <div style="padding:16px 18px;border-radius:16px;margin-bottom:20px;font-weight:800;background:<?= $statusOk ? '#ecfdf5' : '#fef2f2' ?>;color:<?= $statusOk ? '#059669' : '#b91c1c' ?>;border:1px solid <?= $statusOk ? '#a7f3d0' : '#fecaca' ?>;">
        <i class="fa-solid <?= $statusOk ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>" style="margin-inline-end:8px;"></i><?= e($statusMsg) ?>
      </div>

      <div style="background:#faf9f6;padding:16px 18px;border-radius:16px;border:1px solid #eaeef2;font-size:0.9rem;margin-bottom:22px;">
        <strong style="color:#1e293b;display:block;margin-bottom:8px;"><?= $en ? 'Default Accounts:' : 'بيانات الدخول الافتراضية للنظام:' ?></strong>
        <div><i class="fa-solid fa-user-shield" style="color:#c49029;margin-inline-end:6px;"></i><strong>Admin:</strong> <code>admin</code> / <code>admin123</code></div>
        <div style="margin-top:4px;"><i class="fa-solid fa-user-shield" style="color:#c49029;margin-inline-end:6px;"></i><strong>Admin:</strong> <code>ramadan</code> / <code>admin123</code></div>
        <div style="margin-top:4px;"><i class="fa-solid fa-id-badge" style="color:#0d9499;margin-inline-end:6px;"></i><strong>Staff:</strong> <code>staff1</code> / <code>staff123</code></div>
      </div>

      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-teal" style="flex:1;">
          <i class="fa-solid fa-compass"></i>
          <span><?= $en ? 'Open Public Website' : 'فتح الموقع الرئيسي' ?></span>
        </a>
        <a href="<?= e(appUrl('admin/login.php')) ?>" class="btn btn-gold" style="flex:1;">
          <i class="fa-solid fa-lock"></i>
          <span><?= $en ? 'Open Dashboard' : 'فتح لوحة الإدارة' ?></span>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
