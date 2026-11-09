(function() {
  "use strict";
  const cfg = window.ASLY_PUBLIC || {};
  const isRtl = (document.documentElement.getAttribute("dir") || "").toLowerCase() === "rtl";

  document.documentElement.classList.add("js-motion");

  function esc(str) {
    const d = document.createElement("div");
    d.textContent = str == null ? "" : String(str);
    return d.innerHTML;
  }

  function prepareTypeBlock(root) {
    const words = [];
    function walk(node) {
      if (node.nodeType === Node.TEXT_NODE) {
        const text = node.nodeValue || "";
        if (!text.trim()) return;
        const parts = text.split(/(\s+)/);
        const frag = document.createDocumentFragment();
        for (let i = 0; i < parts.length; i++) {
          const part = parts[i];
          if (!part) continue;
          if (/^\s+$/.test(part)) {
            frag.appendChild(document.createTextNode(part));
          } else {
            const span = document.createElement("span");
            span.className = "tw-word";
            span.textContent = part;
            words.push({ el: span, weight: Math.max(2, part.length), start: 0, end: 1 });
            frag.appendChild(span);
          }
        }
        if (node.parentNode) {
          node.parentNode.replaceChild(frag, node);
        }
        return;
      }
      if (node.nodeType === Node.ELEMENT_NODE) {
        const tag = node.tagName.toUpperCase();
        if (tag === "SCRIPT" || tag === "STYLE" || node.classList.contains("tw-word") || node.classList.contains("tw-caret")) return;
        const children = Array.from(node.childNodes);
        for (let i = 0; i < children.length; i++) {
          walk(children[i]);
        }
      }
    }
    walk(root);
    if (!words.length) return null;

    let totalWeight = 0;
    for (let i = 0; i < words.length; i++) totalWeight += words[i].weight;
    let acc = 0;
    for (let i = 0; i < words.length; i++) {
      const w = words[i];
      w.start = acc / totalWeight;
      acc += w.weight;
      w.end = acc / totalWeight;
    }

    const caret = document.createElement("span");
    caret.className = "tw-caret idle";
    caret.setAttribute("aria-hidden", "true");
    root.appendChild(caret);

    return {
      el: root,
      words,
      totalWeight,
      caret,
      current: 0,
      target: 0,
      lastRendered: -1
    };
  }

  function applyWriteProgress(blk, rawP) {
    const p = rawP < 0 ? 0 : rawP > 1 ? 1 : rawP;
    if (Math.abs(p - blk.lastRendered) < 0.0008) return;
    blk.lastRendered = p;

    const words = blk.words;
    const n = words.length;

    if (p <= 0.002) {
      for (let i = 0; i < n; i++) {
        const el = words[i].el;
        el.style.opacity = "0";
        el.style.clipPath = isRtl ? "inset(-28% 0% -28% 100%)" : "inset(-28% 100% -28% 0%)";
      }
      blk.caret.classList.add("idle");
      return;
    }

    if (p >= 0.998) {
      for (let i = 0; i < n; i++) {
        const el = words[i].el;
        el.style.opacity = "1";
        el.style.clipPath = "none";
      }
      blk.caret.classList.add("idle");
      return;
    }

    let activeWordEl = words[0].el;
    for (let i = 0; i < n; i++) {
      const w = words[i];
      const el = w.el;
      if (p <= w.start) {
        el.style.opacity = "0";
        el.style.clipPath = isRtl ? "inset(-28% 0% -28% 100%)" : "inset(-28% 100% -28% 0%)";
      } else if (p >= w.end) {
        el.style.opacity = "1";
        el.style.clipPath = "none";
        activeWordEl = el;
      } else {
        const local = (p - w.start) / Math.max(0.0001, w.end - w.start);
        const clip = ((1 - local) * 100).toFixed(1);
        el.style.opacity = (0.38 + 0.62 * Math.min(1, local * 2.1)).toFixed(3);
        el.style.clipPath = isRtl ? `inset(-28% 0% -28% ${clip}%)` : `inset(-28% ${clip}% -28% 0%)`;
        activeWordEl = el;
      }
    }

    if (activeWordEl && activeWordEl.parentNode) {
      if (activeWordEl.nextSibling !== blk.caret) {
        activeWordEl.after(blk.caret);
      }
      blk.caret.classList.remove("idle");
    }
  }

  const heroBlocks = [];
  document.querySelectorAll("[data-hero-write]").forEach((el, idx) => {
    const blk = prepareTypeBlock(el);
    if (!blk) return;
    applyWriteProgress(blk, 0);
    const delay = parseInt(el.getAttribute("data-hero-delay") || String(idx === 0 ? 120 : 920), 10);
    const duration = idx === 0 ? 1480 : 1720;
    heroBlocks.push({ blk, delay, duration });
  });

  if (heroBlocks.length) {
    const t0 = performance.now();
    function tickHero(now) {
      let active = false;
      for (let i = 0; i < heroBlocks.length; i++) {
        const h = heroBlocks[i];
        const elapsed = now - t0 - h.delay;
        if (elapsed < 0) {
          active = true;
          continue;
        }
        const prog = Math.min(1, elapsed / h.duration);
        const eased = 1 - Math.pow(1 - prog, 1.65);
        applyWriteProgress(h.blk, eased);
        if (prog < 1) active = true;
      }
      if (active) requestAnimationFrame(tickHero);
    }
    requestAnimationFrame(tickHero);
  }

  const scrollBlocks = [];
  document.querySelectorAll("[data-scroll-write]").forEach(el => {
    const blk = prepareTypeBlock(el);
    if (!blk) return;
    applyWriteProgress(blk, 0);
    scrollBlocks.push(blk);
  });

  let rafId = 0;
  let lastTickTs = 0;
  function tickScrollWrite(nowTs) {
    rafId = 0;
    const now = typeof nowTs === "number" ? nowTs : performance.now();
    const dt = lastTickTs ? Math.min(48, Math.max(8, now - lastTickTs)) : 16.67;
    lastTickTs = now;
    const dtScale = dt / 16.67;

    const vh = window.innerHeight || document.documentElement.clientHeight || 800;
    let keepRunning = false;

    for (let i = 0; i < scrollBlocks.length; i++) {
      const blk = scrollBlocks[i];
      const rect = blk.el.getBoundingClientRect();

      if (rect.top >= vh * 0.99) {
        blk.target = 0;
        blk.current = 0;
        applyWriteProgress(blk, 0);
        continue;
      }

      blk.target = 1;

      if (blk.current < 0.999) {
        keepRunning = true;
        const baseStep = Math.min(0.022, Math.max(0.011, 0.58 / Math.max(14, blk.totalWeight || 24)));
        const boost = rect.top < vh * 0.38 ? 1.55 : 1;
        blk.current = Math.min(1, blk.current + baseStep * boost * dtScale);
      } else {
        blk.current = 1;
      }

      applyWriteProgress(blk, blk.current);
    }

    if (keepRunning) {
      rafId = requestAnimationFrame(tickScrollWrite);
    } else {
      lastTickTs = 0;
    }
  }

  function scheduleScrollWrite() {
    if (!rafId) {
      rafId = requestAnimationFrame(tickScrollWrite);
    }
  }

  if (scrollBlocks.length) {
    scheduleScrollWrite();
    window.addEventListener("scroll", scheduleScrollWrite, { passive: true });
    window.addEventListener("resize", scheduleScrollWrite, { passive: true });
  }

  const sideItems = document.querySelectorAll("[data-side]");
  if ("IntersectionObserver" in window && sideItems.length) {
    const sideObserver = new IntersectionObserver(entries => {
      const vh = window.innerHeight || 800;
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("side-in");
        } else if (entry.boundingClientRect.top > vh * 1.03) {
          entry.target.classList.remove("side-in");
        }
      });
    }, {
      threshold: 0.08,
      rootMargin: "0px 0px -5% 0px"
    });
    sideItems.forEach(el => {
      if (el.closest(".hero-stage")) {
        requestAnimationFrame(() => el.classList.add("side-in"));
      } else {
        sideObserver.observe(el);
      }
    });
  } else {
    sideItems.forEach(el => el.classList.add("side-in"));
  }

  const revealItems = document.querySelectorAll(".reveal-up");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("in-view");
        } else if (entry.boundingClientRect.top > (window.innerHeight || 800) * 1.03) {
          entry.target.classList.remove("in-view");
        }
      });
    }, {
      threshold: 0.08
    });
    revealItems.forEach(el => io.observe(el));
  } else {
    revealItems.forEach(el => el.classList.add("in-view"));
  }

  const parallaxNodes = document.querySelectorAll("[data-parallax]");
  if (parallaxNodes.length && window.matchMedia && window.matchMedia("(pointer: fine)").matches) {
    window.addEventListener("mousemove", e => {
      const rx = (e.clientX / window.innerWidth - 0.5) * 2;
      const ry = (e.clientY / window.innerHeight - 0.5) * 2;
      parallaxNodes.forEach(node => {
        const factor = parseFloat(node.getAttribute("data-parallax") || "10");
        const px = `${(rx * factor).toFixed(1)}px`;
        const py = `${(ry * factor).toFixed(1)}px`;
        if (node.hasAttribute("data-side")) {
          node.style.setProperty("--px", px);
          node.style.setProperty("--py", py);
        } else {
          node.style.translate = `${px} ${py}`;
        }
      });
    }, { passive: true });
  }

  document.querySelectorAll("img[data-focus]").forEach(img => {
    const focus = String(img.getAttribute("data-focus") || "").trim();
    if (focus) img.style.setProperty("object-position", focus);
  });

  const deck = document.querySelector("[data-craft-deck]");
  const craftCards = Array.prototype.slice.call(document.querySelectorAll("[data-craft-card]"));
  const craftBar = document.querySelector("[data-craft-bar]");
  if (deck && craftCards.length) {
    let craftIndex = 0;
    let craftTimer = null;
    let craftPaused = false;

    function paintCraftBar(index) {
      if (!craftBar) return;
      const step = 100 / craftCards.length;
      craftBar.style.width = step + "%";
      const dir = isRtl ? -1 : 1;
      craftBar.style.translate = dir * index * 100 + "% 0";
    }

    function setCraft(index, fromUser) {
      const total = craftCards.length;
      craftIndex = ((index % total) + total) % total;
      craftCards.forEach((card, i) => {
        card.classList.toggle("is-active", i === craftIndex);
        card.setAttribute("aria-current", i === craftIndex ? "true" : "false");
      });
      paintCraftBar(craftIndex);
      if (fromUser) scheduleScrollWrite();
    }

    function startCraftAuto() {
      if (craftTimer || craftPaused) return;
      craftTimer = window.setInterval(() => {
        if (document.hidden) return;
        setCraft(craftIndex + 1, false);
      }, 5200);
    }

    function stopCraftAuto() {
      if (!craftTimer) return;
      window.clearInterval(craftTimer);
      craftTimer = null;
    }

    setCraft(0, false);

    craftCards.forEach((card, i) => {
      card.addEventListener("mouseenter", () => {
        craftPaused = true;
        stopCraftAuto();
        setCraft(i, true);
      });
      card.addEventListener("focusin", () => {
        craftPaused = true;
        stopCraftAuto();
        setCraft(i, true);
      });
      card.addEventListener("click", () => setCraft(i, true));
    });

    deck.addEventListener("mouseleave", () => {
      craftPaused = false;
      startCraftAuto();
    });

    if ("IntersectionObserver" in window) {
      const deckObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            craftCards.forEach((card, i) => {
              card.style.setProperty("--deck-delay", i * 110 + "ms");
              card.classList.add("deck-in");
            });
            startCraftAuto();
          } else if (entry.boundingClientRect.top > (window.innerHeight || 800) * 1.03) {
            craftCards.forEach(card => card.classList.remove("deck-in"));
            stopCraftAuto();
          } else {
            stopCraftAuto();
          }
        });
      }, { threshold: 0.14 });
      deckObserver.observe(deck);
    } else {
      craftCards.forEach(card => card.classList.add("deck-in"));
    }
  }

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
      setTimeout(scheduleScrollWrite, 180);
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
  if (mobileModal) {
    mobileModal.addEventListener("click", e => {
      if (e.target === mobileModal) mobileModal.classList.remove("open");
    });
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
          document.getElementById("repair-lab")?.scrollIntoView({ behavior: "smooth", block: "center" });
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
