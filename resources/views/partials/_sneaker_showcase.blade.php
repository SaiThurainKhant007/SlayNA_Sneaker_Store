{{--
    Sneaker Showcase (home page, below Newest Arrivals): a drag-to-orbit wheel.
    Every frame, each sneaker's angle goes through sin/cos into one CSS matrix() transform.
    Under the wheel: arrows, a "Spin for me" button that lands on a random sneaker,
    and the name and price of the sneaker at the front.
    $showcase: list of ['image' => url, 'product' => Product], from config/shop.php 'showcase'.
--}}
@if ($showcase->isNotEmpty())
<section class="sl-showcase" aria-labelledby="showcase-title">
    <p class="sl-eyebrow text-center mb-1">Drag, swipe, use the arrows or let us pick</p>
    <h2 id="showcase-title" class="sl-section-title text-center">Spin the Wheel</h2>

    <div class="sl-orbit" data-orbit tabindex="0" role="region" aria-roledescription="carousel"
         aria-label="Sneaker wheel. Use the left and right arrow keys to spin.">
        <div class="sl-orbit-stage" data-orbit-stage>
            @foreach ($showcase as $item)
                <a class="sl-orbit-item" href="{{ route('products.show', $item['product']) }}" data-orbit-item
                   data-name="{{ $item['product']->name }}" data-price="{{ $item['product']->formattedPrice() }}"
                   aria-label="{{ $item['product']->brand->name }} {{ $item['product']->name }}, {{ $item['product']->formattedPrice() }}">
                    <img src="{{ $item['image'] }}" alt="" draggable="false" loading="lazy">
                </a>
            @endforeach
        </div>
        <div class="sl-orbit-floor" aria-hidden="true"></div>
    </div>

    <div class="sl-orbit-controls">
        <button type="button" class="sl-orbit-btn" data-orbit-step="-1" aria-label="Previous sneaker"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
        <button type="button" class="sl-orbit-spin" data-orbit-spin><i class="bi bi-shuffle" aria-hidden="true"></i> Spin for me</button>
        <button type="button" class="sl-orbit-btn" data-orbit-step="1" aria-label="Next sneaker"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </div>

    {{-- Name and price of the sneaker at the front of the wheel --}}
    <p class="sl-orbit-caption" data-orbit-caption aria-live="polite">
        <span class="sl-orbit-pick" data-orbit-pick hidden><i class="bi bi-stars" aria-hidden="true"></i> Your pick</span>
        <strong data-orbit-name>{{ $showcase[0]["product"]->name }}</strong>
        <span data-orbit-price>{{ $showcase[0]["product"]->formattedPrice() }}</span>
    </p>
</section>

