/**
 * ASLY | أصلي TECH STORE
 * Complete Dashboard Controller:
 * - Daily Sales & Net Profit Reports
 * - Camera 1D Elongated Barcode & 2D QR Scanner (Add Product + Quick Customer Price Check)
 * - Auto QR & Barcode Sticker Generator + Printer
 * - Multi-Item POS with Strict Floor-Price Enforcement + PNG/WhatsApp/Print Invoice
 * - Interactive QR Maintenance Stages + Email Triggers + Auto-Wipe on Delivery
 * - Staff Roles & Dynamic Contact Channels Manager
 */

(() => {
  const CFG = window.ASLY_CFG || {};
  const BASE = CFG.baseUrl || '';
  const IS_ADMIN = !!CFG.isAdmin;

  const apiUrl = (path) => `${BASE}/${path.replace(/^\/+/, '')}`;
  const fmtMoney = (n) => `${Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ج.م`;

  const esc = (str) => {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  };

  const normalizeWa = (phone) => {
    let d = String(phone || '').replace(/\D+/g, '');
    if (!d) return '';
    if (d.startsWith('00')) d = d.slice(2);
    if (d.startsWith('01') && d.length === 11) return '2' + d;
    return d;
  };

  function showToast(msg, isError = false) {
    const wrap = document.getElementById('toastContainer');
    if (!wrap) return;
    const t = document.createElement('div');
    t.className = 'toast' + (isError ? ' error' : '');
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 4200);
  }

  async function apiFetch(path, options = {}) {
    const headers = Object.assign({ 'X-CSRF-Token': CFG.csrfToken }, options.headers || {});
    if (options.json) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.json);
      delete options.json;
    }
    const res = await fetch(apiUrl(path), Object.assign({}, options, { headers }));
    const data = await res.json();
    if (!data.ok) {
      throw new Error(data.error || 'حدث خطأ أثناء تنفيذ العملية');
    }
    return data;
  }

  // Subtle scan beep using Web Audio API
  function playScanBeep() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, ctx.currentTime);
      gain.gain.setValueAtTime(0.08, ctx.currentTime);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.11);
    } catch (_) {}
  }

  // ============================================================
  // QR & 1D Barcode Rendering Helpers
  // ============================================================
  function renderQrInto(el, text, cellSize = 4) {
    if (!el) return;
    el.innerHTML = '';
    if (!text || typeof window.qrcode !== 'function') return;
    try {
      const qr = window.qrcode(0, 'M');
      qr.addData(String(text));
      qr.make();
      el.innerHTML = qr.createSvgTag({ cellSize, margin: 2, scalable: false });
    } catch (e) {
      el.textContent = text;
    }
  }

  function renderBarcodeInto(svgEl, code) {
    if (!svgEl) return;
    svgEl.innerHTML = '';
    if (!code || typeof window.JsBarcode !== 'function') return;
    try {
      window.JsBarcode(svgEl, String(code), {
        format: 'CODE128',
        lineColor: '#043538',
        width: 1.7,
        height: 40,
        displayValue: false,
        margin: 2,
      });
    } catch (_) {}
  }

  function printHtmlSnippet(title, bodyHtml) {
    const win = window.open('', '_blank', 'width=760,height=680');
    if (!win) {
      showToast('يرجى السماح بالنوافذ المنبثقة للطباعة', true);
      return;
    }
    win.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>${esc(title)}</title>
      <style>
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; margin: 16px; color: #043538; }
        @media print { body { margin: 4mm; } }
      </style>
    </head><body>${bodyHtml}<script>window.onload=function(){setTimeout(function(){window.print();},250);};<\/script></body></html>`);
    win.document.close();
  }

  // ============================================================
  // Modals & Tab Navigation
  // ============================================================
  window.openModal = (id) => {
    const m = document.getElementById(id);
    if (m) m.classList.add('open');
  };

  window.closeModal = (id) => {
    const m = document.getElementById(id);
    if (m) m.classList.remove('open');
  };

  const tabTitles = {
    'overview': IS_ADMIN ? 'لوحة المبيعات اليومية والأرباح الصافية' : 'ملخص المبيعات والتشغيل اليومي',
    'quick-scanner': 'كاشف السعر السريع بالكاميرا (الباركود المطاول والـ QR)',
    'pos': 'نقطة البيع وإصدار الفواتير الرسمية',
    'add-product': 'إضافة منتج جديد (مسح الكود أو توليد QR تلقائي)',
    'inventory': 'إدارة المخزون والأجهزة واستيكرات الأكواد',
    'maintenance': 'إدارة الصيانة التفاعلية بالـ QR والمراحل والإيميل',
    'staff': 'إدارة الموظفين وصلاحيات النظام',
    'settings': 'الهوية البصرية وقنوات التواصل وإعدادات البريد',
  };

  window.switchTab = async (tabName) => {
    await stopAllCameras();
    document.querySelectorAll('.side-btn').forEach((b) => {
      b.classList.toggle('active', b.dataset.tab === tabName);
    });
    document.querySelectorAll('.tab-panel').forEach((p) => {
      p.classList.toggle('active', p.id === `tab-${tabName}`);
    });
    const titleEl = document.getElementById('activePageTitle');
    if (titleEl && tabTitles[tabName]) titleEl.textContent = tabTitles[tabName];

    const sb = document.getElementById('appSidebar');
    if (sb) sb.classList.remove('open');

    if (tabName === 'overview') loadOverviewReport();
    if (tabName === 'pos') loadPosCatalog();
    if (tabName === 'inventory') loadInventoryTable();
    if (tabName === 'maintenance') loadMaintenanceList();
    if (tabName === 'staff' && IS_ADMIN) loadUsersList();
    if (tabName === 'settings' && IS_ADMIN) loadSettingsData();
  };

  document.querySelectorAll('.side-btn[data-tab]').forEach((btn) => {
    btn.addEventListener('click', () => window.switchTab(btn.dataset.tab));
  });

  const mobBtn = document.getElementById('btnMobileSidebar');
  if (mobBtn) {
    mobBtn.addEventListener('click', () => {
      document.getElementById('appSidebar')?.classList.toggle('open');
    });
  }

  // ============================================================
  // Camera Barcode & QR Scanner Manager (1D Elongated + 2D QR)
  // ============================================================
  let activeScanners = {};

  async function startCameraScanner(mountId, onScanSuccess) {
    await stopCameraScanner(mountId);
    if (typeof window.Html5Qrcode === 'undefined') {
      showToast('مكتبة الكاميرا غير محملة', true);
      return false;
    }

    const mount = document.getElementById(mountId);
    if (mount) mount.innerHTML = '';

    const formats = window.Html5QrcodeSupportedFormats
      ? [
          Html5QrcodeSupportedFormats.QR_CODE,
          Html5QrcodeSupportedFormats.CODE_128,
          Html5QrcodeSupportedFormats.EAN_13,
          Html5QrcodeSupportedFormats.EAN_8,
          Html5QrcodeSupportedFormats.UPC_A,
          Html5QrcodeSupportedFormats.UPC_E,
          Html5QrcodeSupportedFormats.CODE_39,
          Html5QrcodeSupportedFormats.ITF,
        ]
      : undefined;

    const html5Qr = new Html5Qrcode(mountId, formats ? { formatsToSupport: formats } : undefined);
    activeScanners[mountId] = html5Qr;

    let lastCode = '';
    let lastTime = 0;

    try {
      await html5Qr.start(
        { facingMode: 'environment' },
        { fps: 12, qrbox: { width: 250, height: 150 } },
        (decodedText) => {
          const now = Date.now();
          if (decodedText === lastCode && now - lastTime < 2200) return;
          lastCode = decodedText;
          lastTime = now;
          playScanBeep();
          onScanSuccess(decodedText.trim());
        },
        () => {}
      );
      return true;
    } catch (err) {
      delete activeScanners[mountId];
      if (mount) {
        mount.innerHTML = `<div style="padding:18px;color:#fca5a5;font-size:.84rem;">
          تعذر فتح الكاميرا مباشرة (${esc(err?.message || 'تأكد من صلاحية الكاميرا أو استخدام localhost / HTTPS')}).<br>
          يمكنك استخدام زر <strong>«مسح من صورة باركود»</strong> أو إدخال/مسح الكود بالخانة بالأسفل.
        </div>`;
      }
      return false;
    }
  }

  async function stopCameraScanner(mountId) {
    const inst = activeScanners[mountId];
    if (!inst) return;
    delete activeScanners[mountId];
    try {
      if (inst.isScanning) {
        await inst.stop();
      }
      inst.clear();
    } catch (_) {}
  }

  async function stopAllCameras() {
    for (const id of Object.keys(activeScanners)) {
      await stopCameraScanner(id);
    }
    const bStopQuick = document.getElementById('btnStopQuickCam');
    const bStartQuick = document.getElementById('btnStartQuickCam');
    if (bStopQuick) bStopQuick.style.display = 'none';
    if (bStartQuick) bStartQuick.style.display = 'inline-flex';

    const bStopAdd = document.getElementById('btnStopAddCam');
    const bStartAdd = document.getElementById('btnStartAddCam');
    if (bStopAdd) bStopAdd.style.display = 'none';
    if (bStartAdd) bStartAdd.style.display = 'inline-flex';

    const posCamWrap = document.getElementById('posCameraWrap');
    if (posCamWrap) posCamWrap.style.display = 'none';
  }

  // ============================================================
  // TAB 1: OVERVIEW & DAILY SALES / REVENUE REPORT
  // ============================================================
  let cachedInvoicesMap = {};

  async function loadOverviewReport() {
    const fromEl = document.getElementById('repDateFrom');
    const toEl = document.getElementById('repDateTo');
    const dFrom = fromEl ? fromEl.value : '';
    const dTo = toEl ? toEl.value : '';

    try {
      const data = await apiFetch(`api/pos.php?action=list&date_from=${encodeURIComponent(dFrom)}&date_to=${encodeURIComponent(dTo)}`);
      const s = data.summary;
      const kpiWrap = document.getElementById('overviewKpis');

      if (kpiWrap) {
        if (IS_ADMIN) {
          const cap = CFG.inventoryStats || {};
          kpiWrap.innerHTML = `
            <div class="kpi-card hero-kpi">
              <div class="kpi-label">إجمالي النقدية الداخلة (مبيعات + صيانة)</div>
              <div class="kpi-value">${fmtMoney(s.combined_revenue)}</div>
              <div class="kpi-sub">${s.invoices_count} فاتورة بيع + ${s.maintenance_delivered_count} جهاز صيانة مُسلّم</div>
            </div>
            <div class="kpi-card" style="border-color:var(--db-gold-500);background:var(--db-gold-50);">
              <div class="kpi-label" style="color:var(--db-gold-600);">صافي الربح الحقيقي للفترة</div>
              <div class="kpi-value" style="color:#065f46;">${fmtMoney(s.combined_net_profit)}</div>
              <div class="kpi-sub">ربح المبيعات: ${fmtMoney(s.sales_profit)} | الصيانة: ${fmtMoney(s.maintenance_profit)}</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">إيراد فواتير المبيعات</div>
              <div class="kpi-value">${fmtMoney(s.sales_revenue)}</div>
              <div class="kpi-sub">إجمالي القطع المباعة: ${s.items_sold_count} قطعة</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">إيراد الصيانة المسلمة</div>
              <div class="kpi-value">${fmtMoney(s.maintenance_revenue)}</div>
              <div class="kpi-sub">تم تسليم وحذف بيانات ${s.maintenance_delivered_count} جهاز</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">رأس مال البضاعة الحالية بالمخزن</div>
              <div class="kpi-value">${fmtMoney(cap.total_capital || 0)}</div>
              <div class="kpi-sub">${cap.total_units || 0} قطعة بالمخزن (${cap.total_skus || 0} صنف)</div>
            </div>
          `;
        } else {
          kpiWrap.innerHTML = `
            <div class="kpi-card hero-kpi">
              <div class="kpi-label">إجمالي الفواتير الصادرة بالفترة</div>
              <div class="kpi-value">${fmtMoney(s.sales_revenue)}</div>
              <div class="kpi-sub">${s.invoices_count} فاتورة • ${s.items_sold_count} قطعة مباعة</div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">كاشف السعر السريع</div>
              <div class="kpi-value" style="font-size:1.15rem;">فحص بالكاميرا</div>
              <div class="kpi-sub"><button class="btn btn-gold btn-sm" onclick="switchTab('quick-scanner')">فتح الاسكانر الآن</button></div>
            </div>
            <div class="kpi-card">
              <div class="kpi-label">نقطة البيع والفواتير</div>
              <div class="kpi-value" style="font-size:1.15rem;">بيع وإصدار فاتورة</div>
              <div class="kpi-sub"><button class="btn btn-teal btn-sm" onclick="switchTab('pos')">فتح نقطة البيع</button></div>
            </div>
          `;
        }
      }

      const invBody = document.getElementById('dailyInvoicesBody');
      cachedInvoicesMap = {};
      if (invBody) {
        if (!data.invoices.length) {
          invBody.innerHTML = `<tr><td colspan="${IS_ADMIN ? 9 : 7}" style="text-align:center;padding:26px;color:var(--db-muted);">لا توجد فواتير مبيعات مسجلة في هذه الفترة.</td></tr>`;
        } else {
          invBody.innerHTML = data.invoices.map((inv) => {
            cachedInvoicesMap[inv.id] = inv;
            const itemsSummary = (inv.items || []).map((i) => `${esc(i.product_name)} (×${i.quantity})`).join(' + ');
            return `
              <tr>
                <td style="direction:ltr;font-weight:800;">${esc(inv.invoice_number)}</td>
                <td style="direction:ltr;font-size:.8rem;">${esc(inv.created_at)}</td>
                <td>
                  <strong>${esc(inv.customer_name)}</strong>
                  ${inv.customer_phone ? `<div style="font-size:.76rem;color:var(--db-muted);direction:ltr;">${esc(inv.customer_phone)}</div>` : ''}
                </td>
                <td style="max-width:260px;font-size:.84rem;">${itemsSummary}</td>
                <td>${esc(inv.sold_by_name)}</td>
                <td style="font-weight:900;color:var(--db-teal-900);">${fmtMoney(inv.total_amount)}</td>
                ${IS_ADMIN ? `
                  <td style="color:var(--db-muted);">${fmtMoney(inv.total_cost)}</td>
                  <td style="font-weight:800;color:#059669;">+${fmtMoney(inv.total_profit)}</td>
                ` : ''}
                <td>
                  <button type="button" class="btn btn-gold btn-sm" onclick="openSavedInvoice(${inv.id})">عرض / صورة / واتساب</button>
                </td>
              </tr>
            `;
          }).join('');
        }
      }

      if (IS_ADMIN) {
        const mRevBody = document.getElementById('dailyMaintRevBody');
        if (mRevBody) {
          if (!data.maintenance_revenues.length) {
            mRevBody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--db-muted);">لا توجد أجهزة صيانة مسلمة في هذه الفترة.</td></tr>`;
          } else {
            mRevBody.innerHTML = data.maintenance_revenues.map((mr) => `
              <tr>
                <td style="direction:ltr;font-weight:800;">${esc(mr.ticket_code_ref)}</td>
                <td style="direction:ltr;">${esc(mr.revenue_date)}</td>
                <td>${esc(mr.device_category)}</td>
                <td style="font-weight:900;color:var(--db-teal-900);">${fmtMoney(mr.amount_paid)}</td>
                <td>${fmtMoney(mr.parts_cost)}</td>
                <td style="font-weight:800;color:#059669;">+${fmtMoney(mr.net_profit)}</td>
                <td>${esc(mr.payment_method)}</td>
                <td>${esc(mr.delivered_by_name || '—')}</td>
              </tr>
            `).join('');
          }
        }
      }
    } catch (err) {
      showToast(err.message, true);
    }
  }

  document.getElementById('btnLoadReport')?.addEventListener('click', loadOverviewReport);
  document.querySelectorAll('.js-date-preset').forEach((btn) => {
    btn.addEventListener('click', () => {
      const now = new Date();
      const iso = (d) => d.toISOString().slice(0, 10);
      let from = iso(now);
      let to = iso(now);
      if (btn.dataset.preset === 'yesterday') {
        const y = new Date(now);
        y.setDate(y.getDate() - 1);
        from = to = iso(y);
      } else if (btn.dataset.preset === 'month') {
        from = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
      }
      document.getElementById('repDateFrom').value = from;
      document.getElementById('repDateTo').value = to;
      loadOverviewReport();
    });
  });

  // ============================================================
  // TAB 2: QUICK PRICE CHECKER (IN FRONT OF CUSTOMER)
  // ============================================================
  let lastQuickProduct = null;

  async function lookupQuickPrice(code) {
    if (!code) return;
    const resWrap = document.getElementById('quickScanResult');
    try {
      const data = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
      if (data.is_maintenance_ticket && data.ticket) {
        resWrap.innerHTML = `
          <div class="price-reveal-card">
            <span class="role-pill role-admin" style="margin-bottom:10px;">🛠️ تذكرة صيانة ممسوحة</span>
            <h3 style="margin:6px 0;">${esc(data.ticket.device_model)} (${esc(data.ticket.ticket_code)})</h3>
            <p>العميل: <strong>${esc(data.ticket.customer_name)}</strong></p>
            <button type="button" class="btn btn-gold" onclick="switchTab('maintenance'); setTimeout(()=>openMaintTicket(${data.ticket.id}), 250);">فتح ملف الصيانة الآن</button>
          </div>
        `;
        return;
      }

      if (!data.found) {
        resWrap.innerHTML = `
          <div class="card" style="border-color:#fca5a5;text-align:center;padding:34px;">
            <h3 style="color:#b91c1c;margin:0 0 8px;">لم يتم العثور على منتج بالكود: <span style="direction:ltr;">${esc(data.scanned_code)}</span></h3>
            <p style="color:var(--db-muted);margin:0 0 16px;">هذا الكود غير مسجل بالمخزن حتى الآن.</p>
            ${IS_ADMIN ? `<button type="button" class="btn btn-gold" onclick="prefillNewProductCode('${esc(data.scanned_code)}')">➕ تسجيل كمنتج جديد بهذا الكود</button>` : ''}
          </div>
        `;
        return;
      }

      const p = data.product;
      lastQuickProduct = p;
      const img = p.image_path ? apiUrl(p.image_path) : apiUrl('assets/brand/official-banner.png');

      resWrap.innerHTML = `
        <div class="price-reveal-card">
          <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <img src="${esc(img)}" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:14px;border:1.5px solid var(--db-border);">
            <div style="flex:1;min-width:180px;">
              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:4px;">
                <span style="padding:2px 10px;border-radius:999px;background:var(--db-teal-50);color:var(--db-teal-900);font-size:.76rem;font-weight:800;">${esc(p.category_label)}</span>
                <span style="padding:2px 10px;border-radius:999px;background:var(--db-gold-50);color:var(--db-gold-600);font-size:.76rem;font-weight:800;">${esc(p.condition_label)}</span>
                <span style="padding:2px 10px;border-radius:999px;background:${p.quantity > 0 ? '#ecfdf5' : '#fef2f2'};color:${p.quantity > 0 ? '#065f46' : '#b91c1c'};font-size:.76rem;font-weight:800;">
                  المتاح بالمخزن: ${p.quantity}
                </span>
              </div>
              <h3 style="margin:0 0 4px;font-size:1.25rem;color:var(--db-teal-900);">${esc(p.name)}</h3>
              <div style="font-size:.82rem;color:var(--db-muted);">${esc(p.specs || '')}</div>
            </div>
          </div>

          <div class="big-sell-price-box">
            <div class="lbl">سعر البيع للعميل</div>
            <div class="val">${fmtMoney(p.sell_price)}</div>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
            <button type="button" class="btn btn-ghost" id="btnShowDecoyCost" style="flex:1;min-width:180px;">
              👁️ اعرض سعر الشراء
            </button>
            ${p.quantity > 0 ? `
              <button type="button" class="btn btn-gold" id="btnQuickSellNow" style="flex:1;min-width:180px;">
                🛒 بيع هذه القطعة وإصدار فاتورة
              </button>
            ` : ''}
          </div>

          <!-- Decoy Box: Shows min_sell_price when clicking "اعرض سعر الشراء"! -->
          <div class="decoy-cost-box" id="decoyCostBox">
            <div style="font-size:.8rem;color:var(--db-gold-600);font-weight:700;">سعر التكلفة المسجل بالنظام</div>
            <div style="font-size:1.65rem;font-weight:900;color:var(--db-teal-900);margin-top:2px;">${fmtMoney(p.display_floor_price)}</div>
          </div>
        </div>
      `;

      document.getElementById('btnShowDecoyCost')?.addEventListener('click', (ev) => {
        const box = document.getElementById('decoyCostBox');
        if (!box) return;
        const open = box.classList.toggle('visible');
        ev.currentTarget.textContent = open ? '🙈 إخفاء السعر' : '👁️ اعرض سعر الشراء';
      });

      document.getElementById('btnQuickSellNow')?.addEventListener('click', () => {
        if (lastQuickProduct) {
          addToPosCart(lastQuickProduct);
          window.switchTab('pos');
        }
      });
    } catch (err) {
      showToast(err.message, true);
    }
  }

  window.prefillNewProductCode = (code) => {
    window.switchTab('add-product');
    const inp = document.getElementById('prodBarcode');
    if (inp) {
      inp.value = code;
      updateLiveStickerPreview();
    }
  };

  document.getElementById('btnStartQuickCam')?.addEventListener('click', async () => {
    const ok = await startCameraScanner('quickCameraReader', (code) => {
      document.getElementById('quickCodeInput').value = code;
      lookupQuickPrice(code);
    });
    if (ok) {
      document.getElementById('btnStartQuickCam').style.display = 'none';
      document.getElementById('btnStopQuickCam').style.display = 'inline-flex';
    }
  });

  document.getElementById('btnStopQuickCam')?.addEventListener('click', () => stopAllCameras());

  document.getElementById('quickScanFile')?.addEventListener('change', async (e) => {
    const file = e.target.files?.[0];
    if (!file || typeof window.Html5Qrcode === 'undefined') return;
    try {
      const tempScanner = new Html5Qrcode('quickCameraReader');
      const decoded = await tempScanner.scanFile(file, true);
      playScanBeep();
      document.getElementById('quickCodeInput').value = decoded;
      lookupQuickPrice(decoded);
    } catch (_) {
      showToast('لم يتم التعرف على باركود واضح في الصورة المختارة', true);
    }
    e.target.value = '';
  });

  document.getElementById('quickManualForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    lookupQuickPrice(document.getElementById('quickCodeInput').value.trim());
  });

  // ============================================================
  // TAB 3: POS CART, MINIMUM PRICE VALIDATION & INVOICE EXPORT
  // ============================================================
  let posProductsCache = [];
  let posCart = [];
  let activeInvoiceData = null;

  async function loadPosCatalog() {
    try {
      const q = document.getElementById('posSearchInput')?.value.trim() || '';
      const cat = document.getElementById('posCatFilter')?.value || 'all';
      const data = await apiFetch(`api/products.php?in_stock=1&category=${encodeURIComponent(cat)}&q=${encodeURIComponent(q)}`);
      posProductsCache = data.products || [];
      renderPosGrid();
    } catch (err) {
      showToast(err.message, true);
    }
  }

  function renderPosGrid() {
    const grid = document.getElementById('posProductsGrid');
    if (!grid) return;
    if (!posProductsCache.length) {
      grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--db-muted);">لا توجد منتجات متاحة بالمخزن مطابقة للبحث.</div>`;
      return;
    }
    grid.innerHTML = posProductsCache.map((p) => {
      const img = p.image_path ? apiUrl(p.image_path) : apiUrl('assets/brand/official-banner.png');
      return `
        <div class="pos-prod-tile" data-pid="${p.id}">
          <img src="${esc(img)}" alt="">
          <strong style="font-size:.86rem;color:var(--db-teal-900);line-height:1.3;">${esc(p.name)}</strong>
          <div style="display:flex;justify-content:space-between;font-size:.76rem;color:var(--db-muted);">
            <span>متاح: ${p.quantity}</span>
            <span style="direction:ltr;">${esc(p.barcode)}</span>
          </div>
          <div style="margin-top:auto;padding-top:6px;border-top:1px dashed var(--db-border);display:flex;justify-content:space-between;align-items:center;">
            <strong style="color:var(--db-teal-800);font-size:.9rem;">${fmtMoney(p.sell_price)}</strong>
            <span style="font-size:.72rem;color:var(--db-gold-600);font-weight:800;">+ أضف</span>
          </div>
        </div>
      `;
    }).join('');

    grid.querySelectorAll('.pos-prod-tile').forEach((tile) => {
      tile.addEventListener('click', () => {
        const pid = Number(tile.dataset.pid);
        const prod = posProductsCache.find((x) => x.id === pid);
        if (prod) addToPosCart(prod);
      });
    });
  }

  function addToPosCart(product) {
    const existing = posCart.find((i) => i.product_id === product.id);
    if (existing) {
      if (existing.quantity + 1 > product.quantity) {
        showToast(`أقصى كمية متاحة من "${product.name}" هي ${product.quantity}`, true);
        return;
      }
      existing.quantity += 1;
    } else {
      posCart.push({
        product_id: product.id,
        name: product.name,
        barcode: product.barcode,
        max_qty: product.quantity,
        quantity: 1,
        sell_price: Number(product.sell_price),
        min_sell_price: Number(product.min_sell_price),
        unit_sold_price: Number(product.sell_price),
        serial_imei: '',
        warranty_text: product.warranty_default || 'ضمان الفحص والتجربة - أصلي تك ستور',
      });
    }
    renderPosCart();
    showToast(`تمت إضافة "${product.name}" إلى الفاتورة`);
  }

  function renderPosCart() {
    const wrap = document.getElementById('posCartItemsList');
    const badge = document.getElementById('posCartBadge');
    const totalEl = document.getElementById('posTotalDisplay');

    const totalItems = posCart.reduce((acc, i) => acc + i.quantity, 0);
    if (badge) {
      badge.style.display = totalItems > 0 ? 'inline-block' : 'none';
      badge.textContent = String(totalItems);
    }

    if (!wrap) return;
    if (!posCart.length) {
      wrap.innerHTML = `<div style="text-align:center;padding:28px 16px;background:#f8fbfb;border-radius:12px;border:1px dashed var(--db-border);color:var(--db-muted);font-size:.88rem;">السلة فارغة — اضغط على أي منتج من القائمة أو امسح الباركود لإضافته للفاتورة (سواء منتج واحد أو عدة منتجات).</div>`;
      if (totalEl) totalEl.textContent = '0.00 ج.م';
      return;
    }

    let grandTotal = 0;
    wrap.innerHTML = posCart.map((item, idx) => {
      const belowMin = item.unit_sold_price + 0.001 < item.min_sell_price;
      const sub = item.unit_sold_price * item.quantity;
      grandTotal += sub;
      return `
        <div class="pos-cart-item">
          <div style="display:flex;justify-content:space-between;align-items:start;gap:8px;margin-bottom:8px;">
            <div>
              <strong style="color:var(--db-teal-900);font-size:.93rem;">${esc(item.name)}</strong>
              <div style="font-size:.75rem;color:var(--db-muted);">سعر العرض: ${fmtMoney(item.sell_price)} | <strong style="color:#065f46;">أقل سعر مسموح: ${fmtMoney(item.min_sell_price)}</strong></div>
            </div>
            <button type="button" class="btn btn-danger btn-sm" data-rm="${idx}">حذف</button>
          </div>

          <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;margin-bottom:8px;">
            <div>
              <label style="font-size:.75rem;font-weight:700;">الكمية (متاح ${item.max_qty})</label>
              <input type="number" min="1" max="${item.max_qty}" value="${item.quantity}" data-qty="${idx}" class="form-control" style="padding:7px 10px;">
            </div>
            <div>
              <label style="font-size:.75rem;font-weight:700;">السعر المتفق عليه للقطعة *</label>
              <input type="number" step="0.01" min="${item.min_sell_price}" value="${item.unit_sold_price}" data-price="${idx}" class="form-control ${belowMin ? 'price-error' : ''}" style="padding:7px 10px;font-weight:800;">
            </div>
          </div>

          ${belowMin ? `<div style="color:#dc2626;font-size:.78rem;font-weight:800;margin-bottom:6px;">⛔ غير مسموح بالبيع بأقل من الحد الأدنى (${fmtMoney(item.min_sell_price)})!</div>` : ''}

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <input type="text" placeholder="السيريال / IMEI (اختياري)" value="${esc(item.serial_imei)}" data-sn="${idx}" class="form-control" style="padding:6px 10px;font-size:.8rem;">
            <input type="text" placeholder="الضمان بالفاتورة" value="${esc(item.warranty_text)}" data-war="${idx}" class="form-control" style="padding:6px 10px;font-size:.8rem;">
          </div>
        </div>
      `;
    }).join('');

    if (totalEl) totalEl.textContent = fmtMoney(grandTotal);

    wrap.querySelectorAll('[data-rm]').forEach((b) => {
      b.addEventListener('click', () => {
        posCart.splice(Number(b.dataset.rm), 1);
        renderPosCart();
      });
    });

    wrap.querySelectorAll('[data-qty]').forEach((inp) => {
      inp.addEventListener('change', () => {
        const it = posCart[Number(inp.dataset.qty)];
        let v = parseInt(inp.value, 10) || 1;
        if (v < 1) v = 1;
        if (v > it.max_qty) {
          v = it.max_qty;
          showToast(`أقصى كمية متاحة هي ${it.max_qty}`, true);
        }
        it.quantity = v;
        renderPosCart();
      });
    });

    wrap.querySelectorAll('[data-price]').forEach((inp) => {
      inp.addEventListener('input', () => {
        const idx = Number(inp.dataset.price);
        const it = posCart[idx];
        it.unit_sold_price = parseFloat(inp.value) || 0;
        const below = it.unit_sold_price + 0.001 < it.min_sell_price;
        inp.classList.toggle('price-error', below);
        const totalSum = posCart.reduce((a, c) => a + c.unit_sold_price * c.quantity, 0);
        if (totalEl) totalEl.textContent = fmtMoney(totalSum);
      });
      inp.addEventListener('blur', () => renderPosCart());
    });

    wrap.querySelectorAll('[data-sn]').forEach((inp) => {
      inp.addEventListener('input', () => { posCart[Number(inp.dataset.sn)].serial_imei = inp.value; });
    });

    wrap.querySelectorAll('[data-war]').forEach((inp) => {
      inp.addEventListener('input', () => { posCart[Number(inp.dataset.war)].warranty_text = inp.value; });
    });
  }

  document.getElementById('posSearchInput')?.addEventListener('input', loadPosCatalog);
  document.getElementById('posSearchInput')?.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const val = e.target.value.trim();
      if (!val) return;
      // Try exact barcode scan into cart
      try {
        const res = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(val)}`);
        if (res.found && res.product) {
          addToPosCart(res.product);
          e.target.value = '';
          loadPosCatalog();
        }
      } catch (_) {}
    }
  });
  document.getElementById('posCatFilter')?.addEventListener('change', loadPosCatalog);
  document.getElementById('btnClearCart')?.addEventListener('click', () => {
    posCart = [];
    renderPosCart();
  });

  document.getElementById('btnPosToggleCam')?.addEventListener('click', async () => {
    const wrap = document.getElementById('posCameraWrap');
    if (!wrap) return;
    wrap.style.display = 'block';
    await startCameraScanner('posCameraReader', async (code) => {
      try {
        const res = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
        if (res.found && res.product) {
          addToPosCart(res.product);
        } else {
          showToast(`الكود (${code}) غير مسجل بالمخزن`, true);
        }
      } catch (err) {
        showToast(err.message, true);
      }
    });
  });

  document.getElementById('btnPosCloseCam')?.addEventListener('click', () => stopAllCameras());

  document.getElementById('btnCompleteSale')?.addEventListener('click', async () => {
    if (!posCart.length) {
      showToast('أضف منتجاً واحداً على الأقل لإصدار الفاتورة', true);
      return;
    }
    const invalidItem = posCart.find((i) => i.unit_sold_price + 0.001 < i.min_sell_price);
    if (invalidItem) {
      showToast(`مرفوض! سعر "${invalidItem.name}" أقل من الحد الأدنى المسموح (${fmtMoney(invalidItem.min_sell_price)})`, true);
      renderPosCart();
      return;
    }

    const btn = document.getElementById('btnCompleteSale');
    btn.disabled = true;
    btn.textContent = 'جاري حفظ البيعة وإصدار الفاتورة...';

    try {
      const res = await apiFetch('api/pos.php', {
        method: 'POST',
        json: {
          action: 'checkout',
          customer_name: document.getElementById('posCustomerName').value.trim(),
          customer_phone: document.getElementById('posCustomerPhone').value.trim(),
          payment_method: document.getElementById('posPaymentMethod').value,
          notes: document.getElementById('posInvoiceNotes').value.trim(),
          items: posCart,
        },
      });

      showToast(res.message);
      posCart = [];
      document.getElementById('posCustomerName').value = '';
      document.getElementById('posCustomerPhone').value = '';
      document.getElementById('posInvoiceNotes').value = '';
      renderPosCart();
      loadPosCatalog();
      openInvoicePreviewModal(res.invoice, res.items);
    } catch (err) {
      showToast(err.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = '✅ إتمام البيع وإصدار الفاتورة (صورة + واتساب + طباعة)';
    }
  });

  // Render Branded Invoice HTML inside Modal for PNG / WhatsApp / Print
  function openInvoicePreviewModal(invoice, items) {
    activeInvoiceData = { invoice, items };
    const st = CFG.store || {};
    const payLabels = {
      cash: 'نقدي (كاش)',
      vodafone_cash: 'فودافون كاش / محفظة',
      instapay: 'إنستا باي (InstaPay)',
      card: 'بطاقة بنكية',
    };

    const rowsHtml = (items || []).map((it, idx) => `
      <tr style="background:${idx % 2 === 1 ? '#f8fbfb' : '#ffffff'};">
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;">${idx + 1}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;">
          <strong>${esc(it.product_name)}</strong>
          ${it.product_barcode ? `<div style="font-size:11px;color:#5c7678;direction:ltr;text-align:right;">${esc(it.product_barcode)}</div>` : ''}
        </td>
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;font-size:12px;">
          ${it.serial_imei ? `<div>S/N: ${esc(it.serial_imei)}</div>` : ''}
          <div style="color:#065f46;font-weight:700;">${esc(it.warranty_text || '')}</div>
        </td>
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;">${it.quantity}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;">${fmtMoney(it.unit_sold_price)}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #e3eded;font-weight:800;">${fmtMoney(it.subtotal)}</td>
      </tr>
    `).join('');

    const capture = document.getElementById('modalInvoiceCapture');
    capture.innerHTML = `
      <div style="background:linear-gradient(135deg,#043538,#085a5e);color:#fff;padding:22px 26px;border-bottom:4px solid #d49b27;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
        <div>
          <div style="font-size:24px;font-weight:900;letter-spacing:1px;">ASLY <span style="color:#f2c75c;">|</span> أصلي</div>
          <div style="font-size:11px;color:#f2c75c;letter-spacing:4px;">TECH STORE</div>
          <div style="font-size:12px;color:#d6ecec;margin-top:4px;">${esc(st.address || '')}</div>
        </div>
        <div style="text-align:left;">
          <div style="font-size:11px;color:#f2c75c;font-weight:800;">فاتورة مبيعات معتمدة</div>
          <div style="font-size:19px;font-weight:900;direction:ltr;">${esc(invoice.invoice_number)}</div>
          <div style="font-size:12px;color:#d6ecec;direction:ltr;">${esc(invoice.created_at)}</div>
        </div>
      </div>

      <div style="padding:22px 26px;color:#0d2628;">
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;background:#f3f9f9;padding:14px 16px;border-radius:10px;border:1px solid #dceaea;font-size:13px;margin-bottom:16px;">
          <div><strong>العميل:</strong> ${esc(invoice.customer_name)}</div>
          <div><strong>الهاتف:</strong> <span style="direction:ltr;display:inline-block;">${esc(invoice.customer_phone || '—')}</span></div>
          <div><strong>طريقة الدفع:</strong> ${esc(payLabels[invoice.payment_method] || invoice.payment_method)}</div>
          <div><strong>مسؤول المبيعات:</strong> ${esc(invoice.sold_by_name)}</div>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;">
          <thead>
            <tr style="background:#043538;color:#f2c75c;">
              <th style="padding:10px 12px;text-align:right;">#</th>
              <th style="padding:10px 12px;text-align:right;">المنتج / البيان</th>
              <th style="padding:10px 12px;text-align:right;">السيريال / الضمان</th>
              <th style="padding:10px 12px;text-align:right;">الكمية</th>
              <th style="padding:10px 12px;text-align:right;">سعر الوحدة</th>
              <th style="padding:10px 12px;text-align:right;">الإجمالي</th>
            </tr>
          </thead>
          <tbody>${rowsHtml}</tbody>
        </table>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;padding-top:14px;border-top:2px solid #043538;">
          <div style="max-width:410px;font-size:12px;color:#486365;">
            ${invoice.notes ? `<div style="margin-bottom:4px;"><strong>ملاحظات:</strong> ${esc(invoice.notes)}</div>` : ''}
            <div>🛡️ ${esc(st.footerNote || '')}</div>
          </div>
          <div style="background:#043538;color:#fff;padding:12px 22px;border-radius:10px;border:1.5px solid #d49b27;text-align:center;">
            <div style="font-size:11px;color:#f2c75c;">الإجمالي المدفوع</div>
            <div style="font-size:21px;font-weight:900;">${fmtMoney(invoice.total_amount)}</div>
          </div>
        </div>

        <div style="margin-top:16px;padding-top:10px;border-top:1px dashed #cbdcdc;display:flex;justify-content:space-between;font-size:12px;color:#526d70;">
          <span><strong>${esc(st.nameAr)} (${esc(st.nameEn)})</strong></span>
          <span style="direction:ltr;font-weight:800;color:#043538;">📞 ${esc(st.primaryPhone)}${st.secondaryPhone ? ' | ' + esc(st.secondaryPhone) : ''}</span>
        </div>
      </div>
    `;

    window.openModal('invoiceModal');
  }

  window.openSavedInvoice = async (id) => {
    try {
      const res = await apiFetch(`api/pos.php?action=invoice&id=${id}`);
      openInvoicePreviewModal(res.invoice, res.items);
    } catch (err) {
      showToast(err.message, true);
    }
  };

  async function generateInvoiceCanvas() {
    const el = document.getElementById('modalInvoiceCapture');
    return await window.html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
  }

  document.getElementById('btnModalInvoicePng')?.addEventListener('click', async (e) => {
    if (!activeInvoiceData) return;
    const btn = e.currentTarget;
    btn.disabled = true;
    btn.textContent = 'جاري توليد الصورة...';
    try {
      const canvas = await generateInvoiceCanvas();
      const a = document.createElement('a');
      a.download = `${activeInvoiceData.invoice.invoice_number}.png`;
      a.href = canvas.toDataURL('image/png');
      a.click();
      showToast('تم تحميل صورة الفاتورة عالية الدقة بنجاح!');
    } finally {
      btn.disabled = false;
      btn.textContent = '📥 تحميل الفاتورة كصورة (PNG)';
    }
  });

  document.getElementById('btnModalInvoiceWhatsapp')?.addEventListener('click', async (e) => {
    if (!activeInvoiceData) return;
    const { invoice, items } = activeInvoiceData;
    const btn = e.currentTarget;
    btn.disabled = true;
    btn.textContent = 'جاري تجهيز الفاتورة لواتساب...';

    try {
      const canvas = await generateInvoiceCanvas();
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
      const file = new File([blob], `${invoice.invoice_number}.png`, { type: 'image/png' });

      const lines = (items || []).map((i) => `• ${i.product_name} (×${i.quantity}) — ${fmtMoney(i.subtotal)}`).join('\n');
      const textMsg =
        `مرحباً أستاذ/ ${invoice.customer_name} 👋\n` +
        `شكراً لتشريفكم *${CFG.store.nameAr} (${CFG.store.nameEn})*\n\n` +
        `🧾 *فاتورة رقم:* ${invoice.invoice_number}\n` +
        `${lines}\n` +
        `💰 *الإجمالي المدفوع:* ${fmtMoney(invoice.total_amount)}\n\n` +
        `🔗 رابط الفاتورة الرقمية للتحميل كصورة أو الطباعة:\n${invoice.public_url}`;

      // If running on mobile with native file share support, allow sharing the PNG directly!
      if (navigator.canShare && navigator.canShare({ files: [file] }) && !invoice.customer_phone) {
        await navigator.share({ title: `فاتورة ${invoice.invoice_number}`, text: textMsg, files: [file] });
      } else {
        // Auto-save the PNG image so cashier can also drop it in chat + open direct WhatsApp chat to customer's number!
        const a = document.createElement('a');
        a.download = `${invoice.invoice_number}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();

        let waPhone = normalizeWa(invoice.customer_phone || '');
        if (!waPhone) {
          const entered = prompt('أدخل رقم واتساب العميل لإرسال الفاتورة مباشرة:', '010');
          waPhone = normalizeWa(entered || '');
        }
        const waUrl = waPhone
          ? `https://wa.me/${waPhone}?text=${encodeURIComponent(textMsg)}`
          : `https://wa.me/?text=${encodeURIComponent(textMsg)}`;
        window.open(waUrl, '_blank');
        showToast('تم تحميل صورة الفاتورة وفتح محادثة واتساب العميل!');
      }
    } catch (err) {
      showToast('تعذر المشاركة: ' + err.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = '💬 إرسال الفاتورة للعميل عبر واتساب';
    }
  });

  document.getElementById('btnModalInvoicePrint')?.addEventListener('click', () => {
    const el = document.getElementById('modalInvoiceCapture');
    if (el && activeInvoiceData) {
      printHtmlSnippet(`فاتورة ${activeInvoiceData.invoice.invoice_number}`, el.outerHTML);
    }
  });

  // ============================================================
  // TAB 4: ADD / EDIT PRODUCT + SCANNER + AUTO QR (ADMIN ONLY)
  // ============================================================
  function updateLiveStickerPreview() {
    const code = document.getElementById('prodBarcode')?.value.trim() || '';
    const name = document.getElementById('prodName')?.value.trim() || 'منتج أصلي تك ستور';
    const nameEl = document.getElementById('stickerProdName');
    const txtEl = document.getElementById('stickerCodeText');
    if (nameEl) nameEl.textContent = name;
    if (txtEl) txtEl.textContent = code || '—';
    renderQrInto(document.getElementById('stickerQrMount'), code, 3);
    renderBarcodeInto(document.getElementById('stickerBarcodeSvg'), code);
  }

  function updateLiveProfitHint() {
    const hint = document.getElementById('liveProfitHint');
    if (!hint) return;
    const buy = parseFloat(document.getElementById('prodPurchasePrice')?.value) || 0;
    const sell = parseFloat(document.getElementById('prodSellPrice')?.value) || 0;
    const minSell = parseFloat(document.getElementById('prodMinSellPrice')?.value) || 0;
    if (sell > 0) {
      const pNormal = sell - buy;
      const pMin = minSell - buy;
      hint.innerHTML = `📊 ربح البيع الأساسي: <strong>${fmtMoney(pNormal)}</strong> | صافي الربح عند أقل سعر مسموح: <strong>${fmtMoney(pMin)}</strong>`;
    } else {
      hint.textContent = '';
    }
  }

  if (IS_ADMIN) {
    ['prodBarcode', 'prodName'].forEach((id) => {
      document.getElementById(id)?.addEventListener('input', updateLiveStickerPreview);
    });
    ['prodPurchasePrice', 'prodSellPrice', 'prodMinSellPrice'].forEach((id) => {
      document.getElementById(id)?.addEventListener('input', updateLiveProfitHint);
    });

    document.getElementById('btnGenerateAutoQr')?.addEventListener('click', async () => {
      try {
        const cat = document.getElementById('prodCategory').value;
        const res = await apiFetch(`api/scanner.php?action=generate_code&category=${encodeURIComponent(cat)}`);
        document.getElementById('prodBarcode').value = res.code;
        document.getElementById('prodCodeType').value = 'qr';
        document.getElementById('existingProdAlert').style.display = 'none';
        updateLiveStickerPreview();
        showToast(`تم توليد رمز QR جديد: ${res.code}`);
      } catch (err) {
        showToast(err.message, true);
      }
    });

    document.getElementById('btnStartAddCam')?.addEventListener('click', async () => {
      const ok = await startCameraScanner('addProdCameraReader', async (code) => {
        document.getElementById('prodBarcode').value = code;
        document.getElementById('prodCodeType').value = code.startsWith('ASLY-') ? 'qr' : 'barcode';
        updateLiveStickerPreview();
        // Check if this barcode already exists in DB!
        try {
          const check = await apiFetch(`api/scanner.php?action=lookup&code=${encodeURIComponent(code)}`);
          const alertEl = document.getElementById('existingProdAlert');
          if (check.found && check.product) {
            alertEl.style.display = 'block';
            alertEl.innerHTML = `🔔 هذا الكود مسجل مسبقاً للمنتج: <strong>${esc(check.product.name)}</strong> (الكمية الحالية: ${check.product.quantity})
              <button type="button" class="btn btn-teal btn-sm" style="margin-right:8px;" id="btnLoadScannedProd">تحميل بياناته للتعديل أو زيادة الكمية</button>`;
            document.getElementById('btnLoadScannedProd')?.addEventListener('click', () => populateProductForm(check.product));
          } else {
            alertEl.style.display = 'none';
            showToast(`تم التقاط الكود (${code}) — أدخل بيانات المنتج وأسعاره للحفظ`);
          }
        } catch (_) {}
      });
      if (ok) {
        document.getElementById('btnStartAddCam').style.display = 'none';
        document.getElementById('btnStopAddCam').style.display = 'inline-flex';
      }
    });

    document.getElementById('btnStopAddCam')?.addEventListener('click', () => stopAllCameras());

    document.getElementById('btnPrintCurrentSticker')?.addEventListener('click', () => {
      const code = document.getElementById('prodBarcode').value.trim();
      if (!code) {
        showToast('ولّد أو امسح كود المنتج أولاً لطباعة الاستيكر', true);
        return;
      }
      printHtmlSnippet(`استيكر ${code}`, `<div style="text-align:center;width:240px;padding:10px;border:1px solid #000;border-radius:8px;">${document.getElementById('liveStickerBox').innerHTML}</div>`);
    });

    function resetProductForm() {
      document.getElementById('productSaveForm').reset();
      document.getElementById('prodId').value = '0';
      document.getElementById('productFormHeading').textContent = '➕ تسجيل بيانات المنتج والأسعار الثلاثة';
      document.getElementById('existingProdAlert').style.display = 'none';
      document.getElementById('liveProfitHint').textContent = '';
      updateLiveStickerPreview();
    }

    window.populateProductForm = (p) => {
      document.getElementById('prodId').value = p.id;
      document.getElementById('prodBarcode').value = p.barcode || '';
      document.getElementById('prodCodeType').value = p.code_type || 'qr';
      document.getElementById('prodName').value = p.name || '';
      document.getElementById('prodCategory').value = p.category || 'laptops';
      document.getElementById('prodCondition').value = p.condition_status || 'new';
      document.getElementById('prodBrand').value = p.brand || '';
      document.getElementById('prodPurchasePrice').value = p.purchase_price ?? 0;
      document.getElementById('prodSellPrice').value = p.sell_price ?? 0;
      document.getElementById('prodMinSellPrice').value = p.min_sell_price ?? 0;
      document.getElementById('prodQuantity').value = p.quantity ?? 1;
      document.getElementById('prodLowStock').value = p.low_stock_threshold ?? 2;
      document.getElementById('prodWarranty').value = p.warranty_default || '';
      document.getElementById('prodSpecs').value = p.specs || '';
      document.getElementById('prodDescription').value = p.description || '';
      document.getElementById('prodShowLanding').checked = Number(p.show_on_landing) === 1;
      document.getElementById('prodFeatured').checked = Number(p.is_featured) === 1;
      document.getElementById('productFormHeading').textContent = `✏️ تعديل بيانات المنتج: ${p.name}`;
      document.getElementById('existingProdAlert').style.display = 'none';
      updateLiveStickerPreview();
      updateLiveProfitHint();
    };

    document.getElementById('btnResetProductForm')?.addEventListener('click', resetProductForm);

    document.getElementById('productSaveForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.currentTarget;
      const fd = new FormData(form);
      fd.set('action', 'save');
      fd.set('show_on_landing', document.getElementById('prodShowLanding').checked ? '1' : '0');
      fd.set('is_featured', document.getElementById('prodFeatured').checked ? '1' : '0');

      try {
        const res = await apiFetch('api/products.php', { method: 'POST', body: fd });
        showToast(res.message);
        document.getElementById('prodBarcode').value = res.barcode;
        updateLiveStickerPreview();
        if (document.getElementById('prodId').value === '0') {
          resetProductForm();
        }
      } catch (err) {
        showToast(err.message, true);
      }
    });
  }

  // ============================================================
  // TAB 5: INVENTORY MANAGEMENT & STICKER PRINTING
  // ============================================================
  let inventoryCache = [];

  async function loadInventoryTable() {
    const q = document.getElementById('invSearch')?.value.trim() || '';
    const cat = document.getElementById('invCategory')?.value || 'all';
    const tbody = document.getElementById('inventoryTableBody');
    if (!tbody) return;

    try {
      const data = await apiFetch(`api/products.php?category=${encodeURIComponent(cat)}&q=${encodeURIComponent(q)}`);
      inventoryCache = data.products || [];

      if (!inventoryCache.length) {
        tbody.innerHTML = `<tr><td colspan="${IS_ADMIN ? 9 : 7}" style="text-align:center;padding:28px;color:var(--db-muted);">لا توجد منتجات مطابقة.</td></tr>`;
        return;
      }

      tbody.innerHTML = inventoryCache.map((p) => {
        const img = p.image_path ? apiUrl(p.image_path) : apiUrl('assets/brand/official-banner.png');
        const low = p.quantity <= p.low_stock_threshold;
        return `
          <tr>
            <td><img src="${esc(img)}" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:10px;border:1px solid var(--db-border);"></td>
            <td>
              <strong>${esc(p.name)}</strong>
              <div style="font-size:.76rem;color:var(--db-muted);">${esc(p.category_label)} • ${esc(p.condition_label)}</div>
            </td>
            <td style="direction:ltr;font-family:monospace;font-weight:800;">${esc(p.barcode)}</td>
            <td>
              <span style="padding:3px 10px;border-radius:999px;font-weight:800;font-size:.8rem;background:${low ? '#fef2f2' : '#ecfdf5'};color:${low ? '#dc2626' : '#059669'};">
                ${p.quantity} ${low ? '⚠️' : ''}
              </span>
            </td>
            ${IS_ADMIN ? `<td style="color:var(--db-muted);font-weight:700;">${fmtMoney(p.purchase_price)}</td>` : ''}
            <td style="font-weight:900;color:var(--db-teal-900);">${fmtMoney(p.sell_price)}</td>
            <td style="font-weight:700;color:#065f46;">${fmtMoney(p.min_sell_price)}</td>
            ${IS_ADMIN ? `
              <td>
                <input type="checkbox" ${p.show_on_landing ? 'checked' : ''} onchange="toggleLandingVis(${p.id}, this.checked)" title="إظهار / إخفاء باللاندينج بيدج">
              </td>
            ` : ''}
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="openStickerModal(${p.id})">🏷️ الكود</button>
                ${p.quantity > 0 ? `<button type="button" class="btn btn-gold btn-sm" onclick="sellFromInv(${p.id})">🛒 بيع</button>` : ''}
                ${IS_ADMIN ? `
                  <button type="button" class="btn btn-teal btn-sm" onclick="editFromInv(${p.id})">تعديل</button>
                  <button type="button" class="btn btn-danger btn-sm" onclick="deleteProd(${p.id})">حذف</button>
                ` : ''}
              </div>
            </td>
          </tr>
        `;
      }).join('');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  document.getElementById('invSearch')?.addEventListener('input', loadInventoryTable);
  document.getElementById('invCategory')?.addEventListener('change', loadInventoryTable);

  window.openStickerModal = (id) => {
    const p = inventoryCache.find((x) => x.id === id);
    if (!p) return;
    document.getElementById('modalStickerName').textContent = p.name;
    document.getElementById('modalStickerCode').textContent = p.barcode;
    renderQrInto(document.getElementById('modalStickerQr'), p.barcode, 4);
    renderBarcodeInto(document.getElementById('modalStickerBarcode'), p.barcode);
    window.openModal('stickerModal');
  };

  document.getElementById('btnPrintModalSticker')?.addEventListener('click', () => {
    printHtmlSnippet('طباعة استيكر كود', `<div style="text-align:center;width:250px;padding:12px;border:1px solid #000;border-radius:8px;">${document.getElementById('modalStickerArea').innerHTML}</div>`);
  });

  window.sellFromInv = (id) => {
    const p = inventoryCache.find((x) => x.id === id);
    if (p) {
      addToPosCart(p);
      window.switchTab('pos');
    }
  };

  window.editFromInv = (id) => {
    const p = inventoryCache.find((x) => x.id === id);
    if (p && IS_ADMIN) {
      window.switchTab('add-product');
      window.populateProductForm(p);
    }
  };

  window.toggleLandingVis = async (id, checked) => {
    try {
      const res = await apiFetch('api/products.php', {
        method: 'POST',
        json: { action: 'toggle_landing', id, show_on_landing: checked ? 1 : 0 },
      });
      showToast(res.message);
    } catch (err) {
      showToast(err.message, true);
    }
  };

  window.deleteProd = async (id) => {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج نهائياً من المخزن؟')) return;
    try {
      const res = await apiFetch('api/products.php', { method: 'POST', json: { action: 'delete', id } });
      showToast(res.message);
      loadInventoryTable();
    } catch (err) {
      showToast(err.message, true);
    }
  };

  // ============================================================
  // TAB 6: INTERACTIVE QR MAINTENANCE + EMAILS + WIPE ON DELIVERY
  // ============================================================
  async function loadMaintenanceList() {
    const q = document.getElementById('maintSearch')?.value.trim() || '';
    const stage = document.getElementById('maintStageFilter')?.value || 'all';
    const tbody = document.getElementById('maintTableBody');
    if (!tbody) return;

    try {
      const data = await apiFetch(`api/maintenance.php?stage=${encodeURIComponent(stage)}&q=${encodeURIComponent(q)}`);
      const tickets = data.tickets || [];

      const totalUnread = tickets.reduce((a, t) => a + (t.unread_customer_notes || 0), 0);
      const navBadge = document.getElementById('maintNavBadge');
      if (navBadge) {
        navBadge.style.display = totalUnread > 0 ? 'inline-block' : 'none';
        navBadge.textContent = `${totalUnread} جديد`;
      }

      if (!tickets.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--db-muted);">لا توجد أجهزة صيانة نشطة حالياً.</td></tr>`;
        return;
      }

      tbody.innerHTML = tickets.map((t) => {
        const wa = normalizeWa(t.customer_phone);
        return `
          <tr>
            <td style="direction:ltr;font-weight:900;color:var(--db-teal-900);">${esc(t.ticket_code)}</td>
            <td>
              <strong>${esc(t.customer_name)}</strong>
              <div style="display:flex;gap:6px;align-items:center;margin-top:4px;flex-wrap:wrap;">
                <a href="tel:${esc(t.customer_phone)}" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:.75rem;">📞 ${esc(t.customer_phone)}</a>
                ${wa ? `<a href="https://wa.me/${wa}" target="_blank" class="btn btn-emerald btn-sm" style="padding:3px 8px;font-size:.75rem;">واتساب</a>` : ''}
                ${t.customer_email ? `<span title="${esc(t.customer_email)}" style="font-size:.74rem;color:#059669;font-weight:700;">📧 إيميل مفعل</span>` : ''}
              </div>
            </td>
            <td>
              <strong>${esc(t.device_model)}</strong>
              <div style="font-size:.78rem;color:var(--db-muted);max-width:220px;">${esc(t.problem_description)}</div>
            </td>
            <td>
              <span style="padding:4px 12px;border-radius:999px;background:var(--db-gold-50);color:var(--db-gold-600);border:1px solid rgba(212,155,39,.4);font-weight:800;font-size:.78rem;">
                ${esc(t.stage_title)}
              </span>
            </td>
            <td>
              <div style="font-weight:800;">${fmtMoney(t.estimated_cost)}</div>
              <div style="font-size:.75rem;color:var(--db-muted);">عربون: ${fmtMoney(t.deposit_paid)}</div>
            </td>
            <td>
              ${t.unread_customer_notes > 0
                ? `<span class="badge-alert">💬 ${t.unread_customer_notes} ملاحظة جديدة!</span>`
                : `<span style="font-size:.78rem;color:var(--db-muted);">لا يوجد جديد</span>`}
            </td>
            <td>
              <button type="button" class="btn btn-gold btn-sm" onclick="openMaintTicket(${t.id})">إدارة المراحل / QR / تسليم</button>
            </td>
          </tr>
        `;
      }).join('');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  document.getElementById('maintSearch')?.addEventListener('input', loadMaintenanceList);
  document.getElementById('maintStageFilter')?.addEventListener('change', loadMaintenanceList);
  document.getElementById('btnOpenNewMaint')?.addEventListener('click', () => window.openModal('newMaintModal'));

  document.getElementById('newMaintForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    payload.action = 'create';

    try {
      const res = await apiFetch('api/maintenance.php', { method: 'POST', json: payload });
      showToast(res.message);
      e.currentTarget.reset();
      window.closeModal('newMaintModal');
      await loadMaintenanceList();
      window.openMaintTicket(res.ticket.id);
    } catch (err) {
      showToast(err.message, true);
    }
  });

  window.openMaintTicket = async (id) => {
    try {
      const res = await apiFetch(`api/maintenance.php?action=details&id=${id}`);
      const t = res.ticket;
      const stages = res.stages || {};
      const notes = res.notes || [];
      const wa = normalizeWa(t.customer_phone);
      const waTrackMsg =
        `مرحباً أستاذ/ ${t.customer_name} 👋\n` +
        `تم تسجيل جهازكم (${t.device_model}) بقسم الصيانة في *${CFG.store.nameAr}*\n` +
        `🔖 رقم التذكرة: ${t.ticket_code}\n` +
        `📍 المرحلة الحالية: ${t.stage_title}\n\n` +
        `يمكنك متابعة مراحل الصيانة لحظة بلحظة وإضافة أي ملاحظات عبر الرابط التالي:\n${t.tracking_url}`;

      document.getElementById('mdModalTitle').textContent = `🛠️ تذكرة صيانة ${t.ticket_code} — ${t.device_model}`;

      const stageOptions = Object.entries(stages)
        .filter(([k]) => k !== 'delivered')
        .map(([k, st]) => `<option value="${esc(k)}" ${t.current_stage === k ? 'selected' : ''}>${st.order}. ${esc(st.title)}</option>`)
        .join('');

      const notesHtml = notes.map((n) => `
        <div style="padding:10px 14px;border-radius:10px;margin-bottom:8px;background:${n.sender_type === 'admin' ? '#eef7f7' : '#fef9ec'};border-right:4px solid ${n.sender_type === 'admin' ? '#074f53' : '#d49b27'};">
          <div style="display:flex;justify-content:space-between;font-size:.76rem;color:#475569;margin-bottom:4px;">
            <strong>${n.sender_type === 'admin' ? '👨‍🔧 ' + esc(n.sender_name) : '👤 العميل: ' + esc(n.sender_name)} — [${esc(stages[n.stage_key]?.title || n.stage_key)}]</strong>
            <span style="direction:ltr;">${esc(n.created_at)}</span>
          </div>
          <div style="font-size:.88rem;">${esc(n.note_text)}</div>
        </div>
      `).join('');

      const body = document.getElementById('maintDetailBody');
      body.innerHTML = `
        <div style="display:grid;grid-template-columns:1.3fr 0.7fr;gap:18px;margin-bottom:20px;">
          <!-- Customer & Device Full Profile -->
          <div style="background:#f8fbfb;padding:16px;border-radius:14px;border:1px solid var(--db-border);font-size:.88rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--db-border);">
              <strong style="font-size:1rem;color:var(--db-teal-900);">👤 ${esc(t.customer_name)}</strong>
              <div style="display:flex;gap:6px;">
                <a href="tel:${esc(t.customer_phone)}" class="btn btn-teal btn-sm">📞 اتصال: ${esc(t.customer_phone)}</a>
                ${wa ? `<a href="https://wa.me/${wa}?text=${encodeURIComponent(waTrackMsg)}" target="_blank" class="btn btn-emerald btn-sm">💬 إرسال رابط التتبع واتساب</a>` : ''}
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <div><strong>هاتف بديل:</strong> ${esc(t.customer_alt_phone || '—')}</div>
              <div><strong>البريد الإلكتروني:</strong> ${esc(t.customer_email || 'غير مسجل')}</div>
              <div><strong>تفاصيل العميل:</strong> ${esc(t.customer_details || '—')}</div>
              <div><strong>باسورد الفحص:</strong> <span style="background:#fee2e2;color:#991b1b;padding:1px 8px;border-radius:6px;font-weight:800;">${esc(t.device_passcode || 'لا يوجد')}</span></div>
              <div><strong>المواصفات:</strong> ${esc(t.device_specs || '—')}</div>
              <div><strong>الحالة عند الاستلام:</strong> ${esc(t.physical_condition || '—')}</div>
            </div>
            <div style="margin-top:10px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid var(--db-border);">
              <strong>العطل المسجل:</strong> ${esc(t.problem_description)}
            </div>
          </div>

          <!-- Printable Customer QR Receipt Box -->
          <div style="background:#ffffff;border:2px dashed var(--db-teal-800);border-radius:14px;padding:14px;text-align:center;" id="maintReceiptPrintArea">
            <strong style="color:#043538;font-size:.9rem;">ASLY | أصلي TECH STORE</strong>
            <div style="font-size:.76rem;color:#475569;">إيصال متابعة صيانة بالـ QR</div>
            <div id="maintTicketQrMount" style="margin:8px auto;"></div>
            <div style="font-family:monospace;font-weight:900;font-size:.95rem;color:#043538;">${esc(t.ticket_code)}</div>
            <div style="font-size:.78rem;margin:4px 0;">${esc(t.device_model)}</div>
            <div style="font-size:.74rem;color:#64748b;">امسح الرمز بكاميرا هاتفك لمتابعة المراحل</div>
            <div style="margin-top:10px;display:flex;gap:6px;justify-content:center;" class="no-print">
              <button type="button" class="btn btn-teal btn-sm" id="btnPrintMaintReceipt">🖨️ طباعة إيصال QR</button>
              <a href="${esc(t.tracking_url)}" target="_blank" class="btn btn-ghost btn-sm">معاينة صفحة العميل</a>
            </div>
          </div>
        </div>

        <!-- Stage Transition & Email Trigger -->
        <div style="background:var(--db-teal-50);padding:16px;border-radius:14px;border:1px solid var(--db-border);margin-bottom:18px;">
          <strong style="display:block;margin-bottom:10px;color:var(--db-teal-900);">🔄 نقل الجهاز لمرحلة جديدة / إضافة ملاحظة للعميل ${t.customer_email ? '(سيُرسل بريد إلكتروني تلقائي للعميل 📧)' : ''}</strong>
          <form id="updateMaintStageForm" style="display:grid;grid-template-columns:1fr 1.5fr auto;gap:10px;align-items:end;">
            <div>
              <label style="font-size:.78rem;font-weight:700;">المرحلة الحالية</label>
              <select name="stage_key" class="form-control">${stageOptions}</select>
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:700;">ملاحظة أو تقرير المهندس على هذه المرحلة</label>
              <input type="text" name="note_text" class="form-control" placeholder="اكتب ما تم إنجازه أو المطلوب من العميل...">
            </div>
            <button type="submit" class="btn btn-teal">تحديث المرحلة وإشعار العميل</button>
          </form>
        </div>

        <!-- Timeline Notes History -->
        <div style="margin-bottom:20px;">
          <h4 style="margin:0 0 10px;color:var(--db-teal-900);">💬 سجل المراحل والتعليقات المتبادلة مع العميل</h4>
          <div style="max-height:240px;overflow-y:auto;padding-left:4px;">${notesHtml || '<p style="color:var(--db-muted);">لا توجد ملاحظات بعد.</p>'}</div>
        </div>

        <!-- Final Handover & Data Wipe Box -->
        <div style="background:linear-gradient(135deg,#043538,#064b4e);color:#fff;padding:18px 20px;border-radius:16px;border:2px solid var(--db-gold-500);">
          <strong style="color:var(--db-gold-400);display:block;margin-bottom:6px;font-size:1rem;">✅ التسليم النهائي للجهاز (حفظ المبلغ المحصل في إيراد اليوم + مسح بيانات الجهاز نهائياً)</strong>
          <p style="margin:0 0 12px;font-size:.8rem;color:#d6ecec;">بمجرد تأكيد التسليم، سيُسجّل المبلغ المدفوع في حسابات اليوم، وتُمسح كافة تفاصيل الجهاز والعميل والملاحظات نهائياً كما هو معتمد.</p>
          <form id="deliverWipeForm" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
            <div style="flex:1;min-width:150px;">
              <label style="font-size:.78rem;color:#fde68a;display:block;margin-bottom:4px;">إجمالي المبلغ المحصل للصيانة (ج.م) *</label>
              <input type="number" step="0.01" min="0" name="final_paid_amount" value="${t.estimated_cost}" class="form-control" required style="font-weight:900;">
            </div>
            ${IS_ADMIN ? `
            <div style="flex:1;min-width:140px;">
              <label style="font-size:.78rem;color:#d6ecec;display:block;margin-bottom:4px;">تكلفة قطع الغيار الفعلية</label>
              <input type="number" step="0.01" min="0" name="parts_cost" value="${t.parts_cost || 0}" class="form-control">
            </div>` : ''}
            <div style="min-width:140px;">
              <label style="font-size:.78rem;color:#d6ecec;display:block;margin-bottom:4px;">طريقة الدفع</label>
              <select name="payment_method" class="form-control">
                <option value="cash">نقدي (كاش)</option>
                <option value="vodafone_cash">فودافون كاش</option>
                <option value="instapay">إنستا باي</option>
              </select>
            </div>
            <button type="submit" class="btn btn-gold">تسليم الجهاز ومسح بياناته</button>
          </form>
        </div>
      `;

      renderQrInto(document.getElementById('maintTicketQrMount'), t.tracking_url, 3);
      window.openModal('maintDetailModal');
      loadMaintenanceList();

      document.getElementById('btnPrintMaintReceipt')?.addEventListener('click', () => {
        const clone = document.getElementById('maintReceiptPrintArea').cloneNode(true);
        clone.querySelector('.no-print')?.remove();
        printHtmlSnippet(`إيصال صيانة ${t.ticket_code}`, `<div style="text-align:center;width:270px;padding:14px;border:1.5px solid #043538;border-radius:10px;">${clone.innerHTML}</div>`);
      });

      document.getElementById('updateMaintStageForm')?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const fd = new FormData(ev.currentTarget);
        try {
          const r = await apiFetch('api/maintenance.php', {
            method: 'POST',
            json: {
              action: 'update_stage',
              id: t.id,
              stage_key: fd.get('stage_key'),
              note_text: fd.get('note_text'),
            },
          });
          showToast(r.message);
          window.openMaintTicket(t.id);
        } catch (err) {
          showToast(err.message, true);
        }
      });

      document.getElementById('deliverWipeForm')?.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (!confirm('تأكيد تسليم الجهاز للعميل؟ سيتم حفظ المبلغ المدفوع في إيرادات اليوم ومسح كافة بيانات التذكرة والجهاز نهائياً!')) return;
        const fd = new FormData(ev.currentTarget);
        try {
          const r = await apiFetch('api/maintenance.php', {
            method: 'POST',
            json: {
              action: 'deliver_and_wipe',
              id: t.id,
              final_paid_amount: fd.get('final_paid_amount'),
              parts_cost: fd.get('parts_cost'),
              payment_method: fd.get('payment_method'),
            },
          });
          showToast(r.message);
          window.closeModal('maintDetailModal');
          loadMaintenanceList();
        } catch (err) {
          showToast(err.message, true);
        }
      });
    } catch (err) {
      showToast(err.message, true);
    }
  };

  // ============================================================
  // TAB 7: STAFF & ADMIN USERS MANAGEMENT (ADMIN ONLY)
  // ============================================================
  let usersCache = [];

  async function loadUsersList() {
    if (!IS_ADMIN) return;
    try {
      const data = await apiFetch('api/users.php');
      usersCache = data.users || [];
      const tbody = document.getElementById('usersTableBody');
      if (!tbody) return;
      tbody.innerHTML = usersCache.map((u) => `
        <tr>
          <td><strong>${esc(u.full_name)}</strong></td>
          <td style="direction:ltr;font-weight:700;">@${esc(u.username)}</td>
          <td>
            <span class="role-pill ${u.role === 'admin' ? 'role-admin' : 'role-staff'}" style="background:${u.role === 'admin' ? '#fef9ec' : '#ecfdf5'};color:${u.role === 'admin' ? '#b45309' : '#065f46'};">
              ${u.role === 'admin' ? '👑 مدير (Admin)' : '💼 موظف (Staff)'}
            </span>
          </td>
          <td style="direction:ltr;">${esc(u.phone || '—')}</td>
          <td>${Number(u.is_active) === 1 ? '<span style="color:#059669;font-weight:800;">نشط</span>' : '<span style="color:#dc2626;font-weight:800;">معطل</span>'}</td>
          <td style="direction:ltr;font-size:.8rem;">${esc(u.created_at)}</td>
          <td>
            <button type="button" class="btn btn-teal btn-sm" onclick="editUser(${u.id})">تعديل</button>
            ${u.id !== CFG.user.id ? `<button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})">حذف</button>` : ''}
          </td>
        </tr>
      `).join('');
    } catch (err) {
      showToast(err.message, true);
    }
  }

  if (IS_ADMIN) {
    document.getElementById('btnAddUserModal')?.addEventListener('click', () => {
      document.getElementById('userSaveForm').reset();
      document.getElementById('uFormId').value = '0';
      document.getElementById('uPassHint').textContent = '(مطلوبة - 6 أحرف على الأقل)';
      document.getElementById('uFormPassword').required = true;
      window.openModal('userModal');
    });

    window.editUser = (id) => {
      const u = usersCache.find((x) => Number(x.id) === id);
      if (!u) return;
      document.getElementById('uFormId').value = u.id;
      document.getElementById('uFormFullName').value = u.full_name;
      document.getElementById('uFormUsername').value = u.username;
      document.getElementById('uFormPassword').value = '';
      document.getElementById('uFormPassword').required = false;
      document.getElementById('uPassHint').textContent = '(اتركها فارغة لعدم التغيير)';
      document.getElementById('uFormRole').value = u.role;
      document.getElementById('uFormPhone').value = u.phone || '';
      document.getElementById('uFormActive').checked = Number(u.is_active) === 1;
      window.openModal('userModal');
    };

    window.deleteUser = async (id) => {
      if (!confirm('حذف هذا الحساب نهائياً؟')) return;
      try {
        const res = await apiFetch('api/users.php', { method: 'POST', json: { action: 'delete', id } });
        showToast(res.message);
        loadUsersList();
      } catch (err) {
        showToast(err.message, true);
      }
    };

    document.getElementById('userSaveForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const payload = Object.fromEntries(fd.entries());
      payload.action = 'save';
      payload.is_active = document.getElementById('uFormActive').checked ? 1 : 0;
      try {
        const res = await apiFetch('api/users.php', { method: 'POST', json: payload });
        showToast(res.message);
        window.closeModal('userModal');
        loadUsersList();
      } catch (err) {
        showToast(err.message, true);
      }
    });

    // ============================================================
    // TAB 8: BRAND SETTINGS, DYNAMIC CONTACT CHANNELS & SMTP
    // ============================================================
    let channelsCache = [];

    window.loadSettingsData = async () => {
      try {
        const data = await apiFetch('api/settings.php');
        const s = data.settings || {};
        channelsCache = data.channels || [];

        Object.entries(s).forEach(([k, v]) => {
          const el = document.getElementById(`set_${k}`);
          if (el) el.value = v ?? '';
        });

        const tbody = document.getElementById('channelsTableBody');
        if (tbody) {
          tbody.innerHTML = channelsCache.map((ch) => `
            <tr>
              <td><strong>${esc(ch.channel_type)}</strong></td>
              <td>${esc(ch.label)}</td>
              <td style="direction:ltr;font-weight:700;">${esc(ch.value)}</td>
              <td style="direction:ltr;font-size:.78rem;max-width:190px;overflow:hidden;text-overflow:ellipsis;">${esc(ch.url || '')}</td>
              <td>${ch.sort_order}</td>
              <td>${Number(ch.is_active) === 1 ? '<span style="color:#059669;font-weight:800;">ظاهر</span>' : '<span style="color:#94a3b8;">مخفي</span>'}</td>
              <td>
                <button type="button" class="btn btn-teal btn-sm" onclick="editChannel(${ch.id})">تعديل</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteChannel(${ch.id})">حذف</button>
              </td>
            </tr>
          `).join('');
        }
      } catch (err) {
        showToast(err.message, true);
      }
    };

    document.getElementById('settingsSaveForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const settingsObj = Object.fromEntries(fd.entries());
      try {
        const res = await apiFetch('api/settings.php', {
          method: 'POST',
          json: { action: 'save_settings', settings: settingsObj },
        });
        showToast(res.message);
      } catch (err) {
        showToast(err.message, true);
      }
    });

    document.getElementById('btnAddChannelModal')?.addEventListener('click', () => {
      document.getElementById('channelSaveForm').reset();
      document.getElementById('chFormId').value = '0';
      window.openModal('channelModal');
    });

    window.editChannel = (id) => {
      const ch = channelsCache.find((x) => Number(x.id) === id);
      if (!ch) return;
      document.getElementById('chFormId').value = ch.id;
      document.getElementById('chFormType').value = ch.channel_type;
      document.getElementById('chFormSort').value = ch.sort_order;
      document.getElementById('chFormLabel').value = ch.label;
      document.getElementById('chFormValue').value = ch.value;
      document.getElementById('chFormUrl').value = ch.url || '';
      document.getElementById('chFormActive').checked = Number(ch.is_active) === 1;
      window.openModal('channelModal');
    };

    window.deleteChannel = async (id) => {
      if (!confirm('حذف وسيلة التواصل هذه؟')) return;
      try {
        const res = await apiFetch('api/settings.php', { method: 'POST', json: { action: 'delete_channel', id } });
        showToast(res.message);
        window.loadSettingsData();
      } catch (err) {
        showToast(err.message, true);
      }
    };

    document.getElementById('channelSaveForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      const payload = Object.fromEntries(fd.entries());
      payload.action = 'save_channel';
      payload.is_active = document.getElementById('chFormActive').checked ? 1 : 0;
      try {
        const res = await apiFetch('api/settings.php', { method: 'POST', json: payload });
        showToast(res.message);
        window.closeModal('channelModal');
        window.loadSettingsData();
      } catch (err) {
        showToast(err.message, true);
      }
    });

    document.getElementById('btnSendTestEmail')?.addEventListener('click', async () => {
      const to = document.getElementById('testEmailRecipient').value.trim();
      if (!to) {
        showToast('أدخل بريداً إلكترونياً للتجربة أولاً', true);
        return;
      }
      try {
        const res = await apiFetch('api/settings.php', { method: 'POST', json: { action: 'test_email', to_email: to } });
        showToast(res.message);
      } catch (err) {
        showToast(err.message, true);
      }
    });

    document.getElementById('btnViewEmailLogs')?.addEventListener('click', async () => {
      try {
        const res = await apiFetch('api/settings.php?action=email_logs');
        const logs = res.logs || [];
        const body = document.getElementById('emailLogsBody');
        body.innerHTML = logs.length
          ? logs.map((l) => `
              <details style="border:1px solid var(--db-border);border-radius:12px;padding:12px 16px;margin-bottom:10px;background:#fafdfd;">
                <summary style="cursor:pointer;font-weight:800;color:var(--db-teal-900);">
                  📧 إلى: ${esc(l.recipient_email)} — ${esc(l.subject)} <span style="font-size:.75rem;color:#059669;">[${esc(l.status)} • ${esc(l.created_at)}]</span>
                </summary>
                <div style="margin-top:12px;border-top:1px solid var(--db-border);padding-top:12px;">
                  ${l.error_message ? `<div style="color:#dc2626;margin-bottom:8px;">خطأ SMTP: ${esc(l.error_message)}</div>` : ''}
                  <iframe srcdoc="${esc(l.body_preview || '')}" style="width:100%;height:340px;border:1px solid #dce8e8;border-radius:8px;background:#fff;"></iframe>
                </div>
              </details>
            `).join('')
          : '<p style="text-align:center;color:var(--db-muted);padding:24px;">لا توجد رسائل مسجلة بعد.</p>';
        window.openModal('emailLogsModal');
      } catch (err) {
        showToast(err.message, true);
      }
    });
  }

  // Initial Boot
  document.addEventListener('DOMContentLoaded', () => {
    renderPosCart();
    loadOverviewReport();
  });
})();
