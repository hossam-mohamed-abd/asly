<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginPage();
$isAdmin = ($user['role'] === 'admin');
$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$stages = getMaintenanceStages();
$catsMeta = getCategoryMeta();
$en = isEn();
$otherLang = $en ? 'ar' : 'en';

$unreadMaintNotes = (int)$pdo->query("SELECT COALESCE(SUM(unread_customer_notes), 0) FROM maintenance_tickets")->fetchColumn();
$activeMaintCount = (int)$pdo->query("SELECT COUNT(*) FROM maintenance_tickets")->fetchColumn();
$lowStockCount = $isAdmin ? (int)$pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= low_stock_threshold")->fetchColumn() : 0;

$inventoryStats = ['total_skus' => 0, 'total_units' => 0, 'total_capital' => 0.0, 'expected_sales' => 0.0];
if ($isAdmin) {
    $invRow = $pdo->query("
        SELECT
            COUNT(*) AS total_skus,
            COALESCE(SUM(quantity), 0) AS total_units,
            COALESCE(SUM(purchase_price * quantity), 0) AS total_capital,
            COALESCE(SUM(sell_price * quantity), 0) AS expected_sales
        FROM products
    ")->fetch();
    if ($invRow) {
        $inventoryStats = [
            'total_skus' => (int)$invRow['total_skus'],
            'total_units' => (int)$invRow['total_units'],
            'total_capital' => round((float)$invRow['total_capital'], 2),
            'expected_sales' => round((float)$invRow['expected_sales'], 2),
        ];
    }
}

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$displayStore = $en ? $storeNameEn : $storeNameAr;

$jsConfig = [
    'baseUrl' => appBasePath(),
    'csrfToken' => csrfToken(),
    'lang' => currentLang(),
    'isEn' => $en,
    'user' => $user,
    'isAdmin' => $isAdmin,
    'inventoryStats' => $inventoryStats,
    'lowStockCount' => $lowStockCount,
    'stages' => $stages,
    'categories' => $catsMeta,
    'store' => [
        'nameAr' => $storeNameAr,
        'nameEn' => $storeNameEn,
        'address' => $en
            ? ($settings['store_address_en'] ?: 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum')
            : ($settings['store_address'] ?: 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا'),
        'primaryPhone' => $settings['primary_phone'] ?: '01015981677',
        'secondaryPhone' => $settings['secondary_phone'] ?: '01154639718',
        'primaryWhatsapp' => normalizeWhatsappNumber($settings['primary_whatsapp'] ?: ($settings['primary_phone'] ?: '01015981677')),
        'footerNote' => $en
            ? ($settings['invoice_footer_note_en'] ?: 'All devices include ASLY TECH STORE certified hardware inspection & warranty.')
            : ($settings['invoice_footer_note'] ?: 'جميع الأجهزة تخضع لضمان الفحص والتجربة المعتمد من مركز أصلي ASLY TECH STORE.'),
        'logoUrl' => appUrl('assets/brand/logo-transparent.png'),
        'logoCardUrl' => appUrl('assets/brand/logo-card.jpg'),
    ],
];
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <title><?= $en ? 'Smart Dashboard' : 'لوحة التحكم الذكية' ?> | <?= e($displayStore) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/dashboard.css')) ?>">
</head>
<body>

<div class="app-layout">

  
  <aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
      <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store">
      <span class="role-pill <?= $isAdmin ? 'role-admin' : 'role-staff' ?>">
        <i class="fa-solid <?= $isAdmin ? 'fa-user-shield' : 'fa-id-badge' ?>"></i>
        <?= $isAdmin ? ($en ? 'System Admin' : 'مدير النظام (Admin)') : ($en ? 'Sales Staff' : 'موظف مبيعات (Staff)') ?>
      </span>
    </div>

    <nav class="sidebar-nav">
      <?php if ($isAdmin): ?>
        <div class="nav-section-label"><?= $en ? 'Daily Operations & Sales' : 'التشغيل اليومي والمبيعات' ?></div>

        <button type="button" class="side-btn active" data-tab="overview">
          <span class="side-btn-left"><i class="fa-solid fa-chart-pie"></i> <?= $en ? 'Daily Sales & Profit' : 'المبيعات اليومية والأرباح' ?></span>
        </button>

        <button type="button" class="side-btn" data-tab="quick-scanner">
          <span class="side-btn-left"><i class="fa-solid fa-barcode"></i> <?= $en ? 'Quick Price Scanner' : 'كاشف السعر السريع' ?></span>
          <span style="font-size:0.7rem;color:var(--db-gold-600);font-weight:800;"><?= $en ? 'Client Safe' : 'أمام العميل' ?></span>
        </button>

        <button type="button" class="side-btn" data-tab="pos">
          <span class="side-btn-left"><i class="fa-solid fa-cash-register"></i> <?= $en ? 'POS & Official Invoices' : 'نقطة البيع وإصدار الفواتير الرسمية' ?></span>
          <span id="posCartBadge" class="badge-alert" style="display:none;background:var(--db-teal-700);color:#fff;">0</span>
        </button>

        <div class="nav-section-label"><?= $en ? 'Inventory & QR/Barcode' : 'المخزون والباركود / QR' ?></div>

        <button type="button" class="side-btn" data-tab="add-product">
          <span class="side-btn-left"><i class="fa-solid fa-circle-plus"></i> <?= $en ? 'Add Product (Scan / QR)' : 'إضافة منتج (اسكان / QR)' ?></span>
        </button>

        <button type="button" class="side-btn" data-tab="inventory">
          <span class="side-btn-left"><i class="fa-solid fa-boxes-stacked"></i> <?= $en ? 'Inventory Catalog' : 'المخزون والمنتجات' ?></span>
          <?php if ($lowStockCount > 0): ?>
            <span class="badge-alert" title="Low stock"><?= $lowStockCount ?></span>
          <?php endif; ?>
        </button>

        <div class="nav-section-label"><?= $en ? 'Interactive Repair Lab' : 'قسم الصيانة التفاعلي' ?></div>

        <button type="button" class="side-btn" data-tab="maintenance">
          <span class="side-btn-left"><i class="fa-solid fa-screwdriver-wrench"></i> <?= $en ? 'QR Maintenance, Stages & Email' : 'إدارة الصيانة التفاعلية بالـ QR والمراحل والإيميل' ?></span>
          <span id="maintNavBadge" class="badge-alert" style="display:<?= $unreadMaintNotes > 0 ? 'inline-block' : 'none' ?>;">
            <?= $unreadMaintNotes ?> <?= $en ? 'new' : 'جديد' ?>
          </span>
        </button>

        <div class="nav-section-label"><?= $en ? 'Administration & Brand' : 'الإدارة والهوية' ?></div>

        <button type="button" class="side-btn" data-tab="staff">
          <span class="side-btn-left"><i class="fa-solid fa-users-gear"></i> <?= $en ? 'Staff & Permissions' : 'الموظفون والصلاحيات' ?></span>
        </button>

        <button type="button" class="side-btn" data-tab="settings">
          <span class="side-btn-left"><i class="fa-solid fa-sliders"></i> <?= $en ? 'Brand & Social Links' : 'الهوية وروابط السوشيال' ?></span>
        </button>
      <?php else: ?>
        <div class="nav-section-label"><?= $en ? 'Staff Workspace' : 'مهام الموظف المعتمدة' ?></div>

        <button type="button" class="side-btn active" data-tab="quick-scanner">
          <span class="side-btn-left"><i class="fa-solid fa-barcode"></i> <?= $en ? 'Quick Price Scanner' : 'كاشف السعر السريع' ?></span>
        </button>

        <button type="button" class="side-btn" data-tab="pos">
          <span class="side-btn-left"><i class="fa-solid fa-cash-register"></i> <?= $en ? 'POS & Official Invoices' : 'نقطة البيع وإصدار الفواتير الرسمية' ?></span>
          <span id="posCartBadge" class="badge-alert" style="display:none;background:var(--db-teal-700);color:#fff;">0</span>
        </button>

        <button type="button" class="side-btn" data-tab="maintenance">
          <span class="side-btn-left"><i class="fa-solid fa-screwdriver-wrench"></i> <?= $en ? 'QR Maintenance, Stages & Email' : 'إدارة الصيانة التفاعلية بالـ QR والمراحل والإيميل' ?></span>
          <span id="maintNavBadge" class="badge-alert" style="display:<?= $unreadMaintNotes > 0 ? 'inline-block' : 'none' ?>;">
            <?= $unreadMaintNotes ?> <?= $en ? 'new' : 'جديد' ?>
          </span>
        </button>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div>
        <strong style="display:block;color:var(--db-text);"><?= e($user['full_name']) ?></strong>
        <span style="font-size:0.75rem;color:var(--db-muted);">@<?= e($user['username']) ?></span>
      </div>
      <a href="<?= e(appUrl('admin/logout.php')) ?>" class="btn btn-danger btn-sm">
        <i class="fa-solid fa-right-from-bracket"></i> <?= $en ? 'Sign Out' : 'خروج' ?>
      </a>
    </div>
  </aside>

  
  <div class="main-area">

    
    <header class="topbar-admin">
      <div class="topbar-title-wrap">
        <button type="button" class="mobile-menu-btn" id="btnMobileSidebar"><i class="fa-solid fa-bars"></i></button>
        <div>
          <h2 id="activePageTitle" style="margin:0;font-size:1.2rem;color:var(--db-text);">
            <?= $isAdmin ? ($en ? 'Daily Sales & Net Profit Overview' : 'لوحة المبيعات اليومية والأرباح') : ($en ? 'Quick Price Scanner' : 'كاشف السعر السريع') ?>
          </h2>
          <span style="font-size:0.78rem;color:var(--db-muted);"><?= e($displayStore) ?> • <?= date('Y-m-d') ?></span>
        </div>
      </div>

      <div class="quick-bar-btns">
        <span class="scanner-ready-pill" id="globalScannerPill" title="<?= $en ? 'Scan any barcode or QR anywhere on the page without clicking an input' : 'امسح أي باركود أو QR بمسدس الباركود في أي مكان بالصفحة مباشرة دون الضغط داخل أي حقل' ?>">
          <span class="scanner-live-dot"></span>
          <i class="fa-solid fa-barcode"></i>
          <span><?= $en ? 'Anywhere Scanner Active' : 'الماسح الذكي نشط بأي مكان' ?></span>
        </span>
        <button type="button" class="btn btn-gold btn-sm" onclick="switchTab('quick-scanner')">
          <i class="fa-solid fa-barcode"></i> <?= $en ? 'Quick Price Scanner' : 'كاشف السعر السريع' ?>
        </button>
        <button type="button" class="btn btn-teal btn-sm" onclick="switchTab('pos')">
          <i class="fa-solid fa-file-invoice-dollar"></i> <?= $en ? 'POS & Invoices' : 'نقطة البيع والفواتير' ?>
        </button>
        <?php if (!$isAdmin): ?>
          <button type="button" class="btn btn-ghost btn-sm" onclick="switchTab('maintenance')">
            <i class="fa-solid fa-screwdriver-wrench"></i> <?= $en ? 'QR Maintenance' : 'إدارة الصيانة بالـ QR' ?>
          </button>
        <?php endif; ?>
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-globe"></i> <?= e(t('lang_switch_label')) ?>
        </a>
        <a href="<?= e(appUrl('index.php')) ?>" target="_blank" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> <?= $en ? 'Public Website' : 'الموقع الخارجي' ?>
        </a>
      </div>
    </header>

    <div id="globalScanHud" class="global-scan-hud" aria-live="polite"></div>

    <div class="content-wrap">

      <?php if ($isAdmin): ?>
      
      <section class="tab-panel active" id="tab-overview">
        <div class="card" style="padding:16px 22px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <strong><i class="fa-regular fa-calendar-days" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Report Period:' : 'عرض حسابات ومبيعات:' ?></strong>
              <button type="button" class="btn btn-teal btn-sm js-date-preset" data-preset="today"><?= $en ? 'Today' : 'اليوم' ?></button>
              <button type="button" class="btn btn-ghost btn-sm js-date-preset" data-preset="yesterday"><?= $en ? 'Yesterday' : 'أمس' ?></button>
              <button type="button" class="btn btn-ghost btn-sm js-date-preset" data-preset="month"><?= $en ? 'This Month' : 'هذا الشهر' ?></button>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <label style="font-size:0.82rem;font-weight:700;"><?= $en ? 'From:' : 'من:' ?></label>
              <input type="date" id="repDateFrom" class="form-control" style="width:auto;padding:6px 10px;" value="<?= date('Y-m-d') ?>">
              <label style="font-size:0.82rem;font-weight:700;"><?= $en ? 'To:' : 'إلى:' ?></label>
              <input type="date" id="repDateTo" class="form-control" style="width:auto;padding:6px 10px;" value="<?= date('Y-m-d') ?>">
              <button type="button" class="btn btn-gold btn-sm" id="btnLoadReport"><i class="fa-solid fa-rotate"></i> <?= $en ? 'Refresh' : 'تحديث الجرد' ?></button>
            </div>
          </div>
        </div>

        <div class="kpi-grid" id="overviewKpis"></div>

        
        <div class="card">
          <div class="card-header">
            <h3><i class="fa-solid fa-receipt" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Sales Invoices & Sold Items' : 'سجل الفواتير والقطع المباعة خلال الفترة المحددة' ?></h3>
            <button type="button" class="btn btn-teal btn-sm" onclick="switchTab('pos')"><i class="fa-solid fa-plus"></i> <?= $en ? 'New Invoice' : 'إصدار فاتورة جديدة' ?></button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th><?= $en ? 'Invoice #' : 'رقم الفاتورة' ?></th>
                  <th><?= $en ? 'Date / Time' : 'التوقيت' ?></th>
                  <th><?= $en ? 'Customer' : 'العميل' ?></th>
                  <th><?= $en ? 'Sold Items' : 'الأصناف المباعة' ?></th>
                  <th><?= $en ? 'Cashier' : 'البائع' ?></th>
                  <th><?= $en ? 'Total Collected' : 'الإجمالي المحصل' ?></th>
                  <?php if ($isAdmin): ?>
                    <th><?= $en ? 'Purchase Cost' : 'تكلفة الشراء' ?></th>
                    <th><?= $en ? 'Net Profit' : 'صافي الربح' ?></th>
                  <?php endif; ?>
                  <th><?= $en ? 'Actions' : 'الفاتورة' ?></th>
                </tr>
              </thead>
              <tbody id="dailyInvoicesBody">
                <tr><td colspan="<?= $isAdmin ? 9 : 7 ?>" style="text-align:center;padding:24px;">...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <?php if ($isAdmin): ?>
          <div class="card">
            <div class="card-header">
              <div>
                <h3><i class="fa-solid fa-screwdriver-wrench" style="color:var(--db-gold-600);margin-inline-end:6px;"></i><?= $en ? 'Delivered Repair Revenues (Post-Privacy Wipe Ledger)' : 'إيرادات أجهزة الصيانة المسلمة (بعد الحذف التلقائي لبيانات الأجهزة)' ?></h3>
                <span style="font-size:0.8rem;color:var(--db-muted);"><?= $en ? 'Only collected amounts are retained for daily accounting after device & personal customer data are wiped upon handover.' : 'يتم الاحتفاظ بالمبالغ المحصلة فقط لحساب إيراد وأرباح اليوم بعد مسح بيانات الجهاز والعميل فور التسليم.' ?></span>
              </div>
              <span style="padding:5px 12px;border-radius:999px;background:var(--db-teal-50);color:var(--db-teal-800);font-size:0.8rem;font-weight:800;"><i class="fa-solid fa-user-lock"></i> <?= $en ? 'Privacy Wipe Active' : 'حماية خصوصية مفعّلة' ?></span>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th><?= $en ? 'Ticket Ref' : 'كود التذكرة المرجعي' ?></th>
                    <th><?= $en ? 'Date' : 'التاريخ' ?></th>
                    <th><?= $en ? 'Device Type' : 'فئة الجهاز' ?></th>
                    <th><?= $en ? 'Amount Paid' : 'المبلغ المحصل' ?></th>
                    <th><?= $en ? 'Parts Cost' : 'تكلفة قطع الغيار' ?></th>
                    <th><?= $en ? 'Net Profit' : 'صافي ربح الصيانة' ?></th>
                    <th><?= $en ? 'Payment' : 'طريقة الدفع' ?></th>
                    <th><?= $en ? 'Delivered By' : 'المُسلّم' ?></th>
                  </tr>
                </thead>
                <tbody id="dailyMaintRevBody"></tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </section>
      <?php endif; ?>

      
      <section class="tab-panel<?= !$isAdmin ? ' active' : '' ?>" id="tab-quick-scanner">
        <div class="scanner-grid">

          <div class="camera-viewport-box">
            <h3 style="margin:0 0 6px;color:var(--db-teal-800);"><i class="fa-solid fa-camera"></i> <?= $en ? 'Scan 1D Barcode or 2D QR Code' : 'اسكان الباركود المطاول أو رمز الـ QR' ?></h3>
            <p style="margin:0 0 14px;font-size:0.84rem;color:var(--db-muted);"><?= $en ? 'Point your camera at the elongated box barcode or QR sticker to display customer selling price immediately.' : 'وجّه الكاميرا نحو الباركود الطولي أو الـ QR الموجود على الجهاز لعرض سعر البيع فوراً' ?></p>

            <div id="quickCameraReader" class="reader-mount">
              <div style="padding:24px;color:var(--db-muted);font-size:0.9rem;"><?= $en ? 'Click "Start Camera" to scan live or use a USB scanner / image' : 'اضغط «تشغيل الكاميرا» لبدء المسح المباشر أو استخدم قارئ الباركود' ?></div>
            </div>

            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:14px;">
              <button type="button" class="btn btn-teal btn-sm" id="btnStartQuickCam"><i class="fa-solid fa-play"></i> <?= $en ? 'Start Camera' : 'تشغيل الكاميرا' ?></button>
              <button type="button" class="btn btn-danger btn-sm" id="btnStopQuickCam" style="display:none;"><i class="fa-solid fa-stop"></i> <?= $en ? 'Stop Camera' : 'إيقاف الكاميرا' ?></button>
              <label class="btn btn-ghost btn-sm" style="cursor:pointer;margin:0;">
                <i class="fa-solid fa-image"></i> <?= $en ? 'Scan Barcode Image' : 'مسح من صورة باركود' ?>
                <input type="file" id="quickScanFile" accept="image/*" style="display:none;">
              </label>
            </div>

            <form id="quickManualForm" style="display:flex;gap:8px;">
              <input type="text" id="quickCodeInput" class="form-control" placeholder="<?= $en ? 'Scan or enter product barcode / QR code...' : 'أو امسح بمسدس الباركود / اكتب كود المنتج هنا...' ?>" style="direction:ltr;text-align:center;font-weight:800;">
              <button type="submit" class="btn btn-gold"><i class="fa-solid fa-magnifying-glass"></i> <?= $en ? 'Lookup' : 'فحص' ?></button>
            </form>
          </div>

          <div id="quickScanResult">
            <div class="card" style="text-align:center;padding:48px 24px;">
              <div style="font-size:2.6rem;color:var(--db-teal-700);margin-bottom:12px;"><i class="fa-solid fa-qrcode"></i></div>
              <h3 style="margin:0 0 8px;color:var(--db-text);"><?= $en ? 'Ready to Scan Product Code' : 'في انتظار مسح كود القطعة' ?></h3>
              <p style="margin:0;color:var(--db-muted);font-size:0.9rem;">
                <?= $en
                  ? 'Scanning displays <strong>Selling Price only</strong>, with a safe <strong>“Show Purchase Price”</strong> button that reveals the <strong>Minimum Allowed Floor Price</strong> in front of customers.'
                  : 'بمجرد مسح الباركود أو الـ QR سيظهر <strong>سعر البيع فقط</strong> بخط واضح، مع زر <strong>«اعرض سعر الشراء»</strong> الذي يعرض <strong>أقل سعر بيع مسموح</strong> بأمان أمام العميل.' ?>
              </p>
            </div>
          </div>

        </div>
      </section>

      
      <section class="tab-panel" id="tab-pos">
        <div class="pos-layout">

          <div class="card">
            <div class="card-header">
              <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Select Products or Scan into Invoice' : 'اختر المنتجات أو امسح الكود للإضافة للفاتورة' ?></h3>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-teal btn-sm" id="btnPosToggleCam"><i class="fa-solid fa-camera"></i> <?= $en ? 'Scan with Camera' : 'مسح بالكاميرا للفاتورة' ?></button>
                <label class="btn btn-ghost btn-sm" style="cursor:pointer;margin:0;">
                  <i class="fa-solid fa-image"></i> <?= $en ? 'Scan Image' : 'مسح صورة كود' ?>
                  <input type="file" id="posScanFile" accept="image/*" style="display:none;">
                </label>
              </div>
            </div>

            <div id="posCameraWrap" style="display:none;margin-bottom:16px;" class="camera-viewport-box">
              <div id="posCameraReader" class="reader-mount" style="min-height:210px;"></div>
              <div id="posCameraLiveLog" class="pos-cam-live-log">
                <i class="fa-solid fa-crosshairs" style="color:var(--db-teal-700);"></i>
                <span><?= $en ? 'Point camera at barcode/QR — items add immediately with sound & live counter' : 'وجّه الكاميرا نحو الباركود أو الـ QR — ستُضاف القطع فوراً للفاتورة مع تنبيه صوتي وعداد مباشر' ?></span>
              </div>
              <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:10px;">
                <button type="button" class="btn btn-gold btn-sm" id="btnPosJumpToCartFromCam"><i class="fa-solid fa-file-invoice-dollar"></i> <?= $en ? 'Review Invoice' : 'مراجعة الفاتورة الحالية' ?></button>
                <button type="button" class="btn btn-danger btn-sm" id="btnPosCloseCam"><i class="fa-solid fa-xmark"></i> <?= $en ? 'Close Camera' : 'إغلاق الكاميرا' ?></button>
              </div>
            </div>

            <div id="posScanFeedbackBox" class="pos-scan-feedback" aria-live="assertive">
              <div class="pos-scan-idle-row">
                <span class="pos-scan-icon-orb"><i class="fa-solid fa-qrcode"></i></span>
                <div style="flex:1;min-width:180px;">
                  <strong><?= $en ? 'Instant Barcode & QR Addition Ready' : 'جاهز للإضافة الفورية بالباركود أو الـ QR' ?></strong>
                  <div style="font-size:0.79rem;color:var(--db-muted);"><?= $en ? 'Scan anywhere with your barcode gun, use camera scan, or click any product below — confirmation appears right here.' : 'امسح الكود بمسدس الباركود في أي مكان بالصفحة أو بالكاميرا أو اضغط على المنتج — سيظهر تأكيد الإضافة والكمية والإجمالي هنا فوراً.' ?></div>
                </div>
              </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
              <input type="text" id="posSearchInput" class="form-control" placeholder="<?= $en ? 'Search by name or scan barcode & press Enter...' : 'ابحث بالاسم أو امسح الباركود واضغط Enter...' ?>" style="flex:1;min-width:200px;">
              <select id="posCatFilter" class="form-control" style="width:auto;">
                <option value="all"><?= $en ? 'All Categories' : 'كل الأقسام' ?></option>
                <option value="laptops"><?= $en ? 'Laptops' : 'لابتوب' ?></option>
                <option value="mobiles"><?= $en ? 'Smartphones' : 'هواتف' ?></option>
                <option value="accessories"><?= $en ? 'Accessories' : 'إكسسوارات' ?></option>
                <option value="wearables"><?= $en ? 'Wearables' : 'ساعات ذكية' ?></option>
              </select>
            </div>

            <div class="pos-products-grid" id="posProductsGrid"></div>
          </div>

          <div class="card" id="posInvoiceCard" style="border:1.5px solid var(--db-teal-100);">
            <div class="card-header">
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <h3><i class="fa-solid fa-file-invoice-dollar" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Active Sales Invoice' : 'فاتورة البيع الحالية' ?></h3>
                <span id="posCartCountHeader" class="pos-header-qty-pill" style="display:none;">0</span>
              </div>
              <button type="button" class="btn btn-danger btn-sm" id="btnClearCart"><i class="fa-solid fa-trash-can"></i> <?= $en ? 'Clear Cart' : 'إفراغ السلة' ?></button>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><?= $en ? 'Customer Name' : 'اسم العميل' ?></label>
                <input type="text" id="posCustomerName" class="form-control" placeholder="<?= $en ? 'Walk-in Customer / Buyer Name' : 'عميل نقدي / اسم المشتري' ?>">
              </div>
              <div class="form-group">
                <label><?= $en ? 'Customer WhatsApp / Phone' : 'رقم واتساب / هاتف العميل (لإرسال الفاتورة)' ?></label>
                <input type="text" id="posCustomerPhone" class="form-control" placeholder="010xxxxxxxx" style="direction:ltr;">
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group">
                <label><?= $en ? 'Payment Method' : 'طريقة الدفع' ?></label>
                <select id="posPaymentMethod" class="form-control">
                  <option value="cash"><?= $en ? 'Cash' : 'نقدي (كاش بالدرج)' ?></option>
                  <option value="vodafone_cash"><?= $en ? 'Vodafone Cash / Wallet' : 'فودافون كاش / محفظة' ?></option>
                  <option value="instapay">InstaPay</option>
                  <option value="card"><?= $en ? 'Bank Card' : 'بطاقة بنكية' ?></option>
                </select>
              </div>
              <div class="form-group">
                <label><?= $en ? 'Invoice Notes' : 'ملاحظات إضافية بالفاتورة' ?></label>
                <input type="text" id="posInvoiceNotes" class="form-control" placeholder="<?= $en ? 'e.g. Includes free sleeve & mouse' : 'مثال: شامل شنطة وماوس هدية' ?>">
              </div>
            </div>

            <div id="posCartItemsList"></div>

            <div style="background:linear-gradient(135deg,var(--db-teal-50),var(--db-gold-50));padding:16px 20px;border-radius:16px;display:flex;justify-content:space-between;align-items:center;margin:16px 0;border:1.5px solid var(--db-gold-500);">
              <span style="font-weight:800;color:var(--db-text);"><?= $en ? 'Invoice Total:' : 'إجمالي الفاتورة المطلوب:' ?></span>
              <strong id="posTotalDisplay" style="font-size:1.5rem;color:var(--db-teal-800);">0.00 <?= e(t('currency')) ?></strong>
            </div>

            <button type="button" class="btn btn-teal" id="btnCompleteSale" style="width:100%;padding:14px;font-size:0.98rem;">
              <i class="fa-solid fa-circle-check"></i> <?= $en ? 'Complete Sale & Generate Invoice (PNG + WhatsApp + Print)' : 'إتمام البيع وإصدار الفاتورة (صورة + واتساب + طباعة)' ?>
            </button>
          </div>

        </div>

        <div id="posFloatingCartBar" class="pos-floating-bar" style="display:none;">
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="pos-floating-badge" id="posFloatingCount">0</span>
            <div>
              <div style="font-size:0.76rem;color:var(--db-muted);font-weight:700;"><?= $en ? 'Items in Active Invoice' : 'القطع المضافة للفاتورة الحالية' ?></div>
              <strong id="posFloatingTotal" style="font-size:1.05rem;color:var(--db-teal-800);">0.00 <?= e(t('currency')) ?></strong>
            </div>
          </div>
          <button type="button" class="btn btn-teal btn-sm" id="btnFloatingJumpInvoice">
            <i class="fa-solid fa-arrow-down"></i> <?= $en ? 'Go to Invoice' : 'عرض وإتمام الفاتورة' ?>
          </button>
        </div>
      </section>

      <?php if ($isAdmin): ?>
      
      <section class="tab-panel" id="tab-add-product">
        <div class="add-flow">

          <header class="add-flow-head">
            <h2><i class="fa-solid fa-box-open"></i> <?= $en ? 'Add Product to Stock' : 'إضافة منتج للمخزن' ?></h2>
            <p><?= $en ? 'One step at a time: scan the code, then fill the short form.' : 'خطوة واحدة في كل مرة: امسح الكود، وبعدها املا الفورم القصير.' ?></p>
            <ol class="add-flow-steps">
              <li class="is-current" data-flow-dot="scan"><span>1</span> <?= $en ? 'Scan Code' : 'امسح الكود' ?></li>
              <li data-flow-dot="form"><span>2</span> <?= $en ? 'Fill Details' : 'اكتب البيانات' ?></li>
              <li data-flow-dot="done"><span>3</span> <?= $en ? 'Saved' : 'تم الحفظ' ?></li>
            </ol>
          </header>

          <div class="add-flow-stage" id="stageScan">
            <div class="camera-viewport-box">
              <div id="addProdCameraReader" class="reader-mount" style="min-height:260px;">
                <div style="padding:22px;color:var(--db-muted);font-size:0.9rem;"><?= $en ? 'Camera is off — press the big button below to start scanning' : 'الكاميرا متوقفة — اضغط الزر الكبير اللي تحت لبدء المسح' ?></div>
              </div>

              <div class="add-flow-actions">
                <button type="button" class="btn btn-teal" id="btnStartAddCam" style="min-width:220px;padding:13px 20px;font-size:0.98rem;"><i class="fa-solid fa-camera"></i> <?= $en ? 'Start Camera & Scan' : 'تشغيل الكاميرا وابدأ المسح' ?></button>
                <button type="button" class="btn btn-danger" id="btnStopAddCam" style="display:none;padding:13px 20px;"><i class="fa-solid fa-stop"></i> <?= $en ? 'Stop' : 'إيقاف' ?></button>
              </div>

              <div class="add-flow-alt">
                <span><?= $en ? 'No box barcode?' : 'مفيش باركود على العلبة؟' ?></span>
                <button type="button" class="btn btn-gold btn-sm" id="btnGenerateAutoQr"><i class="fa-solid fa-wand-magic-sparkles"></i> <?= $en ? 'Generate QR' : 'ولّد QR' ?></button>
                <button type="button" class="btn btn-ghost btn-sm" id="btnSkipScan"><i class="fa-solid fa-keyboard"></i> <?= $en ? 'Type code manually' : 'اكتب الكود يدوي' ?></button>
              </div>
            </div>

            <div class="add-flow-hint">
              <i class="fa-solid fa-circle-info"></i>
              <span><?= $en ? 'If the code already exists, you will be asked how many units to add to the current stock — no duplicate products.' : 'لو الكود موجود بالفعل، هنسألك كام قطعة تضيفها على المخزون الحالي — ومش هيتكرر منتج.' ?></span>
            </div>
          </div>

          <div class="add-flow-stage" id="stageForm" style="display:none;">
            <div class="card">
              <div class="slim-code-strip">
                <span class="slim-code-label"><?= $en ? 'Scanned code' : 'الكود اللي اتمسح' ?></span>
                <strong id="slimCodeText" style="direction:ltr;">—</strong>
                <span class="slim-code-tag" id="slimCodeType">QR</span>
              </div>

              <div id="existingProdAlert" class="add-existing-alert" style="display:none;"></div>

              <div id="scanQtyBar" class="add-qty-panel" style="display:none;">
                <div class="add-qty-head">
                  <span class="add-qty-ico"><i class="fa-solid fa-boxes-stacked"></i></span>
                  <div>
                    <strong><?= $en ? 'This product already exists' : 'المنتج ده موجود بالفعل' ?></strong>
                    <div class="add-qty-sub"><?= $en ? 'Current stock:' : 'المتاح حالياً:' ?> <strong id="scanQtyExisting">0</strong> <?= $en ? 'units' : 'قطعة' ?></div>
                  </div>
                </div>
                <div class="add-qty-row">
                  <label for="scanQtyInput"><?= $en ? 'How many to add?' : 'كام قطعة تريد تضيفها؟' ?></label>
                  <button type="button" class="qty-step" data-qty-step="-1">−</button>
                  <input type="number" min="1" step="1" value="1" id="scanQtyInput" class="scan-qty-input">
                  <button type="button" class="qty-step" data-qty-step="1">+</button>
                </div>
                <div class="add-qty-preview"><?= $en ? 'Stock will become:' : 'المخزون سيصبح:' ?> <strong id="scanQtyPreview">1</strong></div>
                <button type="button" class="btn btn-teal" id="btnApplyQty" style="width:100%;padding:12px;font-size:0.95rem;"><i class="fa-solid fa-plus"></i> <?= $en ? 'Add and scan next' : 'أضف الكمية وامسح العنصر التالي' ?></button>
              </div>

              <form id="productSaveForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prodId" value="0">
                <input type="hidden" name="barcode" id="prodBarcode" value="">
                <input type="hidden" name="code_type" id="prodCodeType" value="qr">
                <input type="hidden" name="name_en" id="prodNameEn" value="">
                <input type="hidden" name="category" id="prodCategory" value="accessories">
                <input type="hidden" name="condition_status" id="prodCondition" value="new">
                <input type="hidden" name="brand" id="prodBrand" value="">
                <input type="hidden" name="specs" id="prodSpecs" value="">
                <input type="hidden" name="specs_en" id="prodSpecsEn" value="">
                <input type="hidden" name="description" id="prodDescription" value="">
                <input type="hidden" name="low_stock_threshold" id="prodLowStock" value="2">
                <input type="hidden" name="warranty_default" id="prodWarranty" value="<?= e($en ? 'Certified ASLY Testing Warranty' : 'ضمان الفحص والتجربة - أصلي تك ستور') ?>">
                <input type="hidden" name="show_on_landing" id="prodShowLanding" value="0">
                <input type="hidden" name="is_featured" id="prodFeatured" value="0">

                <div class="add-form-block" id="newProductFields">
                  <div class="form-group">
                    <label><?= $en ? 'Product name' : 'اسم المنتج' ?></label>
                    <input type="text" name="name" id="prodName" class="form-control" placeholder="<?= $en ? 'Dell XPS 15 Laptop' : 'لابتوب ديل Dell XPS 15' ?>">
                  </div>

                  <div class="add-price-panel">
                    <div class="add-price-head"><i class="fa-solid fa-coins"></i> <?= $en ? 'Smart 3-Tier Pricing' : 'نظام التسعير الثلاثي الذكي' ?></div>
                    <div class="form-group">
                      <label><?= $en ? 'Purchase cost (admin only)' : 'سعر الشراء الحقيقي (سري للأدمن)' ?></label>
                      <input type="number" step="0.01" min="0" name="purchase_price" id="prodPurchasePrice" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                      <label><?= $en ? 'Selling price' : 'سعر البيع الأساسي' ?></label>
                      <input type="number" step="0.01" min="0" name="sell_price" id="prodSellPrice" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                      <label><?= $en ? 'Minimum floor price' : 'أقل سعر بيع مسموح' ?></label>
                      <input type="number" step="0.01" min="0" name="min_sell_price" id="prodMinSellPrice" class="form-control" placeholder="0.00">
                    </div>
                    <div id="liveProfitHint" class="add-profit-hint"></div>
                  </div>

                  <div class="form-group">
                    <label><?= $en ? 'Quantity in stock' : 'الكمية المتوفرة بالمخزن' ?></label>
                    <input type="number" min="0" name="quantity" id="prodQuantity" class="form-control" value="1">
                  </div>
                </div>

                <div class="form-group">
                  <label><i class="fa-solid fa-image"></i> <?= $en ? 'Product photo' : 'الصورة الخاصة بالمنتج' ?></label>
                  <div class="img-capture-row">
                    <input type="file" name="image" id="prodImageInput" accept="image/*" class="form-control">
                    <input type="file" id="prodImageCapture" accept="image/*" capture="environment" style="display:none;">
                    <div class="img-capture-actions">
                      <button type="button" class="btn btn-teal btn-sm" id="btnCaptureProdImage"><i class="fa-solid fa-camera"></i> <?= $en ? 'Take Photo Now' : 'صور بالكاميرا الآن' ?></button>
                    </div>
                    <div class="img-capture-preview" id="prodImagePreview">
                      <img alt="" id="prodImagePreviewThumb">
                      <span style="flex:1;font-size:0.78rem;color:var(--db-muted);" id="prodImagePreviewName"></span>
                      <button type="button" id="btnClearProdImage"><i class="fa-solid fa-xmark"></i> <?= $en ? 'Remove' : 'حذف' ?></button>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-teal" style="width:100%;padding:14px;font-size:1rem;">
                  <i class="fa-solid fa-floppy-disk"></i> <?= $en ? 'Save Product' : 'حفظ المنتج' ?>
                </button>
                <button type="button" class="btn btn-ghost btn-sm" id="btnBackToScan" style="width:100%;margin-top:8px;">
                  <i class="fa-solid fa-rotate-left"></i> <?= $en ? 'Back to scanning' : 'رجوع للمسح' ?>
                </button>
              </form>
            </div>
          </div>

          <div class="prod-cam-overlay" id="prodCamOverlay">
            <div class="prod-cam-box">
              <div class="prod-cam-head">
                <strong><i class="fa-solid fa-camera"></i> <?= $en ? 'Take the product photo' : 'صوّر المنتج دلوقتي' ?></strong>
                <button type="button" id="btnProdCamClose"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div class="prod-cam-stage">
                <video id="prodCamVideo" autoplay playsinline muted></video>
              </div>
              <div class="prod-cam-actions">
                <button type="button" class="btn btn-teal" id="btnProdCamShot"><i class="fa-solid fa-circle-dot"></i> <?= $en ? 'Capture' : 'التقط الصورة' ?></button>
                <span class="prod-cam-hint"><?= $en ? 'Frame the device so the whole product is clear.' : 'خلّي الجهاز واضح وكامل داخل الإطار.' ?></span>
              </div>
            </div>
            <canvas id="prodCamCanvas" style="display:none;"></canvas>
          </div>

          <div class="add-flow-stage" id="stageDone" style="display:none;">
            <div class="card add-done-card">
              <span class="add-done-orb"><i class="fa-solid fa-circle-check"></i></span>
              <h3 id="addDoneTitle"><?= $en ? 'Saved successfully' : 'تم الحفظ بنجاح' ?></h3>
              <p id="addDoneText"><?= $en ? 'The product is now in stock.' : 'المنتج بقى داخل المخزون.' ?></p>
              <div class="add-done-summary" id="addDoneSummary"></div>
              <div class="add-done-actions">
                <button type="button" class="btn btn-teal" id="btnScanNextProduct"><i class="fa-solid fa-camera"></i> <?= $en ? 'Scan next product' : 'امسح المنتج التالي' ?></button>
                <button type="button" class="btn btn-ghost" id="btnSeeInventory"><i class="fa-solid fa-boxes-stacked"></i> <?= $en ? 'Open inventory' : 'فتح المخزون' ?></button>
              </div>
              <div class="sticker-preview-box" id="liveStickerBox" style="margin-top:18px;">
                <strong style="font-size:0.84rem;color:#0d9499;">ASLY | أصلي TECH STORE</strong>
                <div id="stickerProdName" style="font-size:0.78rem;color:#475569;max-width:210px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $en ? 'New Product' : 'منتج جديد' ?></div>
                <div id="stickerQrMount" style="margin:4px auto;"></div>
                <svg id="stickerBarcodeSvg" style="max-width:210px;height:48px;"></svg>
                <div id="stickerCodeText" style="font-family:monospace;font-weight:800;font-size:0.82rem;letter-spacing:1px;">—</div>
                <button type="button" class="btn btn-teal btn-sm" id="btnPrintCurrentSticker" style="margin-top:10px;"><i class="fa-solid fa-print"></i> <?= $en ? 'Print Sticker' : 'طباعة الاستيكر' ?></button>
              </div>
            </div>
          </div>

        </div>
      </section>

      <section class="tab-panel" id="tab-inventory">
        <div class="card">
          <div class="card-header">
            <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Inventory & Stock Catalog' : 'سجل المخزون والأجهزة والإكسسوارات' ?></h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <input type="text" id="invSearch" class="form-control" placeholder="<?= $en ? 'Search name, barcode, brand...' : 'بحث بالاسم أو الكود أو الماركة...' ?>" style="width:240px;">
              <select id="invCategory" class="form-control" style="width:auto;">
                <option value="all"><?= $en ? 'All Categories' : 'كل الأقسام' ?></option>
                <option value="laptops"><?= $en ? 'Laptops' : 'لابتوب' ?></option>
                <option value="mobiles"><?= $en ? 'Smartphones' : 'هواتف' ?></option>
                <option value="accessories"><?= $en ? 'Accessories' : 'إكسسوارات' ?></option>
                <option value="wearables"><?= $en ? 'Wearables' : 'ساعات ذكية' ?></option>
              </select>
              <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-teal btn-sm" onclick="switchTab('add-product')"><i class="fa-solid fa-plus"></i> <?= $en ? 'Add Product' : 'إضافة منتج جديد' ?></button>
              <?php endif; ?>
            </div>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th><?= $en ? 'Photo' : 'الصورة' ?></th>
                  <th><?= $en ? 'Product & Specs' : 'المنتج والمواصفات' ?></th>
                  <th><?= $en ? 'Code (QR/Barcode)' : 'الكود (QR/Barcode)' ?></th>
                  <th><?= $en ? 'Stock' : 'الكمية' ?></th>
                  <?php if ($isAdmin): ?>
                    <th><?= $en ? 'True Buy Cost' : 'سعر الشراء (سري)' ?></th>
                  <?php endif; ?>
                  <th><?= $en ? 'Sell Price' : 'سعر البيع' ?></th>
                  <th><?= $en ? 'Min Floor Price' : 'أقل سعر مسموح' ?></th>
                  <th><?= $en ? 'Actions' : 'إجراءات' ?></th>
                </tr>
              </thead>
              <tbody id="inventoryTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>
      <?php endif; ?>

      
      <section class="tab-panel" id="tab-maintenance">
        <div class="card">
          <div class="card-header">
            <div>
              <h3><i class="fa-solid fa-screwdriver-wrench" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Active QR Repair Tickets & Stage Management' : 'إدارة أجهزة الصيانة النشطة وتتبع المراحل بالـ QR' ?></h3>
              <span style="font-size:0.82rem;color:var(--db-muted);"><?= $en ? 'Unique customer QR receipt per device, automatic stage email notifications, and full personal data wipe upon delivery.' : 'كل جهاز يُولّد له QR خاص للعميل، مع إشعار بريد إلكتروني بكل مرحلة، وحذف كامل للبيانات فور التسليم مع حفظ الإيراد فقط.' ?></span>
            </div>
            <button type="button" class="btn btn-teal" id="btnOpenNewMaint"><i class="fa-solid fa-plus"></i> <?= $en ? 'Receive Repair Device' : 'استلام جهاز صيانة جديد' ?></button>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            <input type="text" id="maintSearch" class="form-control" placeholder="<?= $en ? 'Search ticket code, customer, phone, or device model...' : 'ابحث بكود التذكرة، اسم العميل، الهاتف، أو موديل الجهاز...' ?>" style="flex:1;min-width:240px;">
            <select id="maintStageFilter" class="form-control" style="width:auto;">
              <option value="all"><?= $en ? 'All Active Stages' : 'كل المراحل النشطة' ?></option>
              <?php foreach ($stages as $sKey => $sInfo): if ($sKey === 'delivered') continue; ?>
                <option value="<?= e($sKey) ?>"><?= e($sInfo['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th><?= $en ? 'Ticket #' : 'كود التذكرة' ?></th>
                  <th><?= $en ? 'Customer & Contact' : 'العميل والتواصل السريع' ?></th>
                  <th><?= $en ? 'Device & Issue' : 'الجهاز والعطل' ?></th>
                  <th><?= $en ? 'Current Stage' : 'المرحلة الحالية' ?></th>
                  <th><?= $en ? 'Est. / Deposit' : 'التكلفة / العربون' ?></th>
                  <th><?= $en ? 'Client Notes' : 'ملاحظات العميل' ?></th>
                  <th><?= $en ? 'Manage' : 'إدارة وتحديث' ?></th>
                </tr>
              </thead>
              <tbody id="maintTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <?php if ($isAdmin): ?>
      
      <section class="tab-panel" id="tab-staff">
        <div class="card" style="background:linear-gradient(135deg,var(--db-teal-50),var(--db-gold-50));border-color:var(--db-gold-500);">
          <h3 style="margin:0 0 8px;color:var(--db-teal-800);"><i class="fa-solid fa-shield-halved"></i> <?= $en ? 'Role Separation & Price Floor Protection' : 'نظام حماية الأسعار والصلاحيات بين المدير والموظفين' ?></h3>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;font-size:0.86rem;color:var(--db-text);">
            <div><strong><i class="fa-solid fa-user-shield" style="color:var(--db-gold-600);"></i> Admin:</strong> <?= $en ? 'Full access to true purchase cost, inventory capital, net invoice profit, delivered maintenance revenues, and social/brand settings.' : 'يتحكم في كل شيء، يرى سعر الشراء الحقيقي، رأس مال المخزن، صافي أرباح كل فاتورة، إيرادات الصيانة اليومية، وروابط السوشيال ميديا.' ?></div>
            <div><strong><i class="fa-solid fa-id-badge" style="color:var(--db-teal-700);"></i> Staff:</strong> <?= $en ? 'Sees ONLY 3 modules: Quick Price Scanner, POS & Official Invoices (strictly blocked below Minimum Floor Price), and Interactive QR Maintenance with stages & email alerts.' : 'يظهر له 3 أقسام فقط: (كاشف السعر السريع)، (نقطة البيع وإصدار الفواتير الرسمية مع المنع من البيع بأقل من الحد الأدنى)، و(إدارة الصيانة التفاعلية بالـ QR والمراحل والإيميل).' ?></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3><i class="fa-solid fa-users" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Admin & Sales Staff Accounts' : 'حسابات المديرين وموظفي المبيعات' ?></h3>
            <button type="button" class="btn btn-teal btn-sm" id="btnAddUserModal"><i class="fa-solid fa-user-plus"></i> <?= $en ? 'Add Account' : 'إضافة موظف / مدير جديد' ?></button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th><?= $en ? 'Full Name' : 'الاسم الكامل' ?></th>
                  <th><?= $en ? 'Username' : 'اسم المستخدم' ?></th>
                  <th><?= $en ? 'Role' : 'الصلاحية' ?></th>
                  <th><?= $en ? 'Phone' : 'الهاتف' ?></th>
                  <th><?= $en ? 'Status' : 'الحالة' ?></th>
                  <th><?= $en ? 'Created' : 'تاريخ الإنشاء' ?></th>
                  <th><?= $en ? 'Actions' : 'إجراءات' ?></th>
                </tr>
              </thead>
              <tbody id="usersTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      
      <section class="tab-panel" id="tab-settings">

        
        <form id="settingsSaveForm" class="card">
          <div class="card-header">
            <div>
              <h3><i class="fa-solid fa-share-nodes" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Official Social Media Links, Brand Identity & SMTP' : 'روابط منصات السوشيال ميديا، نبذة من نحن، وإعدادات البريد' ?></h3>
              <span style="font-size:0.82rem;color:var(--db-muted);"><?= $en ? 'Update any social media link below and it updates live on the public Landing Page immediately (leave blank to hide any platform).' : 'عدّل أي رابط سوشيال ميديا أدناه ليتم تحديثه فوراً في الصفحة الرئيسية للزوار (اترك الحقل فارغاً لإخفاء أي منصة).' ?></span>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" id="btnViewEmailLogs"><i class="fa-solid fa-inbox"></i> <?= $en ? 'Sent Email Outbox' : 'سجل البريد المرسل' ?></button>
          </div>

          <div style="background:linear-gradient(135deg,var(--db-teal-50),#ffffff);padding:20px;border-radius:18px;border:1.5px solid var(--db-teal-100);margin-bottom:22px;">
            <div style="font-weight:900;color:var(--db-teal-800);margin-bottom:14px;font-size:0.98rem;">
              <i class="fa-solid fa-phone-volume"></i> <?= $en ? 'Website & Invoice Phone & WhatsApp Numbers (Live Editable)' : 'أرقام الهاتف والواتساب الظاهرة في الموقع والفواتير (قابلة للتعديل الفوري)' ?>
            </div>
            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><i class="fa-solid fa-phone" style="color:#0d9499;margin-inline-end:5px;"></i><?= $en ? 'Website & Invoice Phone 1' : 'رقم الهاتف الأول بالموقع والفواتير' ?></label>
                <input type="text" name="primary_phone" id="set_primary_phone" class="form-control" placeholder="01015981677" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><?= $en ? 'Phone 1 Display Label' : 'عنوان رقم الهاتف الأول بالموقع' ?></label>
                <input type="text" name="primary_phone_label" id="set_primary_phone_label" class="form-control" placeholder="مبيعات وصيانة - الرقم الرئيسي">
              </div>
              <div class="form-group">
                <label><i class="fa-solid fa-phone" style="color:#0d9499;margin-inline-end:5px;"></i><?= $en ? 'Website & Invoice Phone 2' : 'رقم الهاتف الثاني بالموقع والفواتير' ?></label>
                <input type="text" name="secondary_phone" id="set_secondary_phone" class="form-control" placeholder="01154639718" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><?= $en ? 'Phone 2 Display Label' : 'عنوان رقم الهاتف الثاني بالموقع' ?></label>
                <input type="text" name="secondary_phone_label" id="set_secondary_phone_label" class="form-control" placeholder="مبيعات وصيانة - الرقم الإضافي">
              </div>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label><i class="fa-brands fa-whatsapp" style="color:#059669;margin-inline-end:5px;"></i><?= $en ? 'Official WhatsApp 1' : 'رقم واتساب الأول بالموقع' ?></label>
                <input type="text" name="primary_whatsapp" id="set_primary_whatsapp" class="form-control" placeholder="201015981677" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><?= $en ? 'WhatsApp 1 Display Label' : 'عنوان واتساب الأول بالموقع' ?></label>
                <input type="text" name="primary_whatsapp_label" id="set_primary_whatsapp_label" class="form-control" placeholder="واتساب الاستفسارات والمبيعات">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-whatsapp" style="color:#059669;margin-inline-end:5px;"></i><?= $en ? 'Official WhatsApp 2' : 'رقم واتساب الثاني بالموقع' ?></label>
                <input type="text" name="secondary_whatsapp" id="set_secondary_whatsapp" class="form-control" placeholder="201154639718" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><?= $en ? 'WhatsApp 2 Display Label' : 'عنوان واتساب الثاني بالموقع' ?></label>
                <input type="text" name="secondary_whatsapp_label" id="set_secondary_whatsapp_label" class="form-control" placeholder="واتساب قسم الصيانة والدعم الفني">
              </div>
            </div>
          </div>

          <div style="background:linear-gradient(135deg,var(--db-teal-50),var(--db-gold-50));padding:20px;border-radius:18px;border:1.5px solid var(--db-gold-500);margin-bottom:22px;">
            <div style="font-weight:900;color:var(--db-teal-800);margin-bottom:14px;font-size:0.98rem;">
              <i class="fa-solid fa-hashtag"></i> <?= $en ? 'Social Media Links (Live on Public Website)' : 'روابط حسابات السوشيال ميديا الرسمية (تظهر مباشرة باللاندينج بيدج)' ?>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label><i class="fa-brands fa-facebook-f" style="color:#0d9499;margin-inline-end:5px;"></i>Facebook URL</label>
                <input type="url" name="social_facebook" id="set_social_facebook" class="form-control" placeholder="https://facebook.com/..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-instagram" style="color:#c49029;margin-inline-end:5px;"></i>Instagram URL</label>
                <input type="url" name="social_instagram" id="set_social_instagram" class="form-control" placeholder="https://instagram.com/..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-tiktok" style="color:#1e293b;margin-inline-end:5px;"></i>TikTok URL</label>
                <input type="url" name="social_tiktok" id="set_social_tiktok" class="form-control" placeholder="https://tiktok.com/@..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-telegram" style="color:#0d9499;margin-inline-end:5px;"></i>Telegram URL</label>
                <input type="url" name="social_telegram" id="set_social_telegram" class="form-control" placeholder="https://t.me/..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-youtube" style="color:#dc2626;margin-inline-end:5px;"></i>YouTube URL</label>
                <input type="url" name="social_youtube" id="set_social_youtube" class="form-control" placeholder="https://youtube.com/@..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-brands fa-x-twitter" style="color:#1e293b;margin-inline-end:5px;"></i>X (Twitter) URL</label>
                <input type="url" name="social_x" id="set_social_x" class="form-control" placeholder="https://x.com/..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-solid fa-location-dot" style="color:#c49029;margin-inline-end:5px;"></i><?= $en ? 'Google Maps URL' : 'رابط اللوكيشن على خرائط جوجل' ?></label>
                <input type="url" name="google_maps_url" id="set_google_maps_url" class="form-control" placeholder="https://maps.google.com/..." style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><i class="fa-solid fa-envelope" style="color:#0d9499;margin-inline-end:5px;"></i><?= $en ? 'Official Public Email' : 'البريد الإلكتروني الرسمي للتواصل' ?></label>
                <input type="email" name="primary_email" id="set_primary_email" class="form-control" placeholder="contact@aslystore.com" style="direction:ltr;">
              </div>
            </div>
          </div>

          
          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Store Name (Arabic)' : 'اسم المركز بالعربية' ?></label>
              <input type="text" name="store_name_ar" id="set_store_name_ar" class="form-control">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Store Name (English)' : 'اسم المركز بالإنجليزية' ?></label>
              <input type="text" name="store_name_en" id="set_store_name_en" class="form-control" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Who We Are Story (Arabic)' : 'نص «من نحن» بالصفحة الرئيسية (عربي)' ?></label>
              <textarea name="about_story_ar" id="set_about_story_ar" rows="3" class="form-control"></textarea>
            </div>
            <div class="form-group">
              <label><?= $en ? 'Who We Are Story (English)' : 'نص «من نحن» بالصفحة الرئيسية (إنجليزي)' ?></label>
              <textarea name="about_story_en" id="set_about_story_en" rows="3" class="form-control" style="direction:ltr;"></textarea>
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Store Tagline (Arabic)' : 'الوصف المختصر (عربي)' ?></label>
              <input type="text" name="store_tagline" id="set_store_tagline" class="form-control">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Store Tagline (English)' : 'الوصف المختصر (إنجليزي)' ?></label>
              <input type="text" name="store_tagline_en" id="set_store_tagline_en" class="form-control" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Management Line (Arabic)' : 'سطر الإدارة والإشراف (عربي)' ?></label>
              <input type="text" name="founders_line" id="set_founders_line" class="form-control">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Management Line (English)' : 'سطر الإدارة والإشراف (إنجليزي)' ?></label>
              <input type="text" name="founders_line_en" id="set_founders_line_en" class="form-control" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Address (Arabic)' : 'العنوان (عربي)' ?></label>
              <input type="text" name="store_address" id="set_store_address" class="form-control">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Address (English)' : 'العنوان (إنجليزي)' ?></label>
              <input type="text" name="store_address_en" id="set_store_address_en" class="form-control" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label><?= $en ? 'Working Hours (Arabic)' : 'مواعيد العمل (عربي)' ?></label>
              <input type="text" name="working_hours" id="set_working_hours" class="form-control">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Working Hours (English)' : 'مواعيد العمل (إنجليزي)' ?></label>
              <input type="text" name="working_hours_en" id="set_working_hours_en" class="form-control" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:22px;">
            <div class="form-group">
              <label><?= $en ? 'Public Base URL for QR Links (Leave empty for auto-detect or enter LAN IP on XAMPP)' : 'الرابط الأساسي لتوليد أكواد QR الصيانة والفواتير (اتركه فارغاً للتعرف التلقائي أو ضع IP الشبكة)' ?></label>
              <input type="text" name="public_base_url" id="set_public_base_url" class="form-control" placeholder="http://192.168.1.10/asly" style="direction:ltr;">
            </div>
            <div class="form-group">
              <label><?= $en ? 'Invoice Warranty Footer Note' : 'نص الضمان أسفل الفاتورة' ?></label>
              <input type="text" name="invoice_footer_note" id="set_invoice_footer_note" class="form-control">
            </div>
          </div>

          <div style="background:linear-gradient(135deg,#fffbf0,#effafa);padding:20px;border-radius:18px;border:1.5px solid var(--db-gold-500);margin-bottom:22px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
              <div>
                <strong style="color:var(--db-teal-800);font-size:0.98rem;"><i class="fa-solid fa-magnifying-glass-chart"></i> <?= $en ? 'SEO & AI Answer Engine Optimization (Google, ChatGPT, Gemini, Perplexity)' : 'تهيئة محركات البحث (SEO) والظهور في إجابات الذكاء الاصطناعي (ChatGPT / Gemini / Perplexity)' ?></strong>
                <div style="font-size:0.8rem;color:var(--db-muted);"><?= $en ? 'Controls Google meta tags, Schema.org Knowledge Graph, and live llms.txt / ai.json feeds read by AI assistants.' : 'يتحكم مباشرة في بيانات جوجل وخريطة المعرفة Schema.org وملفات llms.txt و ai.json التي تقرأها محركات الذكاء الاصطناعي لترشيح المركز.' ?></div>
              </div>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <a href="<?= e(appUrl('llms.txt')) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-robot"></i> llms.txt</a>
                <a href="<?= e(appUrl('llms-full.txt')) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-brain"></i> llms-full.txt</a>
                <a href="<?= e(appUrl('ai.json')) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-code"></i> ai.json</a>
                <a href="<?= e(appUrl('sitemap.xml')) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-sitemap"></i> sitemap.xml</a>
                <a href="<?= e(appUrl('robots.txt')) ?>" target="_blank" class="btn btn-ghost btn-sm"><i class="fa-solid fa-file-shield"></i> robots.txt</a>
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><?= $en ? 'SEO Title (Arabic)' : 'عنوان الموقع في بحث جوجل (عربي)' ?></label>
                <input type="text" name="seo_title_ar" id="set_seo_title_ar" class="form-control">
              </div>
              <div class="form-group">
                <label><?= $en ? 'SEO Title (English)' : 'عنوان الموقع في بحث جوجل (إنجليزي)' ?></label>
                <input type="text" name="seo_title_en" id="set_seo_title_en" class="form-control" style="direction:ltr;">
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><?= $en ? 'SEO Meta Description (Arabic)' : 'وصف الموقع في نتائج جوجل والسوشيال (عربي)' ?></label>
                <textarea name="seo_description_ar" id="set_seo_description_ar" rows="2" class="form-control"></textarea>
              </div>
              <div class="form-group">
                <label><?= $en ? 'SEO Meta Description (English)' : 'وصف الموقع في نتائج جوجل والسوشيال (إنجليزي)' ?></label>
                <textarea name="seo_description_en" id="set_seo_description_en" rows="2" class="form-control" style="direction:ltr;"></textarea>
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><?= $en ? 'AI Recommendation Summary (Arabic — Fed to ChatGPT / Gemini / Perplexity)' : 'الملخص المرجعي لمحركات الذكاء الاصطناعي (عربي — يُقرأ في llms.txt و Schema)' ?></label>
                <textarea name="seo_ai_summary_ar" id="set_seo_ai_summary_ar" rows="3" class="form-control"></textarea>
              </div>
              <div class="form-group">
                <label><?= $en ? 'AI Recommendation Summary (English — Fed to ChatGPT / Gemini / Perplexity)' : 'الملخص المرجعي لمحركات الذكاء الاصطناعي (إنجليزي)' ?></label>
                <textarea name="seo_ai_summary_en" id="set_seo_ai_summary_en" rows="3" class="form-control" style="direction:ltr;"></textarea>
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label><?= $en ? 'SEO Keywords (Arabic)' : 'الكلمات المفتاحية للبحث المحلي (عربي)' ?></label>
                <input type="text" name="seo_keywords_ar" id="set_seo_keywords_ar" class="form-control">
              </div>
              <div class="form-group">
                <label><?= $en ? 'SEO Keywords (English)' : 'الكلمات المفتاحية (إنجليزي)' ?></label>
                <input type="text" name="seo_keywords_en" id="set_seo_keywords_en" class="form-control" style="direction:ltr;">
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Google Search Console Verification Code</label>
                <input type="text" name="google_site_verification" id="set_google_site_verification" class="form-control" placeholder="google-site-verification token" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>Bing Webmaster / Copilot Verification Code</label>
                <input type="text" name="bing_site_verification" id="set_bing_site_verification" class="form-control" placeholder="msvalidate.01 token" style="direction:ltr;">
              </div>
            </div>
          </div>

          
          <div style="background:var(--db-teal-50);padding:20px;border-radius:18px;border:1px solid var(--db-teal-100);margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
              <div>
                <strong style="color:var(--db-teal-800);font-size:0.98rem;"><i class="fa-solid fa-envelope-circle-check"></i> <?= $en ? 'Automatic Repair Stage Email Notifications (SMTP)' : 'إعدادات إشعارات البريد الإلكتروني التلقائية لمراحل الصيانة (SMTP)' ?></strong>
                <div style="font-size:0.8rem;color:var(--db-muted);"><?= $en ? 'Automatically emails registered customers whenever their repair moves to a new stage.' : 'عند تسجيل إيميل العميل بتذكرة الصيانة، يصله بريد بتفاصيل كل مرحلة جديدة تلقائياً.' ?></div>
              </div>
              <select name="smtp_enabled" id="set_smtp_enabled" class="form-control" style="width:auto;font-weight:800;">
                <option value="0"><?= $en ? 'Local Outbox / PHP mail()' : 'وضع التجربة المحلية (حفظ بالسجل / PHP mail)' ?></option>
                <option value="1"><?= $en ? 'Live SMTP Delivery' : 'تفعيل الإرسال الفعلي عبر SMTP' ?></option>
              </select>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" id="set_smtp_host" class="form-control" placeholder="smtp.gmail.com" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>Port</label>
                <input type="number" name="smtp_port" id="set_smtp_port" class="form-control" placeholder="587" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label><?= $en ? 'Encryption' : 'التشفير' ?></label>
                <select name="smtp_encryption" id="set_smtp_encryption" class="form-control">
                  <option value="tls">TLS (587)</option>
                  <option value="ssl">SSL (465)</option>
                  <option value="none">None</option>
                </select>
              </div>
              <div class="form-group">
                <label>SMTP Username</label>
                <input type="text" name="smtp_username" id="set_smtp_username" class="form-control" placeholder="yourstore@gmail.com" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>SMTP App Password</label>
                <input type="password" name="smtp_password" id="set_smtp_password" class="form-control" placeholder="••••••••••••" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>From Email</label>
                <input type="email" name="smtp_from_email" id="set_smtp_from_email" class="form-control" style="direction:ltr;">
              </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center;margin-top:14px;flex-wrap:wrap;">
              <input type="email" id="testEmailRecipient" class="form-control" placeholder="email@domain.com" style="max-width:280px;direction:ltr;">
              <button type="button" class="btn btn-teal btn-sm" id="btnSendTestEmail"><i class="fa-solid fa-paper-plane"></i> <?= $en ? 'Verify SMTP Delivery' : 'فحص اتصال البريد الإلكتروني' ?></button>
            </div>
          </div>

          <button type="submit" class="btn btn-teal" style="padding:13px 32px;font-size:1rem;">
            <i class="fa-solid fa-floppy-disk"></i> <?= $en ? 'Save Social Links & Settings' : 'حفظ روابط السوشيال ميديا والإعدادات' ?>
          </button>
        </form>

        
        <div class="card">
          <div class="card-header">
            <div>
              <h3><i class="fa-solid fa-phone-volume" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Direct Contact Lines & Custom Channels' : 'أرقام التواصل المباشر والقنوات الإضافية' ?></h3>
              <span style="font-size:0.82rem;color:var(--db-muted);"><?= $en ? 'Add or edit direct phone numbers, WhatsApp lines, or extra links shown in the Contact section.' : 'أضف أو عدّل أرقام الهواتف أو خطوط الواتساب الإضافية الظاهرة في قسم تواصل معنا.' ?></span>
            </div>
            <button type="button" class="btn btn-teal btn-sm" id="btnAddChannelModal"><i class="fa-solid fa-plus"></i> <?= $en ? 'Add Line' : 'إضافة خط تواصل' ?></button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th><?= $en ? 'Type' : 'النوع' ?></th>
                  <th><?= $en ? 'Public Label' : 'العنوان الظاهر للعميل' ?></th>
                  <th><?= $en ? 'Value / Number' : 'الرقم / القيمة' ?></th>
                  <th><?= $en ? 'Direct URL' : 'الرابط التلقائي' ?></th>
                  <th><?= $en ? 'Order' : 'الترتيب' ?></th>
                  <th><?= $en ? 'Status' : 'الحالة' ?></th>
                  <th><?= $en ? 'Actions' : 'إجراءات' ?></th>
                </tr>
              </thead>
              <tbody id="channelsTableBody"></tbody>
            </table>
          </div>
        </div>

        
        <div class="card">
          <div class="card-header">
            <div>
              <h3><i class="fa-solid fa-palette" style="color:var(--db-gold-600);margin-inline-end:6px;"></i><?= $en ? 'Official Luminous Brand Identity Kit — ASLY TECH STORE' : 'حزمة الهوية البصرية المحدثة (الفاتحة) — ASLY | أصلي TECH STORE' ?></h3>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;align-items:stretch;">
            <div style="background:var(--db-bg);padding:20px;border-radius:18px;text-align:center;border:1px solid var(--db-border);">
              <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="Transparent Logo" style="height:66px;margin:0 auto 12px;object-fit:contain;">
              <div style="color:var(--db-muted);font-size:0.82rem;margin-bottom:10px;"><?= $en ? 'Transparent PNG Logo' : 'الشعار الرسمي بخلفية شفافة (PNG)' ?></div>
              <a href="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" download="ASLY-Logo-Transparent.png" class="btn btn-teal btn-sm"><i class="fa-solid fa-download"></i> PNG</a>
            </div>
            <div style="background:#ffffff;padding:20px;border-radius:18px;text-align:center;border:1px solid var(--db-border);">
              <img src="<?= e(appUrl('assets/brand/logo-light.svg')) ?>" alt="Vector Logo" style="height:66px;margin:0 auto 12px;object-fit:contain;">
              <div style="color:var(--db-muted);font-size:0.82rem;margin-bottom:10px;"><?= $en ? 'Scalable Vector Logo (SVG)' : 'الشعار المتجه للطباعة (SVG)' ?></div>
              <a href="<?= e(appUrl('assets/brand/logo-light.svg')) ?>" download="ASLY-Logo.svg" class="btn btn-gold btn-sm"><i class="fa-solid fa-download"></i> SVG</a>
            </div>
            <div style="background:#ffffff;padding:20px;border-radius:18px;text-align:center;border:1px solid var(--db-border);">
              <img src="<?= e(appUrl('assets/brand/favicon.svg')) ?>" alt="Icon" style="height:66px;margin:0 auto 12px;object-fit:contain;">
              <div style="color:var(--db-muted);font-size:0.82rem;margin-bottom:10px;"><?= $en ? 'Brand Monogram Icon' : 'أيقونة العلامة (Favicon SVG)' ?></div>
              <a href="<?= e(appUrl('assets/brand/favicon.svg')) ?>" download="ASLY-Icon.svg" class="btn btn-ghost btn-sm"><i class="fa-solid fa-download"></i> Icon</a>
            </div>
          </div>
        </div>

      </section>
      <?php endif; ?>

    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="invoiceModal">
  <div class="db-modal" style="width:min(820px,100%);">
    <div class="db-modal-head">
      <strong><i class="fa-solid fa-file-invoice" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Official Sales Invoice — Download PNG, Share on WhatsApp, or Print' : 'فاتورة مبيعات رسمية — جاهزة للتحميل كصورة أو الإرسال واتساب أو الطباعة' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('invoiceModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body">
      <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-bottom:18px;">
        <button type="button" class="btn btn-teal" id="btnModalInvoicePng"><i class="fa-solid fa-download"></i> <?= $en ? 'Download PNG Image' : 'تحميل الفاتورة كصورة (PNG)' ?></button>
        <button type="button" class="btn btn-emerald" id="btnModalInvoiceWhatsapp"><i class="fa-brands fa-whatsapp"></i> <?= $en ? 'Send via WhatsApp' : 'إرسال الفاتورة للعميل عبر واتساب' ?></button>
        <button type="button" class="btn btn-gold" id="btnModalInvoicePrint"><i class="fa-solid fa-print"></i> <?= $en ? 'Print Invoice' : 'طباعة الفاتورة' ?></button>
      </div>

      <div id="modalInvoiceCapture" style="background:#ffffff;border:1.5px solid #d5f2f4;border-radius:18px;overflow:hidden;"></div>
    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="newMaintModal">
  <div class="db-modal">
    <div class="db-modal-head">
      <strong><i class="fa-solid fa-screwdriver-wrench" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Receive Repair Device & Generate Customer QR Receipt' : 'استلام وتسجيل جهاز صيانة جديد + توليد كود QR للعميل' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('newMaintModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body">
      <form id="newMaintForm">
        <h4 style="margin:0 0 10px;color:var(--db-teal-800);"><i class="fa-solid fa-user"></i> <?= $en ? '1. Customer Contact Details' : 'أولاً: بيانات العميل والتواصل' ?></h4>
        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label><?= $en ? 'Customer Full Name *' : 'اسم العميل الثلاثي *' ?></label>
            <input type="text" name="customer_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label><?= $en ? 'Primary Mobile *' : 'رقم الموبايل الأساسي *' ?></label>
            <input type="text" name="customer_phone" class="form-control" required placeholder="010xxxxxxxx" style="direction:ltr;">
          </div>
          <div class="form-group">
            <label><?= $en ? 'Alt Phone / WhatsApp' : 'رقم هاتف بديل / واتساب' ?></label>
            <input type="text" name="customer_alt_phone" class="form-control" placeholder="011xxxxxxxx" style="direction:ltr;">
          </div>
        </div>

        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label><?= $en ? 'Customer Email (Optional — Auto stage emails!)' : 'البريد الإلكتروني للعميل (اختياري — يصله إيميل تلقائي بكل مرحلة!)' ?></label>
            <input type="email" name="customer_email" class="form-control" placeholder="client@gmail.com" style="direction:ltr;">
          </div>
          <div class="form-group">
            <label><?= $en ? 'Address / Notes' : 'تفاصيل إضافية عن العميل / العنوان' ?></label>
            <input type="text" name="customer_details" class="form-control">
          </div>
        </div>

        <h4 style="margin:0 0 10px;color:var(--db-teal-800);padding-top:10px;border-top:1px solid var(--db-border);"><i class="fa-solid fa-laptop-medical"></i> <?= $en ? '2. Device Specs, Issue & Estimate' : 'ثانياً: مواصفات الجهاز والعطل والتكلفة' ?></h4>
        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label><?= $en ? 'Device Type *' : 'نوع الجهاز *' ?></label>
            <select name="device_type" class="form-control">
              <option value="laptop">Laptop • لابتوب</option>
              <option value="mobile">Smartphone • موبايل</option>
              <option value="wearable">Smart Watch • ساعة ذكية</option>
              <option value="other">Other • جهاز آخر</option>
            </select>
          </div>
          <div class="form-group">
            <label><?= $en ? 'Brand & Model *' : 'الماركة والموديل *' ?></label>
            <input type="text" name="device_model" class="form-control" required placeholder="HP ZBook G7 / iPhone 14 Pro">
          </div>
          <div class="form-group">
            <label>Serial / IMEI</label>
            <input type="text" name="serial_imei" class="form-control" style="direction:ltr;">
          </div>
        </div>

        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label><?= $en ? 'Device Specs' : 'مواصفات الجهاز (رام، مساحة، لون...)' ?></label>
            <input type="text" name="device_specs" class="form-control">
          </div>
          <div class="form-group">
            <label><?= $en ? 'Passcode (Wiped on delivery)' : 'باسورد / رمز قفل الجهاز للفحص' ?></label>
            <input type="text" name="device_passcode" class="form-control">
          </div>
          <div class="form-group">
            <label><?= $en ? 'Physical Condition & Accessories' : 'الحالة الظاهرية والملحقات المستلمة' ?></label>
            <input type="text" name="physical_condition" class="form-control">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:14px;">
          <label><?= $en ? 'Problem Description *' : 'وصف العطل المطلوب إصلاحه بالتفصيل *' ?></label>
          <textarea name="problem_description" rows="2" class="form-control" required></textarea>
        </div>

        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label><?= $en ? 'Estimated Cost' : 'التكلفة المبدئية المتوقعة' ?></label>
            <input type="number" step="0.01" min="0" name="estimated_cost" class="form-control" value="0">
          </div>
          <?php if ($isAdmin): ?>
          <div class="form-group">
            <label><?= $en ? 'Est. Parts Cost (Admin)' : 'تكلفة قطع الغيار المبدئية (للأدمن)' ?></label>
            <input type="number" step="0.01" min="0" name="parts_cost" class="form-control" value="0">
          </div>
          <?php endif; ?>
          <div class="form-group">
            <label><?= $en ? 'Deposit Paid' : 'العربون المدفوع عند الاستلام' ?></label>
            <input type="number" step="0.01" min="0" name="deposit_paid" class="form-control" value="0">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:18px;">
          <label><?= $en ? 'Initial Stage Note (Visible on Customer QR)' : 'ملاحظة المرحلة الأولى (تظهر للعميل عند مسح الـ QR)' ?></label>
          <input type="text" name="initial_note" class="form-control" value="<?= $en ? 'Device received and logged at ASLY Tech Store diagnostic lab.' : 'تم استلام الجهاز وتسجيل بياناته بمركز أصلي تك ستور لبدء الفحص الفني.' ?>">
        </div>

        <button type="submit" class="btn btn-teal" style="width:100%;padding:13px;font-size:1rem;">
          <i class="fa-solid fa-qrcode"></i> <?= $en ? 'Register Ticket & Generate Customer QR' : 'تسجيل الجهاز وتوليد إيصال الـ QR للعميل' ?>
        </button>
      </form>
    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="maintDetailModal">
  <div class="db-modal" style="width:min(920px,100%);">
    <div class="db-modal-head">
      <strong id="mdModalTitle"><?= $en ? 'Repair Ticket Profile' : 'ملف تذكرة الصيانة' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('maintDetailModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body" id="maintDetailBody"></div>
  </div>
</div>

<?php if ($isAdmin): ?>

<div class="db-modal-backdrop" id="stickerModal">
  <div class="db-modal" style="width:min(440px,100%);">
    <div class="db-modal-head">
      <strong><i class="fa-solid fa-tag" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Print Product QR / Barcode Sticker' : 'طباعة استيكر QR / باركود للمنتج' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('stickerModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body" style="text-align:center;">
      <div id="modalStickerArea" class="sticker-preview-box" style="margin-bottom:18px;">
        <strong style="font-size:0.9rem;color:#0d9499;">ASLY | أصلي TECH STORE</strong>
        <div id="modalStickerName" style="font-size:0.82rem;color:#334155;font-weight:700;"></div>
        <div id="modalStickerQr" style="margin:6px auto;"></div>
        <svg id="modalStickerBarcode" style="max-width:220px;height:52px;"></svg>
        <div id="modalStickerCode" style="font-family:monospace;font-weight:900;font-size:0.88rem;"></div>
      </div>
      <div>
        <button type="button" class="btn btn-teal" id="btnPrintModalSticker" style="width:100%;"><i class="fa-solid fa-print"></i> <?= $en ? 'Print Sticker Now' : 'طباعة الاستيكر الآن' ?></button>
      </div>
    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="userModal">
  <div class="db-modal" style="width:min(500px,100%);">
    <div class="db-modal-head">
      <strong id="userModalTitle"><?= $en ? 'Manage Staff / Admin Account' : 'إدارة حساب موظف / مدير' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('userModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body">
      <form id="userSaveForm">
        <input type="hidden" name="id" id="uFormId" value="0">
        <div class="form-group" style="margin-bottom:12px;">
          <label><?= $en ? 'Full Name *' : 'الاسم الكامل *' ?></label>
          <input type="text" name="full_name" id="uFormFullName" class="form-control" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label><?= $en ? 'Username *' : 'اسم المستخدم للدخول *' ?></label>
          <input type="text" name="username" id="uFormUsername" class="form-control" required style="direction:ltr;">
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label><?= $en ? 'Password' : 'كلمة المرور' ?> <span id="uPassHint" style="font-weight:400;color:var(--db-muted);"></span></label>
          <input type="password" name="password" id="uFormPassword" class="form-control" style="direction:ltr;">
        </div>
        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label><?= $en ? 'Role *' : 'الصلاحية *' ?></label>
            <select name="role" id="uFormRole" class="form-control">
              <option value="staff"><?= $en ? 'Sales & Repair Staff (No Buy Cost)' : 'موظف مبيعات وصيانة (Staff - بدون سعر الشراء)' ?></option>
              <option value="admin"><?= $en ? 'Full Administrator (Admin)' : 'مدير كامل الصلاحيات (Admin)' ?></option>
            </select>
          </div>
          <div class="form-group">
            <label><?= $en ? 'Phone' : 'رقم الهاتف' ?></label>
            <input type="text" name="phone" id="uFormPhone" class="form-control" style="direction:ltr;">
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-weight:700;">
          <input type="checkbox" name="is_active" id="uFormActive" value="1" checked> <?= $en ? 'Account Active' : 'الحساب نشط ومسموح له بالدخول' ?>
        </label>
        <button type="submit" class="btn btn-teal" style="width:100%;"><i class="fa-solid fa-floppy-disk"></i> <?= $en ? 'Save Account' : 'حفظ الحساب' ?></button>
      </form>
    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="channelModal">
  <div class="db-modal" style="width:min(520px,100%);">
    <div class="db-modal-head">
      <strong><i class="fa-solid fa-phone-volume" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Add / Edit Direct Contact Channel' : 'إضافة / تعديل خط تواصل بالموقع' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('channelModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body">
      <form id="channelSaveForm">
        <input type="hidden" name="id" id="chFormId" value="0">
        <div class="form-grid" style="margin-bottom:12px;">
          <div class="form-group">
            <label><?= $en ? 'Channel Type *' : 'نوع الوسيلة *' ?></label>
            <select name="channel_type" id="chFormType" class="form-control">
              <option value="phone">Phone • اتصال هاتفي</option>
              <option value="whatsapp">WhatsApp • واتساب</option>
              <option value="facebook">Facebook • فيسبوك</option>
              <option value="instagram">Instagram • إنستجرام</option>
              <option value="tiktok">TikTok • تيك توك</option>
              <option value="telegram">Telegram • تيليجرام</option>
              <option value="youtube">YouTube • يوتيوب</option>
              <option value="email">Email • بريد إلكتروني</option>
              <option value="location">Location • موقع</option>
            </select>
          </div>
          <div class="form-group">
            <label><?= $en ? 'Display Order' : 'ترتيب الظهور' ?></label>
            <input type="number" name="sort_order" id="chFormSort" class="form-control" value="1">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label><?= $en ? 'Public Label *' : 'العنوان الظاهر للعميل *' ?></label>
          <input type="text" name="label" id="chFormLabel" class="form-control" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label><?= $en ? 'Phone Number / Handle *' : 'الرقم أو اسم الحساب الظاهر *' ?></label>
          <input type="text" name="value" id="chFormValue" class="form-control" required style="direction:ltr;">
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label><?= $en ? 'Direct URL (Leave empty to auto-generate tel/wa.me)' : 'الرابط المباشر عند الضغط (اتركه فارغاً للتوليد التلقائي)' ?></label>
          <input type="text" name="url" id="chFormUrl" class="form-control" style="direction:ltr;">
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-weight:700;">
          <input type="checkbox" name="is_active" id="chFormActive" value="1" checked> <?= $en ? 'Visible on Public Website' : 'إظهار في الصفحة الرئيسية للعملاء' ?>
        </label>
        <button type="submit" class="btn btn-teal" style="width:100%;"><i class="fa-solid fa-floppy-disk"></i> <?= $en ? 'Save Channel' : 'حفظ وسيلة التواصل' ?></button>
      </form>
    </div>
  </div>
</div>

<div class="db-modal-backdrop" id="emailLogsModal">
  <div class="db-modal" style="width:min(860px,100%);">
    <div class="db-modal-head">
      <strong><i class="fa-solid fa-inbox" style="color:var(--db-teal-700);margin-inline-end:6px;"></i><?= $en ? 'Sent Customer Email Notifications Log' : 'سجل إشعارات البريد الإلكتروني المرسلة للعملاء' ?></strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('emailLogsModal')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="db-modal-body" id="emailLogsBody"></div>
  </div>
</div>
<?php endif; ?>

<div class="toast-container" id="toastContainer"></div>

<script>window.ASLY_CFG = <?= json_encode($jsConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e(appUrl('assets/vendor/qrcode.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/qrcode_UTF8.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/JsBarcode.all.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/zbar-wasm.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/html5-qrcode.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/js/turbo-scanner.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/html2canvas.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/js/dashboard.js')) ?>"></script>
</body>
</html>
