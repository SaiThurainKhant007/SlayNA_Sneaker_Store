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

@include('partials._sneaker_showcase')

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
                    @if ($photo->title || $photo->subtitle)
                        <figcaption>
                            @if ($photo->title)<strong>{{ $photo->title }}</strong>@endif
                            @if ($photo->subtitle)<span>{{ $photo->subtitle }}</span>@endif
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
                        <div class="sl-promo-media" @if ($promo->hoverImageUrl()) tabindex="0" aria-label="{{ $promo->title }}: tap to see another view" @endif>
                            <img src="{{ $promo->imageUrl() }}" alt="{{ $promo->title }}" class="sl-promo-img" loading="lazy">
                            @if ($promo->hoverImageUrl())
                                <img src="{{ $promo->hoverImageUrl() }}" alt="" class="sl-promo-img sl-promo-img--alt" loading="lazy">
                                <span class="sl-promo-hint" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                            @endif
                            <span class="sl-promo-badge">-{{ $promo->discount_percent }}%</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="mb-1"><span class="sl-type-pill">Promotion</span></p>
                            <h3 class="h6 mb-1">{{ $promo->title }}</h3>
                            <p class="text-muted small mb-2">{{ $promo->displayBrand()?->name }}@if ($promo->description) &middot; {{ $promo->description }}@endif</p>
                            <div class="sl-promo-price mb-1">
                                <span class="sl-promo-now">{{ number_format($promo->discountedPrice(), 0) }} MMK</span>
                                <span class="sl-promo-was">{{ number_format($promo->listPrice(), 0) }} MMK</span>
                            </div>
                            <p class="small text-success mb-2">You save {{ number_format($promo->savings(), 0) }} MMK</p>
                            @if ($promo->ends_on)
                                <p class="small text-muted mb-2"><i class="bi bi-clock" aria-hidden="true"></i> Ends {{ $promo->ends_on->format('j M Y') }}</p>
                            @endif
                            <a href="{{ route('products.index', array_filter(['brand' => $promo->brand_id])) }}" class="btn sl-btn-red btn-sm mt-auto">Shop the Brand</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif

@if ($limitedEditions->isNotEmpty())
    {{-- Limited Edition: featured at full price, never discounted --}}
    <section class="sl-promo-section sl-limited-section" aria-labelledby="limited-title">
        <p class="sl-eyebrow text-center mb-1">Rare pairs, only here, while they last</p>
        <h2 id="limited-title" class="sl-section-title text-center">Limited Edition</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4">
            @foreach ($limitedEditions as $promo)
                <div class="col">
                    <article class="card sl-promo-card sl-limited-card h-100">
                        <div class="sl-promo-media" @if ($promo->hoverImageUrl()) tabindex="0" aria-label="{{ $promo->product->name }}: tap to see another view" @endif>
                            <img src="{{ $promo->imageUrl() }}" alt="{{ $promo->product->name }}" class="sl-promo-img" loading="lazy">
                            @if ($promo->hoverImageUrl())
                                <img src="{{ $promo->hoverImageUrl() }}" alt="" class="sl-promo-img sl-promo-img--alt" loading="lazy">
                                <span class="sl-promo-hint" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                            @endif
                            <span class="sl-promo-badge sl-limited-badge"><i class="bi bi-gem" aria-hidden="true"></i> Limited Edition</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="mb-1"><span class="sl-type-pill sl-type-pill--limited">Limited Edition</span></p>
                            <h3 class="sl-limited-name mb-1">{{ $promo->title }}</h3>
                            <p class="text-muted small mb-2">{{ $promo->product->brand->name }}@if ($promo->description) &middot; {{ $promo->description }}@endif</p>
                            <div class="sl-promo-price mb-2">
                                <span class="sl-limited-price">{{ number_format((float) $promo->product->price, 0) }} MMK</span>
                            </div>
                            {{-- Bought right here: pick a size, then Add to Cart (cart and checkout as usual) --}}
                            @php
                                $sizes = $promo->product->variants->sortBy(fn ($v) => (float) $v->size->label)->values();
                                $first = $sizes->first(fn ($v) => $v->stock_quantity > 0);
                            @endphp
                            @if (! $first)
                                <p class="sl-limited-soldout mt-auto mb-0">Sold out</p>
                            @elseif (auth()->check() && ! auth()->user()->isCustomer())
                                <p class="small text-muted mt-auto mb-0">Customers buy Limited Editions here.</p>
                            @else
                                <form method="POST" action="{{ route('cart.store') }}" class="sl-limited-buy mt-auto" data-limited-buy>
                                    @csrf
                                    <fieldset class="mb-2">
                                        <legend class="small fw-semibold mb-1">Size: <span data-limited-size>EU {{ $first->size->label }}</span></legend>
                                        <div class="sl-limited-sizes">
                                            @foreach ($sizes as $variant)
                                                @php $out = $variant->stock_quantity <= 0; @endphp
                                                <label class="sl-size sl-size--sm {{ $out ? 'is-sold-out' : '' }}">
                                                    <input type="radio" name="product_size_id" value="{{ $variant->id }}" required data-size="EU {{ $variant->size->label }}"
                                                           data-stock="{{ $variant->stock_quantity }}" @checked($variant->id === $first->id) @disabled($out)>
                                                    <span>{{ $variant->size->label }}<span class="visually-hidden">{{ $out ? ', sold out' : '' }}</span></span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="visually-hidden" for="limited-qty-{{ $promo->id }}">Quantity</label>
                                        <input type="number" id="limited-qty-{{ $promo->id }}" name="quantity" value="1" min="1" max="{{ min(10, $first->stock_quantity) }}"
                                               class="form-control form-control-sm sl-limited-qty" data-limited-qty>
                                        @auth
                                            <button type="submit" class="btn sl-btn-limited btn-sm flex-fill">Add to Cart</button>
                                        @else
                                            <a href="{{ route('login') }}" class="btn sl-btn-limited btn-sm flex-fill">Log in to Buy</a>
                                        @endauth
                                    </div>
                                </form>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif

@include('partials._promo_swap_script')
<script>
// Limited Edition buy boxes: the chosen size sets the label and the most you can add
document.querySelectorAll('[data-limited-buy]').forEach((form) => {
    const label = form.querySelector('[data-limited-size]');
    const qty = form.querySelector('[data-limited-qty]');
    form.querySelectorAll('input[name=product_size_id]').forEach((r) => r.addEventListener('change', () => {
        label.textContent = r.dataset.size;
        qty.max = Math.min(10, +r.dataset.stock);
        if (+qty.value > +qty.max) qty.value = qty.max;
    }));
});
</script>

{{-- Home page only: corner badge. The "SY" monogram stays still; the store name rotates around it. --}}
<div class="sl-spin-badge" aria-hidden="true">
    <svg class="sl-spin-badge-ring" viewBox="0 0 120 120" focusable="false">
        <defs>
            <path id="sl-spin-badge-path" d="M60,60 m-45,0 a45,45 0 1,1 90,0 a45,45 0 1,1 -90,0"/>
        </defs>
        <text><textPath href="#sl-spin-badge-path" textLength="282" lengthAdjust="spacing">SLAYNA ★ SNEAKER STORE ★</textPath></text>
    </svg>
    <span class="sl-spin-badge-mark">@include('partials._logo_mark', ['id' => 'sl-mark-badge'])</span>
</div>
@endsection
