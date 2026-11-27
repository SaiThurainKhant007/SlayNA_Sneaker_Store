<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** UC_04 Product Browsing Management: featured products on the landing page. */
    public function index(): View
    {
        $featured = Product::with(['brand', 'category', 'reviews', 'livePromotion'])
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();

        // Brands with the most active products, used as quick links in the hero search
        $topBrands = Brand::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(5)
            ->get();

        $marqueeBrands = Brand::inMarquee()->orderBy('name')->get();

        // Home-page Hot Deals only (not linked to shop products; biggest discount first)
        $promotions = Promotion::live()->ofType(Promotion::TYPE_PROMOTION)
            ->with('brand')
            ->orderByDesc('discount_percent')
            ->get();

        // Limited Editions: shown and sold right here, below Hot Deals (full price, sizes from their own stock)
        $limitedEditions = Promotion::live()->ofType(Promotion::TYPE_LIMITED)
            ->whereHas('product', fn ($q) => $q->where('status', 'limited'))
            ->with(['product.brand', 'product.variants.size'])
            ->latest('id')
            ->get();

        // Promotional model photos, shown as a gallery below Hot Deals
        $gallery = GalleryImage::shown()->get();

        // Sneaker Showcase: the photos listed in config/shop.php, each linked to its active product
        $entries = collect(config('shop.showcase', []));
        // (brand and promotion give the name, aria label and price shown under the wheel)
        $products = Product::with(['brand', 'livePromotion'])
            ->where('status', 'active')
            ->whereIn('name', $entries->pluck('product'))
            ->get()
            ->keyBy('name');
        $showcase = $entries
            ->filter(fn ($entry) => $products->has($entry['product']))
            ->map(fn ($entry) => ['image' => asset($entry['image']), 'product' => $products[$entry['product']]])
            ->values();

        return view('home', compact('featured', 'topBrands', 'marqueeBrands', 'promotions', 'limitedEditions', 'gallery', 'showcase'));
    }
}
