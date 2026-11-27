@extends('layouts.app')

@section('title', $product->name)
@section('description', Str::limit(trim($product->brand->name.' '.$product->name.' – '.$product->formattedPrice().'. '.preg_replace('/\s+/', ' ', (string) $product->description)), 155))
@section('og_type', 'product')
@section('og_image', $product->imageUrl())

@section('content')
<div class="row">
    <div class="col-md-5">
        @php $photos = $product->galleryUrls(); @endphp
        <div class="sl-pdp-gallery" data-pdp-gallery>
            <div class="sl-pdp-main sl-photo-bg">
                <img src="{{ $photos[0] }}" alt="{{ $product->name }}" data-pdp-main onerror="this.src='{{ asset('img/no-image.png') }}'">
            </div>
            @if (count($photos) > 1)
                <span class="sl-pdp-count" data-pdp-count aria-live="polite">1 / {{ count($photos) }}</span>
                <div class="sl-pdp-thumbs" role="group" aria-label="{{ $product->name }} photos">
                    @foreach ($photos as $url)
                        <button type="button" class="sl-pdp-thumb sl-photo-bg {{ $loop->first ? 'is-active' : '' }}" data-pdp-thumb="{{ $url }}" aria-label="Show photo {{ $loop->iteration }} of {{ count($photos) }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <img src="{{ $url }}" alt="" loading="lazy" onerror="this.src='{{ asset('img/no-image.png') }}'">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-7">
        <h1 class="h3">{{ $product->name }}</h1>
        <p class="text-muted">{{ $product->brand->name }} &middot; {{ $product->category->name }}</p>
        <h2 class="h4">
            {{ $product->formattedPrice() }}
            @if ($product->livePromotion)
                <small class="sl-promo-was">{{ number_format((float) $product->price, 0) }} MMK</small>
                <span class="badge text-bg-danger align-middle">Promotion &middot; -{{ $product->livePromotion->discount_percent }}% &middot; {{ $product->livePromotion->title }}</span>
            @endif
        </h2>
        <p>{!! nl2br(e($product->description)) !!}</p>

        @php
            $gallery = collect($product->gallery())->map(fn ($p, $i) => $p + ['name' => $p['label'] ?: 'Photo '.($i + 1)]);
            // Stock per colour: sizes follow the Color/Type picked. Start on the first photo whose colour has stock rows.
            $hasColor = fn ($color) => $color && $product->variants->contains(fn ($v) => mb_strtolower((string) $v->color) === mb_strtolower($color));
            $startIndex = $gallery->search(fn ($p) => $hasColor($p['label']));
            $colorMode = $startIndex !== false;
            $startIndex = $colorMode ? $startIndex : 0;
            $startColor = $colorMode ? $gallery[$startIndex]['label'] : null;
        @endphp
        @if (count($photos) > 1)
            {{-- Color/Type circles: the same photos as the gallery thumbnails, each with the name admin gave it --}}
            <div class="sl-buy-group">
                <p class="sl-buy-label">Color/Type: <span data-pdp-swatch-label aria-live="polite">{{ $gallery[$startIndex]['name'] }}</span></p>
                <div class="sl-swatches" role="group" aria-label="{{ $product->name }} colors and types">
                    @foreach ($gallery as $photo)
                        <button type="button" class="sl-swatch {{ $loop->index === $startIndex ? 'is-active' : '' }}" data-pdp-swatch="{{ $loop->index }}"
                                data-name="{{ $photo['name'] }}" data-color="{{ $photo['label'] }}" title="{{ $photo['name'] }}"
                                aria-label="{{ $photo['name'] }}" aria-pressed="{{ $loop->index === $startIndex ? 'true' : 'false' }}">
                            <span class="sl-photo-bg"><img src="{{ $photo['url'] }}" alt="" loading="lazy" onerror="this.src='{{ asset('img/no-image.png') }}'"></span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @if (!auth()->check() || auth()->user()->isCustomer())
            @php
                $variants = $product->variants->sortBy(fn ($v) => (float) $v->size->label)->values();
                $inColor = fn ($v) => ! $colorMode || mb_strtolower((string) $v->color) === mb_strtolower($startColor);
                $firstInStock = $variants->first(fn ($v) => $inColor($v) && $v->stock_quantity > 0);
            @endphp
            <form method="POST" action="{{ route('cart.store') }}" class="sl-buy" data-buy-form @if ($colorMode) data-color-mode @endif>
                @csrf
                <fieldset class="sl-buy-group">
                    <legend class="sl-buy-label">Size: <span data-size-label>{{ $firstInStock ? 'EU '.$firstInStock->size->label : 'Sold out' }}</span></legend>
                    <div class="sl-size-grid">
                        @foreach ($variants as $variant)
                            @php $soldOut = $variant->stock_quantity <= 0; @endphp
                            <label class="sl-size {{ $soldOut ? 'is-sold-out' : '' }}" @unless ($inColor($variant)) hidden @endunless>
                                <input type="radio" name="product_size_id" value="{{ $variant->id }}" required
                                       data-size="EU {{ $variant->size->label }}" data-stock="{{ $variant->stock_quantity }}" data-color="{{ $variant->color }}"
                                       @checked($firstInStock && $firstInStock->id === $variant->id) @disabled($soldOut || ! $inColor($variant))>
                                <span>EU {{ $variant->size->label }}<span class="visually-hidden">{{ $soldOut ? ', sold out' : '' }}</span></span>
                            </label>
                        @endforeach
                    </div>
                    <p class="sl-stock-note" data-stock-note aria-live="polite"></p>
                </fieldset>

                @if ($soldRecently >= 3)
                    <p class="sl-going-fast"><span aria-hidden="true">🔥</span> <strong>Going fast!</strong> <em>{{ $soldRecently }} pairs sold in the last 30 days.</em></p>
                @endif

                <div class="sl-buy-row">
                    <div class="sl-qty" data-qty>
                        <button type="button" class="sl-qty-btn" data-qty-step="-1" aria-label="One less"><i class="bi bi-dash" aria-hidden="true"></i></button>
                        <input type="number" name="quantity" value="1" min="1" max="{{ min(10, $firstInStock?->stock_quantity ?? 1) }}" inputmode="numeric" aria-label="Quantity" data-qty-input>
                        <button type="button" class="sl-qty-btn" data-qty-step="1" aria-label="One more"><i class="bi bi-plus" aria-hidden="true"></i></button>
                    </div>
                    @auth
                        <button type="submit" class="sl-add-btn" data-add-btn @disabled(! $firstInStock)>{{ $firstInStock ? 'Add to Cart' : 'Sold Out' }}</button>
                    @else
                        <a href="{{ route('login') }}" class="sl-add-btn">Log in to Buy</a>
                    @endauth
                </div>
            </form>
        @endif
    </div>
