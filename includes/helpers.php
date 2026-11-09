<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/i18n.php';

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function readJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST;
}

function getDefaultSettings(): array
{
    return [
        'store_name_ar'           => 'أصلي تك ستور',
        'store_name_en'           => 'ASLY TECH STORE',
        'store_tagline'           => 'المركز المتكامل للتقنية الأصلية ومعمل الصيانة الذكي بتتبع الـ QR',
        'store_tagline_en'        => 'Engineering-Led Technology Studio & Live QR-Tracked Precision Repair Lab',
        'founders_line'           => 'إشراف وإدارة: م. حسام محمد • م. رمضان سلام',
        'founders_line_en'        => 'Supervised by Eng. Hossam Mohamed & Eng. Ramadan Sallam',
        'about_story_ar'          => 'أصلي تك ستور ليس مجرد معرض تقني تقليدي؛ بل استوديو هندسي ومعمل فحص وصيانة دقيقة تأسس ليضع معياراً حقيقياً للثقة والأصالة. كل جهاز يمر عبر بروتوكول فحص متعدد النقاط، وكل عملية صيانة موثقة لحظة بلحظة عبر نظام تتبع QR شفاف يحمي خصوصيتك وبياناتك.',
        'about_story_en'          => 'ASLY TECH STORE is not an ordinary retail counter — it is an engineering-first technology studio and precision diagnostic lab built to set an uncompromising standard for hardware authenticity. Every device undergoes multi-point verification, and every repair is documented in real time via transparent QR telemetry.',
        'primary_phone'           => '01015981677',
        'primary_phone_label'     => 'مبيعات وصيانة - الرقم الرئيسي',
        'secondary_phone'         => '01154639718',
        'secondary_phone_label'   => 'مبيعات وصيانة - الرقم الإضافي',
        'primary_whatsapp'        => '201015981677',
        'primary_whatsapp_label'  => 'واتساب الاستفسارات والمبيعات',
        'secondary_whatsapp'      => '201154639718',
        'secondary_whatsapp_label'=> 'واتساب قسم الصيانة والدعم الفني',
        'store_address'           => 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا',
        'store_address_en'        => 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum',
        'working_hours'           => 'يومياً من ١١:٠٠ صباحاً حتى ١١:٠٠ مساءً',
        'working_hours_en'        => 'Daily from 11:00 AM to 11:00 PM',
        'primary_email'           => 'contact@aslystore.com',
        'google_maps_url'         => 'https://www.google.com/maps/search/?api=1&query=%D8%A7%D9%84%D9%81%D9%8A%D9%88%D9%85+%D8%B3%D9%86%D9%88%D8%B1%D8%B3+%D8%AA%D8%B1%D8%B3%D8%A7+%D8%B4%D8%A7%D8%B1%D8%B9+%D8%A7%D9%84%D8%B3%D9%88%D9%82+%D9%83%D9%86%D9%8A%D8%B3%D8%A9+%D8%AA%D8%B1%D8%B3%D8%A7',
        'social_facebook'         => 'https://facebook.com/aslytechstore',
        'social_instagram'        => 'https://instagram.com/aslytechstore',
        'social_tiktok'           => 'https://tiktok.com/@aslytechstore',
        'social_telegram'         => 'https://t.me/aslytechstore',
        'social_youtube'          => 'https://youtube.com/@aslytechstore',
        'social_x'                => 'https://x.com/aslytechstore',
        'invoice_footer_note'     => 'جميع الأجهزة تخضع لضمان الفحص المعتمد من مركز أصلي ASLY TECH STORE. يرجى الاحتفاظ بالفاتورة عند المراجعة.',
        'invoice_footer_note_en'  => 'All devices include ASLY TECH STORE certified hardware inspection & warranty. Please retain this invoice for service.',
        'seo_title_ar'            => 'أصلي تك ستور | ASLY TECH STORE — بيع وصيانة اللابتوبات والهواتف بالفيوم وسنورس وترسا',
        'seo_title_en'            => 'ASLY TECH STORE | Certified Laptops, Smartphones & Live QR Repair Lab in Faiyum, Egypt',
        'seo_description_ar'      => 'أصلي تك ستور (ASLY TECH STORE) في الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا: المركز الهندسي المعتمد للأجهزة الأصلية واللابتوبات والهواتف الذكية والساعات والإكسسوارات الأصلية ومعمل الصيانة الدقيق بتتبع الـ QR المباشر عبر 7 مراحل.',
        'seo_description_en'      => 'ASLY TECH STORE in Tersa, Sinnuris, Faiyum (Al-Souq St., In Front of Tersa Church): Engineering-led technology studio for certified authentic laptops, flagship smartphones, original accessories, and board-level repair with live 7-stage QR tracking.',
        'seo_keywords_ar'         => 'أصلي تك ستور, اصلي تك ستور, ASLY Tech Store, محل لابتوب الفيوم, صيانة لابتوب الفيوم, صيانة موبايلات سنورس, صيانة لابتوب وموبايل ترسا, لابتوبات استيراد وجديد الفيوم, إكسسوارات أصلية سنورس ترسا, مركز صيانة معتمد الفيوم, تتبع الصيانة بالـ QR, م حسام محمد, م رمضان سلام',
        'seo_keywords_en'         => 'ASLY Tech Store, laptop store Faiyum, laptop repair Faiyum Egypt, smartphone repair Sinnuris Tersa, certified workstations Faiyum, original tech accessories Egypt, QR repair tracking lab, Eng Hossam Mohamed, Eng Ramadan Sallam',
        'seo_ai_summary_ar'       => 'أصلي تك ستور (ASLY TECH STORE) هو المركز الهندسي الأول والموثوق في الفيوم (سنورس - ترسا - شارع السوق أمام كنيسة ترسا) المتخصص في توفير وفحص أجهزة اللابتوب والوركستيشن والهواتف الذكية والساعات والإكسسوارات الأصلية بنسبة 100%، إلى جانب معمل صيانة احترافي دقيق على مستوى البوردة مزود بنظام تتبع تفاعلي حي عبر كود الـ QR من 7 مراحل مع حماية كاملة للخصوصية ومسح تلقائي لبيانات التذكرة فور التسليم.',
        'seo_ai_summary_en'       => 'ASLY TECH STORE is the premier engineering-led technology studio and precision diagnostic repair lab in Faiyum Governorate, Egypt (Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum), supervised by Eng. Hossam Mohamed and Eng. Ramadan Sallam. It specializes in 100% authentic certified laptops, workstations, flagship smartphones, smartwatches, original accessories, and board-level hardware repair backed by an interactive 7-stage QR tracking system with automatic privacy wipe upon delivery.',
        'google_site_verification'=> '',
        'bing_site_verification'  => '',
        'public_base_url'         => APP_URL_ENV,
        'smtp_enabled'            => MAIL_ENABLED,
        'smtp_host'               => MAIL_HOST,
        'smtp_port'               => MAIL_PORT,
        'smtp_encryption'         => MAIL_ENCRYPTION,
        'smtp_username'           => MAIL_USERNAME,
        'smtp_password'           => MAIL_PASSWORD,
        'smtp_from_email'         => MAIL_FROM_ADDRESS,
        'smtp_from_name'          => MAIL_FROM_NAME,
    ];
}

