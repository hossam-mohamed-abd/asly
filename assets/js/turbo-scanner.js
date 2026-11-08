(function() {
  "use strict";

  let zbarScannerPromise = null;
  let nativeDetectorPromise = null;

  function getZbarScanner() {
    if (zbarScannerPromise) return zbarScannerPromise;
    if (typeof window.zbarWasm === "undefined") return Promise.resolve(null);
    zbarScannerPromise = (async () => {
      try {
        const z = window.zbarWasm;
        const sc = await z.getDefaultScanner();
        sc.enableCache(false);
        sc.setConfig(0, z.ZBarConfigType.ZBAR_CFG_TEST_INVERTED, 1);
        sc.setConfig(0, z.ZBarConfigType.ZBAR_CFG_X_DENSITY, 1);
        sc.setConfig(0, z.ZBarConfigType.ZBAR_CFG_Y_DENSITY, 1);
        [ 128, 13, 8, 12, 9, 93 ].forEach(sym => {
          sc.setConfig(sym, z.ZBarConfigType.ZBAR_CFG_UNCERTAINTY, 0);
        });
        [ 39, 25, 38 ].forEach(sym => {
          sc.setConfig(sym, z.ZBarConfigType.ZBAR_CFG_UNCERTAINTY, 1);
        });
        return sc;
      } catch (_) {
        return null;
      }
    })();
    return zbarScannerPromise;
  }

  function getNativeDetector() {
    if (nativeDetectorPromise) return nativeDetectorPromise;
    if (typeof window.BarcodeDetector === "undefined") return Promise.resolve(null);
    nativeDetectorPromise = (async () => {
      try {
        const wanted = [ "qr_code", "code_128", "ean_13", "ean_8", "upc_a", "upc_e", "code_39", "code_93", "itf", "codabar" ];
        let supported = wanted;
        if (typeof window.BarcodeDetector.getSupportedFormats === "function") {
          const avail = await window.BarcodeDetector.getSupportedFormats();
          supported = wanted.filter(f => avail.includes(f));
        }
        return supported.length ? new window.BarcodeDetector({ formats: supported }) : null;
      } catch (_) {
        return null;
      }
    })();
    return nativeDetectorPromise;
  }

  function rgbaToAutoContrastGray(rgba, w, h) {
    const len = w * h;
    const gray = new Uint8Array(len);
    for (let i = 0, p = 0; i < len; i++, p += 4) {
      gray[i] = (rgba[p] * 77 + rgba[p + 1] * 150 + rgba[p + 2] * 29) >> 8;
    }
    let min = 255;
    let max = 0;
    const y0 = (h * 0.15) | 0;
    const y1 = (h * 0.85) | 0;
    const x0 = (w * 0.12) | 0;
    const x1 = (w * 0.88) | 0;
    for (let y = y0; y < y1; y += 3) {
      const row = y * w;
      for (let x = x0; x < x1; x += 3) {
        const v = gray[row + x];
        if (v < min) min = v;
        if (v > max) max = v;
      }
    }
    if (max > min + 8 && (min > 22 || max < 232)) {
      const scale = 255 / (max - min);
      for (let i = 0; i < len; i++) {
        const v = (gray[i] - min) * scale;
        gray[i] = v <= 0 ? 0 : v >= 255 ? 255 : v | 0;
      }
    }
    return gray;
  }

  function deblurSharpen2D(gray, w, h, radius, strength) {
    const out = new Uint8Array(w * h);
    out.set(gray);
    const r = radius;
    const yEnd = h - r;
    const xEnd = w - r;
    const rOffset = r * w;
    for (let y = r; y < yEnd; y++) {
      const row = y * w;
      for (let x = r; x < xEnd; x++) {
        const idx = row + x;
        const c = gray[idx];
        const nb = (gray[idx - r] + gray[idx + r] + gray[idx - rOffset] + gray[idx + rOffset]) * 0.25;
        const v = c + strength * (c - nb);
        out[idx] = v <= 0 ? 0 : v >= 255 ? 255 : v | 0;
      }
    }
    return out;
  }

  function localGlareNormalize1D(gray, w, h) {
    const out = new Uint8Array(w * h);
    const win = Math.max(12, (w / 18) | 0);
    const rowSum = new Int32Array(w + 1);
    for (let y = 0; y < h; y++) {
      const row = y * w;
      rowSum[0] = 0;
      for (let x = 0; x < w; x++) {
        rowSum[x + 1] = rowSum[x] + gray[row + x];
      }
      for (let x = 0; x < w; x++) {
        const a = x > win ? x - win : 0;
        const b = x + win < w ? x + win : w - 1;
        const mean = (rowSum[b + 1] - rowSum[a]) / (b - a + 1);
        const diff = gray[row + x] - mean;
        const v = 136 + diff * 2.35;
        out[row + x] = v <= 0 ? 0 : v >= 255 ? 255 : v | 0;
      }
    }
    return out;
  }

  async function decodeCanvasWithZbar(grayBuf, w, h) {
    const sc = await getZbarScanner();
    if (!sc || !window.zbarWasm) return "";
    const syms = await window.zbarWasm.scanGrayBuffer(grayBuf.buffer, w, h, sc);
    if (syms && syms.length > 0) {
      for (const s of syms) {
        const txt = (s.decode() || "").trim();
        if (txt) return txt;
      }
    }
    return "";
  }

  const instances = {};

  async function stop(mountId) {
    const inst = instances[mountId];
    if (!inst) return;
    inst.running = false;
    delete instances[mountId];
    if (inst.rafId) cancelAnimationFrame(inst.rafId);
    if (inst.stream) {
      inst.stream.getTracks().forEach(t => {
        try { t.stop(); } catch (_) {}
      });
    }
    const mount = document.getElementById(mountId);
    if (mount) mount.innerHTML = "";
  }

  async function stopAll() {
    for (const id of Object.keys(instances)) {
      await stop(id);
    }
  }

  async function openBestStream(preferredDeviceId) {
    const tries = [];
    if (preferredDeviceId) {
      tries.push({
        video: {
          deviceId: { exact: preferredDeviceId },
          width: { ideal: 1920 },
          height: { ideal: 1080 },
          frameRate: { ideal: 30 }
        },
        audio: false
      });
    }
    tries.push(
      {
        video: {
          facingMode: { ideal: "environment" },
          width: { ideal: 1920 },
          height: { ideal: 1080 },
          frameRate: { ideal: 30 }
        },
        audio: false
      },
      {
        video: {
          facingMode: { ideal: "environment" },
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: false
      },
      { video: true, audio: false }
    );
    let lastErr = null;
    for (const c of tries) {
      try {
        return await navigator.mediaDevices.getUserMedia(c);
      } catch (err) {
        lastErr = err;
      }
    }
    throw lastErr || new Error("Camera unavailable");
  }

  async function start(mountId, onScanSuccess, opts = {}) {
    await stop(mountId);
    const isEn = !!opts.isEn;
    const tr = (ar, en) => isEn ? en : ar;
    const mount = document.getElementById(mountId);
    if (!mount) return false;

    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== "function") {
      mount.innerHTML = `<div style="padding:18px;color:#dc2626;font-size:0.85rem;font-weight:700;">${tr("المتصفح لا يدعم تشغيل الكاميرا مباشرة (يتطلب HTTPS أو localhost). استخدم زر مسح صورة الكود أو قارئ الباركود.", "Direct camera requires HTTPS or localhost. Use image scan or a barcode gun.")}</div>`;
      return false;
    }

    getZbarScanner();
    getNativeDetector();

    let stream = null;
    try {
      stream = await openBestStream(opts.deviceId || "");
    } catch (err) {
      mount.innerHTML = `<div style="padding:18px;color:#dc2626;font-size:0.85rem;font-weight:700;">${tr("تعذر فتح الكاميرا:", "Could not access camera:")} ${(err && err.message) ? err.message : "Permission denied"}</div>`;
      return false;
    }

    const inst = {
      running: true,
      stream: stream,
      rafId: 0,
      zoomLevel: 1,
      torchOn: false,
      devices: [],
      currentDeviceId: ""
    };
    instances[mountId] = inst;

    mount.innerHTML = `
      <div class="turbo-cam-shell">
        <video class="turbo-cam-video" playsinline muted autoplay></video>
        <div class="turbo-cam-reticle">
          <span class="tc-corner tc-tl"></span>
          <span class="tc-corner tc-tr"></span>
          <span class="tc-corner tc-bl"></span>
          <span class="tc-corner tc-br"></span>
          <div class="turbo-laser-line"></div>
        </div>
        <div class="turbo-cam-badge">
          <i class="fa-solid fa-bolt"></i>
          <span>${tr("مسح فائق السرعة + تحسين تلقائي للكاميرا الضعيفة", "Turbo Scan + Auto Low-Quality Deblur")}</span>
        </div>
        <div class="turbo-cam-controls">
          <button type="button" class="turbo-ctrl-btn active" data-zoom="1">1×</button>
          <button type="button" class="turbo-ctrl-btn" data-zoom="1.6">1.6×</button>
          <button type="button" class="turbo-ctrl-btn" data-zoom="2.3">2.3×</button>
          <button type="button" class="turbo-ctrl-btn" data-torch="1" style="display:none;" title="${tr("إضاءة الفلاش", "Flashlight")}"><i class="fa-solid fa-lightbulb"></i></button>
          <button type="button" class="turbo-ctrl-btn" data-switch-cam="1" style="display:none;" title="${tr("تبديل الكاميرا", "Switch Camera")}"><i class="fa-solid fa-camera-rotate"></i></button>
        </div>
      </div>
    `;

    const shell = mount.querySelector(".turbo-cam-shell");
    const video = mount.querySelector(".turbo-cam-video");
    const torchBtn = mount.querySelector("[data-torch]");
    const switchBtn = mount.querySelector("[data-switch-cam]");
    const zoomBtns = mount.querySelectorAll("[data-zoom]");

    video.srcObject = stream;
    try { await video.play(); } catch (_) {}

    const track = stream.getVideoTracks()[0];
    if (track) {
      const settings = typeof track.getSettings === "function" ? track.getSettings() : {};
      inst.currentDeviceId = settings.deviceId || "";
      const caps = typeof track.getCapabilities === "function" ? track.getCapabilities() : {};
      const adv = {};
      if (caps.focusMode && caps.focusMode.includes("continuous")) adv.focusMode = "continuous";
      if (caps.exposureMode && caps.exposureMode.includes("continuous")) adv.exposureMode = "continuous";
      if (caps.whiteBalanceMode && caps.whiteBalanceMode.includes("continuous")) adv.whiteBalanceMode = "continuous";
      if (Object.keys(adv).length) {
        track.applyConstraints({ advanced: [ adv ] }).catch(() => {});
      }
      if (caps.torch && torchBtn) {
        torchBtn.style.display = "inline-flex";
        torchBtn.addEventListener("click", () => {
          inst.torchOn = !inst.torchOn;
          torchBtn.classList.toggle("active", inst.torchOn);
          track.applyConstraints({ advanced: [ { torch: inst.torchOn } ] }).catch(() => {});
        });
      }
      zoomBtns.forEach(btn => {
        btn.addEventListener("click", () => {
          const z = parseFloat(btn.dataset.zoom) || 1;
          inst.zoomLevel = z;
          zoomBtns.forEach(b => b.classList.toggle("active", b === btn));
          if (caps.zoom && typeof caps.zoom.max === "number" && caps.zoom.max >= z) {
            video.style.transform = "scale(1)";
            track.applyConstraints({ advanced: [ { zoom: Math.min(caps.zoom.max, Math.max(caps.zoom.min || 1, z)) } ] }).catch(() => {
              video.style.transform = `scale(${z})`;
            });
          } else {
            video.style.transform = `scale(${z})`;
          }
        });
      });
    }

    if (navigator.mediaDevices.enumerateDevices) {
      navigator.mediaDevices.enumerateDevices().then(list => {
        if (!inst.running) return;
        const cams = list.filter(d => d.kind === "videoinput");
        inst.devices = cams;
        if (cams.length > 1 && switchBtn) {
          switchBtn.style.display = "inline-flex";
          switchBtn.addEventListener("click", async () => {
            const curIdx = cams.findIndex(c => c.deviceId === inst.currentDeviceId);
            const nextCam = cams[(curIdx + 1) % cams.length];
            if (nextCam) {
              await start(mountId, onScanSuccess, Object.assign({}, opts, { deviceId: nextCam.deviceId }));
            }
          });
        }
      }).catch(() => {});
    }

    const workCanvas = document.createElement("canvas");
    const workCtx = workCanvas.getContext("2d", { willReadFrequently: true });
    let busy = false;
    let passIdx = 0;
    let lastCode = "";
    let lastEmitTs = 0;

    function emitHit(rawText) {
      const clean = (rawText || "").trim();
      if (!clean) return;
      const now = Date.now();
      if (clean === lastCode && now - lastEmitTs < 1700) return;
      lastCode = clean;
      lastEmitTs = now;
      if (shell) {
        shell.classList.remove("scan-hit");
        void shell.offsetWidth;
        shell.classList.add("scan-hit");
        setTimeout(() => shell.classList.remove("scan-hit"), 420);
      }
      onScanSuccess(clean);
    }

    async function processFrame() {
      if (!inst.running) return;
      inst.rafId = requestAnimationFrame(processFrame);
      if (busy || video.readyState < 2 || !video.videoWidth || !video.videoHeight) return;
      busy = true;
      try {
        const vw = video.videoWidth;
        const vh = video.videoHeight;
        const mode = passIdx % 3;
        passIdx++;

        if (mode === 0) {
          const detector = await getNativeDetector();
          if (detector) {
            try {
              const found = await detector.detect(video);
              if (found && found.length > 0 && found[0].rawValue) {
                emitHit(found[0].rawValue);
                busy = false;
                return;
              }
            } catch (_) {}
          }
        }

        const zFactor = inst.zoomLevel > 1 ? inst.zoomLevel : 1;
        let sx = 0, sy = 0, sw = vw, sh = vh, dw = vw, dh = vh;

        if (mode === 0) {
          if (zFactor > 1) {
            sw = Math.round(vw / zFactor);
            sh = Math.round(vh / zFactor);
            sx = Math.round((vw - sw) * 0.5);
            sy = Math.round((vh - sh) * 0.5);
          }
          const maxW = 920;
          const scale = sw > maxW ? maxW / sw : 1;
          dw = Math.max(240, Math.round(sw * scale));
          dh = Math.max(160, Math.round(sh * scale));
        } else if (mode === 1) {
          const cropRatio = zFactor > 1 ? Math.min(0.95, 0.78 / zFactor) : 0.72;
          sw = Math.round(vw * cropRatio);
          sh = Math.round(vh * Math.min(0.85, cropRatio * 0.92));
          sx = Math.round((vw - sw) * 0.5);
          sy = Math.round((vh - sh) * 0.5);
          const targetW = sw < 640 ? Math.round(sw * 1.45) : Math.min(960, sw);
          const sc = targetW / sw;
          dw = Math.max(260, Math.round(sw * sc));
          dh = Math.max(180, Math.round(sh * sc));
        } else {
          const cropRatio = zFactor > 1 ? Math.min(0.9, 0.62 / zFactor) : 0.56;
          sw = Math.round(vw * cropRatio);
          sh = Math.round(vh * Math.min(0.8, cropRatio * 0.9));
          sx = Math.round((vw - sw) * 0.5);
          sy = Math.round((vh - sh) * 0.5);
          const targetW = Math.min(980, Math.max(680, Math.round(sw * 1.65)));
          const sc = targetW / sw;
          dw = Math.round(sw * sc);
          dh = Math.round(sh * sc);
        }

        workCanvas.width = dw;
        workCanvas.height = dh;
        workCtx.imageSmoothingEnabled = true;
        workCtx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
        const imgData = workCtx.getImageData(0, 0, dw, dh);
        const baseGray = rgbaToAutoContrastGray(imgData.data, dw, dh);

        let grayToScan = baseGray;
        if (mode === 1) {
          grayToScan = deblurSharpen2D(baseGray, dw, dh, 2, 1.85);
        } else if (mode === 2) {
          const glareFixed = localGlareNormalize1D(baseGray, dw, dh);
          grayToScan = deblurSharpen2D(glareFixed, dw, dh, 2, 1.75);
        }

        const decoded = await decodeCanvasWithZbar(grayToScan, dw, dh);
        if (decoded && inst.running) {
          emitHit(decoded);
        }
      } catch (_) {
      } finally {
        busy = false;
      }
    }

    inst.rafId = requestAnimationFrame(processFrame);
    return true;
  }

  async function scanFile(file) {
    if (!file) throw new Error("No file");
    const bitmapUrl = URL.createObjectURL(file);
    try {
      const img = await new Promise((resolve, reject) => {
        const i = new Image();
        i.onload = () => resolve(i);
        i.onerror = reject;
        i.src = bitmapUrl;
      });

      const detector = await getNativeDetector();
      if (detector) {
        try {
          const det = await detector.detect(img);
          if (det && det.length > 0 && det[0].rawValue) return det[0].rawValue.trim();
        } catch (_) {}
      }

      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d", { willReadFrequently: true });
      const scales = [ 1, 1.6 ];
      for (const scFactor of scales) {
        const maxDim = 1500;
        let w = Math.round(img.naturalWidth * scFactor);
        let h = Math.round(img.naturalHeight * scFactor);
        if (w > maxDim) {
          h = Math.round(h * (maxDim / w));
          w = maxDim;
        }
        canvas.width = Math.max(120, w);
        canvas.height = Math.max(120, h);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        const rgba = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        const autoGray = rgbaToAutoContrastGray(rgba, canvas.width, canvas.height);

        let hit = await decodeCanvasWithZbar(autoGray, canvas.width, canvas.height);
        if (hit) return hit;

        const sharp = deblurSharpen2D(autoGray, canvas.width, canvas.height, 2, 1.9);
        hit = await decodeCanvasWithZbar(sharp, canvas.width, canvas.height);
        if (hit) return hit;

        const glare = deblurSharpen2D(localGlareNormalize1D(autoGray, canvas.width, canvas.height), canvas.width, canvas.height, 2, 1.7);
        hit = await decodeCanvasWithZbar(glare, canvas.width, canvas.height);
        if (hit) return hit;
      }
    } finally {
      URL.revokeObjectURL(bitmapUrl);
    }

    if (typeof window.Html5Qrcode !== "undefined") {
      const tmpId = "aslyTmpFileScanNode";
      let tmpEl = document.getElementById(tmpId);
      if (!tmpEl) {
        tmpEl = document.createElement("div");
        tmpEl.id = tmpId;
        tmpEl.style.display = "none";
        document.body.appendChild(tmpEl);
      }
      const fallback = new window.Html5Qrcode(tmpId);
      const res = await fallback.scanFile(file, false);
      if (res) return res.trim();
    }
    throw new Error("No barcode detected");
  }

  window.AslyTurboScanner = {
    start: start,
    stop: stop,
    stopAll: stopAll,
    scanFile: scanFile
  };
})();
