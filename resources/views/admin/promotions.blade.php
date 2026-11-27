@extends('layouts.admin')

@section('title', 'Promotions')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Manage Promotions</h1>
</div>

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
                                @if ($promo->isLimited())
                                    <span class="sl-promo-badge sl-limited-badge">Limited Edition</span>
                                @else
                                    <span class="sl-promo-badge">-{{ $promo->discount_percent }}%</span>
                                @endif
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
                                @if ($promo->isLimited())
                                    <strong>{{ number_format($promo->listPrice(), 0) }} MMK</strong> <span class="text-muted">(full price)</span>
                                    <br><span class="text-muted">{{ $promo->product->totalStock() }} pairs in stock</span>
                                @else
                                    <span class="text-decoration-line-through text-muted">{{ number_format($promo->listPrice(), 0) }}</span>
                                    &rarr; <strong class="text-danger">{{ number_format($promo->discountedPrice(), 0) }} MMK</strong>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-8">
                            @include('admin._promotion_fields', ['promo' => $promo, 'prefix' => 'p'.$promo->id])
                            <div class="d-flex flex-wrap gap-3 small mb-2">
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
                <button type="submit" class="btn btn-dark btn-sm w-100">Add</button>
            </form>
        </div>
    </div>
</div>

@include('admin._promotion_script')
@include('partials.image-cropper')
@endsection
