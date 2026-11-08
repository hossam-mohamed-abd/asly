<?php
/**
 * ASLY | أصلي TECH STORE
 * Interactive Customer QR Maintenance Tracking & Stage Commenting Page (Bilingual EN + AR)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getConnection();
$settings = getAllSettings($pdo);
$stages = getMaintenanceStages();
$en = isEn();
$otherLang = $en ? 'ar' : 'en';

$storeNameAr = $settings['store_name_ar'] ?: 'أصلي تك ستور';
$storeNameEn = $settings['store_name_en'] ?: 'ASLY TECH STORE';
$displayStore = $en ? $storeNameEn : $storeNameAr;
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
            $lookupError = t('track_not_found');
        }
    }
}

$hasValidQrToken = $ticket && $qrToken !== '' && hash_equals((string)$ticket['qr_token'], $qrToken);
?>
<!DOCTYPE html>
<html lang="<?= e(currentLang()) ?>" dir="<?= e(htmlDir()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(t('track_page_title')) ?> | <?= e($displayStore) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(appUrl('assets/brand/favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
</head>
<body>

  <header class="site-header">
    <div class="container nav-inner">
      <a href="<?= e(appUrl('index.php')) ?>" class="brand-logo">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store">
      </a>
      <div class="nav-actions">
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill">
          🌐 <?= e(t('lang_switch_label')) ?>
        </a>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-soft btn-sm"><?= e(t('track_back_home')) ?></a>
        <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-teal btn-sm">💬 <?= e(t('track_wa_support')) ?></a>
      </div>
    </div>
  </header>

  <main class="track-shell">
    <div class="container" style="max-width:940px;">

      <!-- Search Box -->
      <div class="track-card" style="padding:28px 32px;">
        <div class="eyebrow-pill">🔬 <?= e(t('lab_eyebrow')) ?></div>
        <h1 style="margin:0 0 8px;font-size:1.65rem;color:var(--ink-heading);"><?= e(t('track_heading')) ?></h1>
        <p style="margin:0 0 20px;color:var(--ink-muted);font-size:0.95rem;"><?= e(t('track_subheading')) ?></p>
        <form method="get" action="<?= e(appUrl('track.php')) ?>" class="track-mini-form">
          <input type="text" name="code" value="<?= e($code) ?>" placeholder="<?= e(t('lab_input_ph')) ?>" required>
          <button type="submit" class="btn btn-teal"><?= e(t('track_search_btn')) ?></button>
        </form>
      </div>

      <?php if ($lookupError !== ''): ?>
        <div class="track-card" style="padding:26px;border-color:#fca5a5;background:#fef2f2;color:#b91c1c;text-align:center;font-weight:700;">
          <?= e($lookupError) ?>
        </div>
      <?php endif; ?>

      <?php if ($deliveredRef): ?>
        <div class="track-card" style="padding:40px 30px;text-align:center;">
          <div style="width:68px;height:68px;border-radius:50%;background:#ecfdf5;color:#059669;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:14px;">✓</div>
          <h2 style="margin:0 0 10px;color:var(--ink-heading);"><?= e(t('track_delivered_title')) ?> (<?= e((string)$deliveredRef['ticket_code_ref']) ?>)</h2>
          <p style="margin:0 auto 22px;max-width:560px;color:var(--ink-muted);">
            <?= e(t('track_delivered_desc')) ?> (<?= e((string)$deliveredRef['revenue_date']) ?>)
          </p>
          <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-teal"><?= e(t('track_back_home')) ?></a>
        </div>
      <?php endif; ?>

      <?php if ($ticket):
        $currentOrder = (int)($stages[$ticket['current_stage']]['order'] ?? 1);
      ?>
        <div class="track-card">
          <div class="track-card-header">
            <div>
              <div style="font-size:0.82rem;color:var(--asly-teal-600);font-weight:800;"><?= e(t('track_verified_ticket')) ?> • <?= e((string)$ticket['ticket_code']) ?></div>
              <h2 style="margin:4px 0 0;font-size:1.5rem;color:var(--ink-heading);"><?= e((string)$ticket['device_model']) ?></h2>
            </div>
            <div>
              <span style="display:inline-block;padding:8px 18px;border-radius:999px;background:var(--asly-teal-500);color:#ffffff;font-weight:800;font-size:0.9rem;">
                <?= e($stages[$ticket['current_stage']]['title'] ?? $ticket['current_stage']) ?>
              </span>
            </div>
          </div>

          <!-- Device Summary Grid -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;padding:22px 30px;background:var(--bg-ivory);border-bottom:1px solid var(--line-soft);font-size:0.9rem;">
            <div><strong style="color:var(--ink-heading);"><?= e(t('track_client_name')) ?>:</strong><br><?= e((string)$ticket['customer_name']) ?></div>
            <div><strong style="color:var(--ink-heading);"><?= e(t('track_specs')) ?>:</strong><br><?= e($ticket['device_specs'] ?: '—') ?></div>
            <div><strong style="color:var(--ink-heading);"><?= e(t('track_issue')) ?>:</strong><br><?= e((string)$ticket['problem_description']) ?></div>
            <div>
              <strong style="color:var(--ink-heading);"><?= e(t('track_cost_deposit')) ?>:</strong><br>
              <?= formatMoney((float)$ticket['estimated_cost']) ?> (<?= e(t('track_paid_prefix')) ?>: <?= formatMoney((float)$ticket['deposit_paid']) ?>)
            </div>
          </div>

          <!-- Stages Timeline & Mutual Notes -->
          <div class="timeline-steps">
            <h3 style="margin:0 0 4px;color:var(--ink-heading);"><?= e(t('track_timeline_title')) ?></h3>
            <p style="margin:0 0 12px;font-size:0.88rem;color:var(--ink-muted);"><?= e(t('track_timeline_sub')) ?></p>

            <?php foreach ($stages as $stageKey => $st):
              if ($stageKey === 'delivered') continue;
              $order = (int)$st['order'];
              $stateClass = $order < $currentOrder ? 'completed' : ($order === $currentOrder ? 'current' : '');
              $stageNotes = $notesByStage[$stageKey] ?? [];
            ?>
              <div class="stage-step <?= $stateClass ?>">
                <div class="stage-step-head">
                  <div style="display:flex;align-items:center;gap:12px;">
                    <span class="stage-num"><?= $order < $currentOrder ? '✓' : $order ?></span>
                    <div>
                      <strong style="font-size:1.03rem;color:var(--ink-heading);"><?= e($st['title']) ?></strong>
                      <div style="font-size:0.84rem;color:var(--ink-muted);"><?= e($st['desc']) ?></div>
                    </div>
                  </div>
                  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <?php if ($order === $currentOrder): ?>
                      <span style="padding:4px 12px;border-radius:999px;background:var(--bg-white);color:var(--asly-gold-600);border:1px solid var(--asly-gold-500);font-size:0.78rem;font-weight:800;"><?= e(t('track_current_badge')) ?></span>
                    <?php elseif ($order < $currentOrder): ?>
                      <span style="font-size:0.78rem;color:var(--asly-teal-600);font-weight:800;"><?= e(t('track_completed_badge')) ?></span>
                    <?php endif; ?>
                    <button
                      type="button"
                      class="btn btn-soft btn-sm js-toggle-reply"
                      data-stage="<?= e($stageKey) ?>"
                    >
                      <?= e(t('track_add_note_btn')) ?>
                    </button>
                  </div>
                </div>

                <!-- Notes on this stage -->
                <div class="stage-notes-list" id="notes-<?= e($stageKey) ?>">
                  <?php foreach ($stageNotes as $note): ?>
                    <div class="note-bubble <?= e((string)$note['sender_type']) ?>">
                      <div style="display:flex;justify-content:space-between;font-size:0.78rem;margin-bottom:4px;color:var(--ink-muted);">
                        <strong><?= $note['sender_type'] === 'admin' ? '👨‍🔧 ' . e(t('track_eng_prefix')) . ' (' . e((string)$note['sender_name']) . ')' : '👤 ' . e(t('track_cust_prefix')) . ' (' . e((string)$note['sender_name']) . ')' ?></strong>
                        <span style="direction:ltr;"><?= e((string)$note['created_at']) ?></span>
                      </div>
                      <div><?= nl2br(e((string)$note['note_text'])) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Customer Reply Form for this Stage -->
                <form class="js-customer-note-form" data-stage="<?= e($stageKey) ?>" style="display:<?= $order === $currentOrder ? 'block' : 'none' ?>;margin-top:14px;padding-top:12px;border-top:1px dashed var(--line-aqua);">
                  <?php if (!$hasValidQrToken): ?>
                    <div style="margin-bottom:8px;">
                      <input
                        type="text"
                        name="phone_verify"
                        placeholder="<?= e(t('track_phone_verify_ph')) ?>"
                        required
                        style="width:100%;padding:10px 14px;border-radius:14px;border:1px solid var(--line-aqua);font-family:inherit;font-size:0.86rem;"
                      >
                    </div>
                  <?php endif; ?>
                  <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input
                      type="text"
                      name="note_text"
                      placeholder="<?= e(t('track_note_ph')) ?>"
                      required
                      style="flex:1;min-width:220px;padding:11px 16px;border-radius:999px;border:1px solid var(--line-aqua);font-family:inherit;font-size:0.9rem;"
                    >
                    <button type="submit" class="btn btn-teal btn-sm" style="padding:11px 22px;"><?= e(t('track_send_note')) ?></button>
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
    const LBL_SEND = <?= json_encode(t('track_send_note'), JSON_UNESCAPED_UNICODE) ?>;
    const LBL_SENDING = <?= json_encode(t('track_sending'), JSON_UNESCAPED_UNICODE) ?>;
    const LBL_CUST = <?= json_encode(t('track_cust_prefix'), JSON_UNESCAPED_UNICODE) ?>;

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
        btn.textContent = LBL_SENDING;
        try {
          const res = await fetch(TRACK_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });
          const data = await res.json();
          if (!data.ok) {
            alert(data.error || 'Error');
          } else {
            const list = document.getElementById('notes-' + stageKey);
            const div = document.createElement('div');
            div.className = 'note-bubble customer';
            div.innerHTML = `<div style="display:flex;justify-content:space-between;font-size:0.78rem;margin-bottom:4px;color:var(--ink-muted);"><strong>👤 ${LBL_CUST} (${data.note.sender_name})</strong><span style="direction:ltr;">${data.note.created_at}</span></div><div></div>`;
            div.lastElementChild.textContent = data.note.note_text;
            list.appendChild(div);
            noteInput.value = '';
          }
        } catch (err) {
          alert('Connection error.');
        } finally {
          btn.disabled = false;
          btn.textContent = LBL_SEND;
        }
      });
    });
  </script>
</body>
</html>
