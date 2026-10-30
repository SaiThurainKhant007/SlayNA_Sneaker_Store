@extends('layouts.app')

@section('title', 'Brands')

@section('hero')
<section class="sl-page-head">
    <div class="container">
        <p class="sl-eyebrow">Only authentic pairs</p>
        <h1 class="sl-page-title">Our <span>Brands</span></h1>
        <p class="sl-page-sub">Shop by the names you trust. Pick a brand to see every pair we have in stock.</p>
    </div>
</section>
@endsection

@section('content')
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4">
    @forelse ($brands as $brand)
        <div class="col">
            <a href="{{ route('products.index', ['brand' => $brand->id]) }}" class="sl-brand-card">
                @if ($brand->logo_path)
                    <span class="sl-brand-logo"><img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }} logo" loading="lazy"></span>
                @else
                    <span class="sl-brand-mark">{{ strtoupper(mb_substr($brand->name, 0, 1)) }}</span>
                @endif
                <span class="sl-brand-name">{{ $brand->name }}</span>
                <span class="sl-brand-count">{{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}</span>
                <span class="sl-brand-cta">Shop {{ $brand->name }} &rarr;</span>
            </a>
        </div>
    @empty
        <p class="text-muted">No brands available yet.</p>
    @endforelse
</div>
@endsection
