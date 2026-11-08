<?php
/**
 * ASLY | أصلي TECH STORE
 * Bilingual Internationalization Engine (Arabic RTL + English LTR) — Zero Emojis
 */

declare(strict_types=1);

function currentLang(): string
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }

    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) {
        $lang = (string)$_GET['lang'];
        $_SESSION['asly_lang'] = $lang;
        if (!headers_sent()) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
            setcookie('asly_lang', $lang, [
                'expires'  => time() + 86400 * 365,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => false,
                'samesite' => $isHttps ? 'None' : 'Lax',
            ]);
        }
        $resolved = $lang;
        return $resolved;
    }

    if (!empty($_SESSION['asly_lang']) && in_array($_SESSION['asly_lang'], ['ar', 'en'], true)) {
        $resolved = (string)$_SESSION['asly_lang'];
        return $resolved;
    }

    if (!empty($_COOKIE['asly_lang']) && in_array($_COOKIE['asly_lang'], ['ar', 'en'], true)) {
        $resolved = (string)$_COOKIE['asly_lang'];
        return $resolved;
    }

    $resolved = 'ar';
    return $resolved;
}

function isEn(): bool
{
    return currentLang() === 'en';
}

function htmlDir(): string
{
    return isEn() ? 'ltr' : 'rtl';
}

function langSwitchUrl(string $targetLang): string
{
    $params = $_GET;
    $params['lang'] = $targetLang;
    $uri = strtok($_SERVER['REQUEST_URI'] ?? 'index.php', '?') ?: 'index.php';
    return $uri . '?' . http_build_query($params);
}

function pickLangField(array $row, string $baseKey): string
{
    if (isEn()) {
        $enVal = trim((string)($row[$baseKey . '_en'] ?? ''));
        if ($enVal !== '') {
            return $enVal;
        }
    }
    return (string)($row[$baseKey] ?? '');
}

function translateChannelLabel(string $label, string $type): string
{
    if (!isEn()) {
        return $label;
    }
    $map = [
        'مبيعات وصيانة - م. حسام محمد' => 'Engineering & Service — Eng. Hossam Mohamed',
        'مبيعات وصيانة - م. رمضان سلام' => 'Engineering & Service — Eng. Ramadan Sallam',
        'واتساب المبيعات والاستفسار عن الأسعار' => 'Official Inquiries WhatsApp',
        'واتساب قسم الصيانة والدعم الفني' => 'Repair Lab & Technical Support WhatsApp',
    ];
    if (isset($map[$label])) {
        return $map[$label];
    }
    if (preg_match('/\p{Arabic}/u', $label)) {
        $typeNames = [
            'phone' => 'Direct Phone Line',
            'whatsapp' => 'Official WhatsApp',
            'facebook' => 'Facebook Page',
            'instagram' => 'Instagram Official',
            'tiktok' => 'TikTok Account',
            'telegram' => 'Telegram Channel',
            'youtube' => 'YouTube Channel',
            'x' => 'X Official',
            'email' => 'Official Email',
            'location' => 'Studio Location',
        ];
        return $typeNames[$type] ?? 'Direct Channel';
    }
    return $label;
}

