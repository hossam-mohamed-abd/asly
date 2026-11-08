<?php
/**
 * ASLY | أصلي TECH STORE
 * Bright Daylight Editorial Lookbook Storefront (Bilingual EN LTR + AR RTL)
 * Strictly ZERO public prices exposed in SQL, JSON, or DOM!
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$channels = getActiveContactChannels($pdo);
$catsMeta = getCategoryMeta();
$condsMeta = getConditionMeta();
$en = isEn();
$langKey = $en ? 'en' : 'ar';

// Strictly select ONLY public showcase fields — NEVER purchase_price, sell_price, or min_sell_price!
$productsStmt = $pdo->query("
    SELECT id, category, condition_status, brand,
           name, name_en, specs, specs_en, description, description_en,
           image_path, secondary_image_path, warranty_default, is_featured
    FROM products
    WHERE show_on_landing = 1
    ORDER BY is_featured DESC, id DESC
");
$rawProducts = $productsStmt->fetchAll();

$defaultByCat = [
    'laptops'     => ['uploads/products/laptop-studio.jpg', 'uploads/products/laptop-lifestyle.jpg'],
    'mobiles'     => ['uploads/products/mobile-studio.jpg', 'uploads/products/mobile-lifestyle.jpg'],
    'accessories' => ['uploads/products/headphones-studio.jpg', 'uploads/products/headphones-lifestyle.jpg'],
    'wearables'   => ['uploads/products/watch-studio.jpg', 'uploads/products/watch-lifestyle.jpg'],
];

$publicProducts = [];
foreach ($rawProducts as $p) {
    $cat = (string)$p['category'];
    $fallbacks = $defaultByCat[$cat] ?? $defaultByCat['laptops'];
    $imgPrimary = !empty($p['image_path']) ? appUrl((string)$p['image_path']) : appUrl($fallbacks[0]);
    $imgSecondary = !empty($p['secondary_image_path']) ? appUrl((string)$p['secondary_image_path']) : '';

    $publicProducts[] = [
        'id'              => (int)$p['id'],
        'category'        => $cat,
        'category_label'  => $catsMeta[$cat][$langKey] ?? $cat,
        'condition_label' => $condsMeta[$p['condition_status']][$langKey] ?? $p['condition_status'],
        'brand'           => (string)($p['brand'] ?? ''),
        'title'           => pickLangField($p, 'name'),
        'specs'           => pickLangField($p, 'specs'),
        'description'     => pickLangField($p, 'description'),
        'warranty'        => $en && preg_match('/\p{Arabic}/u', (string)$p['warranty_default'])
            ? 'Certified ASLY Testing & Hardware Warranty'
            : (string)($p['warranty_default'] ?: ''),
        'img_primary'     => $imgPrimary,
        'img_secondary'   => $imgSecondary,
        'is_featured'     => (int)$p['is_featured'] === 1,
    ];
}

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$displayBrandTitle = $en ? $storeNameEn : ($storeNameAr . ' | ' . $storeNameEn);
$tagline = $en
    ? ($settings['store_tagline_en'] ?: 'Curated Laptops, Flagship Smartphones, Original Accessories & Precision QR-Tracked Repair Lab')
    : ($settings['store_tagline'] ?: 'المركز المتكامل لبيع وصيانة اللابتوبات والهواتف الذكية والساعات والإكسسوارات الأصلية');
$foundersLine = $en
    ? ($settings['founders_line_en'] ?: 'Managed & Supervised by Eng. Hossam Mohamed • Eng. Ramadan Sallam')
    : ($settings['founders_line'] ?: 'إشراف وإدارة: م. حسام محمد • م. رمضان سلام');
$addressText = $en
    ? ($settings['store_address_en'] ?: 'Sohag — Arab Republic of Egypt')
    : ($settings['store_address'] ?: 'سوهاج — جمهورية مصر العربية');
$hoursText = $en
    ? ($settings['working_hours_en'] ?: 'Daily from 11:00 AM to 11:00 PM')
    : ($settings['working_hours'] ?: 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً');

$primaryPhone = $settings['primary_phone'] ?: '01015981677';
$primaryWa = normalizeWhatsappNumber($settings['primary_whatsapp'] ?: $primaryPhone);
$mapsUrl = trim($settings['google_maps_url'] ?? '');

$channelIcons = [
    'phone'     => '📞',
    'whatsapp'  => '💬',
    'facebook'  => '🌐',
    'instagram' => '📸',
    'tiktok'    => '🎵',
    'telegram'  => '✈️',
    'email'     => '✉️',
    'location'  => '📍',
];

$otherLang = $en ? 'ar' : 'en';
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($displayBrandTitle) ?> — <?= e(t('hero_title')) ?></title>
  <meta name="description" content="<?= e($tagline) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body>

  <!-- 1. Kinetic Top Announcement Marquee -->
  <div class="marquee-bar" aria-label="Store highlights">
    <div class="marquee-track">
      <?php for ($loop = 0; $loop < 2; $loop++): ?>
        <span class="marquee-item"><span class="marquee-dot"></span> <?= e(t('marquee_1')) ?></span>
        <span class="marquee-item"><span class="marquee-dot"></span> <?= e(t('marquee_2')) ?></span>
        <span class="marquee-item"><span class="marquee-dot"></span> <?= e(t('marquee_3')) ?></span>
        <span class="marquee-item"><span class="marquee-dot"></span> <?= e(t('marquee_4')) ?></span>
      <?php endfor; ?>
    </div>
  </div>

  <!-- 2. Floating Frosted White Navigation Header -->
  <header class="site-header">
    <div class="container nav-inner">
      <a href="<?= e(appUrl('index.php')) ?>" class="brand-logo" aria-label="ASLY Tech Store">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store">
      </a>

      <nav class="nav-links" id="mainNavLinks">
        <a href="#collections"><?= e(t('nav_collections')) ?></a>
        <a href="#catalog"><?= e(t('nav_catalog')) ?></a>
        <a href="#story"><?= e(t('nav_story')) ?></a>
        <a href="#repair-lab"><?= e(t('nav_lab')) ?></a>
        <a href="#real-life"><?= e(t('nav_gallery')) ?></a>
        <a href="#faq"><?= e(t('nav_faq')) ?></a>
        <a href="#contact"><?= e(t('nav_contact')) ?></a>
      </nav>

      <div class="nav-actions">
        <button type="button" class="icon-pill-btn" id="btnOpenSearch" aria-label="<?= e(t('nav_search_btn')) ?>">
          🔍 <span><?= e(t('nav_search_btn')) ?></span>
        </button>

        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill" title="Switch Language">
          🌐 <?= e(t('lang_switch_label')) ?>
        </a>

        <a href="<?= e(appUrl('track.php')) ?>" class="btn btn-teal btn-sm">
          🛠️ <?= e(t('nav_track_btn')) ?>
        </a>

        <button type="button" class="mobile-nav-toggle" id="btnMobileMenu" aria-label="Menu">☰</button>
      </div>
    </div>
  </header>

  <main>

    <!-- 3. Split Editorial Lookbook Hero -->
    <section class="hero-lookbook">
      <div class="container hero-grid">

        <div class="hero-copy">
          <div class="eyebrow-pill">✨ <?= e(t('hero_eyebrow')) ?></div>
          <h1><?= e(t('hero_title')) ?></h1>
          <p><?= e(t('hero_subtitle')) ?></p>

          <div class="hero-cta-row">
            <a href="#catalog" class="btn btn-teal"><?= e(t('hero_cta_explore')) ?></a>
            <a href="#repair-lab" class="btn btn-gold">🔎 <?= e(t('hero_cta_track')) ?></a>
          </div>

          <div class="hero-trust-row">
            <div class="hero-trust-card">
              <div class="hero-trust-icon">🛡️</div>
              <div>
                <strong><?= e(t('hero_badge_1_top')) ?></strong>
                <span><?= e(t('hero_badge_1_sub')) ?></span>
              </div>
            </div>
            <div class="hero-trust-card">
              <div class="hero-trust-icon">📲</div>
              <div>
                <strong><?= e(t('hero_badge_2_top')) ?></strong>
                <span><?= e(t('hero_badge_2_sub')) ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Asymmetrical Daylight Editorial Photography Collage -->
        <div class="hero-visuals">
          <div class="lookbook-frame tall">
            <img src="<?= e(appUrl('assets/images/lookbook/hero-2.jpg')) ?>" alt="ASLY Everyday Mobility">
            <span class="floating-caption"><?= e(t('gal_cap_2')) ?></span>
          </div>

          <div class="hero-stack-col">
            <div class="lookbook-frame medium">
              <img src="<?= e(appUrl('assets/images/lookbook/hero-1.jpg')) ?>" alt="ASLY Bright Workstation">
              <span class="floating-caption"><?= e(t('gal_cap_1')) ?></span>
            </div>

            <div class="lookbook-frame cameo-row">
              <a href="#catalog" class="mini-product-cameo" data-jump-cat="accessories">
                <img src="<?= e(appUrl('uploads/products/headphones-studio.jpg')) ?>" alt="Studio ANC">
                <span><?= e(t('cat_accessories')) ?></span>
              </a>
              <a href="#catalog" class="mini-product-cameo" data-jump-cat="wearables">
                <img src="<?= e(appUrl('uploads/products/watch-studio.jpg')) ?>" alt="Smart Watch">
                <span><?= e(t('cat_wearables')) ?></span>
              </a>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- 4. Curated Tech Collections Strip -->
    <section class="section-pad" id="collections" style="padding-bottom:32px;">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill"><?= e(t('collections_eyebrow')) ?></div>
          <h2><?= e(t('collections_title')) ?></h2>
        </div>

        <div class="collections-grid">
          <button type="button" class="collection-tile" data-jump-cat="laptops">
            <div class="collection-img-wrap">
              <img src="<?= e(appUrl('uploads/products/laptop-lifestyle.jpg')) ?>" alt="Laptops">
            </div>
            <div class="collection-tile-foot">
              <strong><?= e(t('cat_laptops')) ?></strong>
              <span class="collection-arrow"><?= $en ? '→' : '←' ?></span>
            </div>
          </button>

          <button type="button" class="collection-tile" data-jump-cat="mobiles">
            <div class="collection-img-wrap">
              <img src="<?= e(appUrl('uploads/products/mobile-lifestyle.jpg')) ?>" alt="Smartphones">
            </div>
            <div class="collection-tile-foot">
              <strong><?= e(t('cat_mobiles')) ?></strong>
              <span class="collection-arrow"><?= $en ? '→' : '←' ?></span>
            </div>
          </button>

          <button type="button" class="collection-tile" data-jump-cat="accessories">
            <div class="collection-img-wrap">
              <img src="<?= e(appUrl('uploads/products/headphones-lifestyle.jpg')) ?>" alt="Audio & Accessories">
            </div>
            <div class="collection-tile-foot">
              <strong><?= e(t('cat_accessories')) ?></strong>
              <span class="collection-arrow"><?= $en ? '→' : '←' ?></span>
            </div>
          </button>

          <button type="button" class="collection-tile" data-jump-cat="wearables">
            <div class="collection-img-wrap">
              <img src="<?= e(appUrl('uploads/products/watch-lifestyle.jpg')) ?>" alt="Wearables">
            </div>
            <div class="collection-tile-foot">
              <strong><?= e(t('cat_wearables')) ?></strong>
              <span class="collection-arrow"><?= $en ? '→' : '←' ?></span>
            </div>
          </button>
        </div>
      </div>
    </section>

    <!-- 5. Dual-Image Hover-Swap Product Showcase (Zero Public Prices!) -->
    <section class="section-pad" id="catalog">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill"><?= e(t('catalog_eyebrow')) ?></div>
          <h2><?= e(t('catalog_title')) ?></h2>
        </div>

        <div class="catalog-toolbar">
          <div class="filter-pills" id="catalogFilterPills">
            <button type="button" class="filter-pill active" data-cat="all"><?= e(t('cat_all')) ?></button>
            <button type="button" class="filter-pill" data-cat="laptops"><?= e(t('cat_laptops')) ?></button>
            <button type="button" class="filter-pill" data-cat="mobiles"><?= e(t('cat_mobiles')) ?></button>
            <button type="button" class="filter-pill" data-cat="accessories"><?= e(t('cat_accessories')) ?></button>
            <button type="button" class="filter-pill" data-cat="wearables"><?= e(t('cat_wearables')) ?></button>
          </div>

          <div class="catalog-search-box">
            <input type="search" id="catalogInlineSearch" placeholder="<?= e(t('catalog_search_ph')) ?>">
          </div>
        </div>

        <div class="products-lookbook-grid" id="publicProductsGrid">
          <?php foreach ($publicProducts as $idx => $prod):
            $hasSec = $prod['img_secondary'] !== '';
            $waLink = 'https://wa.me/' . $primaryWa . '?text=' . rawurlencode(t('wa_inquiry_msg') . $prod['title']);
          ?>
            <article
              class="lookbook-product-card <?= $hasSec ? 'has-secondary' : '' ?>"
              data-category="<?= e($prod['category']) ?>"
              data-search="<?= e(mb_strtolower($prod['title'] . ' ' . $prod['brand'] . ' ' . $prod['specs'])) ?>"
              data-pindex="<?= $idx ?>"
            >
              <div class="product-media-stage js-open-quickview" data-pindex="<?= $idx ?>">
                <div class="card-badges-top">
                  <span class="pill-tag"><?= e($prod['condition_label']) ?></span>
                  <?php if ($prod['is_featured']): ?>
                    <span class="pill-tag featured">★ <?= e(t('badge_featured')) ?></span>
                  <?php endif; ?>
                </div>

                <img class="img-primary" src="<?= e($prod['img_primary']) ?>" alt="<?= e($prod['title']) ?>" loading="lazy">
                <?php if ($hasSec): ?>
                  <img class="img-secondary" src="<?= e($prod['img_secondary']) ?>" alt="<?= e($prod['title']) ?> lifestyle" loading="lazy">
                <?php endif; ?>

                <button type="button" class="quick-view-chip js-open-quickview" data-pindex="<?= $idx ?>">
                  <?= e(t('btn_quick_view')) ?>
                </button>
              </div>

              <div class="product-body">
                <div class="product-meta-line">
                  <span><?= e($prod['category_label']) ?></span>
                  <?php if ($prod['brand'] !== ''): ?>
                    <span><?= e($prod['brand']) ?></span>
                  <?php endif; ?>
                </div>
                <h3><?= e($prod['title']) ?></h3>
                <p class="product-specs-line"><?= e($prod['specs']) ?></p>

                <div class="product-actions-foot">
                  <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn btn-teal">
                    💬 <?= e(t('btn_inquire_wa')) ?>
                  </a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div id="emptyCatalogNotice" style="display:<?= empty($publicProducts) ? 'block' : 'none' ?>;text-align:center;padding:48px 20px;background:var(--bg-ivory);border-radius:24px;border:1px solid var(--line-soft);margin-top:20px;">
          <p style="margin:0 0 16px;font-weight:700;color:var(--ink-heading);"><?= e(t('empty_catalog')) ?></p>
          <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-teal">WhatsApp</a>
        </div>
      </div>
    </section>

    <!-- 6. Kinetic Editorial Divider Marquee -->
    <div class="marquee-bar marquee-divider" aria-hidden="true">
      <div class="marquee-track">
        <?php for ($loop = 0; $loop < 3; $loop++): ?>
          <span class="marquee-item">ASLY TECH STORE <span class="marquee-dot"></span></span>
          <span class="marquee-item"><?= e(t('marquee_3')) ?> <span class="marquee-dot"></span></span>
          <span class="marquee-item"><?= e(t('marquee_2')) ?> <span class="marquee-dot"></span></span>
        <?php endfor; ?>
      </div>
    </div>

    <!-- 7. 3-Pillar Brand Story & Stats Ribbon ("THE IDEA" — getbouga Editorial Rhythm) -->
    <section class="section-pad story-band" id="story">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill"><?= e(t('pillars_eyebrow')) ?></div>
          <h2><?= e($displayBrandTitle) ?></h2>
          <p><?= e($foundersLine) ?></p>
        </div>

        <div class="pillars-grid">
          <div class="pillar-card">
            <span class="pillar-tag"><?= e(t('pillar_1_tag')) ?></span>
            <h3><?= e(t('pillar_1_title')) ?></h3>
            <p><?= e(t('pillar_1_desc')) ?></p>
          </div>
          <div class="pillar-card">
            <span class="pillar-tag"><?= e(t('pillar_2_tag')) ?></span>
            <h3><?= e(t('pillar_2_title')) ?></h3>
            <p><?= e(t('pillar_2_desc')) ?></p>
          </div>
          <div class="pillar-card">
            <span class="pillar-tag"><?= e(t('pillar_3_tag')) ?></span>
            <h3><?= e(t('pillar_3_title')) ?></h3>
            <p><?= e(t('pillar_3_desc')) ?></p>
          </div>
        </div>

        <div class="stats-ribbon">
          <div class="stat-box">
            <strong><?= e(t('stat_1_num')) ?></strong>
            <span><?= e(t('stat_1_lbl')) ?></span>
          </div>
          <div class="stat-box">
            <strong><?= e(t('stat_2_num')) ?></strong>
            <span><?= e(t('stat_2_lbl')) ?></span>
          </div>
          <div class="stat-box">
            <strong><?= e(t('stat_3_num')) ?></strong>
            <span><?= e(t('stat_3_lbl')) ?></span>
          </div>
        </div>
      </div>
    </section>

    <!-- 8. Sunlit Interactive QR Repair Lab Section -->
    <section class="section-pad" id="repair-lab">
      <div class="container">
        <div class="lab-split-card">
          <div class="lab-photo-box">
            <img src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg')) ?>" alt="ASLY Sunlit Diagnostic Lab" loading="lazy">
          </div>

          <div class="lab-form-box">
            <div class="eyebrow-pill">🔬 <?= e(t('lab_eyebrow')) ?></div>
            <h2><?= e(t('lab_title')) ?></h2>
            <p><?= e(t('lab_desc')) ?></p>

            <form method="get" action="<?= e(appUrl('track.php')) ?>" class="track-mini-form">
              <input type="text" name="code" placeholder="<?= e(t('lab_input_ph')) ?>" required>
              <button type="submit" class="btn btn-teal"><?= e(t('lab_btn_track')) ?></button>
            </form>

            <div style="margin-top:14px;">
              <a href="<?= e(appUrl('track.php?code=MNT-2026-101')) ?>" style="font-size:0.84rem;font-weight:800;color:var(--asly-teal-600);text-decoration:underline;">
                ✨ <?= e(t('lab_try_demo')) ?>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 9. "In Use — In Our Real Life" Multi-Photo Lookbook Gallery -->
    <section class="section-pad" id="real-life" style="background:var(--bg-ivory);border-top:1px solid var(--line-soft);border-bottom:1px solid var(--line-soft);">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill">📸 <?= e(t('gallery_eyebrow')) ?></div>
          <h2><?= e(t('gallery_title')) ?></h2>
          <p><?= e(t('gallery_sub')) ?></p>
        </div>

        <div class="irl-gallery-grid">
          <figure class="irl-photo-item span-wide span-tall js-lightbox-trigger" data-src="<?= e(appUrl('assets/images/lookbook/hero-1.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('assets/images/lookbook/hero-1.jpg')) ?>" alt="<?= e(t('gal_cap_1')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_1')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('assets/images/lookbook/hero-2.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('assets/images/lookbook/hero-2.jpg')) ?>" alt="<?= e(t('gal_cap_2')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_2')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('uploads/products/headphones-lifestyle.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('uploads/products/headphones-lifestyle.jpg')) ?>" alt="<?= e(t('gal_cap_4')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_4')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('uploads/products/laptop-lifestyle.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('uploads/products/laptop-lifestyle.jpg')) ?>" alt="<?= e(t('gal_cap_5')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_5')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('uploads/products/mobile-lifestyle.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('uploads/products/mobile-lifestyle.jpg')) ?>" alt="<?= e(t('gal_cap_6')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_6')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item span-wide js-lightbox-trigger" data-src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg')) ?>" alt="<?= e(t('gal_cap_3')) ?>" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('gal_cap_3')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('uploads/products/watch-lifestyle.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('uploads/products/watch-lifestyle.jpg')) ?>" alt="Starlight Watch" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('cat_wearables')) ?></figcaption>
          </figure>

          <figure class="irl-photo-item js-lightbox-trigger" data-src="<?= e(appUrl('uploads/products/earbuds-lifestyle.jpg')) ?>" style="margin:0;">
            <img src="<?= e(appUrl('uploads/products/earbuds-lifestyle.jpg')) ?>" alt="Wireless Audio" loading="lazy">
            <figcaption class="irl-caption"><?= e(t('cat_accessories')) ?></figcaption>
          </figure>
        </div>
      </div>
    </section>

    <!-- 10. Editorial Accordion FAQ Section -->
    <section class="section-pad" id="faq">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill"><?= e(t('faq_eyebrow')) ?></div>
          <h2><?= e(t('faq_title')) ?></h2>
        </div>

        <div class="faq-wrap">
          <details class="faq-item" open>
            <summary><span><?= e(t('faq_q1')) ?></span> <span class="faq-plus">+</span></summary>
            <div class="faq-answer"><?= e(t('faq_a1')) ?></div>
          </details>
          <details class="faq-item">
            <summary><span><?= e(t('faq_q2')) ?></span> <span class="faq-plus">+</span></summary>
            <div class="faq-answer"><?= e(t('faq_a2')) ?></div>
          </details>
          <details class="faq-item">
            <summary><span><?= e(t('faq_q3')) ?></span> <span class="faq-plus">+</span></summary>
            <div class="faq-answer"><?= e(t('faq_a3')) ?></div>
          </details>
          <details class="faq-item">
            <summary><span><?= e(t('faq_q4')) ?></span> <span class="faq-plus">+</span></summary>
            <div class="faq-answer"><?= e(t('faq_a4')) ?></div>
          </details>
        </div>
      </div>
    </section>

    <!-- 11. Dynamic Contact Channels (Managed from Admin Dashboard) -->
    <section class="section-pad" id="contact" style="padding-top:20px;">
      <div class="container">
        <div class="section-head">
          <div class="eyebrow-pill"><?= e(t('contact_eyebrow')) ?></div>
          <h2><?= e(t('contact_title')) ?></h2>
          <p>📍 <?= e($addressText) ?> &nbsp;•&nbsp; 🕒 <?= e($hoursText) ?></p>
          <?php if ($mapsUrl !== ''): ?>
            <div style="margin-top:14px;">
              <a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" class="btn btn-soft btn-sm">🗺️ <?= e(t('contact_map_btn')) ?></a>
            </div>
          <?php endif; ?>
        </div>

        <div class="channels-grid">
          <?php foreach ($channels as $ch):
            $type = (string)$ch['channel_type'];
            $icon = $channelIcons[$type] ?? '🔗';
            $href = trim((string)($ch['url'] ?? '')) ?: '#contact';
            $labelText = translateChannelLabel((string)$ch['label'], $type);
          ?>
            <a
              href="<?= e($href) ?>"
              <?= strpos($href, 'http') === 0 ? 'target="_blank" rel="noopener"' : '' ?>
              class="channel-card <?= $type === 'whatsapp' ? 'is-whatsapp' : '' ?>"
            >
              <div class="channel-icon"><?= $icon ?></div>
              <div>
                <div style="font-size:0.8rem;color:var(--ink-muted);font-weight:700;"><?= e($labelText) ?></div>
                <strong style="font-size:1.05rem;color:var(--ink-heading);direction:ltr;display:inline-block;"><?= e((string)$ch['value']) ?></strong>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  </main>

  <!-- 12. Pure Bright Editorial Footer (Zero Dark Colors!) -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-top">
        <div class="footer-brand-col">
          <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store">
          <p style="margin:0 0 10px;font-weight:700;color:var(--ink-heading);"><?= e($tagline) ?></p>
          <p style="margin:0;font-size:0.86rem;color:var(--ink-muted);"><?= e($foundersLine) ?></p>
        </div>

        <div style="display:flex;gap:36px;flex-wrap:wrap;">
          <div style="display:flex;flex-direction:column;gap:8px;font-size:0.9rem;font-weight:700;">
            <a href="#collections"><?= e(t('nav_collections')) ?></a>
            <a href="#catalog"><?= e(t('nav_catalog')) ?></a>
            <a href="#repair-lab"><?= e(t('nav_lab')) ?></a>
            <a href="#faq"><?= e(t('nav_faq')) ?></a>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;font-size:0.9rem;font-weight:700;">
            <a href="<?= e(appUrl('track.php')) ?>">🛠️ <?= e(t('nav_track_btn')) ?></a>
            <a href="<?= e(langSwitchUrl($otherLang)) ?>">🌐 <?= e(t('lang_switch_label')) ?></a>
            <a href="<?= e(appUrl('admin/login.php')) ?>" style="color:var(--asly-teal-600);">🔐 <?= e(t('nav_staff_portal')) ?></a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© <?= date('Y') ?> <?= e($storeNameEn) ?> | <?= e($storeNameAr) ?> — <?= e(t('footer_rights')) ?>.</span>
        <span style="direction:ltr;font-weight:800;color:var(--asly-teal-600);">📞 <?= e($primaryPhone) ?></span>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Concierge Button -->
  <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="floating-wa">
    💬 <span>WhatsApp</span>
  </a>

  <!-- Product Quick-View Modal (Dual Image Switcher + Zero Prices) -->
  <div class="bright-backdrop" id="quickViewModal">
    <div class="quickview-dialog">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <span class="eyebrow-pill" id="qvCategory" style="margin:0;"></span>
        <button type="button" class="icon-pill-btn" id="btnCloseQuickView">✕ <?= e(t('close')) ?></button>
      </div>
      <div class="qv-grid">
        <div>
          <img src="" alt="" id="qvMainImg" class="qv-main-img">
          <div class="qv-thumbs" id="qvThumbs"></div>
        </div>
        <div>
          <div id="qvCondition" style="font-size:0.8rem;font-weight:800;color:var(--asly-gold-600);margin-bottom:6px;"></div>
          <h2 id="qvTitle" style="margin:0 0 12px;color:var(--ink-heading);font-size:1.55rem;"></h2>
          <div id="qvSpecs" style="padding:12px 16px;background:var(--bg-aqua-soft);border-radius:14px;font-size:0.88rem;font-weight:700;color:var(--ink-heading);margin-bottom:14px;"></div>
          <p id="qvDesc" style="margin:0 0 16px;color:var(--ink-body);font-size:0.94rem;"></p>
          <div id="qvWarranty" style="font-size:0.84rem;color:var(--asly-teal-600);font-weight:800;margin-bottom:22px;"></div>
          <a href="#" target="_blank" rel="noopener" id="qvWaBtn" class="btn btn-teal" style="width:100%;">
            💬 <?= e(t('btn_inquire_wa')) ?>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Live Search Modal -->
  <div class="bright-backdrop" id="searchModal">
    <div class="quickview-dialog" style="max-width:680px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <strong style="font-size:1.15rem;color:var(--ink-heading);">🔍 <?= e(t('search_drawer_title')) ?></strong>
        <button type="button" class="icon-pill-btn" id="btnCloseSearch">✕ <?= e(t('close')) ?></button>
      </div>
      <input type="search" id="modalSearchInput" placeholder="<?= e(t('catalog_search_ph')) ?>" style="width:100%;padding:14px 20px;border-radius:999px;border:1.5px solid var(--asly-teal-400);font-family:inherit;font-size:1rem;margin-bottom:18px;">
      <div id="modalSearchResults" style="display:flex;flex-direction:column;gap:10px;max-height:380px;overflow-y:auto;"></div>
    </div>
  </div>

  <!-- Bright Gallery Lightbox Modal -->
  <div class="bright-backdrop" id="lightboxModal">
    <div class="quickview-dialog" style="max-width:860px;text-align:center;padding:18px;">
      <div style="display:flex;justify-content:flex-end;margin-bottom:10px;">
        <button type="button" class="icon-pill-btn" id="btnCloseLightbox">✕ <?= e(t('close')) ?></button>
      </div>
      <img src="" alt="" id="lightboxImg" style="width:100%;max-height:72vh;object-fit:contain;border-radius:20px;">
    </div>
  </div>

  <script>
    window.ASLY_PUBLIC = {
      products: <?= json_encode($publicProducts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      waNumber: <?= json_encode($primaryWa) ?>,
      waPrefix: <?= json_encode(t('wa_inquiry_msg'), JSON_UNESCAPED_UNICODE) ?>,
      inquireLabel: <?= json_encode(t('btn_inquire_wa'), JSON_UNESCAPED_UNICODE) ?>,
      emptyText: <?= json_encode(t('empty_catalog'), JSON_UNESCAPED_UNICODE) ?>
    };
  </script>
  <script src="<?= e(appUrl('assets/js/landing.js')) ?>"></script>
</body>
</html>
