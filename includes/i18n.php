<?php
/**
 * ASLY | أصلي TECH STORE
 * Bilingual Internationalization Engine (Arabic RTL + English LTR)
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

/**
 * Pick localized field from a product/channel row with graceful fallback
 */
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

/**
 * Translate channel labels stored in DB when viewing in English
 */
function translateChannelLabel(string $label, string $type): string
{
    if (!isEn()) {
        return $label;
    }
    $map = [
        'مبيعات وصيانة - م. حسام محمد' => 'Sales & Service — Eng. Hossam Mohamed',
        'مبيعات وصيانة - م. رمضان سلام' => 'Sales & Service — Eng. Ramadan Sallam',
        'واتساب المبيعات والاستفسار عن الأسعار' => 'Sales & Price Inquiry WhatsApp',
        'واتساب قسم الصيانة والدعم الفني' => 'Maintenance & Technical Support WhatsApp',
    ];
    if (isset($map[$label])) {
        return $map[$label];
    }
    // If label contains Arabic characters and isn't in static map, provide clean English fallback prefix
    if (preg_match('/\p{Arabic}/u', $label)) {
        $typeNames = [
            'phone' => 'Direct Phone Line',
            'whatsapp' => 'Official WhatsApp',
            'facebook' => 'Facebook Page',
            'instagram' => 'Instagram Official',
            'tiktok' => 'TikTok Account',
            'telegram' => 'Telegram Channel',
            'email' => 'Official Email',
            'location' => 'Showroom Location',
        ];
        return $typeNames[$type] ?? 'Contact Channel';
    }
    return $label;
}