</div>

@if ($related->isNotEmpty())
    <section class="mt-5" aria-labelledby="related-title">
        <h2 id="related-title" class="h5 mb-3">You May Also Like</h2>
        <div class="row row-cols-2 row-cols-lg-4 g-3">
            @foreach ($related as $item)
                @include('products._card', ['product' => $item])
            @endforeach
        </div>
    </section>
@endif

@push('scripts')
<script>
// Product photos: thumbnails switch the big photo; arrow keys move between thumbnails
document.querySelectorAll('[data-pdp-gallery]').forEach((gallery) => {
    const main = gallery.querySelector('[data-pdp-main]');
    const count = gallery.querySelector('[data-pdp-count]');
    const thumbs = Array.from(gallery.querySelectorAll('[data-pdp-thumb]'));
    const show = (index) => {
        thumbs.forEach((t, i) => { t.classList.toggle('is-active', i === index); t.setAttribute('aria-pressed', i === index ? 'true' : 'false'); });
        main.src = thumbs[index].dataset.pdpThumb;
        if (count) count.textContent = `${index + 1} / ${thumbs.length}`;
    };
    // Photo circles beside the buy box drive the same gallery
    const swatches = Array.from(document.querySelectorAll('[data-pdp-swatch]'));
    const swatchLabel = document.querySelector('[data-pdp-swatch-label]');
    const baseShow = show;
    const showBoth = (index) => {
        baseShow(index);
        swatches.forEach((s, i) => { s.classList.toggle('is-active', i === index); s.setAttribute('aria-pressed', i === index ? 'true' : 'false'); });
        if (swatchLabel && swatches[index]) swatchLabel.textContent = swatches[index].dataset.name;
        if (swatches[index]) document.dispatchEvent(new CustomEvent('sl-pdp-color', { detail: swatches[index].dataset.color || '' }));
    };
    const start = swatches.findIndex((s) => s.classList.contains('is-active'));
    if (start > 0) baseShow(start);
    swatches.forEach((s, i) => s.addEventListener('click', () => showBoth(i)));
    thumbs.forEach((t, i) => {
        t.addEventListener('click', () => showBoth(i));
        t.addEventListener('keydown', (e) => {
            const step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
            if (!step) return;
            e.preventDefault();
            const next = (i + step + thumbs.length) % thumbs.length;
            thumbs[next].focus();
            showBoth(next);
        });
    });
});

