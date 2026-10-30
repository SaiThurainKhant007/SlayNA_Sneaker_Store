@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Search &amp; Filter</h2>
            <form method="get">
                <div class="mb-2">
                    <label class="form-label small">Keyword</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ $search }}" placeholder="Search by sneaker name">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Brand</label>
                    <select name="brand" class="form-select form-select-sm">
                        <option value="">All Brands</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->id }}" @selected((int) $brandId === $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((int) $categoryId === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="mb-2">
                    <legend class="form-label small mb-1">Price (MMK)</legend>
                    <div class="d-flex align-items-center gap-1">
                        <input type="number" name="min_price" class="form-control form-control-sm" min="0" step="1000" value="{{ $minPrice !== null ? (int) $minPrice : '' }}" placeholder="Min" aria-label="Minimum price in MMK">
                        <span class="text-muted" aria-hidden="true">&ndash;</span>
                        <input type="number" name="max_price" class="form-control form-control-sm" min="0" step="1000" value="{{ $maxPrice !== null ? (int) $maxPrice : '' }}" placeholder="Max" aria-label="Maximum price in MMK">
                    </div>
                </fieldset>
                <div class="mb-3">
                    <label class="form-label small">Size</label>
                    <select name="size" class="form-select form-select-sm">
                        <option value="">Any Size</option>
                        @foreach ($sizes as $s)
                            <option value="{{ $s->id }}" @selected((int) $sizeId === $s->id)>EU {{ $s->label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100">Apply</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear</a>
            </form>
        </div>
    </div>

    <div class="col-md-9">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ request()->fullUrlWithQuery(['brand' => null, 'page' => null]) }}"
               class="btn btn-sm {{ $brandId ? 'btn-outline-dark' : 'btn-dark' }}">All Brands</a>
            @foreach ($brands as $b)
                <a href="{{ request()->fullUrlWithQuery(['brand' => $b->id, 'page' => null]) }}"
                   class="btn btn-sm {{ (int) $brandId === $b->id ? 'btn-dark' : 'btn-outline-dark' }}">
                    {{ $b->name }} <span class="opacity-75">({{ $b->products_count }})</span>
                </a>
            @endforeach
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h5 mb-0">{{ $products->total() }} product(s) found</h1>
            <form method="get" class="d-flex align-items-center gap-2">
                @foreach (request()->except(['sort', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label class="small text-muted" for="sort">Sort by</label>
                <select id="sort" name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="name" @selected($sort === 'name')>Name (A&ndash;Z)</option>
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Price: High to Low</option>
                </select>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @forelse ($products as $product)
                @include('products._card', ['product' => $product, 'showStock' => true])
            @empty
                <p class="text-muted">No products matched your search.</p>
            @endforelse
        </div>

        @if ($products->hasPages())
            <nav class="mt-4" aria-label="Product pages">
                {{ $products->onEachSide(1)->links() }}
            </nav>
        @endif
    </div>
</div>
@endsection
