<div class="col">
    <div class="card product-card h-100">
        <img src="{{ $product->imageUrl() }}"
             class="card-img-top" alt="{{ $product->name }}"
             data-fallback="{{ asset('img/no-image.png') }}" onerror="this.onerror=null;this.src=this.dataset.fallback">
        <div class="card-body d-flex flex-column">
            <h3 class="h6">{{ $product->name }}</h3>
            <p class="text-muted small mb-1">{{ $product->brand->name }} &middot; {{ $product->category->name }}</p>
            @include('products._card_rating', ['product' => $product])
            <p class="fw-bold mb-1">
                {{ $product->formattedPrice() }}
                @if ($product->livePromotion)
                    <small class="sl-promo-was">{{ number_format((float) $product->price, 0) }} MMK</small>
                    <span class="badge text-bg-danger">-{{ $product->livePromotion->discount_percent }}%</span>
                @endif
            </p>
            @if(isset($showStock) && $showStock)
                @php $stock = $product->totalStock(); @endphp
                <p class="small {{ $stock <= 0 ? 'text-danger' : 'text-success' }}">{{ $stock > 0 ? 'In stock' : 'Out of stock' }}</p>
            @endif
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-dark btn-sm mt-auto">View Product</a>
        </div>
    </div>
</div>
