@extends('layouts.admin')

@section('title', 'Model Gallery')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Model Gallery</h1>
</div>
<div class="card dashboard-card p-3 mb-4">
    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
        @csrf
        <div class="col-md">
            <label class="form-label small" for="gallery-upload">Add photos</label>
            <input type="file" id="gallery-upload" name="images[]" data-crop="4/5" data-crop-limit="12" accept="image/png,image/jpeg,image/webp" class="form-control" multiple required>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-upload" aria-hidden="true"></i> Upload</button>
        </div>
    </form>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
    @forelse ($images as $image)
        <div class="col">
            <div class="card dashboard-card h-100 overflow-hidden @unless ($image->is_active) opacity-75 @endunless">
                <div class="sl-admin-gallery-thumb">
                    <img src="{{ $image->imageUrl() }}" alt="{{ $image->altText() }}" loading="lazy">
                    <span class="badge {{ $image->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $image->is_active ? 'Shown' : 'Hidden' }}</span>
                    <span class="badge text-bg-dark sl-admin-gallery-order">#{{ $image->sort_order }}</span>
                </div>
                <div class="p-3">
                    <form method="POST" action="{{ route('admin.gallery.update', $image) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-2">
                            <label class="form-label small" for="title-{{ $image->id }}">Title</label>
                            <input type="text" id="title-{{ $image->id }}" name="title" maxlength="80" class="form-control form-control-sm" value="{{ $image->title }}" placeholder="Photo title">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small" for="subtitle-{{ $image->id }}">Subtitle</label>
                            <input type="text" id="subtitle-{{ $image->id }}" name="subtitle" maxlength="120" class="form-control form-control-sm" value="{{ $image->subtitle }}" placeholder="Brand and model">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small" for="product-{{ $image->id }}">"Shop this look" product (optional)</label>
                            <select id="product-{{ $image->id }}" name="product_id" class="form-select form-select-sm">
                                <option value="">None</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected($image->product_id === $product->id)>{{ $product->name }} ({{ $product->brand->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-5">
                                <label class="form-label small" for="order-{{ $image->id }}">Order</label>
                                <input type="number" id="order-{{ $image->id }}" name="sort_order" min="0" max="9999" class="form-control form-control-sm" value="{{ $image->sort_order }}" required>
                            </div>
                            <div class="col-7 d-flex align-items-end">
                                <div class="form-check form-switch mb-1">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active-{{ $image->id }}" @checked($image->is_active)>
                                    <label class="form-check-label small" for="active-{{ $image->id }}">Show on home page</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small" for="replace-{{ $image->id }}">Replace photo</label>
                            <input type="file" id="replace-{{ $image->id }}" name="image" data-crop="4/5" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                        </div>
                        <button type="submit" class="btn btn-sm btn-dark">Save</button>
                    </form>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" class="sl-admin-delete" onsubmit="return confirm('Remove this photo from the gallery?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No photos yet. Upload some above.</p>
    @endforelse
</div>
@include('partials.image-cropper')
@endsection