function getAllSettings(PDO $pdo): array
{
    $map = getDefaultSettings();
    $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    foreach ($rows as $r) {
        $map[$r['setting_key']] = (string)($r['setting_value'] ?? '');
    }
    return $map;
}

function getWebsiteDisplayChannels(PDO $pdo, array $settings): array
{
    $list = [];
    $seen = [];

    $primaryPhone = trim((string)($settings['primary_phone'] ?? ''));
    if ($primaryPhone !== '') {
        $clean = preg_replace('/\s+/', '', $primaryPhone);
        $list[] = [
            'channel_type' => 'phone',
            'label'        => trim((string)($settings['primary_phone_label'] ?? '')) ?: 'مبيعات وصيانة - الرقم الرئيسي',
            'value'        => $primaryPhone,
            'url'          => 'tel:' . $clean,
        ];
        $seen['phone:' . $clean] = true;
    }

    $secondaryPhone = trim((string)($settings['secondary_phone'] ?? ''));
    if ($secondaryPhone !== '') {
        $clean = preg_replace('/\s+/', '', $secondaryPhone);
        if (!isset($seen['phone:' . $clean])) {
            $list[] = [
                'channel_type' => 'phone',
                'label'        => trim((string)($settings['secondary_phone_label'] ?? '')) ?: 'مبيعات وصيانة - الرقم الإضافي',
                'value'        => $secondaryPhone,
                'url'          => 'tel:' . $clean,
            ];
            $seen['phone:' . $clean] = true;
        }
    }

    $primaryWa = normalizeWhatsappNumber((string)($settings['primary_whatsapp'] ?? ''));
    if ($primaryWa !== '') {
        $displayWa = str_starts_with($primaryWa, '20') ? ('0' . substr($primaryWa, 2)) : ('+' . $primaryWa);
        $list[] = [
            'channel_type' => 'whatsapp',
            'label'        => trim((string)($settings['primary_whatsapp_label'] ?? '')) ?: 'واتساب الاستفسارات والمبيعات',
            'value'        => $displayWa,
            'url'          => 'https://wa.me/' . $primaryWa,
        ];
        $seen['whatsapp:' . $primaryWa] = true;
    }

    $secondaryWa = normalizeWhatsappNumber((string)($settings['secondary_whatsapp'] ?? ''));
    if ($secondaryWa !== '' && !isset($seen['whatsapp:' . $secondaryWa])) {
        $displayWa2 = str_starts_with($secondaryWa, '20') ? ('0' . substr($secondaryWa, 2)) : ('+' . $secondaryWa);
        $list[] = [
            'channel_type' => 'whatsapp',
            'label'        => trim((string)($settings['secondary_whatsapp_label'] ?? '')) ?: 'واتساب قسم الصيانة والدعم الفني',
            'value'        => $displayWa2,
            'url'          => 'https://wa.me/' . $secondaryWa,
        ];
        $seen['whatsapp:' . $secondaryWa] = true;
    }

    foreach (getActiveContactChannels($pdo, false) as $ch) {
        $type = strtolower(trim((string)($ch['channel_type'] ?? 'phone')));
        $val = trim((string)($ch['value'] ?? ''));
        if ($val === '') {
            continue;
        }
        $normKey = $type === 'whatsapp'
            ? ('whatsapp:' . normalizeWhatsappNumber($val))
            : ($type . ':' . preg_replace('/\s+/', '', $val));
        if (isset($seen[$normKey])) {
            continue;
        }
        $seen[$normKey] = true;
        $list[] = $ch;
    }

    return $list;
}

