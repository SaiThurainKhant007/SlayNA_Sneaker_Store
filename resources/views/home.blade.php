@extends('layouts.app')

@section('title', 'Home')

@section('hero')
<section class="sl-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <p class="sl-eyebrow">Step up your style</p>
                <h1 class="sl-title">Find your<br><span>Perfect</span><br>Pair</h1>
                <p class="sl-sub">Exclusive sneakers. Authentic style.<br>Only at SlayNa Sneaker Store.</p>
                <div class="sl-hero-actions">
                    <a href="{{ route('products.index') }}" class="btn sl-btn-red">Shop Now &rarr;</a>
                    <a href="{{ route('products.new') }}" class="btn sl-btn-outline">New Arrivals</a>
                </div>
                @if ($topBrands->isNotEmpty())
                    <div class="sl-hero-tags">
                        <span>Popular:</span>
                        @foreach ($topBrands as $brand)
                            <a href="{{ route('products.index', ['brand' => $brand->id]) }}">{{ $brand->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-lg-7 sl-hero-media">
                {{-- 3D carousel (After Effects style): the shoes sit on a turning ring. The front one is big, sharp and
                     glowing; the rest wait behind it, smaller, angled away and fading into the dark. The ring holds
                     on each shoe, then swings to the next with an eased overshoot. Drag to turn it.
                     Add or remove <img> tags freely; the script adapts. --}}
                <div class="sl-hero-stage" role="img" aria-label="Featured sneakers: Air Jordan 7, Nike Air Yeezy, Louis Vuitton x Nike Air Force 1 and Air Jordan 4">
                    <span class="sl-hero-glow" aria-hidden="true"></span>
                    <span class="sl-hero-floor" aria-hidden="true"></span>
                    {{-- img/hero/: each shoe trimmed, scaled to the same visual size and set on the same floor line --}}
                    <img src="{{ asset('img/hero/shoe-1.webp') }}" class="sl-hero-shoe" alt="" width="1200" height="675">
                    <img src="{{ asset('img/hero/shoe-2.webp') }}" class="sl-hero-shoe" alt="" width="1200" height="675">
                    <img src="{{ asset('img/hero/shoe-3.webp') }}" class="sl-hero-shoe" alt="" width="1200" height="675">
                    <img src="{{ asset('img/hero/shoe-4.webp') }}" class="sl-hero-shoe" alt="" width="1200" height="675">
                    <div class="sl-hero-dots"></div>
                </div>
                <script>
                (() => {
                    const stage = document.currentScript.previousElementSibling;
                    const shoes = [...stage.querySelectorAll('.sl-hero-shoe')];
                    const dotsBox = stage.querySelector('.sl-hero-dots');
                    const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const N = shoes.length, STEP = 360 / N;
                    const HOLD = 2600, MOVE = 1150;   // ms resting on a shoe / ms swinging to the next

                    let W = stage.offsetWidth, angle = 0, tween = null, drag = null, holdUntil = 0, visible = true;
                    let front = -1;
                    const filters = [];

                    const dots = shoes.map((_, i) => {
                        const b = document.createElement('button');
                        b.type = 'button';
                        b.setAttribute('aria-label', `Show sneaker ${i + 1}`);
                        b.addEventListener('click', () => {
                            // Shortest way round to shoe i.
                            let d = ((i * STEP - angle) % 360 + 540) % 360 - 180;
                            goTo(angle + d);
                        });
                        dotsBox.appendChild(b);
                        return b;
                    });

                    // Ease in, swing a little past, settle back: the classic motion-graphics move.
                    const easeInOutBack = p => {
                        const c = 1.2 * 1.525;
                        return p < 0.5
                            ? (Math.pow(2 * p, 2) * ((c + 1) * 2 * p - c)) / 2
                            : (Math.pow(2 * p - 2, 2) * ((c + 1) * (p * 2 - 2) + c) + 2) / 2;
                    };
                    function goTo(target, now = performance.now()) {
                        if (still) { angle = target; tween = null; render(now); return; }
                        tween = { from: angle, to: target, start: now };
                    }

                    function render(now) {
                        const r = STEP > 0 ? Math.round(angle / STEP) : 0;
                        const f = ((r % N) + N) % N;
                        if (f !== front) {
                            front = f;
                            dots.forEach((d, i) => d.classList.toggle('is-active', i === f));
                        }
                        const bob = still ? 0 : Math.sin(now / 560) * W * 0.012;           // gentle float
                        shoes.forEach((img, i) => {
                            const t = (i * STEP - angle) * Math.PI / 180;
                            const depth = (1 - Math.cos(t)) / 2;                           // 0 front .. 1 back
                            const x = Math.sin(t) * W * 0.4;
                            const z = -depth * W * 1.1;
                            const turn = Math.sin(t) * 38;                                 // side shoes angle away
                            img.style.transform =
                                `translate(-50%, -50%) translate3d(${x.toFixed(1)}px, ${(bob * (1 - depth) - depth * W * 0.04).toFixed(1)}px, ${z.toFixed(1)}px) rotateY(${turn.toFixed(1)}deg) rotate(-6deg)`;
                            img.style.zIndex = String(100 - Math.round(depth * 50));
                            // Depth without blur: shoes further back stay sharp but sink into the dark (darker, less colour,
                            // fainter); the one straight behind fades out so it never ghosts through the front shoe.
                            const glow = Math.max(0, 1 - depth * 2.2) * 0.55;
                            const filter = `brightness(${(1 - depth * 0.6).toFixed(2)}) saturate(${(1 - depth * 0.5).toFixed(2)}) drop-shadow(0 25px 30px rgba(250,49,49,${glow.toFixed(2)}))`;
                            if (filters[i] !== filter) { img.style.filter = filters[i] = filter; }   // filters are costly: only on change
                            img.style.opacity = (1 - Math.pow(depth, 3)).toFixed(2);
                        });
                    }

                    function frame(now) {
                        if (visible) {
                            if (tween) {
                                const p = Math.min(1, (now - tween.start) / MOVE);
                                angle = tween.from + (tween.to - tween.from) * easeInOutBack(p);
                                if (p === 1) { angle = tween.to; tween = null; holdUntil = now + HOLD; }
                            } else if (!drag && N > 1 && now >= holdUntil) {
                                goTo(Math.round(angle / STEP) * STEP + STEP, now);
                            }
                            render(now);
                        }
                        requestAnimationFrame(frame);
                    }

                    window.addEventListener('resize', () => { W = stage.offsetWidth; render(performance.now()); });
                    holdUntil = performance.now() + HOLD;
                    render(performance.now());
                    if (still || N < 2) return;
                    requestAnimationFrame(frame);

                    // Drag to turn the ring; on release it settles on the nearest shoe.
                    stage.addEventListener('pointerdown', e => {
                        if (e.target.closest('button')) return;
                        drag = { x: e.clientX, v: 0 };
                        tween = null;
                        stage.setPointerCapture(e.pointerId);
                        stage.classList.add('is-dragging');
                    });
                    stage.addEventListener('pointermove', e => {
                        if (!drag) return;
                        const d = -(e.clientX - drag.x) / W * 160;
                        angle += d;
                        drag.v = d;
                        drag.x = e.clientX;
                    });
                    const release = () => {
                        if (!drag) return;
                        const target = Math.round((angle + drag.v * 6) / STEP) * STEP;   // a flick carries on a bit
                        drag = null;
                        stage.classList.remove('is-dragging');
                        goTo(target);
                    };
                    stage.addEventListener('pointerup', release);
                    stage.addEventListener('pointercancel', release);

                    // Don't animate while the hero is scrolled out of view.
                    new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; }).observe(stage);
                })();
                </script>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<h2 class="sl-section-title">Newest Arrivals</h2>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4 sl-grid">
    @forelse ($featured as $product)
        @include('products._card', ['product' => $product])
    @empty
        <p class="text-muted">No products available yet.</p>
    @endforelse
