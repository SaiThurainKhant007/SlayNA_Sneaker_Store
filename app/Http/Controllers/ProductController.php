<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** UC_04 Product Browsing Management. */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $brandId = $request->query('brand');
        $categoryId = $request->query('category');
        $sizeId = $request->query('size');
        $minPrice = is_numeric($request->query('min_price')) ? (float) $request->query('min_price') : null;
        $maxPrice = is_numeric($request->query('max_price')) ? (float) $request->query('max_price') : null;

        $query = Product::with(['brand', 'category', 'reviews', 'variants', 'livePromotion'])
            ->where('status', 'active');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($sizeId) {
            $query->whereHas('variants', function ($q) use ($sizeId) {
                $q->where('size_id', $sizeId)->where('stock_quantity', '>', 0);
            });
        }

        // Price range on the regular (non-promotion) price
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $sort = $request->query('sort', 'name');

        match ($sort) {
            'newest' => $query->latest()->orderByDesc('id'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();

        // Only brands that actually have active products, with counts for the brand bar
        $brands = Brand::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->whereHas('products', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();
        $sizes = Size::orderByRaw('label + 0')->get();

        return view('products.index', compact('products', 'brands', 'categories', 'sizes', 'search', 'brandId', 'categoryId', 'sizeId', 'minPrice', 'maxPrice', 'sort'));
    }

    /** Newest active products, most recently added first. */
    public function newArrivals(): View
    {
        $products = Product::with(['brand', 'category', 'reviews', 'variants', 'livePromotion'])
            ->where('status', 'active')
            ->latest()
            ->orderByDesc('id')
            ->take(12)
            ->get();

        return view('products.new', compact('products'));
    }

    public function show(Request $request, Product $product): View
    {
        $product->load([
            'brand',
            'category',
            'variants.size',
            'images',
            'reviews' => fn ($q) => $q->latest(),
            'reviews.customer',
        ]);

        // "You may also like": same category or brand, items in stock first
        $related = Product::with(['brand', 'category', 'reviews', 'variants', 'livePromotion'])
            ->where('status', 'active')
            ->whereKeyNot($product->id)
            ->where(fn ($q) => $q->where('category_id', $product->category_id)->orWhere('brand_id', $product->brand_id))
            ->withSum('variants as stock', 'stock_quantity')
            ->orderByRaw('CASE WHEN category_id = ? AND brand_id = ? THEN 0 ELSE 1 END', [$product->category_id, $product->brand_id])
            ->orderByDesc('stock')
            ->take(4)
            ->get();

        $alreadyReviewed = $request->user()
            && $product->reviews->contains('customer_id', $request->user()->id);

        return view('products.show', compact('product', 'related', 'alreadyReviewed'));
    }

    /** UC_02 Review & Rating Management: customer submits a review. */
    public function storeReview(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($product->reviews()->where('customer_id', $user->id)->exists()) {
            return back()->withErrors(['review' => 'You have already reviewed this product.']);
        }

        // Precondition: customer must have a completed order containing this product
        $order = $user->orders()
            ->where('order_status', 'completed')
            ->whereHas('items.variant', fn ($q) => $q->where('product_id', $product->id))
            ->first();

        if (! $order) {
            return back()->withErrors(['review' => 'You can only review products from a completed order.']);
        }

        $product->reviews()->create([
            'customer_id' => $user->id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Thank you! Your review has been posted.');
    }
}