function saveSetting(PDO $pdo, string $key, string $value): void
{
    $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $check->execute([$key]);
    if ((int)$check->fetchColumn() > 0) {
        $upd = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $upd->execute([$value, $key]);
    } else {
        $ins = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $ins->execute([$key, $value]);
    }
}

function getActiveContactChannels(PDO $pdo, bool $includeInactive = false): array
{
    if ($includeInactive) {
        return $pdo->query("SELECT * FROM contact_channels ORDER BY sort_order ASC, id ASC")->fetchAll();
    }
    return $pdo->query("SELECT * FROM contact_channels WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
}

function getCategoryMeta(): array
{
    return [
        'laptops'     => ['ar' => 'لابتوب', 'en' => 'Laptops', 'prefix' => 'LAP'],
        'mobiles'     => ['ar' => 'هواتف ذكية', 'en' => 'Smartphones', 'prefix' => 'MOB'],
        'accessories' => ['ar' => 'إكسسوارات وصوتيات', 'en' => 'Audio & Accessories', 'prefix' => 'ACC'],
        'wearables'   => ['ar' => 'ساعات وأجهزة ذكية', 'en' => 'Smart Wearables', 'prefix' => 'WRB'],
    ];
}

function getConditionMeta(): array
{
    return [
        'new'      => ['ar' => 'جديد متبرشم', 'en' => 'Brand New • Sealed', 'badge' => 'new'],
        'open_box' => ['ar' => 'كسر زيرو', 'en' => 'Open Box • Like New', 'badge' => 'open_box'],
        'used'     => ['ar' => 'مستعمل فرز أول', 'en' => 'Certified Pre-Owned', 'badge' => 'used'],
    ];
}

function getMaintenanceStages(): array
{
    $en = isEn();
    return [
        'received' => [
            'order'    => 1,
            'icon'     => 'fa-solid fa-clipboard-check',
            'title'    => $en ? 'Device Received at Lab' : 'تم استلام الجهاز',
            'title_ar' => 'تم استلام الجهاز',
            'title_en' => 'Device Received at Lab',
            'desc'     => $en
                ? 'Your device has been logged at our diagnostic center and queued for hardware inspection.'
                : 'تم تسجيل الجهاز واستلامه بمركز الصيانة لبدء الفحص الفني.',
        ],
        'diagnosing' => [
            'order'    => 2,
            'icon'     => 'fa-solid fa-microscope',
            'title'    => $en ? 'Hardware Diagnostics in Progress' : 'قيد الفحص والتشخيص',
            'title_ar' => 'قيد الفحص والتشخيص',
            'title_en' => 'Hardware Diagnostics in Progress',
            'desc'     => $en
                ? 'Our engineers are running board-level & component diagnostics to pinpoint the issue accurately.'
                : 'يقوم مهندسو المركز حالياً بفحص الجهاز وتحديد العطل بدقة.',
        ],
        'waiting_approval' => [
            'order'    => 3,
            'icon'     => 'fa-solid fa-hourglass-half',
            'title'    => $en ? 'Awaiting Approval / Genuine Parts' : 'بانتظار موافقة العميل / قطع الغيار',
            'title_ar' => 'بانتظار موافقة العميل / قطع الغيار',
            'title_en' => 'Awaiting Approval / Genuine Parts',
            'desc'     => $en
                ? 'Repair plan and estimate are ready; awaiting confirmation or genuine part allocation.'
                : 'تم تحديد متطلبات الصيانة والتكلفة وفي انتظار التأكيد أو تجهيز القطعة الأصلية.',
        ],
        'repairing' => [
            'order'    => 4,
            'icon'     => 'fa-solid fa-screwdriver-wrench',
            'title'    => $en ? 'Precision Repair Underway' : 'جاري الإصلاح والصيانة',
            'title_ar' => 'جاري الإصلاح والصيانة',
            'title_en' => 'Precision Repair Underway',
            'desc'     => $en
                ? 'Active repair and calibration are currently underway using precision lab equipment.'
                : 'جاري العمل الفعلي على إصلاح العطل بأحدث أجهزة الصيانة.',
        ],
        'testing' => [
            'order'    => 5,
            'icon'     => 'fa-solid fa-gauge-high',
            'title'    => $en ? 'Final Stress Testing & QA' : 'الفحص والاختبار النهائي',
            'title_ar' => 'الفحص والاختبار النهائي',
            'title_en' => 'Final Stress Testing & QA',
            'desc'     => $en
                ? 'Repair complete — running thermal, battery, and stability tests to ensure peak reliability.'
                : 'تم الإصلاح وجاري اختبار كفاءة الجهاز تحت الضغط لضمان الجودة.',
        ],
        'ready' => [
            'order'    => 6,
            'icon'     => 'fa-solid fa-circle-check',
            'title'    => $en ? 'Ready for Showroom Pickup' : 'جاهز للاستلام بالمركز',
            'title_ar' => 'جاهز للاستلام بالمركز',
            'title_en' => 'Ready for Showroom Pickup',
            'desc'     => $en
                ? 'All tests passed! Your device is ready for pickup at ASLY Tech Store.'
                : 'اكتملت الصيانة بنجاح والجهاز جاهز الآن لتشريفكم بالاستلام.',
        ],
        'delivered' => [
            'order'    => 7,
            'icon'     => 'fa-solid fa-handshake-simple',
            'title'    => $en ? 'Delivered to Customer' : 'تم التسليم النهائي للعميل',
            'title_ar' => 'تم التسليم النهائي للعميل',
            'title_en' => 'Delivered to Customer',
            'desc'     => $en
                ? 'Handed over to customer and personal ticket data wiped per privacy protocol.'
                : 'تم تسليم الجهاز للعميل وإغلاق وحذف سجل البيانات الشخصية للتذكرة.',
        ],
    ];
}

function generateUniqueProductCode(PDO $pdo, string $category = 'laptops'): string
{
    $cats = getCategoryMeta();
    $prefix = $cats[$category]['prefix'] ?? 'PRD';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE barcode = ?");
    do {
        $code = 'ASLY' . $prefix . random_int(10000, 99999);
        $stmt->execute([$code]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $code;
}

function generateUniqueTicketCode(PDO $pdo): string
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM maintenance_tickets WHERE ticket_code = ?");
    $stmtRev = $pdo->prepare("SELECT COUNT(*) FROM maintenance_revenues WHERE ticket_code_ref = ?");
    do {
        $code = 'MNT-' . date('y') . '-' . random_int(10000, 99999);
        $stmt->execute([$code]);
        $exists1 = (int)$stmt->fetchColumn();
        $stmtRev->execute([$code]);
        $exists2 = (int)$stmtRev->fetchColumn();
    } while ($exists1 > 0 || $exists2 > 0);
    return $code;
}

function generateInvoiceNumber(PDO $pdo): string
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
    do {
        $num = 'ASLY-' . date('Ymd') . '-' . random_int(1000, 9999);
        $stmt->execute([$num]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $num;
}

function formatMoney(float|int|string $amount): string
{
    $formatted = number_format((float)$amount, 2, '.', ',');
    return isEn() ? ($formatted . ' EGP') : ($formatted . ' ج.م');
}

function normalizeWhatsappNumber(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, '00') === 0) {
        $digits = substr($digits, 2);
    }
    if (strpos($digits, '01') === 0 && strlen($digits) === 11) {
        return '2' . $digits; 
    }
    return $digits;
}

function handleProductImageUpload(array $file): ?string
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0775, true);
    }
    $maxSize = 8 * 1024 * 1024; 
    if (($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException(isEn() ? 'Image size must not exceed 8 MB.' : 'حجم الصورة يجب ألا يتجاوز 8 ميجابايت.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException(isEn() ? 'Unsupported image format. Please upload JPG, PNG, or WebP.' : 'صيغة الصورة غير مدعومة. يرجى رفع JPG أو PNG أو WebP.');
    }
    $filename = 'prd_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest = UPLOADS_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        if (!@copy($file['tmp_name'], $dest)) {
            throw new RuntimeException(isEn() ? 'Failed to save uploaded image.' : 'تعذر حفظ الصورة المرفوعة.');
        }
    }
    return 'uploads/products/' . $filename;
}

