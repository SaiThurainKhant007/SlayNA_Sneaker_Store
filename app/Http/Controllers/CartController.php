<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Shopping cart (persisted per customer) — Objective 3. */
class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = $request->user()->cartItems()
            ->with(['variant.size', 'variant.product.livePromotion'])
            ->latest()
            ->get();

        $total = $cartItems->sum(fn ($item) => $item->subtotal());

        return view('cart.index', compact('cartItems', 'total'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $quantity = max(1, min(10, (int) ($validated['quantity'] ?? 1)));

        if ($variant->stock_quantity < $quantity) {
            return redirect()->route('products.show', $variant->product_id)
                ->with('error', 'Not enough stock available for the selected size.');
        }

        $customer = $request->user();
        $existing = $customer->cartItems()->where('variant_id', $variant->id)->first();

        if ($existing) {
            $existing->update(['quantity' => min(10, $existing->quantity + $quantity)]);
        } else {
            $customer->cartItems()->create(['variant_id' => $variant->id, 'quantity' => $quantity]);
        }

        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }

    public function update(Request $request, \App\Models\CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $cartItem->update(['quantity' => $validated['quantity']]);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, \App\Models\CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($request, $cartItem);

        $cartItem->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    private function authorizeOwnership(Request $request, \App\Models\CartItem $cartItem): void
    {
        if ($cartItem->customer_id !== $request->user()->id) {
            abort(403);
        }
    }
}
