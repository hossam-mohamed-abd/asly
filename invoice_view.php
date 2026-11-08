<?php
/**
 * ASLY | أصلي TECH STORE
 * Branded Digital Customer Invoice (Downloadable as PNG Image + Printable)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
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
$address = $settings['store_address'] ?: 'سوهاج - جمهورية مصر العربية';
$footerNote = $settings['invoice_footer_note'] ?: 'جميع الأجهزة تخضع لضمان الفحص والتجربة المعتمد من مركز أصلي ASLY TECH STORE.';

$paymentLabels = [
    'cash' => 'نقدي (كاش)',
    'vodafone_cash' => 'فودافون كاش / محفظة إلكترونية',
    'instapay' => 'إنستا باي (InstaPay)',
    'card' => 'بطاقة بنكية (فيزا)',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $invoice ? 'فاتورة ' . e((string)$invoice['invoice_number']) : 'عرض الفاتورة' ?> | <?= e($storeNameAr) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
  <style>
    .invoice-page-wrap { max-width: 820px; margin: 34px auto 60px; padding: 0 16px; }
    .invoice-toolbar { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 20px; }
    .invoice-paper {
      background: #ffffff;
      border-radius: 18px;
      overflow: hidden;
      border: 2px solid #043538;
      box-shadow: 0 20px 50px rgba(4,53,56,0.12);
    }
    .inv-head {
      background: linear-gradient(135deg, #043538 0%, #085a5e 100%);
      color: #ffffff;
      padding: 26px 32px;
      border-bottom: 4px solid #d49b27;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .inv-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.92rem; }
    .inv-table th {
      background: #043538;
      color: #f2c75c;
      padding: 12px 14px;
      text-align: right;
      font-weight: 800;
    }
    .inv-table td {
      padding: 12px 14px;
      border-bottom: 1px solid #e3eded;
      color: #0d2628;
    }
    .inv-table tr:nth-child(even) td { background: #f8fbfb; }
    @media print {
      body { background: #fff; }
      .invoice-toolbar, .site-header { display: none !important; }
      .invoice-page-wrap { margin: 0; max-width: 100%; padding: 0; }
      .invoice-paper { box-shadow: none; border-radius: 0; }
    }
  </style>
</head>
<body>

  <div class="invoice-page-wrap">
    <?php if (!$invoice): ?>
      <div class="track-card" style="padding:40px;text-align:center;">
        <h2 style="margin:0 0 10px;color:var(--asly-teal-900);">الفاتورة غير موجودة</h2>
        <p style="color:var(--asly-muted);">يرجى التأكد من رابط الفاتورة المرسل إليك من مركز أصلي تك ستور.</p>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-gold">زيارة الموقع الرئيسي</a>
      </div>
    <?php else: ?>
      <div class="invoice-toolbar">
        <button type="button" id="btnDownloadPng" class="btn btn-gold">📥 تحميل الفاتورة كصورة (PNG)</button>
        <button type="button" onclick="window.print()" class="btn btn-teal">🖨️ طباعة الفاتورة</button>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-outline-gold" style="background:#043538;">الموقع الرئيسي</a>
      </div>

      <div class="invoice-paper" id="invoiceCaptureArea">
        <div class="inv-head">
          <div>
            <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي" style="height:58px;">
            <div style="font-size:.82rem;color:#d8ecec;margin-top:6px;"><?= e($address) ?></div>
          </div>
          <div style="text-align:left;">
            <div style="font-size:.8rem;color:#f2c75c;font-weight:800;letter-spacing:1px;">SALES INVOICE • فاتورة مبيعات</div>
            <div style="font-size:1.35rem;font-weight:900;direction:ltr;"><?= e((string)$invoice['invoice_number']) ?></div>
            <div style="font-size:.84rem;color:#d8ecec;direction:ltr;"><?= e((string)$invoice['created_at']) ?></div>
          </div>
        </div>

        <div style="padding:26px 32px;">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;background:#f3f9f9;padding:16px 20px;border-radius:12px;border:1px solid #dceaea;font-size:.9rem;">
            <div><strong style="color:#043538;">العميل:</strong> <?= e((string)$invoice['customer_name']) ?></div>
            <div><strong style="color:#043538;">رقم الهاتف:</strong> <span style="direction:ltr;display:inline-block;"><?= e($invoice['customer_phone'] ?: '—') ?></span></div>
            <div><strong style="color:#043538;">طريقة الدفع:</strong> <?= e($paymentLabels[$invoice['payment_method']] ?? $invoice['payment_method']) ?></div>
            <div><strong style="color:#043538;">مسؤول المبيعات:</strong> <?= e((string)$invoice['sold_by_name']) ?></div>
          </div>

          <table class="inv-table">
            <thead>
              <tr>
                <th>#</th>
                <th>المنتج / البيان</th>
                <th>السيريال / الضمان</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $idx => $it): ?>
                <tr>
                  <td><?= $idx + 1 ?></td>
                  <td>
                    <strong><?= e((string)$it['product_name']) ?></strong>
                    <?php if (!empty($it['product_barcode'])): ?>
                      <div style="font-size:.76rem;color:#5c7678;direction:ltr;text-align:right;"><?= e((string)$it['product_barcode']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.82rem;">
                    <?php if (!empty($it['serial_imei'])): ?>
                      <div>S/N: <?= e((string)$it['serial_imei']) ?></div>
                    <?php endif; ?>
                    <div style="color:#065f46;font-weight:700;"><?= e($it['warranty_text'] ?: 'ضمان المركز') ?></div>
                  </td>
                  <td><?= (int)$it['quantity'] ?></td>
                  <td><?= formatMoney((float)$it['unit_sold_price']) ?></td>
                  <td style="font-weight:800;"><?= formatMoney((float)$it['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:20px;padding-top:16px;border-top:2px solid #043538;">
            <div style="max-width:440px;font-size:.83rem;color:#486365;">
              <?php if (!empty($invoice['notes'])): ?>
                <div style="margin-bottom:6px;"><strong>ملاحظات:</strong> <?= e((string)$invoice['notes']) ?></div>
              <?php endif; ?>
              <div>🛡️ <?= e($footerNote) ?></div>
            </div>
            <div style="background:linear-gradient(135deg,#043538,#085a5e);color:#fff;padding:14px 26px;border-radius:12px;border:1.5px solid #d49b27;text-align:center;">
              <div style="font-size:.8rem;color:#f2c75c;">الإجمالي النهائي المدفوع</div>
              <div style="font-size:1.45rem;font-weight:900;"><?= formatMoney((float)$invoice['total_amount']) ?></div>
            </div>
          </div>

          <div style="margin-top:22px;padding-top:14px;border-top:1px dashed #cbdcdc;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;font-size:.82rem;color:#526d70;">
            <span><strong><?= e($storeNameAr) ?> (<?= e($storeNameEn) ?>)</strong></span>
            <span style="direction:ltr;font-weight:800;color:#043538;">📞 <?= e($primaryPhone) ?><?= $secondaryPhone ? ' | ' . e($secondaryPhone) : '' ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="<?= e(appUrl('assets/vendor/html2canvas.min.js')) ?>"></script>
  <script>
    const dlBtn = document.getElementById('btnDownloadPng');
    if (dlBtn) {
      dlBtn.addEventListener('click', async () => {
        const el = document.getElementById('invoiceCaptureArea');
        dlBtn.disabled = true;
        dlBtn.textContent = 'جاري تجهيز الصورة...';
        try {
          const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
          const link = document.createElement('a');
          link.download = <?= json_encode(($invoice['invoice_number'] ?? 'ASLY-Invoice') . '.png') ?>;
          link.href = canvas.toDataURL('image/png');
          link.click();
        } finally {
          dlBtn.disabled = false;
          dlBtn.textContent = '📥 تحميل الفاتورة كصورة (PNG)';
        }
      });
    }
  </script>
</body>
</html>
