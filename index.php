<?php
/**
 * ASLY | أصلي TECH STORE
 * Public Storefront & Landing Page (Strictly NO PRICES Exposed Anywhere)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$channels = getActiveContactChannels($pdo, false);
$catsMeta = getCategoryMeta();
$condsMeta = getConditionMeta();

// Select ONLY public-safe columns (NEVER purchase_price, sell_price, or min_sell_price!)
$products = $pdo->query("
    SELECT id, barcode, name, category, condition_status, brand, specs, description,
           image_path, quantity, warranty_default, is_featured
    FROM products
    WHERE show_on_landing = 1 AND quantity > 0
    ORDER BY is_featured DESC, id DESC
")->fetchAll();

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$storeTagline = $settings['store_tagline'] ?: 'المركز المتكامل لبيع وصيانة اللابتوبات والهواتف الذكية والأجهزة القابلة للارتداء والإكسسوارات الأصلية';
$foundersLine = $settings['founders_line'] ?: 'م. حسام محمد (Eng. Hossam Mohamed) | م. رمضان سلام (Eng. Ramadan Sallam)';
$primaryPhone = $settings['primary_phone'] ?: '01015981677';
$secondaryPhone = $settings['secondary_phone'] ?: '01154639718';
$primaryWa = normalizeWhatsappNumber($settings['primary_whatsapp'] ?: $primaryPhone);

function renderChannelSvg(string $type): string
{
    return match ($type) {
        'whatsapp' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
        'facebook' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
        'instagram' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
        'tiktok' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>',
        'telegram' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
        'email' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
        'location' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        default => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    };
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($storeNameAr) ?> | <?= e($storeNameEn) ?> — لابتوب، هواتف، صيانة وإكسسوارات</title>
  <meta name="description" content="<?= e($storeTagline) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body>

  <!-- Top Utility Bar -->
  <div class="topbar">
    <div class="container topbar-inner">
      <div class="topbar-phones">
        <span><?= e($settings['working_hours'] ?: 'يومياً من 11 صباحاً حتى 11 مساءً') ?></span>
        <a href="tel:<?= e($primaryPhone) ?>">📞 <?= e($primaryPhone) ?></a>
        <?php if ($secondaryPhone !== ''): ?>
          <a href="tel:<?= e($secondaryPhone) ?>">📞 <?= e($secondaryPhone) ?></a>
        <?php endif; ?>
      </div>
      <div class="topbar-links">
        <a href="<?= e(appUrl('track.php')) ?>">🔍 تتبع حالة جهازك بالصيانة</a>
        <span style="opacity:.3">|</span>
        <a href="<?= e(appUrl('admin/index.php')) ?>">لوحة الإدارة والموظفين</a>
      </div>
    </div>
  </div>

  <!-- Sticky Luxury Header -->
  <header class="site-header">
    <div class="container nav-inner">
      <a href="<?= e(appUrl('index.php')) ?>" class="brand-logo" aria-label="<?= e($storeNameEn) ?>">
        <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي TECH STORE">
      </a>

      <nav class="nav-links">
        <a href="#hero">الرئيسية</a>
        <a href="#catalog">معرض الأجهزة</a>
        <a href="#services">خدمات الصيانة</a>
        <a href="<?= e(appUrl('track.php')) ?>">تتبع صيانة QR</a>
        <a href="#contact">تواصل معنا</a>
      </nav>

      <div class="nav-actions">
        <a href="<?= e(appUrl('track.php')) ?>" class="btn btn-outline-gold">تتبع جهازك</a>
        <a href="https://wa.me/<?= e($primaryWa) ?>?text=<?= urlencode('مرحباً مركز أصلي ASLY TECH STORE، أود الاستفسار عن الأجهزة المتاحة.') ?>" target="_blank" rel="noopener" class="btn btn-gold">
          تواصل واتساب
        </a>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="hero" class="hero">
    <div class="container hero-grid">
      <div>
        <div class="hero-eyebrow">
          <span>✦</span>
          <span>الثقة والجودة الأصلية تحت سقف واحد</span>
        </div>
        <h1>
          مركز <span class="gold-text">أصلي | ASLY</span><br>
          لأحدث اللابتوبات والهواتف والصيانة الاحترافية
        </h1>
        <p class="lead">
          <?= e($storeTagline) ?> — نوفر لك أجهزة جديدة متبرشمة وكسر زيرو ومستعملة مفحوصة بعناية هندسية فائقة مع ضمان حقيقي ومركز صيانة متكامل بنظام تتبع لحظي عبر الـ QR.
        </p>

        <div class="hero-cta-row">
          <a href="#catalog" class="btn btn-gold">تصفح الأجهزة المتاحة</a>
          <a href="<?= e(appUrl('track.php')) ?>" class="btn btn-outline-gold">متابعة جهازك في الصيانة</a>
        </div>

        <div class="hero-categories-bar">
          <div class="hero-cat-pill" data-filter-cat="laptops">
            <strong>لابتوب</strong>
            <span>Laptops</span>
          </div>
          <div class="hero-cat-pill" data-filter-cat="mobiles">
            <strong>هواتف</strong>
            <span>Mobiles</span>
          </div>
          <div class="hero-cat-pill" data-filter-cat="accessories">
            <strong>إكسسوارات</strong>
            <span>Accessories</span>
          </div>
          <div class="hero-cat-pill" data-filter-cat="wearables">
            <strong>أجهزة قابلة للارتداء</strong>
            <span>Wearables</span>
          </div>
        </div>
      </div>

      <div class="hero-showcase-card">
        <div class="hero-banner-frame">
          <img src="<?= e(appUrl('assets/brand/official-banner.png')) ?>" alt="ASLY | أصلي TECH STORE Official Identity">
        </div>

        <div class="hero-track-box">
          <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            متابعة حالة جهازك في الصيانة لحظة بلحظة
          </h3>
          <p>امسح كود الـ QR الموجود على إيصال الاستلام بكاميرا هاتفك أو أدخل رقم التذكرة هنا لمعرفة المرحلة الحالية وإضافة ملاحظاتك:</p>
          <form action="<?= e(appUrl('track.php')) ?>" method="get" class="track-mini-form">
            <input type="text" name="code" placeholder="مثال: MNT-2026-101" required>
            <button type="submit" class="btn btn-gold">تتبع الآن</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Products Showcase Section (NO PRICES EXPOSED!) -->
  <section id="catalog" class="section">
    <div class="container">
      <div class="section-head">
        <span class="section-badge">تشكيلة مختارة بعناية</span>
        <h2>معرض الأجهزة والإكسسوارات المتاحة</h2>
        <p>تصفح أحدث الأجهزة المتوفرة داخل المركز بالمواصفات والصور الحقيقية — للاستفسار عن أفضل سعر تواصل معنا بضغطة زر عبر واتساب.</p>
      </div>

      <div class="catalog-controls">
        <div class="filter-tabs">
          <button type="button" class="filter-tab active" data-filter-cat="all">الكل (<?= count($products) ?>)</button>
          <?php foreach ($catsMeta as $catKey => $catInfo): ?>
            <button type="button" class="filter-tab" data-filter-cat="<?= e($catKey) ?>"><?= e($catInfo['ar']) ?></button>
          <?php endforeach; ?>
        </div>

        <div class="catalog-search-row">
          <select id="conditionFilter" aria-label="تصفية حسب الحالة">
            <option value="all">كل الحالات (جديد / مستعمل)</option>
            <option value="new">جديد متبرشم</option>
            <option value="open_box">كسر زيرو</option>
            <option value="used">مستعمل فرز أول</option>
          </select>
          <input type="search" id="catalogSearch" placeholder="ابحث بالموديل أو المعالج أو الماركة...">
        </div>
      </div>

      <div class="products-grid">
        <?php foreach ($products as $p):
          $catLabel = $catsMeta[$p['category']]['ar'] ?? $p['category'];
          $condLabel = $condsMeta[$p['condition_status']]['ar'] ?? $p['condition_status'];
          $imgUrl = !empty($p['image_path']) ? appUrl($p['image_path']) : appUrl('assets/brand/official-banner.png');
          $waMsg = sprintf(
              "مرحباً مركز أصلي ASLY TECH STORE 👋\nأود الاستفسار عن سعر وتفاصيل هذا المنتج:\n• المنتج: %s\n• الحالة: %s\n• الكود المرجعي: %s",
              $p['name'],
              $condLabel,
              $p['barcode']
          );
          $waUrl = 'https://wa.me/' . $primaryWa . '?text=' . urlencode($waMsg);

          $modalPayload = [
              'name' => $p['name'],
              'barcode' => $p['barcode'],
              'brand' => $p['brand'],
              'category_label' => $catLabel,
              'condition_label' => $condLabel,
              'specs' => $p['specs'],
              'description' => $p['description'],
              'warranty' => $p['warranty_default'],
              'image' => $imgUrl,
              'whatsapp_url' => $waUrl,
          ];
        ?>
          <article
            class="product-card"
            data-category="<?= e((string)$p['category']) ?>"
            data-condition="<?= e((string)$p['condition_status']) ?>"
            data-search="<?= e($p['name'] . ' ' . $p['brand'] . ' ' . $p['specs'] . ' ' . $p['barcode']) ?>"
          >
            <div class="product-img-wrap">
              <img src="<?= e($imgUrl) ?>" alt="<?= e((string)$p['name']) ?>" loading="lazy">
              <span class="cond-badge cond-<?= e((string)$p['condition_status']) ?>"><?= e($condLabel) ?></span>
              <span class="cat-chip"><?= e($catLabel) ?></span>
            </div>

            <div class="product-body">
              <div class="product-brand-row">
                <span><?= e($p['brand'] ?: 'ASLY Certified') ?></span>
                <span class="stock-pill">● متوفر بالمعرض</span>
              </div>
              <h3 class="product-title"><?= e((string)$p['name']) ?></h3>
              <div class="product-specs">
                <?= e($p['specs'] ?: 'مفحوص بالكامل وجاهز للمعاينة والتجربة داخل المركز.') ?>
              </div>

              <div class="product-card-actions">
                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp">
                  اسأل عن السعر واتساب
                </a>
                <button
                  type="button"
                  class="btn btn-teal js-open-product"
                  data-product="<?= e((string)json_encode($modalPayload, JSON_UNESCAPED_UNICODE)) ?>"
                  title="عرض المواصفات الكاملة"
                >
                  التفاصيل
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div id="catalogEmptyState" style="display:<?= count($products) === 0 ? 'block' : 'none' ?>;text-align:center;padding:48px 20px;background:#fff;border-radius:16px;border:1px solid var(--asly-border);margin-top:20px;">
        <h3 style="margin:0 0 8px;color:var(--asly-teal-900);">لا توجد منتجات مطابقة لفلتر البحث الحالي</h3>
        <p style="margin:0;color:var(--asly-muted);">تواصل معنا عبر واتساب وسنوفر لك الجهاز المطلوب بأفضل حالة وضمان.</p>
      </div>
    </div>
  </section>

  <!-- Services & Maintenance Lab Section -->
  <section id="services" class="section section-alt">
    <div class="container">
      <div class="section-head">
        <span class="section-badge">خبرة هندسية معتمدة</span>
        <h2>لماذا تختار مركز أصلي ASLY TECH STORE؟</h2>
        <p><?= e($foundersLine) ?></p>
      </div>

      <div class="services-grid">
        <div class="service-card">
          <div class="service-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
          </div>
          <h3>لابتوبات وهواتف جديدة وكسر زيرو</h3>
          <p>تشكيلة متجددة من أجهزة الاستيراد الفرز الأول والأجهزة الجديدة المتبرشمة بعد اجتياز اختبارات الفحص الهندسي الشامل.</p>
        </div>

        <div class="service-card">
          <div class="service-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <h3>صيانة احترافية للهاردوير والسوفتوير</h3>
          <p>صيانة دقيقة للماذربورد، الشاشات، دوائر الشحن، التبريد الاحترافي، وترقية الأداء بقطع غيار أصلية مضمونة.</p>
        </div>

        <div class="service-card">
          <div class="service-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><path d="M3 14h7v7H3z"/></svg>
          </div>
          <h3>تتبع الصيانة بالـ QR وإشعارات البريد</h3>
          <p>استلم إيصالاً مزوداً بكود QR خاص بجهازك لتتابع كل مرحلة من مراحل الفحص والإصلاح وتتبادل الملاحظات مع المهندس المختص.</p>
        </div>

        <div class="service-card">
          <div class="service-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <h3>ضمان وفاتورة رسمية وحماية الخصوصية</h3>
          <p>فاتورة رسمية موثقة لكل عملية شراء، وحذف تلقائي كامل لبيانات تذكرة الصيانة فور استلامك لجهازك حفاظاً على خصوصيتك التامة.</p>
        </div>
      </div>

      <div class="maint-cta-banner">
        <div>
          <h3>هل لديك جهاز في قسم الصيانة حالياً؟</h3>
          <p style="margin:0;color:#d6ebeb;">يمكنك متابعة المرحلة التي وصل إليها جهازك، قراءة تقارير المهندس على كل مرحلة، وكتابة أي ملاحظة أو استفسار مباشرة من هاتفك.</p>
        </div>
        <form action="<?= e(appUrl('track.php')) ?>" method="get" class="track-mini-form">
          <input type="text" name="code" placeholder="أدخل كود التذكرة (مثلاً MNT-2026-101)" required>
          <button type="submit" class="btn btn-gold">عرض المراحل</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Dynamic Contact & Channels Section -->
  <section id="contact" class="section">
    <div class="container">
      <div class="section-head">
        <span class="section-badge">نحن في خدمتك دائماً</span>
        <h2>قنوات التواصل المباشر مع المركز</h2>
        <p><?= e($settings['store_address'] ?: 'تواصل معنا هاتفياً أو عبر واتساب ومنصات التواصل الاجتماعي') ?> — <?= e($settings['working_hours'] ?: '') ?></p>
      </div>

      <div class="channels-grid">
        <?php foreach ($channels as $ch):
          $href = trim((string)($ch['url'] ?? ''));
          if ($href === '') {
              $href = '#contact';
          }
          $isExternal = strpos($href, 'http') === 0;
        ?>
          <a
            href="<?= e($href) ?>"
            <?= $isExternal ? 'target="_blank" rel="noopener"' : '' ?>
            class="channel-card"
          >
            <div class="channel-icon">
              <?= renderChannelSvg((string)$ch['channel_type']) ?>
            </div>
            <div class="channel-meta">
              <strong><?= e((string)$ch['label']) ?></strong>
              <span><?= e((string)$ch['value']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($settings['google_maps_url'])): ?>
        <div style="text-align:center;margin-top:28px;">
          <a href="<?= e($settings['google_maps_url']) ?>" target="_blank" rel="noopener" class="btn btn-teal">
            📍 فتح موقع المركز على خرائط Google
          </a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي" style="height:56px;margin-bottom:14px;">
          <p style="margin:0;color:#b8d4d6;font-size:.93rem;max-width:440px;">
            <?= e($storeTagline) ?>
          </p>
          <div class="footer-founders"><?= e($foundersLine) ?></div>
        </div>

        <div>
          <h4 style="margin:0 0 14px;color:var(--asly-gold-400);">روابط سريعة</h4>
          <div style="display:flex;flex-direction:column;gap:9px;font-size:.92rem;">
            <a href="#catalog">معرض اللابتوبات والهواتف</a>
            <a href="#services">خدمات الصيانة المتكاملة</a>
            <a href="<?= e(appUrl('track.php')) ?>">تتبع تذكرة الصيانة بالـ QR</a>
            <a href="<?= e(appUrl('admin/login.php')) ?>">بوابة الإدارة والموظفين</a>
          </div>
        </div>

        <div>
          <h4 style="margin:0 0 14px;color:var(--asly-gold-400);">العنوان ومواعيد العمل</h4>
          <p style="margin:0 0 8px;font-size:.92rem;">📍 <?= e($settings['store_address'] ?: 'سوهاج - جمهورية مصر العربية') ?></p>
          <p style="margin:0 0 12px;font-size:.92rem;">🕒 <?= e($settings['working_hours'] ?: 'يومياً من 11:00 صباحاً حتى 11:00 مساءً') ?></p>
          <p style="margin:0;font-weight:800;color:#ffffff;direction:ltr;text-align:right;">
            <?= e($primaryPhone) ?><?= $secondaryPhone ? '  |  ' . e($secondaryPhone) : '' ?>
          </p>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© <?= date('Y') ?> <?= e($storeNameAr) ?> (<?= e($storeNameEn) ?>). جميع الحقوق محفوظة.</span>
        <span>بيع وصيانة اللابتوبات والهواتف الذكية والإكسسوارات الأصلية</span>
      </div>
    </div>
  </footer>

  <!-- Product Details Quick-View Modal (ZERO PRICES!) -->
  <div class="modal-backdrop" id="productModal">
    <div class="modal-dialog">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 24px;background:var(--asly-teal-900);color:#fff;border-bottom:3px solid var(--asly-gold-500);">
        <strong id="modalProdTitle" style="font-size:1.15rem;">تفاصيل الجهاز</strong>
        <button type="button" id="closeProductModal" style="background:transparent;border:none;color:var(--asly-gold-400);font-size:1.5rem;cursor:pointer;line-height:1;">×</button>
      </div>
      <div style="padding:24px;">
        <div style="border-radius:14px;overflow:hidden;max-height:310px;background:#042b2d;margin-bottom:18px;">
          <img id="modalProdImg" src="" alt="" style="width:100%;height:300px;object-fit:cover;">
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
          <span id="modalProdCondition" style="padding:4px 12px;border-radius:999px;background:var(--asly-gold-50);color:var(--asly-gold-600);font-weight:800;font-size:.82rem;"></span>
          <span id="modalProdCategory" style="padding:4px 12px;border-radius:999px;background:var(--asly-teal-50);color:var(--asly-teal-900);font-weight:800;font-size:.82rem;"></span>
          <span id="modalProdBrand" style="padding:4px 12px;border-radius:999px;background:#f3f4f6;color:#374151;font-weight:700;font-size:.82rem;"></span>
          <span id="modalProdCode" style="padding:4px 12px;border-radius:999px;background:#f3f4f6;color:#374151;font-weight:700;font-size:.82rem;direction:ltr;"></span>
        </div>
        <div style="margin-bottom:14px;">
          <strong style="display:block;color:var(--asly-teal-900);margin-bottom:6px;">المواصفات الفنية:</strong>
          <div id="modalProdSpecs" style="background:var(--asly-teal-50);padding:12px 14px;border-radius:10px;border-right:4px solid var(--asly-gold-500);font-size:.92rem;"></div>
        </div>
        <p id="modalProdDesc" style="margin:0 0 14px;color:var(--asly-muted);font-size:.92rem;"></p>
        <div style="padding:10px 14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#065f46;font-size:.86rem;font-weight:700;margin-bottom:20px;">
          🛡️ الضمان: <span id="modalProdWarranty"></span>
        </div>
        <a id="modalWhatsappBtn" href="#" target="_blank" rel="noopener" class="btn btn-whatsapp" style="width:100%;padding:14px;font-size:1rem;">
          تواصل عبر واتساب لمعرفة السعر والحجز
        </a>
      </div>
    </div>
  </div>

  <script src="<?= e(appUrl('assets/js/landing.js')) ?>"></script>
</body>
</html>
