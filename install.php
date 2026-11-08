<?php
/**
 * ASLY | أصلي TECH STORE
 * 1-Click XAMPP / MySQL Database Setup & Environment Diagnostic Wizard
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$statusMsg = '';
$statusOk = false;

try {
    $pdo = Database::getConnection();
    $usersCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $statusOk = true;
    $statusMsg = "تم الاتصال بقاعدة البيانات بنجاح وتجهيز جميع الجداول! (عدد الحسابات: {$usersCount} • عدد المنتجات الحالية: {$productsCount})";
} catch (Throwable $e) {
    $statusOk = false;
    $statusMsg = 'تعذر الاتصال بخادم MySQL في XAMPP: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>معالج التشغيل السريع على XAMPP | ASLY TECH STORE</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/landing.css')) ?>">
</head>
<body style="background:#043538;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
  <div style="max-width:660px;width:100%;background:#ffffff;border-radius:24px;overflow:hidden;border:2px solid #d49b27;box-shadow:0 24px 60px rgba(0,0,0,.35);">
    <div style="background:linear-gradient(135deg,#021d1f,#074f53);padding:28px;text-align:center;border-bottom:4px solid #d49b27;">
      <img src="<?= htmlspecialchars(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY" style="height:60px;margin:0 auto 8px;">
      <div style="color:#f2c75c;font-weight:800;">معالج التشغيل التلقائي لبيئة XAMPP و phpMyAdmin</div>
    </div>
    <div style="padding:28px;">
      <div style="padding:16px 18px;border-radius:14px;margin-bottom:20px;font-weight:800;background:<?= $statusOk ? '#ecfdf5' : '#fef2f2' ?>;color:<?= $statusOk ? '#065f46' : '#991b1b' ?>;border:1px solid <?= $statusOk ? '#a7f3d0' : '#fecaca' ?>;">
        <?= $statusOk ? '✅ ' : '⚠️ ' ?><?= htmlspecialchars($statusMsg) ?>
      </div>

      <div style="background:#f4f9f9;padding:16px 18px;border-radius:14px;border:1px solid #dce8e8;font-size:.9rem;margin-bottom:22px;">
        <strong style="color:#043538;display:block;margin-bottom:8px;">بيانات الدخول الافتراضية للنظام:</strong>
        <div>👑 <strong>المدير العام (Admin):</strong> اسم المستخدم <code>admin</code> | كلمة المرور <code>admin123</code></div>
        <div style="margin-top:4px;">👑 <strong>المدير الشريك (Admin):</strong> اسم المستخدم <code>ramadan</code> | كلمة المرور <code>admin123</code></div>
        <div style="margin-top:4px;">💼 <strong>موظف المبيعات (Staff):</strong> اسم المستخدم <code>staff1</code> | كلمة المرور <code>staff123</code></div>
      </div>

      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= htmlspecialchars(appUrl('index.php')) ?>" class="btn btn-teal" style="flex:1;">🌐 فتح الواجهة الخارجية (Landing Page)</a>
        <a href="<?= htmlspecialchars(appUrl('admin/login.php')) ?>" class="btn btn-gold" style="flex:1;">🔐 فتح لوحة الإدارة والموظفين</a>
      </div>
    </div>
  </div>
</body>
</html>
