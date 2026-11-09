(() => {
  const CFG = window.ASLY_CFG || {};
  const BASE = CFG.baseUrl || "";
  const IS_ADMIN = !!CFG.isAdmin;
  const EN = !!CFG.isEn;
  const tr = (ar, en) => EN ? en : ar;
  const apiUrl = path => `${BASE}/${path.replace(/^\/+/, "")}`;
  const fmtMoney = n => `${Number(n || 0).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })} ${EN ? "EGP" : "ج.م"}`;
  const esc = str => {
    const d = document.createElement("div");
    d.textContent = str == null ? "" : String(str);
    return d.innerHTML;
  };
  const normalizeWa = phone => {
    let d = String(phone || "").replace(/\D+/g, "");
    if (!d) return "";
    if (d.startsWith("00")) d = d.slice(2);
    if (d.startsWith("01") && d.length === 11) return "2" + d;
    return d;
  };
  const INLINE_FALLBACK_SVG = "data:image/svg+xml;charset=UTF-8," + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="240" height="180" viewBox="0 0 240 180"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#effafa"/><stop offset="100%" stop-color="#fffbf0"/></linearGradient></defs><rect width="240" height="180" rx="18" fill="url(#g)"/><rect x="8" y="8" width="224" height="164" rx="14" fill="none" stroke="#14b4b8" stroke-opacity="0.22" stroke-width="1.5"/><text x="120" y="92" text-anchor="middle" font-family="Arial,sans-serif" font-weight="900" font-size="26" fill="#0d9499">ASLY</text><text x="120" y="116" text-anchor="middle" font-family="Arial,sans-serif" font-weight="700" font-size="11" fill="#c58b24" letter-spacing="2">TECH STORE</text></svg>');
  const fallbackImg = () => CFG.store?.logoCardUrl || INLINE_FALLBACK_SVG;
  window.aslyImgErr = el => {
    if (!el) return;
    el.onerror = null;
    el.src = INLINE_FALLBACK_SVG;
  };
  function showToast(msg, isError = false) {
    const wrap = document.getElementById("toastContainer");
    if (!wrap) return;
    const t = document.createElement("div");
    t.className = "toast" + (isError ? " error" : "");
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 4200);
  }
  async function apiFetch(path, options = {}) {
    const headers = Object.assign({
      "X-CSRF-Token": CFG.csrfToken
    }, options.headers || {});
    if (options.json) {
      headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(options.json);
      delete options.json;
    }
    const res = await fetch(apiUrl(path), Object.assign({}, options, {
      headers: headers
    }));
    const data = await res.json();
    if (!data.ok) {
      throw new Error(data.error || tr("حدث خطأ أثناء تنفيذ العملية", "An error occurred"));
    }
    return data;
  }
  function playScanBeep() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext);
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(880, now);
      osc.frequency.setValueAtTime(1174, now + .07);
      gain.gain.setValueAtTime(.1, now);
      gain.gain.exponentialRampToValueAtTime(.01, now + .18);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + .18);
    } catch (_) {}
  }
  function playScanErrorTone() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext);
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = "triangle";
      osc.frequency.setValueAtTime(240, now);
      osc.frequency.setValueAtTime(180, now + .11);
      gain.gain.setValueAtTime(.12, now);
      gain.gain.exponentialRampToValueAtTime(.01, now + .24);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + .24);
    } catch (_) {}
  }
  const AR_KEYMAP_MULTI = [ [ "لا", "b" ], [ "لآ", "B" ], [ "لأ", "G" ], [ "لإ", "T" ] ];
  const AR_KEYMAP_SINGLE = {
    "ض": "q", "ص": "w", "ث": "e", "ق": "r", "ف": "t", "غ": "y", "ع": "u", "ه": "i", "خ": "o", "ح": "p",
    "ج": "[", "د": "]", "ش": "a", "س": "s", "ي": "d", "ب": "f", "ل": "g", "ا": "h", "أ": "H", "ت": "j",
    "ن": "k", "م": "l", "ك": ";", "ط": "'", "ئ": "z", "ء": "x", "ؤ": "c", "ر": "v", "ى": "n", "ة": "m",
    "و": ",", "ز": ".", "ظ": "/", "ذ": "`",
    "٠": "0", "١": "1", "٢": "2", "٣": "3", "٤": "4", "٥": "5", "٦": "6", "٧": "7", "٨": "8", "٩": "9"
  };
  function convertArabicWedgeToAscii(str) {
    let s = String(str || "");
    for (const [ from, to ] of AR_KEYMAP_MULTI) {
      s = s.split(from).join(to);
    }
    let out = "";
    for (const ch of s) {
      out += AR_KEYMAP_SINGLE[ch] !== undefined ? AR_KEYMAP_SINGLE[ch] : ch;
    }
    return out;
  }
  function normalizeScannedCode(raw, forceConvertArabic = false) {
    let val = String(raw || "").replace(/[\u0000-\u001F\u007F]+/g, "").trim();
    if (!val) return "";
    if (forceConvertArabic || (!val.includes(" ") && /[\u0600-\u06FF]/.test(val) && /[-0-9/:?=]/.test(val))) {
      val = convertArabicWedgeToAscii(val);
    }
    if (/code=/i.test(val)) {
      const m = val.match(/[?&]code=([^&#\s]+)/i);
      if (m && m[1]) {
        try {
          return decodeURIComponent(m[1]).trim();
        } catch (_) {
          return m[1].trim();
        }
      }
    }
    return val;
  }
  function getActiveTabName() {
    const activePanel = document.querySelector(".tab-panel.active");
    return activePanel ? activePanel.id.replace(/^tab-/, "") : (IS_ADMIN ? "overview" : "quick-scanner");
  }
  function pulseScannerPill() {
    const pill = document.getElementById("globalScannerPill");
    if (!pill) return;
    pill.classList.remove("pulse");
    void pill.offsetWidth;
    pill.classList.add("pulse");
    setTimeout(() => pill.classList.remove("pulse"), 900);
  }
  let hudTimer = null;
  function showGlobalScanHud(html, variant = "hud-success", durationMs = 6000) {
    const hud = document.getElementById("globalScanHud");
    if (!hud) return;
    if (hudTimer) clearTimeout(hudTimer);
    hud.className = `global-scan-hud visible ${variant}`;
    hud.innerHTML = html;
    hud.querySelector("[data-close-hud]")?.addEventListener("click", () => {
      hud.classList.remove("visible");
    });
    if (durationMs > 0) {
      hudTimer = setTimeout(() => hud.classList.remove("visible"), durationMs);
    }
  }
  function renderQrInto(el, text, cellSize = 4) {
    if (!el) return;
    el.innerHTML = "";
    if (!text || typeof window.qrcode !== "function") return;
    try {
      const qr = window.qrcode(0, "M");
      qr.addData(String(text));
      qr.make();
      el.innerHTML = qr.createSvgTag({
        cellSize: cellSize,
        margin: 2,
        scalable: false
      });
    } catch (_) {
      el.textContent = text;
    }
  }
  function renderBarcodeInto(svgEl, code) {
    if (!svgEl) return;
    svgEl.innerHTML = "";
    if (!code || typeof window.JsBarcode !== "function") return;
    try {
      window.JsBarcode(svgEl, String(code), {
        format: "CODE128",
        lineColor: "#0d9499",
        width: 1.7,
        height: 40,
        displayValue: false,
        margin: 2
      });
    } catch (_) {}
  }
  function printHtmlSnippet(title, bodyHtml) {
    const win = window.open("", "_blank", "width=780,height=700");
    if (!win) {
      showToast(tr("يرجى السماح بالنوافذ المنبثقة للطباعة", "Please allow pop-ups to print"), true);
      return;
    }
    win.document.write(`<!DOCTYPE html><html dir="${EN ? "ltr" : "rtl"}" lang="${EN ? "en" : "ar"}"><head><meta charset="UTF-8"><title>${esc(title)}</title>\n      <style>\n        body { font-family: Tahoma, Arial, sans-serif; direction: ${EN ? "ltr" : "rtl"}; margin: 16px; color: #1e293b; background: #ffffff; }\n        @media print { body { margin: 4mm; } }\n      </style>\n    </head><body>${bodyHtml}<script>window.onload=function(){setTimeout(function(){window.print();},250);};<\/script></body></html>`);
    win.document.close();
  }
  window.openModal = id => document.getElementById(id)?.classList.add("open");
  window.closeModal = id => document.getElementById(id)?.classList.remove("open");
  const staffAllowedTabs = new Set([ "quick-scanner", "pos", "maintenance" ]);
  const tabTitles = {
    overview: tr("لوحة المبيعات اليومية والأرباح الصافية", "Daily Sales & Net Profit Overview"),
    "quick-scanner": tr("كاشف السعر السريع", "Quick Price Scanner"),
    pos: tr("نقطة البيع وإصدار الفواتير الرسمية", "Point of Sale & Official Invoicing"),
    "add-product": tr("إضافة منتج جديد (مسح الكود أو توليد QR تلقائي)", "Add Product (Scan Barcode or Generate Auto QR)"),
    inventory: tr("إدارة المخزون والأجهزة واستيكرات الأكواد", "Inventory Catalog & Printable Stickers"),
    maintenance: tr("إدارة الصيانة التفاعلية بالـ QR والمراحل والإيميل", "Interactive QR Maintenance, Stages & Email"),
    staff: tr("إدارة الموظفين وصلاحيات النظام", "Staff Accounts & Role Permissions"),
    settings: tr("الهوية البصرية وقنوات التواصل وإعدادات البريد", "Brand Identity, Contact Channels & SMTP")
  };
  window.switchTab = async tabName => {
    if (!IS_ADMIN && !staffAllowedTabs.has(tabName)) {
      tabName = "quick-scanner";
    }
    await stopAllCameras();
    document.querySelectorAll(".side-btn").forEach(b => {
      b.classList.toggle("active", b.dataset.tab === tabName);
    });
    document.querySelectorAll(".tab-panel").forEach(p => {
      p.classList.toggle("active", p.id === `tab-${tabName}`);
    });
    const titleEl = document.getElementById("activePageTitle");
    if (titleEl && tabTitles[tabName]) titleEl.textContent = tabTitles[tabName];
    document.getElementById("appSidebar")?.classList.remove("open");
    if (tabName === "overview" && IS_ADMIN) loadOverviewReport();
    if (tabName === "pos") loadPosCatalog();
    if (tabName === "inventory" && IS_ADMIN) loadInventoryTable();
    if (tabName === "maintenance") loadMaintenanceList();
    if (tabName === "staff" && IS_ADMIN) loadUsersList();
    if (tabName === "settings" && IS_ADMIN) loadSettingsData();
    if (tabName === "add-product") {
      if (window.AslyAddFlow) {
        window.AslyAddFlow.clear();
        window.AslyAddFlow.stage("scan");
      }
    }
  };
  document.querySelectorAll(".side-btn[data-tab]").forEach(btn => {
    btn.addEventListener("click", () => window.switchTab(btn.dataset.tab));
  });
  document.getElementById("btnMobileSidebar")?.addEventListener("click", () => {
    document.getElementById("appSidebar")?.classList.toggle("open");
  });
  function camToast(mountId, message, variant, holdMs) {
    if (window.AslyTurboScanner && typeof window.AslyTurboScanner.toast === "function") {
      return window.AslyTurboScanner.toast(mountId, message, variant, holdMs);
    }
    return false;
  }
  async function startCameraScanner(mountId, onScanSuccess, extraOpts = {}) {
    await stopCameraScanner(mountId);
    if (window.AslyTurboScanner) {
      return await window.AslyTurboScanner.start(mountId, decodedText => {
        if (mountId !== "posCameraReader") playScanBeep();
        onScanSuccess(decodedText.trim());
      }, Object.assign({ isEn: EN }, extraOpts));
    }
    return false;
  }
  async function stopCameraScanner(mountId) {
    if (window.AslyTurboScanner) {
      await window.AslyTurboScanner.stop(mountId);
    }
  }
  function resetScanButtons() {
    const pairs = [
      ["btnStartQuickCam", "btnStopQuickCam"],
      ["btnStartAddCam", "btnStopAddCam"]
    ];
    pairs.forEach(([startId, stopId]) => {
      const bStart = document.getElementById(startId);
      const bStop = document.getElementById(stopId);
      if (bStart) bStart.style.display = "inline-flex";
      if (bStop) bStop.style.display = "none";
    });
  }
  async function stopAllCameras() {
    if (window.AslyTurboScanner) {
      await window.AslyTurboScanner.stopAll();
    }
    const bStopQuick = document.getElementById("btnStopQuickCam");
    const bStartQuick = document.getElementById("btnStartQuickCam");
    if (bStopQuick) bStopQuick.style.display = "none";
    if (bStartQuick) bStartQuick.style.display = "inline-flex";
    const bStopAdd = document.getElementById("btnStopAddCam");
    const bStartAdd = document.getElementById("btnStartAddCam");
    if (bStopAdd) bStopAdd.style.display = "none";
    if (bStartAdd) bStartAdd.style.display = "inline-flex";
    const posCamWrap = document.getElementById("posCameraWrap");
    if (posCamWrap) posCamWrap.style.display = "none";
  }
  async function loadOverviewReport() {
    if (!IS_ADMIN) return;
    const dFrom = document.getElementById("repDateFrom")?.value || "";
    const dTo = document.getElementById("repDateTo")?.value || "";
    try {
      const data = await apiFetch(`api/pos.php?action=list&date_from=${encodeURIComponent(dFrom)}&date_to=${encodeURIComponent(dTo)}`);
      const s = data.summary;
      const kpiWrap = document.getElementById("overviewKpis");
      if (kpiWrap) {
        if (IS_ADMIN) {
          const cap = CFG.inventoryStats || {};
          kpiWrap.innerHTML = `\n            <div class="kpi-card hero-kpi">\n              <div class="kpi-label">${tr("إجمالي النقدية الداخلة (مبيعات + صيانة)", "Total Cash Inflow (Sales + Repairs)")}</div>\n              <div class="kpi-value">${fmtMoney(s.combined_revenue)}</div>\n              <div class="kpi-sub">${s.invoices_count} ${tr("فاتورة بيع", "invoices")} + ${s.maintenance_delivered_count} ${tr("صيانة مسلمة", "repairs")}</div>\n            </div>\n            <div class="kpi-card" style="border-color:var(--db-gold-500);background:var(--db-gold-50);">\n              <div class="kpi-label" style="color:var(--db-gold-600);">${tr("صافي الربح الحقيقي للفترة", "True Net Profit")}</div>\n              <div class="kpi-value" style="color:#059669;">${fmtMoney(s.combined_net_profit)}</div>\n              <div class="kpi-sub">${tr("المبيعات:", "Sales:")} ${fmtMoney(s.sales_profit)} | ${tr("الصيانة:", "Repairs:")} ${fmtMoney(s.maintenance_profit)}</div>\n            </div>\n            <div class="kpi-card">\n              <div class="kpi-label">${tr("إيراد فواتير المبيعات", "Sales Revenue")}</div>\n              <div class="kpi-value">${fmtMoney(s.sales_revenue)}</div>\n              <div class="kpi-sub">${tr("القطع المباعة:", "Units sold:")} ${s.items_sold_count}</div>\n            </div>\n            <div class="kpi-card">\n              <div class="kpi-label">${tr("إيراد الصيانة المسلمة", "Delivered Repair Revenue")}</div>\n              <div class="kpi-value">${fmtMoney(s.maintenance_revenue)}</div>\n              <div class="kpi-sub">${s.maintenance_delivered_count} ${tr("تذكرة ممسوحة البيانات", "wiped tickets")}</div>\n            </div>\n            <div class="kpi-card">\n              <div class="kpi-label">${tr("رأس مال البضاعة بالمخزن", "Current Stock Capital")}</div>\n              <div class="kpi-value">${fmtMoney(cap.total_capital || 0)}</div>\n              <div class="kpi-sub">${cap.total_units || 0} ${tr("قطعة", "units")} (${cap.total_skus || 0} SKU)</div>\n            </div>\n          `;
        } else {
          kpiWrap.innerHTML = `\n            <div class="kpi-card hero-kpi">\n              <div class="kpi-label">${tr("إجمالي الفواتير الصادرة بالفترة", "Total Issued Invoices")}</div>\n              <div class="kpi-value">${fmtMoney(s.sales_revenue)}</div>\n              <div class="kpi-sub">${s.invoices_count} ${tr("فاتورة", "invoices")} • ${s.items_sold_count} ${tr("قطعة مباعة", "items")}</div>\n            </div>\n            <div class="kpi-card">\n              <div class="kpi-label">${tr("كاشف السعر السريع", "Quick Price Scanner")}</div>\n              <div class="kpi-value" style="font-size:1.15rem;">${tr("فحص بالكاميرا", "Camera Lookup")}</div>\n              <div class="kpi-sub"><button class="btn btn-gold btn-sm" onclick="switchTab('quick-scanner')">${tr("فتح الاسكانر الآن", "Open Scanner")}</button></div>\n            </div>\n            <div class="kpi-card">\n              <div class="kpi-label">${tr("نقطة البيع والفواتير", "POS & Invoicing")}</div>\n              <div class="kpi-value" style="font-size:1.15rem;">${tr("بيع وإصدار فاتورة", "Issue Sale Invoice")}</div>\n              <div class="kpi-sub"><button class="btn btn-teal btn-sm" onclick="switchTab('pos')">${tr("فتح نقطة البيع", "Open POS")}</button></div>\n            </div>\n          `;
        }
      }
      const invBody = document.getElementById("dailyInvoicesBody");
      if (invBody) {
        if (!data.invoices.length) {
          invBody.innerHTML = `<tr><td colspan="${IS_ADMIN ? 9 : 7}" style="text-align:center;padding:26px;color:var(--db-muted);">${tr("لا توجد فواتير مبيعات مسجلة في هذه الفترة.", "No sales invoices recorded in this period.")}</td></tr>`;
        } else {
          invBody.innerHTML = data.invoices.map(inv => {
            const itemsSummary = (inv.items || []).map(i => `${esc(i.product_name)} (×${i.quantity})`).join(" + ");
            return `\n              <tr>\n                <td style="direction:ltr;font-weight:800;">${esc(inv.invoice_number)}</td>\n                <td style="direction:ltr;font-size:0.8rem;">${esc(inv.created_at)}</td>\n                <td>\n                  <strong>${esc(inv.customer_name)}</strong>\n                  ${inv.customer_phone ? `<div style="font-size:0.76rem;color:var(--db-muted);direction:ltr;">${esc(inv.customer_phone)}</div>` : ""}\n                </td>\n                <td style="max-width:260px;font-size:0.84rem;">${itemsSummary}</td>\n                <td>${esc(inv.sold_by_name)}</td>\n                <td style="font-weight:900;color:var(--db-teal-800);">${fmtMoney(inv.total_amount)}</td>\n                ${IS_ADMIN ? `\n                  <td style="color:var(--db-muted);">${fmtMoney(inv.total_cost)}</td>\n                  <td style="font-weight:800;color:#059669;">+${fmtMoney(inv.total_profit)}</td>\n                ` : ""}\n                <td>\n                  <button type="button" class="btn btn-teal btn-sm" onclick="openSavedInvoice(${inv.id})">${tr("عرض / صورة / واتساب", "View / PNG / WhatsApp")}</button>\n                </td>\n              </tr>\n            `;
          }).join("");
        }
      }
      if (IS_ADMIN) {
        const mRevBody = document.getElementById("dailyMaintRevBody");
        if (mRevBody) {
          if (!data.maintenance_revenues.length) {
            mRevBody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--db-muted);">${tr("لا توجد أجهزة صيانة مسلمة في هذه الفترة.", "No delivered repair tickets in this period.")}</td></tr>`;
          } else {
            mRevBody.innerHTML = data.maintenance_revenues.map(mr => `\n              <tr>\n                <td style="direction:ltr;font-weight:800;">${esc(mr.ticket_code_ref)}</td>\n                <td style="direction:ltr;">${esc(mr.revenue_date)}</td>\n                <td>${esc(mr.device_category)}</td>\n                <td style="font-weight:900;color:var(--db-teal-800);">${fmtMoney(mr.amount_paid)}</td>\n                <td>${fmtMoney(mr.parts_cost)}</td>\n                <td style="font-weight:800;color:#059669;">+${fmtMoney(mr.net_profit)}</td>\n                <td>${esc(mr.payment_method)}</td>\n                <td>${esc(mr.delivered_by_name || "—")}</td>\n              </tr>\n            `).join("");
          }
        }
      }
    } catch (err) {
      showToast(err.message, true);
    }
  }
  document.getElementById("btnLoadReport")?.addEventListener("click", loadOverviewReport);
  document.querySelectorAll(".js-date-preset").forEach(btn => {
    btn.addEventListener("click", () => {
      const now = new Date;
      const iso = d => d.toISOString().slice(0, 10);
      let from = iso(now);
      let to = iso(now);
      if (btn.dataset.preset === "yesterday") {
        const y = new Date(now);
        y.setDate(y.getDate() - 1);
        from = to = iso(y);
      } else if (btn.dataset.preset === "month") {
        from = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-01`;
      }
      document.getElementById("repDateFrom").value = from;
      document.getElementById("repDateTo").value = to;
      loadOverviewReport();
    });
  });
  let lastQuickProduct = null;
  async function lookupQuickPrice(code) {
    if (!code) return { ok: false, message: "" };
    const resWrap = document.getElementById("quickScanResult");
    try {
      const data = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
      if (data.is_maintenance_ticket && data.ticket) {
        resWrap.innerHTML = `\n          <div class="price-reveal-card">\n            <span class="role-pill role-admin" style="margin-bottom:10px;"><i class="fa-solid fa-screwdriver-wrench"></i> ${tr("تذكرة صيانة ممسوحة", "Scanned Repair Ticket")}</span>\n            <h3 style="margin:6px 0;">${esc(data.ticket.device_model)} (${esc(data.ticket.ticket_code)})</h3>\n            <p>${tr("العميل:", "Customer:")} <strong>${esc(data.ticket.customer_name)}</strong></p>\n            <button type="button" class="btn btn-teal" onclick="switchTab('maintenance'); setTimeout(()=>openMaintTicket(${data.ticket.id}), 250);">${tr("فتح ملف الصيانة الآن", "Open Ticket Now")}</button>\n          </div>\n        `;
        return { ok: true, kind: "ticket", label: data.ticket.ticket_code };
      }
      if (!data.found) {
        resWrap.innerHTML = `\n          <div class="card" style="border-color:#fca5a5;text-align:center;padding:34px;">\n            <h3 style="color:#b91c1c;margin:0 0 8px;">${tr("لم يتم العثور على منتج بالكود:", "No product found for code:")} <span style="direction:ltr;">${esc(data.scanned_code)}</span></h3>\n            <p style="color:var(--db-muted);margin:0 0 16px;">${tr("هذا الكود غير مسجل بالمخزن حتى الآن.", "This code is not registered in inventory yet.")}</p>\n            ${IS_ADMIN ? `<button type="button" class="btn btn-teal" onclick="prefillNewProductCode('${esc(data.scanned_code)}')"><i class="fa-solid fa-plus"></i> ${tr("تسجيل كمنتج جديد بهذا الكود", "Register New Product with Code")}</button>` : ""}\n          </div>\n        `;
        return { ok: false, message: tr("الكود غير مسجل بالمخزن", "Code is not registered in inventory") };
      }
      const p = data.product;
      lastQuickProduct = p;
      const img = p.image_path ? apiUrl(p.image_path) : fallbackImg();
      const prodTitle = p.display_name || p.name;
      const prodSpecs = p.display_specs || p.specs || "";
      const decoyBtnHtml = `<i class="fa-solid fa-eye"></i> ${tr("اعرض سعر الشراء", "Show Purchase Price")}`;
      const hideBtnHtml = `<i class="fa-solid fa-eye-slash"></i> ${tr("إخفاء السعر", "Hide Price")}`;
      resWrap.innerHTML = `\n        <div class="price-reveal-card">\n          <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">\n            <img src="${esc(img)}" onerror="aslyImgErr(this)" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:16px;border:1px solid var(--db-border);background:#fff;">\n            <div style="flex:1;min-width:180px;">\n              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:4px;">\n                <span style="padding:2px 10px;border-radius:999px;background:var(--db-teal-50);color:var(--db-teal-800);font-size:0.76rem;font-weight:800;">${esc(p.category_label)}</span>\n                <span style="padding:2px 10px;border-radius:999px;background:var(--db-gold-50);color:var(--db-gold-600);font-size:0.76rem;font-weight:800;">${esc(p.condition_label)}</span>\n                <span style="padding:2px 10px;border-radius:999px;background:${p.quantity > 0 ? "#ecfdf5" : "#fef2f2"};color:${p.quantity > 0 ? "#059669" : "#b91c1c"};font-size:0.76rem;font-weight:800;">\n                  ${tr("المتاح:", "In Stock:")} ${p.quantity}\n                </span>\n              </div>\n              <h3 style="margin:0 0 4px;font-size:1.25rem;color:var(--db-text);">${esc(prodTitle)}</h3>\n              <div style="font-size:0.82rem;color:var(--db-muted);">${esc(prodSpecs)}</div>\n            </div>\n          </div>\n\n          <div class="big-sell-price-box">\n            <div class="lbl">${tr("سعر البيع للعميل", "Customer Selling Price")}</div>\n            <div class="val">${fmtMoney(p.sell_price)}</div>\n          </div>\n\n          <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">\n            <button type="button" class="btn btn-ghost" id="btnShowDecoyCost" style="flex:1;min-width:180px;">\n              ${decoyBtnHtml}\n            </button>\n            ${p.quantity > 0 ? `\n              <button type="button" class="btn btn-teal" id="btnQuickSellNow" style="flex:1;min-width:180px;">\n                <i class="fa-solid fa-cart-plus"></i> ${tr("بيع هذه القطعة وإصدار فاتورة", "Sell Item & Issue Invoice")}\n              </button>\n            ` : ""}\n          </div>\n\n          <div class="decoy-cost-box" id="decoyCostBox">\n            <div style="font-size:0.8rem;color:var(--db-gold-600);font-weight:700;">${tr("سعر التكلفة المسجل بالنظام", "Registered System Floor Rate")}</div>\n            <div style="font-size:1.65rem;font-weight:900;color:var(--db-teal-800);margin-top:2px;">${fmtMoney(p.display_floor_price)}</div>\n          </div>\n        </div>\n      `;
      document.getElementById("btnShowDecoyCost")?.addEventListener("click", ev => {
        const box = document.getElementById("decoyCostBox");
        if (!box) return;
        const open = box.classList.toggle("visible");
        ev.currentTarget.innerHTML = open ? hideBtnHtml : decoyBtnHtml;
      });
      document.getElementById("btnQuickSellNow")?.addEventListener("click", () => {
        if (lastQuickProduct) {
          window.switchTab("pos");
          setTimeout(() => addToPosCart(lastQuickProduct, { viaScan: true }), 100);
        }
      });
      return { ok: true, kind: "product", label: prodTitle };
    } catch (err) {
      showToast(err.message, true);
      return { ok: false, message: err.message };
    }
  }
  window.prefillNewProductCode = code => {
    window.switchTab("add-product");
    const inp = document.getElementById("prodBarcode");
    if (inp) {
      inp.value = code;
      updateLiveStickerPreview();
    }
  };
  async function runQuickScan(code) {
    const clean = (code || "").trim();
    if (!clean) return;
    camToast("quickCameraReader", tr("جاري قراءة الكود...", "Reading code..."), "info", 1400);
    document.getElementById("quickCodeInput").value = clean;
    const outcome = await lookupQuickPrice(clean);
    if (outcome && outcome.ok) {
      stopCameraScanner("quickCameraReader");
      resetScanButtons();
    } else {
      camToast(
        "quickCameraReader",
        outcome && outcome.message
          ? outcome.message
          : tr("الكود غير مسجل — جرّب تاني", "Code not registered — try again"),
        "err",
        3600
      );
    }
  }
  document.getElementById("btnStartQuickCam")?.addEventListener("click", async () => {
    const ok = await startCameraScanner("quickCameraReader", code => { runQuickScan(code); }, {
      onClose: () => resetScanButtons()
    });
    if (ok) {
      document.getElementById("btnStartQuickCam").style.display = "none";
      document.getElementById("btnStopQuickCam").style.display = "inline-flex";
    }
  });
  document.getElementById("btnStopQuickCam")?.addEventListener("click", () => stopAllCameras());
  document.getElementById("quickScanFile")?.addEventListener("change", async e => {
    const file = e.target.files?.[0];
    if (!file || !window.AslyTurboScanner) return;
    try {
      const decoded = await window.AslyTurboScanner.scanFile(file);
      playScanBeep();
      document.getElementById("quickCodeInput").value = decoded;
      lookupQuickPrice(decoded);
    } catch (_) {
      playScanErrorTone();
      showToast(tr("لم يتم التعرف على باركود واضح في الصورة", "Could not detect a clear barcode in the selected image"), true);
    }
    e.target.value = "";
  });
  document.getElementById("quickManualForm")?.addEventListener("submit", e => {
    e.preventDefault();
    lookupQuickPrice(document.getElementById("quickCodeInput").value.trim());
  });
  let posProductsCache = [];
  let posCart = [];
  let activeInvoiceData = null;
  let lastAddedPosProductId = null;
  let posSearchDebounce = null;
  function scrollToPosInvoice() {
    const invCard = document.getElementById("posInvoiceCard");
    if (invCard) {
      invCard.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
  window.scrollToPosInvoice = scrollToPosInvoice;
  function resetPosScanFeedbackIdle() {
    const box = document.getElementById("posScanFeedbackBox");
    const camLog = document.getElementById("posCameraLiveLog");
    const hud = document.getElementById("globalScanHud");
    if (box) {
      box.className = "pos-scan-feedback";
      box.innerHTML = `<div class="pos-scan-idle-row">
        <span class="pos-scan-icon-orb"><i class="fa-solid fa-qrcode"></i></span>
        <div style="flex:1;min-width:180px;">
          <strong>${tr("جاهز للإضافة الفورية بالباركود أو الـ QR", "Instant Barcode & QR Addition Ready")}</strong>
          <div style="font-size:0.79rem;color:var(--db-muted);">${tr("امسح الكود بمسدس الباركود في أي مكان بالصفحة أو بالكاميرا أو اضغط على المنتج — سيظهر تأكيد الإضافة والكمية والإجمالي هنا فوراً.", "Scan anywhere with your barcode gun, use camera scan, or click any product below — confirmation appears right here.")}</div>
        </div>
      </div>`;
    }
    if (camLog) {
      camLog.className = "pos-cam-live-log";
      camLog.innerHTML = `<i class="fa-solid fa-crosshairs" style="color:var(--db-teal-700);"></i>
        <span>${tr("وجّه الكاميرا نحو الباركود أو الـ QR — ستُضاف القطع فوراً للفاتورة مع تنبيه صوتي وعداد مباشر", "Point camera at barcode/QR — items add immediately with sound & live counter")}</span>`;
    }
    if (hud) {
      hud.classList.remove("visible");
    }
  }
  function renderPosScanFeedbackSuccess(product, itemInCart) {
    const box = document.getElementById("posScanFeedbackBox");
    const camLog = document.getElementById("posCameraLiveLog");
    const totalItems = posCart.reduce((a, i) => a + i.quantity, 0);
    const grandTotal = posCart.reduce((a, i) => a + i.unit_sold_price * i.quantity, 0);
    const pName = product.display_name || product.name || itemInCart.name;
    const rawImg = product.image_path || itemInCart.image_path || "";
    const img = rawImg ? apiUrl(rawImg) : fallbackImg();
    const bCode = product.barcode || itemInCart.barcode || "";
    if (box) {
      box.className = "pos-scan-feedback is-added";
      void box.offsetWidth;
      box.innerHTML = `<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:12px;min-width:210px;flex:1;">
          <img src="${esc(img)}" onerror="aslyImgErr(this)" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:12px;border:2px solid #10b981;background:#fff;flex-shrink:0;">
          <div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:2px;">
              <span style="background:#10b981;color:#fff;font-size:0.74rem;font-weight:900;padding:2px 10px;border-radius:999px;"><i class="fa-solid fa-circle-check"></i> ${tr("مضاف حالياً بالفاتورة", "In Active Invoice")}</span>
              <span style="background:var(--db-teal-50);color:var(--db-teal-800);font-size:0.76rem;font-weight:900;padding:2px 9px;border-radius:999px;">${tr("الكمية بالفاتورة:", "In Cart:")} ×${itemInCart.quantity}</span>
            </div>
            <strong style="font-size:0.96rem;color:var(--db-text);display:block;">${esc(pName)}</strong>
            <span style="font-size:0.78rem;color:var(--db-muted);">${tr("سعر الوحدة:", "Unit:")} <strong style="color:var(--db-teal-800);">${fmtMoney(itemInCart.unit_sold_price)}</strong>${bCode ? ` • <span style="direction:ltr;display:inline-block;">${esc(bCode)}</span>` : ""}</span>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <div class="scan-qty-bar" style="margin-top:0;padding:6px 10px;">
            <label for="posScanQty">${tr("الكمية", "Qty")}</label>
            <input type="number" min="1" max="${Number(itemInCart.max_qty) || 99}" step="1" value="1" id="posScanQty" class="scan-qty-input" data-pid="${Number(itemInCart.product_id)}">
            <span class="scan-qty-hint">${tr("Enter تضيف وتكمل المسح", "Enter adds & keeps scanning")}</span>
          </div>
          <div style="text-align:end;margin-inline-end:4px;">
            <div style="font-size:0.74rem;color:var(--db-muted);font-weight:700;">${tr("إجمالي الفاتورة الآن", "Invoice Total")} (${totalItems})</div>
            <strong style="font-size:1.12rem;color:#047857;">${fmtMoney(grandTotal)}</strong>
          </div>
          <button type="button" class="btn btn-teal btn-sm" onclick="scrollToPosInvoice()"><i class="fa-solid fa-file-invoice-dollar"></i> ${tr("عرض الفاتورة", "View Invoice")}</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removePosItemByPid(${Number(itemInCart.product_id)})"><i class="fa-solid fa-trash-can"></i> ${tr("حذف", "Remove")}</button>
        </div>
      </div>`;
    }
    const qtyField = document.getElementById("posScanQty");
    if (qtyField) {
      qtyField.addEventListener("keydown", async ev => {
        if (ev.key !== "Enter") return;
        ev.preventDefault();
        const want = Math.max(1, parseInt(qtyField.value, 10) || 1);
        const pid = Number(qtyField.getAttribute("data-pid"));
        const prod = posProductsCache.find(x => Number(x.id) === pid) || product;
        const added = addToPosCart(prod, { qty: want, viaScan: true });
        if (added) {
          camToast("posCameraReader", tr("تمت الإضافة — جاهز للمسح التالي", "Added — ready for next scan"), "ok", 2200);
          const nextQty = document.getElementById("posScanQty");
          if (nextQty) {
            nextQty.value = "1";
            nextQty.focus();
            nextQty.select();
          }
        }
      });
      window.setTimeout(() => {
        qtyField.focus();
        qtyField.select();
      }, 120);
    }
    if (camLog) {
      camLog.className = "pos-cam-live-log added";
      camLog.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${tr("بالفاتورة الآن:", "In Invoice:")} <strong>${esc(pName)}</strong> (×${itemInCart.quantity}) — ${tr("الإجمالي:", "Total:")} <strong>${fmtMoney(grandTotal)}</strong> (${totalItems} ${tr("قطع", "items")})</span>`;
    }
  }
  function syncPosFeedbackWithCart() {
    if (!posCart.length) {
      lastAddedPosProductId = null;
      resetPosScanFeedbackIdle();
      return;
    }
    let activeItem = posCart.find(i => Number(i.product_id) === Number(lastAddedPosProductId));
    if (!activeItem) {
      activeItem = posCart[posCart.length - 1];
      lastAddedPosProductId = Number(activeItem.product_id);
      document.getElementById("globalScanHud")?.classList.remove("visible");
    }
    const cachedProd = posProductsCache.find(p => Number(p.id) === Number(activeItem.product_id)) || activeItem;
    renderPosScanFeedbackSuccess(cachedProd, activeItem);
  }
  window.removePosItemByPid = pid => {
    const idx = posCart.findIndex(i => Number(i.product_id) === Number(pid));
    if (idx === -1) return;
    posCart.splice(idx, 1);
    renderPosCart(false);
    renderPosGrid();
    syncPosFeedbackWithCart();
  };
  function renderPosScanFeedbackError(titleMsg, subMsg, unknownCode = "") {
    const box = document.getElementById("posScanFeedbackBox");
    const camLog = document.getElementById("posCameraLiveLog");
    if (box) {
      box.className = "pos-scan-feedback is-error";
      box.innerHTML = `<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;">
          <span class="pos-scan-icon-orb" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i></span>
          <div>
            <strong style="color:#b91c1c;display:block;font-size:0.93rem;">${esc(titleMsg)}</strong>
            <div style="font-size:0.8rem;color:#7f1d1d;">${esc(subMsg)}</div>
          </div>
        </div>
        ${IS_ADMIN && unknownCode ? `<button type="button" class="btn btn-teal btn-sm" onclick="prefillNewProductCode('${esc(unknownCode)}')"><i class="fa-solid fa-plus"></i> ${tr("تسجيل كمنتج جديد", "Register Product")}</button>` : ""}
      </div>`;
    }
    if (camLog) {
      camLog.className = "pos-cam-live-log";
      camLog.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> <span style="color:#b91c1c;">${esc(titleMsg)}</span>`;
    }
  }
  async function loadPosCatalog() {
    try {
      const q = document.getElementById("posSearchInput")?.value.trim() || "";
      const cat = document.getElementById("posCatFilter")?.value || "all";
      const data = await apiFetch(`api/products.php?in_stock=1&category=${encodeURIComponent(cat)}&q=${encodeURIComponent(q)}`);
      posProductsCache = data.products || [];
      renderPosGrid();
    } catch (err) {
      showToast(err.message, true);
    }
  }
  function renderPosGrid() {
    const grid = document.getElementById("posProductsGrid");
    if (!grid) return;
    if (!posProductsCache.length) {
      grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--db-muted);">${tr("لا توجد منتجات متاحة بالمخزن مطابقة للبحث.", "No matching in-stock products found.")}</div>`;
      return;
    }
    grid.innerHTML = posProductsCache.map(p => {
      const img = p.image_path ? apiUrl(p.image_path) : fallbackImg();
      const pName = p.display_name || p.name;
      const inCartItem = posCart.find(i => Number(i.product_id) === Number(p.id));
      return `
        <div class="pos-prod-tile ${inCartItem ? "in-cart" : ""}" data-pid="${p.id}">
          ${inCartItem ? `<span class="pos-tile-cart-badge"><i class="fa-solid fa-check"></i> ${tr("بالفاتورة", "In Cart")} ×${inCartItem.quantity}</span>` : ""}
          <img src="${esc(img)}" onerror="aslyImgErr(this)" alt="">
          <strong style="font-size:0.86rem;color:var(--db-text);line-height:1.3;">${esc(pName)}</strong>
          <div style="display:flex;justify-content:space-between;font-size:0.76rem;color:var(--db-muted);">
            <span>${tr("متاح:", "Qty:")} ${p.quantity}</span>
            <span style="direction:ltr;">${esc(p.barcode)}</span>
          </div>
          <div style="margin-top:auto;padding-top:6px;border-top:1px dashed var(--db-border);display:flex;justify-content:space-between;align-items:center;">
            <strong style="color:var(--db-teal-800);font-size:0.9rem;">${fmtMoney(p.sell_price)}</strong>
            <span style="font-size:0.74rem;color:${inCartItem ? "#059669" : "var(--db-gold-600)"};font-weight:800;">+ ${inCartItem ? tr("زيادة قطعة", "Add More") : tr("أضف", "Add")}</span>
          </div>
        </div>
      `;
    }).join("");
    grid.querySelectorAll(".pos-prod-tile").forEach(tile => {
      tile.addEventListener("click", () => {
        const pid = Number(tile.dataset.pid);
        const prod = posProductsCache.find(x => Number(x.id) === pid);
        if (prod) addToPosCart(prod, { viaScan: false });
      });
    });
  }
  function addToPosCart(product, opts = {}) {
    const addQty = Math.max(1, parseInt(opts.qty, 10) || 1);
    const pName = product.display_name || product.name;
    const availQty = Number(product.quantity) || 0;
    if (availQty <= 0) {
      playScanErrorTone();
      const msg = tr(`نفدت الكمية من المخزن للمنتج "${pName}"`, `"${pName}" is currently out of stock`);
      renderPosScanFeedbackError(msg, tr("يرجى مراجعة رصيد المخزن قبل البيع.", "Check inventory stock before selling."));
      showToast(msg, true);
      if (opts.viaScan) {
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;"><div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-circle-xmark" style="color:#dc2626;font-size:1.35rem;"></i><strong>${esc(msg)}</strong></div><button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button></div>`, "hud-error");
      }
      return false;
    }
    let itemRef = posCart.find(i => Number(i.product_id) === Number(product.id));
    if (itemRef) {
      if (itemRef.quantity + addQty > availQty) {
        playScanErrorTone();
        const maxMsg = tr(`وصلت لأقصى كمية متاحة بالمخزن (${availQty}) من "${pName}"`, `Reached max available stock (${availQty}) for "${pName}"`);
        renderPosScanFeedbackError(maxMsg, tr("لا يمكن إضافة قطع أكثر من الرصيد الفعلي بالمخزن.", "Cannot exceed physical inventory stock."));
        showToast(maxMsg, true);
        if (opts.viaScan) {
          showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;"><div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;font-size:1.35rem;"></i><strong>${esc(maxMsg)}</strong></div><button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button></div>`, "hud-error");
        }
        return false;
      }
      itemRef.quantity += addQty;
      itemRef.max_qty = availQty;
    } else {
      itemRef = {
        product_id: Number(product.id),
        name: pName,
        barcode: product.barcode,
        image_path: product.image_path || "",
        max_qty: availQty,
        quantity: addQty,
        sell_price: Number(product.sell_price),
        min_sell_price: Number(product.min_sell_price),
        unit_sold_price: Number(product.sell_price),
        serial_imei: "",
        warranty_text: product.warranty_default || tr("ضمان الفحص والتجربة - أصلي تك ستور", "Certified ASLY Testing Warranty")
      };
      posCart.push(itemRef);
    }
    lastAddedPosProductId = Number(product.id);
    playScanBeep();
    renderPosCart(true);
    renderPosGrid();
    renderPosScanFeedbackSuccess(product, itemRef);
    const totalItems = posCart.reduce((a, i) => a + i.quantity, 0);
    const grandTotal = posCart.reduce((a, i) => a + i.unit_sold_price * i.quantity, 0);
    if (opts.viaScan) {
      showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;">
          <i class="fa-solid fa-circle-check" style="color:#059669;font-size:1.45rem;"></i>
          <div>
            <strong style="color:#065f46;font-size:0.95rem;">${tr("تمت إضافة المنتج للفاتورة:", "Added to Invoice:")} ${esc(pName)} (×${itemRef.quantity})</strong>
            <div style="font-size:0.8rem;color:var(--db-muted);">${tr("سعر الوحدة:", "Unit:")} <strong>${fmtMoney(itemRef.unit_sold_price)}</strong> • ${tr("إجمالي الفاتورة:", "Invoice Total:")} <strong style="color:var(--db-teal-800);">${fmtMoney(grandTotal)}</strong> (${totalItems})</div>
          </div>
        </div>
        <div style="display:flex;gap:6px;align-items:center;">
          <button type="button" class="btn btn-teal btn-sm" onclick="scrollToPosInvoice()"><i class="fa-solid fa-file-invoice-dollar"></i> ${tr("الفاتورة", "Invoice")}</button>
          <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
        </div>
      </div>`, "hud-success", 4800);
    } else {
      showToast(tr(`تمت إضافة "${pName}" للفاتورة (الكمية: ×${itemRef.quantity})`, `Added "${pName}" to invoice (Qty: ×${itemRef.quantity})`));
    }
    return true;
  }
  function renderPosCart(highlightLatest = false) {
    const wrap = document.getElementById("posCartItemsList");
    const badge = document.getElementById("posCartBadge");
    const headerCount = document.getElementById("posCartCountHeader");
    const totalEl = document.getElementById("posTotalDisplay");
    const floatBar = document.getElementById("posFloatingCartBar");
    const floatCount = document.getElementById("posFloatingCount");
    const floatTotal = document.getElementById("posFloatingTotal");
    const totalItems = posCart.reduce((acc, i) => acc + i.quantity, 0);
    if (badge) {
      badge.style.display = totalItems > 0 ? "inline-block" : "none";
      badge.textContent = String(totalItems);
    }
    if (headerCount) {
      headerCount.style.display = totalItems > 0 ? "inline-block" : "none";
      headerCount.textContent = `${totalItems} ${tr("قطع", "items")}`;
    }
    if (!wrap) return;
    if (!posCart.length) {
      wrap.innerHTML = `<div style="text-align:center;padding:28px 16px;background:var(--db-bg);border-radius:14px;border:1px dashed var(--db-border);color:var(--db-muted);font-size:0.88rem;">${tr("السلة فارغة — اضغط على أي منتج أو امسح الكود بمسدس الباركود في أي مكان لإضافته للفاتورة فوراً.", "Cart is empty — click any product or scan its barcode anywhere to add it to the invoice.")}</div>`;
      if (totalEl) totalEl.textContent = fmtMoney(0);
      if (floatBar) floatBar.style.display = "none";
      return;
    }
    let grandTotal = 0;
    wrap.innerHTML = posCart.map((item, idx) => {
      const belowMin = item.unit_sold_price + .001 < item.min_sell_price;
      const sub = item.unit_sold_price * item.quantity;
      grandTotal += sub;
      const isHighlighted = highlightLatest && Number(item.product_id) === Number(lastAddedPosProductId);
      return `
        <div class="pos-cart-item ${isHighlighted ? "just-added" : ""}" data-cart-pid="${item.product_id}">
          <div style="display:flex;justify-content:space-between;align-items:start;gap:8px;margin-bottom:8px;">
            <div>
              <strong style="color:var(--db-text);font-size:0.93rem;">${esc(item.name)} <span style="color:#059669;font-weight:900;">(×${item.quantity})</span></strong>
              <div style="font-size:0.75rem;color:var(--db-muted);">${tr("سعر العرض:", "List:")} ${fmtMoney(item.sell_price)} | <strong style="color:#059669;">${tr("أقل سعر مسموح:", "Min Floor:")} ${fmtMoney(item.min_sell_price)}</strong></div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" data-rm="${idx}">${tr("حذف", "Remove")}</button>
          </div>

          <div style="display:grid;grid-template-columns:95px 1fr;gap:10px;margin-bottom:8px;">
            <div>
              <label style="font-size:0.75rem;font-weight:700;">${tr("الكمية", "Qty")} (${item.max_qty})</label>
              <input type="number" min="1" max="${item.max_qty}" value="${item.quantity}" data-qty="${idx}" class="form-control" style="padding:7px 10px;">
            </div>
            <div>
              <label style="font-size:0.75rem;font-weight:700;">${tr("السعر الفعلي للقطعة *", "Agreed Unit Price *")}</label>
              <input type="number" step="0.01" min="${item.min_sell_price}" value="${item.unit_sold_price}" data-price="${idx}" class="form-control ${belowMin ? "price-error" : ""}" style="padding:7px 10px;font-weight:800;">
            </div>
          </div>

          ${belowMin ? `<div style="color:#dc2626;font-size:0.78rem;font-weight:800;margin-bottom:6px;"><i class="fa-solid fa-ban"></i> ${tr("غير مسموح بالبيع بأقل من الحد الأدنى", "Cannot sell below minimum floor price")} (${fmtMoney(item.min_sell_price)})!</div>` : ""}

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <input type="text" placeholder="${tr("السيريال / IMEI (اختياري)", "Serial / IMEI (optional)")}" value="${esc(item.serial_imei)}" data-sn="${idx}" class="form-control" style="padding:6px 10px;font-size:0.8rem;">
            <input type="text" placeholder="${tr("الضمان بالفاتورة", "Warranty note")}" value="${esc(item.warranty_text)}" data-war="${idx}" class="form-control" style="padding:6px 10px;font-size:0.8rem;">
          </div>
        </div>
      `;
    }).join("");
    if (totalEl) totalEl.textContent = fmtMoney(grandTotal);
    if (floatBar) {
      floatBar.style.display = "flex";
      if (floatCount) floatCount.textContent = String(totalItems);
      if (floatTotal) floatTotal.textContent = fmtMoney(grandTotal);
    }
    wrap.querySelectorAll("[data-rm]").forEach(b => {
      b.addEventListener("click", () => {
        posCart.splice(Number(b.dataset.rm), 1);
        renderPosCart(false);
        renderPosGrid();
        syncPosFeedbackWithCart();
      });
    });
    wrap.querySelectorAll("[data-qty]").forEach(inp => {
      inp.addEventListener("change", () => {
        const it = posCart[Number(inp.dataset.qty)];
        let v = parseInt(inp.value, 10) || 1;
        if (v < 1) v = 1;
        if (v > it.max_qty) {
          v = it.max_qty;
          showToast(tr(`أقصى كمية متاحة هي ${it.max_qty}`, `Max available quantity is ${it.max_qty}`), true);
        }
        it.quantity = v;
        renderPosCart(false);
        renderPosGrid();
        syncPosFeedbackWithCart();
      });
    });
    wrap.querySelectorAll("[data-price]").forEach(inp => {
      inp.addEventListener("input", () => {
        const idx = Number(inp.dataset.price);
        const it = posCart[idx];
        it.unit_sold_price = parseFloat(inp.value) || 0;
        const below = it.unit_sold_price + .001 < it.min_sell_price;
        inp.classList.toggle("price-error", below);
        const totalSum = posCart.reduce((a, c) => a + c.unit_sold_price * c.quantity, 0);
        if (totalEl) totalEl.textContent = fmtMoney(totalSum);
        if (floatTotal) floatTotal.textContent = fmtMoney(totalSum);
        syncPosFeedbackWithCart();
      });
      inp.addEventListener("blur", () => {
        renderPosCart(false);
        syncPosFeedbackWithCart();
      });
    });
    wrap.querySelectorAll("[data-sn]").forEach(inp => {
      inp.addEventListener("input", () => {
        posCart[Number(inp.dataset.sn)].serial_imei = inp.value;
      });
    });
    wrap.querySelectorAll("[data-war]").forEach(inp => {
      inp.addEventListener("input", () => {
        posCart[Number(inp.dataset.war)].warranty_text = inp.value;
      });
    });
  }
  async function lookupAndAddPosCode(rawCode) {
    const cleanCode = normalizeScannedCode(rawCode);
    if (!cleanCode) return;
    pulseScannerPill();
    try {
      const res = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(cleanCode)}`);
      if (res.is_maintenance_ticket && res.ticket) {
        playScanBeep();
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-screwdriver-wrench" style="color:var(--db-teal-700);font-size:1.35rem;"></i>
            <div>
              <strong>${tr("تم مسح تذكرة صيانة:", "Scanned Repair Ticket:")} ${esc(res.ticket.ticket_code)}</strong>
              <div style="font-size:0.8rem;color:var(--db-muted);">${esc(res.ticket.device_model)} — ${esc(res.ticket.customer_name)}</div>
            </div>
          </div>
          <div style="display:flex;gap:6px;">
            <button type="button" class="btn btn-teal btn-sm" onclick="switchTab('maintenance'); setTimeout(()=>openMaintTicket(${res.ticket.id}), 220);"><i class="fa-solid fa-arrow-up-right-from-square"></i> ${tr("فتح التذكرة", "Open Ticket")}</button>
            <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`, "hud-success", 7500);
        return;
      }
      if (res.found && res.product) {
        addToPosCart(res.product, { viaScan: true });
      } else {
        playScanErrorTone();
        const codeTxt = res.scanned_code || cleanCode;
        const errTitle = tr(`الكود "${codeTxt}" غير مسجل بالمخزن`, `Code "${codeTxt}" is not registered in inventory`);
        renderPosScanFeedbackError(errTitle, tr("تأكد من تسجيل المنتج أولاً في قسم إضافة منتج جديد.", "Register this barcode first in Add Product."), codeTxt);
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-exclamation" style="color:#dc2626;font-size:1.35rem;"></i>
            <strong>${esc(errTitle)}</strong>
          </div>
          <div style="display:flex;gap:6px;">
            ${IS_ADMIN ? `<button type="button" class="btn btn-teal btn-sm" onclick="prefillNewProductCode('${esc(codeTxt)}')"><i class="fa-solid fa-plus"></i> ${tr("تسجيل المنتج", "Register")}</button>` : ""}
            <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`, "hud-error", 6500);
      }
    } catch (err) {
      playScanErrorTone();
      showToast(err.message, true);
    }
  }
  document.getElementById("posSearchInput")?.addEventListener("input", () => {
    if (posSearchDebounce) clearTimeout(posSearchDebounce);
    posSearchDebounce = setTimeout(() => loadPosCatalog(), 220);
  });
  document.getElementById("posSearchInput")?.addEventListener("keydown", async e => {
    if (e.key === "Enter") {
      e.preventDefault();
      if (posSearchDebounce) clearTimeout(posSearchDebounce);
      const val = e.target.value.trim();
      if (!val) return;
      e.target.value = "";
      await lookupAndAddPosCode(val);
      loadPosCatalog();
    }
  });
  document.getElementById("posCatFilter")?.addEventListener("change", loadPosCatalog);
  document.getElementById("btnClearCart")?.addEventListener("click", () => {
    posCart = [];
    lastAddedPosProductId = null;
    renderPosCart(false);
    renderPosGrid();
    syncPosFeedbackWithCart();
  });
  document.getElementById("btnPosJumpToCartFromCam")?.addEventListener("click", scrollToPosInvoice);
  document.getElementById("btnFloatingJumpInvoice")?.addEventListener("click", scrollToPosInvoice);
  document.getElementById("btnPosToggleCam")?.addEventListener("click", async () => {
    const wrap = document.getElementById("posCameraWrap");
    if (!wrap) return;
    wrap.style.display = "block";
    await startCameraScanner("posCameraReader", async code => {
      await lookupAndAddPosCode(code);
    }, {
      onClose: () => { wrap.style.display = "none"; }
    });
  });
  document.getElementById("posScanFile")?.addEventListener("change", async e => {
    const file = e.target.files?.[0];
    if (!file || !window.AslyTurboScanner) return;
    try {
      const decoded = await window.AslyTurboScanner.scanFile(file);
      await lookupAndAddPosCode(decoded);
    } catch (_) {
      playScanErrorTone();
      showToast(tr("لم يتم التعرف على باركود واضح في الصورة", "Could not detect a clear barcode in the selected image"), true);
    }
    e.target.value = "";
  });
  document.getElementById("btnPosCloseCam")?.addEventListener("click", () => stopAllCameras());
  document.getElementById("btnCompleteSale")?.addEventListener("click", async () => {
    if (!posCart.length) {
      showToast(tr("أضف منتجاً واحداً على الأقل لإصدار الفاتورة", "Add at least one item to issue an invoice"), true);
      return;
    }
    const invalidItem = posCart.find(i => i.unit_sold_price + .001 < i.min_sell_price);
    if (invalidItem) {
      showToast(tr(`مرفوض! سعر "${invalidItem.name}" أقل من الحد الأدنى المسموح (${fmtMoney(invalidItem.min_sell_price)})`, `Rejected! "${invalidItem.name}" is priced below floor (${fmtMoney(invalidItem.min_sell_price)})`), true);
      renderPosCart();
      return;
    }
    const btn = document.getElementById("btnCompleteSale");
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = tr("جاري حفظ البيعة وإصدار الفاتورة...", "Saving sale & generating invoice...");
    try {
      const res = await apiFetch("api/pos.php", {
        method: "POST",
        json: {
          action: "checkout",
          customer_name: document.getElementById("posCustomerName").value.trim(),
          customer_phone: document.getElementById("posCustomerPhone").value.trim(),
          payment_method: document.getElementById("posPaymentMethod").value,
          notes: document.getElementById("posInvoiceNotes").value.trim(),
          items: posCart
        }
      });
      showToast(res.message);
      posCart = [];
      lastAddedPosProductId = null;
      document.getElementById("posCustomerName").value = "";
      document.getElementById("posCustomerPhone").value = "";
      document.getElementById("posInvoiceNotes").value = "";
      renderPosCart(false);
      syncPosFeedbackWithCart();
      loadPosCatalog();
      openInvoicePreviewModal(res.invoice, res.items);
    } catch (err) {
      showToast(err.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = origText;
    }
  });
  function openInvoicePreviewModal(invoice, items) {
    activeInvoiceData = {
      invoice: invoice,
      items: items
    };
    const st = CFG.store || {};
    const payLabels = EN ? {
      cash: "Cash",
      vodafone_cash: "Vodafone Cash / Wallet",
      instapay: "InstaPay",
      card: "Bank Card"
    } : {
      cash: "نقدي (كاش)",
      vodafone_cash: "فودافون كاش / محفظة",
      instapay: "إنستا باي (InstaPay)",
      card: "بطاقة بنكية"
    };
    const alignStart = EN ? "left" : "right";
    const alignEnd = EN ? "right" : "left";
    const rowsHtml = (items || []).map((it, idx) => `\n      <tr style="background:${idx % 2 === 1 ? "#faf9f6" : "#ffffff"};">\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;">${idx + 1}</td>\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;">\n          <strong style="color:#1e293b;">${esc(it.product_name)}</strong>\n          ${it.product_barcode ? `<div style="font-size:11px;color:#64748b;direction:ltr;text-align:${alignStart};">${esc(it.product_barcode)}</div>` : ""}\n        </td>\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;font-size:12px;">\n          ${it.serial_imei ? `<div>S/N: ${esc(it.serial_imei)}</div>` : ""}\n          <div style="color:#0d9499;font-weight:700;">${esc(it.warranty_text || "")}</div>\n        </td>\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;">${it.quantity}</td>\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;">${fmtMoney(it.unit_sold_price)}</td>\n        <td style="padding:10px 12px;border-bottom:1px solid #eaeef2;font-weight:800;color:#1e293b;">${fmtMoney(it.subtotal)}</td>\n      </tr>\n    `).join("");
    const capture = document.getElementById("modalInvoiceCapture");
    capture.innerHTML = `\n      <div style="background:linear-gradient(135deg,#effafa 0%,#ffffff 55%,#fffbf0 100%);padding:22px 26px;border-bottom:3px solid #e4b453;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">\n        <div>\n          <img src="${esc(st.logoUrl || "")}" alt="ASLY Tech Store" style="height:54px;object-fit:contain;">\n          <div style="font-size:12px;color:#64748b;margin-top:4px;">${esc(st.address || "")}</div>\n        </div>\n        <div style="text-align:${alignEnd};">\n          <div style="font-size:11px;color:#c58b24;font-weight:800;letter-spacing:1px;">${tr("فاتورة مبيعات معتمدة", "OFFICIAL SALES INVOICE")}</div>\n          <div style="font-size:19px;font-weight:900;color:#0d9499;direction:ltr;">${esc(invoice.invoice_number)}</div>\n          <div style="font-size:12px;color:#64748b;direction:ltr;">${esc(invoice.created_at)}</div>\n        </div>\n      </div>\n\n      <div style="padding:22px 26px;color:#1e293b;background:#ffffff;">\n        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;background:#faf9f6;padding:14px 16px;border-radius:14px;border:1px solid #eaeef2;font-size:13px;margin-bottom:16px;">\n          <div><strong>${tr("العميل:", "Customer:")}</strong> ${esc(invoice.customer_name)}</div>\n          <div><strong>${tr("الهاتف:", "Phone:")}</strong> <span style="direction:ltr;display:inline-block;">${esc(invoice.customer_phone || "—")}</span></div>\n          <div><strong>${tr("طريقة الدفع:", "Payment:")}</strong> ${esc(payLabels[invoice.payment_method] || invoice.payment_method)}</div>\n          <div><strong>${tr("مسؤول المبيعات:", "Sales Rep:")}</strong> ${esc(invoice.sold_by_name)}</div>\n        </div>\n\n        <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;">\n          <thead>\n            <tr style="background:#effafa;color:#0d9499;border-bottom:2px solid #d5f2f4;">\n              <th style="padding:10px 12px;text-align:${alignStart};">#</th>\n              <th style="padding:10px 12px;text-align:${alignStart};">${tr("المنتج / البيان", "Product / Description")}</th>\n              <th style="padding:10px 12px;text-align:${alignStart};">${tr("السيريال / الضمان", "Serial / Warranty")}</th>\n              <th style="padding:10px 12px;text-align:${alignStart};">${tr("الكمية", "Qty")}</th>\n              <th style="padding:10px 12px;text-align:${alignStart};">${tr("سعر الوحدة", "Unit Price")}</th>\n              <th style="padding:10px 12px;text-align:${alignStart};">${tr("الإجمالي", "Subtotal")}</th>\n            </tr>\n          </thead>\n          <tbody>${rowsHtml}</tbody>\n        </table>\n\n        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;padding-top:14px;border-top:2px solid #d5f2f4;">\n          <div style="max-width:410px;font-size:12px;color:#64748b;">\n            ${invoice.notes ? `<div style="margin-bottom:4px;color:#1e293b;"><strong>${tr("ملاحظات:", "Notes:")}</strong> ${esc(invoice.notes)}</div>` : ""}\n            <div><i class="fa-solid fa-shield-halved" style="color:#0d9499;margin-inline-end:4px;"></i>${esc(st.footerNote || "")}</div>\n          </div>\n          <div style="background:linear-gradient(135deg,#effafa,#fffbf0);padding:12px 22px;border-radius:14px;border:1.5px solid #e4b453;text-align:center;">\n            <div style="font-size:11px;color:#c58b24;font-weight:800;">${tr("الإجمالي المدفوع", "Total Paid")}</div>\n            <div style="font-size:21px;font-weight:900;color:#0d9499;">${fmtMoney(invoice.total_amount)}</div>\n          </div>\n        </div>\n\n        <div style="margin-top:16px;padding-top:10px;border-top:1px dashed #d5f2f4;display:flex;justify-content:space-between;font-size:12px;color:#64748b;">\n          <span><strong style="color:#1e293b;">${esc(st.nameAr)} | ${esc(st.nameEn)}</strong></span>\n          <span style="direction:ltr;font-weight:800;color:#0d9499;"><i class="fa-solid fa-phone" style="margin-inline-end:4px;"></i>${esc(st.primaryPhone)}${st.secondaryPhone ? " | " + esc(st.secondaryPhone) : ""}</span>\n        </div>\n      </div>\n    `;
    window.openModal("invoiceModal");
  }
  window.openSavedInvoice = async id => {
    try {
      const res = await apiFetch(`api/pos.php?action=invoice&id=${id}`);
      openInvoicePreviewModal(res.invoice, res.items);
    } catch (err) {
      showToast(err.message, true);
    }
  };
  async function generateInvoiceCanvas() {
    const el = document.getElementById("modalInvoiceCapture");
    return await window.html2canvas(el, {
      scale: 2,
      useCORS: true,
      backgroundColor: "#ffffff"
    });
  }
  document.getElementById("btnModalInvoicePng")?.addEventListener("click", async e => {
    if (!activeInvoiceData) return;
    const btn = e.currentTarget;
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = "...";
    try {
      const canvas = await generateInvoiceCanvas();
      const a = document.createElement("a");
      a.download = `${activeInvoiceData.invoice.invoice_number}.png`;
      a.href = canvas.toDataURL("image/png");
      a.click();
      showToast(tr("تم تحميل صورة الفاتورة عالية الدقة بنجاح!", "Invoice PNG image downloaded!"));
    } finally {
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  });
  document.getElementById("btnModalInvoiceWhatsapp")?.addEventListener("click", async e => {
    if (!activeInvoiceData) return;
    const {invoice: invoice, items: items} = activeInvoiceData;
    const btn = e.currentTarget;
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = "...";
    try {
      const canvas = await generateInvoiceCanvas();
      const blob = await new Promise(resolve => canvas.toBlob(resolve, "image/png"));
      const file = new File([ blob ], `${invoice.invoice_number}.png`, {
        type: "image/png"
      });
      const lines = (items || []).map(i => `- ${i.product_name} (x${i.quantity}) — ${fmtMoney(i.subtotal)}`).join("\n");
      const textMsg = EN ? `Hello ${invoice.customer_name},\nThank you for choosing *${CFG.store.nameEn}*\n\n*Invoice:* ${invoice.invoice_number}\n${lines}\n*Total Paid:* ${fmtMoney(invoice.total_amount)}\n\nDigital Invoice (PNG & Print):\n${invoice.public_url}` : `مرحباً أستاذ/ ${invoice.customer_name}\nشكراً لتشريفكم *${CFG.store.nameAr} (${CFG.store.nameEn})*\n\n*فاتورة رقم:* ${invoice.invoice_number}\n${lines}\n*الإجمالي المدفوع:* ${fmtMoney(invoice.total_amount)}\n\nرابط الفاتورة الرقمية للتحميل كصورة أو الطباعة:\n${invoice.public_url}`;
      if (navigator.canShare && navigator.canShare({
        files: [ file ]
      }) && !invoice.customer_phone) {
        await navigator.share({
          title: `Invoice ${invoice.invoice_number}`,
          text: textMsg,
          files: [ file ]
        });
      } else {
        const a = document.createElement("a");
        a.download = `${invoice.invoice_number}.png`;
        a.href = canvas.toDataURL("image/png");
        a.click();
        let waPhone = normalizeWa(invoice.customer_phone || "");
        if (!waPhone) {
          const entered = prompt(tr("أدخل رقم واتساب العميل لإرسال الفاتورة مباشرة:", "Enter customer WhatsApp number:"), "010");
          waPhone = normalizeWa(entered || "");
        }
        const waUrl = waPhone ? `https://wa.me/${waPhone}?text=${encodeURIComponent(textMsg)}` : `https://wa.me/?text=${encodeURIComponent(textMsg)}`;
        window.open(waUrl, "_blank");
        showToast(tr("تم تحميل صورة الفاتورة وفتح محادثة واتساب العميل!", "Invoice PNG saved & WhatsApp chat opened!"));
      }
    } catch (err) {
      showToast(err.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = orig;
    }
  });
  document.getElementById("btnModalInvoicePrint")?.addEventListener("click", () => {
    const el = document.getElementById("modalInvoiceCapture");
    if (el && activeInvoiceData) {
      printHtmlSnippet(`Invoice ${activeInvoiceData.invoice.invoice_number}`, el.outerHTML);
    }
  });
  function syncSlimCodeStrip() {
    const codeEl = document.getElementById("slimCodeText");
    const typeEl = document.getElementById("slimCodeType");
    const code = (document.getElementById("prodBarcode")?.value || "").trim();
    if (codeEl) codeEl.textContent = code || "—";
    if (typeEl) typeEl.textContent = code.toUpperCase().startsWith("ASLY-") ? "QR" : "BARCODE";
  }
  function updateLiveStickerPreview() {
    syncSlimCodeStrip();
    const code = document.getElementById("prodBarcode")?.value.trim() || "";
    const name = document.getElementById("prodName")?.value.trim() || document.getElementById("prodNameEn")?.value.trim() || "ASLY TECH STORE";
    const nameEl = document.getElementById("stickerProdName");
    const txtEl = document.getElementById("stickerCodeText");
    if (nameEl) nameEl.textContent = name;
    if (txtEl) txtEl.textContent = code || "—";
    renderQrInto(document.getElementById("stickerQrMount"), code, 3);
    renderBarcodeInto(document.getElementById("stickerBarcodeSvg"), code);
  }
  function updateLiveProfitHint() {
    const hint = document.getElementById("liveProfitHint");
    if (!hint) return;
    const buy = parseFloat(document.getElementById("prodPurchasePrice")?.value) || 0;
    const sell = parseFloat(document.getElementById("prodSellPrice")?.value) || 0;
    const minSell = parseFloat(document.getElementById("prodMinSellPrice")?.value) || 0;
    if (sell > 0) {
      const pNormal = sell - buy;
      const pMin = minSell - buy;
      hint.innerHTML = tr(`<i class="fa-solid fa-chart-line"></i> ربح البيع الأساسي: <strong>${fmtMoney(pNormal)}</strong> | صافي الربح عند أقل سعر مسموح: <strong>${fmtMoney(pMin)}</strong>`, `<i class="fa-solid fa-chart-line"></i> List Profit: <strong>${fmtMoney(pNormal)}</strong> | Profit at Floor Price: <strong>${fmtMoney(pMin)}</strong>`);
    } else {
      hint.textContent = "";
    }
  }
  if (IS_ADMIN) {
    [ "prodBarcode", "prodName", "prodNameEn" ].forEach(id => {
      document.getElementById(id)?.addEventListener("input", updateLiveStickerPreview);
    });
    [ "prodPurchasePrice", "prodSellPrice", "prodMinSellPrice" ].forEach(id => {
      document.getElementById(id)?.addEventListener("input", updateLiveProfitHint);
    });
    document.getElementById("btnGenerateAutoQr")?.addEventListener("click", async () => {
      try {
        const cat = document.getElementById("prodCategory").value;
        const res = await apiFetch(`api/scanner.php?action=generate_code&category=${encodeURIComponent(cat)}`);
        document.getElementById("prodBarcode").value = res.code;
        document.getElementById("prodCodeType").value = "qr";
        document.getElementById("existingProdAlert").style.display = "none";
        syncSlimCodeStrip();
        updateLiveStickerPreview();
        resetScanQtyBar();
        const nfGen = document.getElementById("newProductFields");
        if (nfGen) nfGen.style.display = "block";
        const idGen = document.getElementById("prodId");
        if (idGen) idGen.value = "0";
        showStage("form");
        showToast(tr(`تم توليد رمز QR جديد: ${res.code}`, `Generated new QR code: ${res.code}`));
      } catch (err) {
        showToast(err.message, true);
      }
    });
    const stageScan = document.getElementById("stageScan");
    const stageForm = document.getElementById("stageForm");
    const stageDone = document.getElementById("stageDone");
    const flowDots = Array.prototype.slice.call(document.querySelectorAll("[data-flow-dot]"));
    const scanQtyBar = document.getElementById("scanQtyBar");
    const scanQtyInput = document.getElementById("scanQtyInput");
    const scanQtyExisting = document.getElementById("scanQtyExisting");
    const scanQtyPreview = document.getElementById("scanQtyPreview");
    const newProductFields = document.getElementById("newProductFields");
    let scannedExistingProduct = null;

    function showStage(name) {
      [["scan", stageScan], ["form", stageForm], ["done", stageDone]].forEach(([key, el]) => {
        if (!el) return;
        el.style.display = key === name ? "block" : "none";
      });
      const order = ["scan", "form", "done"];
      const idx = order.indexOf(name);
      flowDots.forEach(dot => {
        const dotIdx = order.indexOf(dot.getAttribute("data-flow-dot"));
        dot.classList.toggle("is-current", dotIdx === idx);
        dot.classList.toggle("is-done", dotIdx < idx);
      });
      window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function setQtyPreview() {
      const base = Number(scannedExistingProduct?.quantity || 0);
      const add = Math.max(1, parseInt(scanQtyInput?.value, 10) || 1);
      if (scanQtyPreview) scanQtyPreview.textContent = String(base + add);
    }

    document.querySelectorAll("[data-qty-step]").forEach(btn => {
      btn.addEventListener("click", () => {
        if (!scanQtyInput) return;
        const delta = parseInt(btn.getAttribute("data-qty-step"), 10) || 0;
        const next = Math.max(1, (parseInt(scanQtyInput.value, 10) || 1) + delta);
        scanQtyInput.value = String(next);
        setQtyPreview();
      });
    });
    scanQtyInput?.addEventListener("input", setQtyPreview);

    function resetScanQtyBar() {
      scannedExistingProduct = null;
      if (scanQtyBar) scanQtyBar.style.display = "none";
      if (scanQtyInput) scanQtyInput.value = "1";
      if (newProductFields) newProductFields.style.display = "block";
    }

    async function handleAddProductScan(rawCode) {
      const code = normalizeScannedCode(rawCode, true);
      if (!code) return;
      document.getElementById("prodBarcode").value = code;
      document.getElementById("prodCodeType").value = code.toUpperCase().startsWith("ASLY-") ? "qr" : "barcode";
      syncSlimCodeStrip();
      updateLiveStickerPreview();
      pulseScannerPill();
      camToast("addProdCameraReader", tr("جاري فحص الكود...", "Checking code..."), "info", 1200);
      try {
        const check = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
        const alertEl = document.getElementById("existingProdAlert");
        if (check.found && check.product) {
          playScanBeep();
          scannedExistingProduct = check.product;
          stopCameraScanner("addProdCameraReader");
          resetScanButtons();
          if (newProductFields) newProductFields.style.display = "none";
          if (alertEl) {
            alertEl.style.display = "block";
            alertEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <strong>${esc(check.product.name)}</strong>`;
          }
          if (scanQtyBar) scanQtyBar.style.display = "block";
          if (scanQtyExisting) scanQtyExisting.textContent = String(check.product.quantity);
          if (scanQtyInput) scanQtyInput.value = "1";
          setQtyPreview();
          showStage("form");
          window.setTimeout(() => scanQtyInput?.focus(), 260);
        } else {
          playScanBeep();
          stopCameraScanner("addProdCameraReader");
          resetScanButtons();
          resetScanQtyBar();
          if (alertEl) alertEl.style.display = "none";
          document.getElementById("prodId").value = "0";
          const nameField = document.getElementById("prodName");
          if (nameField) nameField.value = "";
          ["prodPurchasePrice", "prodSellPrice", "prodMinSellPrice"].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = "";
          });
          const qtyField = document.getElementById("prodQuantity");
          if (qtyField) qtyField.value = "1";
          const nf = document.getElementById("newProductFields");
          if (nf) nf.style.display = "block";
          showStage("form");
          window.setTimeout(() => document.getElementById("prodName")?.focus(), 280);
          camToast("addProdCameraReader", tr("كود جديد — اكتب البيانات", "New code — fill the details"), "ok", 2200);
        }
      } catch (err) {
        camToast("addProdCameraReader", err.message || tr("تعذر فحص الكود", "Could not check the code"), "err", 3200);
      }
    }

    async function startAddCam() {
      const bStart = document.getElementById("btnStartAddCam");
      const bStop = document.getElementById("btnStopAddCam");
      const ok = await startCameraScanner("addProdCameraReader", async code => {
        await handleAddProductScan(code);
      }, { onClose: () => resetScanButtons() });
      if (ok) {
        if (bStart) bStart.style.display = "none";
        if (bStop) bStop.style.display = "inline-flex";
        camToast("addProdCameraReader", tr("وجّه الكاميرا نحو كود المنتج", "Point the camera at the product code"), "info", 2400);
      }
    }

    document.getElementById("btnStartAddCam")?.addEventListener("click", startAddCam);
    document.getElementById("btnStopAddCam")?.addEventListener("click", () => {
      stopCameraScanner("addProdCameraReader");
      resetScanButtons();
    });

    async function applyQtyTopUp() {
      if (!scannedExistingProduct) return;
      const addQty = Math.max(1, parseInt(scanQtyInput?.value, 10) || 1);
      const targetId = Number(scannedExistingProduct.id);
      const btn = document.getElementById("btnApplyQty");
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${tr("جاري الحفظ...", "Saving...")}`;
      }
      try {
        const fd = new FormData();
        fd.set("action", "save");
        fd.set("id", String(targetId));
        fd.set("barcode", scannedExistingProduct.barcode || "");
        fd.set("code_type", scannedExistingProduct.code_type || "qr");
        fd.set("name", scannedExistingProduct.name || "");
        fd.set("name_en", scannedExistingProduct.name_en || "");
        fd.set("category", scannedExistingProduct.category || "accessories");
        fd.set("condition_status", scannedExistingProduct.condition_status || "new");
        fd.set("brand", scannedExistingProduct.brand || "");
        fd.set("specs", scannedExistingProduct.specs || "");
        fd.set("specs_en", scannedExistingProduct.specs_en || "");
        fd.set("description", scannedExistingProduct.description || "");
        fd.set("purchase_price", String(scannedExistingProduct.purchase_price ?? 0));
        fd.set("sell_price", String(scannedExistingProduct.sell_price ?? 0));
        fd.set("min_sell_price", String(scannedExistingProduct.min_sell_price ?? 0));
        fd.set("low_stock_threshold", String(scannedExistingProduct.low_stock_threshold ?? 2));
        fd.set("warranty_default", scannedExistingProduct.warranty_default || "");
        fd.set("show_on_landing", String(scannedExistingProduct.show_on_landing ?? 0));
        fd.set("is_featured", String(scannedExistingProduct.is_featured ?? 0));
        fd.set("quantity", String(Number(scannedExistingProduct.quantity || 0) + addQty));
        const res = await apiFetch("api/products.php", { method: "POST", body: fd });
        const confirm = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(scannedExistingProduct.barcode || "")}`);
        const finalQty = confirm.found && confirm.product ? confirm.product.quantity : Number(scannedExistingProduct.quantity || 0) + addQty;
        playScanBeep();
        showToast(res.message || tr("تم تحديث الكمية", "Stock updated"));
        showDone(
          tr("تمت زيادة الكمية", "Stock increased"),
          tr(`أضفنا ${addQty} قطعة إلى ${scannedExistingProduct.name}`, `Added ${addQty} unit(s) to ${scannedExistingProduct.name}`),
          `<strong>${esc(scannedExistingProduct.name)}</strong><br><span style="font-size:0.84rem;">${tr("المتاح الآن بالمخزن", "Now in stock")}: <strong>${finalQty}</strong> ${tr("قطعة", "unit(s)")}</span>`
        );
        loadInventoryTable();
      } catch (err) {
        playScanErrorTone();
        showToast(err.message, true);
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = `<i class="fa-solid fa-plus"></i> ${tr("أضف الكمية وامسح العنصر التالي", "Add and scan next")}`;
        }
      }
    }
    document.getElementById("btnApplyQty")?.addEventListener("click", applyQtyTopUp);
    scanQtyInput?.addEventListener("keydown", e => {
      if (e.key !== "Enter") return;
      e.preventDefault();
      applyQtyTopUp();
    });

    function showDone(title, text, summaryHtml) {
      const t = document.getElementById("addDoneTitle");
      const d = document.getElementById("addDoneText");
      const sum = document.getElementById("addDoneSummary");
      if (t) t.textContent = title;
      if (d) d.textContent = text;
      if (sum) sum.innerHTML = summaryHtml || "";
      stopCameraScanner("addProdCameraReader");
      resetScanButtons();
      resetScanQtyBar();
      showStage("done");
    }

    const imgCaptureBtn = document.getElementById("btnCaptureProdImage");
    const imgCaptureInput = document.getElementById("prodImageCapture");
    const imgFileInput = document.getElementById("prodImageInput");
    const imgPreview = document.getElementById("prodImagePreview");
    const imgThumb = document.getElementById("prodImagePreviewThumb");
    const imgName = document.getElementById("prodImagePreviewName");

    function showImagePreview(label) {
      if (!imgPreview) return;
      if (imgName) imgName.textContent = label || "";
      imgPreview.classList.add("visible");
    }

    let capturedProdFile = null;
    const camOverlay = document.getElementById("prodCamOverlay");
    const camVideo = document.getElementById("prodCamVideo");
    const camCanvas = document.getElementById("prodCamCanvas");
    let camStream = null;

    function stopProdCam() {
      if (camStream) {
        camStream.getTracks().forEach(t => t.stop());
        camStream = null;
      }
      if (camVideo) camVideo.srcObject = null;
      camOverlay?.classList.remove("visible");
    }

    async function openProdCam() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (imgCaptureInput) {
          imgCaptureInput.value = "";
          imgCaptureInput.click();
        }
        return;
      }
      try {
        stopProdCam();
        camStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: "environment" }, width: { ideal: 1280 }, height: { ideal: 1280 } },
          audio: false
        });
        if (camVideo) {
          camVideo.srcObject = camStream;
          await camVideo.play().catch(() => {});
        }
        camOverlay?.classList.add("visible");
      } catch (err) {
        stopProdCam();
        if (imgCaptureInput) {
          imgCaptureInput.value = "";
          imgCaptureInput.click();
        }
      }
    }

    imgCaptureBtn?.addEventListener("click", openProdCam);
    document.getElementById("btnProdCamClose")?.addEventListener("click", stopProdCam);

    document.getElementById("btnProdCamShot")?.addEventListener("click", async () => {
      if (!camVideo || !camCanvas) return;
      const w = camVideo.videoWidth || 960;
      const h = camVideo.videoHeight || 960;
      camCanvas.width = w;
      camCanvas.height = h;
      camCanvas.getContext("2d").drawImage(camVideo, 0, 0, w, h);
      const blob = await new Promise(res => camCanvas.toBlob(res, "image/jpeg", 0.9));
      if (!blob) {
        showToast(tr("تعذر التقاط الصورة، جرّب تاني", "Could not capture, please retry"), true);
        return;
      }
      const file = new File([blob], `product-${Date.now()}.jpg`, { type: "image/jpeg" });
      capturedProdFile = file;
      if (imgCaptureInput && window.DataTransfer) {
        const dt = new DataTransfer();
        dt.items.add(file);
        imgCaptureInput.files = dt.files;
      }
      if (imgThumb) imgThumb.src = URL.createObjectURL(blob);
      if (imgFileInput) imgFileInput.value = "";
      showImagePreview(tr("صورة بالكاميرا", "Camera photo") + " — " + file.name);
      stopProdCam();
      showToast(tr("تم التقاط صورة المنتج", "Product photo captured"));
    });

    imgCaptureInput?.addEventListener("change", e => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      if (imgThumb) imgThumb.src = URL.createObjectURL(file);
      if (imgFileInput) imgFileInput.value = "";
      showImagePreview(tr("صورة بالكاميرا", "Camera photo") + " — " + file.name);
      showToast(tr("تم التقاط صورة المنتج", "Product photo captured"));
    });

    imgFileInput?.addEventListener("change", e => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      capturedProdFile = null;
      if (imgThumb) imgThumb.src = URL.createObjectURL(file);
      if (imgCaptureInput) imgCaptureInput.value = "";
      showImagePreview(tr("صورة من الملفات", "Selected file") + " — " + file.name);
    });

    document.getElementById("btnClearProdImage")?.addEventListener("click", () => {
      capturedProdFile = null;
      if (imgFileInput) imgFileInput.value = "";
      if (imgCaptureInput) imgCaptureInput.value = "";
      if (imgThumb) imgThumb.removeAttribute("src");
      imgPreview?.classList.remove("visible");
    });

    document.getElementById("btnSkipScan")?.addEventListener("click", async () => {
      const typed = window.prompt(tr("اكتب الكود الموجود على المنتج:", "Type the code printed on the product:"));
      if (!typed) return;
      const clean = typed.trim();
      if (!clean) return;
      await handleAddProductScan(clean);
    });

    document.getElementById("btnPrintCurrentSticker")?.addEventListener("click", () => {
      const code = document.getElementById("prodBarcode")?.value || "";
      const nm = document.getElementById("prodName")?.value || document.getElementById("stickerProdName")?.textContent || "";
      if (!code) {
        showToast(tr("لا يوجد كود لطباعته بعد", "No code to print yet"), true);
        return;
      }
      renderQrInto(document.getElementById("modalStickerQr"), code, 4);
      renderBarcodeInto(document.getElementById("modalStickerBarcode"), code);
      const nameEl = document.getElementById("modalStickerName");
      const codeEl = document.getElementById("modalStickerCode");
      if (nameEl) nameEl.textContent = nm;
      if (codeEl) codeEl.textContent = code;
      window.openModal("stickerModal");
    });

    window.AslyAddFlow = { stage: showStage, clear: clearProductFormState, resetQty: resetScanQtyBar };

    document.getElementById("btnScanNextProduct")?.addEventListener("click", () => {
      clearProductFormState();
      showStage("scan");
      window.setTimeout(startAddCam, 240);
    });

    document.getElementById("btnSeeInventory")?.addEventListener("click", () => {
      clearProductFormState();
      window.switchTab("inventory");
    });

    document.getElementById("btnBackToScan")?.addEventListener("click", () => {
      stopCameraScanner("addProdCameraReader");
      resetScanButtons();
      clearProductFormState();
      showStage("scan");
    });

    function clearProductFormState() {
      const form = document.getElementById("productSaveForm");
      if (form) form.reset();
      document.getElementById("prodId").value = "0";
      const bc = document.getElementById("prodBarcode");
      if (bc) bc.value = "";
      const ct = document.getElementById("prodCodeType");
      if (ct) ct.value = "qr";
      const cat = document.getElementById("prodCategory");
      if (cat) cat.value = "accessories";
      const cond = document.getElementById("prodCondition");
      if (cond) cond.value = "new";
      const qty = document.getElementById("prodQuantity");
      if (qty) qty.value = "1";
      document.getElementById("existingProdAlert").style.display = "none";
      const hint = document.getElementById("liveProfitHint");
      if (hint) hint.textContent = "";
      document.getElementById("btnClearProdImage")?.click();
      resetScanQtyBar();
      syncSlimCodeStrip();
      updateLiveStickerPreview();
    }

    window.populateProductForm = p => {
      if (!p) return;
      document.getElementById("prodId").value = p.id || 0;
      document.getElementById("prodBarcode").value = p.barcode || "";
      document.getElementById("prodCodeType").value = p.code_type || "qr";
      const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
      };
      set("prodName", p.name || "");
      set("prodNameEn", p.name_en || "");
      set("prodCategory", p.category || "accessories");
      set("prodCondition", p.condition_status || "new");
      set("prodBrand", p.brand || "");
      set("prodPurchasePrice", p.purchase_price ?? 0);
      set("prodSellPrice", p.sell_price ?? 0);
      set("prodMinSellPrice", p.min_sell_price ?? 0);
      set("prodQuantity", p.quantity ?? 1);
      set("prodLowStock", p.low_stock_threshold ?? 2);
      set("prodWarranty", p.warranty_default || "");
      set("prodSpecs", p.specs || "");
      set("prodSpecsEn", p.specs_en || "");
      set("prodDescription", p.description || "");
      set("prodShowLanding", Number(p.show_on_landing) === 1 ? "1" : "0");
      set("prodFeatured", Number(p.is_featured) === 1 ? "1" : "0");
      const alertBox = document.getElementById("existingProdAlert");
      if (alertBox) alertBox.style.display = "none";
      const nf = document.getElementById("newProductFields");
      if (nf) nf.style.display = "block";
      const qtyBar = document.getElementById("scanQtyBar");
      if (qtyBar) qtyBar.style.display = "none";
      scannedExistingProduct = null;
      syncSlimCodeStrip();
      updateLiveStickerPreview();
      updateLiveProfitHint();
      showStage("form");
    };

    document.getElementById("productSaveForm")?.addEventListener("submit", async e => {
      e.preventDefault();
      const form = e.currentTarget;
      const fd = new FormData(form);
      const captured = document.getElementById("prodImageCapture");
      let photo = null;
      if (captured && captured.files && captured.files[0]) photo = captured.files[0];
      if (!photo && capturedProdFile) photo = capturedProdFile;
      if (photo) fd.set("image", photo, photo.name || "product.jpg");
      fd.set("action", "save");
      fd.set("show_on_landing", "0");
      fd.set("is_featured", "0");
      const pCat = document.getElementById("prodCategory");
      const pCond = document.getElementById("prodCondition");
      if (pCat) fd.set("category", pCat.value || "accessories");
      if (pCond) fd.set("condition_status", pCond.value || "new");
      try {
        const res = await apiFetch("api/products.php", {
          method: "POST",
          body: fd
        });
        showToast(res.message);
        document.getElementById("prodBarcode").value = res.barcode;
        updateLiveStickerPreview();
        if (document.getElementById("prodId").value === "0") {
          const pName = (document.getElementById("prodName")?.value || "").trim();
          const pQty = document.getElementById("prodQuantity")?.value || "1";
          document.getElementById("btnClearProdImage")?.click();
          showDone(
            tr("تم حفظ المنتج", "Product saved"),
            tr("المنتج بقى داخل المخزون جاهز للبيع.", "The product is now in stock."),
            `<strong>${esc(pName)}</strong><br><span style="font-size:0.82rem;color:var(--db-muted);">${tr("الكمية", "Qty")}: <strong>${esc(pQty)}</strong> • ${tr("الكود", "Code")}: <strong style="direction:ltr;display:inline-block;">${esc(res.barcode || "")}</strong></span>`
          );
          loadInventoryTable();
        }
      } catch (err) {
        showToast(err.message, true);
      }
    });
  }
  let inventoryCache = [];
  async function loadInventoryTable() {
    if (!IS_ADMIN) return;
    const q = document.getElementById("invSearch")?.value.trim() || "";
    const cat = document.getElementById("invCategory")?.value || "all";
    const tbody = document.getElementById("inventoryTableBody");
    if (!tbody) return;
    try {
      const data = await apiFetch(`api/products.php?category=${encodeURIComponent(cat)}&q=${encodeURIComponent(q)}`);
      inventoryCache = data.products || [];
      if (!inventoryCache.length) {
        tbody.innerHTML = `<tr><td colspan="${IS_ADMIN ? 9 : 7}" style="text-align:center;padding:28px;color:var(--db-muted);">${tr("لا توجد منتجات مطابقة.", "No matching products.")}</td></tr>`;
        return;
      }
      tbody.innerHTML = inventoryCache.map(p => {
        const img = p.image_path ? apiUrl(p.image_path) : fallbackImg();
        const low = p.quantity <= p.low_stock_threshold;
        const pTitle = p.display_name || p.name;
        return `\n          <tr>\n            <td><img src="${esc(img)}" onerror="aslyImgErr(this)" alt="" style="width:54px;height:54px;object-fit:cover;border-radius:12px;border:1px solid var(--db-border);background:#fff;"></td>\n            <td>\n              <strong>${esc(pTitle)}</strong>\n              <div style="font-size:0.76rem;color:var(--db-muted);">${esc(p.category_label)} • ${esc(p.condition_label)}</div>\n            </td>\n            <td style="direction:ltr;font-family:monospace;font-weight:800;">${esc(p.barcode)}</td>\n            <td>\n              <span style="padding:3px 10px;border-radius:999px;font-weight:800;font-size:0.8rem;background:${low ? "#fef2f2" : "#ecfdf5"};color:${low ? "#dc2626" : "#059669"};">\n                ${p.quantity} ${low ? '<i class="fa-solid fa-triangle-exclamation"></i>' : ""}\n              </span>\n            </td>\n            ${IS_ADMIN ? `<td style="color:var(--db-muted);font-weight:700;">${fmtMoney(p.purchase_price)}</td>` : ""}\n            <td style="font-weight:900;color:var(--db-teal-800);">${fmtMoney(p.sell_price)}</td>\n            <td style="font-weight:700;color:#059669;">${fmtMoney(p.min_sell_price)}</td>\n            <td>\n              <div style="display:flex;gap:6px;flex-wrap:wrap;">\n                <button type="button" class="btn btn-ghost btn-sm" onclick="openStickerModal(${p.id})"><i class="fa-solid fa-tag"></i> ${tr("الكود", "Sticker")}</button>\n                ${p.quantity > 0 ? `<button type="button" class="btn btn-gold btn-sm" onclick="sellFromInv(${p.id})"><i class="fa-solid fa-cart-plus"></i> ${tr("بيع", "Sell")}</button>` : ""}\n                ${IS_ADMIN ? `\n                  <button type="button" class="btn btn-teal btn-sm" onclick="editFromInv(${p.id})">${tr("تعديل", "Edit")}</button>\n                  <button type="button" class="btn btn-danger btn-sm" onclick="deleteProd(${p.id})">${tr("حذف", "Delete")}</button>\n                ` : ""}\n              </div>\n            </td>\n          </tr>\n        `;
      }).join("");
    } catch (err) {
      showToast(err.message, true);
    }
  }
  document.getElementById("invSearch")?.addEventListener("input", loadInventoryTable);
  document.getElementById("invCategory")?.addEventListener("change", loadInventoryTable);
  window.openStickerModal = id => {
    const p = inventoryCache.find(x => x.id === id);
    if (!p) return;
    document.getElementById("modalStickerName").textContent = p.display_name || p.name;
    document.getElementById("modalStickerCode").textContent = p.barcode;
    renderQrInto(document.getElementById("modalStickerQr"), p.barcode, 4);
    renderBarcodeInto(document.getElementById("modalStickerBarcode"), p.barcode);
    window.openModal("stickerModal");
  };
  document.getElementById("btnPrintModalSticker")?.addEventListener("click", () => {
    printHtmlSnippet("Product Sticker", `<div style="text-align:center;width:250px;padding:12px;border:1.5px solid #0d9499;border-radius:10px;">${document.getElementById("modalStickerArea").innerHTML}</div>`);
  });
  window.sellFromInv = id => {
    const p = inventoryCache.find(x => x.id === id);
    if (p) {
      addToPosCart(p);
      window.switchTab("pos");
    }
  };
  window.editFromInv = id => {
    const p = inventoryCache.find(x => x.id === id);
    if (p && IS_ADMIN) {
      window.switchTab("add-product");
      window.populateProductForm(p);
    }
  };
  window.toggleLandingVis = async (id, checked) => {
    try {
      const res = await apiFetch("api/products.php", {
        method: "POST",
        json: {
          action: "toggle_landing",
          id: id,
          show_on_landing: checked ? 1 : 0
        }
      });
      showToast(res.message);
    } catch (err) {
      showToast(err.message, true);
    }
  };
  window.deleteProd = async id => {
    if (!confirm(tr("هل أنت متأكد من حذف هذا المنتج نهائياً من المخزن؟", "Permanently delete this product from inventory?"))) return;
    try {
      const res = await apiFetch("api/products.php", {
        method: "POST",
        json: {
          action: "delete",
          id: id
        }
      });
      showToast(res.message);
      loadInventoryTable();
    } catch (err) {
      showToast(err.message, true);
    }
  };
  async function loadMaintenanceList() {
    const q = document.getElementById("maintSearch")?.value.trim() || "";
    const stage = document.getElementById("maintStageFilter")?.value || "all";
    const tbody = document.getElementById("maintTableBody");
    if (!tbody) return;
    try {
      const data = await apiFetch(`api/maintenance.php?stage=${encodeURIComponent(stage)}&q=${encodeURIComponent(q)}`);
      const tickets = data.tickets || [];
      const totalUnread = tickets.reduce((a, t) => a + (t.unread_customer_notes || 0), 0);
      const navBadge = document.getElementById("maintNavBadge");
      if (navBadge) {
        navBadge.style.display = totalUnread > 0 ? "inline-block" : "none";
        navBadge.textContent = `${totalUnread} ${tr("جديد", "new")}`;
      }
      if (!tickets.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--db-muted);">${tr("لا توجد أجهزة صيانة نشطة حالياً.", "No active repair tickets right now.")}</td></tr>`;
        return;
      }
      tbody.innerHTML = tickets.map(t => {
        const wa = normalizeWa(t.customer_phone);
        return `\n          <tr>\n            <td style="direction:ltr;font-weight:900;color:var(--db-teal-800);">${esc(t.ticket_code)}</td>\n            <td>\n              <strong>${esc(t.customer_name)}</strong>\n              <div style="display:flex;gap:6px;align-items:center;margin-top:4px;flex-wrap:wrap;">\n                <a href="tel:${esc(t.customer_phone)}" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:0.75rem;"><i class="fa-solid fa-phone"></i> ${esc(t.customer_phone)}</a>\n                ${wa ? `<a href="https://wa.me/${wa}" target="_blank" class="btn btn-emerald btn-sm" style="padding:3px 8px;font-size:0.75rem;"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>` : ""}\n                ${t.customer_email ? `<span title="${esc(t.customer_email)}" style="font-size:0.74rem;color:#059669;font-weight:700;"><i class="fa-solid fa-envelope"></i> Email</span>` : ""}\n              </div>\n            </td>\n            <td>\n              <strong>${esc(t.device_model)}</strong>\n              <div style="font-size:0.78rem;color:var(--db-muted);max-width:220px;">${esc(t.problem_description)}</div>\n            </td>\n            <td>\n              <span style="padding:4px 12px;border-radius:999px;background:var(--db-gold-50);color:var(--db-gold-600);border:1px solid rgba(228,180,83,0.45);font-weight:800;font-size:0.78rem;">\n                ${esc(t.stage_title)}\n              </span>\n            </td>\n            <td>\n              <div style="font-weight:800;">${fmtMoney(t.estimated_cost)}</div>\n              <div style="font-size:0.75rem;color:var(--db-muted);">${tr("عربون:", "Dep:")} ${fmtMoney(t.deposit_paid)}</div>\n            </td>\n            <td>\n              ${t.unread_customer_notes > 0 ? `<span class="badge-alert"><i class="fa-solid fa-comment-dots"></i> ${t.unread_customer_notes} ${tr("ملاحظة!", "new!")}</span>` : `<span style="font-size:0.78rem;color:var(--db-muted);">—</span>`}\n            </td>\n            <td>\n              <button type="button" class="btn btn-teal btn-sm" onclick="openMaintTicket(${t.id})">${tr("إدارة المراحل / QR / تسليم", "Stages / QR / Deliver")}</button>\n            </td>\n          </tr>\n        `;
      }).join("");
    } catch (err) {
      showToast(err.message, true);
    }
  }
  document.getElementById("maintSearch")?.addEventListener("input", loadMaintenanceList);
  document.getElementById("maintStageFilter")?.addEventListener("change", loadMaintenanceList);
  document.getElementById("btnOpenNewMaint")?.addEventListener("click", () => window.openModal("newMaintModal"));
  document.getElementById("newMaintForm")?.addEventListener("submit", async e => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    payload.action = "create";
    try {
      const res = await apiFetch("api/maintenance.php", {
        method: "POST",
        json: payload
      });
      showToast(res.message);
      e.currentTarget.reset();
      window.closeModal("newMaintModal");
      await loadMaintenanceList();
      window.openMaintTicket(res.ticket.id);
    } catch (err) {
      showToast(err.message, true);
    }
  });
  window.openMaintTicket = async id => {
    try {
      const res = await apiFetch(`api/maintenance.php?action=details&id=${id}`);
      const t = res.ticket;
      const stages = res.stages || {};
      const notes = res.notes || [];
      const wa = normalizeWa(t.customer_phone);
      const waTrackMsg = EN ? `Hello ${t.customer_name},\nYour device (${t.device_model}) is registered at *${CFG.store.nameEn}* Repair Lab.\nTicket: ${t.ticket_code}\nStage: ${t.stage_title}\n\nTrack live stages & reply here:\n${t.tracking_url}` : `مرحباً أستاذ/ ${t.customer_name}\nتم تسجيل جهازكم (${t.device_model}) بقسم الصيانة في *${CFG.store.nameAr}*\nرقم التذكرة: ${t.ticket_code}\nالمرحلة الحالية: ${t.stage_title}\n\nيمكنك متابعة مراحل الصيانة لحظة بلحظة وإضافة أي ملاحظات عبر الرابط التالي:\n${t.tracking_url}`;
      document.getElementById("mdModalTitle").innerHTML = `<i class="fa-solid fa-screwdriver-wrench"></i> ${esc(t.ticket_code)} — ${esc(t.device_model)}`;
      const stageOptions = Object.entries(stages).filter(([k]) => k !== "delivered").map(([k, st]) => `<option value="${esc(k)}" ${t.current_stage === k ? "selected" : ""}>${st.order}. ${esc(st.title)}</option>`).join("");
      const notesHtml = notes.map(n => `\n        <div style="padding:10px 14px;border-radius:12px;margin-bottom:8px;background:${n.sender_type === "admin" ? "#effafa" : "#fffbf0"};border-inline-start:4px solid ${n.sender_type === "admin" ? "#14b4b8" : "#e4b453"};">\n          <div style="display:flex;justify-content:space-between;font-size:0.76rem;color:#475569;margin-bottom:4px;">\n            <strong><i class="fa-solid ${n.sender_type === "admin" ? "fa-user-gear" : "fa-user"}"></i> ${esc(n.sender_name)} — [${esc(stages[n.stage_key]?.title || n.stage_key)}]</strong>\n            <span style="direction:ltr;">${esc(n.created_at)}</span>\n          </div>\n          <div style="font-size:0.88rem;color:#1e293b;">${esc(n.note_text)}</div>\n        </div>\n      `).join("");
      const body = document.getElementById("maintDetailBody");
      body.innerHTML = `\n        <div style="display:grid;grid-template-columns:1.3fr 0.7fr;gap:18px;margin-bottom:20px;">\n          <div style="background:var(--db-bg);padding:16px;border-radius:16px;border:1px solid var(--db-border);font-size:0.88rem;">\n            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--db-border);">\n              <strong style="font-size:1rem;color:var(--db-text);"><i class="fa-solid fa-user"></i> ${esc(t.customer_name)}</strong>\n              <div style="display:flex;gap:6px;flex-wrap:wrap;">\n                <a href="tel:${esc(t.customer_phone)}" class="btn btn-teal btn-sm"><i class="fa-solid fa-phone"></i> ${esc(t.customer_phone)}</a>\n                ${wa ? `<a href="https://wa.me/${wa}?text=${encodeURIComponent(waTrackMsg)}" target="_blank" class="btn btn-emerald btn-sm"><i class="fa-brands fa-whatsapp"></i> WhatsApp QR Link</a>` : ""}\n              </div>\n            </div>\n            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">\n              <div><strong>${tr("هاتف بديل:", "Alt Phone:")}</strong> ${esc(t.customer_alt_phone || "—")}</div>\n              <div><strong>Email:</strong> ${esc(t.customer_email || "—")}</div>\n              <div><strong>${tr("تفاصيل:", "Details:")}</strong> ${esc(t.customer_details || "—")}</div>\n              <div><strong>${tr("باسورد الفحص:", "Passcode:")}</strong> <span style="background:#fef2f2;color:#b91c1c;padding:1px 8px;border-radius:6px;font-weight:800;">${esc(t.device_passcode || "—")}</span></div>\n              <div><strong>${tr("المواصفات:", "Specs:")}</strong> ${esc(t.device_specs || "—")}</div>\n              <div><strong>${tr("الحالة عند الاستلام:", "Condition:")}</strong> ${esc(t.physical_condition || "—")}</div>\n            </div>\n            <div style="margin-top:10px;padding:8px 12px;background:#fff;border-radius:10px;border:1px solid var(--db-border);">\n              <strong>${tr("العطل المسجل:", "Reported Issue:")}</strong> ${esc(t.problem_description)}\n            </div>\n          </div>\n\n          <div style="background:#ffffff;border:2px dashed var(--db-teal-700);border-radius:16px;padding:14px;text-align:center;" id="maintReceiptPrintArea">\n            <strong style="color:#0d9499;font-size:0.9rem;">ASLY | أصلي TECH STORE</strong>\n            <div style="font-size:0.76rem;color:#64748b;">${tr("إيصال متابعة صيانة بالـ QR", "Live QR Repair Receipt")}</div>\n            <div id="maintTicketQrMount" style="margin:8px auto;"></div>\n            <div style="font-family:monospace;font-weight:900;font-size:0.95rem;color:#1e293b;">${esc(t.ticket_code)}</div>\n            <div style="font-size:0.78rem;margin:4px 0;">${esc(t.device_model)}</div>\n            <div style="font-size:0.74rem;color:#64748b;">${tr("امسح الرمز بكاميرا هاتفك لمتابعة المراحل", "Scan QR with phone camera for live status")}</div>\n            <div style="margin-top:10px;display:flex;gap:6px;justify-content:center;" class="no-print">\n              <button type="button" class="btn btn-teal btn-sm" id="btnPrintMaintReceipt"><i class="fa-solid fa-print"></i> ${tr("طباعة QR", "Print QR")}</button>\n              <a href="${esc(t.tracking_url)}" target="_blank" class="btn btn-ghost btn-sm">${tr("معاينة صفحة العميل", "Preview Portal")}</a>\n            </div>\n          </div>\n        </div>\n\n        <div style="background:var(--db-teal-50);padding:16px;border-radius:16px;border:1px solid var(--db-teal-100);margin-bottom:18px;">\n          <strong style="display:block;margin-bottom:10px;color:var(--db-teal-800);"><i class="fa-solid fa-arrows-rotate"></i> ${tr("نقل الجهاز لمرحلة جديدة / إضافة ملاحظة للعميل", "Update Repair Stage / Add Engineer Note")} ${t.customer_email ? '<i class="fa-solid fa-envelope-circle-check"></i>' : ""}</strong>\n          <form id="updateMaintStageForm" style="display:grid;grid-template-columns:1fr 1.5fr auto;gap:10px;align-items:end;">\n            <div>\n              <label style="font-size:0.78rem;font-weight:700;">${tr("المرحلة الحالية", "Stage")}</label>\n              <select name="stage_key" class="form-control">${stageOptions}</select>\n            </div>\n            <div>\n              <label style="font-size:0.78rem;font-weight:700;">${tr("ملاحظة المهندس", "Engineer Report / Note")}</label>\n              <input type="text" name="note_text" class="form-control" placeholder="${tr("اكتب ما تم إنجازه...", "Write diagnostic/repair update...")}">\n            </div>\n            <button type="submit" class="btn btn-teal">${tr("تحديث المرحلة", "Update Stage")}</button>\n          </form>\n        </div>\n\n        <div style="margin-bottom:20px;">\n          <h4 style="margin:0 0 10px;color:var(--db-text);"><i class="fa-solid fa-comments"></i> ${tr("سجل المراحل والتعليقات المتبادلة", "Stage History & Mutual Notes")}</h4>\n          <div style="max-height:240px;overflow-y:auto;">${notesHtml || `<p style="color:var(--db-muted);">—</p>`}</div>\n        </div>\n\n        <div style="background:linear-gradient(135deg,#effafa 0%,#fffbf0 100%);color:var(--db-text);padding:18px 20px;border-radius:18px;border:1.5px solid var(--db-gold-500);">\n          <strong style="color:var(--db-teal-800);display:block;margin-bottom:6px;font-size:1rem;"><i class="fa-solid fa-circle-check"></i> ${tr("التسليم النهائي للجهاز (حفظ الإيراد + مسح بيانات الجهاز نهائياً)", "Final Handover (Record Revenue + Permanently Wipe Device Data)")}</strong>\n          <p style="margin:0 0 12px;font-size:0.82rem;color:var(--db-muted);">${tr("بمجرد تأكيد التسليم، يُسجّل المبلغ المدفوع في حسابات اليوم وتُمسح كافة تفاصيل الجهاز والعميل والملاحظات نهائياً.", "Confirming delivery logs the paid amount into daily revenue and permanently deletes all personal device & customer records.")}</p>\n          <form id="deliverWipeForm" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">\n            <div style="flex:1;min-width:150px;">\n              <label style="font-size:0.78rem;font-weight:800;display:block;margin-bottom:4px;">${tr("إجمالي المبلغ المحصل *", "Final Paid Amount *")}</label>\n              <input type="number" step="0.01" min="0" name="final_paid_amount" value="${t.estimated_cost}" class="form-control" required style="font-weight:900;">\n            </div>\n            ${IS_ADMIN ? `\n            <div style="flex:1;min-width:140px;">\n              <label style="font-size:0.78rem;font-weight:700;display:block;margin-bottom:4px;">${tr("تكلفة قطع الغيار الفعلية", "Actual Parts Cost")}</label>\n              <input type="number" step="0.01" min="0" name="parts_cost" value="${t.parts_cost || 0}" class="form-control">\n            </div>` : ""}\n            <div style="min-width:140px;">\n              <label style="font-size:0.78rem;font-weight:700;display:block;margin-bottom:4px;">${tr("طريقة الدفع", "Payment Method")}</label>\n              <select name="payment_method" class="form-control">\n                <option value="cash">${tr("نقدي (كاش)", "Cash")}</option>\n                <option value="vodafone_cash">${tr("فودافون كاش", "Vodafone Cash")}</option>\n                <option value="instapay">InstaPay</option>\n              </select>\n            </div>\n            <button type="submit" class="btn btn-gold">${tr("تسليم الجهاز ومسح بياناته", "Deliver & Wipe Data")}</button>\n          </form>\n        </div>\n      `;
      renderQrInto(document.getElementById("maintTicketQrMount"), t.tracking_url, 3);
      window.openModal("maintDetailModal");
      loadMaintenanceList();
      document.getElementById("btnPrintMaintReceipt")?.addEventListener("click", () => {
        const clone = document.getElementById("maintReceiptPrintArea").cloneNode(true);
        clone.querySelector(".no-print")?.remove();
        printHtmlSnippet(`Receipt ${t.ticket_code}`, `<div style="text-align:center;width:270px;padding:14px;border:1.5px solid #0d9499;border-radius:12px;">${clone.innerHTML}</div>`);
      });
      document.getElementById("updateMaintStageForm")?.addEventListener("submit", async ev => {
        ev.preventDefault();
        const fd = new FormData(ev.currentTarget);
        try {
          const r = await apiFetch("api/maintenance.php", {
            method: "POST",
            json: {
              action: "update_stage",
              id: t.id,
              stage_key: fd.get("stage_key"),
              note_text: fd.get("note_text")
            }
          });
          showToast(r.message);
          window.openMaintTicket(t.id);
        } catch (err) {
          showToast(err.message, true);
        }
      });
      document.getElementById("deliverWipeForm")?.addEventListener("submit", async ev => {
        ev.preventDefault();
        if (!confirm(tr("تأكيد تسليم الجهاز للعميل؟ سيتم حفظ المبلغ المدفوع في إيرادات اليوم ومسح كافة بيانات التذكرة نهائياً!", "Confirm final delivery? Paid revenue will be recorded and all personal ticket records permanently wiped!"))) return;
        const fd = new FormData(ev.currentTarget);
        try {
          const r = await apiFetch("api/maintenance.php", {
            method: "POST",
            json: {
              action: "deliver_and_wipe",
              id: t.id,
              final_paid_amount: fd.get("final_paid_amount"),
              parts_cost: fd.get("parts_cost"),
              payment_method: fd.get("payment_method")
            }
          });
          showToast(r.message);
          window.closeModal("maintDetailModal");
          loadMaintenanceList();
        } catch (err) {
          showToast(err.message, true);
        }
      });
    } catch (err) {
      showToast(err.message, true);
    }
  };
  let usersCache = [];
  async function loadUsersList() {
    if (!IS_ADMIN) return;
    try {
      const data = await apiFetch("api/users.php");
      usersCache = data.users || [];
      const tbody = document.getElementById("usersTableBody");
      if (!tbody) return;
      tbody.innerHTML = usersCache.map(u => `\n        <tr>\n          <td><strong>${esc(u.full_name)}</strong></td>\n          <td style="direction:ltr;font-weight:700;">@${esc(u.username)}</td>\n          <td>\n            <span class="role-pill ${u.role === "admin" ? "role-admin" : "role-staff"}">\n              <i class="fa-solid ${u.role === "admin" ? "fa-user-shield" : "fa-id-badge"}"></i> ${u.role === "admin" ? "Admin" : "Staff"}\n            </span>\n          </td>\n          <td style="direction:ltr;">${esc(u.phone || "—")}</td>\n          <td>${Number(u.is_active) === 1 ? `<span style="color:#059669;font-weight:800;">${tr("نشط", "Active")}</span>` : `<span style="color:#dc2626;font-weight:800;">${tr("معطل", "Disabled")}</span>`}</td>\n          <td style="direction:ltr;font-size:0.8rem;">${esc(u.created_at)}</td>\n          <td>\n            <button type="button" class="btn btn-teal btn-sm" onclick="editUser(${u.id})">${tr("تعديل", "Edit")}</button>\n            ${u.id !== CFG.user.id ? `<button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})">${tr("حذف", "Delete")}</button>` : ""}\n          </td>\n        </tr>\n      `).join("");
    } catch (err) {
      showToast(err.message, true);
    }
  }
  if (IS_ADMIN) {
    document.getElementById("btnAddUserModal")?.addEventListener("click", () => {
      document.getElementById("userSaveForm").reset();
      document.getElementById("uFormId").value = "0";
      document.getElementById("uPassHint").textContent = tr("(مطلوبة - 6 أحرف على الأقل)", "(Required — min 6 chars)");
      document.getElementById("uFormPassword").required = true;
      window.openModal("userModal");
    });
    window.editUser = id => {
      const u = usersCache.find(x => Number(x.id) === id);
      if (!u) return;
      document.getElementById("uFormId").value = u.id;
      document.getElementById("uFormFullName").value = u.full_name;
      document.getElementById("uFormUsername").value = u.username;
      document.getElementById("uFormPassword").value = "";
      document.getElementById("uFormPassword").required = false;
      document.getElementById("uPassHint").textContent = tr("(اتركها فارغة لعدم التغيير)", "(Leave blank to keep unchanged)");
      document.getElementById("uFormRole").value = u.role;
      document.getElementById("uFormPhone").value = u.phone || "";
      document.getElementById("uFormActive").checked = Number(u.is_active) === 1;
      window.openModal("userModal");
    };
    window.deleteUser = async id => {
      if (!confirm(tr("حذف هذا الحساب نهائياً؟", "Delete this account?"))) return;
      try {
        const res = await apiFetch("api/users.php", {
          method: "POST",
          json: {
            action: "delete",
            id: id
          }
        });
        showToast(res.message);
        loadUsersList();
      } catch (err) {
        showToast(err.message, true);
      }
    };
    document.getElementById("userSaveForm")?.addEventListener("submit", async e => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const payload = Object.fromEntries(fd.entries());
      payload.action = "save";
      payload.is_active = document.getElementById("uFormActive").checked ? 1 : 0;
      try {
        const res = await apiFetch("api/users.php", {
          method: "POST",
          json: payload
        });
        showToast(res.message);
        window.closeModal("userModal");
        loadUsersList();
      } catch (err) {
        showToast(err.message, true);
      }
    });
    let channelsCache = [];
    window.loadSettingsData = async () => {
      try {
        const data = await apiFetch("api/settings.php");
        const s = data.settings || {};
        channelsCache = data.channels || [];
        Object.entries(s).forEach(([k, v]) => {
          const el = document.getElementById(`set_${k}`);
          if (el) el.value = v ?? "";
        });
        const tbody = document.getElementById("channelsTableBody");
        if (tbody) {
          tbody.innerHTML = channelsCache.map(ch => `\n            <tr>\n              <td><strong>${esc(ch.channel_type)}</strong></td>\n              <td>${esc(ch.label)}</td>\n              <td style="direction:ltr;font-weight:700;">${esc(ch.value)}</td>\n              <td style="direction:ltr;font-size:0.78rem;max-width:190px;overflow:hidden;text-overflow:ellipsis;">${esc(ch.url || "")}</td>\n              <td>${ch.sort_order}</td>\n              <td>${Number(ch.is_active) === 1 ? `<span style="color:#059669;font-weight:800;">${tr("ظاهر", "Visible")}</span>` : `<span style="color:#94a3b8;">${tr("مخفي", "Hidden")}</span>`}</td>\n              <td>\n                <button type="button" class="btn btn-teal btn-sm" onclick="editChannel(${ch.id})">${tr("تعديل", "Edit")}</button>\n                <button type="button" class="btn btn-danger btn-sm" onclick="deleteChannel(${ch.id})">${tr("حذف", "Delete")}</button>\n              </td>\n            </tr>\n          `).join("");
        }
      } catch (err) {
        showToast(err.message, true);
      }
    };
    document.getElementById("settingsSaveForm")?.addEventListener("submit", async e => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const settingsObj = Object.fromEntries(fd.entries());
      try {
        const res = await apiFetch("api/settings.php", {
          method: "POST",
          json: {
            action: "save_settings",
            settings: settingsObj
          }
        });
        showToast(res.message);
      } catch (err) {
        showToast(err.message, true);
      }
    });
    document.getElementById("btnAddChannelModal")?.addEventListener("click", () => {
      document.getElementById("channelSaveForm").reset();
      document.getElementById("chFormId").value = "0";
      window.openModal("channelModal");
    });
    window.editChannel = id => {
      const ch = channelsCache.find(x => Number(x.id) === id);
      if (!ch) return;
      document.getElementById("chFormId").value = ch.id;
      document.getElementById("chFormType").value = ch.channel_type;
      document.getElementById("chFormSort").value = ch.sort_order;
      document.getElementById("chFormLabel").value = ch.label;
      document.getElementById("chFormValue").value = ch.value;
      document.getElementById("chFormUrl").value = ch.url || "";
      document.getElementById("chFormActive").checked = Number(ch.is_active) === 1;
      window.openModal("channelModal");
    };
    window.deleteChannel = async id => {
      if (!confirm(tr("حذف وسيلة التواصل هذه؟", "Delete this contact channel?"))) return;
      try {
        const res = await apiFetch("api/settings.php", {
          method: "POST",
          json: {
            action: "delete_channel",
            id: id
          }
        });
        showToast(res.message);
        window.loadSettingsData();
      } catch (err) {
        showToast(err.message, true);
      }
    };
    document.getElementById("channelSaveForm")?.addEventListener("submit", async e => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const payload = Object.fromEntries(fd.entries());
      payload.action = "save_channel";
      payload.is_active = document.getElementById("chFormActive").checked ? 1 : 0;
      try {
        const res = await apiFetch("api/settings.php", {
          method: "POST",
          json: payload
        });
        showToast(res.message);
        window.closeModal("channelModal");
        window.loadSettingsData();
      } catch (err) {
        showToast(err.message, true);
      }
    });
    document.getElementById("btnSendTestEmail")?.addEventListener("click", async () => {
      const to = document.getElementById("testEmailRecipient").value.trim();
      if (!to) {
        showToast(tr("أدخل بريداً إلكترونياً للتجربة أولاً", "Enter a recipient email first"), true);
        return;
      }
      try {
        const res = await apiFetch("api/settings.php", {
          method: "POST",
          json: {
            action: "test_email",
            to_email: to
          }
        });
        showToast(res.message);
      } catch (err) {
        showToast(err.message, true);
      }
    });
    document.getElementById("btnViewEmailLogs")?.addEventListener("click", async () => {
      try {
        const res = await apiFetch("api/settings.php?action=email_logs");
        const logs = res.logs || [];
        const body = document.getElementById("emailLogsBody");
        body.innerHTML = logs.length ? logs.map(l => `\n              <details style="border:1px solid var(--db-border);border-radius:14px;padding:12px 16px;margin-bottom:10px;background:var(--db-bg);">\n                <summary style="cursor:pointer;font-weight:800;color:var(--db-text);">\n                  <i class="fa-solid fa-envelope" style="color:#0d9499;"></i> ${esc(l.recipient_email)} — ${esc(l.subject)} <span style="font-size:0.75rem;color:#059669;">[${esc(l.status)} • ${esc(l.created_at)}]</span>\n                </summary>\n                <div style="margin-top:12px;border-top:1px solid var(--db-border);padding-top:12px;">\n                  ${l.error_message ? `<div style="color:#dc2626;margin-bottom:8px;">SMTP Error: ${esc(l.error_message)}</div>` : ""}\n                  <iframe srcdoc="${esc(l.body_preview || "")}" style="width:100%;height:340px;border:1px solid var(--db-border);border-radius:10px;background:#fff;"></iframe>\n                </div>\n              </details>\n            `).join("") : `<p style="text-align:center;color:var(--db-muted);padding:24px;">${tr("لا توجد رسائل مسجلة بعد.", "No email logs yet.")}</p>`;
        window.openModal("emailLogsModal");
      } catch (err) {
        showToast(err.message, true);
      }
    });
  }
  async function checkBarcodeForAddProduct(rawCode) {
    const code = normalizeScannedCode(rawCode, true);
    if (!code) return;
    const inp = document.getElementById("prodBarcode");
    if (inp) inp.value = code;
    const typeEl = document.getElementById("prodCodeType");
    if (typeEl) typeEl.value = code.toUpperCase().startsWith("ASLY-") ? "qr" : "barcode";
    updateLiveStickerPreview();
    pulseScannerPill();
    try {
      const check = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
      const alertEl = document.getElementById("existingProdAlert");
      if (check.found && check.product) {
        playScanBeep();
        if (alertEl) {
          alertEl.style.display = "block";
          alertEl.innerHTML = `<i class="fa-solid fa-bell"></i> ${tr("هذا الكود مسجل مسبقاً للمنتج:", "Already registered to:")} <strong>${esc(check.product.name)}</strong> (${check.product.quantity})
            <button type="button" class="btn btn-teal btn-sm" style="margin-inline-start:8px;" id="btnLoadScannedProd">${tr("تعديل / زيادة الكمية", "Load to Edit")}</button>`;
          document.getElementById("btnLoadScannedProd")?.addEventListener("click", () => window.populateProductForm(check.product));
        }
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-bell" style="color:var(--db-gold-600);font-size:1.3rem;"></i><strong>${tr("الكود مسجل مسبقاً:", "Code already exists:")} ${esc(check.product.name)}</strong></div>
          <div style="display:flex;gap:6px;">
            <button type="button" class="btn btn-teal btn-sm" id="hudBtnEditExisting">${tr("تحميل للتعديل", "Load to Edit")}</button>
            <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`, "hud-success", 6500);
        document.getElementById("hudBtnEditExisting")?.addEventListener("click", () => window.populateProductForm(check.product));
      } else {
        playScanBeep();
        if (alertEl) alertEl.style.display = "none";
        document.getElementById("prodName")?.focus();
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-circle-check" style="color:#059669;font-size:1.35rem;"></i><strong>${tr("تم التقاط كود المنتج الجديد:", "Captured new product code:")} <span style="direction:ltr;display:inline-block;">${esc(code)}</span></strong></div>
          <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
        </div>`, "hud-success", 4500);
      }
    } catch (_) {}
  }
  document.getElementById("prodBarcode")?.addEventListener("keydown", e => {
    if (e.key === "Enter") {
      e.preventDefault();
      checkBarcodeForAddProduct(e.target.value);
    }
  });
  let lastGlobalProductScanned = null;
  window.quickSellGlobalScanned = () => {
    if (!lastGlobalProductScanned) return;
    window.switchTab("pos");
    setTimeout(() => {
      addToPosCart(lastGlobalProductScanned, { viaScan: true });
    }, 120);
  };
  window.quickInspectGlobalScanned = code => {
    window.switchTab("quick-scanner");
    const inp = document.getElementById("quickCodeInput");
    if (inp) inp.value = code;
    lookupQuickPrice(code);
  };
  async function handleAnywhereScan(rawScan) {
    const code = normalizeScannedCode(rawScan, true);
    if (!code || code.length < 3) return;
    pulseScannerPill();
    const tab = getActiveTabName();
    if (tab === "pos") {
      await lookupAndAddPosCode(code);
      return;
    }
    if (tab === "add-product" && IS_ADMIN) {
      await checkBarcodeForAddProduct(code);
      return;
    }
    if (tab === "quick-scanner") {
      playScanBeep();
      const qInp = document.getElementById("quickCodeInput");
      if (qInp) qInp.value = code;
      await lookupQuickPrice(code);
      showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-barcode" style="color:#059669;font-size:1.3rem;"></i><strong>${tr("تم فحص الكود:", "Scanned Code:")} <span style="direction:ltr;display:inline-block;">${esc(code)}</span></strong></div>
        <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
      </div>`, "hud-success", 3600);
      return;
    }
    try {
      const res = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
      if (res.is_maintenance_ticket && res.ticket) {
        playScanBeep();
        await window.switchTab("maintenance");
        await window.openMaintTicket(res.ticket.id);
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div style="display:flex;align-items:center;gap:10px;"><i class="fa-solid fa-screwdriver-wrench" style="color:#059669;font-size:1.35rem;"></i><strong>${tr("تم فتح تذكرة الصيانة مباشرة:", "Opened Repair Ticket:")} ${esc(res.ticket.ticket_code)} (${esc(res.ticket.device_model)})</strong></div>
          <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
        </div>`, "hud-success", 5000);
        return;
      }
      if (res.found && res.product) {
        playScanBeep();
        const p = res.product;
        lastGlobalProductScanned = p;
        const pName = p.display_name || p.name;
        if (tab === "inventory" && IS_ADMIN) {
          const invInp = document.getElementById("invSearch");
          if (invInp) invInp.value = p.barcode;
          loadInventoryTable();
        }
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-check" style="color:#059669;font-size:1.45rem;"></i>
            <div>
              <strong style="font-size:0.95rem;color:var(--db-text);">${esc(pName)}</strong>
              <div style="font-size:0.8rem;color:var(--db-muted);">${tr("سعر البيع:", "Selling Price:")} <strong style="color:var(--db-teal-800);font-size:0.95rem;">${fmtMoney(p.sell_price)}</strong> • ${tr("المتاح:", "Stock:")} <strong>${p.quantity}</strong></div>
            </div>
          </div>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            ${p.quantity > 0 ? `<button type="button" class="btn btn-teal btn-sm" onclick="quickSellGlobalScanned()"><i class="fa-solid fa-cart-plus"></i> ${tr("إضافة للفاتورة الآن", "Add to Invoice")}</button>` : ""}
            <button type="button" class="btn btn-gold btn-sm" onclick="quickInspectGlobalScanned('${esc(p.barcode)}')"><i class="fa-solid fa-magnifying-glass"></i> ${tr("كاشف السعر", "Price View")}</button>
            <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`, "hud-success", 8000);
      } else {
        playScanErrorTone();
        const cTxt = res.scanned_code || code;
        showGlobalScanHud(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-exclamation" style="color:#dc2626;font-size:1.35rem;"></i>
            <strong>${tr("الكود الممسوح غير مسجل بالمخزن:", "Scanned code not registered:")} <span style="direction:ltr;display:inline-block;">${esc(cTxt)}</span></strong>
          </div>
          <div style="display:flex;gap:6px;">
            ${IS_ADMIN ? `<button type="button" class="btn btn-teal btn-sm" onclick="prefillNewProductCode('${esc(cTxt)}')"><i class="fa-solid fa-plus"></i> ${tr("تسجيل كمنتج جديد", "Register Product")}</button>` : ""}
            <button type="button" class="btn btn-ghost btn-sm" data-close-hud="1"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>`, "hud-error", 6500);
      }
    } catch (err) {
      playScanErrorTone();
      showToast(err.message, true);
    }
  }
  function initGlobalAnywhereScanner() {
    const dedicatedScanIds = new Set([ "posSearchInput", "quickCodeInput", "prodBarcode" ]);
    let freeBuf = "";
    let freeLastTs = 0;
    let freeFirstTs = 0;
    let fieldBurst = "";
    let fieldLastTs = 0;
    let fieldTarget = null;

    window.addEventListener("keydown", e => {
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      const now = performance.now();
      const target = e.target;
      const tag = target && target.tagName ? target.tagName.toUpperCase() : "";
      const isTextInput = (tag === "INPUT" && ![ "checkbox", "radio", "file", "button", "submit", "reset", "range", "color" ].includes((target.type || "").toLowerCase()) && !target.readOnly) || tag === "TEXTAREA" || (target && target.isContentEditable);

      if (!isTextInput) {
        if (e.key === "Enter" || e.key === "Tab") {
          if (freeBuf.length >= 3 && now - freeLastTs < 250 && now - freeFirstTs < 2200) {
            e.preventDefault();
            e.stopPropagation();
            const captured = freeBuf;
            freeBuf = "";
            handleAnywhereScan(captured);
          } else {
            freeBuf = "";
          }
          return;
        }
        if (e.key.length === 1) {
          if (now - freeLastTs > 150) {
            freeBuf = e.key;
            freeFirstTs = now;
          } else {
            freeBuf += e.key;
          }
          freeLastTs = now;
        }
        return;
      }

      if (target && dedicatedScanIds.has(target.id)) {
        return;
      }
      if (target && (target.type || "").toLowerCase() === "password") {
        return;
      }

      if (e.key === "Enter") {
        if (fieldTarget === target && fieldBurst.length >= 5 && now - fieldLastTs <= 65) {
          e.preventDefault();
          e.stopPropagation();
          const scanned = fieldBurst;
          fieldBurst = "";
          if (typeof target.value === "string" && target.value.endsWith(scanned)) {
            target.value = target.value.slice(0, target.value.length - scanned.length);
            target.dispatchEvent(new Event("input", { bubbles: true }));
          }
          handleAnywhereScan(scanned);
        } else {
          fieldBurst = "";
        }
        return;
      }
      if (e.key.length === 1) {
        if (fieldTarget === target && now - fieldLastTs <= 45) {
          fieldBurst += e.key;
        } else {
          fieldTarget = target;
          fieldBurst = e.key;
        }
        fieldLastTs = now;
      } else if (e.key !== "Shift") {
        fieldBurst = "";
      }
    }, true);
  }
  document.addEventListener("DOMContentLoaded", () => {
    renderPosCart(false);
    initGlobalAnywhereScanner();
    if (IS_ADMIN) {
      loadOverviewReport();
    } else {
      document.getElementById("quickCodeInput")?.focus();
    }
  });
})();
