(function() {
  "use strict";
  const cfg = window.ASLY_PUBLIC || {};
  function esc(str) {
    const d = document.createElement("div");
    d.textContent = str == null ? "" : String(str);
    return d.innerHTML;
  }
  const revealItems = document.querySelectorAll(".reveal-up");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("in-view");
          io.unobserve(entry.target);
        }
      });
    }, {
      threshold: .1
    });
    revealItems.forEach(el => io.observe(el));
  } else {
    revealItems.forEach(el => el.classList.add("in-view"));
  }
  const parallaxNodes = document.querySelectorAll("[data-parallax]");
  if (parallaxNodes.length && window.matchMedia("(pointer: fine)").matches) {
    window.addEventListener("mousemove", e => {
      const rx = (e.clientX / window.innerWidth - .5) * 2;
      const ry = (e.clientY / window.innerHeight - .5) * 2;
      parallaxNodes.forEach(node => {
        const factor = parseFloat(node.getAttribute("data-parallax") || "10");
        node.style.translate = `${(rx * factor).toFixed(1)}px ${(ry * factor).toFixed(1)}px`;
      });
    });
  }
  const slices = document.querySelectorAll(".craft-slice");
  slices.forEach(slice => {
    slice.addEventListener("mouseenter", () => {
      slices.forEach(s => s.classList.remove("active"));
      slice.classList.add("active");
    });
    slice.addEventListener("click", () => {
      slices.forEach(s => s.classList.remove("active"));
      slice.classList.add("active");
    });
  });
  document.querySelectorAll(".faq-trigger").forEach(btn => {
    btn.addEventListener("click", () => {
      const item = btn.closest(".faq-item");
      const ans = item.querySelector(".faq-answer");
      const isOpen = item.classList.contains("open");
      document.querySelectorAll(".faq-item").forEach(other => {
        other.classList.remove("open");
        const oa = other.querySelector(".faq-answer");
        if (oa) oa.style.maxHeight = "0px";
      });
      if (!isOpen) {
        item.classList.add("open");
        ans.style.maxHeight = ans.scrollHeight + 24 + "px";
      }
    });
  });
  const trackForm = document.getElementById("inlineTrackForm");
  const ticketInput = document.getElementById("inlineTicketInput");
  const resultBox = document.getElementById("inlineTicketResult");
  async function inspectTicketInline(rawCode) {
    const code = (rawCode || "").trim().toUpperCase();
    if (!code || !resultBox) return;
    resultBox.classList.add("visible");
    resultBox.innerHTML = `\n      <div style="display:flex;align-items:center;gap:10px;font-weight:800;color:var(--teal-deep);">\n        <i class="fa-solid fa-circle-notch fa-spin"></i>\n        <span>${esc(cfg.i18n?.checking || "Checking...")}</span>\n      </div>\n    `;
    try {
      const url = `${cfg.apiPublicUrl}?action=track&code=${encodeURIComponent(code)}&lang=${encodeURIComponent(cfg.lang || "ar")}`;
      const res = await fetch(url, {
        headers: {
          Accept: "application/json"
        }
      });
      const data = await res.json();
      if (!data.ok) {
        resultBox.innerHTML = `\n          <div style="display:flex;align-items:center;gap:12px;color:#b91c1c;font-weight:800;">\n            <i class="fa-solid fa-circle-exclamation" style="font-size:1.25rem;"></i>\n            <span>${esc(data.error || "Ticket not found")}</span>\n          </div>\n        `;
        return;
      }
      if (data.delivered_and_wiped) {
        resultBox.innerHTML = `\n          <div style="display:flex;align-items:flex-start;gap:14px;">\n            <div class="chip-icon" style="background:#dcfce7;color:#059669;">\n              <i class="fa-solid fa-shield-check fa-check-double"></i>\n            </div>\n            <div>\n              <div style="font-weight:900;font-size:1.05rem;color:var(--ink-1);">${esc(cfg.i18n?.wipedTitle)} (${esc(data.ticket_code)})</div>\n              <p style="margin:6px 0 0;font-size:0.9rem;color:var(--ink-2);">${esc(data.message)}</p>\n            </div>\n          </div>\n        `;
        return;
      }
      const t = data.ticket;
      const stages = data.stages || {};
      const stageKeys = Object.keys(stages);
      const curStageObj = stages[t.current_stage] || {
        order: 1,
        title: t.current_stage,
        icon: "fa-solid fa-screwdriver-wrench"
      };
      const pct = Math.min(100, Math.max(14, Math.round((curStageObj.order || 1) / Math.max(1, stageKeys.length) * 100)));
      const notes = Array.isArray(data.notes) ? data.notes : [];
      const lastNote = notes.length ? notes[notes.length - 1] : null;
      const fullUrl = data.track_url || `${cfg.trackPageUrl}?code=${encodeURIComponent(t.ticket_code)}&lang=${encodeURIComponent(cfg.lang || "ar")}`;
      resultBox.innerHTML = `\n        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">\n          <div style="display:flex;align-items:center;gap:10px;">\n            <span class="chip-icon"><i class="${esc(curStageObj.icon || "fa-solid fa-microscope")}"></i></span>\n            <div>\n              <div style="font-family:var(--font-mono);font-size:0.78rem;font-weight:700;color:var(--teal-deep);">${esc(t.ticket_code)}</div>\n              <div style="font-weight:900;font-size:1.06rem;color:var(--ink-1);">${esc(t.device_model)}</div>\n            </div>\n          </div>\n          <span style="padding:6px 14px;border-radius:999px;background:var(--bg-aqua-soft);border:1px solid var(--teal-border);font-size:0.82rem;font-weight:800;color:var(--teal-deep);">\n            <i class="fa-solid fa-wave-square" style="margin-inline-end:5px;"></i>${esc(curStageObj.title)}\n          </span>\n        </div>\n\n        <div class="inline-stage-bar">\n          <div class="inline-stage-fill" style="width:${pct}%;"></div>\n        </div>\n\n        ${lastNote ? `\n          <div style="background:var(--bg-ivory);border-radius:16px;padding:12px 15px;font-size:0.88rem;color:var(--ink-2);margin-bottom:14px;border:1px solid var(--line-warm);">\n            <strong style="color:var(--ink-1);display:block;margin-bottom:3px;">\n              <i class="fa-solid fa-comment-dots" style="color:var(--gold-deep);margin-inline-end:6px;"></i>${esc(cfg.i18n?.latestNote)}:\n            </strong>\n            ${esc(lastNote.note_text)}\n          </div>\n        ` : ""}\n\n        <a href="${esc(fullUrl)}" class="btn btn-teal btn-sm" style="width:100%;">\n          <i class="fa-solid fa-comments"></i>\n          <span>${esc(cfg.i18n?.openFull)}</span>\n        </a>\n      `;
    } catch (err) {
      window.location.href = `${cfg.trackPageUrl}?code=${encodeURIComponent(code)}&lang=${encodeURIComponent(cfg.lang || "ar")}`;
    }
  }
  if (trackForm && ticketInput) {
    trackForm.addEventListener("submit", e => {
      e.preventDefault();
      inspectTicketInline(ticketInput.value);
    });
  }
  const qrModal = document.getElementById("landingQrModal");
  const openQrBtn = document.getElementById("openLandingQrBtn");
  const closeQrBtn = document.getElementById("closeLandingQrBtn");
  const qrFileInput = document.getElementById("landingQrFileInput");
  const qrStatus = document.getElementById("landingQrStatus");
  let html5Qr = null;
  function handleDecodedQr(decodedText) {
    stopLandingQr();
    if (!decodedText) return;
    const trimmed = decodedText.trim();
    if (/^https?:\/\//i.test(trimmed) && trimmed.includes("track.php")) {
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
    if (window.AslyTurboScanner) {
      await window.AslyTurboScanner.stop("landingQrReader");
    }
    if (qrModal) qrModal.classList.remove("open");
  }
  if (openQrBtn && qrModal) {
    openQrBtn.addEventListener("click", async () => {
      qrModal.classList.add("open");
      if (qrStatus) qrStatus.textContent = "";
      if (!window.AslyTurboScanner) return;
      const ok = await window.AslyTurboScanner.start("landingQrReader", decoded => handleDecodedQr(decoded), { isEn: cfg.lang === "en" });
      if (!ok && qrStatus) {
        qrStatus.textContent = cfg.lang === "en" ? "Camera unavailable — choose a receipt photo instead." : "الكاميرا غير متاحة — يمكنك اختيار صورة الإيصال مباشرة.";
      }
    });
  }
  if (closeQrBtn) {
    closeQrBtn.addEventListener("click", stopLandingQr);
  }
  if (qrModal) {
    qrModal.addEventListener("click", e => {
      if (e.target === qrModal) stopLandingQr();
    });
  }
  if (qrFileInput) {
    qrFileInput.addEventListener("change", async e => {
      const file = e.target.files && e.target.files[0];
      if (!file || !window.AslyTurboScanner) return;
      try {
        await window.AslyTurboScanner.stop("landingQrReader");
        const decoded = await window.AslyTurboScanner.scanFile(file);
        handleDecodedQr(decoded);
      } catch (_) {
        if (qrStatus) {
          qrStatus.textContent = cfg.lang === "en" ? "No valid QR code found in selected image." : "لم يتم العثور على رمز QR واضح في الصورة المختارة.";
        }
      }
    });
  }
  const mobileModal = document.getElementById("mobileNavModal");
  const openMobBtn = document.getElementById("openMobileDrawerBtn");
  const closeMobBtn = document.getElementById("closeMobileDrawerBtn");
  if (openMobBtn && mobileModal) {
    openMobBtn.addEventListener("click", () => mobileModal.classList.add("open"));
  }
  if (closeMobBtn && mobileModal) {
    closeMobBtn.addEventListener("click", () => mobileModal.classList.remove("open"));
  }
  document.querySelectorAll(".mobile-nav-item").forEach(link => {
    link.addEventListener("click", () => mobileModal && mobileModal.classList.remove("open"));
  });
  const arMulti = [ [ "لا", "b" ], [ "لآ", "B" ], [ "لأ", "G" ], [ "لإ", "T" ] ];
  const arSingle = {
    "ض": "q", "ص": "w", "ث": "e", "ق": "r", "ف": "t", "غ": "y", "ع": "u", "ه": "i", "خ": "o", "ح": "p",
    "ش": "a", "س": "s", "ي": "d", "ب": "f", "ل": "g", "ا": "h", "ت": "j", "ن": "k", "م": "l", "ك": ";",
    "ئ": "z", "ء": "x", "ؤ": "c", "ر": "v", "ى": "n", "ة": "m", "و": ",", "ز": ".", "ظ": "/",
    "٠": "0", "١": "1", "٢": "2", "٣": "3", "٤": "4", "٥": "5", "٦": "6", "٧": "7", "٨": "8", "٩": "9"
  };
  function cleanWedge(raw) {
    let s = String(raw || "").trim();
    for (const [ f, t ] of arMulti) s = s.split(f).join(t);
    let out = "";
    for (const ch of s) out += arSingle[ch] !== undefined ? arSingle[ch] : ch;
    const m = out.match(/[?&]code=([^&#\s]+)/i);
    if (m && m[1]) {
      try { return decodeURIComponent(m[1]).trim(); } catch (_) { return m[1].trim(); }
    }
    return out;
  }
  let pubScanBuf = "";
  let pubLastTs = 0;
  window.addEventListener("keydown", e => {
    if (e.ctrlKey || e.metaKey || e.altKey) return;
    const tag = e.target && e.target.tagName ? e.target.tagName.toUpperCase() : "";
    if (tag === "INPUT" || tag === "TEXTAREA") return;
    const now = performance.now();
    if (e.key === "Enter") {
      if (pubScanBuf.length >= 4 && now - pubLastTs < 250) {
        e.preventDefault();
        const code = cleanWedge(pubScanBuf);
        pubScanBuf = "";
        if (ticketInput) {
          ticketInput.value = code.toUpperCase();
          document.getElementById("repair-portal")?.scrollIntoView({ behavior: "smooth", block: "center" });
          inspectTicketInline(code);
        }
      } else {
        pubScanBuf = "";
      }
      return;
    }
    if (e.key.length === 1) {
      pubScanBuf = now - pubLastTs > 150 ? e.key : pubScanBuf + e.key;
      pubLastTs = now;
    }
  }, true);
})();
