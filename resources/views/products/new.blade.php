@extends('layouts.app')

@section('title', 'New Arrivals')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="sl-section-title mb-0">New Arrivals</h1>
    <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm">Browse the full shop &rarr;</a>
</div>
<p class="text-muted">The latest sneakers just added to SlayNa.</p>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
    @forelse ($products as $product)
        @include('products._card', ['product' => $product, 'showStock' => true])
    @empty
        <p class="text-muted">No new arrivals yet.</p>
    @endforelse
</div>
@endsection