function t(string $key): string
{
    static $dict = [
        // Navigation & Global
        'nav_collections' => ['ar' => 'المجموعات', 'en' => 'Collections'],
        'nav_catalog' => ['ar' => 'المعرض', 'en' => 'Catalog'],
        'nav_story' => ['ar' => 'فلسفة أصلي', 'en' => 'The Idea'],
        'nav_lab' => ['ar' => 'معمل الصيانة', 'en' => 'Repair Lab'],
        'nav_gallery' => ['ar' => 'في الحياة اليومية', 'en' => 'In Real Life'],
        'nav_faq' => ['ar' => 'الأسئلة الشائعة', 'en' => 'FAQ'],
        'nav_contact' => ['ar' => 'تواصل معنا', 'en' => 'Contact'],
        'nav_track_btn' => ['ar' => 'تتبع جهاز صيانة', 'en' => 'Track Repair'],
        'nav_search_btn' => ['ar' => 'بحث', 'en' => 'Search'],
        'nav_staff_portal' => ['ar' => 'بوابة الإدارة', 'en' => 'Staff Portal'],
        'lang_switch_label' => ['ar' => 'EN', 'en' => 'عربي'],
        'currency' => ['ar' => 'ج.م', 'en' => 'EGP'],

        // Announcement Marquee
        'marquee_1' => ['ar' => 'أجهزة أصلية مفحوصة هندسياً ١٠٠٪', 'en' => '100% ENGINEER-VERIFIED AUTHENTIC TECH'],
        'marquee_2' => ['ar' => 'صيانة احترافية بتتبع مباشر عبر رمز QR', 'en' => 'LIVE QR-TRACKED PRECISION DIAGNOSTICS'],
        'marquee_3' => ['ar' => 'لابتوبات • هواتف ذكية • ساعات ذكية • إكسسوارات أصلية', 'en' => 'LAPTOPS • SMARTPHONES • WEARABLES • ORIGINAL ACCESSORIES'],
        'marquee_4' => ['ar' => 'ضمان الفحص والتجربة المعتمد من ASLY TECH STORE', 'en' => 'CERTIFIED TESTING & WARRANTY BY ASLY TECH STORE'],

        // Hero Lookbook
        'hero_eyebrow' => ['ar' => 'مجموعة ٢٠٢٦ المختارة • ASLY LOOKBOOK', 'en' => '2026 CURATED EDITION • ASLY LOOKBOOK'],
        'hero_title' => ['ar' => 'تقنية أصلية، مصممة لحياتك اليومية.', 'en' => 'Original Tech, Curated For Everyday Life.'],
        'hero_subtitle' => [
            'ar' => 'لابتوبات احترافية، هواتف رائدة، إكسسوارات منتقاة، ومعمل صيانة ذكي بتتبع فوري عبر الـ QR — بإشراف هندسي يضمن لك راحة البال في كل تفصيلة.',
            'en' => 'Workstation laptops, flagship smartphones, refined accessories, and a live QR-tracked repair studio — engineer-inspected for effortless daily performance.'
        ],
        'hero_cta_explore' => ['ar' => 'استكشف المجموعة الكاملة', 'en' => 'Explore Collection'],
        'hero_cta_track' => ['ar' => 'تتبع حالة صيانة جهازك', 'en' => 'Track Your Device'],
        'hero_badge_1_top' => ['ar' => 'فحص هندسي شامل', 'en' => 'Engineer Verified'],
        'hero_badge_1_sub' => ['ar' => 'قبل عرض أي جهاز بالمعرض', 'en' => 'Multi-point hardware check'],
        'hero_badge_2_top' => ['ar' => 'تتبع صيانة بالـ QR', 'en' => 'Live QR Repair'],
        'hero_badge_2_sub' => ['ar' => 'شفافية كاملة مرحلة بمرحلة', 'en' => 'Real-time stage transparency'],

        // Curated Collections Strip
        'collections_eyebrow' => ['ar' => 'تسوق حسب الفئة', 'en' => 'BROWSE BY CATEGORY'],
        'collections_title' => ['ar' => 'مجموعاتنا المختارة', 'en' => 'Curated Tech Collections'],
        'cat_all' => ['ar' => 'كل المعروضات', 'en' => 'All Pieces'],
        'cat_laptops' => ['ar' => 'لابتوب ووركستيشن', 'en' => 'Laptops & Workstations'],
        'cat_mobiles' => ['ar' => 'هواتف ذكية', 'en' => 'Smartphones'],
        'cat_accessories' => ['ar' => 'صوتيات وإكسسوارات', 'en' => 'Audio & Accessories'],
        'cat_wearables' => ['ar' => 'ساعات وأجهزة ذكية', 'en' => 'Smart Wearables'],

        // Catalog Showcase (Zero Public Prices!)
        'catalog_eyebrow' => ['ar' => 'معرض أصلي الحصري • بدون قوائم أسعار ثابتة لضمان أفضل عرض يومي', 'en' => 'THE SHOWROOM EDIT • DIRECT WHATSAPP CONCIERGE'],
        'catalog_title' => ['ar' => 'أحدث الأجهزة المتوفرة بالمعرض', 'en' => 'Featured Devices & Essentials'],
        'catalog_search_ph' => ['ar' => 'ابحث عن موديل لابتوب، آيفون، سماعة، ساعة...', 'en' => 'Search MacBook, Dell, iPhone, Watch, ANC...'],
        'badge_featured' => ['ar' => 'مختار بعناية', 'en' => 'Editor’s Pick'],
        'hover_hint' => ['ar' => 'مرّر لمشاهدة اللقطة الحية', 'en' => 'Hover for lifestyle view'],
        'btn_inquire_wa' => ['ar' => 'اسأل عن التوفر والعرض عبر واتساب', 'en' => 'Inquire on WhatsApp'],
        'btn_quick_view' => ['ar' => 'التفاصيل الكاملة', 'en' => 'Quick View'],
        'wa_inquiry_msg' => ['ar' => 'مرحباً أصلي تك ستور، أرغب في الاستفسار عن توفر وتفاصيل الجهاز: ', 'en' => 'Hello ASLY Tech Store, I would like to inquire about availability & details for: '],
        'empty_catalog' => ['ar' => 'لا توجد أجهزة مطابقة لبحثك حالياً — تواصل معنا مباشرة عبر واتساب لتوفير طلبك.', 'en' => 'No matching items right now — message us on WhatsApp and we will source it for you.'],

        // 3 Pillars ("WHY ASLY" — getbouga-inspired editorial columns)
        'pillars_eyebrow' => ['ar' => 'لماذا يختار العملاء أصلي؟', 'en' => 'THE ASLY STANDARD'],
        'pillar_1_tag' => ['ar' => 'الفكرة • THE IDEA', 'en' => '01 • THE IDEA'],
        'pillar_1_title' => ['ar' => 'أجهزة منتقاة بعين مهندس، لا مجرد تاجر.', 'en' => 'Selected By Engineers, Not Just Retailers.'],
        'pillar_1_desc' => [
            'ar' => 'كل لابتوب أو هاتف في أصلي يمر باختبارات ضغط دقيقة للشاشة، البطارية، الهارد NVMe، ودوائر التبريد قبل أن يُعرض أمامك.',
            'en' => 'Every laptop and smartphone at ASLY undergoes strict thermal, battery, NVMe, and display diagnostics before earning a spot in our showroom.'
        ],
        'pillar_2_tag' => ['ar' => 'الأصالة • AUTHENTICITY', 'en' => '02 • PURE AUTHENTICITY'],
        'pillar_2_title' => ['ar' => 'ضمان الفحص والتجربة وقطع أصلية ١٠٠٪.', 'en' => 'Certified Warranty & Genuine Components.'],
        'pillar_2_desc' => [
            'ar' => 'سواء اخترت جهازاً جديداً متبرشماً أو كسر زيرو بحالة المصنع، تحصل على فاتورة رسمية موثقة وضمان حقيقي يضمن حقك.',
            'en' => 'Whether factory-sealed or pristine open-box, every purchase comes with a documented digital invoice and our trusted testing warranty.'
        ],
        'pillar_3_tag' => ['ar' => 'الشفافية • LIVE QR LAB', 'en' => '03 • TRANSPARENT REPAIR LAB'],
        'pillar_3_title' => ['ar' => 'تتبع جهازك لحظة بلحظة مع حماية كاملة لخصوصيتك.', 'en' => 'Step-By-Step QR Tracking & Privacy First.'],
        'pillar_3_desc' => [
            'ar' => 'امسح كود الـ QR المطبوع على إيصالك لمتابعة مراحل الفحص ومراسلة المهندس مباشرة، مع مسح تلقائي لبيانات التذكرة فور التسليم.',
            'en' => 'Scan your receipt QR code to view live repair stages, message your technician, receive email alerts, and enjoy automatic data wipe upon handover.'
        ],

        // Stats Strip (getbouga 3-stat rhythm)
        'stat_1_num' => ['ar' => '١٠٠٪', 'en' => '100%'],
        'stat_1_lbl' => ['ar' => 'فحص هندسي شامل قبل التسليم', 'en' => 'Multi-Point Hardware Diagnostics'],
        'stat_2_num' => ['ar' => '٧ مراحل', 'en' => '7 Stages'],
        'stat_2_lbl' => ['ar' => 'تتبع تفاعلي للصيانة عبر الـ QR والإيميل', 'en' => 'Interactive QR & Email Repair Tracking'],
        'stat_3_num' => ['ar' => '٠٪ قلق', 'en' => 'Zero Worry'],
        'stat_3_lbl' => ['ar' => 'خصوصية تامة ومسح فوري للبيانات عند الاستلام', 'en' => 'Automatic Personal Data Wipe Upon Delivery'],

        // Interactive Repair Lab Section
        'lab_eyebrow' => ['ar' => 'معمل أصلي للصيانة الدقيقة • LIVE QR TRACKING', 'en' => 'ASLY PRECISION LAB • LIVE QR TRACKING'],
        'lab_title' => ['ar' => 'تابع حالة جهازك في الصيانة مباشرة', 'en' => 'Track Your Device Repair In Real Time'],
        'lab_desc' => [
            'ar' => 'أدخل كود تذكرة الصيانة الموجود على الإيصال (أو امسح رمز الـ QR بكاميرا هاتفك) للاطلاع على التقرير الفني، المرحلة الحالية، وإضافة ملاحظاتك لمهندس الصيانة.',
            'en' => 'Enter your receipt ticket code (or scan your printed QR sticker) to view live technician reports, current repair stage, and leave direct notes for our engineers.'
        ],
        'lab_input_ph' => ['ar' => 'أدخل كود الصيانة — مثال: MNT-2026-101', 'en' => 'Enter ticket code — e.g. MNT-2026-101'],
        'lab_btn_track' => ['ar' => 'اعرض حالة الجهاز الآن', 'en' => 'Track Status Now'],
        'lab_try_demo' => ['ar' => 'جرّب تذكرة حية للتجربة: MNT-2026-101', 'en' => 'Try live demo ticket: MNT-2026-101'],

        // In Real Life Lookbook Gallery ("In Use — In Our Real Life")
        'gallery_eyebrow' => ['ar' => 'في الاستخدام اليومي • IN REAL LIFE', 'en' => 'IN USE • IN OUR REAL LIFE'],
        'gallery_title' => ['ar' => 'الأداء والأناقة في كل مكان تذهب إليه', 'en' => 'Designed To Move With Your Day'],
        'gallery_sub' => [
            'ar' => 'لقطات حية من بيئة العمل، الدراسة، السفر، وداخل معمل الفحص الهندسي بمركز أصلي.',
            'en' => 'From sunlit study desks and travel courtyards to our bright diagnostic lab — tech that fits effortlessly.'
        ],
        'gal_cap_1' => ['ar' => 'مساحة عمل هادئة • لابتوبات فائقة النحافة', 'en' => 'Bright Studio Workstations'],
        'gal_cap_2' => ['ar' => 'رفيقك اليومي • هواتف رائدة وساعات ذكية', 'en' => 'Everyday Flagship Mobility'],
        'gal_cap_3' => ['ar' => 'معمل أصلي • دقة تشخيص بأحدث الأجهزة', 'en' => 'Precision Hardware Diagnostics'],
        'gal_cap_4' => ['ar' => 'تجربة صوتية غامرة وعزل ضوضاء نقي', 'en' => 'Pure Studio Acoustic Comfort'],
        'gal_cap_5' => ['ar' => 'قوة الأداء الهندسي في كل تفصيلة', 'en' => 'Creator-Grade Laptop Power'],
        'gal_cap_6' => ['ar' => 'تصميم تيتانيوم أنيق يدوم طويلاً', 'en' => 'Natural Titanium Craftsmanship'],

        // FAQ Accordion (getbouga-inspired)
        'faq_eyebrow' => ['ar' => 'معلومات تهمك • FAQ', 'en' => 'GOOD TO KNOW • FAQ'],
        'faq_title' => ['ar' => 'الأسئلة الأكثر شيوعاً', 'en' => 'Frequently Asked Questions'],
        'faq_q1' => ['ar' => 'لماذا لا تظهر أسعار ثابتة على الموقع؟ وكيف أعرف السعر الحالي؟', 'en' => 'Why aren’t fixed prices listed publicly, and how do I get a quote?'],
        'faq_a1' => [
            'ar' => 'نظراً لتغير عروض الأجهزة والمواصفات يومياً (سواء الأجهزة المتبرشمة أو كسر الزيرو)، يتيح لك زر الواتساب أسفل كل جهاز الحصول على أفضل سعر لحظي مع فيديو وفحص حي للجهاز قبل الشراء.',
            'en' => 'Because configurations and daily offers update rapidly across sealed and open-box stock, tapping “Inquire on WhatsApp” on any item connects you directly with our team for our best live quote and inspection photos.'
        ],
        'faq_q2' => ['ar' => 'ما هو ضمان الأجهزة المستعملة أو كسر الزيرو في أصلي تك ستور؟', 'en' => 'What warranty comes with Open-Box and Pre-Owned laptops or phones?'],
        'faq_a2' => [
            'ar' => 'جميع الأجهزة تمر بفحص هندسي شامل قبل العرض، وتصدر بفاتورة رسمية مختومة تشمل فترة ضمان الفحص والتجربة المحددة لكل جهاز لراحة بالك التامة.',
            'en' => 'Every open-box and pre-owned device passes our multi-point engineering check and includes a documented warranty period printed right on your official digital & paper invoice.'
        ],
        'faq_q3' => ['ar' => 'كيف يعمل نظام تتبع الصيانة بالـ QR؟ وماذا يحدث لبياناتي بعد الاستلام؟', 'en' => 'How does QR repair tracking work, and how is my privacy protected?'],
        'faq_a3' => [
            'ar' => 'عند تسليم جهازك للصيانة تحصل على إيصال مطبوع عليه كود QR خاص بك؛ بمسحه ترى المرحلة الحالية وملاحظات المهندس ويمكنك الرد عليه، كما يصلك إيميل بكل تحديث. وفور استلامك للجهاز يتم حذف سجل بيانات التذكرة الشخصية تلقائياً.',
            'en' => 'Upon drop-off, you receive a unique QR receipt. Scan it anytime to see live repair stages, read engineer notes, reply directly, and get automatic email updates. Once delivered, personal device records are automatically wiped.'
        ],
        'faq_q4' => ['ar' => 'هل تتوفر فواتير رسمية وإمكانية الدفع عبر المحافظ وإنستا باي؟', 'en' => 'Do you provide official invoices and support InstaPay / mobile wallets?'],
        'faq_a4' => [
            'ar' => 'نعم، كل عملية شراء تصدر لها فاتورة رقمية ومطبوعة فورية يمكن إرسالها لك كصورة عبر واتساب، مع دعم الدفع النقدي، فودافون كاش، وإنستا باي (InstaPay).',
            'en' => 'Yes! Every purchase generates an instant branded invoice (printable and downloadable as a PNG image via WhatsApp), with Cash, Vodafone Cash, InstaPay, and Card options.'
        ],

        // Contact & Footer
        'contact_eyebrow' => ['ar' => 'تفضل بزيارتنا أو تواصل معنا', 'en' => 'GET IN TOUCH'],
        'contact_title' => ['ar' => 'قنوات التواصل الرسمية لمركز أصلي', 'en' => 'Direct Concierge & Showroom Channels'],
        'contact_hours' => ['ar' => 'مواعيد العمل', 'en' => 'Working Hours'],
        'contact_location' => ['ar' => 'العنوان', 'en' => 'Showroom Location'],
        'contact_map_btn' => ['ar' => 'افتح الموقع على خرائط جوجل', 'en' => 'Open in Google Maps'],
        'footer_rights' => ['ar' => 'جميع الحقوق محفوظة', 'en' => 'All rights reserved'],
        'search_drawer_title' => ['ar' => 'ابحث في معرض أصلي', 'en' => 'Search ASLY Showroom'],
        'close' => ['ar' => 'إغلاق', 'en' => 'Close'],

        // Track Page
        'track_page_title' => ['ar' => 'تتبع حالة الصيانة المباشر', 'en' => 'Live Repair Status Portal'],
        'track_back_home' => ['ar' => 'العودة للمعرض الرئيسي', 'en' => 'Back to Showroom'],
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
        'track_add_note_btn' => ['ar' => '+ أضف ملاحظة على هذه المرحلة', 'en' => '+ Add Note on Stage'],
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
        'login_back_store' => ['ar' => 'العودة لمعرض العملاء الخارجي', 'en' => 'Return to Public Storefront'],
    ];

    $lang = currentLang();
    return $dict[$key][$lang] ?? ($dict[$key]['ar'] ?? $key);
}
