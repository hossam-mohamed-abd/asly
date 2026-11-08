<?php
/**
 * ASLY | أصلي TECH STORE
 * Branded Admin & Staff Login Portal
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (currentUser() !== null) {
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'انتهت صلاحية الجلسة، يرجى إعادة المحاولة.';
    } else {
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = ? LIMIT 1");
        $stmt->execute([$username]);
        $account = $stmt->fetch();

        if (!$account || !password_verify($password, (string)$account['password_hash'])) {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
        } elseif ((int)$account['is_active'] !== 1) {
            $error = 'هذا الحساب معطل حالياً، يرجى مراجعة مدير المركز.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$account['id'];
            $_SESSION['full_name'] = (string)$account['full_name'];
            $_SESSION['username'] = (string)$account['username'];
            $_SESSION['role'] = (string)$account['role'];
            header('Location: ' . appUrl('admin/index.php'));
            exit;
        }
    }
}

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل الدخول | <?= e($storeNameAr) ?> - لوحة الإدارة</title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/dashboard.css')) ?>">
  <style>
    .login-shell {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background:
        radial-gradient(circle at 20% 20%, rgba(242, 199, 92, 0.16), transparent 40%),
        radial-gradient(circle at 80% 80%, rgba(11, 104, 109, 0.45), transparent 45%),
        linear-gradient(135deg, #021a1c 0%, #043538 55%, #074f53 100%);
    }
    .login-card {
      width: min(460px, 100%);
      background: #ffffff;
      border-radius: 24px;
      overflow: hidden;
      border: 2px solid rgba(212, 155, 39, 0.5);
      box-shadow: 0 28px 70px rgba(0, 0, 0, 0.38);
    }
    .login-card-top {
      background: linear-gradient(135deg, #032629, #06484c);
      padding: 30px 28px 24px;
      text-align: center;
      border-bottom: 4px solid #d49b27;
      color: #ffffff;
    }
    .login-card-top img {
      height: 62px;
      margin: 0 auto 10px;
    }
    .login-card-body {
      padding: 28px;
    }
    .quick-demo-box {
      margin-top: 20px;
      padding: 14px;
      border-radius: 12px;
      background: #f3f9f9;
      border: 1px dashed #b9d4d6;
      font-size: 0.8rem;
      color: #345255;
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <div class="login-card">
      <div class="login-card-top">
        <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي">
        <div style="font-size:0.9rem;color:#d6ecec;font-weight:700;">بوابة الإدارة ونقطة البيع والصيانة الذكية</div>
      </div>

      <div class="login-card-body">
        <?php if ($error !== ''): ?>
          <div style="margin-bottom:16px;padding:12px 14px;border-radius:11px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-weight:700;font-size:.88rem;">
            ⚠️ <?= e($error) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= e(appUrl('admin/login.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

          <div class="form-group" style="margin-bottom:16px;">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="مثال: admin أو staff1" required autofocus>
          </div>

          <div class="form-group" style="margin-bottom:22px;">
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>

          <button type="submit" class="btn btn-gold" style="width:100%;padding:13px;font-size:1rem;">
            تسجيل الدخول للوحة التحكم
          </button>
        </form>

        <div class="quick-demo-box">
          <div style="font-weight:800;color:#043538;margin-bottom:8px;">بيانات الدخول الافتراضية (قابلة للتغيير من الإعدادات):</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-teal btn-sm" onclick="fillLogin('admin','admin123')">دخول كمدير (admin)</button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="fillLogin('staff1','staff123')">دخول كموظف مبيعات (staff1)</button>
          </div>
        </div>

        <div style="text-align:center;margin-top:18px;">
          <a href="<?= e(appUrl('index.php')) ?>" style="font-size:.86rem;color:#074f53;font-weight:700;text-decoration:none;">
            ← العودة لصفحة العرض الخارجية للعملاء
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
    function fillLogin(u, p) {
      document.getElementById('username').value = u;
      document.getElementById('password').value = p;
    }
  </script>
</body>
</html>
