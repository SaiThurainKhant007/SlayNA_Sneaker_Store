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
            <img src="{{ $photos[0] }}" class="sl-pdp-main" alt="{{ $product->name }}" data-pdp-main onerror="this.src='{{ asset('img/no-image.png') }}'">
            @if (count($photos) > 1)
                <span class="sl-pdp-count" data-pdp-count aria-live="polite">1 / {{ count($photos) }}</span>
                <div class="sl-pdp-thumbs" role="group" aria-label="{{ $product->name }} photos">
                    @foreach ($photos as $url)
                        <button type="button" class="sl-pdp-thumb {{ $loop->first ? 'is-active' : '' }}" data-pdp-thumb="{{ $url }}" aria-label="Show photo {{ $loop->iteration }} of {{ count($photos) }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
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
        <p>
            @include('products._stars', ['rating' => $product->averageRating(), 'count' => $product->reviewCount()])
        </p>
        <h2 class="h4">
            {{ $product->formattedPrice() }}
            @if ($product->livePromotion)
                <small class="sl-promo-was">{{ number_format((float) $product->price, 0) }} MMK</small>
                <span class="badge text-bg-danger align-middle">-{{ $product->livePromotion->discount_percent }}% &middot; {{ $product->livePromotion->title }}</span>
            @endif
        </h2>
        <p>{!! nl2br(e($product->description)) !!}</p>

        @if (!auth()->check() || auth()->user()->isCustomer())
            <form method="POST" action="{{ route('cart.store') }}" class="mt-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small">Size</label>
                        <select name="variant_id" class="form-select" required>
                            @foreach ($product->variants as $variant)
                                <option value="{{ $variant->id }}" @disabled($variant->stock_quantity <= 0)>
                                    EU {{ $variant->size->label }} &mdash; {{ $variant->stock_quantity > 0 ? $variant->stock_quantity.' in stock' : 'Out of stock' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small">Qty</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="10" style="width:90px">
                    </div>
                    <div class="col-auto">
                        @auth
                            <button type="submit" class="btn btn-dark">Add to Cart</button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-dark">Log in to Buy</a>
                        @endauth
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

<hr class="my-4">

<h2 class="h5">Customer Reviews</h2>

@auth
    @if (auth()->user()->isCustomer() && $alreadyReviewed)
        <p class="small text-muted"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> Thanks, you have reviewed this product.</p>
    @elseif (auth()->user()->isCustomer())
        <div class="card dashboard-card p-3 mb-3">
            <h3 class="h6">Write a Review</h3>
            <form method="POST" action="{{ route('products.reviews.store', $product) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Rating</label>
                    <select name="rating" class="form-select form-select-sm" style="width:150px" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Bad</option>
                    </select>
                </div>
                <div class="mb-2">
                    <textarea name="comment" class="form-control form-control-sm" rows="2" placeholder="Share your experience with this product..."></textarea>
                </div>
                <button type="submit" class="btn btn-dark btn-sm">Submit Review</button>
            </form>
        </div>
    @endif
@endauth

@forelse ($product->reviews as $review)
    <div class="border-bottom py-2">
        <p class="rating-stars mb-0 small">
            {{ str_repeat('★', (int) $review->rating) . str_repeat('☆', 5 - (int) $review->rating) }}
            <strong class="text-body">{{ $review->customer->name }}</strong>
            <span class="text-muted">&middot; {{ $review->created_at->format('d M Y') }}</span>
        </p>
        <p class="mb-0">{!! nl2br(e($review->comment)) !!}</p>
    </div>
@empty
    <p class="text-muted">No reviews yet for this product.</p>
@endforelse

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
    thumbs.forEach((t, i) => {
        t.addEventListener('click', () => show(i));
        t.addEventListener('keydown', (e) => {
            const step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
            if (!step) return;
            e.preventDefault();
            const next = (i + step + thumbs.length) % thumbs.length;
            thumbs[next].focus();
            show(next);
        });
    });
});
</script>
@endpush
@endsection
