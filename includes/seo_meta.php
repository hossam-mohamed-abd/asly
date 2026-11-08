<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function formatE164EgyptPhone(string $raw): string
{
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if ($digits === '') {
        return '';
    }
    if (str_starts_with($digits, '00')) {
        return '+' . substr($digits, 2);
    }
    if (str_starts_with($digits, '01') && strlen($digits) === 11) {
        return '+2' . $digits;
    }
    if (str_starts_with($digits, '20') && strlen($digits) === 12) {
        return '+' . $digits;
    }
    return '+' . $digits;
}

function renderPublicSeoHead(array $settings, array $socialPlatforms, array $opts = []): string
{
    $en = isEn();
    $langCode = $en ? 'en' : 'ar';
    $pageType = (string)($opts['page'] ?? 'home');
    $noindex = !empty($opts['noindex']);

    $baseConf = trim((string)($settings['public_base_url'] ?? '')) ?: null;
    $homeUrl = rtrim(publicAbsoluteUrl('', $baseConf), '/') . '/';
    $homeArUrl = publicAbsoluteUrl('index.php?lang=ar', $baseConf);
    $homeEnUrl = publicAbsoluteUrl('index.php?lang=en', $baseConf);
    $trackArUrl = publicAbsoluteUrl('track.php?lang=ar', $baseConf);
    $trackEnUrl = publicAbsoluteUrl('track.php?lang=en', $baseConf);

    $storeNameAr = trim((string)($settings['store_name_ar'] ?? '')) ?: 'أصلي تك ستور';
    $storeNameEn = trim((string)($settings['store_name_en'] ?? '')) ?: 'ASLY TECH STORE';
    $addressAr = trim((string)($settings['store_address'] ?? '')) ?: 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا';
    $addressEn = trim((string)($settings['store_address_en'] ?? '')) ?: 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum';
    $hoursAr = trim((string)($settings['working_hours'] ?? '')) ?: 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً';
    $hoursEn = trim((string)($settings['working_hours_en'] ?? '')) ?: 'Daily from 11:00 AM to 11:00 PM';
    $foundersAr = trim((string)($settings['founders_line'] ?? '')) ?: 'إشراف وإدارة: م. حسام محمد • م. رمضان سلام';
    $foundersEn = trim((string)($settings['founders_line_en'] ?? '')) ?: 'Supervised by Eng. Hossam Mohamed & Eng. Ramadan Sallam';

    $defaultTitleAr = "{$storeNameAr} | {$storeNameEn} — بيع وصيانة اللابتوبات والهواتف بالفيوم وسنورس وترسا";
    $defaultTitleEn = "{$storeNameEn} | Certified Laptops, Smartphones & Live QR Repair Lab in Faiyum, Egypt";

    if ($pageType === 'track') {
        $title = $en
            ? (t('track_page_title') . ' | ' . $storeNameEn . ' — Faiyum')
            : (t('track_page_title') . ' | ' . $storeNameAr . ' — ' . $storeNameEn);
        $desc = $en
            ? "Track your laptop or smartphone repair status live across 7 diagnostic stages via QR receipt at {$storeNameEn} ({$addressEn})."
            : "تابع حالة جهازك في قسم الصيانة لحظة بلحظة عبر كود التذكرة أو رمز الـ QR خلال 7 مراحل فحص وإصلاح دقيقة في {$storeNameAr} ({$addressAr}).";
        $canonical = $en ? $trackEnUrl : $trackArUrl;
        $altAr = $trackArUrl;
        $altEn = $trackEnUrl;
        $xDefault = publicAbsoluteUrl('track.php', $baseConf);
    } else {
        $title = $en
            ? (trim((string)($settings['seo_title_en'] ?? '')) ?: $defaultTitleEn)
            : (trim((string)($settings['seo_title_ar'] ?? '')) ?: $defaultTitleAr);
        $desc = $en
            ? (trim((string)($settings['seo_description_en'] ?? '')) ?: "ASLY TECH STORE in Tersa, Sinnuris, Faiyum: Certified authentic laptops, flagship smartphones, original accessories, and board-level repair with live 7-stage QR tracking.")
            : (trim((string)($settings['seo_description_ar'] ?? '')) ?: "{$storeNameAr} ({$storeNameEn}) في {$addressAr}: المركز الهندسي المعتمد للأجهزة الأصلية واللابتوبات والهواتف الذكية ومعمل الصيانة الدقيق بتتبع الـ QR المباشر.");
        $canonical = $en ? $homeEnUrl : $homeUrl;
        $altAr = $homeArUrl;
        $altEn = $homeEnUrl;
        $xDefault = $homeUrl;
    }

    $keywords = $en
        ? (trim((string)($settings['seo_keywords_en'] ?? '')) ?: 'ASLY Tech Store, laptop store Faiyum, laptop repair Faiyum, smartphone repair Sinnuris Tersa, QR repair lab Egypt')
        : (trim((string)($settings['seo_keywords_ar'] ?? '')) ?: 'أصلي تك ستور, اصلي تك ستور, ASLY Tech Store, محل لابتوب الفيوم, صيانة لابتوب الفيوم, صيانة موبايلات سنورس, صيانة موبايلات ترسا');

    $aiSummary = $en
        ? (trim((string)($settings['seo_ai_summary_en'] ?? '')) ?: $desc)
        : (trim((string)($settings['seo_ai_summary_ar'] ?? '')) ?: $desc);

    $robotsDirective = $noindex
        ? 'noindex, nofollow, noarchive'
        : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

    $ogImage = publicAbsoluteUrl('assets/brand/logo-card.jpg', $baseConf);
    $logoUrl = publicAbsoluteUrl('assets/brand/logo-transparent.png', $baseConf);
    $showroomImg = publicAbsoluteUrl('assets/images/lookbook/showroom-space.jpg', $baseConf);
    $labImg = publicAbsoluteUrl('assets/images/lookbook/lab-repair.jpg', $baseConf);

    $sitemapUrl = publicAbsoluteUrl('sitemap.xml', $baseConf);
    $manifestUrl = publicAbsoluteUrl('site.webmanifest', $baseConf);
    $llmsUrl = publicAbsoluteUrl('llms.txt', $baseConf);
    $llmsFullUrl = publicAbsoluteUrl('llms-full.txt', $baseConf);
    $aiJsonUrl = publicAbsoluteUrl('ai.json', $baseConf);

    $googleVerif = trim((string)($settings['google_site_verification'] ?? ''));
    $bingVerif = trim((string)($settings['bing_site_verification'] ?? ''));

    $primaryPhone = trim((string)($settings['primary_phone'] ?? ''));
    $secondaryPhone = trim((string)($settings['secondary_phone'] ?? ''));
    $primaryWa = normalizeWhatsappNumber((string)($settings['primary_whatsapp'] ?? ''));
    $primaryEmail = trim((string)($settings['primary_email'] ?? ''));
    $mapsUrl = trim((string)($settings['google_maps_url'] ?? ''));

    $sameAs = [];
    foreach ($socialPlatforms as $sp) {
        if (!empty($sp['url'])) {
            $sameAs[] = (string)$sp['url'];
        }
    }
    $sameAs = array_values(array_unique($sameAs));

    $contactPoints = [];
    if ($primaryPhone !== '') {
        $contactPoints[] = [
            '@type' => 'ContactPoint',
            'telephone' => formatE164EgyptPhone($primaryPhone),
            'contactType' => 'customer service',
            'areaServed' => 'EG',
            'availableLanguage' => ['Arabic', 'English'],
        ];
    }
    if ($secondaryPhone !== '') {
        $contactPoints[] = [
            '@type' => 'ContactPoint',
            'telephone' => formatE164EgyptPhone($secondaryPhone),
            'contactType' => 'technical support',
            'areaServed' => 'EG',
            'availableLanguage' => ['Arabic', 'English'],
        ];
    }
    if ($primaryWa !== '') {
        $contactPoints[] = [
            '@type' => 'ContactPoint',
            'telephone' => '+' . $primaryWa,
            'url' => 'https://wa.me/' . $primaryWa,
            'contactType' => 'sales',
            'areaServed' => 'EG',
            'availableLanguage' => ['Arabic', 'English'],
        ];
    }

    $orgNode = [
        '@type' => ['ElectronicsStore', 'ComputerStore', 'MobilePhoneStore', 'LocalBusiness'],
        '@id' => $homeUrl . '#organization',
        'name' => "{$storeNameAr} | {$storeNameEn}",
        'alternateName' => [
            'ASLY TECH STORE',
            'أصلي تك ستور',
            'اصلي تك ستور',
            'ASLY Tech Studio',
            'مركز أصلي لصيانة وبيع اللابتوبات والهواتف',
        ],
        'description' => $aiSummary,
        'slogan' => $en ? ($settings['store_tagline_en'] ?? '') : ($settings['store_tagline'] ?? ''),
        'url' => $homeUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ],
        'image' => [$ogImage, $showroomImg, $labImg],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'شارع السوق أمام كنيسة ترسا — Al-Souq St., In Front of Tersa Church',
            'addressLocality' => 'ترسا، سنورس (Tersa, Sinnuris)',
            'addressRegion' => 'الفيوم (Faiyum Governorate)',
            'addressCountry' => 'EG',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 29.4086,
            'longitude' => 30.8656,
        ],
        'areaServed' => [
            ['@type' => 'Place', 'name' => 'ترسا - Tersa'],
            ['@type' => 'City', 'name' => 'سنورس - Sinnuris'],
            ['@type' => 'AdministrativeArea', 'name' => 'محافظة الفيوم - Faiyum Governorate'],
            ['@type' => 'Country', 'name' => 'مصر - Egypt'],
        ],
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'opens' => '11:00',
                'closes' => '23:00',
            ],
        ],
        'knowsLanguage' => ['ar-EG', 'ar', 'en'],
        'knowsAbout' => [
            'Laptops & Mobile Workstations',
            'Flagship Smartphones',
            'Smartwatches & Original Tech Accessories',
            'Motherboard Micro-Soldering & Diagnostics',
            'Interactive QR Code Device Repair Tracking',
            'بيع وفحص اللابتوبات الاحترافية والاستيراد والجديد',
            'الهواتف الذكية والساعات والإكسسوارات الأصلية',
            'صيانة اللابتوب والموبايل الدقيقة على مستوى البوردة',
            'تتبع الصيانة المباشر بالـ QR في الفيوم وسنورس وترسا',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => $en ? 'ASLY Engineering Domains & Services' : 'مجالات التخصص والخدمات الهندسية في أصلي تك ستور',
            'itemListElement' => [
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $en ? 'Certified Workstations & Authentic Laptops' : 'أجهزة اللابتوب والوركستيشن المفحوصة هندسياً',
                        'description' => t('craft_1_sub'),
                    ],
                ],
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $en ? 'Flagship Smartphones & Genuine Ecosystem' : 'الهواتف الذكية الرائدة والإكسسوارات الأصلية',
                        'description' => t('craft_2_sub'),
                    ],
                ],
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $en ? 'Precision Board-Level Repair Lab with 7-Stage QR Tracking' : 'معمل الصيانة الدقيقة على مستوى البوردة بتتبع الـ QR المباشر',
                        'description' => t('craft_3_sub'),
                    ],
                ],
                [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $en ? 'Smart Wearables, Audio & Original Power Solutions' : 'الساعات الذكية والصوتيات وحلول الشحن الأصلية المعتمدة',
                        'description' => t('craft_4_sub'),
                    ],
                ],
            ],
        ],
    ];

    if ($primaryPhone !== '') {
        $orgNode['telephone'] = formatE164EgyptPhone($primaryPhone);
    }
    if ($primaryEmail !== '') {
        $orgNode['email'] = $primaryEmail;
    }
    if ($mapsUrl !== '') {
        $orgNode['hasMap'] = $mapsUrl;
    }
    if (!empty($contactPoints)) {
        $orgNode['contactPoint'] = $contactPoints;
    }
    if (!empty($sameAs)) {
        $orgNode['sameAs'] = $sameAs;
    }

    $graph = [
        $orgNode,
        [
            '@type' => 'WebSite',
            '@id' => $homeUrl . '#website',
            'url' => $homeUrl,
            'name' => "{$storeNameAr} | {$storeNameEn}",
            'inLanguage' => ['ar-EG', 'en'],
            'publisher' => ['@id' => $homeUrl . '#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => publicAbsoluteUrl('track.php', $baseConf) . '?code={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $title,
            'description' => $desc,
            'inLanguage' => $en ? 'en' : 'ar-EG',
            'isPartOf' => ['@id' => $homeUrl . '#website'],
            'about' => ['@id' => $homeUrl . '#organization'],
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ['.hero-display', '.hero-lead', '.mosaic-story', '#faq'],
            ],
        ],
    ];

    if ($pageType === 'track') {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $en ? $storeNameEn : $storeNameAr, 'item' => $en ? $homeEnUrl : $homeUrl],
                ['@type' => 'ListItem', 'position' => 2, 'name' => t('track_page_title'), 'item' => $canonical],
            ],
        ];
    } else {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $en ? $storeNameEn : $storeNameAr, 'item' => $canonical],
            ],
        ];

        $faqEntities = [];
        for ($i = 1; $i <= 6; $i++) {
            $qText = t("faq_q{$i}");
            $aText = $i === 4
                ? ($en
                    ? "{$storeNameEn} is located at {$addressEn}. Studio hours: {$hoursEn}."
                    : "يقع مقر {$storeNameAr} في {$addressAr}. مواعيد العمل: {$hoursAr}.")
                : t("faq_a{$i}");
            $faqEntities[] = [
                '@type' => 'Question',
                'name' => $qText,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $aText,
                ],
            ];
        }
        $graph[] = [
            '@type' => 'FAQPage',
            '@id' => $canonical . '#faqpage',
            'mainEntity' => $faqEntities,
        ];
    }

    $schemaJson = json_encode(
        ['@context' => 'https://schema.org', '@graph' => $graph],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    $lines = [];
    $lines[] = '<title>' . e($title) . '</title>';
    $lines[] = '<meta name="description" content="' . e($desc) . '">';
    $lines[] = '<meta name="keywords" content="' . e($keywords) . '">';
    $lines[] = '<meta name="author" content="' . e($en ? $foundersEn : $foundersAr) . '">';
    $lines[] = '<meta name="application-name" content="' . e($en ? $storeNameEn : $storeNameAr) . '">';
    $lines[] = '<meta name="apple-mobile-web-app-title" content="' . e($storeNameEn) . '">';
    $lines[] = '<meta name="theme-color" content="#ffffff">';
    $lines[] = '<meta name="color-scheme" content="light">';
    $lines[] = '<meta name="robots" content="' . e($robotsDirective) . '">';
    $lines[] = '<meta name="googlebot" content="' . e($robotsDirective) . '">';
    $lines[] = '<meta name="bingbot" content="' . e($robotsDirective) . '">';
    $lines[] = '<meta name="ai-content-Summary" content="' . e($aiSummary) . '">';
    $lines[] = '<meta name="geo.region" content="EG-FYM">';
    $lines[] = '<meta name="geo.placename" content="Tersa, Sinnuris, Faiyum, Egypt">';
    $lines[] = '<meta name="geo.position" content="29.4086;30.8656">';
    $lines[] = '<meta name="ICBM" content="29.4086, 30.8656">';

    if ($googleVerif !== '') {
        $lines[] = '<meta name="google-site-verification" content="' . e($googleVerif) . '">';
    }
    if ($bingVerif !== '') {
        $lines[] = '<meta name="msvalidate.01" content="' . e($bingVerif) . '">';
    }

    if (!$noindex) {
        $lines[] = '<link rel="canonical" href="' . e($canonical) . '">';
        $lines[] = '<link rel="alternate" hreflang="ar-EG" href="' . e($altAr) . '">';
        $lines[] = '<link rel="alternate" hreflang="ar" href="' . e($altAr) . '">';
        $lines[] = '<link rel="alternate" hreflang="en" href="' . e($altEn) . '">';
        $lines[] = '<link rel="alternate" hreflang="x-default" href="' . e($xDefault) . '">';
    }

    $lines[] = '<link rel="sitemap" type="application/xml" title="Sitemap" href="' . e($sitemapUrl) . '">';
    $lines[] = '<link rel="manifest" href="' . e($manifestUrl) . '">';
    $lines[] = '<link rel="alternate" type="text/plain" title="LLMs Knowledge Summary" href="' . e($llmsUrl) . '">';
    $lines[] = '<link rel="alternate" type="text/plain" title="LLMs Full Knowledge Base" href="' . e($llmsFullUrl) . '">';
    $lines[] = '<link rel="alternate" type="application/json" title="AI Entity Profile" href="' . e($aiJsonUrl) . '">';

    $lines[] = '<meta property="og:type" content="website">';
    $lines[] = '<meta property="og:site_name" content="' . e("{$storeNameAr} | {$storeNameEn}") . '">';
    $lines[] = '<meta property="og:title" content="' . e($title) . '">';
    $lines[] = '<meta property="og:description" content="' . e($desc) . '">';
    $lines[] = '<meta property="og:url" content="' . e($canonical) . '">';
    $lines[] = '<meta property="og:locale" content="' . ($en ? 'en_US' : 'ar_EG') . '">';
    $lines[] = '<meta property="og:locale:alternate" content="' . ($en ? 'ar_EG' : 'en_US') . '">';
    $lines[] = '<meta property="og:image" content="' . e($ogImage) . '">';
    $lines[] = '<meta property="og:image:secure_url" content="' . e($ogImage) . '">';
    $lines[] = '<meta property="og:image:type" content="image/jpeg">';
    $lines[] = '<meta property="og:image:width" content="1200">';
    $lines[] = '<meta property="og:image:height" content="630">';
    $lines[] = '<meta property="og:image:alt" content="' . e("{$storeNameAr} | {$storeNameEn} — {$addressAr}") . '">';

    $lines[] = '<meta name="twitter:card" content="summary_large_image">';
    $lines[] = '<meta name="twitter:title" content="' . e($title) . '">';
    $lines[] = '<meta name="twitter:description" content="' . e($desc) . '">';
    $lines[] = '<meta name="twitter:image" content="' . e($ogImage) . '">';

    $lines[] = '<link rel="preconnect" href="https://fonts.googleapis.com">';
    $lines[] = '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';

    if (!$noindex && $schemaJson !== false) {
        $lines[] = '<script type="application/ld+json">' . $schemaJson . '</script>';
    }

    return implode("\n  ", $lines);
}
