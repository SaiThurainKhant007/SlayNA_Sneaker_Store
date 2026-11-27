{{-- Shared fields for adding and editing a promotion. $promo is null when adding; $prefix keeps ids unique.
     Promotion = home-page Hot Deals card (brand + original price + discount; not linked to a shop product).
     Limited Edition = its own name, brand, price, photo and sizes, sold on the home page only. --}}
@php
    $type = $promo?->type ?? old('type', \App\Models\Promotion::TYPE_PROMOTION);
    $isLimited = $type === \App\Models\Promotion::TYPE_LIMITED;
    $limitedProduct = $promo?->isLimited() ? $promo->product : null;
    $stockBySize = $limitedProduct ? $limitedProduct->variants->whereNull('color')->pluck('stock_quantity', 'size_id') : collect();
    $promoBrandId = $promo?->brand_id ?? old('brand_id');
    $promoPrice = $promo?->original_price ?? old('original_price');
@endphp
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-type">Type</label>
    @if ($promo)
        {{-- Fixed once added --}}
        <input type="hidden" name="type" value="{{ $type }}" data-promo-type>
        <p class="mb-0"><span class="badge {{ $isLimited ? 'sl-limited-badge' : 'text-bg-danger' }}">{{ \App\Models\Promotion::TYPES[$type] }}</span></p>
    @else
        <select id="{{ $prefix }}-type" name="type" class="form-select form-select-sm" data-promo-type>
            @foreach (\App\Models\Promotion::TYPES as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @endif
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-title"><span data-for="promotion" @if ($isLimited) hidden @endif>Title</span><span data-for="limited" @unless ($isLimited) hidden @endunless>Sneaker name</span></label>
    <input type="text" id="{{ $prefix }}-title" name="title" class="form-control form-control-sm" maxlength="120"
           value="{{ $promo?->title ?? old('title') }}" placeholder="{{ $isLimited ? 'e.g. Air Jordan 4 "Holiday" 1 of 100' : 'Promotion name' }}" required>
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-desc">Short description (optional)</label>
    <input type="text" id="{{ $prefix }}-desc" name="description" class="form-control form-control-sm" maxlength="255"
           value="{{ $promo?->description ?? old('description') }}" placeholder="Short note shown on the card">
</div>

{{-- Promotion only: brand + original price + discount + end date (home Hot Deals; not sold from the shop) --}}
<fieldset data-for="promotion" @if ($isLimited) hidden disabled @endif>
    <div class="mb-2">
        <label class="form-label small" for="{{ $prefix }}-promo-brand">Brand</label>
        <select id="{{ $prefix }}-promo-brand" name="brand_id" class="form-select form-select-sm" required>
            <option value="">Choose&hellip;</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected((string) $promoBrandId === (string) $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-7">
            <label class="form-label small" for="{{ $prefix }}-original">Original price (MMK)</label>
            <input type="number" id="{{ $prefix }}-original" name="original_price" class="form-control form-control-sm" min="1" step="1"
                   value="{{ $promoPrice !== null ? (int) $promoPrice : '' }}" data-promo-price required>
        </div>
        <div class="col-5">
            <label class="form-label small" for="{{ $prefix }}-pct">Discount %</label>
            <input type="number" id="{{ $prefix }}-pct" name="discount_percent" class="form-control form-control-sm" min="1" max="90"
                   value="{{ $promo?->discount_percent ?: old('discount_percent', 20) }}" data-promo-percent required>
        </div>
    </div>
    <p class="small mb-2" data-promo-preview aria-live="polite"></p>
    <div class="mb-2">
        <label class="form-label small" for="{{ $prefix }}-end">Ends on (optional)</label>
        <input type="date" id="{{ $prefix }}-end" name="ends_on" class="form-control form-control-sm"
               value="{{ $promo?->ends_on?->toDateString() ?? old('ends_on') }}">
    </div>
</fieldset>

{{-- Limited Edition only: its own brand, full price and stock per size --}}
<fieldset data-for="limited" @unless ($isLimited) hidden disabled @endunless>
    <div class="row g-2 mb-2">
        <div class="col-7">
            <label class="form-label small" for="{{ $prefix }}-brand">Brand</label>
            <select id="{{ $prefix }}-brand" name="brand_id" class="form-select form-select-sm" required>
                <option value="">Choose&hellip;</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(($limitedProduct?->brand_id ?? old('brand_id')) == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-5">
            <label class="form-label small" for="{{ $prefix }}-price">Price (MMK)</label>
            <input type="number" id="{{ $prefix }}-price" name="price" class="form-control form-control-sm" min="1" step="1"
                   value="{{ $limitedProduct ? (int) $limitedProduct->price : old('price') }}" required>
        </div>
    </div>
    <p class="small text-muted mb-2"><i class="bi bi-gem" aria-hidden="true"></i> Sold at full price on the home page. No discount, no end date.</p>
    <label class="form-label small mb-1">Stock per size</label>
    <div class="sl-stock-sizes mb-2">
        @foreach ($sizes as $size)
            <label class="sl-stock-cell">
                <span>EU {{ $size->label }}</span>
                <input type="number" name="stock[{{ $size->id }}]" class="form-control form-control-sm" min="0"
                       value="{{ old('stock.'.$size->id, $stockBySize[$size->id] ?? 0) }}">
            </label>
        @endforeach
    </div>
</fieldset>

<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-img">
        @if ($promo?->image_path) Replace main image
        @else <span data-for="promotion" @if ($isLimited) hidden @endif>Main image (required)</span><span data-for="limited" @unless ($isLimited) hidden @endunless>Photo (required)</span>
        @endif
    </label>
    <input type="file" id="{{ $prefix }}-img" name="image" data-crop="4/3" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" @unless ($promo?->image_path) required @endunless>
</div>
<div class="mb-2">
    <label class="form-label small" for="{{ $prefix }}-hover">{{ $promo?->hover_image_path ? 'Replace hover image' : 'Hover / tap image (optional)' }}</label>
    <input type="file" id="{{ $prefix }}-hover" name="hover_image" data-crop="4/3" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
</div>
<div class="form-check form-switch small mb-3">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="{{ $prefix }}-active" @checked($promo?->is_active ?? true)>
    <label class="form-check-label" for="{{ $prefix }}-active">Active</label>
</div>