</div>

@if ($gallery->isNotEmpty())
    {{-- Promotional model photos (managed in Admin > Model Gallery). Tap a photo to view it full screen. --}}
    <section class="sl-gallery-section" aria-labelledby="gallery-title">
        <p class="sl-eyebrow text-center mb-1">Styled by SlayNa</p>
        <h2 id="gallery-title" class="sl-section-title text-center">The Lookbook</h2>
        <div class="sl-gallery">
            @foreach ($gallery as $photo)
                <figure class="sl-gallery-item">
                    <button type="button" class="sl-gallery-open" data-gallery-index="{{ $loop->index }}" aria-label="View larger: {{ $photo->altText() }}">
                        <img src="{{ $photo->imageUrl() }}" alt="{{ $photo->altText() }}" loading="lazy">
                    </button>
                    @if ($photo->title || $photo->subtitle || $photo->product)
                        <figcaption>
                            @if ($photo->title)<strong>{{ $photo->title }}</strong>@endif
                            @if ($photo->subtitle)<span>{{ $photo->subtitle }}</span>@endif
                            @if ($photo->product)
                                <a href="{{ route('products.show', $photo->product) }}" class="sl-gallery-shop">Shop this look <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                            @endif
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </section>

    <dialog class="sl-lightbox" aria-label="Lookbook photo">
        <button type="button" class="sl-lightbox-close" aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        <button type="button" class="sl-lightbox-nav sl-lightbox-prev" aria-label="Previous photo"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
        <figure class="sl-lightbox-figure">
            <img src="" alt="">
            <figcaption></figcaption>
        </figure>
        <button type="button" class="sl-lightbox-nav sl-lightbox-next" aria-label="Next photo"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
    </dialog>
    <script>
    // Lightbox: open a photo full screen; arrows, swipe and the keyboard move between photos; Esc or a tap outside closes
    (() => {
        const items = [...document.querySelectorAll('.sl-gallery-item')];
        const box = document.querySelector('.sl-lightbox');
        const img = box.querySelector('img');
        const cap = box.querySelector('figcaption');
        let current = 0, startX = null;

        const show = i => {
            current = (i + items.length) % items.length;
            const src = items[current].querySelector('img');
            img.src = src.src;
            img.alt = src.alt;
            cap.textContent = `${src.alt}  ·  ${current + 1} / ${items.length}`;
        };
        items.forEach((item, i) => item.querySelector('.sl-gallery-open').addEventListener('click', () => { show(i); box.showModal(); }));
        box.querySelector('.sl-lightbox-close').addEventListener('click', () => box.close());
        box.querySelector('.sl-lightbox-prev').addEventListener('click', () => show(current - 1));
        box.querySelector('.sl-lightbox-next').addEventListener('click', () => show(current + 1));
        box.addEventListener('click', e => { if (e.target === box) box.close(); });
        box.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') show(current - 1);
            if (e.key === 'ArrowRight') show(current + 1);
        });
        box.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', e => {
            if (startX === null) return;
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 50) show(current + (dx < 0 ? 1 : -1));
            startX = null;
        });
        if (items.length < 2) box.querySelectorAll('.sl-lightbox-nav').forEach(b => b.hidden = true);
    })();
    </script>
