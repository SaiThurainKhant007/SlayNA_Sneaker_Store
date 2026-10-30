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

        // Running promotions on active products, shown below the brand logos (biggest discount first)
        $promotions = Promotion::live()
            ->whereHas('product', fn ($q) => $q->where('status', 'active'))
            ->with('product.brand')
            ->orderByDesc('discount_percent')
            ->get();

        // Promotional model photos, shown as a gallery below Hot Deals
        $gallery = GalleryImage::shown()->with('product')->get();

        return view('home', compact('featured', 'topBrands', 'marqueeBrands', 'promotions', 'gallery'));
    }
}