function t(string $key): string
{
    static $dict = [
        // Navigation & Global
        'nav_about' => ['ar' => 'من نحن', 'en' => 'Who We Are'],
        'nav_craft' => ['ar' => 'تخصصاتنا', 'en' => 'Our Craft'],
        'nav_lab' => ['ar' => 'متابعة الصيانة', 'en' => 'Repair Tracker'],
        'nav_social' => ['ar' => 'منصاتنا', 'en' => 'Social Hub'],
        'nav_faq' => ['ar' => 'الأسئلة', 'en' => 'FAQ'],
        'nav_contact' => ['ar' => 'العنوان والتواصل', 'en' => 'Contact'],
        'nav_track_btn' => ['ar' => 'تتبع صيانة جهازك', 'en' => 'Track Repair'],
        'nav_staff_portal' => ['ar' => 'بوابة الإدارة', 'en' => 'Admin Portal'],
        'lang_switch_label' => ['ar' => 'EN', 'en' => 'عربي'],
        'currency' => ['ar' => 'ج.م', 'en' => 'EGP'],

        // Kinetic Marquee Ribbons
        'marquee_1' => ['ar' => 'استوديو التقنية الأصلية ومعمل الفحص الهندسي', 'en' => 'AUTHENTIC TECH STUDIO & DIAGNOSTIC LAB'],
        'marquee_2' => ['ar' => 'تتبع مباشر لمراحل الصيانة عبر رمز QR', 'en' => 'LIVE 7-STAGE QR REPAIR TELEMETRY'],
        'marquee_3' => ['ar' => 'لابتوبات احترافية • هواتف رائدة • صيانة دقيقة على مستوى البوردة', 'en' => 'WORKSTATIONS • FLAGSHIP PHONES • BOARD-LEVEL REPAIR'],
        'marquee_4' => ['ar' => 'حماية كاملة للخصوصية ومسح تلقائي لبيانات التذكرة فور التسليم', 'en' => 'PRIVACY-FIRST AUTO-WIPE PROTOCOL UPON DELIVERY'],

        // Hero Collage
        'hero_eyebrow' => ['ar' => '01 // هويتنا الهندسية • ASLY TECH STUDIO', 'en' => '01 // IDENTITY & LAB • ASLY TECH STUDIO'],
        'hero_title_1' => ['ar' => 'حيث تلتقي', 'en' => 'WHERE PURE'],
        'hero_title_2' => ['ar' => 'الأصالة التقنية', 'en' => 'HARDWARE CRAFT'],
        'hero_title_3' => ['ar' => 'بالدقة الهندسية.', 'en' => 'MEETS PRECISION.'],
        'hero_subtitle' => [
            'ar' => 'أصلي تك ستور هو وجهتك الموثوقة للأجهزة الأصلية المفحوصة هندسياً ومعمل الصيانة الذكي المدعوم بنظام تتبع تفاعلي عبر رمز الـ QR لحظة بلحظة.',
            'en' => 'ASLY Tech Store is an engineering-led technology house and precision diagnostic studio — uniting certified authentic hardware with transparent, real-time QR repair tracking.'
        ],
        'hero_cta_track' => ['ar' => 'تتبع حالة جهازك بالـ QR', 'en' => 'Track Repair Status'],
        'hero_cta_who' => ['ar' => 'تعرّف علينا أكثر', 'en' => 'Discover Who We Are'],
        'hero_cta_social' => ['ar' => 'تابع منصاتنا الرسمية', 'en' => 'Our Social Channels'],

        // Who We Are Mosaic
        'who_eyebrow' => ['ar' => '02 // من نحن • WHO WE ARE', 'en' => '02 // WHO WE ARE • THE STUDIO'],
        'who_title' => ['ar' => 'أكثر من مجرد متجر — منظومة هندسية مبنية على الثقة والشفافية.', 'en' => 'More Than A Store — An Engineering Standard Built On Trust.'],
        'pillar_1_tag' => ['ar' => 'الفلسفة • 01', 'en' => '01 // PHILOSOPHY'],
        'pillar_1_title' => ['ar' => 'فحص بعين مهندس مختص في كل تفصيلة.', 'en' => 'Engineered Verification, Zero Guesswork.'],
        'pillar_1_desc' => [
            'ar' => 'نؤمن أن اقتناء أو صيانة جهاز تقني يعتمد أولاً على الأمانة الهندسية؛ لذلك يمر كل جهاز باختبارات دقيقة للأداء، الحرارة، البطارية، والقطع الأصلية.',
            'en' => 'Every laptop, smartphone, and board passing through ASLY undergoes calibrated thermal, power, storage, and component verification.'
        ],
        'pillar_2_tag' => ['ar' => 'معمل الصيانة • 02', 'en' => '02 // MICRO-LAB'],
        'pillar_2_title' => ['ar' => 'تشخيص وصيانة احترافية بتوثيق كامل.', 'en' => 'Board-Level Diagnostics & Cleanroom Care.'],
        'pillar_2_desc' => [
            'ar' => 'معمل مجهز لصيانة اللابتوبات الاحترافية والهواتف الرائدة باستخدام أدوات قياس دقيقة وقطع معتمدة مع تواصل مباشر بينك وبين المهندس.',
            'en' => 'Equipped for workstation laptops and flagship smartphones with direct two-way communication between you and your engineer.'
        ],
        'pillar_3_tag' => ['ar' => 'الخصوصية • 03', 'en' => '03 // PRIVACY SHIELD'],
        'pillar_3_title' => ['ar' => 'شفافية كاملة أثناء الصيانة ومسح فوري عند الاستلام.', 'en' => 'Live QR Updates & Automatic Data Wipe.'],
        'pillar_3_desc' => [
            'ar' => 'تتابع كل مرحلة عبر كود الـ QR المطبوع على إيصالك أو عبر البريد الإلكتروني، وفور تسليمك الجهاز تُحذف سجلاتك الشخصية تلقائياً.',
            'en' => 'Follow all 7 stages via your receipt QR code and email alerts — then enjoy automatic personal record deletion the moment you pick up your device.'
        ],

        // Domains of Craft (No Products, Pure Capabilities)
        'craft_eyebrow' => ['ar' => '03 // مجالات تخصصنا • DOMAINS OF CRAFT', 'en' => '03 // DOMAINS OF CRAFT • WHAT WE DO'],
        'craft_title' => ['ar' => 'أربعة أركان تصنع تجربة أصلي', 'en' => 'Four Pillars Defining The ASLY Experience'],
        'craft_1_title' => ['ar' => 'أجهزة اللابتوب والووركستيشن الاحترافية', 'en' => 'Workstations & Creator Laptops'],
        'craft_1_sub' => ['ar' => 'اختيار وفحص أجهزة التصميم الهندسي، البرمجة، والمونتاج بأعلى معايير الكفاءة الحرارية.', 'en' => 'Precision selection, thermal calibration, and stress-testing for engineering, coding, and studio laptops.'],
        'craft_2_title' => ['ar' => 'الهواتف الذكية الرائدة والأجهزة الأصلية', 'en' => 'Flagship Smartphones & Ecosystems'],
        'craft_2_sub' => ['ar' => 'فحص أصالة المكونات، كفاءة البطارية، والشاشات لضمان تجربة استخدام خالية من المفاجآت.', 'en' => 'Authentic flagship mobility backed by strict component verification and documented testing warranty.'],
        'craft_3_title' => ['ar' => 'معمل الصيانة الدقيقة وتتبع الـ QR التفاعلي', 'en' => 'Precision Repair & Live QR Telemetry'],
        'craft_3_sub' => ['ar' => 'صيانة الهاردوير والسوفتوير في 7 مراحل واضحة يمكنك متابعتها والرد على المهندس من هاتفك.', 'en' => 'Hardware & board-level restoration across 7 transparent stages you can track and reply to from your phone.'],
        'craft_4_title' => ['ar' => 'الساعات الذكية والصوتيات والحلول المعتمدة', 'en' => 'Smart Wearables & Studio Acoustics'],
        'craft_4_sub' => ['ar' => 'حلول شحن وصوتيات وإكسسوارات أصلية تحافظ على عمر أجهزتك وأدائها اليومي.', 'en' => 'Certified charging, spatial acoustics, and smart wearables curated to protect your devices every day.'],

        // Interactive Repair Lab Console
        'lab_eyebrow' => ['ar' => '04 // بوابة متابعة الصيانة الحية • LIVE QR TELEMETRY', 'en' => '04 // LIVE REPAIR TELEMETRY • QR TRACKER'],
        'lab_title' => ['ar' => 'تابع جهازك في الصيانة الآن بخطوة واحدة', 'en' => 'Inspect Your Device Repair Status Instantly'],
        'lab_desc' => [
            'ar' => 'أدخل كود التذكرة المطبوع على إيصال الاستلام لعرض المرحلة الحالية فوراً داخل الصفحة، أو امسح كود الـ QR بالكاميرا لفتح سجل المحادثة الكامل مع المهندس.',
            'en' => 'Enter your receipt ticket code to preview live stage progress right here, or scan your printed QR sticker with your camera to open two-way engineer notes.'
        ],
        'lab_input_ph' => ['ar' => 'اكتب كود التذكرة — مثال: MNT-2026-101', 'en' => 'Enter Ticket Code — e.g. MNT-2026-101'],
        'lab_btn_instant' => ['ar' => 'فحص فوري للحالة', 'en' => 'Check Status Now'],
        'lab_btn_scan_qr' => ['ar' => 'مسح QR بالكاميرا', 'en' => 'Scan QR Receipt'],
        'lab_btn_open_full' => ['ar' => 'فتح صفحة المحادثة والملاحظات الكاملة', 'en' => 'Open Full Interactive Ticket & Reply'],
        'lab_try_demo' => ['ar' => 'جرّب كود تذكرة حي للتجربة: MNT-2026-101', 'en' => 'Test live demo ticket: MNT-2026-101'],

        // Social Universe & Direct Contact
        'social_eyebrow' => ['ar' => '05 // مجتمع أصلي ومنصات التواصل • CONNECT WITH US', 'en' => '05 // SOCIAL UNIVERSE • FOLLOW & CONNECT'],
        'social_title' => ['ar' => 'كن قريباً منا على منصاتنا الرسمية', 'en' => 'Follow Our Studio Across Social Media'],
        'social_sub' => [
            'ar' => 'تابع كواليس الفحص الهندسي، نصائح الحفاظ على أجهزتك، وأحدث أخبار المركز عبر حساباتنا الرسمية أو تواصل معنا مباشرة.',
            'en' => 'Explore behind-the-scenes diagnostics, hardware care tips, and studio updates across our official platforms — or reach our engineers directly.'
        ],
        'contact_eyebrow' => ['ar' => '06 // المقر ومواعيد العمل • VISIT THE STUDIO', 'en' => '06 // VISIT & DIRECT LINES • STUDIO CONCIERGE'],
        'contact_title' => ['ar' => 'تشرفنا زيارتك أو تواصلك المباشر', 'en' => 'Direct Engineering Lines & Location'],
        'contact_hours' => ['ar' => 'مواعيد استقبال العملاء', 'en' => 'Studio Hours'],
        'contact_location' => ['ar' => 'عنوان المركز', 'en' => 'Studio Address'],
        'contact_map_btn' => ['ar' => 'افتح الاتجاهات على خرائط جوجل', 'en' => 'Open Directions in Google Maps'],
        'footer_rights' => ['ar' => 'جميع الحقوق محفوظة', 'en' => 'All rights reserved'],
        'close' => ['ar' => 'إغلاق', 'en' => 'Close'],

        // FAQ Accordion
        'faq_eyebrow' => ['ar' => '07 // أسئلة متكررة • GOOD TO KNOW', 'en' => '07 // FAQ • GOOD TO KNOW'],
        'faq_title' => ['ar' => 'كل ما تود معرفته عن أصلي', 'en' => 'Answers To Common Questions'],
        'faq_q1' => ['ar' => 'كيف أتابع حالة جهازي الموجود في قسم الصيانة؟', 'en' => 'How do I track my device while it is in the repair lab?'],
        'faq_a1' => [
            'ar' => 'عند استلام جهازك تحصل على إيصال مطبوع يحمل كود تذكرة ورمز QR خاص بك؛ يمكنك إدخال الكود مباشرة في قسم متابعة الصيانة أعلى الصفحة أو مسح الـ QR بهاتفك لمعرفة المرحلة الحالية ومراسلة المهندس.',
            'en' => 'When you drop off your device, you receive a printed receipt with a unique ticket code and QR sticker. Enter the code above or scan the QR with your phone to view live stages and message your technician.'
        ],
        'faq_q2' => ['ar' => 'كيف يحافظ النظام على خصوصية بياناتي بعد استلام جهاز الصيانة؟', 'en' => 'How is my personal data protected after I pick up my repaired device?'],
        'faq_a2' => [
            'ar' => 'فور تسليم الجهاز لك وإغلاق التذكرة، يقوم النظام تلقائياً بحذف كافة البيانات الشخصية وملاحظات وتفاصيل العطل نهائياً من سجل التتبع العام حفاظاً على خصوصيتك ١٠٠٪.',
            'en' => 'The moment your device is handed back to you, our system automatically wipes your personal details, device serials, and conversation notes from the tracking database.'
        ],
        'faq_q3' => ['ar' => 'كيف أستفسر عن توفر جهاز معين أو أحجز موعد فحص؟', 'en' => 'How can I ask about a specific device or book a diagnostic checkup?'],
        'faq_a3' => [
            'ar' => 'تواصل معنا بضغطة واحدة عبر واتساب المباشر أو صفحاتنا الرسمية على فيسبوك وإنستجرام وتيك توك الموجودة في قسم المنصات الرسمية، وسيرد عليك فريقنا الهندسي فوراً.',
            'en' => 'Tap our Direct WhatsApp button or message us on Facebook, Instagram, TikTok, or Telegram in our Social Hub above — our engineering team responds promptly.'
        ],

        // Track Page
        'track_page_title' => ['ar' => 'تتبع حالة الصيانة المباشر', 'en' => 'Live Repair Status Portal'],
        'track_back_home' => ['ar' => 'العودة للصفحة الرئيسية', 'en' => 'Back to Home'],
        'track_wa_support' => ['ar' => 'واتساب الصيانة', 'en' => 'Repair WhatsApp'],
        'track_heading' => ['ar' => 'بوابة التتبع المباشر لأجهزة الصيانة', 'en' => 'Interactive QR Device Repair Tracker'],
        'track_subheading' => [
            'ar' => 'أدخل رقم تذكرة الصيانة أو امسح رمز الـ QR المطبوع على الإيصال لمتابعة مراحل الفحص والإصلاح والتواصل مع المهندس المختص.',
            'en' => 'Enter your repair ticket number or scan the QR code on your receipt to follow diagnostics, stages, and communicate directly with your engineer.'
        ],
        'track_search_btn' => ['ar' => 'بحث وعرض الحالة', 'en' => 'Lookup Status'],
        'track_not_found' => ['ar' => 'لم يتم العثور على تذكرة صيانة بهذا الكود. يرجى التأكد من الرقم المكتوب على إيصال الاستلام.', 'en' => 'No active repair ticket found with this code. Please verify the code printed on your receipt.'],
        'track_delivered_title' => ['ar' => 'تم تسليم الجهاز للعميل بنجاح', 'en' => 'Device Delivered & Ticket Closed'],
        'track_delivered_desc' => [
            'ar' => 'تم تسليم هذا الجهاز بنجاح، وكما نلتزم في أصلي تك ستور تم حذف كافة البيانات التفصيلية والملاحظات الخاصة بالجهاز فور التسليم حفاظاً على خصوصية العميل.',
            'en' => 'This device has been handed over, and in accordance with ASLY’s privacy guarantee, all personal ticket details and notes were permanently wiped upon delivery.'
        ],
        'track_verified_ticket' => ['ar' => 'تذكرة صيانة معتمدة', 'en' => 'Verified Repair Ticket'],
        'track_client_name' => ['ar' => 'اسم العميل', 'en' => 'Customer Name'],
        'track_specs' => ['ar' => 'المواصفات المسجلة', 'en' => 'Recorded Specs'],
        'track_issue' => ['ar' => 'وصف العطل', 'en' => 'Reported Issue'],
        'track_cost_deposit' => ['ar' => 'التكلفة المقدرة / العربون', 'en' => 'Estimated Cost / Deposit'],
        'track_paid_prefix' => ['ar' => 'مدفوع', 'en' => 'Paid'],
        'track_timeline_title' => ['ar' => 'مراحل الفحص والصيانة والتعليقات المتبادلة', 'en' => 'Repair Progress & Two-Way Engineer Notes'],
        'track_timeline_sub' => ['ar' => 'يمكنك قراءة ملاحظات المهندس على كل مرحلة وإضافة تعليقك أو ردك مباشرة أسفل أي مرحلة.', 'en' => 'Read technician updates on each stage and post your reply or question directly below any stage.'],
        'track_current_badge' => ['ar' => 'المرحلة الحالية', 'en' => 'Current Stage'],
        'track_completed_badge' => ['ar' => 'مكتملة', 'en' => 'Completed'],
        'track_add_note_btn' => ['ar' => 'أضف ملاحظة على هذه المرحلة', 'en' => 'Add Note on Stage'],
        'track_phone_verify_ph' => ['ar' => 'لتأكيد هويتك: أدخل آخر 4 أرقام من رقم هاتفك المسجل', 'en' => 'Verify ownership: enter the last 4 digits of your registered phone'],
        'track_note_ph' => ['ar' => 'اكتب ملاحظتك أو ردك لمهندس الصيانة...', 'en' => 'Write your note or question for the repair engineer...'],
        'track_send_note' => ['ar' => 'إرسال الملاحظة', 'en' => 'Send Note'],
        'track_sending' => ['ar' => 'جاري الإرسال...', 'en' => 'Sending...'],
        'track_eng_prefix' => ['ar' => 'إدارة الصيانة', 'en' => 'Repair Engineer'],
        'track_cust_prefix' => ['ar' => 'العميل', 'en' => 'Customer'],

        // Invoice Page
        'inv_not_found' => ['ar' => 'الفاتورة غير موجودة', 'en' => 'Invoice Not Found'],
        'inv_not_found_sub' => ['ar' => 'يرجى التأكد من رابط الفاتورة المرسل إليك من مركز أصلي تك ستور.', 'en' => 'Please check the invoice link sent to you by ASLY Tech Store.'],
        'inv_download_png' => ['ar' => 'تحميل الفاتورة كصورة (PNG)', 'en' => 'Download Invoice Image (PNG)'],
        'inv_print' => ['ar' => 'طباعة الفاتورة', 'en' => 'Print Invoice'],
        'inv_sales_title' => ['ar' => 'فاتورة مبيعات معتمدة', 'en' => 'OFFICIAL SALES INVOICE'],
        'inv_customer' => ['ar' => 'العميل', 'en' => 'Customer'],
        'inv_phone' => ['ar' => 'رقم الهاتف', 'en' => 'Phone'],
        'inv_payment' => ['ar' => 'طريقة الدفع', 'en' => 'Payment Method'],
        'inv_cashier' => ['ar' => 'مسؤول المبيعات', 'en' => 'Sales Rep'],
        'inv_col_item' => ['ar' => 'المنتج / البيان', 'en' => 'Product / Description'],
        'inv_col_warranty' => ['ar' => 'السيريال / الضمان', 'en' => 'Serial / Warranty'],
        'inv_col_qty' => ['ar' => 'الكمية', 'en' => 'Qty'],
        'inv_col_unit' => ['ar' => 'سعر الوحدة', 'en' => 'Unit Price'],
        'inv_col_total' => ['ar' => 'الإجمالي', 'en' => 'Subtotal'],
        'inv_grand_total' => ['ar' => 'الإجمالي النهائي المدفوع', 'en' => 'Total Amount Paid'],
        'inv_notes' => ['ar' => 'ملاحظات', 'en' => 'Notes'],

        // Login Page
        'login_title' => ['ar' => 'بوابة الإدارة ونقطة البيع والصيانة الذكية', 'en' => 'Smart POS, Inventory & Repair Management'],
        'login_user_lbl' => ['ar' => 'اسم المستخدم', 'en' => 'Username'],
        'login_user_ph' => ['ar' => 'مثال: admin أو staff1', 'en' => 'e.g. admin or staff1'],
        'login_pass_lbl' => ['ar' => 'كلمة المرور', 'en' => 'Password'],
        'login_btn' => ['ar' => 'تسجيل الدخول للوحة التحكم', 'en' => 'Sign In to Dashboard'],
        'login_demo_hint' => ['ar' => 'تجربة سريعة للحسابات الافتراضية:', 'en' => 'Quick Demo Sign-In Presets:'],
        'login_demo_admin' => ['ar' => 'دخول كمدير (admin)', 'en' => 'Admin Account (admin)'],
        'login_demo_staff' => ['ar' => 'دخول كموظف مبيعات (staff1)', 'en' => 'Sales Staff (staff1)'],
        'login_back_store' => ['ar' => 'العودة للصفحة الرئيسية', 'en' => 'Return to Public Website'],
    ];

    $lang = currentLang();
    return $dict[$key][$lang] ?? ($dict[$key]['ar'] ?? $key);
}
