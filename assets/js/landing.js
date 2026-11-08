/**
 * ASLY | أصلي TECH STORE
 * Interactive Geometric & Editorial Landing Page Controller (Zero Emojis • Font Awesome 6)
 */
(function () {
  'use strict';

  const cfg = window.ASLY_PUBLIC || {};

  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }

  // 1. Scroll Reveal Observer
  const revealItems = document.querySelectorAll('.reveal-up');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 }
    );
    revealItems.forEach((el) => io.observe(el));
  } else {
    revealItems.forEach((el) => el.classList.add('in-view'));
  }

  // 2. Subtle Cursor Parallax for Geometric Primitives & Collage Frames
  const parallaxNodes = document.querySelectorAll('[data-parallax]');
  if (parallaxNodes.length && window.matchMedia('(pointer: fine)').matches) {
    window.addEventListener('mousemove', (e) => {
      const rx = (e.clientX / window.innerWidth - 0.5) * 2;
      const ry = (e.clientY / window.innerHeight - 0.5) * 2;
      parallaxNodes.forEach((node) => {
        const factor = parseFloat(node.getAttribute('data-parallax') || '10');
        node.style.translate = `${(rx * factor).toFixed(1)}px ${(ry * factor).toFixed(1)}px`;
      });
    });
  }

  // 3. Interactive Domains of Craft Slices
  const slices = document.querySelectorAll('.craft-slice');
  slices.forEach((slice) => {
    slice.addEventListener('mouseenter', () => {
      slices.forEach((s) => s.classList.remove('active'));
      slice.classList.add('active');
    });
    slice.addEventListener('click', () => {
      slices.forEach((s) => s.classList.remove('active'));
      slice.classList.add('active');
    });
  });

  // 4. Smooth FAQ Accordion
  document.querySelectorAll('.faq-trigger').forEach((btn) => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const ans = item.querySelector('.faq-answer');
      const isOpen = item.classList.contains('open');

      document.querySelectorAll('.faq-item').forEach((other) => {
        other.classList.remove('open');
        const oa = other.querySelector('.faq-answer');
        if (oa) oa.style.maxHeight = '0px';
      });

      if (!isOpen) {
        item.classList.add('open');
        ans.style.maxHeight = ans.scrollHeight + 24 + 'px';
      }
    });
  });

  // 5. Instant Live AJAX Repair Ticket Lookup right inside the Landing Page
  const trackForm = document.getElementById('inlineTrackForm');
  const ticketInput = document.getElementById('inlineTicketInput');
  const resultBox = document.getElementById('inlineTicketResult');

  async function inspectTicketInline(rawCode) {
    const code = (rawCode || '').trim().toUpperCase();
    if (!code || !resultBox) return;

    resultBox.classList.add('visible');
    resultBox.innerHTML = `
      <div style="display:flex;align-items:center;gap:10px;font-weight:800;color:var(--teal-deep);">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
        <span>${esc(cfg.i18n?.checking || 'Checking...')}</span>
      </div>
    `;

    try {
      const url = `${cfg.apiPublicUrl}?action=track&code=${encodeURIComponent(code)}&lang=${encodeURIComponent(cfg.lang || 'ar')}`;
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      const data = await res.json();

      if (!data.ok) {
        resultBox.innerHTML = `
          <div style="display:flex;align-items:center;gap:12px;color:#b91c1c;font-weight:800;">
            <i class="fa-solid fa-circle-exclamation" style="font-size:1.25rem;"></i>
            <span>${esc(data.error || 'Ticket not found')}</span>
          </div>
        `;
        return;
      }

      if (data.delivered_and_wiped) {
        resultBox.innerHTML = `
          <div style="display:flex;align-items:flex-start;gap:14px;">
            <div class="chip-icon" style="background:#dcfce7;color:#059669;">
              <i class="fa-solid fa-shield-check fa-check-double"></i>
            </div>
            <div>
              <div style="font-weight:900;font-size:1.05rem;color:var(--ink-1);">${esc(cfg.i18n?.wipedTitle)} (${esc(data.ticket_code)})</div>
              <p style="margin:6px 0 0;font-size:0.9rem;color:var(--ink-2);">${esc(data.message)}</p>
            </div>
          </div>
        `;
        return;
      }

      const t = data.ticket;
      const stages = data.stages || {};
      const stageKeys = Object.keys(stages);
      const curStageObj = stages[t.current_stage] || { order: 1, title: t.current_stage, icon: 'fa-solid fa-screwdriver-wrench' };
      const pct = Math.min(100, Math.max(14, Math.round(((curStageObj.order || 1) / Math.max(1, stageKeys.length)) * 100)));
      const notes = Array.isArray(data.notes) ? data.notes : [];
      const lastNote = notes.length ? notes[notes.length - 1] : null;
      const fullUrl = data.track_url || `${cfg.trackPageUrl}?code=${encodeURIComponent(t.ticket_code)}&lang=${encodeURIComponent(cfg.lang || 'ar')}`;

      resultBox.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="chip-icon"><i class="${esc(curStageObj.icon || 'fa-solid fa-microscope')}"></i></span>
            <div>
              <div style="font-family:var(--font-mono);font-size:0.78rem;font-weight:700;color:var(--teal-deep);">${esc(t.ticket_code)}</div>
              <div style="font-weight:900;font-size:1.06rem;color:var(--ink-1);">${esc(t.device_model)}</div>
            </div>
          </div>
          <span style="padding:6px 14px;border-radius:999px;background:var(--bg-aqua-soft);border:1px solid var(--teal-border);font-size:0.82rem;font-weight:800;color:var(--teal-deep);">
            <i class="fa-solid fa-wave-square" style="margin-inline-end:5px;"></i>${esc(curStageObj.title)}
          </span>
        </div>

        <div class="inline-stage-bar">
          <div class="inline-stage-fill" style="width:${pct}%;"></div>
        </div>

        ${lastNote ? `
          <div style="background:var(--bg-ivory);border-radius:16px;padding:12px 15px;font-size:0.88rem;color:var(--ink-2);margin-bottom:14px;border:1px solid var(--line-warm);">
            <strong style="color:var(--ink-1);display:block;margin-bottom:3px;">
              <i class="fa-solid fa-comment-dots" style="color:var(--gold-deep);margin-inline-end:6px;"></i>${esc(cfg.i18n?.latestNote)}:
            </strong>
            ${esc(lastNote.note_text)}
          </div>
        ` : ''}

        <a href="${esc(fullUrl)}" class="btn btn-teal btn-sm" style="width:100%;">
          <i class="fa-solid fa-comments"></i>
          <span>${esc(cfg.i18n?.openFull)}</span>
        </a>
      `;
    } catch (err) {
      window.location.href = `${cfg.trackPageUrl}?code=${encodeURIComponent(code)}&lang=${encodeURIComponent(cfg.lang || 'ar')}`;
    }
  }

  if (trackForm && ticketInput) {
    trackForm.addEventListener('submit', (e) => {
      e.preventDefault();
      inspectTicketInline(ticketInput.value);
    });
  }

  // 6. Camera QR Receipt Scanner Modal
  const qrModal = document.getElementById('landingQrModal');
  const openQrBtn = document.getElementById('openLandingQrBtn');
  const closeQrBtn = document.getElementById('closeLandingQrBtn');
  const qrFileInput = document.getElementById('landingQrFileInput');
  const qrStatus = document.getElementById('landingQrStatus');
  let html5Qr = null;

  function handleDecodedQr(decodedText) {
    stopLandingQr();
    if (!decodedText) return;
    const trimmed = decodedText.trim();
    if (/^https?:\/\//i.test(trimmed) && trimmed.includes('track.php')) {
      window.location.href = trimmed;
      return;
    }
    const match = trimmed.match(/MNT-[A-Z0-9-]+/i);
    const code = match ? match[0].toUpperCase() : trimmed;
    if (ticketInput) {
      ticketInput.value = code;
      inspectTicketInline(code);
    } else {
      window.location.href = `${cfg.trackPageUrl}?code=${encodeURIComponent(code)}`;
    }
  }

  async function stopLandingQr() {
    if (html5Qr) {
      try {
        if (html5Qr.isScanning) {
          await html5Qr.stop();
        }
        html5Qr.clear();
      } catch (_) {}
      html5Qr = null;
    }
    if (qrModal) qrModal.classList.remove('open');
  }

  if (openQrBtn && qrModal) {
    openQrBtn.addEventListener('click', async () => {
      qrModal.classList.add('open');
      if (qrStatus) qrStatus.textContent = '';
      if (typeof window.Html5Qrcode === 'undefined') return;
      try {
        html5Qr = new window.Html5Qrcode('landingQrReader');
        await html5Qr.start(
          { facingMode: 'environment' },
          { fps: 10, qrbox: { width: 220, height: 220 } },
          (decoded) => handleDecodedQr(decoded),
          () => {}
        );
      } catch (_) {
        if (qrStatus) {
          qrStatus.textContent = cfg.lang === 'en'
            ? 'Camera unavailable — choose a receipt photo instead.'
            : 'الكاميرا غير متاحة — يمكنك اختيار صورة الإيصال مباشرة.';
        }
      }
    });
  }

  if (closeQrBtn) {
    closeQrBtn.addEventListener('click', stopLandingQr);
  }
  if (qrModal) {
    qrModal.addEventListener('click', (e) => {
      if (e.target === qrModal) stopLandingQr();
    });
  }

  if (qrFileInput) {
    qrFileInput.addEventListener('change', async (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file || typeof window.Html5Qrcode === 'undefined') return;
      try {
        if (html5Qr && html5Qr.isScanning) await html5Qr.stop();
        const fileQr = new window.Html5Qrcode('landingQrReader');
        const decoded = await fileQr.scanFile(file, true);
        handleDecodedQr(decoded);
      } catch (_) {
        if (qrStatus) {
          qrStatus.textContent = cfg.lang === 'en'
            ? 'No valid QR code found in selected image.'
            : 'لم يتم العثور على رمز QR واضح في الصورة المختارة.';
        }
      }
    });
  }

  // 7. Mobile Navigation Drawer
  const mobileModal = document.getElementById('mobileNavModal');
  const openMobBtn = document.getElementById('openMobileDrawerBtn');
  const closeMobBtn = document.getElementById('closeMobileDrawerBtn');
  if (openMobBtn && mobileModal) {
    openMobBtn.addEventListener('click', () => mobileModal.classList.add('open'));
  }
  if (closeMobBtn && mobileModal) {
    closeMobBtn.addEventListener('click', () => mobileModal.classList.remove('open'));
  }
  document.querySelectorAll('.mobile-nav-item').forEach((link) => {
    link.addEventListener('click', () => mobileModal && mobileModal.classList.remove('open'));
  });
})();
