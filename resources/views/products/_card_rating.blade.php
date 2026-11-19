{{-- Product card stars: customers click a star to rate (posts straight to the review route), guests are sent to log in,
     and a product the customer already rated shows their rating. Staff/admin just see the average. --}}
@php
    $viewer = auth()->user();
    $rounded = (int) round($product->averageRating());
    $count = $product->reviewCount();
    // The customer's own ratings, looked up once per page rather than once per card
    $myRatings = $viewer?->isCustomer()
        ? (request()->attributes->get('sl_my_ratings') ?? tap($viewer->reviews()->pluck('rating', 'product_id')->all(), fn ($r) => request()->attributes->set('sl_my_ratings', $r)))
        : [];
    $myRating = $myRatings[$product->id] ?? null;
@endphp
<div class="small mb-1 d-flex align-items-center flex-wrap gap-1">
    @if (($viewer && ! $viewer->isCustomer()) || $myRating)
        <span class="rating-stars" role="img" aria-label="Rated {{ number_format($product->averageRating(), 1) }} out of 5">{{ str_repeat('★', $rounded) . str_repeat('☆', 5 - $rounded) }}</span>
    @elseif ($viewer)
        <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="sl-rate-stars" aria-label="Rate {{ $product->name }}">
            @csrf
            @for ($i = 5; $i >= 1; $i--)
                <button type="submit" name="rating" value="{{ $i }}" class="{{ $i <= $rounded ? 'is-on' : '' }}" title="Rate {{ $i }} out of 5" aria-label="Rate {{ $product->name }} {{ $i }} out of 5">★</button>
            @endfor
        </form>
    @else
        <span class="sl-rate-stars" role="group" aria-label="Log in to rate {{ $product->name }}">
            @for ($i = 5; $i >= 1; $i--)
                <a href="{{ route('login') }}" class="{{ $i <= $rounded ? 'is-on' : '' }}" title="Log in to rate">★</a>
            @endfor
        </span>
    @endif
    <span class="text-muted">({{ $count }} review{{ $count === 1 ? '' : 's' }})</span>
    @if ($myRating)
        <span class="sl-my-rating">· You rated {{ $myRating }}★</span>
    @endif
</div>
