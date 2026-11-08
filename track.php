<?php

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
  <link rel="stylesheet" href="<?= e(appUrl('assets/vendor/fontawesome/css/all.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(appUrl('assets/css/landing.css')) ?>">
  <style>
    body { background: var(--bg-ivory); }
    .track-wrap { max-width: 960px; margin: 36px auto 72px; padding: 0 16px; display: flex; flex-direction: column; gap: 24px; }
    .track-card {
      background: #ffffff;
      border-radius: 38px 16px 38px 16px;
      border: 1.5px solid var(--teal-border);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .track-head {
      padding: 26px 30px;
      background: linear-gradient(135deg, var(--bg-aqua-soft) 0%, #ffffff 55%, var(--bg-gold-soft) 100%);
      border-bottom: 1.5px solid var(--teal-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .timeline-box { padding: 28px 30px; display: flex; flex-direction: column; gap: 16px; }
    .stage-step {
      border: 1.5px solid var(--line);
      border-radius: 26px 10px 26px 10px;
      padding: 20px 22px;
      background: #ffffff;
      transition: all 0.25s ease;
    }
    .stage-step.completed { border-color: var(--teal-border); background: #f8fefe; }
    .stage-step.current { border-color: var(--gold); background: var(--bg-gold-soft); box-shadow: var(--shadow-xs); }
    .stage-step-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .stage-num {
      width: 40px; height: 40px; border-radius: 14px;
      background: var(--bg-ivory); color: var(--ink-2);
      display: inline-flex; align-items: center; justify-content: center;
      font-weight: 900; flex-shrink: 0; border: 1px solid var(--line);
    }
    .stage-step.completed .stage-num { background: var(--teal); color: #ffffff; border-color: var(--teal); }
    .stage-step.current .stage-num { background: var(--gold); color: #ffffff; border-color: var(--gold); }
    .stage-notes-list { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
    .stage-notes-list:empty { margin-top: 0; }
    .note-bubble {
      padding: 12px 16px;
      border-radius: 18px 8px 18px 8px;
      font-size: 0.9rem;
      border: 1px solid var(--line);
      background: #ffffff;
    }
    .note-bubble.admin { border-color: var(--teal-border); background: var(--bg-aqua-soft); }
    .note-bubble.customer { border-color: var(--gold-border); background: #ffffff; }
  </style>
</head>
<body>

  <header class="subpage-header">
    <div class="container subpage-header-inner">
      <a href="<?= e(appUrl('index.php')) ?>">
        <img src="<?= e(appUrl('assets/brand/logo-transparent.png')) ?>" alt="ASLY Tech Store" class="subpage-logo">
      </a>
      <div class="nav-actions">
        <a href="<?= e(langSwitchUrl($otherLang)) ?>" class="icon-pill-btn lang-pill">
          <i class="fa-solid fa-globe"></i>
          <span><?= e(t('lang_switch_label')) ?></span>
        </a>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-house"></i>
          <span><?= e(t('track_back_home')) ?></span>
        </a>
        <a href="https://wa.me/<?= e($primaryWa) ?>" target="_blank" rel="noopener" class="btn btn-teal btn-sm">
          <i class="fa-brands fa-whatsapp"></i>
          <span><?= e(t('track_wa_support')) ?></span>
        </a>
      </div>
    </div>
  </header>

  <main class="track-wrap">

    
    <div class="track-card" style="padding: 28px 32px;">
      <div class="eyebrow-chip">
        <i class="fa-solid fa-microscope"></i>
        <span><?= e(t('lab_eyebrow')) ?></span>
      </div>
      <h1 style="margin: 0 0 8px; font-size: 1.65rem; font-weight: 900; color: var(--ink-1);"><?= e(t('track_heading')) ?></h1>
      <p style="margin: 0 0 18px; color: var(--ink-2); font-size: 0.95rem;"><?= e(t('track_subheading')) ?></p>
      <form method="get" action="<?= e(appUrl('track.php')) ?>" class="lab-search-box" style="margin-top: 0;">
        <input type="hidden" name="lang" value="<?= e(currentLang()) ?>">
        <i class="fa-solid fa-qrcode prefix-ico"></i>
        <input type="text" name="code" value="<?= e($code) ?>" placeholder="<?= e(t('lab_input_ph')) ?>" required>
        <button type="submit" class="btn btn-teal">
          <i class="fa-solid fa-magnifying-glass"></i>
          <span><?= e(t('track_search_btn')) ?></span>
        </button>
      </form>
    </div>

    <?php if ($lookupError !== ''): ?>
      <div class="track-card" style="padding: 26px; border-color: #fca5a5; background: #fef2f2; color: #b91c1c; text-align: center; font-weight: 800;">
        <i class="fa-solid fa-circle-exclamation" style="margin-inline-end: 8px;"></i>
        <?= e($lookupError) ?>
      </div>
    <?php endif; ?>

    <?php if ($deliveredRef): ?>
      <div class="track-card" style="padding: 44px 30px; text-align: center;">
        <div style="width: 72px; height: 72px; border-radius: 24px 10px 24px 10px; background: #dcfce7; color: #059669; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 16px;">
          <i class="fa-solid fa-check-double"></i>
        </div>
        <h2 style="margin: 0 0 10px; color: var(--ink-1);"><?= e(t('track_delivered_title')) ?> (<?= e((string)$deliveredRef['ticket_code_ref']) ?>)</h2>
        <p style="margin: 0 auto 24px; max-width: 580px; color: var(--ink-2);">
          <?= e(t('track_delivered_desc')) ?> (<?= e((string)$deliveredRef['revenue_date']) ?>)
        </p>
        <a href="<?= e(appUrl('index.php')) ?>" class="btn btn-teal">
          <i class="fa-solid fa-house"></i>
          <span><?= e(t('track_back_home')) ?></span>
        </a>
      </div>
    <?php endif; ?>

    <?php if ($ticket):
        $currentOrder = (int)($stages[$ticket['current_stage']]['order'] ?? 1);
    ?>
      <div class="track-card">
        <div class="track-head">
          <div>
            <div style="font-size: 0.82rem; color: var(--teal-deep); font-weight: 800;">
              <i class="fa-solid fa-shield-halved" style="margin-inline-end: 6px;"></i>
              <?= e(t('track_verified_ticket')) ?> • <?= e((string)$ticket['ticket_code']) ?>
            </div>
            <h2 style="margin: 4px 0 0; font-size: 1.5rem; font-weight: 900; color: var(--ink-1);"><?= e((string)$ticket['device_model']) ?></h2>
          </div>
          <div>
            <span class="btn btn-teal btn-sm" style="pointer-events: none;">
              <i class="<?= e($stages[$ticket['current_stage']]['icon'] ?? 'fa-solid fa-screwdriver-wrench') ?>"></i>
              <span><?= e($stages[$ticket['current_stage']]['title'] ?? $ticket['current_stage']) ?></span>
            </span>
          </div>
        </div>

        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; padding: 22px 30px; background: var(--bg-ivory); border-bottom: 1px solid var(--line-warm); font-size: 0.9rem;">
          <div><strong style="color: var(--ink-1);"><i class="fa-solid fa-user" style="color: var(--teal-deep); margin-inline-end: 5px;"></i><?= e(t('track_client_name')) ?>:</strong><br><?= e((string)$ticket['customer_name']) ?></div>
          <div><strong style="color: var(--ink-1);"><i class="fa-solid fa-microchip" style="color: var(--teal-deep); margin-inline-end: 5px;"></i><?= e(t('track_specs')) ?>:</strong><br><?= e($ticket['device_specs'] ?: '—') ?></div>
          <div><strong style="color: var(--ink-1);"><i class="fa-solid fa-stethoscope" style="color: var(--teal-deep); margin-inline-end: 5px;"></i><?= e(t('track_issue')) ?>:</strong><br><?= e((string)$ticket['problem_description']) ?></div>
          <div>
            <strong style="color: var(--ink-1);"><i class="fa-solid fa-receipt" style="color: var(--gold-deep); margin-inline-end: 5px;"></i><?= e(t('track_cost_deposit')) ?>:</strong><br>
            <?= formatMoney((float)$ticket['estimated_cost']) ?> (<?= e(t('track_paid_prefix')) ?>: <?= formatMoney((float)$ticket['deposit_paid']) ?>)
          </div>
        </div>

        
        <div class="timeline-box">
          <div>
            <h3 style="margin: 0 0 4px; color: var(--ink-1); font-weight: 900;"><?= e(t('track_timeline_title')) ?></h3>
            <p style="margin: 0; font-size: 0.88rem; color: var(--ink-3);"><?= e(t('track_timeline_sub')) ?></p>
          </div>

          <?php foreach ($stages as $stageKey => $st):
              if ($stageKey === 'delivered') continue;
              $order = (int)$st['order'];
              $stateClass = $order < $currentOrder ? 'completed' : ($order === $currentOrder ? 'current' : '');
              $stageNotes = $notesByStage[$stageKey] ?? [];
          ?>
            <div class="stage-step <?= $stateClass ?>">
              <div class="stage-step-head">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <span class="stage-num">
                    <?php if ($order < $currentOrder): ?>
                      <i class="fa-solid fa-check"></i>
                    <?php else: ?>
                      <i class="<?= e($st['icon'] ?? 'fa-solid fa-gear') ?>"></i>
                    <?php endif; ?>
                  </span>
                  <div>
                    <strong style="font-size: 1.03rem; color: var(--ink-1);"><?= e($st['title']) ?></strong>
                    <div style="font-size: 0.84rem; color: var(--ink-3);"><?= e($st['desc']) ?></div>
                  </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                  <?php if ($order === $currentOrder): ?>
                    <span style="padding: 5px 12px; border-radius: 999px; background: #ffffff; color: var(--gold-deep); border: 1px solid var(--gold); font-size: 0.78rem; font-weight: 800;"><?= e(t('track_current_badge')) ?></span>
                  <?php elseif ($order < $currentOrder): ?>
                    <span style="font-size: 0.78rem; color: var(--teal-deep); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> <?= e(t('track_completed_badge')) ?></span>
                  <?php endif; ?>
                  <button type="button" class="btn btn-outline btn-sm js-toggle-reply" data-stage="<?= e($stageKey) ?>">
                    <i class="fa-solid fa-comment-medical"></i>
                    <span><?= e(t('track_add_note_btn')) ?></span>
                  </button>
                </div>
              </div>

              <div class="stage-notes-list" id="notes-<?= e($stageKey) ?>">
                <?php foreach ($stageNotes as $note): ?>
                  <div class="note-bubble <?= e((string)$note['sender_type']) ?>">
                    <div style="display: flex; justify-content: space-between; font-size: 0.78rem; margin-bottom: 4px; color: var(--ink-3);">
                      <strong>
                        <?php if ($note['sender_type'] === 'admin'): ?>
                          <i class="fa-solid fa-user-gear" style="color: var(--teal-deep); margin-inline-end: 5px;"></i><?= e(t('track_eng_prefix')) ?> (<?= e((string)$note['sender_name']) ?>)
                        <?php else: ?>
                          <i class="fa-solid fa-user" style="color: var(--gold-deep); margin-inline-end: 5px;"></i><?= e(t('track_cust_prefix')) ?> (<?= e((string)$note['sender_name']) ?>)
                        <?php endif; ?>
                      </strong>
                      <span style="direction: ltr;"><?= e((string)$note['created_at']) ?></span>
                    </div>
                    <div><?= nl2br(e((string)$note['note_text'])) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>

              <form class="js-customer-note-form" data-stage="<?= e($stageKey) ?>" style="display: <?= $order === $currentOrder ? 'block' : 'none' ?>; margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--teal-border);">
                <?php if (!$hasValidQrToken): ?>
                  <div style="margin-bottom: 8px;">
                    <input type="text" name="phone_verify" placeholder="<?= e(t('track_phone_verify_ph')) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 14px; border: 1.5px solid var(--teal-border); font-size: 0.86rem;">
                  </div>
                <?php endif; ?>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                  <input type="text" name="note_text" placeholder="<?= e(t('track_note_ph')) ?>" required style="flex: 1; min-width: 220px; padding: 11px 16px; border-radius: 999px; border: 1.5px solid var(--teal-border); font-size: 0.9rem;">
                  <button type="submit" class="btn btn-teal btn-sm" style="padding: 11px 22px;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span><?= e(t('track_send_note')) ?></span>
                  </button>
                </div>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

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
        const btnSpan = btn.querySelector('span');

        const payload = {
          action: 'customer_note',
          ticket_code: TICKET_CODE,
          qr_token: QR_TOKEN,
          phone_verify: phoneInput ? phoneInput.value : '',
          stage_key: stageKey,
          note_text: noteInput.value.trim(),
        };

        btn.disabled = true;
        if (btnSpan) btnSpan.textContent = LBL_SENDING;
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
            div.innerHTML = `<div style="display:flex;justify-content:space-between;font-size:0.78rem;margin-bottom:4px;color:var(--ink-3);"><strong><i class="fa-solid fa-user" style="color:var(--gold-deep);margin-inline-end:5px;"></i>${LBL_CUST} (${data.note.sender_name})</strong><span style="direction:ltr;">${data.note.created_at}</span></div><div></div>`;
            div.lastElementChild.textContent = data.note.note_text;
            list.appendChild(div);
            noteInput.value = '';
          }
        } catch (err) {
          alert('Connection error.');
        } finally {
          btn.disabled = false;
          if (btnSpan) btnSpan.textContent = LBL_SEND;
        }
      });
    });
  </script>
</body>
</html>
