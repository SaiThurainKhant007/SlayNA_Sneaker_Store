@extends('layouts.admin')

@section('title', 'Brands')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Manage Brands &amp; Logos</h1>
</div>
<p class="text-muted small">
    Logos appear on the public Brands page and, when "Show in home logo strip" is ticked, scroll on the home page.
    Use PNG, JPG or WebP up to 2&nbsp;MB &mdash; a transparent PNG about 400&times;200 px looks best.
</p>

<div class="row g-4">
    <div class="col-lg-8">
        @forelse ($brands as $brand)
            <div class="card dashboard-card p-3 mb-3">
                <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-3 text-center">
                            <div class="sl-admin-logo">
                                @if ($brand->logo_path)
                                    <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }} logo">
                                @else
                                    <span class="text-muted small">No logo</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-9">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <input type="text" name="name" value="{{ $brand->name }}" class="form-control form-control-sm flex-grow-1 w-auto" aria-label="Brand name" required>
                                <span class="badge text-bg-light align-self-center">{{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}</span>
                            </div>
                            <input type="file" name="logo" data-crop="free" data-crop-max="800" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm mb-2" aria-label="{{ $brand->logo_path ? 'Replace' : 'Upload' }} logo">
                            <div class="d-flex flex-wrap gap-3 small mb-2">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="show_in_marquee" value="1" class="form-check-input" id="marquee-{{ $brand->id }}" @checked($brand->show_in_marquee)>
                                    <label class="form-check-label" for="marquee-{{ $brand->id }}">Show in home logo strip</label>
                                </div>
                                @if ($brand->logo_path)
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove-{{ $brand->id }}">
                                        <label class="form-check-label text-danger" for="remove-{{ $brand->id }}">Remove logo</label>
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-sm btn-dark">Save</button>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="sl-admin-delete" onsubmit="return confirm('Delete {{ $brand->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($brand->products_count > 0) title="{{ $brand->products_count > 0 ? 'Brands with products cannot be deleted' : 'Delete brand' }}">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-muted">No brands yet.</p>
        @endforelse
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-card p-3 position-sticky" style="top: 90px;">
            <h2 class="h6">Add Brand</h2>
            <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                    <label class="form-label small" for="new-name">Name</label>
                    <input type="text" id="new-name" name="name" class="form-control form-control-sm" placeholder="Brand name" value="{{ old('name') }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small" for="new-logo">Logo (optional)</label>
                    <input type="file" id="new-logo" name="logo" data-crop="free" data-crop-max="800" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                </div>
                <div class="form-check form-switch small mb-3">
                    <input type="checkbox" name="show_in_marquee" value="1" class="form-check-input" id="new-marquee" checked>
                    <label class="form-check-label" for="new-marquee">Show in home logo strip</label>
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100">Add Brand</button>
            </form>
        </div>
    </div>
</div>
@include('partials.image-cropper')
@endsection
