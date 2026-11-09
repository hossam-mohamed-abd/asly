<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/seo_meta.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$channels = getWebsiteDisplayChannels($pdo, $settings);
$socialPlatforms = getSocialPlatforms($settings);

$en = isEn();
$otherLang = $en ? 'ar' : 'en';

$storeNameAr = $settings['store_name_ar'] ?? 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?? 'ASLY TECH STORE';
$tagline = $en
    ? (($settings['store_tagline_en'] ?? '') !== '' ? $settings['store_tagline_en'] : 'Engineering-Led Technology Studio & Live QR-Tracked Precision Repair Lab')
    : (($settings['store_tagline'] ?? '') !== '' ? $settings['store_tagline'] : 'المركز المتكامل للتقنية الأصلية ومعمل الصيانة الذكي بتتبع الـ QR');
$founders = $en
    ? (($settings['founders_line_en'] ?? '') !== '' ? $settings['founders_line_en'] : 'Supervised by Eng. Hossam Mohamed & Eng. Ramadan Sallam')
    : (($settings['founders_line'] ?? '') !== '' ? $settings['founders_line'] : 'إشراف وإدارة: م. حسام محمد • م. رمضان سلام');
$aboutStory = $en
    ? (($settings['about_story_en'] ?? '') !== '' ? $settings['about_story_en'] : 'ASLY TECH STORE is an engineering-first technology studio and precision diagnostic lab built to set an uncompromising standard for hardware authenticity and transparent QR repair tracking.')
    : (($settings['about_story_ar'] ?? '') !== '' ? $settings['about_story_ar'] : 'أصلي تك ستور هو استوديو هندسي ومعمل فحص وصيانة دقيقة تأسس ليضع معياراً حقيقياً للثقة والأصالة، مع توثيق لحظي لكل مرحلة صيانة عبر نظام QR تفاعلي يحمي خصوصيتك.');