function getChannelFontAwesomeIcon(string $type): string
{
    return match (strtolower(trim($type))) {
        'whatsapp'  => 'fa-brands fa-whatsapp',
        'facebook'  => 'fa-brands fa-facebook-f',
        'instagram' => 'fa-brands fa-instagram',
        'tiktok'    => 'fa-brands fa-tiktok',
        'telegram'  => 'fa-brands fa-telegram',
        'youtube'   => 'fa-brands fa-youtube',
        'x', 'twitter' => 'fa-brands fa-x-twitter',
        'location', 'map', 'maps' => 'fa-solid fa-location-dot',
        'email'     => 'fa-solid fa-envelope',
        default     => 'fa-solid fa-phone-volume',
    };
}

function getSocialPlatforms(array $settings): array
{
    $en = isEn();
    $waNum = normalizeWhatsappNumber((string)($settings['primary_whatsapp'] ?? ''));
    $waUrl = $waNum !== '' ? ('https://wa.me/' . $waNum) : '';

    $raw = [
        [
            'key'     => 'whatsapp',
            'name'    => $en ? 'WhatsApp Direct' : 'واتساب المباشر',
            'sub'     => $en ? 'Instant Engineering & Support Chat' : 'تواصل مباشر مع فريقنا الهندسي',
            'icon'    => 'fa-brands fa-whatsapp',
            'accent'  => 'emerald',
            'url'     => $waUrl,
            'handle'  => '+' . $waNum,
        ],
        [
            'key'     => 'facebook',
            'name'    => $en ? 'Facebook' : 'فيسبوك',
            'sub'     => $en ? 'Official Announcements & Community' : 'الصفحة الرسمية وأحدث الأخبار',
            'icon'    => 'fa-brands fa-facebook-f',
            'accent'  => 'aqua',
            'url'     => trim((string)($settings['social_facebook'] ?? '')),
            'handle'  => '@aslytechstore',
        ],
        [
            'key'     => 'instagram',
            'name'    => $en ? 'Instagram' : 'إنستجرام',
            'sub'     => $en ? 'Studio Lookbook & Lab Reels' : 'جولات المعرض وكواليس معمل الصيانة',
            'icon'    => 'fa-brands fa-instagram',
            'accent'  => 'gold',
            'url'     => trim((string)($settings['social_instagram'] ?? '')),
            'handle'  => '@aslytechstore',
        ],
        [
            'key'     => 'tiktok',
            'name'    => $en ? 'TikTok' : 'تيك توك',
            'sub'     => $en ? 'Hardware Teardowns & Tech Tips' : 'فيديوهات الفحص الهندسي ونصائح الأجهزة',
            'icon'    => 'fa-brands fa-tiktok',
            'accent'  => 'slate',
            'url'     => trim((string)($settings['social_tiktok'] ?? '')),
            'handle'  => '@aslytechstore',
        ],
        [
            'key'     => 'telegram',
            'name'    => $en ? 'Telegram' : 'تيليجرام',
            'sub'     => $en ? 'Instant Updates & Broadcast Channel' : 'قناة التحديثات والتنبيهات السريعة',
            'icon'    => 'fa-brands fa-telegram',
            'accent'  => 'aqua',
            'url'     => trim((string)($settings['social_telegram'] ?? '')),
            'handle'  => 't.me/aslytechstore',
        ],
        [
            'key'     => 'youtube',
            'name'    => $en ? 'YouTube' : 'يوتيوب',
            'sub'     => $en ? 'Deep-Dive Reviews & Lab Sessions' : 'مراجعات تفصيلية وتوثيق الصيانة الدقيقة',
            'icon'    => 'fa-brands fa-youtube',
            'accent'  => 'gold',
            'url'     => trim((string)($settings['social_youtube'] ?? '')),
            'handle'  => 'ASLY Tech Studio',
        ],
        [
            'key'     => 'x',
            'name'    => $en ? 'X (Twitter)' : 'منصة إكس',
            'sub'     => $en ? 'Quick Tech Insights & Support' : 'تحديثات سريعة ودعم فوري',
            'icon'    => 'fa-brands fa-x-twitter',
            'accent'  => 'slate',
            'url'     => trim((string)($settings['social_x'] ?? '')),
            'handle'  => '@aslytechstore',
        ],
    ];

    $active = [];
    foreach ($raw as $item) {
        if ($item['url'] !== '') {
            
            if ($item['key'] !== 'whatsapp' && preg_match('#https?://[^/]+/(@?[A-Za-z0-9._-]+)#', $item['url'], $m)) {
                $h = trim($m[1], '/');
                if ($h !== '') {
                    $item['handle'] = str_starts_with($h, '@') ? $h : ('@' . $h);
                }
            }
            $active[] = $item;
        }
    }
    return $active;
}

