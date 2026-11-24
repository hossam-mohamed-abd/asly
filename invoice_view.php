<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$en = isEn();
$otherLang = $en ? 'ar' : 'en';
$token = trim((string)($_GET['token'] ?? ''));

$invoice = null;
$items = [];

if ($token !== '') {
    $stmt = $pdo->prepare("
        SELECT id, invoice_number, public_token, customer_name, customer_phone,
               total_amount, payment_method, notes, sold_by_name, sale_date, created_at
        FROM invoices
        WHERE public_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $invoice = $stmt->fetch();

    if ($invoice) {
        $iStmt = $pdo->prepare("
            SELECT product_name, product_barcode, serial_imei, warranty_text,
                   quantity, unit_sold_price, subtotal
            FROM invoice_items
            WHERE invoice_id = ?
            ORDER BY id ASC
        ");
        $iStmt->execute([(int)$invoice['id']]);
        $items = $iStmt->fetchAll();
    }
}

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$primaryPhone = $settings['primary_phone'] ?: '01015981677';
$secondaryPhone = $settings['secondary_phone'] ?: '01154639718';
$address = $en
    ? ($settings['store_address_en'] ?: 'Al-Souq St., In Front of Tersa Church, Tersa, Sinnuris, Faiyum')
    : ($settings['store_address'] ?: 'الفيوم - سنورس - ترسا - شارع السوق أمام كنيسة ترسا');
$footerNote = $en
    ? ($settings['invoice_footer_note_en'] ?: 'All devices include ASLY TECH STORE certified hardware inspection & warranty.')
    : ($settings['invoice_footer_note'] ?: 'جميع الأجهزة تخضع لضمان الفحص والتجربة المعتمد من مركز أصلي ASLY TECH STORE.');

$paymentLabels = $en
    ? [
        'cash' => 'Cash',
        'vodafone_cash' => 'Vodafone Cash / Wallet',
        'instapay' => 'InstaPay',
        'card' => 'Bank Card',
    ]
    : [
        'cash' => 'نقدي (كاش)',
        'vodafone_cash' => 'فودافون كاش / محفظة إلكترونية',
        'instapay' => 'إنستا باي (InstaPay)',
        'card' => 'بطاقة بنكية (فيزا)',
    ];
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <title><?= $invoice ? e((string)$invoice['invoice_number']) : e(t('inv_not_found')) ?> | <?= e($storeNameEn) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
  <style>
    body { background: var(--bg-ivory); }
    .invoice-page-wrap { max-width: 840px; margin: 36px auto 64px; padding: 0 16px; }
    .invoice-toolbar { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 22px; }
    .invoice-paper {
      background: #ffffff;
      border-radius: 32px 14px 32px 14px;
      overflow: hidden;
      border: 1.5px solid var(--teal-border);
      box-shadow: var(--shadow-md);
    }
    .inv-head {
      background: linear-gradient(135deg, var(--bg-aqua-soft) 0%, #ffffff 55%, var(--bg-gold-soft) 100%);
      color: var(--ink-1);
      padding: 26px 32px;
      border-bottom: 3px solid var(--gold);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .inv-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.92rem; }
    .inv-table th {
      background: var(--bg-aqua-soft);
      color: var(--teal-deep);
      padding: 12px 14px;
      text-align: start;
      font-weight: 800;
      border-bottom: 2px solid var(--teal-border);
    }
    .inv-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      color: var(--ink-1);
    }
    .inv-table tr:nth-child(even) td { background: var(--bg-ivory); }
    @media print {
      body { background: #fff; }
      .invoice-toolbar { display: none !important; }
      .invoice-page-wrap { margin: 0; max-width: 100%; padding: 0; }
      .invoice-paper { box-shadow: none; border-radius: 0; }
    }
  </style>
</head>
<body>

  <div class="invoice-page-wrap">
    <?php if (!$invoice): ?>
      <div class="invoice-paper" style="padding: 44px; text-align: center;">
        <div style="font-size: 2.2rem; color: var(--gold-deep); margin-bottom: 12px;"><i class="fa-solid fa-file-circle-xmark"></i></div>
        <h2 style="margin: 0 0 10px; color: var(--ink-1);"><?= e(t('inv_not_found')) ?></h2>
        <p style="color: var(--ink-3); margin: 0 0 20px;"><?= e(t('inv_not_found_sub')) ?></p>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-teal"><i class="fa-solid fa-house"></i> <span><?= e(t('track_back_home')) ?></span></a>
      </div>
    <?php else: ?>
      <div class="invoice-toolbar">
        <button type="button" id="btnDownloadPng" class="btn btn-teal">
          <i class="fa-solid fa-download"></i>
          <span><?= e(t('inv_download_png')) ?></span>
        </button>
        <button type="button" onclick="window.print()" class="btn btn-gold">
          <i class="fa-solid fa-print"></i>
          <span><?= e(t('inv_print')) ?></span>
        </button>
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill">
          <i class="fa-solid fa-globe"></i>
          <span><?= e(t('lang_switch_label')) ?></span>
        </a>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-outline">
          <i class="fa-solid fa-house"></i>
          <span><?= e(t('track_back_home')) ?></span>
        </a>
      </div>

      <div class="invoice-paper" id="invoiceCaptureArea">
        <div class="inv-head">
          <div>
            <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store" style="height: 60px;">
            <div style="font-size: 0.84rem; color: var(--ink-3); margin-top: 6px;"><?= e($address) ?></div>
          </div>
          <div style="text-align: end;">
            <div style="font-size: 0.78rem; color: var(--gold-deep); font-weight: 800; letter-spacing: 1px;"><?= e(t('inv_sales_title')) ?></div>
            <div style="font-size: 1.35rem; font-weight: 900; color: var(--teal-deep); direction: ltr;"><?= e((string)$invoice['invoice_number']) ?></div>
            <div style="font-size: 0.84rem; color: var(--ink-3); direction: ltr;"><?= e((string)$invoice['created_at']) ?></div>
          </div>
        </div>

        <div style="padding: 26px 32px;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(185px, 1fr)); gap: 14px; background: var(--bg-ivory); padding: 16px 20px; border-radius: 16px; border: 1px solid var(--line-warm); font-size: 0.9rem;">
            <div><strong style="color: var(--ink-1);"><?= e(t('inv_customer')) ?>:</strong> <?= e((string)$invoice['customer_name']) ?></div>
            <div><strong style="color: var(--ink-1);"><?= e(t('inv_phone')) ?>:</strong> <span style="direction: ltr; display: inline-block;"><?= e($invoice['customer_phone'] ?: '—') ?></span></div>
            <div><strong style="color: var(--ink-1);"><?= e(t('inv_payment')) ?>:</strong> <?= e($paymentLabels[$invoice['payment_method']] ?? $invoice['payment_method']) ?></div>
            <div><strong style="color: var(--ink-1);"><?= e(t('inv_cashier')) ?>:</strong> <?= e((string)$invoice['sold_by_name']) ?></div>
          </div>

          <table class="inv-table">
            <thead>
              <tr>
                <th>#</th>
                <th><?= e(t('inv_col_item')) ?></th>
                <th><?= e(t('inv_col_warranty')) ?></th>
                <th><?= e(t('inv_col_qty')) ?></th>
                <th><?= e(t('inv_col_unit')) ?></th>
                <th><?= e(t('inv_col_total')) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $idx => $it): ?>
                <tr>
                  <td><?= $idx + 1 ?></td>
                  <td>
                    <strong><?= e((string)$it['product_name']) ?></strong>
                    <?php if (!empty($it['product_barcode'])): ?>
                      <div style="font-size: 0.76rem; color: var(--ink-3); direction: ltr; display: inline-block;"><?= e((string)$it['product_barcode']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="font-size: 0.82rem;">
                    <?php if (!empty($it['serial_imei'])): ?>
                      <div>S/N: <?= e((string)$it['serial_imei']) ?></div>
                    <?php endif; ?>
                    <div style="color: var(--teal-deep); font-weight: 700;"><?= e($it['warranty_text'] ?: '') ?></div>
                  </td>
                  <td><?= (int)$it['quantity'] ?></td>
                  <td><?= formatMoney((float)$it['unit_sold_price']) ?></td>
                  <td style="font-weight: 800;"><?= formatMoney((float)$it['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-top: 20px; padding-top: 16px; border-top: 2px solid var(--teal-border);">
            <div style="max-width: 440px; font-size: 0.84rem; color: var(--ink-3);">
              <?php if (!empty($invoice['notes'])): ?>
                <div style="margin-bottom: 6px; color: var(--ink-1);"><strong><?= e(t('inv_notes')) ?>:</strong> <?= e((string)$invoice['notes']) ?></div>
              <?php endif; ?>
              <div><i class="fa-solid fa-shield-halved" style="color: var(--teal-deep); margin-inline-end: 6px;"></i><?= e($footerNote) ?></div>
            </div>
            <div style="background: linear-gradient(135deg, var(--bg-aqua-soft), var(--bg-gold-soft)); padding: 14px 26px; border-radius: 20px 8px 20px 8px; border: 1.5px solid var(--gold); text-align: center;">
              <div style="font-size: 0.8rem; color: var(--gold-deep); font-weight: 800;"><?= e(t('inv_grand_total')) ?></div>
              <div style="font-size: 1.5rem; font-weight: 900; color: var(--teal-deep);"><?= formatMoney((float)$invoice['total_amount']) ?></div>
            </div>
          </div>

          <div style="margin-top: 22px; padding-top: 14px; border-top: 1px dashed var(--teal-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 0.82rem; color: var(--ink-3);">
            <span><strong style="color: var(--ink-1);"><?= e($storeNameAr) ?> | <?= e($storeNameEn) ?></strong></span>
            <span style="direction: ltr; font-weight: 800; color: var(--teal-deep);"><i class="fa-solid fa-phone" style="margin-inline-end: 5px;"></i><?= e($primaryPhone) ?><?= $secondaryPhone ? ' | ' . e($secondaryPhone) : '' ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="<?= e(appUrl('assets/vendor/html2canvas.min.js')) ?>"></script>
  <script>
    const dlBtn = document.getElementById('btnDownloadPng');
    const dlLabel = <?= json_encode(t('inv_download_png'), JSON_UNESCAPED_UNICODE) ?>;
    if (dlBtn) {
      dlBtn.addEventListener('click', async () => {
        const el = document.getElementById('invoiceCaptureArea');
        const span = dlBtn.querySelector('span');
        dlBtn.disabled = true;
        if (span) span.textContent = '...';
        try {
          const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
          const link = document.createElement('a');
          link.download = <?= json_encode(($invoice['invoice_number'] ?? 'ASLY-Invoice') . '.png') ?>;
          link.href = canvas.toDataURL('image/png');
          link.click();
        } finally {
          dlBtn.disabled = false;
          if (span) span.textContent = dlLabel;
        }
      });
    }
  </script>
</body>
</html>
