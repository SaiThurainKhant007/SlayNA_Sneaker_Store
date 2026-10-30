<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Promotions shown on the home page below the brand logos; the discount also applies in cart and checkout. */
class PromotionController extends Controller
{
    private const IMAGE_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    public function index(): View
    {
        $promotions = Promotion::with('product.brand')->latest()->get();
        $products = Product::with('brand')->where('status', 'active')->orderBy('name')->get();

        return view('admin.promotions', compact('promotions', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image_path'] = $request->file('image')?->store('promotions', 'public');
        $data['hover_image_path'] = $request->file('hover_image')?->store('promotions', 'public');

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion added.');
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $this->validated($request);

        foreach (['image' => 'image_path', 'hover_image' => 'hover_image_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $this->deleteFile($promotion->$column);
                $data[$column] = $request->file($input)->store('promotions', 'public');
            } elseif ($request->boolean('remove_'.$input)) {
                $this->deleteFile($promotion->$column);
                $data[$column] = null;
            }
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $this->deleteFile($promotion->image_path);
        $this->deleteFile($promotion->hover_image_path);
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion removed.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'product_id' => ['required', 'exists:products,id'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'ends_on' => ['nullable', 'date'],
            'image' => self::IMAGE_RULES,
            'hover_image' => self::IMAGE_RULES,
        ]);

        unset($validated['image'], $validated['hover_image']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
