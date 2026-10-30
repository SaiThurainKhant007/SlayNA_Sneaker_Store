{{-- Shared fields for adding and editing a promotion. $promo is null when adding; $prefix keeps ids unique. --}}
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-title">Title</label>
    <input type="text" id="{{ $prefix }}-title" name="title" class="form-control form-control-sm" maxlength="120"
           value="{{ $promo?->title ?? old('title') }}" placeholder="Promotion name" required>
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-desc">Short description (optional)</label>
    <input type="text" id="{{ $prefix }}-desc" name="description" class="form-control form-control-sm" maxlength="255"
           value="{{ $promo?->description ?? old('description') }}" placeholder="Short note shown on the deal card">
</div>
<div class="row g-2 mb-2">
    <div class="col-8">
        <label class="form-label small" for="{{ $prefix }}-product">Product</label>
        <select id="{{ $prefix }}-product" name="product_id" class="form-select form-select-sm" data-promo-product required>
            <option value="">Choose&hellip;</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-price="{{ (float) $product->price }}"
                        @selected(($promo?->product_id ?? old('product_id')) == $product->id)>
                    {{ $product->name }} ({{ $product->brand->name }}) &mdash; {{ number_format((float) $product->price, 0) }} MMK
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label class="form-label small" for="{{ $prefix }}-pct">Discount %</label>
        <input type="number" id="{{ $prefix }}-pct" name="discount_percent" class="form-control form-control-sm" min="1" max="90"
               value="{{ $promo?->discount_percent ?? old('discount_percent', 20) }}" data-promo-percent required>
    </div>
</div>
<p class="small mb-2" data-promo-preview aria-live="polite"></p>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-end">Ends on (optional, the discount applies as soon as the promotion is active)</label>
    <input type="date" id="{{ $prefix }}-end" name="ends_on" class="form-control form-control-sm"
           value="{{ $promo?->ends_on?->toDateString() ?? old('ends_on') }}">
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-img">{{ $promo?->image_path ? 'Replace main image' : 'Main image (optional, defaults to product photo)' }}</label>
    <input type="file" id="{{ $prefix }}-img" name="image" data-crop="4/3" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-hover">{{ $promo?->hover_image_path ? 'Replace hover image' : 'Hover / tap image (optional)' }}</label>
    <input type="file" id="{{ $prefix }}-hover" name="hover_image" data-crop="4/3" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
</div>
<div class="form-check form-switch small mb-3">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="{{ $prefix }}-active" @checked($promo?->is_active ?? true)>
    <label class="form-check-label" for="{{ $prefix }}-active">Active</label>
</div>
