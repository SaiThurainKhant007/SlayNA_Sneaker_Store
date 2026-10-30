@extends('layouts.admin')

@section('title', 'Promotions')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Manage Promotions</h1>
</div>
<p class="text-muted small">
    Running promotions appear on the home page below the brand logos, and the discounted price is charged in the cart and at checkout.
    A promotion applies as soon as it is switched on; leave the end date empty to run it until you switch it off. The <strong>hover image</strong> swaps in when a shopper hovers over
    or taps the promotion picture; without one, the product photo is used. PNG, JPG or WebP up to 4&nbsp;MB.
</p>

@if ($errors->any())
    <div class="alert alert-danger small">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        @forelse ($promotions as $promo)
            <div class="card dashboard-card p-3 mb-3">
                <form method="POST" action="{{ route('admin.promotions.update', $promo) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="sl-promo-media" tabindex="0" aria-label="Preview: hover or tap to see the second image">
                                <img src="{{ $promo->imageUrl() }}" alt="" class="sl-promo-img">
                                @if ($promo->hoverImageUrl())
                                    <img src="{{ $promo->hoverImageUrl() }}" alt="" class="sl-promo-img sl-promo-img--alt">
                                @endif
                                <span class="sl-promo-badge">-{{ $promo->discount_percent }}%</span>
                            </div>
                            <div class="small mt-2 text-center">
                                @if ($promo->isLive())
                                    <span class="badge text-bg-success">Live</span>
                                @elseif (! $promo->is_active)
                                    <span class="badge text-bg-secondary">Off</span>
                                @else
                                    <span class="badge text-bg-secondary">Ended</span>
                                @endif
                            </div>
                            <div class="small mt-1 text-center">
                                <span class="text-decoration-line-through text-muted">{{ number_format((float) $promo->product->price, 0) }}</span>
                                &rarr; <strong class="text-danger">{{ number_format($promo->discountedPrice(), 0) }} MMK</strong>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            @include('admin._promotion_fields', ['promo' => $promo, 'prefix' => 'p'.$promo->id])
                            <div class="d-flex flex-wrap gap-3 small mb-2">
                                @if ($promo->image_path)
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="rm-img-{{ $promo->id }}">
                                        <label class="form-check-label text-danger" for="rm-img-{{ $promo->id }}">Remove main image</label>
                                    </div>
                                @endif
                                @if ($promo->hover_image_path)
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_hover_image" value="1" class="form-check-input" id="rm-hover-{{ $promo->id }}">
                                        <label class="form-check-label text-danger" for="rm-hover-{{ $promo->id }}">Remove hover image</label>
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-sm btn-dark">Save</button>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.promotions.destroy', $promo) }}" class="sl-admin-delete" onsubmit="return confirm('Delete this promotion?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-muted">No promotions yet. Add one with the form.</p>
        @endforelse
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-card p-3 position-sticky" style="top: 90px;">
            <h2 class="h6">Add Promotion</h2>
            <form method="POST" action="{{ route('admin.promotions.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin._promotion_fields', ['promo' => null, 'prefix' => 'new'])
                <button type="submit" class="btn btn-dark btn-sm w-100">Add Promotion</button>
            </form>
        </div>
    </div>
</div>

@include('admin._promotion_script')
@include('partials.image-cropper')
@endsection
