<?php
/**
 * ASLY | أصلي TECH STORE
 * Avant-Garde Geometric & Curated Asymmetric Bright Editorial Landing Page
 * Identity ("Who We Are") + Domains of Craft + Live QR Repair Lab + Dynamic Social Hub
 * Zero Products • Zero Prices • Zero Emojis • Font Awesome 6 • Bilingual (AR RTL / EN LTR)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$channels = getActiveContactChannels($pdo, false);
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
    ? (($settings['store_address_en'] ?? '') !== '' ? $settings['store_address_en'] : ($settings['store_address'] ?? 'Sohag — Egypt'))
    : ($settings['store_address'] ?? 'سوهاج — جمهورية مصر العربية');
$hours = $en
    ? (($settings['working_hours_en'] ?? '') !== '' ? $settings['working_hours_en'] : ($settings['working_hours'] ?? 'Daily 11:00 AM – 11:00 PM'))
    : ($settings['working_hours'] ?? 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً');
$primaryWa = normalizeWhatsappNumber($settings['primary_whatsapp'] ?? '201015981677');
$primaryEmail = trim((string)($settings['primary_email'] ?? 'contact@aslystore.com'));
$mapsUrl = trim((string)($settings['google_maps_url'] ?? ''));
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($en ? "{$storeNameEn} — Authentic Tech Studio & QR Repair Lab" : "{$storeNameAr} | {$storeNameEn} — استوديو التقنية ومعمل الصيانة الذكي") ?></title>
  <meta name="description" content="<?= e($tagline) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body class="ambient-canvas">

  <!-- Soft Daylight Ambient Auras (Zero Dark, Zero Grids) -->
  <div class="geo-orb geo-orb-aqua" style="top: -120px; inset-inline-end: -140px;"></div>
  <div class="geo-orb geo-orb-gold" style="top: 380px; inset-inline-start: -180px;"></div>

  <!-- 1. Floating Frosted Architectural Capsule Navbar -->
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
          <span><?= e(t('lang_switch_label')) ?></span>
        </a>

        <a href="#repair-lab" class="btn btn-teal btn-sm">
          <i class="fa-solid fa-qrcode"></i>
          <span><?= e(t('nav_track_btn')) ?></span>
        </a>

        <button type="button" class="mobile-menu-btn" id="openMobileDrawerBtn" aria-label="Menu">
          <i class="fa-solid fa-bars-staggered"></i>
        </button>
      </div>
    </nav>
  </header>

  <!-- 2. Hero Stage: Avant-Garde Geometric + Asymmetric Collage -->
  <section class="hero-stage">
    <!-- Floating Parallax Geometric Shapes ("عشوائية هندسية مدروسة") -->
    <div class="geo-shape geo-ring" data-parallax="18" style="width: 92px; height: 92px; top: 8%; left: 46%;"></div>
    <div class="geo-shape geo-ring-dashed" data-parallax="-14" style="width: 150px; height: 150px; bottom: 8%; right: 44%;"></div>
    <div class="geo-shape geo-diamond" data-parallax="24" style="top: 18%; left: 6%;"></div>
    <div class="geo-shape geo-capsule" data-parallax="-20" style="width: 84px; height: 30px; bottom: 16%; left: 38%; transform: rotate(-22deg);"></div>

    <div class="container hero-grid">
      <!-- Text Manifesto Column -->
      <div class="hero-copy reveal-up in-view">
        <div class="eyebrow-chip">
          <i class="fa-solid fa-compass-drafting"></i>
          <span><?= e(t('hero_eyebrow')) ?></span>
        </div>

        <h1 class="hero-display">
          <span><?= e(t('hero_title_1')) ?></span><br>
          <span class="gradient-word"><?= e(t('hero_title_2')) ?></span><br>
          <span class="stroke-word"><?= e(t('hero_title_3')) ?></span>
        </h1>

        <p class="hero-lead"><?= e(t('hero_subtitle')) ?></p>

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

      <!-- Scattered Asymmetric Geometric Collage Column -->
      <div class="collage-stage reveal-up in-view">
        <!-- Spinning Circular Architectural Stamp -->
        <div class="orbital-stamp" aria-hidden="true">
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

        <!-- Primary Arched Asymmetric Studio Frame -->
        <div class="collage-main-arch" data-parallax="-10">
          <img src="<?= e(appUrl('assets/images/lookbook/showroom-space.jpg')) ?>" alt="ASLY Sunlit Architectural Studio">
        </div>

        <!-- Overlapping Travertine & Aqua Acrylic Flat-Lay Frame -->
        <div class="collage-sub-chamfer" data-parallax="16">
          <img src="<?= e(appUrl('assets/images/lookbook/studio-desk.jpg')) ?>" alt="Precision Tech Craft">
        </div>

        <!-- Top Pill Lens Frame -->
        <div class="collage-top-pill" data-parallax="-18">
          <img src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg')) ?>" alt="ASLY Diagnostic Engineer">
        </div>

        <!-- Floating Telemetry Badge -->
        <div class="floating-chip floating-chip-end">
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

  <!-- 3. Crisscrossing Angled Kinetic Marquee Ribbons -->
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

  <!-- 4. Section 01: WHO WE ARE — Asymmetric Architectural Mosaic -->
  <section id="who-we-are" class="section-pad">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-fingerprint"></i>
            <span><?= e(t('who_eyebrow')) ?></span>
          </div>
          <h2 class="section-title"><?= e(t('who_title')) ?></h2>
        </div>
        <p class="section-sub"><?= e($tagline) ?></p>
      </div>

      <div class="who-mosaic">
        <!-- Span 7: Main Brand Story & Founders Card -->
        <article class="mosaic-card mosaic-story reveal-up">
          <span class="hollow-num">ASLY</span>
          <div>
            <div class="pillar-icon-orb">
              <i class="fa-solid fa-gem"></i>
            </div>
            <span class="pillar-tag"><?= $en ? 'OUR MANIFESTO // STORY' : 'قصتنا وهويتنا // ASLY' ?></span>
            <h3 style="font-size: 1.55rem; margin-bottom: 14px;">
              <?= e($en ? $storeNameEn : "{$storeNameAr} — {$storeNameEn}") ?>
            </h3>
            <p style="font-size: 1.02rem; line-height: 1.85;"><?= e($aboutStory) ?></p>
          </div>

          <div class="founders-ribbon">
            <i class="fa-solid fa-user-check"></i>
            <span><?= e($founders) ?></span>
          </div>
        </article>

        <!-- Span 5: Offset Tilted Architectural Photo -->
        <div class="mosaic-card mosaic-photo-tall reveal-up">
          <img src="<?= e(appUrl('assets/images/lookbook/hero-2.jpg')) ?>" alt="ASLY Everyday Experience">
          <div class="mosaic-photo-caption">
            <span><i class="fa-solid fa-certificate" style="color: var(--teal-deep); margin-inline-end: 6px;"></i> <?= $en ? 'Authentic Hardware Culture' : 'ثقافة التقنية الأصلية' ?></span>
            <span style="font-family: var(--font-mono); color: var(--gold-deep);">100%</span>
          </div>
        </div>

        <!-- 3 Staggered Irregular Pillar Cards -->
        <article class="mosaic-card mosaic-pillar reveal-up">
          <span class="hollow-num">01</span>
          <div class="pillar-icon-orb">
            <i class="fa-solid fa-microchip"></i>
          </div>
          <span class="pillar-tag"><?= e(t('pillar_1_tag')) ?></span>
          <h3><?= e(t('pillar_1_title')) ?></h3>
          <p><?= e(t('pillar_1_desc')) ?></p>
        </article>

        <article class="mosaic-card mosaic-pillar reveal-up">
          <span class="hollow-num">02</span>
          <div class="pillar-icon-orb gold">
            <i class="fa-solid fa-microscope"></i>
          </div>
          <span class="pillar-tag" style="color: var(--gold-deep);"><?= e(t('pillar_2_tag')) ?></span>
          <h3><?= e(t('pillar_2_title')) ?></h3>
          <p><?= e(t('pillar_2_desc')) ?></p>
        </article>

        <article class="mosaic-card mosaic-pillar reveal-up">
          <span class="hollow-num">03</span>
          <div class="pillar-icon-orb">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <span class="pillar-tag"><?= e(t('pillar_3_tag')) ?></span>
          <h3><?= e(t('pillar_3_title')) ?></h3>
          <p><?= e(t('pillar_3_desc')) ?></p>
        </article>
      </div>
    </div>
  </section>

  <!-- 5. Section 02: DOMAINS OF CRAFT — Expandable Architectural Slices (Zero Products!) -->
  <section id="our-craft" class="section-pad section-ivory">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-cubes"></i>
            <span><?= e(t('craft_eyebrow')) ?></span>
          </div>
          <h2 class="section-title"><?= e(t('craft_title')) ?></h2>
        </div>
      </div>

      <div class="craft-accordion-deck reveal-up">
        <div class="craft-slice active">
          <img src="<?= e(appUrl('assets/images/lookbook/hero-1.jpg')) ?>" alt="Workstations" class="craft-slice-img">
          <div class="craft-slice-glass">
            <div class="craft-slice-top">
              <span class="craft-idx">01 // LAB</span>
              <span class="craft-icon"><i class="fa-solid fa-laptop-code"></i></span>
            </div>
            <h3><?= e(t('craft_1_title')) ?></h3>
            <p><?= e(t('craft_1_sub')) ?></p>
          </div>
        </div>

        <div class="craft-slice">
          <img src="<?= e(appUrl('assets/images/lookbook/studio-desk.jpg')) ?>" alt="Flagship Smartphones" class="craft-slice-img">
          <div class="craft-slice-glass">
            <div class="craft-slice-top">
              <span class="craft-idx">02 // MOBILE</span>
              <span class="craft-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
            </div>
            <h3><?= e(t('craft_2_title')) ?></h3>
            <p><?= e(t('craft_2_sub')) ?></p>
          </div>
        </div>

        <div class="craft-slice">
          <img src="<?= e(appUrl('assets/images/lookbook/lab-repair.jpg')) ?>" alt="Precision Repair Lab" class="craft-slice-img">
          <div class="craft-slice-glass">
            <div class="craft-slice-top">
              <span class="craft-idx">03 // QR REPAIR</span>
              <span class="craft-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            </div>
            <h3><?= e(t('craft_3_title')) ?></h3>
            <p><?= e(t('craft_3_sub')) ?></p>
          </div>
        </div>

        <div class="craft-slice">
          <img src="<?= e(appUrl('assets/images/lookbook/showroom-space.jpg')) ?>" alt="Studio Ecosystem" class="craft-slice-img">
          <div class="craft-slice-glass">
            <div class="craft-slice-top">
              <span class="craft-idx">04 // ECOSYSTEM</span>
              <span class="craft-icon"><i class="fa-solid fa-headphones-simple"></i></span>
            </div>
            <h3><?= e(t('craft_4_title')) ?></h3>
            <p><?= e(t('craft_4_sub')) ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. Section 03: INTERACTIVE QR & TICKET MAINTENANCE TELEMETRY CONSOLE -->
  <section id="repair-lab" class="section-pad">
    <div class="container">
      <div class="lab-console-shell reveal-up">
        <div class="geo-orb geo-orb-aqua" style="bottom: -160px; inset-inline-end: -120px;"></div>

        <div class="lab-console-grid">
          <!-- Left: Instant Lookup & Camera QR Trigger -->
          <div>
            <div class="eyebrow-chip" style="background: #ffffff;">
              <i class="fa-solid fa-satellite-dish"></i>
              <span><?= e(t('lab_eyebrow')) ?></span>
            </div>

            <h2 class="section-title" style="margin-bottom: 14px;"><?= e(t('lab_title')) ?></h2>
            <p style="color: var(--ink-2); margin: 0; line-height: 1.8;"><?= e(t('lab_desc')) ?></p>

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

              <button type="button" class="demo-code-chip" id="tryDemoTicketBtn" data-code="MNT-2026-101">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--gold-deep);"></i>
                <span><?= e(t('lab_try_demo')) ?></span>
              </button>
            </div>

            <!-- Instant Live AJAX Telemetry Preview Box -->
            <div id="inlineTicketResult" class="inline-ticket-result" aria-live="polite"></div>
          </div>

          <!-- Right: Scattered 7-Stage Constellation -->
          <div class="stage-constellation">
            <div class="constellation-node">
              <div class="c-node-ico"><i class="fa-solid fa-clipboard-check"></i></div>
              <div>
                <div class="chip-title">01 • <?= $en ? 'Logged & QR Issued' : 'الاستلام وإصدار QR' ?></div>
                <div class="chip-sub"><?= $en ? 'Instant printed token' : 'إيصال ذكي خاص بجهازك' ?></div>
              </div>
            </div>

            <div class="constellation-node">
              <div class="c-node-ico"><i class="fa-solid fa-microscope"></i></div>
              <div>
                <div class="chip-title">02 • <?= $en ? 'Deep Diagnostics' : 'الفحص والتشخيص' ?></div>
                <div class="chip-sub"><?= $en ? 'Board & thermal check' : 'تحديد دقيق للعطل' ?></div>
              </div>
            </div>

            <div class="constellation-node">
              <div class="c-node-ico"><i class="fa-solid fa-screwdriver-wrench"></i></div>
              <div>
                <div class="chip-title">03 • <?= $en ? 'Precision Repair' : 'الإصلاح والاختبار' ?></div>
                <div class="chip-sub"><?= $en ? 'Two-way engineer chat' : 'ملاحظات متبادلة مع المهندس' ?></div>
              </div>
            </div>

            <div class="constellation-node">
              <div class="c-node-ico"><i class="fa-solid fa-envelope-circle-check"></i></div>
              <div>
                <div class="chip-title">04 • <?= $en ? 'Live Email Alerts' : 'تنبيهات البريد الفورية' ?></div>
                <div class="chip-sub"><?= $en ? 'Every stage update' : 'إشعار مع كل مرحلة جديدة' ?></div>
              </div>
            </div>

            <div class="constellation-node span-full">
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

  <!-- 7. Section 04: ASYMMETRIC SOCIAL MEDIA UNIVERSE (100% Editable by Admin!) -->
  <section id="social-universe" class="section-pad section-ivory">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-hashtag"></i>
            <span><?= e(t('social_eyebrow')) ?></span>
          </div>
          <h2 class="section-title"><?= e(t('social_title')) ?></h2>
        </div>
        <p class="section-sub"><?= e(t('social_sub')) ?></p>
      </div>

      <div class="social-wall-grid">
        <?php foreach ($socialPlatforms as $sp): ?>
          <a href="<?= e($sp['url']) ?>" target="_blank" rel="noopener" class="social-geo-tile reveal-up" data-accent="<?= e($sp['accent']) ?>">
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

  <!-- 8. Section 05: STUDIO ADDRESS, HOURS & DIRECT LINES -->
  <section id="contact-studio" class="section-pad">
    <div class="container">
      <div class="section-head-asym reveal-up">
        <div>
          <div class="eyebrow-chip">
            <i class="fa-solid fa-location-dot"></i>
            <span><?= e(t('contact_eyebrow')) ?></span>
          </div>
          <h2 class="section-title"><?= e(t('contact_title')) ?></h2>
        </div>
      </div>

      <div class="concierge-grid reveal-up">
        <!-- Left: Architectural Location & Hours Card -->
        <div class="location-arch-card">
          <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="loc-row">
              <div class="loc-ico"><i class="fa-solid fa-map-location-dot"></i></div>
              <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--ink-3);"><?= e(t('contact_location')) ?></div>
                <div style="font-size: 1.08rem; font-weight: 800; color: var(--ink-1);"><?= e($address) ?></div>
              </div>
            </div>

            <div class="loc-row">
              <div class="loc-ico"><i class="fa-solid fa-clock"></i></div>
              <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--ink-3);"><?= e(t('contact_hours')) ?></div>
                <div style="font-size: 1.04rem; font-weight: 800; color: var(--ink-1);"><?= e($hours) ?></div>
              </div>
            </div>

            <?php if ($primaryEmail !== ''): ?>
            <div class="loc-row">
              <div class="loc-ico"><i class="fa-solid fa-envelope-open-text"></i></div>
              <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--ink-3);">Email</div>
                <a href="mailto:<?= e($primaryEmail) ?>" style="font-size: 1rem; font-weight: 800; color: var(--teal-deep); direction: ltr; display: inline-block;"><?= e($primaryEmail) ?></a>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <?php if ($mapsUrl !== ''): ?>
              <a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" class="btn btn-gold" style="flex: 1;">
                <i class="fa-solid fa-diamond-turn-right"></i>
                <span><?= e(t('contact_map_btn')) ?></span>
              </a>
            <?php endif; ?>
            <?php if ($primaryWa !== ''): ?>
              <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-teal" style="flex: 1;">
                <i class="fa-brands fa-whatsapp"></i>
                <span><?= $en ? 'WhatsApp Concierge' : 'تحدث عبر واتساب' ?></span>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right: Dynamic Admin Contact Channels -->
        <div class="channels-stack">
          <?php foreach ($channels as $ch):
              $type = (string)($ch['channel_type'] ?? 'phone');
              $faIco = getChannelFontAwesomeIcon($type);
              $href = trim((string)($ch['url'] ?? ''));
              if ($href === '') {
                  $href = $type === 'whatsapp'
                      ? ('https://wa.me/' . normalizeWhatsappNumber((string)$ch['value']))
                      : ('tel:' . preg_replace('/\s+/', '', (string)$ch['value']));
              }
              $lbl = translateChannelLabel((string)$ch['label'], $type);
          ?>
            <a href="<?= e($href) ?>" target="<?= str_starts_with($href, 'http') ? '_blank' : '_self' ?>" rel="noopener" class="direct-channel-card">
              <div class="direct-ch-ico">
                <i class="<?= e($faIco) ?>"></i>
              </div>
              <div style="min-width: 0;">
                <div style="font-size: 0.82rem; color: var(--ink-3); font-weight: 700;"><?= e($lbl) ?></div>
                <div style="font-size: 1rem; font-weight: 900; color: var(--ink-1); direction: ltr; text-align: start; overflow: hidden; text-overflow: ellipsis;"><?= e((string)$ch['value']) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 9. Section 06: ARCHITECTURAL FAQ -->
  <section id="faq" class="section-pad section-ivory">
    <div class="container faq-asym-grid reveal-up">
      <div>
        <div class="eyebrow-chip">
          <i class="fa-solid fa-circle-question"></i>
          <span><?= e(t('faq_eyebrow')) ?></span>
        </div>
        <h2 class="section-title" style="margin-bottom: 16px;"><?= e(t('faq_title')) ?></h2>
        <p style="color: var(--ink-2); margin: 0;"><?= e($founders) ?></p>
      </div>

      <div class="faq-list">
        <?php for ($q = 1; $q <= 3; $q++): ?>
          <div class="faq-item <?= $q === 1 ? 'open' : '' ?>">
            <button type="button" class="faq-trigger">
              <span><?= e(t("faq_q{$q}")) ?></span>
              <span class="faq-toggle-ico"><i class="fa-solid fa-plus"></i></span>
            </button>
            <div class="faq-answer" style="<?= $q === 1 ? 'max-height: 240px;' : '' ?>">
              <div class="faq-answer-inner"><?= e(t("faq_a{$q}")) ?></div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- 10. Sculptural Pure-Bright Footer -->
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

  <!-- Live QR Receipt Camera Modal -->
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

  <!-- Mobile Navigation Drawer -->
  <div class="bright-modal-backdrop" id="mobileNavModal" aria-hidden="true">
    <div class="bright-modal-card">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY" style="height: 40px;">
        <button type="button" class="icon-pill-btn" id="closeMobileDrawerBtn">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <a href="#who-we-are" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-fingerprint"></i> <?= e(t('nav_about')) ?></a>
        <a href="#our-craft" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-cubes"></i> <?= e(t('nav_craft')) ?></a>
        <a href="#repair-lab" class="btn btn-teal mobile-nav-item"><i class="fa-solid fa-qrcode"></i> <?= e(t('nav_lab')) ?></a>
        <a href="#social-universe" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-hashtag"></i> <?= e(t('nav_social')) ?></a>
        <a href="#contact-studio" class="btn btn-outline mobile-nav-item"><i class="fa-solid fa-location-dot"></i> <?= e(t('nav_contact')) ?></a>
        <a href="<?= e(appUrl('admin/login.php')) ?>" class="btn btn-gold mobile-nav-item"><i class="fa-solid fa-user-shield"></i> <?= e(t('nav_staff_portal')) ?></a>
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
  <script src="<?= e(appUrl('assets/vendor/html5-qrcode.min.js')) ?>"></script>
  <script src="<?= e(appUrl('assets/js/landing.js')) ?>"></script>
</body>
</html>
