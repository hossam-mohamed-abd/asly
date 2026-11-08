<?php
/**
 * ASLY | أصلي TECH STORE
 * Interactive Customer QR Maintenance Tracking & Stage Commenting Page
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$stages = getMaintenanceStages();

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$primaryPhone = $settings['primary_phone'] ?: '01015981677';
$primaryWa = normalizeWhatsappNumber($settings['primary_whatsapp'] ?: $primaryPhone);

$code = strtoupper(trim((string)($_GET['code'] ?? '')));
$qrToken = trim((string)($_GET['t'] ?? ''));

$ticket = null;
$notesByStage = [];
$deliveredRef = null;
$lookupError = '';

if ($code !== '') {
    $stmt = $pdo->prepare("SELECT * FROM maintenance_tickets WHERE UPPER(ticket_code) = ? LIMIT 1");
    $stmt->execute([$code]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        $nStmt = $pdo->prepare("SELECT * FROM maintenance_notes WHERE ticket_id = ? ORDER BY id ASC");
        $nStmt->execute([(int)$ticket['id']]);
        foreach ($nStmt->fetchAll() as $n) {
            $notesByStage[$n['stage_key']][] = $n;
        }
    } else {
        $rStmt = $pdo->prepare("SELECT ticket_code_ref, revenue_date FROM maintenance_revenues WHERE UPPER(ticket_code_ref) = ? LIMIT 1");
        $rStmt->execute([$code]);
        $deliveredRef = $rStmt->fetch();
        if (!$deliveredRef) {
            $lookupError = 'لم يتم العثور على تذكرة صيانة بهذا الكود. يرجى التأكد من الرقم المكتوب على إيصال الاستلام.';
        }
    }
}

$hasValidQrToken = $ticket && $qrToken !== '' && hash_equals((string)$ticket['qr_token'], $qrToken);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تتبع حالة الصيانة | <?= e($storeNameAr) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body>

  <header class="site-header">
    <div class="container nav-inner">
      <a href="<?= e(appUrl('index.php')) ?>" class="brand-logo">
        <img src="<?= e(appUrl('assets/brand/logo-dark.svg')) ?>" alt="ASLY | أصلي">
      </a>
      <div class="nav-actions">
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-outline-gold">العودة للرئيسية</a>
        <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-gold">واتساب الصيانة</a>
      </div>
    </div>
  </header>

  <main class="track-shell">
    <div class="container" style="max-width:940px;">

      <!-- Search Box -->
      <div class="track-card" style="padding:26px 30px;">
        <h1 style="margin:0 0 8px;font-size:1.55rem;color:var(--asly-teal-900);">بوابة التتبع المباشر لأجهزة الصيانة</h1>
        <p style="margin:0 0 18px;color:var(--asly-muted);font-size:.94rem;">أدخل رقم تذكرة الصيانة أو امسح رمز الـ QR المطبوع على الإيصال لمتابعة مراحل الفحص والإصلاح والتواصل مع المهندس المختص.</p>
        <form method="get" action="<?= e(appUrl('track.php')) ?>" class="track-mini-form">
          <input type="text" name="code" value="<?= e($code) ?>" placeholder="أدخل كود الصيانة (مثال: MNT-2026-101)" required style="border:1.5px solid var(--asly-border);">
          <button type="submit" class="btn btn-teal">بحث وعرض الحالة</button>
        </form>
      </div>

      <?php if ($lookupError !== ''): ?>
        <div class="track-card" style="padding:28px;border-color:#fca5a5;background:#fef2f2;color:#991b1b;text-align:center;font-weight:700;">
          <?= e($lookupError) ?>
        </div>
      <?php endif; ?>

      <?php if ($deliveredRef): ?>
        <div class="track-card" style="padding:38px 30px;text-align:center;">
          <div style="width:68px;height:68px;border-radius:50%;background:#ecfdf5;color:#059669;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:14px;">✓</div>
          <h2 style="margin:0 0 10px;color:var(--asly-teal-900);">تم تسليم الجهاز للعميل بنجاح (<?= e((string)$deliveredRef['ticket_code_ref']) ?>)</h2>
          <p style="margin:0 auto 20px;max-width:560px;color:var(--asly-muted);">
            تم تسليم هذا الجهاز بتاريخ <strong><?= e((string)$deliveredRef['revenue_date']) ?></strong>، وكما نلتزم في <strong><?= e($storeNameAr) ?></strong>، تم حذف جميع البيانات التفصيلية والملاحظات الخاصة بالجهاز فور التسليم حفاظاً على خصوصية العميل.
          </p>
          <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-gold">العودة لمعرض أصلي تك ستور</a>
        </div>
      <?php endif; ?>

      <?php if ($ticket):
        $currentOrder = (int)($stages[$ticket['current_stage']]['order'] ?? 1);
      ?>
        <div class="track-card">
          <div class="track-card-header">
            <div>
              <div style="font-size:.82rem;color:var(--asly-gold-400);font-weight:700;">تذكرة صيانة معتمدة • <?= e((string)$ticket['ticket_code']) ?></div>
              <h2 style="margin:4px 0 0;font-size:1.45rem;"><?= e((string)$ticket['device_model']) ?></h2>
            </div>
            <div style="text-align:left;">
              <span style="display:inline-block;padding:7px 16px;border-radius:999px;background:var(--asly-gold-500);color:#032224;font-weight:900;font-size:.9rem;">
                <?= e($stages[$ticket['current_stage']]['title'] ?? $ticket['current_stage']) ?>
              </span>
            </div>
          </div>

          <!-- Device Summary Grid -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;padding:22px 30px;background:var(--asly-teal-50);border-bottom:1px solid var(--asly-border);font-size:.9rem;">
            <div><strong style="color:var(--asly-teal-900);">اسم العميل:</strong><br><?= e((string)$ticket['customer_name']) ?></div>
            <div><strong style="color:var(--asly-teal-900);">المواصفات المسجلة:</strong><br><?= e($ticket['device_specs'] ?: '—') ?></div>
            <div><strong style="color:var(--asly-teal-900);">وصف العطل:</strong><br><?= e((string)$ticket['problem_description']) ?></div>
            <div>
              <strong style="color:var(--asly-teal-900);">التكلفة المقدرة / العربون:</strong><br>
              <?= formatMoney((float)$ticket['estimated_cost']) ?> (مدفوع: <?= formatMoney((float)$ticket['deposit_paid']) ?>)
            </div>
          </div>

          <!-- Stages Timeline & Mutual Notes -->
          <div class="timeline-steps">
            <h3 style="margin:0 0 4px;color:var(--asly-teal-900);">مراحل الفحص والصيانة والتعليقات المتبادلة</h3>
            <p style="margin:0 0 10px;font-size:.86rem;color:var(--asly-muted);">يمكنك قراءة ملاحظات المهندس على كل مرحلة وإضافة تعليقك أو ردك مباشرة أسفل أي مرحلة.</p>

            <?php foreach ($stages as $stageKey => $st):
              if ($stageKey === 'delivered') continue; // Delivered wipes ticket
              $order = (int)$st['order'];
              $stateClass = $order < $currentOrder ? 'completed' : ($order === $currentOrder ? 'current' : '');
              $stageNotes = $notesByStage[$stageKey] ?? [];
            ?>
              <div class="stage-step <?= $stateClass ?>">
                <div class="stage-step-head">
                  <div style="display:flex;align-items:center;gap:12px;">
                    <span class="stage-num"><?= $order < $currentOrder ? '✓' : $order ?></span>
                    <div>
                      <strong style="font-size:1.03rem;color:var(--asly-teal-900);"><?= e($st['title']) ?></strong>
                      <div style="font-size:.83rem;color:var(--asly-muted);"><?= e($st['desc']) ?></div>
                    </div>
                  </div>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <?php if ($order === $currentOrder): ?>
                      <span style="padding:4px 12px;border-radius:999px;background:var(--asly-gold-50);color:var(--asly-gold-600);border:1px solid rgba(212,155,39,.4);font-size:.78rem;font-weight:800;">المرحلة الحالية</span>
                    <?php elseif ($order < $currentOrder): ?>
                      <span style="font-size:.78rem;color:#059669;font-weight:800;">مكتملة</span>
                    <?php endif; ?>
                    <button
                      type="button"
                      class="btn btn-teal js-toggle-reply"
                      data-stage="<?= e($stageKey) ?>"
                      style="padding:6px 12px;font-size:.78rem;border-radius:8px;"
                    >
                      + أضف ملاحظة على هذه المرحلة
                    </button>
                  </div>
                </div>

                <!-- Notes on this stage -->
                <div class="stage-notes-list" id="notes-<?= e($stageKey) ?>">
                  <?php foreach ($stageNotes as $note): ?>
                    <div class="note-bubble <?= e((string)$note['sender_type']) ?>">
                      <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:4px;opacity:.85;">
                        <strong><?= $note['sender_type'] === 'admin' ? '👨‍🔧 إدارة الصيانة (' . e((string)$note['sender_name']) . ')' : '👤 العميل (' . e((string)$note['sender_name']) . ')' ?></strong>
                        <span style="direction:ltr;"><?= e((string)$note['created_at']) ?></span>
                      </div>
                      <div><?= nl2br(e((string)$note['note_text'])) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Customer Reply Form for this Stage -->
                <form class="js-customer-note-form" data-stage="<?= e($stageKey) ?>" style="display:<?= $order === $currentOrder ? 'block' : 'none' ?>;margin-top:14px;padding-top:12px;border-top:1px dashed var(--asly-border);">
                  <?php if (!$hasValidQrToken): ?>
                    <div style="margin-bottom:8px;">
                      <input
                        type="text"
                        name="phone_verify"
                        placeholder="لتأكيد هويتك: أدخل آخر 4 أرقام من رقم هاتفك المسجل"
                        required
                        style="width:100%;padding:9px 12px;border-radius:9px;border:1px solid var(--asly-border);font-family:inherit;font-size:.85rem;"
                      >
                    </div>
                  <?php endif; ?>
                  <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input
                      type="text"
                      name="note_text"
                      placeholder="اكتب ملاحظتك أو ردك لمهندس الصيانة بخصوص مرحلة (<?= e($st['title']) ?>)..."
                      required
                      style="flex:1;min-width:220px;padding:10px 14px;border-radius:10px;border:1px solid var(--asly-border);font-family:inherit;font-size:.9rem;"
                    >
                    <button type="submit" class="btn btn-gold" style="padding:10px 20px;">إرسال الملاحظة</button>
                  </div>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <script>
    const TRACK_API = <?= json_encode(appUrl('api/public.php')) ?>;
    const TICKET_CODE = <?= json_encode($code) ?>;
    const QR_TOKEN = <?= json_encode($qrToken) ?>;

    document.querySelectorAll('.js-toggle-reply').forEach((btn) => {
      btn.addEventListener('click', () => {
        const st = btn.dataset.stage;
        const form = document.querySelector(`.js-customer-note-form[data-stage="${st}"]`);
        if (form) {
          form.style.display = form.style.display === 'none' ? 'block' : 'none';
          const inp = form.querySelector('input[name="note_text"]');
          if (inp) inp.focus();
        }
      });
    });

    document.querySelectorAll('.js-customer-note-form').forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const stageKey = form.dataset.stage;
        const noteInput = form.querySelector('input[name="note_text"]');
        const phoneInput = form.querySelector('input[name="phone_verify"]');
        const btn = form.querySelector('button[type="submit"]');

        const payload = {
          action: 'customer_note',
          ticket_code: TICKET_CODE,
          qr_token: QR_TOKEN,
          phone_verify: phoneInput ? phoneInput.value : '',
          stage_key: stageKey,
          note_text: noteInput.value.trim(),
        };

        btn.disabled = true;
        btn.textContent = 'جاري الإرسال...';
        try {
          const res = await fetch(TRACK_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!data.ok) {
            alert(data.error || 'تعذر إرسال الملاحظة');
          } else {
            const list = document.getElementById('notes-' + stageKey);
            const div = document.createElement('div');
            div.className = 'note-bubble customer';
            div.innerHTML = `<div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:4px;opacity:.85;"><strong>👤 العميل (${data.note.sender_name})</strong><span style="direction:ltr;">${data.note.created_at}</span></div><div></div>`;
            div.lastElementChild.textContent = data.note.note_text;
            list.appendChild(div);
            noteInput.value = '';
          }
        } catch (err) {
          alert('حدث خطأ في الاتصال بالخادم.');
        } finally {
          btn.disabled = false;
          btn.textContent = 'إرسال الملاحظة';
        }
      });
    });
  </script>
</body>
</html>
