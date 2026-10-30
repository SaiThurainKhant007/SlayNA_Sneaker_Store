@extends('layouts.admin')

@section('title', $product ? 'Edit Product' : 'Add Product')

@section('content')
<h1 class="h4 mb-3">{{ $product ? 'Edit' : 'Add' }} Product</h1>

<form method="POST" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($product) @method('PUT') @endif
    <div class="row">
        <div class="col-md-8">
            <div class="mb-2">
                <label class="form-label small">Product Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
            </div>
            <div class="mb-2">
                <label class="form-label small">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
            <div class="row">
                <div class="col-4 mb-2">
                    <label class="form-label small">Price (MMK)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" required>
                </div>
                <div class="col-4 mb-2">
                    <label class="form-label small">Brand</label>
                    <select name="brand_id" class="form-select" required>
                        <option value="">Select</option>
                        @foreach ($brands as $b)
                            <option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id ?? null) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 mb-2">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id ?? null) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6 mb-2">
                    <label class="form-label small">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}">
                </div>
                <div class="col-6 mb-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
                        <option value="discontinued" @selected(old('status', $product->status ?? '') === 'discontinued')>Discontinued</option>
                    </select>
                </div>
            </div>
            @php
                // Current photos, main first: [form key, url, label]
                $currentPhotos = collect();
                if ($product && $product->image_path && $product->image_path !== 'img/no-image.png') {
                    $currentPhotos->push(['main', $product->imageUrl(), 'Main photo']);
                }
                foreach ($product?->images ?? [] as $photo) {
                    $currentPhotos->push(['existing:'.$photo->id, $photo->imageUrl(), 'Photo '.($currentPhotos->count() + 1)]);
                }
            @endphp
            <div class="card dashboard-card p-3 mb-3" data-product-photos data-max="{{ $maxPhotos }}" data-current="{{ $currentPhotos->count() }}">
                <h2 class="h6 mb-1">Photos</h2>
                <p class="small text-muted mb-3">Up to {{ $maxPhotos }} photos. The <strong>Main</strong> photo shows on product cards; customers see all of them on the product page. If you don't pick one, the first photo is the main photo.</p>

                @if ($currentPhotos->isNotEmpty())
                    <div class="sl-photo-grid mb-3">
                        @foreach ($currentPhotos as [$key, $url, $label])
                            <div class="sl-photo">
                                <img src="{{ $url }}" alt="{{ $label }}" loading="lazy" onerror="this.src='{{ asset('img/no-image.png') }}'">
                                <label class="sl-photo-remove" title="Remove this photo">
                                    @if ($key === 'main')
                                        <input type="checkbox" name="remove_main" value="1" class="visually-hidden" data-photo-remove>
                                    @else
                                        <input type="checkbox" name="remove_photos[]" value="{{ Str::after($key, 'existing:') }}" class="visually-hidden" data-photo-remove>
                                    @endif
                                    <i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Remove {{ $label }}</span>
                                </label>
                                <label class="sl-photo-main-pick">
                                    <input type="radio" name="main_photo" value="{{ $key }}" class="form-check-input mt-0" @checked($key === 'main')> Main
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="form-label small" for="product-photos">{{ $currentPhotos->isEmpty() ? 'Choose photos' : 'Add more photos' }} <span class="text-muted">(one or more; choose again to add more)</span></label>
                <input type="file" id="product-photos" name="photos[]" data-crop="4/3" data-crop-main="main_photo"
                       data-crop-limit="{{ max(0, $maxPhotos - $currentPhotos->count()) }}"
                       accept="image/png,image/jpeg,image/webp" class="form-control" multiple>
            </div>

            <div class="card dashboard-card p-3 mb-3">
                <h2 class="h6">Stock per Size</h2>
                <div class="row row-cols-4 g-2">
                    @foreach ($sizes as $size)
                        <div class="col">
                            <label class="form-label small">EU {{ $size->label }}</label>
                            <input type="number" name="stock[{{ $size->id }}]" class="form-control form-control-sm" min="0" value="{{ old('stock.'.$size->id, $existingStock[$size->id] ?? 0) }}">
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-dark">Save Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
@include('partials.image-cropper')
@push('scripts')
<script>
// Ticking × on a current photo frees a slot for a new one, and it can't stay the Main photo
document.querySelectorAll('[data-product-photos]').forEach((box) => {
    const input = box.querySelector('input[type=file][data-crop]');
    const max = parseInt(box.dataset.max, 10), current = parseInt(box.dataset.current, 10);
    box.querySelectorAll('[data-photo-remove]').forEach((cb) => cb.addEventListener('change', () => {
        const removed = box.querySelectorAll('[data-photo-remove]:checked').length;
        input.dataset.cropLimit = String(max - (current - removed));
        const radio = cb.closest('.sl-photo').querySelector('input[type=radio]');
        if (cb.checked && radio.checked) radio.checked = false;
        input.dispatchEvent(new CustomEvent('sl-crop-refresh'));
    }));
});
</script>
@endpush
@endsection
