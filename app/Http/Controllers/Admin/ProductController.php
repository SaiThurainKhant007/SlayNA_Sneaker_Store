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
        $products = Product::with(['brand', 'category', 'variants'])
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
        return $this->form($product);
    }

    private function form(?Product $product = null): View
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $sizes = Size::orderByRaw('label + 0')->get();

        $existingStock = [];
        if ($product) {
            $existingStock = $product->variants()->pluck('stock_quantity', 'size_id')->toArray();
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
            'stock' => ['nullable', 'array'],
            'stock.*' => ['nullable', 'integer', 'min:0'],
        ]);

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
        $extraPaths = $kept->except([$mainKey])->values()->all();
        $filesToDelete = $current->only($removed->all())->values()->all();

        DB::transaction(function () use (&$product, $validated, $request, $imagePath, $extraPaths) {
            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'brand_id' => $validated['brand_id'],
                'category_id' => $validated['category_id'],
                'image_path' => $imagePath,
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
            foreach ($extraPaths as $i => $path) {
                $product->images()->create(['image_path' => $path, 'sort_order' => $i + 1]);
            }

            // Upsert per-size stock (UC_03 / UC_07)
            foreach (Size::all() as $size) {
                $qty = max(0, (int) ($request->input("stock.{$size->id}", 0)));
                $product->variants()->updateOrCreate(
                    ['size_id' => $size->id],
                    ['stock_quantity' => $qty]
                );
            }
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
