<?php
/**
 * ASLY | أصلي TECH STORE
 * Bright Daylight Admin & Staff Login Portal (Bilingual EN + AR)
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (currentUser() !== null) {
    header('Location: ' . appUrl('admin/index.php'));
    exit;
}

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$en = isEn();
$otherLang = $en ? 'ar' : 'en';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = $en ? 'Session expired, please try again.' : 'انتهت صلاحية الجلسة، يرجى إعادة المحاولة.';
    } else {
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = ? LIMIT 1");
        $stmt->execute([$username]);
        $account = $stmt->fetch();

        if (!$account || !password_verify($password, (string)$account['password_hash'])) {
            $error = $en ? 'Invalid username or password.' : 'اسم المستخدم أو كلمة المرور غير صحيحة.';
        } elseif ((int)$account['is_active'] !== 1) {
            $error = $en ? 'This account is currently disabled.' : 'هذا الحساب معطل حالياً، يرجى مراجعة مدير المركز.';
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

$storeName = $en ? ($settings['store_name_en'] ?: 'ASLY TECH STORE') : ($settings['store_name_ar'] ?: 'أصلي تك ستور');
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(t('login_btn')) ?> | <?= e($storeName) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/dashboard.css')) ?>">
  <style>
    .login-shell {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: linear-gradient(135deg, #effafa 0%, #faf9f6 52%, #fffbf0 100%);
    }
    .login-card {
      width: min(470px, 100%);
      background: #ffffff;
      border-radius: 28px;
      overflow: hidden;
      border: 1.5px solid #d5ecef;
      box-shadow: 0 24px 60px rgba(20, 180, 184, 0.11);
    }
    .login-card-top {
      background: linear-gradient(135deg, #f3fcfc 0%, #ffffff 55%, #fffdf5 100%);
      padding: 28px 28px 22px;
      text-align: center;
      border-bottom: 2px solid #e4b453;
    }
    .login-card-top img {
      height: 68px;
      margin: 0 auto 10px;
      object-fit: contain;
    }
    .login-card-body {
      padding: 28px;
    }
    .quick-demo-box {
      margin-top: 20px;
      padding: 14px 16px;
      border-radius: 16px;
      background: #f6fbfb;
      border: 1px dashed #bce3e6;
      font-size: 0.82rem;
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <div class="login-card">
      <div class="login-card-top">
        <div style="display:flex;justify-content:flex-end;margin-bottom:4px;">
          <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="btn btn-ghost btn-sm">🌐 <?= e(t('lang_switch_label')) ?></a>
        </div>
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store">
        <div style="font-size:0.9rem;color:#475569;font-weight:700;"><?= e(t('login_title')) ?></div>
      </div>

      <div class="login-card-body">
        <?php if ($error !== ''): ?>
          <div style="margin-bottom:16px;padding:12px 14px;border-radius:12px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-weight:700;font-size:0.88rem;">
            ⚠️ <?= e($error) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= e(appUrl('admin/login.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

          <div class="form-group" style="margin-bottom:16px;">
            <label for="username"><?= e(t('login_user_lbl')) ?></label>
            <input type="text" id="username" name="username" class="form-control" placeholder="<?= e(t('login_user_ph')) ?>" required autofocus style="direction:ltr;">
          </div>

          <div class="form-group" style="margin-bottom:22px;">
            <label for="password"><?= e(t('login_pass_lbl')) ?></label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="direction:ltr;">
          </div>

          <button type="submit" class="btn btn-teal" style="width:100%;padding:13px;font-size:1rem;">
            <?= e(t('login_btn')) ?>
          </button>
        </form>

        <div class="quick-demo-box">
          <div style="font-weight:800;color:#1e293b;margin-bottom:8px;"><?= e(t('login_demo_hint')) ?></div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-gold btn-sm" onclick="fillLogin('admin','admin123')"><?= e(t('login_demo_admin')) ?></button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="fillLogin('staff1','staff123')"><?= e(t('login_demo_staff')) ?></button>
          </div>
        </div>

        <div style="text-align:center;margin-top:18px;">
          <a href="<?= e(appUrl('index.php')) ?>" style="font-size:0.86rem;color:#0d9499;font-weight:800;text-decoration:none;">
            <?= $en ? '← ' : '→ ' ?><?= e(t('login_back_store')) ?>
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
