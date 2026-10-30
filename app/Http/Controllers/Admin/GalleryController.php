<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Promotional model photos for the home page gallery (below Hot Deals). */
class GalleryController extends Controller
{
    private const IMAGE_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    public function index(): View
    {
        $images = GalleryImage::with('product')->orderBy('sort_order')->orderBy('id')->get();
        $products = Product::with('brand')->where('status', 'active')->orderBy('name')->get();

        return view('admin.gallery', compact('images', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:12'],
            'images.*' => self::IMAGE_RULES,
        ], [
            'images.required' => 'Choose at least one photo to upload.',
        ]);

        // Several photos can be uploaded at once; they go to the end of the gallery
        $next = (int) GalleryImage::max('sort_order') + 1;
        foreach ($request->file('images') as $file) {
            GalleryImage::create([
                'image_path' => $file->store('gallery', 'public'),
                'sort_order' => $next++,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.gallery.index')->with('success', count($request->file('images')).' photo(s) added to the gallery.');
    }

    public function update(Request $request, GalleryImage $image): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:120'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', ...self::IMAGE_RULES],
        ]);

        $data = collect($validated)->except('image')->all();
        $data['is_active'] = $request->boolean('is_active');

        $oldPath = $image->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }

        $image->update($data);

        if (isset($data['image_path'])) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.gallery.index')->with('success', 'Photo updated.');
    }

    public function destroy(GalleryImage $image): RedirectResponse
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->route('admin.gallery.index')->with('success', 'Photo removed from the gallery.');
    }
}