// Buy box: the size buttons set the label, the stock hint and the most you can add; -/+ step the quantity
document.querySelectorAll('[data-buy-form]').forEach((form) => {
    const label = form.querySelector('[data-size-label]');
    const note = form.querySelector('[data-stock-note]');
    const qty = form.querySelector('[data-qty-input]');
    const clamp = () => {
        const max = +qty.max || 1;
        qty.value = Math.min(max, Math.max(1, Math.round(+qty.value) || 1));
    };
    const pick = (radio) => {
        const stock = +radio.dataset.stock;
        label.textContent = radio.dataset.size;
        qty.max = Math.min(10, stock);
        note.textContent = stock <= 5 ? `Only ${stock} left in this size.` : '';
        clamp();
    };
    const radios = [...form.querySelectorAll('input[name=product_size_id]')];
    const addBtn = form.querySelector('[data-add-btn]');
    const setBuyable = (ok, why) => {
        if (addBtn) { addBtn.disabled = !ok; addBtn.textContent = ok ? 'Add to Cart' : 'Sold Out'; }
        if (!ok) { label.textContent = why; note.textContent = ''; }
    };
    radios.forEach((r) => r.addEventListener('change', () => pick(r)));
    const checked = form.querySelector('input[name=product_size_id]:checked');
    if (checked) pick(checked);

    // Stock per colour: picking a Color/Type shows that colour's sizes (photos without a name keep the current colour)
    if (form.hasAttribute('data-color-mode')) {
        document.addEventListener('sl-pdp-color', (e) => {
            const color = (e.detail || '').toLowerCase();
            if (!color) return;
            const before = form.querySelector('input[name=product_size_id]:checked');
            let known = false;
            radios.forEach((r) => {
                const match = (r.dataset.color || '').toLowerCase() === color;
                known ||= match;
                r.closest('.sl-size').hidden = !match;
                r.disabled = !match || +r.dataset.stock <= 0;
                if (!match) r.checked = false;
            });
            // Keep the same EU size if this colour has it, otherwise the first size in stock
            const next = radios.find((r) => !r.disabled && before && r.dataset.size === before.dataset.size)
                || radios.find((r) => !r.disabled);
            if (next) { next.checked = true; pick(next); setBuyable(true); }
            else setBuyable(false, known ? 'Sold out' : 'Not available in this colour');
        });
    }
    form.querySelectorAll('[data-qty-step]').forEach((btn) => btn.addEventListener('click', () => {
        qty.value = (+qty.value || 1) + +btn.dataset.qtyStep;
        clamp();
    }));
    qty.addEventListener('change', clamp);
});
</script>
@endpush
@endsection
