<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Manage Promotions. Two types:
 *  - Promotion: home-page Hot Deals card (brand, original price, discount). Not linked to a shop
 *    product and never changes catalogue / cart prices.
 *  - Limited Edition: its own name, brand, price, photo and sizes, shown and sold only on the home
 *    page below Hot Deals. Behind the scenes it keeps a hidden product (status 'limited') so cart,
 *    checkout and orders work as usual.
 */
class PromotionController extends Controller
{
    private const IMAGE_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    public function index(): View
    {
        $promotions = Promotion::with(['brand', 'product.brand', 'product.variants'])->latest()->get();
        $brands = Brand::orderBy('name')->get();
        $sizes = Size::orderByRaw('label + 0')->get();

        return view('admin.promotions', compact('promotions', 'brands', 'sizes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type') === Promotion::TYPE_LIMITED ? Promotion::TYPE_LIMITED : Promotion::TYPE_PROMOTION;
        $data = $this->validated($request, $type, null);
        $data['image_path'] = $request->file('image')?->store('promotions', 'public');
        $data['hover_image_path'] = $request->file('hover_image')?->store('promotions', 'public');

        DB::transaction(function () use ($request, $type, $data) {
            if ($type === Promotion::TYPE_LIMITED) {
                $data['product_id'] = $this->saveLimitedProduct($request, null, $data['image_path'])->id;
                $data['brand_id'] = $request->input('brand_id');
                $data['original_price'] = null;
            } else {
                $data['product_id'] = null;
            }
            Promotion::create($data);
        });

        return redirect()->route('admin.promotions.index')
            ->with('success', $type === Promotion::TYPE_LIMITED ? 'Limited Edition added.' : 'Promotion added.');
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        // The type is fixed once added (a Limited Edition owns its hidden product)
        $data = $this->validated($request, $promotion->type, $promotion);

        foreach (['image' => 'image_path', 'hover_image' => 'hover_image_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $this->deleteFile($promotion->$column);
                $data[$column] = $request->file($input)->store('promotions', 'public');
            } elseif ($request->boolean('remove_'.$input) && $input === 'hover_image') {
                $this->deleteFile($promotion->$column);
                $data[$column] = null;
            }
        }

        DB::transaction(function () use ($request, $promotion, $data) {
            if ($promotion->isLimited()) {
                $this->saveLimitedProduct($request, $promotion->product, $data['image_path'] ?? $promotion->image_path);
                $data['product_id'] = $promotion->product_id;
                $data['brand_id'] = $request->input('brand_id');
                $data['original_price'] = null;
            } else {
                $data['product_id'] = null;
            }
            $promotion->update($data);
        });

        return redirect()->route('admin.promotions.index')->with('success', $promotion->isLimited() ? 'Limited Edition updated.' : 'Promotion updated.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $product = $promotion->isLimited() ? $promotion->product : null;
        $keepFiles = false;

        DB::transaction(function () use ($promotion, $product, &$keepFiles) {
            $promotion->delete();
            if ($product) {
                if ($product->variants()->whereHas('orderItems')->exists()) {
                    // Past orders still show it: keep the hidden product (and its photo), just stop selling it
                    $product->update(['status' => 'discontinued']);
                    $keepFiles = true;
                } else {
                    $product->delete();
                }
            }
        });

        if (! $keepFiles) {
            $this->deleteFile($promotion->image_path);
        }
        $this->deleteFile($promotion->hover_image_path);

        return redirect()->route('admin.promotions.index')->with('success', $product ? 'Limited Edition removed.' : 'Promotion removed.');
    }

    private function validated(Request $request, string $type, ?Promotion $promotion): array
    {
        if ($type === Promotion::TYPE_LIMITED) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
                'brand_id' => ['required', 'exists:brands,id'],
                'price' => ['required', 'numeric', 'min:1'],
                'image' => $promotion?->image_path ? self::IMAGE_RULES : ['required', ...array_slice(self::IMAGE_RULES, 1)],
                'hover_image' => self::IMAGE_RULES,
                'stock' => ['nullable', 'array'],
                'stock.*' => ['nullable', 'integer', 'min:0'],
            ], [
                'image.required' => 'Please choose a photo for the Limited Edition.',
            ]);

            return [
                'type' => Promotion::TYPE_LIMITED,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'discount_percent' => 0,
                'ends_on' => null,
                'is_active' => $request->boolean('is_active'),
            ];
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['required', 'exists:brands,id'],
            'original_price' => ['required', 'numeric', 'min:1'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'ends_on' => ['nullable', 'date'],
            'image' => $promotion?->image_path ? self::IMAGE_RULES : ['required', ...array_slice(self::IMAGE_RULES, 1)],
            'hover_image' => self::IMAGE_RULES,
        ], [
            'image.required' => 'Please choose a main image for the promotion.',
        ]);

        unset($validated['image'], $validated['hover_image']);
        $validated['type'] = Promotion::TYPE_PROMOTION;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /** The hidden product behind a Limited Edition: name, brand, price, photo and stock per size. */
    private function saveLimitedProduct(Request $request, ?Product $product, ?string $imagePath): Product
    {
        $data = [
            'name' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'brand_id' => $request->input('brand_id'),
            'image_path' => $imagePath,
            'status' => 'limited',
        ];

        if ($product) {
            $product->update($data);
        } else {
            $product = Product::create($data + [
                'category_id' => Category::firstOrCreate(['name' => 'Lifestyle'])->id,
                'low_stock_threshold' => 2,
            ]);
        }

        foreach (Size::all() as $size) {
            $product->variants()->updateOrCreate(
                ['size_id' => $size->id, 'color' => null],
                ['stock_quantity' => max(0, (int) $request->input("stock.{$size->id}", 0))]
            );
        }

        return $product;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
