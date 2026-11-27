<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** UC_03 Product Management. */
class ProductController extends Controller
{
    private const PHOTO_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    /** Photos per product, main photo included. */
    public const MAX_PHOTOS = 9;

    private const NO_IMAGE = 'img/no-image.png';

    public function index(): View
    {
        // Limited Editions are managed in Manage Promotions, not here
        $products = Product::with(['brand', 'category', 'variants'])
            ->where('status', '!=', 'limited')
            ->latest('id')
            ->get();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return $this->form();
    }

    public function edit(Product $product): View
    {
        abort_if($product->status === 'limited', 404);   // edited in Manage Promotions

        return $this->form($product);
    }

    private function form(?Product $product = null): View
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $sizes = Size::orderByRaw('label + 0')->get();

        $existingStock = [];
        if ($product) {
            // [colour => [size_id => stock]]; '' = one stock for the whole product (no colour names)
            foreach ($product->variants()->get() as $variant) {
                $existingStock[$variant->color ?? ''][$variant->size_id] = $variant->stock_quantity;
            }
            $product->load('images');
        }
        $maxPhotos = self::MAX_PHOTOS;

        return view('admin.products.form', compact('product', 'brands', 'categories', 'sizes', 'existingStock', 'maxPhotos'));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, null);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->status === 'limited', 404);   // edited in Manage Promotions

        return $this->save($request, $product);
    }

    private function save(Request $request, ?Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,discontinued'],
            'photos' => ['nullable', 'array', 'max:'.self::MAX_PHOTOS],
            'photos.*' => self::PHOTO_RULES,
            'remove_main' => ['nullable', 'boolean'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
            'main_photo' => ['nullable', 'string', 'max:30'],   // "main", "existing:{id}" or "new:{index}"
            'photo_labels' => ['nullable', 'array'],            // colour/type name per photo, keyed like main_photo
            'photo_labels.*' => ['nullable', 'string', 'max:60'],
            'stock' => ['nullable', 'array'],
            // Stock grid: stock[row][size_id] with the row's colour in stock_color[row]; without
            // stock_color the old one-row form stock[size_id] is accepted (one stock for the product)
            ...($request->has('stock_color') ? [
                'stock_color' => ['array'],
                'stock_color.*' => ['nullable', 'string', 'max:60'],
                'stock.*' => ['nullable', 'array'],
                'stock.*.*' => ['nullable', 'integer', 'min:0'],
            ] : [
                'stock.*' => ['nullable', 'integer', 'min:0'],
            ]),
        ]);

        // One stock row per colour (blank colour = the whole product), first spelling of a colour wins
        $stockRows = [];
        if ($request->has('stock_color')) {
            foreach ($request->input('stock_color', []) as $row => $color) {
                $color = trim((string) $color) !== '' ? trim((string) $color) : null;
                $stockRows[mb_strtolower($color ?? '')] ??= [$color, (array) $request->input("stock.{$row}", [])];
            }
        }
        if (! $stockRows) {
            $stockRows[''] = [null, (array) $request->input('stock', [])];
        }

        // Current photos, keyed the way the form refers to them (only this product's photos)
        $current = collect();
        if ($product && $product->image_path && $product->image_path !== self::NO_IMAGE) {
            $current['main'] = $product->image_path;
        }
        foreach ($product ? $product->images()->get() : [] as $photo) {
            $current['existing:'.$photo->id] = $photo->image_path;
        }

        $removed = collect($validated['remove_photos'] ?? [])->map(fn ($id) => 'existing:'.$id);
        if ($request->boolean('remove_main')) {
            $removed->push('main');
        }
        $kept = $current->except($removed->all());

        $newFiles = $request->file('photos', []);
        if ($kept->count() + count($newFiles) > self::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photos' => 'A product can have up to '.self::MAX_PHOTOS.' photos; it would have '.($kept->count() + count($newFiles)).'. Remove some first.',
            ]);
        }

        // Final photo list in order: kept photos first, then the new ones
        foreach (array_values($newFiles) as $i => $file) {
            $kept['new:'.$i] = $file->store('products', 'public');
        }

        // Main photo: the one picked, otherwise the first photo
        $mainKey = $validated['main_photo'] ?? null;
        $mainKey = $mainKey !== null && $kept->has($mainKey) ? $mainKey : $kept->keys()->first();
        $imagePath = $mainKey ? $kept[$mainKey] : self::NO_IMAGE;
        $filesToDelete = $current->only($removed->all())->values()->all();

        // Colour/type name typed under each photo (blank = no name)
        $labels = $validated['photo_labels'] ?? [];
        $labelFor = fn ($key) => ($name = trim((string) ($labels[$key] ?? ''))) !== '' ? $name : null;
        $imageLabel = $mainKey ? $labelFor($mainKey) : null;
        $extraPhotos = $kept->except([$mainKey])
            ->map(fn ($path, $key) => ['image_path' => $path, 'label' => $labelFor($key)])
            ->values()->all();

        DB::transaction(function () use (&$product, $validated, $imagePath, $imageLabel, $extraPhotos, $stockRows) {
            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'brand_id' => $validated['brand_id'],
                'category_id' => $validated['category_id'],
                'image_path' => $imagePath,
                'image_label' => $imageLabel,
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
                'status' => $validated['status'],
            ];

            if ($product) {
                $product->update($data);
            } else {
                $product = Product::create($data);
            }

            // Extra photos = every photo except the main one, in order
            $product->images()->delete();
            foreach ($extraPhotos as $i => $photo) {
                $product->images()->create($photo + ['sort_order' => $i + 1]);
            }

            // Upsert stock per colour and size (UC_03 / UC_07)
            $kept = [];
            $sizes = Size::all();
            foreach ($stockRows as [$color, $quantities]) {
                foreach ($sizes as $size) {
                    $kept[] = $product->variants()->updateOrCreate(
                        ['size_id' => $size->id, 'color' => $color],
                        ['stock_quantity' => max(0, (int) ($quantities[$size->id] ?? 0))]
                    )->id;
                }
            }

            // Colours no longer listed: delete their stock rows, except ones past orders point to (kept at 0)
            $gone = $product->variants()->whereNotIn('id', $kept);
            (clone $gone)->whereDoesntHave('orderItems')->delete();
            $gone->update(['stock_quantity' => 0]);
        });

        // Files go only after the database changes are saved
        foreach ($filesToDelete as $path) {
            if (str_starts_with($path, 'products/')) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product saved successfully.');
    }

    /** Soft "delete" — discontinue, matching the prototype's UC_03 behaviour. */
    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['status' => 'discontinued']);

        return redirect()->route('admin.products.index')->with('success', 'Product discontinued.');
    }
}
