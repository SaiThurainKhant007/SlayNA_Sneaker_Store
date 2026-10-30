<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $onlyLow = $request->boolean('low');

        $variants = ProductVariant::with(['product', 'size'])
            ->get()
            ->when($onlyLow, fn ($items) => $items->filter(fn ($v) => $v->stock_quantity <= $v->product->low_stock_threshold))
            ->sortBy([
                fn ($v) => $v->product->name,
                fn ($v) => (float) $v->size->label,
            ])
            ->values();

        return view('staff.inventory', compact('variants', 'onlyLow'));
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $variant->update(['stock_quantity' => $validated['stock_quantity']]);

        return redirect()->route('staff.inventory.index', $request->boolean('low') ? ['low' => 1] : [])
            ->with('success', 'Stock updated.');
    }
}