@endif

@if ($marqueeBrands->isNotEmpty())
    <section class="sl-marquee-section" aria-labelledby="marquee-title">
        <h2 id="marquee-title" class="sl-section-title text-center">Shop by Brand</h2>
        <div class="sl-marquee" style="--sl-marquee-duration: {{ max(20, $marqueeBrands->count() * 4) }}s;">
            {{-- The list is rendered twice so the strip loops seamlessly; the copy is hidden from screen readers --}}
            @foreach ([false, true] as $isCopy)
                <ul class="sl-marquee-track" @if ($isCopy) aria-hidden="true" @endif>
                    @foreach ($marqueeBrands as $brand)
                        <li>
                            <a href="{{ route('products.index', ['brand' => $brand->id]) }}" @if ($isCopy) tabindex="-1" @endif title="Shop {{ $brand->name }}">
                                <img src="{{ $brand->logoUrl() }}" alt="{{ $isCopy ? '' : $brand->name }}" loading="lazy">
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </section>
@endif

@include('partials._brand_videos')

@if ($promotions->isNotEmpty())
    <section class="sl-promo-section" aria-labelledby="promo-title">
        <h2 id="promo-title" class="sl-section-title text-center">Hot Deals</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4">
            @foreach ($promotions as $promo)
                <div class="col">
                    <article class="card sl-promo-card h-100">
                        {{-- Hover (mouse) or tap (touch) swaps to the second image --}}
                        <div class="sl-promo-media" @if ($promo->hoverImageUrl()) tabindex="0" aria-label="{{ $promo->product->name }}: tap to see another view" @endif>
                            <img src="{{ $promo->imageUrl() }}" alt="{{ $promo->product->name }}" class="sl-promo-img" loading="lazy">
                            @if ($promo->hoverImageUrl())
                                <img src="{{ $promo->hoverImageUrl() }}" alt="" class="sl-promo-img sl-promo-img--alt" loading="lazy">
                                <span class="sl-promo-hint" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                            @endif
                            <span class="sl-promo-badge">-{{ $promo->discount_percent }}%</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="sl-promo-tag mb-1">{{ $promo->title }}</p>
                            <h3 class="h6 mb-1">{{ $promo->product->name }}</h3>
                            <p class="text-muted small mb-2">{{ $promo->product->brand->name }}@if ($promo->description) &middot; {{ $promo->description }}@endif</p>
                            <div class="sl-promo-price mb-1">
                                <span class="sl-promo-now">{{ number_format($promo->discountedPrice(), 0) }} MMK</span>
                                <span class="sl-promo-was">{{ number_format((float) $promo->product->price, 0) }} MMK</span>
                            </div>
                            <p class="small text-success mb-2">You save {{ number_format($promo->savings(), 0) }} MMK</p>
                            @if ($promo->ends_on)
                                <p class="small text-muted mb-2"><i class="bi bi-clock" aria-hidden="true"></i> Ends {{ $promo->ends_on->format('j M Y') }}</p>
                            @endif
                            <a href="{{ route('products.show', $promo->product) }}" class="btn sl-btn-red btn-sm mt-auto">Shop the Deal</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif

@include('partials._promo_swap_script')
@endsection