<script>
// Orbit wheel: angle -> sin/cos -> one matrix() per sneaker, recalculated every animation frame
(() => {
    const root = document.querySelector('[data-orbit]');
    if (!root) return;
    const stage = root.querySelector('[data-orbit-stage]');
    const items = [...root.querySelectorAll('[data-orbit-item]')];
    const caption = document.querySelector('[data-orbit-caption]');
    const spinBtn = document.querySelector('[data-orbit-spin]');
    const n = items.length;
    const step = (Math.PI * 2) / n;          // angle between neighbours
    const still = matchMedia('(prefers-reduced-motion: reduce)').matches;

    let rot = 0;          // wheel angle in radians; sneaker i faces front when rot = -i * step
    let target = null;    // angle we are easing towards (buttons, keys, snap after a drag)
    let velocity = 0;     // radians per frame, kept after a drag for a short coast
    let drag = null;      // {x, rot, lastX, moved}
    let spin = null;      // "Spin for me": {from, to, start, duration}
    let frame = 0;
    let front = 0;
    // Auto-rotate: a slow, steady spin (one full turn every 30 s).
    // It pauses while the wheel is hovered, focused or dragged, and for a few seconds after a click.
    const AUTO_SPEED = (Math.PI * 2) / 30000;   // radians per millisecond
    const RESUME_AFTER = 3000;                  // ms of rest after the shopper used the wheel
    let hovered = false, focused = false;
    let restUntil = 0;
    let last = 0;                               // time of the previous frame
    const autoOn = () => !still && !hovered && !focused && !drag && !spin && performance.now() >= restUntil;
    const rest = (ms = RESUME_AFTER) => { restUntil = performance.now() + ms; };

    stage.classList.add('is-live');

    // Name and price under the wheel; "Your pick" after a "Spin for me" landing
    function showCard(idx, picked = false) {
        caption.querySelector('[data-orbit-name]').textContent = items[idx].dataset.name;
        caption.querySelector('[data-orbit-price]').textContent = items[idx].dataset.price;
        caption.querySelector('[data-orbit-pick]').hidden = !picked;
        caption.classList.toggle('is-picked', picked);
    }

    function layout() {
        const w = stage.clientWidth;
        const rx = Math.min(w * 0.38, 420);   // horizontal radius of the orbit
        const ry = Math.min(w * 0.1, 85);     // vertical radius: the wheel is seen from above, so the back row shows behind the front
        items.forEach((item, i) => {
            const theta = rot + i * step;
            const sin = Math.sin(theta), cos = Math.cos(theta);
            const depth = (cos + 1) / 2;                  // 1 = front, 0 = back
            const s = 0.45 + 0.55 * depth;                // scale with depth
            const a = s * (0.55 + 0.45 * Math.abs(cos));  // squash sideways near the edges, like turning away
            const b = 0.12 * sin * s;                     // slight tilt in the direction of travel
            const e = rx * sin;                           // x position on the orbit
            const f = ry * cos;                           // y: front sits lower than back
            item.style.transform = `matrix(${a.toFixed(4)}, ${b.toFixed(4)}, 0, ${s.toFixed(4)}, ${e.toFixed(1)}, ${f.toFixed(1)})`;
            item.style.zIndex = String(Math.round(depth * 100));
            item.style.opacity = (0.3 + 0.7 * depth).toFixed(3);
            item.tabIndex = -1;
        });

        const idx = ((Math.round(-rot / step) % n) + n) % n;
        items[idx].tabIndex = 0;
        if (idx !== front) {
            front = idx;
            if (!spin) showCard(idx);   // during "Spin for me" the card waits for the landing
        }
    }

    function tick(now) {
        frame = 0;
        const dt = last ? Math.min(now - last, 50) : 16;   // cap the gap after a background tab
        last = now;
        if (spin) {
            // "Spin for me": fast start, slow landing (ease-out cubic)
            const t = Math.min(1, (now - spin.start) / spin.duration);
            rot = spin.from + (spin.to - spin.from) * (1 - Math.pow(1 - t, 3));
            if (t === 1) { rot = spin.to; spin = null; land(); }
        } else if (drag) {
            // position is already set by pointermove; just redraw
        } else if (Math.abs(velocity) > 0.0005) {
            rot += velocity;               // coast after release
            velocity *= 0.92;
            if (Math.abs(velocity) <= 0.0005) target = Math.round(rot / step) * step;
        } else if (target !== null) {
            const diff = target - rot;     // ease into place
            rot = Math.abs(diff) < 0.001 ? target : rot + diff * 0.18;
            if (rot === target) target = null;
        } else if (autoOn()) {
            rot -= AUTO_SPEED * dt;        // same direction as the "next" arrow
        }
        layout();
        // Keep the loop going while resting too, so the spin restarts by itself
        if (spin || drag || target !== null || Math.abs(velocity) > 0.0005 || (!still && !hovered && !focused)) {
            frame = requestAnimationFrame(tick);
        } else {
            last = 0;
        }
    }
    const run = () => { if (!frame) frame = requestAnimationFrame(tick); };

    // "Spin for me": two to three full turns, landing on a random sneaker other than the current one
    function land() {
        layout();
        showCard(front, true);
        spinBtn.disabled = false;
        rest(6000);                        // let the shopper look at their pick before auto-rotate resumes
    }
    spinBtn.addEventListener('click', () => {
        if (spin) return;
        velocity = 0; target = null;
        const current = ((Math.round(-rot / step) % n) + n) % n;
        const offset = 1 + Math.floor(Math.random() * (n - 1));          // 1..n-1 sneakers further on
        const base = Math.round(rot / step) * step;
        const to = base - (offset + n * (2 + Math.floor(Math.random() * 2))) * step;
        spinBtn.disabled = true;
        if (still) { rot = base - offset * step; land(); return; }
        showCard(current);
        spin = { from: rot, to, start: performance.now(), duration: 2600 };
        run();
    });

    // Stop on the nearest sneaker when the shopper points at or tabs into the wheel
    const settle = () => { if (!drag && !spin && Math.abs(velocity) <= 0.0005) { target = Math.round(rot / step) * step; run(); } };
    root.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') { hovered = true; settle(); } });
    root.addEventListener('pointerleave', () => { hovered = false; run(); });
    root.addEventListener('focusin', () => { focused = true; settle(); });
    root.addEventListener('focusout', (e) => { if (!root.contains(e.relatedTarget)) { focused = false; run(); } });

    function go(delta) {
        if (spin) return;
        velocity = 0;
        rest();
        target = Math.round(((target ?? rot) - delta * step) / step) * step;
        if (still) { rot = target; target = null; layout(); } else run();
    }

    stage.addEventListener('pointerdown', (e) => {
        if (e.button !== 0 || spin) return;
        drag = { x: e.clientX, rot, lastX: e.clientX, moved: false };
        velocity = 0; target = null;
        stage.setPointerCapture(e.pointerId);
        stage.classList.add('is-dragging');
        run();
    });
    stage.addEventListener('pointermove', (e) => {
        if (!drag) return;
        const rx = Math.min(stage.clientWidth * 0.38, 420);
        if (Math.abs(e.clientX - drag.x) > 5) drag.moved = true;
        const dx = e.clientX - drag.lastX;
        drag.lastX = e.clientX;
        velocity = dx / rx;                         // remembered for the coast
        rot = drag.rot + (e.clientX - drag.x) / rx; // arc length / radius = angle
    });
    const end = () => {
        if (!drag) return;
        stage.classList.remove('is-dragging');
        root.dataset.dragged = drag.moved ? '1' : '';
        drag = null;
        rest();
        if (still || Math.abs(velocity) <= 0.0005) { velocity = 0; target = Math.round(rot / step) * step; }
        if (still) { rot = target; target = null; layout(); } else run();
    };
    stage.addEventListener('pointerup', end);
    stage.addEventListener('pointercancel', end);
    // A drag should spin the wheel, not open the product underneath
    stage.addEventListener('click', (e) => { if (root.dataset.dragged) { e.preventDefault(); root.dataset.dragged = ''; } }, true);

    document.querySelectorAll('[data-orbit-step]').forEach((btn) => btn.addEventListener('click', () => go(+btn.dataset.orbitStep)));
    root.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    });
    addEventListener('resize', layout);
    layout();
    run();   // start the automatic spin
})();
</script>
@endif
