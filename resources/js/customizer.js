// Cốc Mơ — Customizer (canvas-based, gọn & mượt)
// Lấy cảm hứng UX từ tachanh.html nhưng tích hợp chặt với Laravel backend.

const cupDesigner = document.querySelector('[data-cup-customizer]');

if (cupDesigner) {
    // --- DOM refs
    const preview = cupDesigner.querySelector('[data-cup-preview]');
    const canvas = cupDesigner.querySelector('[data-cup-canvas]');
    const ctx = canvas.getContext('2d');
    const mugSprite = cupDesigner.querySelector('[data-cup-mug-sprite]');
    const uploadCaption = cupDesigner.querySelector('[data-upload-caption]');
    const styleOptions = [...cupDesigner.querySelectorAll('[data-cup-variant-option]')];
    const selectedVariantInput = document.querySelector('[data-selected-variant]');
    const iconOptions = [...cupDesigner.querySelectorAll('[data-add-icon]')];
    const addTextButton = cupDesigner.querySelector('[data-add-text]');
    const artworkInput = cupDesigner.querySelector('[data-artwork-input]');
    const artworkChipLayer = cupDesigner.querySelector('[data-artwork-chip]');

    const layerSection = cupDesigner.querySelector('[data-section="layer"]');
    const layerEditor = cupDesigner.querySelector('[data-layer-editor]');
    const layerTextOnly = cupDesigner.querySelector('[data-layer-text-only]');
    const layerImageOnly = cupDesigner.querySelector('[data-layer-image-only]');
    const layerColorRow = cupDesigner.querySelector('[data-layer-color-row]');
    const selectedContentInput = cupDesigner.querySelector('[data-selected-content]');
    const selectedFontInput = cupDesigner.querySelector('[data-selected-font]');
    const selectedColorInput = cupDesigner.querySelector('[data-selected-color]');
    const selectedSizeInput = cupDesigner.querySelector('[data-selected-size]');
    const selectedRotationInput = cupDesigner.querySelector('[data-selected-rotation]');
    const removeBackgroundButton = cupDesigner.querySelector('[data-remove-background]');
    const bgToleranceWrap = cupDesigner.querySelector('[data-bg-tolerance-wrap]');
    const bgToleranceInput = cupDesigner.querySelector('[data-bg-tolerance]');
    const bgToleranceValue = cupDesigner.querySelector('[data-bg-tolerance-value]');
    const bgStatus = cupDesigner.querySelector('[data-bg-status]');
    const removeLayerButton = cupDesigner.querySelector('[data-remove-layer]');
    const frontLayerButton = cupDesigner.querySelector('[data-front-layer]');

    const imageLayersSection = cupDesigner.querySelector('[data-image-layers]');
    const imageLayersList = cupDesigner.querySelector('[data-image-layers-list]');

    // Upload preview refs (hiển thị ảnh đã chọn ngay cho khách thấy)
    const uploadPreviewSection = cupDesigner.querySelector('[data-upload-preview]');
    const uploadPreviewGrid = cupDesigner.querySelector('[data-upload-preview-grid]');
    const uploadPreviewCount = cupDesigner.querySelector('[data-upload-preview-count]');
    const uploadPreviewClear = cupDesigner.querySelector('[data-upload-preview-clear]');

    const quantityInput = cupDesigner.querySelector('[data-cup-quantity]');
    const cupTypeInput = cupDesigner.querySelector('[data-cup-type]');
    const orderTotal = cupDesigner.querySelector('[data-cup-order-total]');
    const discountNote = cupDesigner.querySelector('[data-cup-discount-note]');
    const addToCartButton = cupDesigner.querySelector('[data-add-to-cart]');
    const openOrderButton = cupDesigner.querySelector('[data-open-order]');

    const modal = document.querySelector('[data-cup-modal]');
    const modalCloseButtons = [...document.querySelectorAll('[data-close-modal]')];
    const modalPreview = document.querySelector('[data-modal-preview]');
    const modalSummary = document.querySelector('[data-modal-summary]');
    const designFields = document.querySelector('[data-design-fields]');
    const designPreviewInput = document.querySelector('[data-design-preview]');
    const orderForm = document.querySelector('[data-order-form]');
    const toast = document.querySelector('[data-cup-toast]');

    // --- Config from data attrs
    const unitPrice = Number(cupDesigner.dataset.unitPrice);
    const discountMinimum = Number(cupDesigner.dataset.discountMinimum);
    const discountPercentage = Number(cupDesigner.dataset.discountPercentage);
    const defaultItems = JSON.parse(cupDesigner.dataset.initialItems ?? '[]');
    const newText = cupDesigner.dataset.newText ?? 'Chữ của bạn';
    const mugSpriteBase = cupDesigner.dataset.mugSpriteBase ?? '/img';
    const fmt = new Intl.NumberFormat('vi-VN');

    // --- Mugs (3 ảnh chụp thực tế từ tachanh.html)
    const MUGS = {
        black: { name: 'Quai đen – lòng đen', src: `${mugSpriteBase}/coc-black.jpg?v=1` },
        pink: { name: 'Quai hồng – lòng hồng', src: `${mugSpriteBase}/coc-pink.jpg?v=1` },
        white: { name: 'Trắng tinh', src: `${mugSpriteBase}/coc-white.jpg?v=1` },
    };

    // --- Layers (mỗi layer = 1 phần tử in: text, emoji, hoặc image)
    let layers = defaultItems.map((item) => normalizeLayer(item));
    let sel = null;
    let drag = null;
    let artworkFiles = []; // {file, orig, image (HTMLImageElement), name, k, origW, origH, maskApplied, origUrl}
    let nextImageIndex = 0;
    let isPreparingOrder = false;
    let isModalRendered = false;

    // Normalize layer to internal shape with canvas coords
    function normalizeLayer(item) {
        const type = item.type;
        const x = Number(item.x ?? 50);
        const y = Number(item.y ?? 50);
        const font_size = Number(item.font_size ?? 100);
        return {
            type,
            content: String(item.content ?? ''),
            // We'll store normalized canvas coords (0..1 of print area) for portability
            nx: (x + Number(item.width ?? 50) / 2) / 100, // center normalized
            ny: y / 100 + 0.01,
            nw: Number(item.width ?? 50) / 100,
            rot: 0,
            color: '#111111',
            font: '"Be Vietnam Pro",sans-serif',
            size: 56, // font-size in px (canvas)
            imgSize: 200, // image edge size in px
            font_scale: font_size,
            image_index: item.image_index ?? null,
        };
    }

    // --- Mug sprite load
    let currentMug = MUGS[styleOptions[0]?.dataset.cupVariantOption ?? 'black'] ?? MUGS.black;
    function setMug(key) {
        if (!MUGS[key]) return;
        currentMug = MUGS[key];
        mugSprite.src = currentMug.src;
        if (selectedVariantInput) selectedVariantInput.value = key;
        styleOptions.forEach((opt) => {
            const isOn = opt.dataset.cupVariantOption === key;
            opt.classList.toggle('is-selected', isOn);
            opt.setAttribute('aria-pressed', String(isOn));
        });
        requestRender();
    }

    // --- Canvas geometry: print area = (37.6%, 26.4%) of stage, (52.7%, 54.7%) size
    const PRINT_AREA = { x: 0.376, y: 0.264, w: 0.527, h: 0.547 };

    function resizeCanvas() {
        const stage = preview;
        const w = stage.clientWidth;
        const h = stage.clientHeight;
        const dpr = window.devicePixelRatio || 1;
        // CSS handles positioning (left/top/width/height via %)
        // JS only sets canvas buffer size
        canvas.width = Math.round(PRINT_AREA.w * w * dpr);
        canvas.height = Math.round(PRINT_AREA.h * h * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        requestRender();
    }
    let renderQueued = false;
    function requestRender() {
        if (renderQueued) return;
        renderQueued = true;
        requestAnimationFrame(() => { renderQueued = false; render(); });
    }
    function render() {
        const w = canvas.width / (window.devicePixelRatio || 1);
        const h = canvas.height / (window.devicePixelRatio || 1);
        ctx.clearRect(0, 0, w, h);

        for (const l of layers) {
            ctx.save();
            const cx = l.nx * w;
            const cy = l.ny * h;
            ctx.translate(cx, cy);
            ctx.rotate((l.rot || 0) * Math.PI / 180);

            if (l.type === 'image') {
                const data = artworkFiles[l.image_index ?? 0];
                if (data?.image) {
                    const targetEdge = l.imgSize;
                    const ratio = data.k || 1;
                    let dw, dh;
                    if (ratio >= 1) { dw = targetEdge; dh = targetEdge / ratio; }
                    else { dh = targetEdge; dw = targetEdge * ratio; }
                    ctx.drawImage(data.image, -dw / 2, -dh / 2, dw, dh);
                    l.bw = Math.max(20, dw); l.bh = Math.max(20, dh);
                }
            } else {
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const fontSize = l.size * (l.font_scale / 100);
                if (l.type === 'icon') {
                    ctx.font = `${fontSize}px "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif`;
                } else {
                    ctx.font = `${fontSize}px ${l.font}`;
                }
                ctx.fillStyle = l.color;
                ctx.fillText(l.content, 0, 0);
                const m = ctx.measureText(l.content);
                l.bw = Math.max(20, m.width);
                l.bh = fontSize * 1.2;
            }

            if (l === sel) {
                ctx.setLineDash([6, 4]);
                ctx.strokeStyle = '#ff6fa5';
                ctx.lineWidth = 2;
                ctx.strokeRect(-l.bw / 2 - 4, -l.bh / 2 - 4, l.bw + 8, l.bh + 8);
            }
            ctx.restore();
        }
    }

    // --- Layer management
    function add(partial) {
        const l = {
            type: 'text',
            content: 'Chữ của bạn',
            nx: 0.5, ny: 0.45, nw: 0.6,
            rot: 0,
            color: '#111111',
            font: '"Be Vietnam Pro",sans-serif',
            size: 56,
            imgSize: 200,
            font_scale: 100,
            image_index: null,
            ...partial,
        };
        layers.push(l);
        select(l);
        requestRender();
        return l;
    }
    function removeLayer(l) {
        if (!l) return;
        if (l.type === 'image' && l.image_index != null) {
            const idx = l.image_index;
            const f = artworkFiles[idx];
            if (f?.origUrl) URL.revokeObjectURL(f.origUrl);
            artworkFiles.splice(idx, 1);
            layers.forEach((other) => {
                if (other.type === 'image' && other.image_index != null && other.image_index > idx) other.image_index--;
            });
            nextImageIndex = Math.max(0, nextImageIndex - 1);
        }
        layers = layers.filter((x) => x !== l);
        sel = null;
        syncUI();
        renderImageLayerChips();
        requestRender();
    }
    function bringToFront(l) {
        layers = layers.filter((x) => x !== l);
        layers.push(l);
        requestRender();
    }
    function select(l) {
        sel = l;
        syncUI();
        requestRender();
    }
    function syncUI() {
        if (!layerSection) return;
        const hasSel = !!sel;
        layerSection.hidden = !hasSel;
        if (!hasSel) return;

        layerTextOnly.hidden = sel.type !== 'text';
        layerImageOnly.hidden = sel.type !== 'image';
        layerColorRow.hidden = sel.type === 'image';

        if (sel.type === 'text') {
            selectedContentInput.value = sel.content;
            selectedFontInput.value = sel.font;
            selectedColorInput.value = sel.color;
        }
        selectedSizeInput.value = sel.type === 'image' ? sel.imgSize : sel.size;
        selectedRotationInput.value = sel.rot || 0;

        // Background removal state
        if (sel.type === 'image') {
            const data = artworkFiles[sel.image_index ?? 0];
            if (removeBackgroundButton) {
                removeBackgroundButton.textContent = data?.maskApplied ? '✨ Đang tách nền (bấm để tắt)' : '✨ Tách nền tự động';
            }
            if (bgToleranceWrap) {
                bgToleranceWrap.hidden = !data?.maskApplied;
                if (data?.tolerance != null) {
                    bgToleranceInput.value = data.tolerance;
                    bgToleranceValue.textContent = data.tolerance;
                }
            }
        }
        renderImageLayerChips();
    }

    // --- Pointer interactions on canvas
    function pointerPos(e) {
        const r = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - r.left) * (canvas.width / (window.devicePixelRatio || 1)) / r.width,
            y: (e.clientY - r.top) * (canvas.height / (window.devicePixelRatio || 1)) / r.height,
        };
    }
    function hit(l, p) {
        const a = -(l.rot || 0) * Math.PI / 180;
        const dx = p.x - l.nx * canvas.width / (window.devicePixelRatio || 1);
        const dy = p.y - l.ny * canvas.height / (window.devicePixelRatio || 1);
        const x = dx * Math.cos(a) - dy * Math.sin(a);
        const y = dx * Math.sin(a) + dy * Math.cos(a);
        const halfW = (l.bw || 0) / 2 + 6;
        const halfH = (l.bh || 0) / 2 + 6;
        return Math.abs(x) <= halfW && Math.abs(y) <= halfH;
    }

    canvas.addEventListener('pointerdown', (e) => {
        const p = pointerPos(e);
        let h = null;
        for (let i = layers.length - 1; i >= 0; i--) {
            if (hit(layers[i], p)) { h = layers[i]; break; }
        }
        if (h) {
            select(h);
            drag = { dx: p.x / canvas.width * (window.devicePixelRatio || 1) - h.nx, dy: p.y / canvas.height * (window.devicePixelRatio || 1) - h.ny, w: canvas.width, h: canvas.height };
            canvas.setPointerCapture(e.pointerId);
            canvas.style.cursor = 'grabbing';
        } else {
            select(null);
        }
    });
    canvas.addEventListener('pointermove', (e) => {
        if (!drag || !sel) return;
        const p = pointerPos(e);
        const dpr = window.devicePixelRatio || 1;
        sel.nx = p.x / (canvas.width / dpr) - drag.dx;
        sel.ny = p.y / (canvas.height / dpr) - drag.dy;
        // Clamp
        sel.nx = Math.max(0, Math.min(1, sel.nx));
        sel.ny = Math.max(0, Math.min(0.95, sel.ny));
        requestRender();
    });
    canvas.addEventListener('pointerup', () => { drag = null; canvas.style.cursor = 'grab'; });
    canvas.addEventListener('pointercancel', () => { drag = null; canvas.style.cursor = 'grab'; });

    // --- Wiring UI
    styleOptions.forEach((opt) => opt.addEventListener('click', () => setMug(opt.dataset.cupVariantOption)));
    iconOptions.forEach((btn) => btn.addEventListener('click', () => {
        add({ type: 'icon', content: btn.dataset.addIcon, ny: 0.65, size: 110 });
    }));
    addTextButton?.addEventListener('click', () => {
        add({ type: 'text', content: newText, ny: 0.35, size: 56 });
        selectedContentInput?.focus();
        selectedContentInput?.select();
    });

    artworkInput?.addEventListener('change', async (e) => {
        const files = Array.from(e.target.files ?? []);
        e.target.value = '';
        const added = [];
        const failed = [];
        for (const file of files) {
            if (!file.type.startsWith('image/')) { failed.push(file.name); continue; }
            try {
                const data = await loadImageData(file);
                artworkFiles.push(data);
                const idx = artworkFiles.length - 1;
                add({ type: 'image', content: 'artwork', image_index: idx, imgSize: 220, ny: 0.5 });
                added.push(file.name);
            } catch (err) {
                console.error(err);
                failed.push(file.name);
            }
        }
        if (artworkFiles.length === 0) {
            uploadCaption.textContent = 'JPG, PNG hoặc WEBP · Tối đa 5 MB';
        } else {
            uploadCaption.textContent = `${artworkFiles.length} ảnh đã chọn`;
        }
        renderImageLayerChips();
        renderUploadPreview();
        // Auto layout multiple images into grid
        if (added.length > 1) layoutImageLayers();
        const messages = [];
        if (added.length) messages.push(`Đã thêm ${added.length} ảnh: ${added.join(', ')}.`);
        if (failed.length) messages.push(`Không thể tải: ${failed.join(', ')}.`);
        if (messages.length) showToast(messages.join(' '));
    });

    selectedContentInput?.addEventListener('input', (e) => {
        if (!sel || sel.type !== 'text') return;
        sel.content = e.target.value || ' ';
        requestRender();
    });
    selectedFontInput?.addEventListener('change', async (e) => {
        if (!sel) return;
        sel.font = e.target.value;
        try { await document.fonts.load(`40px ${sel.font}`, sel.content); } catch (_) {}
        requestRender();
    });
    selectedColorInput?.addEventListener('input', (e) => {
        if (!sel) return;
        sel.color = e.target.value;
        requestRender();
    });
    selectedSizeInput?.addEventListener('input', (e) => {
        if (!sel) return;
        if (sel.type === 'image') sel.imgSize = +e.target.value;
        else sel.size = +e.target.value;
        requestRender();
    });
    selectedRotationInput?.addEventListener('input', (e) => {
        if (!sel) return;
        sel.rot = +e.target.value;
        requestRender();
    });
    removeLayerButton?.addEventListener('click', () => sel && removeLayer(sel));
    frontLayerButton?.addEventListener('click', () => sel && bringToFront(sel));

    // Background removal (flood-fill on a scaled mask, then destination-in)
    removeBackgroundButton?.addEventListener('click', async () => {
        if (!sel || sel.type !== 'image') return;
        const idx = sel.image_index;
        const data = artworkFiles[idx];
        if (!data) return;

        if (data.maskApplied) {
            // Toggle off → restore original
            data.image = data.orig;
            data.maskApplied = false;
            if (bgStatus) bgStatus.textContent = '';
            if (bgToleranceWrap) bgToleranceWrap.hidden = true;
            removeBackgroundButton.textContent = '✨ Tách nền tự động';
            requestRender();
            return;
        }

        removeBackgroundButton.disabled = true;
        removeBackgroundButton.textContent = 'Đang tách nền...';
        if (bgStatus) bgStatus.textContent = 'Đang xử lý ảnh trên thiết bị...';
        await new Promise((r) => setTimeout(r, 0));
        try {
            const tol = data.tolerance ?? 40;
            const masked = removeBackground(data.orig, tol);
            data.image = masked;
            data.maskApplied = true;
            data.tolerance = tol;
            if (bgStatus) bgStatus.textContent = 'Đã tách nền.';
            if (bgToleranceWrap) bgToleranceWrap.hidden = false;
            removeBackgroundButton.textContent = '✨ Đang tách nền (bấm để tắt)';
            requestRender();
        } catch (err) {
            console.error(err);
            if (bgStatus) bgStatus.textContent = 'Không thể xử lý ảnh này. Vui lòng thử ảnh khác.';
        } finally {
            removeBackgroundButton.disabled = false;
        }
    });

    let toleranceTimer = 0;
    bgToleranceInput?.addEventListener('input', (e) => {
        if (!sel || sel.type !== 'image') return;
        const data = artworkFiles[sel.image_index];
        const v = +e.target.value;
        bgToleranceValue.textContent = v;
        if (!data?.maskApplied) return;
        clearTimeout(toleranceTimer);
        toleranceTimer = setTimeout(() => {
            data.tolerance = v;
            try {
                data.image = removeBackground(data.orig, v);
                requestRender();
            } catch (err) { console.error(err); }
        }, 60);
    });

    function removeBackground(im, tol) {
        const scale = Math.min(1, 420 / Math.max(im.width, im.height));
        const w = Math.max(1, Math.round(im.width * scale));
        const h = Math.max(1, Math.round(im.height * scale));
        const maskCanvas = document.createElement('canvas');
        maskCanvas.width = w; maskCanvas.height = h;
        const mctx = maskCanvas.getContext('2d', { willReadFrequently: true });
        mctx.drawImage(im, 0, 0, w, h);
        const imgData = mctx.getImageData(0, 0, w, h);
        const { data } = imgData;
        const total = w * h;
        const samples = [[0, 0], [w - 1, 0], [0, h - 1], [w - 1, h - 1], [Math.floor(w / 2), 0], [0, Math.floor(h / 2)]];
        let avgR = 0, avgG = 0, avgB = 0, count = 0;
        for (const [px, py] of samples) {
            const i = (py * w + px) * 4;
            if (data[i + 3] > 20) { avgR += data[i]; avgG += data[i + 1]; avgB += data[i + 2]; count++; }
        }
        const hasBg = count > 0;
        if (hasBg) { avgR /= count; avgG /= count; avgB /= count; }
        const close = (i) => {
            if (data[i + 3] <= 18) return true;
            if (!hasBg) return false;
            return Math.max(Math.abs(data[i] - avgR), Math.abs(data[i + 1] - avgG), Math.abs(data[i + 2] - avgB)) <= Math.max(8, tol);
        };
        const queue = new Int32Array(total);
        const visited = new Uint8Array(total);
        let qEnd = 0, qStart = 0;
        const mark = (p) => {
            if (p < 0 || p >= total || visited[p]) return;
            if (!close(p * 4)) return;
            visited[p] = 1; queue[qEnd++] = p;
        };
        for (let x = 0; x < w; x++) { mark(x); mark((h - 1) * w + x); }
        for (let y = 0; y < h; y++) { mark(y * w); mark(y * w + w - 1); }
        while (qStart < qEnd) {
            const cur = queue[qStart++];
            const x = cur % w, y = (cur / w) | 0;
            if (x > 0) mark(cur - 1);
            if (x < w - 1) mark(cur + 1);
            if (y > 0) mark(cur - w);
            if (y < h - 1) mark(cur + w);
        }
        for (let p = 0; p < total; p++) {
            if (visited[p]) data[p * 4 + 3] = 0;
        }
        mctx.putImageData(imgData, 0, 0);
        const outScale = Math.min(1, 1800 / Math.max(im.width, im.height));
        const out = document.createElement('canvas');
        out.width = Math.max(1, Math.round(im.width * outScale));
        out.height = Math.max(1, Math.round(im.height * outScale));
        const octx = out.getContext('2d');
        octx.drawImage(im, 0, 0, out.width, out.height);
        octx.globalCompositeOperation = 'destination-in';
        octx.drawImage(maskCanvas, 0, 0, out.width, out.height);
        return out;
    }

    function loadImageData(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                const ratio = img.width / img.height;
                resolve({
                    file,
                    orig: img,
                    image: img,
                    name: file.name,
                    k: ratio,
                    origW: img.width,
                    origH: img.height,
                    origUrl: url,
                    maskApplied: false,
                    tolerance: 40,
                });
            };
            img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Không thể đọc ảnh ' + file.name)); };
            img.src = url;
        });
    }

    function layoutImageLayers() {
        const imgs = layers.filter((l) => l.type === 'image');
        if (!imgs.length) return;
        const cols = Math.min(3, Math.ceil(Math.sqrt(imgs.length)));
        const rows = Math.ceil(imgs.length / cols);
        imgs.forEach((l, i) => {
            const col = i % cols;
            const row = Math.floor(i / cols);
            l.nx = (col + 0.5) / cols;
            l.ny = (row + 0.5) / rows;
        });
        if (sel?.type === 'image') selectedSizeInput.value = sel.imgSize;
        requestRender();
    }

    function renderImageLayerChips() {
        if (!imageLayersList) return;
        const imgs = layers.filter((l) => l.type === 'image');
        imageLayersSection.hidden = imgs.length === 0;
        imageLayersList.replaceChildren();
        imgs.forEach((l, idx) => {
            const data = artworkFiles[l.image_index];
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'cup-image-chip' + (l === sel ? ' is-selected' : '');
            chip.setAttribute('aria-pressed', String(l === sel));
            const thumb = document.createElement('canvas');
            thumb.width = thumb.height = 76;
            const tctx = thumb.getContext('2d');
            if (data?.image) {
                const ratio = data.k || 1;
                const maxW = 68, maxH = 68;
                let w = maxW, h = maxW / ratio;
                if (h > maxH) { h = maxH; w = maxH * ratio; }
                tctx.drawImage(data.image, (76 - w) / 2, (76 - h) / 2, w, h);
            }
            const name = document.createElement('span');
            name.textContent = data?.name || `Ảnh ${idx + 1}`;
            chip.append(thumb, name);
            chip.addEventListener('click', () => select(l));
            imageLayersList.appendChild(chip);
        });
    }

    // Render upload preview thumbnails (visible immediately after upload)
    function renderUploadPreview() {
        if (!uploadPreviewSection || !uploadPreviewGrid) return;
        uploadPreviewGrid.replaceChildren();
        const count = artworkFiles.length;
        uploadPreviewSection.hidden = count === 0;
        if (uploadPreviewCount) uploadPreviewCount.textContent = String(count);

        artworkFiles.forEach((data, idx) => {
            const item = document.createElement('div');
            item.className = 'cup-upload-preview-item';

            const img = document.createElement('img');
            img.src = data.origUrl;
            img.alt = data.name || `Ảnh ${idx + 1}`;
            img.loading = 'lazy';

            const meta = document.createElement('div');
            meta.className = 'cup-upload-preview-meta';
            const name = document.createElement('span');
            name.className = 'cup-upload-preview-name';
            name.textContent = data.name || `Ảnh ${idx + 1}`;
            name.title = data.name || `Ảnh ${idx + 1}`;
            const sizeKb = data.file ? Math.round(data.file.size / 1024) : 0;
            const size = document.createElement('span');
            size.className = 'cup-upload-preview-size';
            size.textContent = sizeKb > 0 ? `${sizeKb} KB` : '';
            meta.append(name, size);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'cup-upload-preview-remove';
            remove.setAttribute('aria-label', `Xóa ${data.name || `Ảnh ${idx + 1}`}`);
            remove.title = 'Xóa ảnh này';
            remove.textContent = '×';
            remove.addEventListener('click', () => removeUploadPreviewItem(idx));

            item.append(img, meta, remove);
            uploadPreviewGrid.appendChild(item);
        });
    }

    // Remove a single artwork from the upload preview
    function removeUploadPreviewItem(idx) {
        // Remove the corresponding artwork data and any layer that uses it
        const data = artworkFiles[idx];
        if (data?.origUrl) URL.revokeObjectURL(data.origUrl);
        artworkFiles.splice(idx, 1);
        // Update layers that reference the removed image_index
        layers = layers.filter((l) => {
            if (l.type === 'image' && l.image_index === idx) return false;
            return true;
        });
        layers.forEach((l) => {
            if (l.type === 'image' && l.image_index != null && l.image_index > idx) l.image_index--;
        });
        nextImageIndex = Math.max(0, nextImageIndex - 1);
        sel = null;

        // Update caption
        if (artworkFiles.length === 0) {
            uploadCaption.textContent = 'JPG, PNG hoặc WEBP · Tối đa 5 MB';
        } else {
            uploadCaption.textContent = `${artworkFiles.length} ảnh đã chọn`;
        }

        syncUI();
        renderUploadPreview();
        renderImageLayerChips();
        requestRender();
    }

    // Clear all uploaded artworks
    function clearAllUploadPreview() {
        artworkFiles.forEach((data) => {
            if (data?.origUrl) URL.revokeObjectURL(data.origUrl);
        });
        artworkFiles = [];
        layers = layers.filter((l) => l.type !== 'image');
        sel = null;
        nextImageIndex = 0;
        uploadCaption.textContent = 'JPG, PNG hoặc WEBP · Tối đa 5 MB';
        if (artworkInput) artworkInput.value = '';
        syncUI();
        renderUploadPreview();
        renderImageLayerChips();
        requestRender();
    }
    uploadPreviewClear?.addEventListener('click', clearAllUploadPreview);

    // --- Price calc
    function calc() {
        const q = Math.max(1, Math.min(99, parseInt(quantityInput.value) || 1));
        const base = +(cupTypeInput?.value ?? unitPrice);
        const d = q >= discountMinimum ? 1 - discountPercentage / 100 : 1;
        return { q, base, total: Math.round(base * q * d), discount: d < 1 };
    }
    function updatePrice() {
        const c = calc();
        orderTotal.textContent = fmt.format(c.total) + 'đ';
        discountNote.textContent = c.discount
            ? `Đã giảm ${discountPercentage}% (từ ${discountMinimum} cốc)`
            : `Từ ${discountMinimum} cốc giảm ${discountPercentage}%`;
        quantityInput.value = String(c.q);
    }
    quantityInput?.addEventListener('input', updatePrice);
    cupTypeInput?.addEventListener('change', updatePrice);

    // --- Toast
    let toastTimer = 0;
    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 2500);
    }

    // --- Modal & form submit
    function openOrderModal() {
        if (layers.length === 0) {
            showToast('Hãy thêm ít nhất 1 phần tử (chữ, biểu tượng hoặc ảnh) trước khi đặt.');
            return;
        }
        const c = calc();
        const qtyInput = document.querySelector('[data-quantity]');
        if (qtyInput) qtyInput.value = c.q;
        renderModalPreview();
        modal.hidden = false;
        if (window.initVietnamAddressFields) window.initVietnamAddressFields();
    }
    function closeOrderModal() {
        modal.hidden = true;
    }
    modalCloseButtons.forEach((b) => b.addEventListener('click', closeOrderModal));

    function buildDesignFields() {
        if (!designFields) return;
        designFields.replaceChildren();
        layers.forEach((l, i) => {
            // Compute x, y, width in percentage (matches StoreCustomCupOrderRequest rules)
            const x = Math.max(0, Math.min(100, Math.round((l.nx - (l.type === 'text' ? l.nw / 2 : 0.2)) * 100)));
            const y = Math.max(0, Math.min(94, Math.round(l.ny * 100 - 5)));
            const width = l.type === 'text' ? Math.round(l.nw * 100) : 38;
            const inputs = [
                ['type', l.type],
                ['content', l.content],
                ['x', x],
                ['y', y],
                ['width', width],
            ];
            if (l.type === 'text') {
                inputs.push(['font_size', l.font_scale || 100]);
            }
            if (l.type === 'image') {
                inputs.push(['image_index', l.image_index ?? 0]);
            }
            inputs.forEach(([k, v]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `design[items][${i}][${k}]`;
                input.value = String(v);
                designFields.appendChild(input);
            });
        });
    }

    async function renderModalPreview() {
        // Use html2canvas to capture the preview stage exactly as shown
        try {
            // First ensure all images are loaded
            await new Promise((resolve) => setTimeout(resolve, 50));
            
            const previewStage = document.querySelector('[data-cup-preview]');
            if (!previewStage) return;
            
            const canvas = await html2canvas(previewStage, {
                backgroundColor: '#ffffff',
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
            });
            
            const url = canvas.toDataURL('image/png', 1);
            modalPreview.src = url;
            if (designPreviewInput) designPreviewInput.value = url;
        } catch (err) {
            console.error('Preview capture failed:', err);
            // Fallback to original method
            renderModalPreviewFallback();
        }

        // Build summary text
        const c2 = calc();
        modalSummary.textContent = `${currentMug.name} × ${c2.q} — ${fmt.format(c2.total)}đ`;
    }
    
    function renderModalPreviewFallback() {
        // Fallback: draw mug + canvas overlay
        const W = 720, H = 720;
        const c = document.createElement('canvas');
        c.width = W; c.height = H;
        const cx = c.getContext('2d');
        cx.fillStyle = '#fff';
        cx.fillRect(0, 0, W, H);
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            cx.drawImage(img, 0, 0, W, H);
            cx.globalCompositeOperation = 'multiply';
            const px = 0.376 * W, py = 0.264 * W, pw = 0.527 * W, ph = 0.547 * H;
            const tempC = document.createElement('canvas');
            tempC.width = canvas.width;
            tempC.height = canvas.height;
            tempC.getContext('2d').drawImage(canvas, 0, 0);
            cx.drawImage(tempC, px, py, pw, ph);
            cx.globalCompositeOperation = 'source-over';
            const url = c.toDataURL('image/png', 1);
            modalPreview.src = url;
            if (designPreviewInput) designPreviewInput.value = url;
        };
        img.onerror = () => {
            const url = c.toDataURL('image/png', 1);
            modalPreview.src = url;
            if (designPreviewInput) designPreviewInput.value = url;
        };
        img.src = currentMug.src;
    }

    openOrderButton?.addEventListener('click', () => {
        buildDesignFields();
        openOrderModal();
    });

    // Add to cart (uses session-only custom cart)
    addToCartButton?.addEventListener('click', async () => {
        if (layers.length === 0) { showToast('Hãy thêm ít nhất 1 phần tử trước khi cho vào giỏ.'); return; }
        if (isPreparingOrder) return;
        isPreparingOrder = true;
        addToCartButton.disabled = true;
        const c = calc();
        const payload = {
            _token: document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
            cup_variant: selectedVariantInput?.value ?? 'black',
            quantity: c.q,
            unit_price: c.base,
            total: c.total,
            design: { items: layers.map((l) => ({
                type: l.type, content: l.content,
                x: Math.round((l.nx - (l.type === 'text' ? l.nw / 2 : 0.2)) * 100),
                y: Math.round(l.ny * 100 - 5),
                width: l.type === 'text' ? Math.round(l.nw * 100) : 38,
                font_size: l.type === 'text' ? (l.font_scale || 100) : 100,
                image_index: l.type === 'image' ? (l.image_index ?? 0) : null,
            }))},
            preview: modalPreview.src || '',
        };
        try {
            const res = await fetch('/gio-hang-coc-tu-custom', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': payload._token },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });
            if (!res.ok) throw new Error('Không thể thêm vào giỏ');
            const data = await res.json().catch(() => ({}));
            showToast(data.message || 'Đã thêm vào giỏ hàng.');
            // Update cart badge
            if (data.cart_count != null && window.updateCartBadge) window.updateCartBadge(data.cart_count);
        } catch (err) {
            console.error(err);
            showToast('Có lỗi xảy ra. Vui lòng thử lại.');
        } finally {
            addToCartButton.disabled = false;
            isPreparingOrder = false;
        }
    });

    // --- Init
    function init() {
        const startVariant = styleOptions.find((o) => o.classList.contains('is-selected'))?.dataset.cupVariantOption || styleOptions[0]?.dataset.cupVariantOption;
        if (startVariant) setMug(startVariant);
        resizeCanvas();
        updatePrice();
        requestRender();
        syncUI();
    }

    window.addEventListener('resize', () => {
        resizeCanvas();
    });
    // Use ResizeObserver for accurate stage size
    if (window.ResizeObserver) {
        new ResizeObserver(() => resizeCanvas()).observe(preview);
    }
    init();
}
