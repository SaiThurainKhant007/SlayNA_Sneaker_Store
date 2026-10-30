<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** UC_03 Product Management (brand reference data + brand logos). */
class BrandController extends Controller
{
    private const LOGO_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public function index(): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();

        return view('admin.brands', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:brands,name'],
            'logo' => self::LOGO_RULES,
        ]);

        Brand::create([
            'name' => $validated['name'],
            'logo_path' => $request->file('logo')?->store('brands', 'public'),
            'show_in_marquee' => $request->boolean('show_in_marquee'),
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand added.');
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:brands,name,'.$brand->id],
            'logo' => self::LOGO_RULES,
        ]);

        $data = [
            'name' => $validated['name'],
            'show_in_marquee' => $request->boolean('show_in_marquee'),
        ];

        if ($request->hasFile('logo')) {
            $this->deleteLogo($brand);
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogo($brand);
            $data['logo_path'] = null;
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return redirect()->route('admin.brands.index')->with('error', 'Cannot delete a brand that has products.');
        }

        $this->deleteLogo($brand);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand removed.');
    }

    private function deleteLogo(Brand $brand): void
    {
        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }
    }
}