$address = $en
    ? (($settings['store_address_en'] ?? '') !== '' ? $settings['store_address_en'] : ($settings['store_address'] ?? 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum'))
    : ($settings['store_address'] ?? 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا');
$hours = $en
    ? (($settings['working_hours_en'] ?? '') !== '' ? $settings['working_hours_en'] : ($settings['working_hours'] ?? 'Daily 11:00 AM – 11:00 PM'))
    : ($settings['working_hours'] ?? 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً');
$primaryPhone = trim((string)($settings['primary_phone'] ?? ''));
$secondaryPhone = trim((string)($settings['secondary_phone'] ?? ''));
$primaryWa = normalizeWhatsappNumber($settings['primary_whatsapp'] ?? '');
$primaryEmail = trim((string)($settings['primary_email'] ?? ''));
$mapsUrl = trim((string)($settings['google_maps_url'] ?? ''));
$lookbookStamp = (int)(@max(array_map('filemtime', (array)@glob(__DIR__ . '/assets/images/lookbook/*.jpg'))) ?: 0);
$assetVer = (string)max(
    (int)@filemtime(__DIR__ . '/assets/css/landing.css'),
    (int)@filemtime(__DIR__ . '/assets/js/landing.js'),
    $lookbookStamp,
    20261120
);
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= renderPublicSeoHead($settings, $socialPlatforms, ['page' => 'home']) ?>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="apple-touch-icon" href="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css?v=' . $assetVer)) ?>">
</head>
<body class="ambient-canvas">

  <div class="ambient-orbs-layer" aria-hidden="true">
    <div class="geo-orb geo-orb-aqua" style="top: -120px; inset-inline-end: -140px;"></div>
    <div class="geo-orb geo-orb-gold" style="top: 380px; inset-inline-start: -180px;"></div>
  </div>

  <header class="nav-shell">
    <nav class="nav-capsule" aria-label="Main navigation">
      <a href="<?= e(appUrl('index.php')) ?>" class="brand-mark">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY TECH STORE">
      </a>

      <div class="nav-links">
        <a href="#who-we-are" class="nav-link"><?= e(t('nav_about')) ?></a>
        <a href="#our-craft" class="nav-link"><?= e(t('nav_craft')) ?></a>
        <a href="#repair-lab" class="nav-link"><?= e(t('nav_lab')) ?></a>
        <a href="#social-universe" class="nav-link"><?= e(t('nav_social')) ?></a>
        <a href="#contact-studio" class="nav-link"><?= e(t('nav_contact')) ?></a>
        <a href="#faq" class="nav-link"><?= e(t('nav_faq')) ?></a>
      </div>

      <div class="nav-actions">
        <span class="lab-live-pill">
          <span class="pulse-dot"></span>
          <span><?= $en ? 'QR LAB LIVE' : 'معمل الصيانة متصل' ?></span>
        </span>

        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill" title="Switch Language">
          <i class="fa-solid fa-globe"></i>
          <span class="lang-full"><?= e(t('lang_switch_label')) ?></span>
          <span class="lang-short"><?= $en ? 'ع' : 'EN' ?></span>
        </a>

        <a href="#repair-lab" class="btn btn-teal btn-sm nav-track-cta" aria-label="<?= e(t('nav_track_btn')) ?>" title="<?= e(t('nav_track_btn')) ?>">
          <i class="fa-solid fa-qrcode"></i>
          <span><?= e(t('nav_track_btn')) ?></span>
        </a>

        <button type="button" class="mobile-menu-btn" id="openMobileDrawerBtn" aria-label="Menu">
          <i class="fa-solid fa-bars-staggered"></i>
        </button>
      </div>
    </nav>
  </header>

  
  <section class="hero-stage">
    
    <div class="geo-shape geo-ring" data-parallax="18" style="width: 92px; height: 92px; top: 8%; left: 46%;"></div>
    <div class="geo-shape geo-ring-dashed" data-parallax="-14" style="width: 150px; height: 150px; bottom: 8%; right: 44%;"></div>
    <div class="geo-shape geo-diamond" data-parallax="24" style="top: 18%; left: 6%;"></div>
    <div class="geo-shape geo-capsule" data-parallax="-20" style="width: 84px; height: 30px; bottom: 16%; left: 38%; transform: rotate(-22deg);"></div>

    <div class="container hero-grid">
      <div class="hero-copy" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 40ms;">
        <div class="eyebrow-chip">
          <i class="fa-solid fa-compass-drafting"></i>
          <span><?= e(t('hero_eyebrow')) ?></span>
        </div>

        <h1 class="hero-display" data-hero-write>
          <span><?= e(t('hero_title_1')) ?></span><br>
          <span class="gradient-word"><?= e(t('hero_title_2')) ?></span><br>
          <span class="stroke-word"><?= e(t('hero_title_3')) ?></span>
        </h1>

        <p class="hero-lead" data-hero-write data-hero-delay="220"><?= e(t('hero_subtitle')) ?></p>

        <div class="hero-cta-cluster">
          <a href="#repair-lab" class="btn btn-teal">
            <i class="fa-solid fa-qrcode"></i>
            <span><?= e(t('hero_cta_track')) ?></span>
          </a>
          <a href="#who-we-are" class="btn btn-outline">
            <i class="fa-solid fa-layer-group"></i>
            <span><?= e(t('hero_cta_who')) ?></span>
          </a>
        </div>

        <?php if (!empty($socialPlatforms)): ?>
        <div class="hero-social-strip">
          <span class="hero-social-lbl">
            <i class="fa-solid fa-share-nodes" style="color: var(--teal-deep); margin-inline-end: 6px;"></i>
            <?= e(t('hero_cta_social')) ?>:
          </span>
          <?php foreach ($socialPlatforms as $sp): ?>
            <a href="<?= e($sp['url']) ?>" target="_blank" rel="noopener" class="social-mini-orb" title="<?= e($sp['name']) ?>">
              <i class="<?= e($sp['icon']) ?>"></i>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="collage-stage">
        <div class="orbital-stamp" aria-hidden="true" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 140ms;">
          <svg viewBox="0 0 120 120">
            <defs>
              <path id="aslyCirclePath" d="M 60, 60 m -44, 0 a 44,44 0 1,1 88,0 a 44,44 0 1,1 -88,0"/>
            </defs>
            <text>
              <textPath href="#aslyCirclePath">ASLY TECH STORE • AUTHENTIC LAB •</textPath>
            </text>
          </svg>
          <div class="orbital-stamp-core">
            <i class="fa-solid fa-microchip"></i>
          </div>
        </div>

        <div class="collage-main-arch" data-parallax="-10" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 80ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/product-flagship-phone.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Flagship Smartphone On Display Inside ASLY Tech Store' : 'هاتف ذكي رائد معروض على قاعدة داخل معرض أصلي تك ستور') ?>" fetchpriority="high" decoding="async" data-focus="center 30%">
        </div>

        <div class="collage-sub-chamfer" data-parallax="16" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 220ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/showroom-product-wall.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Illuminated Wall Of Phones, Watches And Earbuds At ASLY' : 'جدار عرض مضيء بالهواتف والساعات والسماعات داخل أصلي تك ستور') ?>" loading="lazy" decoding="async" data-focus="center 40%">
        </div>

        <div class="collage-top-pill" data-parallax="-18" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 160ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/product-repair-macro.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Board-Level Repair Under The Microscope At ASLY Lab' : 'صيانة دقيقة على مستوى البوردة باستخدام الميكروسكوب في معمل أصلي') ?>" loading="lazy" decoding="async" data-focus="center 50%">
        </div>

        <div class="collage-mid-orb" data-parallax="12" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 260ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/product-audio-still.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Original Headphones, Earbuds And Smartwatch On Display' : 'هيدفونات وإيربودز وساعة ذكية أصلية معروضة داخل المعرض') ?>" loading="lazy" decoding="async" data-focus="center 45%">
        </div>

        <div class="floating-chip floating-chip-end" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 310ms;">
          <div class="chip-icon gold">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div class="chip-title"><?= $en ? '7-Stage Live QR Lab' : 'تتبع صيانة تفاعلي ٧ مراحل' ?></div>
            <div class="chip-sub"><?= $en ? '100% Privacy Auto-Wipe' : 'حماية كاملة ومسح تلقائي للخصوصية' ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  
  <div class="kinetic-ribbons-wrap" aria-hidden="true">
    <div class="marquee-ribbon ribbon-aqua">
      <div class="marquee-track">
        <?php for ($i = 0; $i < 3; $i++): ?>
          <span class="marquee-node"><i class="fa-solid fa-microchip"></i> <?= e(t('marquee_1')) ?></span>
          <span class="marquee-node"><i class="fa-solid fa-qrcode"></i> <?= e(t('marquee_2')) ?></span>
          <span class="marquee-node"><i class="fa-solid fa-screwdriver-wrench"></i> <?= e(t('marquee_3')) ?></span>
          <span class="marquee-node"><i class="fa-solid fa-shield-halved"></i> <?= e(t('marquee_4')) ?></span>
        <?php endfor; ?>
      </div>
    </div>

    <div class="marquee-ribbon ribbon-gold">
      <div class="marquee-track">
        <?php for ($i = 0; $i < 3; $i++): ?>
          <span class="marquee-node"><i class="fa-solid fa-compass-drafting"></i> ASLY TECH STORE</span>
          <span class="marquee-node"><i class="fa-solid fa-laptop-code"></i> <?= $en ? 'CERTIFIED WORKSTATIONS' : 'فحص هندسي معتمد' ?></span>
          <span class="marquee-node"><i class="fa-solid fa-microscope"></i> <?= $en ? 'PRECISION DIAGNOSTICS' : 'تشخيص دقيق على مستوى البوردة' ?></span>
          <span class="marquee-node"><i class="fa-solid fa-award"></i> <?= $en ? 'AUTHENTICITY GUARANTEED' : 'ضمان الأصالة والثقة' ?></span>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  
  <section id="in-store" class="section-pad section-ivory ps-section">
    <div class="container">
      <div class="ps-head ps-head--tight reveal-up">
        <div class="ps-head-copy">
          <div class="eyebrow-chip">
            <i class="fa-solid fa-layer-group"></i>
            <span><?= e($en ? 'WHAT WE HANDLE' : 'اللي بنشتغل فيه') ?></span>
          </div>
          <h2 class="section-title" data-scroll-write><?= e($en ? 'The Categories Behind The ASLY Counter' : 'الأقسام اللي بتشوفها في أصلي') ?></h2>
        </div>
        <div class="ps-filters" data-ps-filters>
          <button type="button" class="ps-chip is-on" data-ps-filter="all"><?= e($en ? 'All' : 'الكل') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="phones"><?= e($en ? 'Phones' : 'هواتف') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="laptops"><?= e($en ? 'Laptops' : 'لابتوب') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="tablets"><?= e($en ? 'Tablets' : 'تابلت') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="audio"><?= e($en ? 'Audio' : 'صوتيات') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="wearables"><?= e($en ? 'Watches' : 'ساعات') ?></button>
          <button type="button" class="ps-chip" data-ps-filter="accessories"><?= e($en ? 'Accessories' : 'ملحقات') ?></button>
        </div>
      </div>

      <div class="ps-grid" data-ps-grid>
        <article class="ps-tile ps-tile--tall" data-ps-tile data-cat="phones" style="--ps-i:0;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-phone.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Flagship Smartphones' : 'هواتف رائدة أصلية') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 45%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'PHONES' : 'هواتف') ?></span>
            <strong><?= e($en ? 'Flagship Smartphones' : 'هواتف رائدة أصلية') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--std" data-ps-tile data-cat="wearables" style="--ps-i:1;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-watch.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Smartwatch Series' : 'ساعة ذكية') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 45%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'WATCHES' : 'ساعات') ?></span>
            <strong><?= e($en ? 'Smartwatch Series' : 'ساعة ذكية') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--big" data-ps-tile data-cat="laptops" style="--ps-i:2;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-laptop.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Creator Laptops' : 'لابتوب للمبدعين') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 55%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'LAPTOPS' : 'لابتوب') ?></span>
            <strong><?= e($en ? 'Creator Laptops' : 'لابتوب للمبدعين') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--tall" data-ps-tile data-cat="audio" style="--ps-i:3;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-headphones.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Over-Ear Headphones' : 'هيدفونات أوفر إير') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'AUDIO' : 'صوتيات') ?></span>
            <strong><?= e($en ? 'Over-Ear Headphones' : 'هيدفونات أوفر إير') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--std" data-ps-tile data-cat="audio" style="--ps-i:4;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-earbuds.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'True Wireless Earbuds' : 'إيربودز لاسلكية') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'AUDIO' : 'صوتيات') ?></span>
            <strong><?= e($en ? 'True Wireless Earbuds' : 'إيربودز لاسلكية') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--tall" data-ps-tile data-cat="tablets" style="--ps-i:5;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-tablet.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Tablets & Stylus' : 'تابلت وقلم رقمي') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 45%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'TABLETS' : 'تابلت') ?></span>
            <strong><?= e($en ? 'Tablets & Stylus' : 'تابلت وقلم رقمي') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--std" data-ps-tile data-cat="accessories" style="--ps-i:6;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-charger.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Charging & Cables' : 'شواحن وكابلات') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'ACCESSORIES' : 'ملحقات') ?></span>
            <strong><?= e($en ? 'Charging & Cables' : 'شواحن وكابلات') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--tall" data-ps-tile data-cat="audio" style="--ps-i:7;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-speaker.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Portable Speakers' : 'سماعات محمولة') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'AUDIO' : 'صوتيات') ?></span>
            <strong><?= e($en ? 'Portable Speakers' : 'سماعات محمولة') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--big" data-ps-tile data-cat="accessories" style="--ps-i:8;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-keyboard.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Keyboards & Mice' : 'كيبورد وماوس') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'ACCESSORIES' : 'ملحقات') ?></span>
            <strong><?= e($en ? 'Keyboards & Mice' : 'كيبورد وماوس') ?></strong>
          </div>
        </article>
        <article class="ps-tile ps-tile--std" data-ps-tile data-cat="accessories" style="--ps-i:9;">
          <div class="ps-tile-media">
            <img src="<?= e(appUrl('assets/images/lookbook/mosaic-gamepad.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Gaming Controllers' : 'دراعات ألعاب') ?>" class="ps-tile-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="ps-tile-veil"></span>
            <span class="ps-ring" aria-hidden="true"></span>
          </div>
          <div class="ps-tile-label">
            <span class="ps-tile-tag"><?= e($en ? 'ACCESSORIES' : 'ملحقات') ?></span>
            <strong><?= e($en ? 'Gaming Controllers' : 'دراعات ألعاب') ?></strong>
          </div>
        </article>
      </div>

    </div>
  </section>

  <section id="who-we-are" class="section-pad">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-fingerprint"></i>
            <span><?= e(t('who_eyebrow')) ?></span>
          </div>
          <h2 class="section-title" data-scroll-write><?= e(t('who_title')) ?></h2>
        </div>
        <p class="section-sub" data-scroll-write><?= e($tagline) ?></p>
      </div>

      <div class="who-mosaic">
        <article class="mosaic-card mosaic-story" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 0ms;">
          <span class="hollow-num">ASLY</span>
          <div>
            <div class="pillar-icon-orb">
              <i class="fa-solid fa-gem"></i>
            </div>
            <span class="pillar-tag"><?= $en ? 'OUR MANIFESTO // STORY' : 'قصتنا وهويتنا // ASLY' ?></span>
            <h3 style="font-size: 1.55rem; margin-bottom: 14px;" data-scroll-write>
              <?= e($en ? $storeNameEn : "{$storeNameAr} — {$storeNameEn}") ?>
            </h3>
            <p style="font-size: 1.02rem; line-height: 1.85;" data-scroll-write><?= e($aboutStory) ?></p>
          </div>

          <div class="founders-ribbon">
            <i class="fa-solid fa-user-check"></i>
            <span><?= e($founders) ?></span>
          </div>
        </article>

        <div class="mosaic-card mosaic-photo-tall" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 110ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-team-portrait.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'ASLY Store Specialist With Devices On Display' : 'مختصة المعرض مع الأجهزة المعروضة أمامها') ?>" loading="lazy" decoding="async" data-focus="center 22%">
          <div class="mosaic-photo-caption">
            <span><i class="fa-solid fa-certificate" style="color: var(--teal-deep); margin-inline-end: 6px;"></i> <?= $en ? 'Authentic Hardware Culture' : 'ثقافة التقنية الأصلية' ?></span>
            <span style="font-family: var(--font-mono); color: var(--gold-deep);">100%</span>
          </div>
        </div>

        <figure class="mosaic-card mosaic-photo-landscape" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 60ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/showroom-display-wall.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Phones And Watches Display Wall Inside ASLY Showroom' : 'جدار عرض الهواتف والساعات داخل معرض أصلي تك ستور') ?>" loading="lazy" decoding="async" data-focus="center 45%">
          <figcaption class="mosaic-photo-caption">
            <span><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--teal-deep); margin-inline-end: 6px;"></i> <?= $en ? 'Signature Studio Presence' : 'حضور مميز داخل الاستوديو' ?></span>
            <span style="font-family: var(--font-mono); color: var(--gold-deep);">ASLY</span>
          </figcaption>
        </figure>

        <article class="mosaic-card mosaic-pillar" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 140ms;">
          <span class="hollow-num">01</span>
          <div class="pillar-icon-orb">
            <i class="fa-solid fa-microchip"></i>
          </div>
          <span class="pillar-tag"><?= e(t('pillar_1_tag')) ?></span>
          <h3 data-scroll-write><?= e(t('pillar_1_title')) ?></h3>
          <p data-scroll-write><?= e(t('pillar_1_desc')) ?></p>
        </article>

        <article class="mosaic-card mosaic-pillar" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 140ms;">
          <span class="hollow-num">02</span>
          <div class="pillar-icon-orb gold">
            <i class="fa-solid fa-microscope"></i>
          </div>
          <span class="pillar-tag" style="color: var(--gold-deep);"><?= e(t('pillar_2_tag')) ?></span>
          <h3 data-scroll-write><?= e(t('pillar_2_title')) ?></h3>
          <p data-scroll-write><?= e(t('pillar_2_desc')) ?></p>
        </article>

        <article class="mosaic-card mosaic-pillar mosaic-pillar-wide" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 210ms;">
          <span class="hollow-num">03</span>
          <div class="pillar-icon-orb">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <div class="mosaic-wide-body">
            <span class="pillar-tag"><?= e(t('pillar_3_tag')) ?></span>
            <h3 data-scroll-write><?= e(t('pillar_3_title')) ?></h3>
            <p data-scroll-write><?= e(t('pillar_3_desc')) ?></p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section id="our-craft" class="section-pad section-ivory">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-cubes"></i>
            <span><?= e(t('craft_eyebrow')) ?></span>
          </div>
          <h2 class="section-title" data-scroll-write><?= e(t('craft_title')) ?></h2>
        </div>
      </div>

      <div class="craft-deck" data-craft-deck>
        <article class="craft-card" data-craft-card data-index="01">
          <div class="craft-card-media">
            <img src="<?= e(appUrl('assets/images/lookbook/product-laptop-workstation.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Pro Laptop And Genuine Accessories On The Demo Desk' : 'لابتوب احترافي مع ملحقاته الأصلية على مكتب المعاينة') ?>" class="craft-card-img" loading="lazy" decoding="async" data-focus="center 42%">
            <span class="craft-card-wash"></span>
            <span class="craft-card-index">01</span>
            <span class="craft-card-badge"><i class="fa-solid fa-laptop-code"></i></span>
          </div>
          <div class="craft-card-body">
            <div class="craft-card-meta">
              <span class="craft-card-tag">LAPTOPS</span>
              <span class="craft-card-rule"></span>
            </div>
            <h3 data-scroll-write><?= e(t('craft_1_title')) ?></h3>
            <p><?= e(t('craft_1_sub')) ?></p>
            <span class="craft-card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
          </div>
        </article>
        <article class="craft-card" data-craft-card data-index="02">
          <div class="craft-card-media">
            <img src="<?= e(appUrl('assets/images/lookbook/product-ecosystem-flatlay.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Flagship Phones And Tablets Laid Out On Marble' : 'هواتف وتابلت رائدة معروضة على رخام المعرض') ?>" class="craft-card-img" loading="lazy" decoding="async" data-focus="center 50%">
            <span class="craft-card-wash"></span>
            <span class="craft-card-index">02</span>
            <span class="craft-card-badge"><i class="fa-solid fa-mobile-screen-button"></i></span>
          </div>
          <div class="craft-card-body">
            <div class="craft-card-meta">
              <span class="craft-card-tag">MOBILE</span>
              <span class="craft-card-rule"></span>
            </div>
            <h3 data-scroll-write><?= e(t('craft_2_title')) ?></h3>
            <p><?= e(t('craft_2_sub')) ?></p>
            <span class="craft-card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
          </div>
        </article>
        <article class="craft-card" data-craft-card data-index="03">
          <div class="craft-card-media">
            <img src="<?= e(appUrl('assets/images/lookbook/product-qr-ticket.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'QR Repair Ticket On The Reception Counter With Laptop' : 'إيصال صيانة برمز QR على مكتب الاستقبال مع اللابتوب') ?>" class="craft-card-img" loading="lazy" decoding="async" data-focus="center 45%">
            <span class="craft-card-wash"></span>
            <span class="craft-card-index">03</span>
            <span class="craft-card-badge"><i class="fa-solid fa-screwdriver-wrench"></i></span>
          </div>
          <div class="craft-card-body">
            <div class="craft-card-meta">
              <span class="craft-card-tag">QR REPAIR</span>
              <span class="craft-card-rule"></span>
            </div>
            <h3 data-scroll-write><?= e(t('craft_3_title')) ?></h3>
            <p><?= e(t('craft_3_sub')) ?></p>
            <span class="craft-card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
          </div>
        </article>
        <article class="craft-card" data-craft-card data-index="04">
          <div class="craft-card-media">
            <img src="<?= e(appUrl('assets/images/lookbook/product-smartwatch-macro.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Gold Smartwatch, Earbuds And Audio On Display' : 'ساعة ذهبية وإيربودز وصوتيات أصلية معروضة') ?>" class="craft-card-img" loading="lazy" decoding="async" data-focus="center 45%">
            <span class="craft-card-wash"></span>
            <span class="craft-card-index">04</span>
            <span class="craft-card-badge"><i class="fa-solid fa-headphones-simple"></i></span>
          </div>
          <div class="craft-card-body">
            <div class="craft-card-meta">
              <span class="craft-card-tag">WEARABLES</span>
              <span class="craft-card-rule"></span>
            </div>
            <h3 data-scroll-write><?= e(t('craft_4_title')) ?></h3>
            <p><?= e(t('craft_4_sub')) ?></p>
            <span class="craft-card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
          </div>
        </article>
        <span class="craft-deck-sheen" aria-hidden="true"></span>
      </div>

      <div class="craft-deck-rail" data-craft-rail>
        <span class="craft-deck-bar" data-craft-bar></span>
      </div>

      <div class="lookbook-showcase-grid">
        <figure class="lookbook-frame lookbook-wide" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 40ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-hands-on-preview.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Customer Testing A Smartphone On The Counter' : 'عميل بيجرب هاتف ذكي على الكاونتر') ?>" loading="lazy" decoding="async" data-focus="center 50%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge"><i class="fa-solid fa-hand-holding-heart"></i> <?= $en ? 'HANDS-ON PREVIEW' : 'معاينة على الطبيعة' ?></span>
            <strong data-scroll-write><?= $en ? 'Every Device On Display, Tested In Your Hands Before You Decide' : 'كل جهاز معروض أمامك ومُختبر بيدك قبل أن تقرر' ?></strong>
          </figcaption>
        </figure>

        <figure class="lookbook-frame lookbook-mid" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 120ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-audio-wearables.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Headphones And Smartwatch On Display In Store' : 'هيدفونات وساعة ذكية معروضة داخل المعرض') ?>" loading="lazy" decoding="async" data-focus="center 45%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge gold"><i class="fa-solid fa-headphones-simple"></i> <?= $en ? 'AUDIO & WEARABLES' : 'صوتيات وساعات' ?></span>
            <strong data-scroll-write><?= $en ? 'Original Headphones, Earbuds and Watches Ready to Try' : 'هيدفونات وإيربودز وساعات أصلية جاهزة للتجربة' ?></strong>
          </figcaption>
        </figure>

        <figure class="lookbook-frame lookbook-mid" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 60ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-sound-room.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Headphone Comparison Shelf In The Store' : 'رف مقارنة الهيدفونات داخل المعرض') ?>" loading="lazy" decoding="async" data-focus="center 45%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge"><i class="fa-solid fa-volume-high"></i> <?= $en ? 'SOUND ROOM' : 'زاوية الصوت' ?></span>
            <strong data-scroll-write><?= $en ? 'Compare Headphones and Speakers Side by Side In Store' : 'قارن بين الهيدفونات والسماعات جنباً إلى جنب داخل المعرض' ?></strong>
          </figcaption>
        </figure>

        <figure class="lookbook-frame lookbook-wide" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 140ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-service-desk.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Store Specialist Presenting Phones And Tablets' : 'مختصة بالمعرض بتعرض الهواتف والتابلت') ?>" loading="lazy" decoding="async" data-focus="center 55%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge gold"><i class="fa-solid fa-comments"></i> <?= $en ? 'PERSONAL SERVICE' : 'خدمة شخصية' ?></span>
            <strong data-scroll-write><?= $en ? 'Real Engineers Guide You Through Every Device Choice' : 'مهندسون حقيقيون يرشدونك في اختيار كل جهاز' ?></strong>
          </figcaption>
        </figure>

        <figure class="lookbook-frame lookbook-wide" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 70ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Precision Diagnostics Bench Inside ASLY Tech Lab' : 'معمل الفحص الهندسي الدقيق داخل أصلي تك ستور') ?>" loading="lazy" decoding="async" data-focus="center 38%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge"><i class="fa-solid fa-microscope"></i> <?= $en ? 'PRECISION BENCH' : 'معمل الفحص الدقيق' ?></span>
            <strong data-scroll-write><?= $en ? 'Micro-Level Board Diagnostics Under The Microscope' : 'تشخيص ميكروسكوبي دقيق على مستوى البوردة' ?></strong>
          </figcaption>
        </figure>

        <figure class="lookbook-frame lookbook-mid" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 150ms;">
          <img src="<?= e(appUrl('assets/images/lookbook/scene-categories-shelf.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Category Shelf With Every Device Type' : 'رف الأقسام بكل نوع جهاز') ?>" loading="lazy" decoding="async" data-focus="center 45%">
          <figcaption class="lookbook-caption">
            <span class="lookbook-badge gold"><i class="fa-solid fa-star"></i> <?= $en ? 'OUR SPECIALTY' : 'تخصصنا' ?></span>
            <strong data-scroll-write><?= $en ? 'The Categories We Work On, Shown In Their Best Light' : 'الأقسام اللي بنشتغل فيها معروضة في أفضل صورة' ?></strong>
          </figcaption>
        </figure>
      </div>
    </div>
  </section>

  
  <section id="repair-lab" class="section-pad">
    <div class="container">
      <div class="lab-console-shell">
        <div class="geo-orb geo-orb-aqua" style="bottom: -160px; inset-inline-end: -120px;"></div>

        <div class="lab-console-grid">
          <div data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 0ms;">
            <div class="eyebrow-chip" style="background: #ffffff;">
              <i class="fa-solid fa-satellite-dish"></i>
              <span><?= e(t('lab_eyebrow')) ?></span>
            </div>

            <h2 class="section-title" style="margin-bottom: 14px;" data-scroll-write><?= e(t('lab_title')) ?></h2>
            <p style="color: var(--ink-2); margin: 0; line-height: 1.8;" data-scroll-write><?= e(t('lab_desc')) ?></p>

            <form id="inlineTrackForm" class="lab-search-box" action="<?= e(appUrl('track.php')) ?>" method="get">
              <input type="hidden" name="lang" value="<?= e(currentLang()) ?>">
              <i class="fa-solid fa-ticket prefix-ico"></i>
              <input type="text" id="inlineTicketInput" name="code" placeholder="<?= e(t('lab_input_ph')) ?>" autocomplete="off" required>
              <button type="submit" class="btn btn-teal">
                <i class="fa-solid fa-bolt"></i>
                <span><?= e(t('lab_btn_instant')) ?></span>
              </button>
            </form>

            <div class="lab-quick-actions">
              <button type="button" class="btn btn-gold btn-sm" id="openLandingQrBtn">
                <i class="fa-solid fa-camera"></i>
                <span><?= e(t('lab_btn_scan_qr')) ?></span>
              </button>

              <a href="<?= e(appUrl('track.php?lang=' . currentLang())) ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span><?= e(t('lab_btn_open_portal')) ?></span>
              </a>
            </div>

            <div id="inlineTicketResult" class="inline-ticket-result" aria-live="polite"></div>
          </div>

          <div class="stage-constellation">
            <div class="lab-photo-banner span-full" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 20ms;">
              <img src="<?= e(appUrl('assets/images/lookbook/qr-reception-handover.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'QR Repair Receipt Handover At Reception' : 'تسليم إيصال الصيانة برمز QR من مكتب الاستقبال') ?>" loading="lazy" decoding="async" data-focus="center 20%">
              <div class="lab-photo-overlay">
                <i class="fa-solid fa-qrcode"></i>
                <span><?= $en ? 'ASLY QR Lab • Instant Live Receipt' : 'معمل أصلي • إيصال QR حي لكل جهاز' ?></span>
              </div>
            </div>

            <div class="constellation-node" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 60ms;">
              <div class="c-node-ico"><i class="fa-solid fa-clipboard-check"></i></div>
              <div>
                <div class="chip-title">01 • <?= $en ? 'Logged & QR Issued' : 'الاستلام وإصدار QR' ?></div>
                <div class="chip-sub"><?= $en ? 'Instant printed token' : 'إيصال ذكي خاص بجهازك' ?></div>
              </div>
            </div>

            <div class="constellation-node" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 100ms;">
              <div class="c-node-ico"><i class="fa-solid fa-microscope"></i></div>
              <div>
                <div class="chip-title">02 • <?= $en ? 'Deep Diagnostics' : 'الفحص والتشخيص' ?></div>
                <div class="chip-sub"><?= $en ? 'Board & thermal check' : 'تحديد دقيق للعطل' ?></div>
              </div>
            </div>

            <div class="constellation-node" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 160ms;">
              <div class="c-node-ico"><i class="fa-solid fa-screwdriver-wrench"></i></div>
              <div>
                <div class="chip-title">03 • <?= $en ? 'Precision Repair' : 'الإصلاح والاختبار' ?></div>
                <div class="chip-sub"><?= $en ? 'Two-way engineer chat' : 'ملاحظات متبادلة مع المهندس' ?></div>
              </div>
            </div>

            <div class="constellation-node" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 220ms;">
              <div class="c-node-ico"><i class="fa-solid fa-envelope-circle-check"></i></div>
              <div>
                <div class="chip-title">04 • <?= $en ? 'Live Email Alerts' : 'تنبيهات البريد الفورية' ?></div>
                <div class="chip-sub"><?= $en ? 'Every stage update' : 'إشعار مع كل مرحلة جديدة' ?></div>
              </div>
            </div>

            <div class="constellation-node span-full" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 280ms;">
              <div class="c-node-ico"><i class="fa-solid fa-user-lock"></i></div>
              <div>
                <div class="chip-title"><?= $en ? 'Automatic Privacy Wipe Upon Handover' : 'مسح تلقائي كامل لبيانات التذكرة فور التسليم' ?></div>
                <div class="chip-sub"><?= $en ? 'Zero personal data retained after your device leaves the lab' : 'خصوصيتك محمية ١٠٠٪ بعد استلامك للجهاز' ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="social-universe" class="section-pad section-ivory">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-hashtag"></i>
            <span><?= e(t('social_eyebrow')) ?></span>
          </div>
          <h2 class="section-title" data-scroll-write><?= e(t('social_title')) ?></h2>
        </div>
        <p class="section-sub" data-scroll-write><?= e(t('social_sub')) ?></p>
      </div>

      <div class="social-wall-grid">
        <?php foreach ($socialPlatforms as $spIdx => $sp): ?>
          <a href="<?= e($sp['url']) ?>" target="_blank" rel="noopener" class="social-geo-tile" data-accent="<?= e($sp['accent']) ?>" data-side="<?= ($spIdx % 2 === 0) ? ($en ? 'left' : 'right') : ($en ? 'right' : 'left') ?>" style="--side-delay: <?= (int)(($spIdx % 4) * 70) ?>ms;">
            <i class="<?= e($sp['icon']) ?> social-watermark" aria-hidden="true"></i>

            <div class="social-tile-head">
              <span class="social-badge-ico">
                <i class="<?= e($sp['icon']) ?>"></i>
              </span>
              <span class="social-arrow-orb">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
              </span>
            </div>

            <div class="social-tile-body">
              <h3 class="social-name"><?= e($sp['name']) ?></h3>
              <p class="social-sub"><?= e($sp['sub']) ?></p>
              <span class="social-handle-pill"><?= e($sp['handle']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="contact-studio" class="section-pad contact-stage">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-location-dot"></i>
            <span><?= e(t('contact_eyebrow')) ?></span>
          </div>
          <h2 class="section-title" data-scroll-write><?= e(t('contact_title')) ?></h2>
        </div>
        <p class="section-sub" data-scroll-write><?= e($en ? 'One studio for everything: visit the showroom, call the engineering desk, or send a WhatsApp message and we reply within minutes.' : 'استوديو واحد لكل شيء: زر المعرض، اتصل بمكتب المهندسين، أو أرسل رسالة واتساب وسنرد عليك في دقائق.') ?></p>
      </div>

      <div class="contact-mosaic">

        <article class="contact-hero-card" data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 0ms;">
          <div class="contact-hero-media">
            <img src="<?= e(appUrl('assets/images/lookbook/hero-2.jpg?v=' . $assetVer)) ?>" alt="<?= e($en ? 'Visit The ASLY Studio In Tersa, Sinnuris, Faiyum' : 'زيارة استوديو أصلي تك ستور في ترسا - سنورس - الفيوم') ?>" loading="lazy" decoding="async" data-focus="center 32%">
            <span class="contact-hero-veil" aria-hidden="true"></span>
            <div class="contact-hero-stamp">
              <span class="contact-live-dot" aria-hidden="true"></span>
              <span><?= $en ? 'STUDIO OPEN' : 'الاستوديو مفتوح الآن' ?></span>
            </div>
          </div>

          <div class="contact-hero-body">
            <span class="contact-mini-label"><?= $en ? 'VISIT THE STUDIO' : 'زيارة الاستوديو' ?></span>

            <div class="contact-detail-row">
              <span class="contact-detail-ico"><i class="fa-solid fa-map-location-dot"></i></span>
              <div>
                <span class="contact-detail-lbl"><?= e(t('contact_location')) ?></span>
                <strong class="contact-detail-val" data-scroll-write><?= e($address) ?></strong>
              </div>
            </div>

            <div class="contact-detail-row">
              <span class="contact-detail-ico"><i class="fa-solid fa-clock"></i></span>
              <div>
                <span class="contact-detail-lbl"><?= e(t('contact_hours')) ?></span>
                <strong class="contact-detail-val" data-scroll-write><?= e($hours) ?></strong>
              </div>
            </div>

            <?php if ($primaryEmail !== ''): ?>
            <div class="contact-detail-row">
              <span class="contact-detail-ico"><i class="fa-solid fa-envelope-open-text"></i></span>
              <div>
                <span class="contact-detail-lbl"><?= $en ? 'Official Email' : 'البريد الإلكتروني الرسمي' ?></span>
                <a class="contact-detail-val contact-mail" href="mailto:<?= e($primaryEmail) ?>"><?= e($primaryEmail) ?></a>
              </div>
            </div>
            <?php endif; ?>

            <div class="contact-hero-actions">
              <?php if ($mapsUrl !== ''): ?>
                <a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" class="btn btn-gold contact-act">
                  <i class="fa-solid fa-diamond-turn-right"></i>
                  <span><?= e(t('contact_map_btn')) ?></span>
                </a>
              <?php endif; ?>
              <?php if ($primaryWa !== ''): ?>
                <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-teal contact-act">
                  <i class="fa-brands fa-whatsapp"></i>
                  <span><?= $en ? 'WhatsApp Concierge' : 'تحدث عبر واتساب' ?></span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </article>

        <div class="contact-channels-panel" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: 90ms;">
          <div class="contact-panel-head">
            <span class="contact-panel-kicker"><?= $en ? 'DIRECT LINES' : 'خطوط التواصل المباشرة' ?></span>
            <span class="contact-panel-hint"><?= $en ? 'Tap any line to connect instantly' : 'اضغط على أي خط للتواصل الفوري' ?></span>
          </div>

          <div class="contact-line-list">
            <?php foreach ($channels as $chIdx => $ch):
                $type = (string)($ch['channel_type'] ?? 'phone');
                $faIco = getChannelFontAwesomeIcon($type);
                $href = trim((string)($ch['url'] ?? ''));
                if ($href === '') {
                    $href = $type === 'whatsapp'
                        ? ('https://wa.me/' . normalizeWhatsappNumber((string)$ch['value']))
                        : ('tel:' . preg_replace('/\s+/', '', (string)$ch['value']));
                }
                $lbl = translateChannelLabel((string)$ch['label'], $type);
                $isWa = $type === 'whatsapp';
                $actTxt = $isWa ? ($en ? 'Chat' : 'محادثة') : ($en ? 'Call' : 'اتصال');
            ?>
            <a href="<?= e($href) ?>" target="<?= str_starts_with($href, 'http') ? '_blank' : '_self' ?>" rel="noopener" class="contact-line" data-kind="<?= $isWa ? 'wa' : 'tel' ?>" data-side="<?= ($chIdx % 2 === 0) ? ($en ? 'right' : 'left') : ($en ? 'left' : 'right') ?>" style="--side-delay: <?= (int)(($chIdx % 5) * 55) ?>ms;">
              <span class="contact-line-ico"><i class="<?= e($faIco) ?>"></i></span>
              <span class="contact-line-text">
                <span class="contact-line-lbl"><?= e($lbl) ?></span>
                <span class="contact-line-num"><?= e((string)$ch['value']) ?></span>
              </span>
              <span class="contact-line-cta">
                <i class="fa-solid <?= $isWa ? 'fa-comment-dots' : 'fa-phone' ?>"></i>
                <em><?= e($actTxt) ?></em>
              </span>
            </a>
            <?php endforeach; ?>
          </div>

          <div class="contact-panel-foot">
            <span class="contact-risk-free"><i class="fa-solid fa-shield-halved"></i> <?= $en ? 'No bots • Real engineers reply' : 'بدون ردود آلية • مهندسون حقيقيون يردون' ?></span>
            <a class="contact-callout" href="#repair-lab">
              <i class="fa-solid fa-qrcode"></i>
              <span><?= $en ? 'Already have a receipt? Track it now' : 'لديك إيصال صيانة؟ تابع جهازك الآن' ?></span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section id="faq" class="section-pad section-ivory">
    <div class="container faq-asym-grid">
      <div data-side="<?= $en ? 'left' : 'right' ?>" style="--side-delay: 0ms;">
        <div class="eyebrow-chip">
          <i class="fa-solid fa-circle-question"></i>
          <span><?= e(t('faq_eyebrow')) ?></span>
        </div>
        <h2 class="section-title" style="margin-bottom: 16px;" data-scroll-write><?= e(t('faq_title')) ?></h2>
        <p style="color: var(--ink-2); margin: 0;" data-scroll-write><?= e($founders) ?></p>
      </div>

      <div class="faq-list">
        <?php for ($q = 1; $q <= 6; $q++):
            $ansText = $q === 4
                ? ($en
                    ? "{$storeNameEn} is located at {$address}. Studio hours: {$hours}."
                    : "يقع مقر {$storeNameAr} في {$address}. مواعيد استقبال العملاء: {$hours}.")
                : t("faq_a{$q}");
        ?>
          <div class="faq-item <?= $q === 1 ? 'open' : '' ?>" data-side="<?= $en ? 'right' : 'left' ?>" style="--side-delay: <?= (int)(($q - 1) * 55) ?>ms;">
            <button type="button" class="faq-trigger">
              <span><?= e(t("faq_q{$q}")) ?></span>
              <span class="faq-toggle-ico"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer" style="<?= $q === 1 ? 'max-height: 240px;' : '' ?>">
              <div class="faq-answer-inner"><?= e($ansText) ?></div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  
  <footer class="bright-footer">
    <div class="container">
      <div class="footer-top-grid">
        <div>
          <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY TECH STORE" class="footer-logo">
          <p style="color: var(--ink-2); max-width: 430px; margin: 0 0 18px; font-size: 0.94rem;"><?= e($tagline) ?></p>
          <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <?php foreach ($socialPlatforms as $sp): ?>
              <a href="<?= e($sp['url']) ?>" target="_blank" rel="noopener" class="social-mini-orb" title="<?= e($sp['name']) ?>">
                <i class="<?= e($sp['icon']) ?>"></i>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <strong style="font-size: 0.95rem; color: var(--ink-1);"><?= $en ? 'Navigation' : 'أقسام الموقع' ?></strong>
          <ul class="footer-links-list">
            <li><a href="#who-we-are"><i class="fa-solid fa-angle-<?= $en ? 'right' : 'left' ?>"></i> <?= e(t('nav_about')) ?></a></li>
            <li><a href="#our-craft"><i class="fa-solid fa-angle-<?= $en ? 'right' : 'left' ?>"></i> <?= e(t('nav_craft')) ?></a></li>
            <li><a href="#repair-lab"><i class="fa-solid fa-angle-<?= $en ? 'right' : 'left' ?>"></i> <?= e(t('nav_lab')) ?></a></li>
            <li><a href="#social-universe"><i class="fa-solid fa-angle-<?= $en ? 'right' : 'left' ?>"></i> <?= e(t('nav_social')) ?></a></li>
          </ul>
        </div>

        <div>
          <strong style="font-size: 0.95rem; color: var(--ink-1);"><?= $en ? 'Quick Portals' : 'بوابات سريعة' ?></strong>
          <ul class="footer-links-list">
            <li><a href="<?= e(appUrl('track.php')) ?>"><i class="fa-solid fa-qrcode"></i> <?= e(t('nav_track_btn')) ?></a></li>
            <li><a href="<?= e(langSwitchUrl($otherLang)) ?>"><i class="fa-solid fa-globe"></i> <?= $en ? 'النسخة العربية' : 'English Version' ?></a></li>
            <li><a href="<?= e(appUrl('admin/login.php')) ?>"><i class="fa-solid fa-user-shield"></i> <?= e(t('nav_staff_portal')) ?></a></li>
          </ul>
        </div>
      </div>

      <div class="footer-giant-word" aria-hidden="true">ASLY TECH STORE</div>

      <div class="footer-bottom-bar">
        <span>&copy; <?= date('Y') ?> <?= e($en ? $storeNameEn : $storeNameAr) ?> — <?= e(t('footer_rights')) ?>.</span>
        <span><?= e($founders) ?></span>
      </div>
    </div>
  </footer>

  
  <div class="bright-modal-backdrop" id="landingQrModal" aria-hidden="true">
    <div class="bright-modal-card">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 900;">
          <i class="fa-solid fa-qrcode" style="color: var(--teal-deep); margin-inline-end: 8px;"></i>
          <?= $en ? 'Scan Repair Receipt QR' : 'مسح رمز QR إيصال الصيانة' ?>
        </h3>
        <button type="button" class="icon-pill-btn" id="closeLandingQrBtn">
          <i class="fa-solid fa-xmark"></i>
          <span><?= e(t('close')) ?></span>
        </button>
      </div>
      <p style="margin: 0 0 14px; font-size: 0.9rem; color: var(--ink-2);">
        <?= $en ? 'Point your camera at the QR code printed on your ASLY repair receipt, or choose an image of the receipt.' : 'وجّه الكاميرا نحو رمز الـ QR المطبوع على إيصال الصيانة، أو اختر صورة الإيصال من جهازك.' ?>
      </p>
      <div id="landingQrReader" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--teal-border); background: var(--bg-ivory); min-height: 220px;"></div>
      <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
        <label class="btn btn-outline btn-sm" style="cursor: pointer;">
          <i class="fa-solid fa-image"></i>
          <span><?= $en ? 'Scan From Receipt Photo' : 'مسح من صورة إيصال' ?></span>
          <input type="file" id="landingQrFileInput" accept="image/*" style="display: none;">
        </label>
        <span id="landingQrStatus" style="font-size: 0.82rem; color: var(--ink-3); font-weight: 700;"></span>
      </div>
    </div>
  </div>

  
  <div class="bright-modal-backdrop" id="mobileNavModal" aria-hidden="true">
    <div class="bright-modal-card">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY" style="height: 40px;">
        <button type="button" class="icon-pill-btn" id="closeMobileDrawerBtn">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <a href="#who-we-are" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-fingerprint"></i> <span><?= e(t('nav_about')) ?></span></a>
        <a href="#our-craft" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-cubes"></i> <span><?= e(t('nav_craft')) ?></span></a>
        <a href="#repair-lab" class="btn btn-teal mobile-nav-item"><i class="fa-solid fa-qrcode"></i> <span><?= e(t('nav_lab')) ?></span></a>
        <a href="#social-universe" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-hashtag"></i> <span><?= e(t('nav_social')) ?></span></a>
        <a href="#contact-studio" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-location-dot"></i> <span><?= e(t('nav_contact')) ?></span></a>
        <a href="#faq" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-circle-question"></i> <span><?= e(t('nav_faq')) ?></span></a>
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-globe"></i> <span><?= e(t('lang_switch_label')) ?></span></a>
        <a href="<?= e(appUrl('admin/login.php')) ?>" class="btn btn-gold mobile-nav-item"><i class="fa-solid fa-user-shield"></i> <span><?= e(t('nav_staff_portal')) ?></span></a>
      </div>
    </div>
  </div>

  <script>
    window.ASLY_PUBLIC = {
      lang: <?= json_encode(currentLang()) ?>,
      apiPublicUrl: <?= json_encode(appUrl('api/public.php')) ?>,
      trackPageUrl: <?= json_encode(appUrl('track.php')) ?>,
      i18n: {
        checking: <?= json_encode($en ? 'Checking live lab telemetry...' : 'جاري فحص حالة التذكرة مباشرة من المعمل...') ?>,
        openFull: <?= json_encode(t('lab_btn_open_full')) ?>,
        stageLabel: <?= json_encode($en ? 'Current Stage' : 'المرحلة الحالية') ?>,
        deviceLabel: <?= json_encode($en ? 'Device' : 'الجهاز') ?>,
        latestNote: <?= json_encode($en ? 'Latest Lab Update' : 'آخر تحديث من المهندس') ?>,
        wipedTitle: <?= json_encode(t('track_delivered_title')) ?>
      }
    };
  </script>
  <script src="<?= e(appUrl('assets/vendor/zbar-wasm.min.js?v=' . $assetVer)) ?>"></script>
  <script src="<?= e(appUrl('assets/vendor/html5-qrcode.min.js?v=' . $assetVer)) ?>"></script>
  <script src="<?= e(appUrl('assets/js/turbo-scanner.js?v=' . $assetVer)) ?>"></script>
  <script src="<?= e(appUrl('assets/js/landing.js?v=' . $assetVer)) ?>"></script>
</body>
</html>
