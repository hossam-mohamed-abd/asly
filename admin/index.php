<?php
/**
 * ASLY | أصلي TECH STORE
 * Complete Unified Admin & Staff Dashboard
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = requireLoginPage();
$isAdmin = ($user['role'] === 'admin');
$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$stages = getMaintenanceStages();
$catsMeta = getCategoryMeta();

// Quick server-side counts for badges & admin inventory capital
$unreadMaintNotes = (int)$pdo->query("SELECT COALESCE(SUM(unread_customer_notes), 0) FROM maintenance_tickets")->fetchColumn();
$activeMaintCount = (int)$pdo->query("SELECT COUNT(*) FROM maintenance_tickets")->fetchColumn();
$lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= low_stock_threshold")->fetchColumn();

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

$jsConfig = [
    'baseUrl' => appBasePath(),
    'csrfToken' => csrfToken(),
    'user' => $user,
    'isAdmin' => $isAdmin,
    'inventoryStats' => $inventoryStats,
    'lowStockCount' => $lowStockCount,
    'stages' => $stages,
    'categories' => $catsMeta,
    'store' => [
        'nameAr' => $storeNameAr,
        'nameEn' => $storeNameEn,
        'address' => $settings['store_address'] ?: 'سوهاج - جمهورية مصر العربية',
        'primaryPhone' => $settings['primary_phone'] ?: '01015981677',
        'secondaryPhone' => $settings['secondary_phone'] ?: '01154639718',
        'primaryWhatsapp' => normalizeWhatsappNumber($settings['primary_whatsapp'] ?: ($settings['primary_phone'] ?: '01015981677')),
        'footerNote' => $settings['invoice_footer_note'] ?: 'جميع الأجهزة تخضع لضمان الفحص والتجربة المعتمد من مركز أصلي ASLY TECH STORE.',
        'logoDarkUrl' => appUrl('assets/brand/logo-dark.svg'),
        'logoLightUrl' => appUrl('assets/brand/logo-light.svg'),
    ],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة التحكم الذكية | <?= e($storeNameAr) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/dashboard.css')) ?>">
</head>
<body>

<div class="app-layout">

  <!-- Luxury Dark-Emerald & Gold Sidebar -->
  <aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
      <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي">
      <span class="role-pill <?= $isAdmin ? 'role-admin' : 'role-staff' ?>">
        <?= $isAdmin ? '👑 مدير النظام (Admin)' : '💼 موظف مبيعات (Staff)' ?>
      </span>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">التشغيل اليومي والمبيعات</div>

      <button type="button" class="side-btn active" data-tab="overview">
        <span class="side-btn-left">📊 <?= $isAdmin ? 'المبيعات اليومية والأرباح' : 'ملخص المبيعات اليومية' ?></span>
      </button>

      <button type="button" class="side-btn" data-tab="quick-scanner">
        <span class="side-btn-left">📷 كاشف السعر السريع</span>
        <span style="font-size:.7rem;color:var(--db-gold-400);">أمام العميل</span>
      </button>

      <button type="button" class="side-btn" data-tab="pos">
        <span class="side-btn-left">🛒 نقطة البيع والفواتير</span>
        <span id="posCartBadge" class="badge-alert" style="display:none;background:var(--db-gold-500);color:#032224;">0</span>
      </button>

      <div class="nav-section-label">المخزون والباركود / QR</div>

      <?php if ($isAdmin): ?>
        <button type="button" class="side-btn" data-tab="add-product">
          <span class="side-btn-left">➕ إضافة منتج (اسكان / QR)</span>
        </button>
      <?php endif; ?>

      <button type="button" class="side-btn" data-tab="inventory">
        <span class="side-btn-left">📦 المخزون والمنتجات</span>
        <?php if ($lowStockCount > 0): ?>
          <span class="badge-alert" title="منتجات قاربت على النفاد"><?= $lowStockCount ?></span>
        <?php endif; ?>
      </button>

      <div class="nav-section-label">قسم الصيانة التفاعلي</div>

      <button type="button" class="side-btn" data-tab="maintenance">
        <span class="side-btn-left">🛠️ الصيانة وتتبع الـ QR</span>
        <span id="maintNavBadge" class="badge-alert" style="display:<?= $unreadMaintNotes > 0 ? 'inline-block' : 'none' ?>;">
          <?= $unreadMaintNotes ?> جديد
        </span>
      </button>

      <?php if ($isAdmin): ?>
        <div class="nav-section-label">الإدارة والهوية</div>

        <button type="button" class="side-btn" data-tab="staff">
          <span class="side-btn-left">👥 الموظفون والصلاحيات</span>
        </button>

        <button type="button" class="side-btn" data-tab="settings">
          <span class="side-btn-left">⚙️ الهوية وقنوات التواصل</span>
        </button>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <div>
        <strong style="display:block;color:#fff;"><?= e($user['full_name']) ?></strong>
        <span style="font-size:.75rem;color:#9cc2c5;">@<?= e($user['username']) ?></span>
      </div>
      <a href="<?= e(appUrl('admin/logout.php')) ?>" class="btn btn-danger btn-sm">خروج</a>
    </div>
  </aside>

  <!-- Main Workspace -->
  <div class="main-area">

    <!-- Top Header -->
    <header class="topbar-admin">
      <div class="topbar-title-wrap">
        <button type="button" class="mobile-menu-btn" id="btnMobileSidebar">☰</button>
        <div>
          <h2 id="activePageTitle" style="margin:0;font-size:1.22rem;color:var(--db-teal-900);">
            <?= $isAdmin ? 'لوحة المبيعات اليومية والأرباح' : 'ملخص المبيعات والتشغيل اليومي' ?>
          </h2>
          <span style="font-size:.78rem;color:var(--db-muted);"><?= e($storeNameAr) ?> • <?= date('Y-m-d') ?></span>
        </div>
      </div>

      <div class="quick-bar-btns">
        <button type="button" class="btn btn-gold btn-sm" onclick="switchTab('quick-scanner')">📷 فحص سعر بالكاميرا</button>
        <button type="button" class="btn btn-teal btn-sm" onclick="switchTab('pos')">🧾 فاتورة بيع جديدة</button>
        <a href="<?= e(appUrl('index.php')) ?>" target="_blank" class="btn btn-ghost btn-sm">🌐 اللاندينج بيدج</a>
      </div>
    </header>

    <div class="content-wrap">

      <!-- ========================================================
           TAB 1: OVERVIEW & DAILY SALES / REVENUE DASHBOARD
           ======================================================== -->
      <section class="tab-panel active" id="tab-overview">
        <div class="card" style="padding:16px 22px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <strong style="color:var(--db-teal-900);">عرض حسابات ومبيعات:</strong>
              <button type="button" class="btn btn-teal btn-sm js-date-preset" data-preset="today">اليوم</button>
              <button type="button" class="btn btn-ghost btn-sm js-date-preset" data-preset="yesterday">أمس</button>
              <button type="button" class="btn btn-ghost btn-sm js-date-preset" data-preset="month">هذا الشهر</button>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <label style="font-size:.82rem;font-weight:700;">من:</label>
              <input type="date" id="repDateFrom" class="form-control" style="width:auto;padding:6px 10px;" value="<?= date('Y-m-d') ?>">
              <label style="font-size:.82rem;font-weight:700;">إلى:</label>
              <input type="date" id="repDateTo" class="form-control" style="width:auto;padding:6px 10px;" value="<?= date('Y-m-d') ?>">
              <button type="button" class="btn btn-gold btn-sm" id="btnLoadReport">تحديث الجرد</button>
            </div>
          </div>
        </div>

        <div class="kpi-grid" id="overviewKpis"></div>

        <!-- Daily Sold Invoices Table -->
        <div class="card">
          <div class="card-header">
            <h3>🧾 سجل الفواتير والقطع المباعة خلال الفترة المحددة</h3>
            <button type="button" class="btn btn-gold btn-sm" onclick="switchTab('pos')">+ إصدار فاتورة جديدة</button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>رقم الفاتورة</th>
                  <th>التوقيت</th>
                  <th>العميل</th>
                  <th>الأصناف المباعة</th>
                  <th>البائع</th>
                  <th>الإجمالي المحصل</th>
                  <?php if ($isAdmin): ?>
                    <th>تكلفة الشراء</th>
                    <th>صافي الربح</th>
                  <?php endif; ?>
                  <th>الفاتورة</th>
                </tr>
              </thead>
              <tbody id="dailyInvoicesBody">
                <tr><td colspan="<?= $isAdmin ? 9 : 7 ?>" style="text-align:center;padding:24px;">جاري تحميل بيانات المبيعات...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <?php if ($isAdmin): ?>
          <!-- Delivered Maintenance Revenues Table (Only Financial Figures Kept!) -->
          <div class="card">
            <div class="card-header">
              <div>
                <h3>🛠️ إيرادات أجهزة الصيانة المسلمة (بعد الحذف التلقائي لبيانات الأجهزة)</h3>
                <span style="font-size:.8rem;color:var(--db-muted);">يتم الاحتفاظ بالمبالغ المحصلة فقط لحساب إيراد وأرباح اليوم بعد مسح بيانات الجهاز والعميل فور التسليم.</span>
              </div>
              <span style="padding:5px 12px;border-radius:999px;background:var(--db-teal-50);color:var(--db-teal-900);font-size:.8rem;font-weight:800;">حماية خصوصية مفعّلة ✓</span>
            </div>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>كود التذكرة المرجعي</th>
                    <th>التاريخ</th>
                    <th>فئة الجهاز</th>
                    <th>المبلغ المحصل</th>
                    <th>تكلفة قطع الغيار</th>
                    <th>صافي ربح الصيانة</th>
                    <th>طريقة الدفع</th>
                    <th>المُسلّم</th>
                  </tr>
                </thead>
                <tbody id="dailyMaintRevBody">
                  <tr><td colspan="8" style="text-align:center;padding:20px;">لا توجد تذاكر مسلمة في هذه الفترة بعد.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </section>

      <!-- ========================================================
           TAB 2: QUICK CAMERA PRICE CHECKER (PART 2 - IN FRONT OF CUSTOMER)
           ======================================================== -->
      <section class="tab-panel" id="tab-quick-scanner">
        <div class="scanner-grid">

          <!-- Camera Box -->
          <div class="camera-viewport-box">
            <h3 style="margin:0 0 6px;color:var(--db-gold-400);">📷 اسكان الباركود المطاول أو رمز الـ QR</h3>
            <p style="margin:0 0 14px;font-size:.83rem;color:#c9e2e4;">وجّه الكاميرا نحو الباركود الطولي أو الـ QR الموجود على الجهاز لعرض سعر البيع فوراً</p>

            <div id="quickCameraReader" class="reader-mount">
              <div style="padding:24px;color:#9bc0c3;font-size:.9rem;">اضغط «تشغيل الكاميرا» لبدء المسح المباشر أو استخدم قارئ الباركود</div>
            </div>

            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:14px;">
              <button type="button" class="btn btn-gold btn-sm" id="btnStartQuickCam">▶ تشغيل الكاميرا</button>
              <button type="button" class="btn btn-danger btn-sm" id="btnStopQuickCam" style="display:none;">⏹ إيقاف الكاميرا</button>
              <label class="btn btn-ghost btn-sm" style="cursor:pointer;margin:0;">
                🖼️ مسح من صورة باركود
                <input type="file" id="quickScanFile" accept="image/*" style="display:none;">
              </label>
            </div>

            <form id="quickManualForm" style="display:flex;gap:8px;">
              <input type="text" id="quickCodeInput" class="form-control" placeholder="أو امسح بمسدس الباركود / اكتب الكود هنا..." style="direction:ltr;text-align:center;font-weight:800;">
              <button type="submit" class="btn btn-teal">فحص</button>
            </form>
          </div>

          <!-- Customer-Facing Price Result Card -->
          <div id="quickScanResult">
            <div class="card" style="text-align:center;padding:48px 24px;">
              <div style="font-size:3rem;margin-bottom:10px;">🔍</div>
              <h3 style="margin:0 0 8px;color:var(--db-teal-900);">في انتظار مسح كود القطعة</h3>
              <p style="margin:0;color:var(--db-muted);font-size:.9rem;">
                بمجرد مسح الباركود أو الـ QR سيظهر <strong>سعر البيع فقط</strong> بخط واضح، مع زر <strong>«اعرض سعر الشراء»</strong> الذي يعرض <strong>أقل سعر بيع مسموح</strong> بأمان أمام العميل.
              </p>
            </div>
          </div>

        </div>
      </section>

      <!-- ========================================================
           TAB 3: POS MULTI-ITEM SALES & INVOICE GENERATOR
           ======================================================== -->
      <section class="tab-panel" id="tab-pos">
        <div class="pos-layout">

          <!-- Left: Catalog & Scan into Cart -->
          <div class="card">
            <div class="card-header">
              <h3>📦 اختر المنتجات أو امسح الكود للإضافة للفاتورة</h3>
              <button type="button" class="btn btn-gold btn-sm" id="btnPosToggleCam">📷 مسح بالكاميرا للفاتورة</button>
            </div>

            <div id="posCameraWrap" style="display:none;margin-bottom:16px;" class="camera-viewport-box">
              <div id="posCameraReader" class="reader-mount" style="min-height:210px;"></div>
              <button type="button" class="btn btn-danger btn-sm" id="btnPosCloseCam">إغلاق الكاميرا</button>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
              <input type="text" id="posSearchInput" class="form-control" placeholder="ابحث بالاسم أو امسح الباركود واضغط Enter..." style="flex:1;min-width:200px;">
              <select id="posCatFilter" class="form-control" style="width:auto;">
                <option value="all">كل الأقسام</option>
                <option value="laptops">لابتوب</option>
                <option value="mobiles">هواتف</option>
                <option value="accessories">إكسسوارات</option>
                <option value="wearables">أجهزة قابلة للارتداء</option>
              </select>
            </div>

            <div class="pos-products-grid" id="posProductsGrid"></div>
          </div>

          <!-- Right: Current Invoice Cart -->
          <div class="card" style="border:2px solid var(--db-teal-800);">
            <div class="card-header">
              <h3>🧾 فاتورة البيع الحالية</h3>
              <button type="button" class="btn btn-danger btn-sm" id="btnClearCart">إفراغ السلة</button>
            </div>

            <div class="form-grid" style="margin-bottom:14px;">
              <div class="form-group">
                <label>اسم العميل</label>
                <input type="text" id="posCustomerName" class="form-control" placeholder="عميل نقدي / اسم المشتري">
              </div>
              <div class="form-group">
                <label>رقم واتساب / هاتف العميل (لإرسال الفاتورة)</label>
                <input type="text" id="posCustomerPhone" class="form-control" placeholder="010xxxxxxxx" style="direction:ltr;">
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group">
                <label>طريقة الدفع</label>
                <select id="posPaymentMethod" class="form-control">
                  <option value="cash">نقدي (كاش بالدرج)</option>
                  <option value="vodafone_cash">فودافون كاش / محفظة</option>
                  <option value="instapay">إنستا باي (InstaPay)</option>
                  <option value="card">بطاقة بنكية</option>
                </select>
              </div>
              <div class="form-group">
                <label>ملاحظات إضافية بالفاتورة</label>
                <input type="text" id="posInvoiceNotes" class="form-control" placeholder="مثال: شامل شنطة وماوس هدية">
              </div>
            </div>

            <div id="posCartItemsList"></div>

            <div style="background:linear-gradient(135deg,var(--db-teal-900),var(--db-teal-800));color:#fff;padding:16px 20px;border-radius:14px;display:flex;justify-content:space-between;align-items:center;margin:16px 0;border:1.5px solid var(--db-gold-500);">
              <span style="font-weight:800;color:var(--db-gold-400);">إجمالي الفاتورة المطلوب:</span>
              <strong id="posTotalDisplay" style="font-size:1.5rem;">0.00 ج.م</strong>
            </div>

            <button type="button" class="btn btn-gold" id="btnCompleteSale" style="width:100%;padding:14px;font-size:1rem;">
              ✅ إتمام البيع وإصدار الفاتورة (صورة + واتساب + طباعة)
            </button>
          </div>

        </div>
      </section>

      <?php if ($isAdmin): ?>
      <!-- ========================================================
           TAB 4: ADD / EDIT PRODUCT WITH SCANNER OR AUTO-QR (ADMIN ONLY)
           ======================================================== -->
      <section class="tab-panel" id="tab-add-product">
        <div class="scanner-grid">

          <!-- Left: Scan Box Barcode OR Auto-Generate Printable QR -->
          <div>
            <div class="camera-viewport-box" style="margin-bottom:20px;">
              <h3 style="margin:0 0 6px;color:var(--db-gold-400);">1️⃣ مسح كود العلبة أو توليد رمز QR جديد</h3>
              <p style="margin:0 0 12px;font-size:.82rem;color:#c9e2e4;">يمكنك مسح الباركود المطاول الموجود على العلبة بالكاميرا، أو الضغط على «توليد QR تلقائي» للأجهزة المستعملة والإكسسوارات بدون علبة.</p>

              <div id="addProdCameraReader" class="reader-mount" style="min-height:230px;">
                <div style="padding:20px;color:#9bc0c3;font-size:.88rem;">الكاميرا متوقفة — اضغط تشغيل لمسح باركود المنتج أو ولّد رمز QR مباشرة</div>
              </div>

              <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <button type="button" class="btn btn-teal btn-sm" id="btnStartAddCam">📷 مسح باركود العلبة بالكاميرا</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnStopAddCam" style="display:none;">⏹ إيقاف</button>
                <button type="button" class="btn btn-gold btn-sm" id="btnGenerateAutoQr">✨ توليد QR تلقائي للمنتج</button>
              </div>
            </div>

            <!-- Live Printable Sticker Preview -->
            <div class="card" style="text-align:center;">
              <div class="card-header">
                <h3>🏷️ معاينة استيكر الكود الجاهز للطباعة واللصق</h3>
                <button type="button" class="btn btn-teal btn-sm" id="btnPrintCurrentSticker">🖨️ طباعة الاستيكر</button>
              </div>
              <div class="sticker-preview-box" id="liveStickerBox">
                <strong style="font-size:.84rem;color:#043538;">ASLY | أصلي TECH STORE</strong>
                <div id="stickerProdName" style="font-size:.78rem;color:#475569;max-width:210px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">منتج جديد</div>
                <div id="stickerQrMount" style="margin:4px auto;"></div>
                <svg id="stickerBarcodeSvg" style="max-width:210px;height:48px;"></svg>
                <div id="stickerCodeText" style="font-family:monospace;font-weight:800;font-size:.82rem;letter-spacing:1px;">—</div>
              </div>
            </div>
          </div>

          <!-- Right: Product Details & 3-Tier Pricing Form -->
          <div class="card">
            <div class="card-header">
              <h3 id="productFormHeading">➕ تسجيل بيانات المنتج والأسعار الثلاثة</h3>
              <button type="button" class="btn btn-ghost btn-sm" id="btnResetProductForm">تفريغ النموذج لمنتج جديد</button>
            </div>

            <div id="existingProdAlert" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:12px;background:var(--db-gold-50);border:1.5px solid var(--db-gold-500);color:#043538;font-weight:700;font-size:.88rem;"></div>

            <form id="productSaveForm" enctype="multipart/form-data">
              <input type="hidden" name="id" id="prodId" value="0">

              <div class="form-grid" style="margin-bottom:14px;">
                <div class="form-group">
                  <label>الباركود المطاول / كود الـ QR *</label>
                  <input type="text" name="barcode" id="prodBarcode" class="form-control" placeholder="امسح الكود أو اضغط توليد QR تلقائي" style="direction:ltr;font-weight:800;">
                </div>
                <div class="form-group">
                  <label>نوع الكود</label>
                  <select name="code_type" id="prodCodeType" class="form-control">
                    <option value="qr">QR Code (مولّد أو ممسوح)</option>
                    <option value="barcode">باركود مطاول 1D (علبة أصلية)</option>
                  </select>
                </div>
              </div>

              <div class="form-group" style="margin-bottom:14px;">
                <label>اسم وموديل المنتج *</label>
                <input type="text" name="name" id="prodName" class="form-control" placeholder="مثال: Dell XPS 15 9520 Core i7 32GB RTX 3050 Ti" required>
              </div>

              <div class="form-grid" style="margin-bottom:14px;">
                <div class="form-group">
                  <label>القسم *</label>
                  <select name="category" id="prodCategory" class="form-control">
                    <option value="laptops">لابتوب (Laptops)</option>
                    <option value="mobiles">هواتف ذكية (Mobiles)</option>
                    <option value="accessories">إكسسوارات (Accessories)</option>
                    <option value="wearables">أجهزة قابلة للارتداء (Wearables)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>حالة المنتج *</label>
                  <select name="condition_status" id="prodCondition" class="form-control">
                    <option value="new">جديد متبرشم</option>
                    <option value="open_box">كسر زيرو</option>
                    <option value="used">مستعمل فرز أول</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>الماركة (Brand)</label>
                  <input type="text" name="brand" id="prodBrand" class="form-control" placeholder="Apple / Dell / HP / Samsung">
                </div>
              </div>

              <!-- 3-Tier Pricing Highlight Box -->
              <div style="background:linear-gradient(135deg,#043538,#074f53);padding:18px;border-radius:16px;border:1.5px solid var(--db-gold-500);color:#fff;margin-bottom:16px;">
                <div style="font-weight:800;color:var(--db-gold-400);margin-bottom:10px;font-size:.92rem;">
                  💰 نظام التسعير الثلاثي الذكي (سري ومحمي بالصلاحيات)
                </div>
                <div class="form-grid">
                  <div class="form-group">
                    <label style="color:#fde68a;">1. سعر الشراء الحقيقي (للمدير فقط) *</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" id="prodPurchasePrice" class="form-control" placeholder="0.00" required>
                  </div>
                  <div class="form-group">
                    <label style="color:#ffffff;">2. سعر البيع الأساسي (يظهر بالاسكان) *</label>
                    <input type="number" step="0.01" min="0" name="sell_price" id="prodSellPrice" class="form-control" placeholder="0.00" required>
                  </div>
                  <div class="form-group">
                    <label style="color:#6ee7b7;">3. أقل سعر بيع (يظهر بزر سعر الشراء) *</label>
                    <input type="number" step="0.01" min="0" name="min_sell_price" id="prodMinSellPrice" class="form-control" placeholder="0.00" required>
                  </div>
                </div>
                <div id="liveProfitHint" style="margin-top:10px;font-size:.82rem;color:#d5ecee;"></div>
              </div>

              <div class="form-grid" style="margin-bottom:14px;">
                <div class="form-group">
                  <label>الكمية المتوفرة بالمخزن *</label>
                  <input type="number" min="0" name="quantity" id="prodQuantity" class="form-control" value="1" required>
                </div>
                <div class="form-group">
                  <label>حد التنبيه بالنواقص</label>
                  <input type="number" min="0" name="low_stock_threshold" id="prodLowStock" class="form-control" value="2">
                </div>
                <div class="form-group">
                  <label>فترة ونوع الضمان الافتراضي</label>
                  <input type="text" name="warranty_default" id="prodWarranty" class="form-control" value="ضمان الفحص والتجربة - أصلي تك ستور">
                </div>
              </div>

              <div class="form-group" style="margin-bottom:14px;">
                <label>المواصفات الفنية (تظهر للزوار بدون أسعار)</label>
                <input type="text" name="specs" id="prodSpecs" class="form-control" placeholder="مثال: Core i7-12700H • RAM 16GB DDR5 • SSD 512GB • RTX 3060">
              </div>

              <div class="form-group" style="margin-bottom:14px;">
                <label>وصف إضافي للمنتج</label>
                <textarea name="description" id="prodDescription" rows="2" class="form-control" placeholder="تفاصيل الحالة والملحقات المرفقة..."></textarea>
              </div>

              <div class="form-grid" style="margin-bottom:18px;align-items:center;">
                <div class="form-group">
                  <label>صورة المنتج (JPG / PNG / WebP)</label>
                  <input type="file" name="image" id="prodImageInput" accept="image/*" class="form-control">
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;padding-top:14px;">
                  <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;font-size:.88rem;">
                    <input type="checkbox" name="show_on_landing" id="prodShowLanding" value="1" checked>
                    عرض المنتج في اللاندينج بيدج للزوار (بدون سعر)
                  </label>
                  <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;font-size:.88rem;">
                    <input type="checkbox" name="is_featured" id="prodFeatured" value="1">
                    تمييز المنتج في مقدمة المعرض (Featured)
                  </label>
                </div>
              </div>

              <button type="submit" class="btn btn-gold" style="width:100%;padding:13px;font-size:1rem;">
                💾 حفظ المنتج وتحديث المخزن والكود
              </button>
            </form>
          </div>

        </div>
      </section>
      <?php endif; ?>

      <!-- ========================================================
           TAB 5: INVENTORY & PRODUCTS TABLE
           ======================================================== -->
      <section class="tab-panel" id="tab-inventory">
        <div class="card">
          <div class="card-header">
            <h3>📦 سجل المخزون والأجهزة والإكسسوارات</h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <input type="text" id="invSearch" class="form-control" placeholder="بحث بالاسم أو الكود أو الماركة..." style="width:240px;">
              <select id="invCategory" class="form-control" style="width:auto;">
                <option value="all">كل الأقسام</option>
                <option value="laptops">لابتوب</option>
                <option value="mobiles">هواتف</option>
                <option value="accessories">إكسسوارات</option>
                <option value="wearables">أجهزة قابلة للارتداء</option>
              </select>
              <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-gold btn-sm" onclick="switchTab('add-product')">+ إضافة منتج جديد</button>
              <?php endif; ?>
            </div>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>الصورة</th>
                  <th>المنتج والمواصفات</th>
                  <th>الكود (QR/Barcode)</th>
                  <th>الكمية</th>
                  <?php if ($isAdmin): ?>
                    <th>سعر الشراء (سري)</th>
                  <?php endif; ?>
                  <th>سعر البيع</th>
                  <th>أقل سعر مسموح</th>
                  <?php if ($isAdmin): ?>
                    <th>اللاندينج</th>
                  <?php endif; ?>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="inventoryTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ========================================================
           TAB 6: INTERACTIVE QR MAINTENANCE SYSTEM
           ======================================================== -->
      <section class="tab-panel" id="tab-maintenance">
        <div class="card">
          <div class="card-header">
            <div>
              <h3>🛠️ إدارة أجهزة الصيانة النشطة وتتبع المراحل بالـ QR</h3>
              <span style="font-size:.82rem;color:var(--db-muted);">كل جهاز يُولّد له QR خاص للعميل، مع إشعار بريد إلكتروني بكل مرحلة، وحذف كامل للبيانات فور التسليم مع حفظ الإيراد فقط.</span>
            </div>
            <button type="button" class="btn btn-gold" id="btnOpenNewMaint">+ استلام جهاز صيانة جديد</button>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            <input type="text" id="maintSearch" class="form-control" placeholder="ابحث بكود التذكرة، اسم العميل، الهاتف، أو موديل الجهاز..." style="flex:1;min-width:240px;">
            <select id="maintStageFilter" class="form-control" style="width:auto;">
              <option value="all">كل المراحل النشطة</option>
              <?php foreach ($stages as $sKey => $sInfo): if ($sKey === 'delivered') continue; ?>
                <option value="<?= e($sKey) ?>"><?= e($sInfo['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>كود التذكرة</th>
                  <th>العميل والتواصل السريع</th>
                  <th>الجهاز والعطل</th>
                  <th>المرحلة الحالية</th>
                  <th>التكلفة / العربون</th>
                  <th>ملاحظات العميل</th>
                  <th>إدارة وتحديث</th>
                </tr>
              </thead>
              <tbody id="maintTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <?php if ($isAdmin): ?>
      <!-- ========================================================
           TAB 7: STAFF & ROLES MANAGEMENT (ADMIN ONLY)
           ======================================================== -->
      <section class="tab-panel" id="tab-staff">
        <div class="card" style="background:linear-gradient(135deg,#043538,#074f53);color:#fff;border-color:var(--db-gold-500);">
          <h3 style="margin:0 0 8px;color:var(--db-gold-400);">🛡️ نظام حماية الأسعار والصلاحيات بين المدير والموظفين</h3>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;font-size:.86rem;color:#d9efef;">
            <div><strong>👑 المدير (Admin):</strong> يتحكم في كل شيء، يرى سعر الشراء الحقيقي، رأس مال المخزن، صافي أرباح كل فاتورة، إيرادات الصيانة اليومية، وإعدادات الهوية والتواصل.</div>
            <div><strong>💼 الموظف (Staff):</strong> يفحص الأسعار بالكاميرا، يرى فقط (سعر البيع) و(أقل سعر بيع)، يُصدر فواتير البيع ويخصم المخزون، ويُمنع برمجياً من البيع بأقل من الحد الأدنى المسموح!</div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>👥 حسابات المديرين وموظفي المبيعات</h3>
            <button type="button" class="btn btn-gold btn-sm" id="btnAddUserModal">+ إضافة موظف / مدير جديد</button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>الاسم الكامل</th>
                  <th>اسم المستخدم</th>
                  <th>الصلاحية</th>
                  <th>الهاتف</th>
                  <th>الحالة</th>
                  <th>تاريخ الإنشاء</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="usersTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ========================================================
           TAB 8: BRAND IDENTITY, CONTACT CHANNELS & SMTP SETTINGS
           ======================================================== -->
      <section class="tab-panel" id="tab-settings">

        <!-- Official Brand Identity Kit -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>🎨 حزمة الهوية البصرية المعتمدة — ASLY | أصلي TECH STORE</h3>
              <span style="font-size:.82rem;color:var(--db-muted);">تم تصميم الشعارات المتجهة (Vector SVG) عالية الدقة استناداً إلى بنر المركز الرسمي لاستخدامها على الموقع والفواتير والمطبوعات.</span>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;align-items:stretch;">
            <div style="background:#043538;padding:20px;border-radius:14px;text-align:center;border:1px solid var(--db-gold-500);">
              <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="Dark Logo" style="height:64px;margin:0 auto 12px;">
              <div style="color:#d8ecec;font-size:.82rem;margin-bottom:10px;">الشعار الرسمي للخلفيات الداكنة</div>
              <a href="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" download="ASLY-Logo-Dark.svg" class="btn btn-gold btn-sm">تحميل SVG</a>
            </div>
            <div style="background:#f8fbfb;padding:20px;border-radius:14px;text-align:center;border:1px solid var(--db-border);">
              <img src="<?= e(appUrl('assets/brand/logo-light.svg')) ?>" alt="Light Logo" style="height:64px;margin:0 auto 12px;">
              <div style="color:var(--db-muted);font-size:.82rem;margin-bottom:10px;">الشعار الرسمي للخلفيات الفاتحة والطباعة</div>
              <a href="<?= e(appUrl('assets/brand/logo-light.svg')) ?>" download="ASLY-Logo-Light.svg" class="btn btn-teal btn-sm">تحميل SVG</a>
            </div>
            <div style="background:#ffffff;padding:20px;border-radius:14px;text-align:center;border:1px solid var(--db-border);">
              <img src="<?= e(appUrl('assets/brand/favicon.svg')) ?>" alt="Icon" style="height:64px;margin:0 auto 12px;">
              <div style="color:var(--db-muted);font-size:.82rem;margin-bottom:10px;">أيقونة العلامة (Monogram Crest)</div>
              <a href="<?= e(appUrl('assets/brand/favicon.svg')) ?>" download="ASLY-Icon.svg" class="btn btn-ghost btn-sm">تحميل الأيقونة</a>
            </div>
          </div>
        </div>

        <!-- Dynamic Contact Channels Manager -->
        <div class="card">
          <div class="card-header">
            <div>
              <h3>📞 إدارة أرقام الهواتف والواتساب ومنصات التواصل (تظهر بالموقع الخارجي)</h3>
              <span style="font-size:.82rem;color:var(--db-muted);">أضف أو عدّل أي رقم هاتف، واتساب، فيسبوك، إنستجرام، تيك توك، أو لوكيشن ليظهر فوراً للعملاء.</span>
            </div>
            <button type="button" class="btn btn-gold btn-sm" id="btnAddChannelModal">+ إضافة وسيلة تواصل</button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>النوع</th>
                  <th>العنوان الظاهر للعميل</th>
                  <th>الرقم / القيمة</th>
                  <th>الرابط التلقائي</th>
                  <th>الترتيب</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="channelsTableBody"></tbody>
            </table>
          </div>
        </div>

        <!-- Store Info & SMTP Email Settings Form -->
        <form id="settingsSaveForm" class="card">
          <div class="card-header">
            <h3>⚙️ إعدادات بيانات المركز، رابط الـ QR، وإشعارات البريد الإلكتروني (SMTP)</h3>
            <button type="button" class="btn btn-ghost btn-sm" id="btnViewEmailLogs">📬 سجل رسائل البريد المرسلة</button>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label>اسم المركز بالعربية</label>
              <input type="text" name="store_name_ar" id="set_store_name_ar" class="form-control">
            </div>
            <div class="form-group">
              <label>اسم المركز بالإنجليزية</label>
              <input type="text" name="store_name_en" id="set_store_name_en" class="form-control" style="direction:ltr;">
            </div>
            <div class="form-group">
              <label>رقم واتساب الاستفسارات الرئيسي (بكود الدولة 20)</label>
              <input type="text" name="primary_whatsapp" id="set_primary_whatsapp" class="form-control" placeholder="201015981677" style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label>هاتف الفواتير الرئيسي 1</label>
              <input type="text" name="primary_phone" id="set_primary_phone" class="form-control" style="direction:ltr;">
            </div>
            <div class="form-group">
              <label>هاتف الفواتير 2</label>
              <input type="text" name="secondary_phone" id="set_secondary_phone" class="form-control" style="direction:ltr;">
            </div>
            <div class="form-group">
              <label>مواعيد العمل</label>
              <input type="text" name="working_hours" id="set_working_hours" class="form-control">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label>عنوان المركز بالتفصيل</label>
              <input type="text" name="store_address" id="set_store_address" class="form-control">
            </div>
            <div class="form-group">
              <label>سطر الإدارة والإشراف الهندسي</label>
              <input type="text" name="founders_line" id="set_founders_line" class="form-control">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:18px;">
            <div class="form-group">
              <label>وصف المركز بالواجهة الرئيسية</label>
              <input type="text" name="store_tagline" id="set_store_tagline" class="form-control">
            </div>
            <div class="form-group">
              <label>رابط خرائط جوجل (اختياري)</label>
              <input type="url" name="google_maps_url" id="set_google_maps_url" class="form-control" placeholder="https://maps.google.com/..." style="direction:ltr;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:22px;">
            <div class="form-group">
              <label>الرابط الأساسي لتوليد أكواد QR الصيانة والفواتير (اتركه فارغاً للتعرف التلقائي أو ضع IP الشبكة على XAMPP)</label>
              <input type="text" name="public_base_url" id="set_public_base_url" class="form-control" placeholder="مثال على الشبكة: http://192.168.1.10/asly" style="direction:ltr;">
            </div>
            <div class="form-group">
              <label>نص الضمان أسفل الفاتورة</label>
              <input type="text" name="invoice_footer_note" id="set_invoice_footer_note" class="form-control">
            </div>
          </div>

          <!-- SMTP Configuration for Maintenance Stage Emails -->
          <div style="background:var(--db-teal-50);padding:20px;border-radius:16px;border:1px solid var(--db-border);margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
              <div>
                <strong style="color:var(--db-teal-900);font-size:.98rem;">📧 إعدادات إشعارات البريد الإلكتروني التلقائية لمراحل الصيانة (SMTP)</strong>
                <div style="font-size:.8rem;color:var(--db-muted);">عند تسجيل إيميل العميل بتذكرة الصيانة، يصله بريد بتفاصيل كل مرحلة جديدة تلقائياً (يعمل مع Gmail App Password أو أي استضافة).</div>
              </div>
              <select name="smtp_enabled" id="set_smtp_enabled" class="form-control" style="width:auto;font-weight:800;">
                <option value="0">وضع التجربة المحلية (حفظ بالسجل / PHP mail)</option>
                <option value="1">تفعيل الإرسال الفعلي عبر SMTP</option>
              </select>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>خادم SMTP Host</label>
                <input type="text" name="smtp_host" id="set_smtp_host" class="form-control" placeholder="smtp.gmail.com" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>المنفذ (Port)</label>
                <input type="number" name="smtp_port" id="set_smtp_port" class="form-control" placeholder="587" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>التشفير</label>
                <select name="smtp_encryption" id="set_smtp_encryption" class="form-control">
                  <option value="tls">TLS (Port 587 - مستحسن)</option>
                  <option value="ssl">SSL (Port 465)</option>
                  <option value="none">بدون تشفير</option>
                </select>
              </div>
              <div class="form-group">
                <label>البريد المرسل / اسم مستخدم SMTP</label>
                <input type="text" name="smtp_username" id="set_smtp_username" class="form-control" placeholder="yourstore@gmail.com" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>كلمة مرور التطبيقات (SMTP App Password)</label>
                <input type="password" name="smtp_password" id="set_smtp_password" class="form-control" placeholder="••••••••••••" style="direction:ltr;">
              </div>
              <div class="form-group">
                <label>البريد الظاهر كمرسل (From Email)</label>
                <input type="email" name="smtp_from_email" id="set_smtp_from_email" class="form-control" style="direction:ltr;">
              </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center;margin-top:14px;flex-wrap:wrap;">
              <input type="email" id="testEmailRecipient" class="form-control" placeholder="أدخل إيميل لإرسال رسالة مرحلة تجريبية..." style="max-width:300px;direction:ltr;">
              <button type="button" class="btn btn-teal btn-sm" id="btnSendTestEmail">📨 إرسال بريد تجريبي الآن</button>
            </div>
          </div>

          <button type="submit" class="btn btn-gold" style="padding:13px 32px;font-size:1rem;">💾 حفظ جميع الإعدادات</button>
        </form>

      </section>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- ============================================================
     MODALS: INVOICE PNG/WHATSAPP/PRINT, NEW MAINTENANCE, TICKET DETAIL, STICKER, STAFF, CHANNELS, EMAIL LOGS
     ============================================================ -->

<!-- 1. Invoice Preview, PNG Export, WhatsApp Share & Print Modal -->
<div class="db-modal-backdrop" id="invoiceModal">
  <div class="db-modal" style="width:min(820px,100%);">
    <div class="db-modal-head">
      <strong>🧾 فاتورة مبيعات رسمية — جاهزة للتحميل كصورة أو الإرسال واتساب أو الطباعة</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('invoiceModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body">
      <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-bottom:18px;">
        <button type="button" class="btn btn-gold" id="btnModalInvoicePng">📥 تحميل الفاتورة كصورة (PNG)</button>
        <button type="button" class="btn btn-emerald" id="btnModalInvoiceWhatsapp">💬 إرسال الفاتورة للعميل عبر واتساب</button>
        <button type="button" class="btn btn-teal" id="btnModalInvoicePrint">🖨️ طباعة الفاتورة</button>
      </div>

      <!-- Crisp Branded Invoice Capture Surface -->
      <div id="modalInvoiceCapture" style="background:#ffffff;border:2px solid #043538;border-radius:16px;overflow:hidden;"></div>
    </div>
  </div>
</div>

<!-- 2. Register New Maintenance Ticket Modal -->
<div class="db-modal-backdrop" id="newMaintModal">
  <div class="db-modal">
    <div class="db-modal-head">
      <strong>🛠️ استلام وتسجيل جهاز صيانة جديد + توليد كود QR للعميل</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('newMaintModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body">
      <form id="newMaintForm">
        <h4 style="margin:0 0 10px;color:var(--db-teal-900);">👤 أولاً: بيانات العميل والتواصل</h4>
        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label>اسم العميل الثلاثي *</label>
            <input type="text" name="customer_name" class="form-control" required placeholder="مثال: محمد أحمد علي">
          </div>
          <div class="form-group">
            <label>رقم الموبايل الأساسي *</label>
            <input type="text" name="customer_phone" class="form-control" required placeholder="010xxxxxxxx" style="direction:ltr;">
          </div>
          <div class="form-group">
            <label>رقم هاتف بديل / واتساب</label>
            <input type="text" name="customer_alt_phone" class="form-control" placeholder="011xxxxxxxx" style="direction:ltr;">
          </div>
        </div>

        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>البريد الإلكتروني للعميل (اختياري — يصله إيميل تلقائي بكل مرحلة!)</label>
            <input type="email" name="customer_email" class="form-control" placeholder="client@gmail.com" style="direction:ltr;">
          </div>
          <div class="form-group">
            <label>تفاصيل إضافية عن العميل / العنوان</label>
            <input type="text" name="customer_details" class="form-control" placeholder="مثال: عنوان العميل أو الموعد المفضل للاتصال">
          </div>
        </div>

        <h4 style="margin:0 0 10px;color:var(--db-teal-900);padding-top:10px;border-top:1px solid var(--db-border);">💻 ثانياً: مواصفات الجهاز والعطل والتكلفة</h4>
        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label>نوع الجهاز *</label>
            <select name="device_type" class="form-control">
              <option value="laptop">لابتوب (Laptop)</option>
              <option value="mobile">موبايل (Smartphone)</option>
              <option value="wearable">ساعة ذكية / Wearable</option>
              <option value="other">جهاز آخر</option>
            </select>
          </div>
          <div class="form-group">
            <label>الماركة والموديل *</label>
            <input type="text" name="device_model" class="form-control" required placeholder="مثال: HP ZBook G7 أو iPhone 14 Pro">
          </div>
          <div class="form-group">
            <label>السيريال / IMEI</label>
            <input type="text" name="serial_imei" class="form-control" style="direction:ltr;">
          </div>
        </div>

        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label>مواصفات الجهاز (رام، مساحة، لون...)</label>
            <input type="text" name="device_specs" class="form-control" placeholder="Core i7 • 16GB • 512GB • رمادي">
          </div>
          <div class="form-group">
            <label>باسورد / رمز قفل الجهاز للفحص</label>
            <input type="text" name="device_passcode" class="form-control" placeholder="سري للفني فقط (يُمسح عند التسليم)">
          </div>
          <div class="form-group">
            <label>الحالة الظاهرية والملحقات المستلمة</label>
            <input type="text" name="physical_condition" class="form-control" placeholder="مثال: بدون شاحن، خدش بالإطار">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:14px;">
          <label>وصف العطل المطلوب إصلاحه بالتفصيل *</label>
          <textarea name="problem_description" rows="2" class="form-control" required placeholder="اكتب شكوى العميل والعطل الظاهري..."></textarea>
        </div>

        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>التكلفة المبدئية المتوقعة (ج.م)</label>
            <input type="number" step="0.01" min="0" name="estimated_cost" class="form-control" value="0">
          </div>
          <?php if ($isAdmin): ?>
          <div class="form-group">
            <label>تكلفة قطع الغيار المبدئية (للأدمن)</label>
            <input type="number" step="0.01" min="0" name="parts_cost" class="form-control" value="0">
          </div>
          <?php endif; ?>
          <div class="form-group">
            <label>العربون المدفوع عند الاستلام (ج.م)</label>
            <input type="number" step="0.01" min="0" name="deposit_paid" class="form-control" value="0">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:18px;">
          <label>ملاحظة المرحلة الأولى (تظهر للعميل عند مسح الـ QR)</label>
          <input type="text" name="initial_note" class="form-control" value="تم استلام الجهاز وتسجيل بياناته بمركز أصلي تك ستور لبدء الفحص الفني.">
        </div>

        <button type="submit" class="btn btn-gold" style="width:100%;padding:13px;font-size:1rem;">
          ✅ تسجيل الجهاز وتوليد إيصال الـ QR للعميل
        </button>
      </form>
    </div>
  </div>
</div>

<!-- 3. Maintenance Ticket Detail, QR Receipt, Stages, Mutual Notes & Final Delivery Wipe Modal -->
<div class="db-modal-backdrop" id="maintDetailModal">
  <div class="db-modal" style="width:min(920px,100%);">
    <div class="db-modal-head">
      <strong id="mdModalTitle">ملف تذكرة الصيانة</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('maintDetailModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body" id="maintDetailBody"></div>
  </div>
</div>

<!-- 4. Sticker Print Modal -->
<div class="db-modal-backdrop" id="stickerModal">
  <div class="db-modal" style="width:min(440px,100%);">
    <div class="db-modal-head">
      <strong>🏷️ طباعة استيكر QR / باركود للمنتج</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('stickerModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body" style="text-align:center;">
      <div id="modalStickerArea" class="sticker-preview-box" style="margin-bottom:18px;">
        <strong style="font-size:.9rem;color:#043538;">ASLY | أصلي TECH STORE</strong>
        <div id="modalStickerName" style="font-size:.82rem;color:#334155;font-weight:700;"></div>
        <div id="modalStickerQr" style="margin:6px auto;"></div>
        <svg id="modalStickerBarcode" style="max-width:220px;height:52px;"></svg>
        <div id="modalStickerCode" style="font-family:monospace;font-weight:900;font-size:.88rem;"></div>
      </div>
      <div>
        <button type="button" class="btn btn-gold" id="btnPrintModalSticker" style="width:100%;">🖨️ طباعة الاستيكر الآن</button>
      </div>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- 5. Add/Edit Staff or Admin Modal -->
<div class="db-modal-backdrop" id="userModal">
  <div class="db-modal" style="width:min(500px,100%);">
    <div class="db-modal-head">
      <strong id="userModalTitle">إدارة حساب موظف / مدير</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('userModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body">
      <form id="userSaveForm">
        <input type="hidden" name="id" id="uFormId" value="0">
        <div class="form-group" style="margin-bottom:12px;">
          <label>الاسم الكامل *</label>
          <input type="text" name="full_name" id="uFormFullName" class="form-control" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>اسم المستخدم للدخول *</label>
          <input type="text" name="username" id="uFormUsername" class="form-control" required style="direction:ltr;">
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>كلمة المرور <span id="uPassHint" style="font-weight:400;color:var(--db-muted);"></span></label>
          <input type="password" name="password" id="uFormPassword" class="form-control" style="direction:ltr;">
        </div>
        <div class="form-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>الصلاحية *</label>
            <select name="role" id="uFormRole" class="form-control">
              <option value="staff">موظف مبيعات وصيانة (Staff - بدون سعر الشراء)</option>
              <option value="admin">مدير كامل الصلاحيات (Admin)</option>
            </select>
          </div>
          <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" id="uFormPhone" class="form-control" style="direction:ltr;">
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-weight:700;">
          <input type="checkbox" name="is_active" id="uFormActive" value="1" checked> الحساب نشط ومسموح له بالدخول
        </label>
        <button type="submit" class="btn btn-gold" style="width:100%;">حفظ الحساب</button>
      </form>
    </div>
  </div>
</div>

<!-- 6. Add/Edit Contact Channel Modal -->
<div class="db-modal-backdrop" id="channelModal">
  <div class="db-modal" style="width:min(520px,100%);">
    <div class="db-modal-head">
      <strong>📞 إضافة / تعديل وسيلة تواصل بالموقع</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('channelModal')">إغلاق ×</button>
    </div>
    <div class="db-modal-body">
      <form id="channelSaveForm">
        <input type="hidden" name="id" id="chFormId" value="0">
        <div class="form-grid" style="margin-bottom:12px;">
          <div class="form-group">
            <label>نوع الوسيلة *</label>
            <select name="channel_type" id="chFormType" class="form-control">
              <option value="phone">اتصال هاتفي (Phone)</option>
              <option value="whatsapp">واتساب (WhatsApp)</option>
              <option value="facebook">فيسبوك (Facebook)</option>
              <option value="instagram">إنستجرام (Instagram)</option>
              <option value="tiktok">تيك توك (TikTok)</option>
              <option value="telegram">تيليجرام (Telegram)</option>
              <option value="email">بريد إلكتروني (Email)</option>
              <option value="location">موقع / عنوان</option>
            </select>
          </div>
          <div class="form-group">
            <label>ترتيب الظهور</label>
            <input type="number" name="sort_order" id="chFormSort" class="form-control" value="1">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>العنوان الظاهر للعميل *</label>
          <input type="text" name="label" id="chFormLabel" class="form-control" placeholder="مثال: مبيعات واستفسارات - م. حسام" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>الرقم أو اسم الحساب الظاهر *</label>
          <input type="text" name="value" id="chFormValue" class="form-control" placeholder="01015981677 أو @asly.store" required style="direction:ltr;">
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label>الرابط المباشر عند الضغط (اتركه فارغاً للتوليد التلقائي للهاتف والواتساب)</label>
          <input type="text" name="url" id="chFormUrl" class="form-control" placeholder="https://facebook.com/..." style="direction:ltr;">
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;font-weight:700;">
          <input type="checkbox" name="is_active" id="chFormActive" value="1" checked> إظهار في الصفحة الرئيسية للعملاء
        </label>
        <button type="submit" class="btn btn-gold" style="width:100%;">حفظ وسيلة التواصل</button>
      </form>
    </div>
  </div>
</div>

<!-- 7. Email Outbox Logs Modal -->
<div class="db-modal-backdrop" id="emailLogsModal">
  <div class="db-modal" style="width:min(860px,100%);">
    <div class="db-modal-head">
      <strong>📬 سجل إشعارات البريد الإلكتروني المرسلة للعملاء</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('emailLogsModal')">إغلاق ×</button>
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
<script src="<?= e(appUrl('assets/vendor/html5-qrcode.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/vendor/html2canvas.min.js')) ?>"></script>
<script src="<?= e(appUrl('assets/js/dashboard.js')) ?>"></script>
</body>
</html>
