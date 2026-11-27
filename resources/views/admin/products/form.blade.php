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
                // Current photos, main first: [form key, url, label, colour/type name]
                $currentPhotos = collect();
                if ($product && $product->image_path && $product->image_path !== 'img/no-image.png') {
                    $currentPhotos->push(['main', $product->imageUrl(), 'Main photo', $product->image_label]);
                }
                foreach ($product?->images ?? [] as $photo) {
                    $currentPhotos->push(['existing:'.$photo->id, $photo->imageUrl(), 'Photo '.($currentPhotos->count() + 1), $photo->label]);
                }
            @endphp
            <div class="card dashboard-card p-3 mb-3" data-product-photos data-max="{{ $maxPhotos }}" data-current="{{ $currentPhotos->count() }}">
                <h2 class="h6 mb-3">Photos</h2>
                @error('photo_labels.*')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                @if ($currentPhotos->isNotEmpty())
                    <div class="sl-photo-grid mb-3">
                        @foreach ($currentPhotos as [$key, $url, $label, $colour])
                            <div class="sl-photo-cell">
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
                            <input type="text" name="photo_labels[{{ $key }}]" value="{{ old('photo_labels.'.$key, $colour) }}" maxlength="60"
                                   class="form-control form-control-sm sl-photo-label" placeholder="Color/Type" aria-label="Color/Type name for {{ $label }}">
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="visually-hidden" for="product-photos">{{ $currentPhotos->isEmpty() ? 'Choose photos' : 'Add more photos' }}</label>
                <input type="file" id="product-photos" name="photos[]" data-crop="4/3" data-crop-main="main_photo" data-crop-label="photo_labels"
                       data-crop-limit="{{ max(0, $maxPhotos - $currentPhotos->count()) }}"
                       accept="image/png,image/jpeg,image/webp" class="form-control" multiple>
            </div>

            @php
                // Stock per colour: one row per Color/Type name typed under the photos ('' = no names: one stock for all)
                $seed = [];
                foreach ($existingStock as $color => $qtys) { $seed[mb_strtolower($color)] = $qtys; }
                foreach (old('stock_color', []) as $row => $color) { $seed[mb_strtolower(trim((string) $color))] = old("stock.$row", []); }
                $stockColors = $currentPhotos->map(fn ($p) => trim((string) old('photo_labels.'.$p[0], $p[3])))
                    ->filter()->unique(fn ($c) => mb_strtolower($c))->values();
                if ($stockColors->isEmpty()) { $stockColors = collect(['']); }
            @endphp
            <div class="card dashboard-card p-3 mb-3" data-stock-box
                 data-seed='@json((object) $seed)' data-sizes='@json($sizes->map(fn ($s) => ['id' => $s->id, 'label' => $s->label])->values())'>
                <h2 class="h6 mb-1">Stock per Colour &amp; Size</h2>
                <p class="small text-muted mb-3">One row per Color/Type name under the photos, so each colour has its own sizes. Photos without a name share one stock.</p>
                <div data-stock-rows>
                    @foreach ($stockColors as $i => $color)
                        <div class="sl-stock-row" data-stock-row data-key="{{ mb_strtolower($color) }}">
                            <p class="sl-stock-name" data-stock-name>{{ $color !== '' ? $color : 'All colours' }}</p>
                            <input type="hidden" name="stock_color[{{ $i }}]" value="{{ $color }}">
                            <div class="sl-stock-sizes">
                                @foreach ($sizes as $size)
                                    <label class="sl-stock-cell">
                                        <span>EU {{ $size->label }}</span>
                                        <input type="number" name="stock[{{ $i }}][{{ $size->id }}]" class="form-control form-control-sm" min="0"
                                               value="{{ $seed[mb_strtolower($color)][$size->id] ?? 0 }}">
                                    </label>
                                @endforeach
                            </div>
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

// Stock grid: one row per Color/Type name typed under the photos, updated as names are typed,
// photos are added or removed. Typed numbers stay with their colour, also through a rename.
document.querySelectorAll('[data-stock-box]').forEach((box) => {
    const form = box.closest('form');
    const holder = box.querySelector('[data-stock-rows]');
    const seed = JSON.parse(box.dataset.seed || '{}');
    const sizes = JSON.parse(box.dataset.sizes || '[]');
    const rows = new Map();                 // lower-case colour -> row element ('' = all colours)
    box.querySelectorAll('[data-stock-row]').forEach((r) => rows.set(r.dataset.key, r));
    let next = rows.size;                   // index for new rows' input names
    const lastKey = new WeakMap();          // label input -> colour it had before this edit

    const labelInputs = () => [...form.querySelectorAll('.sl-photo-label')].filter((inp) => {
        const cell = inp.closest('.sl-photo-cell');
        return !(cell && cell.querySelector('[data-photo-remove]:checked'));
    });
    const keyOf = (inp) => inp.value.trim().toLowerCase();

    function makeRow(key) {
        const i = next++;
        const row = document.createElement('div');
        row.className = 'sl-stock-row';
        row.dataset.stockRow = '';
        row.innerHTML = `<p class="sl-stock-name" data-stock-name></p><input type="hidden" name="stock_color[${i}]"><div class="sl-stock-sizes"></div>`;
        const grid = row.querySelector('.sl-stock-sizes');
        sizes.forEach((s) => {
            const cell = document.createElement('label');
            cell.className = 'sl-stock-cell';
            cell.innerHTML = `<span>EU ${s.label}</span><input type="number" min="0" class="form-control form-control-sm" name="stock[${i}][${s.id}]">`;
            cell.querySelector('input').value = (seed[key] || {})[s.id] ?? 0;
            grid.appendChild(cell);
        });
        return row;
    }

    function refresh() {
        const list = [];
        const seen = new Set();
        labelInputs().forEach((inp) => {
            const name = inp.value.trim(), key = name.toLowerCase();
            if (name && !seen.has(key)) { seen.add(key); list.push([key, name]); }
        });
        if (!list.length) list.push(['', '']);
        holder.replaceChildren(...list.map(([key, name]) => {
            if (!rows.has(key)) rows.set(key, makeRow(key));
            const row = rows.get(key);
            row.dataset.key = key;
            row.querySelector('[data-stock-name]').textContent = name || 'All colours';
            row.querySelector('input[type=hidden]').value = name;
            return row;
        }));
    }

    form.addEventListener('focusin', (e) => { if (e.target.matches('.sl-photo-label')) lastKey.set(e.target, keyOf(e.target)); });
    form.addEventListener('input', (e) => {
        if (!e.target.matches('.sl-photo-label')) return;
        const before = lastKey.get(e.target), now = keyOf(e.target);
        // Renaming a colour carries its numbers along (unless another photo still uses the old name)
        if (before !== undefined && before !== now && rows.has(before) && !rows.has(now)
            && !labelInputs().some((inp) => inp !== e.target && keyOf(inp) === before)) {
            rows.set(now, rows.get(before));
            rows.delete(before);
        }
        lastKey.set(e.target, now);
        refresh();
    });
    form.addEventListener('change', (e) => { if (e.target.matches('[data-photo-remove]')) refresh(); });
    // New photos' name boxes appear and disappear as files are chosen or removed
    const photos = form.querySelector('[data-product-photos]');
    if (photos) new MutationObserver(refresh).observe(photos, { childList: true, subtree: true });
});
</script>
@endpush
@endsection
