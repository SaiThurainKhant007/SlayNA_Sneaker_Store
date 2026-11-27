/*
 * Product photo cut-out: makes the plain white background of product photos transparent, in the browser,
 * so the panel colour behind them (--sl-photo-bg) shows through while the shoe keeps its real colours.
 *
 * Works on every <img> inside a .sl-photo-bg panel (product cards, product page photo, thumbnails, Color/Type circles).
 * Only near-white pixels connected to the photo's edge are removed, so white parts inside the shoe stay.
 * Photos without a white edge (e.g. lifestyle shots) are left as they are. Each photo is processed once per page.
 * If anything fails, the original photo is shown.
 */
(() => {
    // Photos stay hidden until cut out (see .sl-cutout in style.css); added here, so without this script photos show normally
    document.documentElement.classList.add("sl-cutout");

    const MAX_SIDE = 1200;   // longest side used for the cut-out
    const WHITE = 248;       // a pixel counts as background when all channels are >= this...
    const GREY_SPREAD = 10;  // ...and it is not coloured (channels within this of each other)
    const cache = new Map(); // original url -> Promise<cut-out url or null>

    function cutOut(img) {
        const w0 = img.naturalWidth, h0 = img.naturalHeight;
        const scale = Math.min(1, MAX_SIDE / Math.max(w0, h0));
        const w = Math.round(w0 * scale), h = Math.round(h0 * scale);
        const canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(img, 0, 0, w, h);
        const data = ctx.getImageData(0, 0, w, h);
        const px = data.data;

        const light = (p) => {
            const i = p * 4;
            if (px[i + 3] < 20) return true;
            const r = px[i], g = px[i + 1], b = px[i + 2];
            const mn = Math.min(r, g, b), mx = Math.max(r, g, b);
            return mn >= WHITE && mx - mn < GREY_SPREAD;
        };

        // Flood fill from every edge pixel through near-white pixels
        const bg = new Uint8Array(w * h);
        const stack = [];
        for (let x = 0; x < w; x++) { stack.push(x, (h - 1) * w + x); }
        for (let y = 0; y < h; y++) { stack.push(y * w, y * w + w - 1); }
        let edgeWhite = 0;
        for (const p of stack) if (light(p)) edgeWhite++;
        if (edgeWhite < stack.length * 0.5) return null;   // not a white-background photo

        let count = 0;
        while (stack.length) {
            const p = stack.pop();
            if (bg[p] || !light(p)) continue;
            bg[p] = 1; count++;
            const x = p % w;
            if (x > 0) stack.push(p - 1);
            if (x < w - 1) stack.push(p + 1);
            if (p >= w) stack.push(p - w);
            if (p < w * (h - 1)) stack.push(p + w);
        }
        if (count > w * h * 0.97) return null;             // nothing left: not a product shot

        // Clear the background; fade light pixels on the shoe's outline so no white halo remains
        for (let p = 0; p < w * h; p++) {
            const i = p * 4;
            if (bg[p]) { px[i + 3] = 0; continue; }
            const x = p % w;
            const edge = (x > 0 && bg[p - 1]) || (x < w - 1 && bg[p + 1]) || (p >= w && bg[p - w]) || (p < w * (h - 1) && bg[p + w]);
            if (edge) {
                const mn = Math.min(px[i], px[i + 1], px[i + 2]);
                if (mn > 160) px[i + 3] = Math.round(px[i + 3] * Math.max(0, Math.min(1, (255 - mn) / 95)));
            }
        }
        ctx.putImageData(data, 0, 0);
        return new Promise((resolve) => canvas.toBlob((blob) => resolve(blob ? URL.createObjectURL(blob) : null), 'image/png'));
    }

    const cutUrls = new Set();

    function process(img) {
        const src = img.currentSrc || img.src;
        if (!src || cutUrls.has(src) || src.startsWith('data:')) { img.classList.add('is-cut'); return; }
        if (!cache.has(src)) {
            cache.set(src, Promise.resolve().then(() => cutOut(img)).catch(() => null));
        }
        cache.get(src).then((url) => {
            if ((img.currentSrc || img.src) !== src) return;   // photo was switched meanwhile
            if (url) { cutUrls.add(url); img.src = url; }
            img.classList.add('is-cut');
        });
    }

    const inPanel = (el) => el instanceof HTMLImageElement && el.closest('.sl-photo-bg');

    // Photos that load later (lazy loading, gallery switching)
    document.addEventListener('load', (e) => { if (inPanel(e.target)) process(e.target); }, true);
    // Hide a switched photo until its cut-out is ready (instant when it was cut before)
    new MutationObserver((records) => {
        for (const r of records) {
            const img = r.target;
            if (!inPanel(img) || cutUrls.has(img.src)) continue;
            const done = cache.get(img.src);
            img.classList.remove('is-cut');
            if (done) done.then((url) => { if (url) { cutUrls.add(url); img.src = url; } img.classList.add('is-cut'); });
        }
    }).observe(document.body, { subtree: true, attributes: true, attributeFilter: ['src'] });
    // Photos that finished loading before this script ran
    document.querySelectorAll('.sl-photo-bg img').forEach((img) => {
        if (img.complete && img.naturalWidth) process(img);
        else if (img.complete) img.classList.add('is-cut');   // broken image: show the fallback
    });
})();
