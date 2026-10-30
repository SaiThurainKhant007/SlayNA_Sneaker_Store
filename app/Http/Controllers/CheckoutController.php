<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

/** Cart & checkout (feeds UC_08 Order Processing, UC_09 Payment Confirmation). */
class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->with(['variant.product.livePromotion', 'variant.size'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(fn ($item) => $item->subtotal());
        $savedCards = $user->savedCards()->get();
        $wallets = config('payments.wallets');

        return view('checkout.show', compact('cartItems', 'total', 'savedCards', 'wallets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $wallets = array_keys(config('payments.wallets'));

        // Mobile wallet transfers need a screenshot so staff can match the payment
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:'.implode(',', [...$wallets, 'cod', 'saved_card'])],
            'proof_image' => ['required_if:payment_method,'.implode(',', $wallets), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'transaction_ref' => ['nullable', 'string', 'max:40'],
        ], [
            'proof_image.required_if' => 'Please upload a screenshot of your transfer.',
        ]);

        $cartItems = $user->cartItems()->with('variant.product.livePromotion')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(fn ($item) => $item->subtotal());

        try {
            $order = DB::transaction(function () use ($user, $validated, $cartItems, $total, $request, $wallets) {
                // Re-check stock for every line to avoid overselling
                foreach ($cartItems as $item) {
                    $variant = $item->variant()->lockForUpdate()->first();
                    if ($variant->stock_quantity < $item->quantity) {
                        throw new RuntimeException("Insufficient stock for {$variant->product->name}. Please update your cart.");
                    }
                }

                $order = Order::create([
                    'customer_id' => $user->id,
                    'shipping_address' => $validated['shipping_address'],
                    'total_amount' => $total,
                    'order_status' => 'pending',
                ]);

                foreach ($cartItems as $item) {
                    $product = $item->variant->product;
                    $promo = $product->livePromotion;

                    // Promotion price is applied automatically; the promotion is recorded on the line for staff
                    $order->items()->create([
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $product->effectivePrice(),
                        'original_price' => $promo ? $product->price : null,
                        'discount_percent' => $promo?->discount_percent,
                        'promotion_id' => $promo?->id,
                        'promotion_title' => $promo?->title,
                    ]);

                    $item->variant->decrement('stock_quantity', $item->quantity);
                }

                $proofPath = null;
                if (in_array($validated['payment_method'], $wallets, true) && $request->hasFile('proof_image')) {
                    $proofPath = $request->file('proof_image')->store('payment_proofs', 'public');
                }

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $validated['payment_method'],
                    'proof_image' => $proofPath,
                    'transaction_ref' => in_array($validated['payment_method'], $wallets, true) ? ($validated['transaction_ref'] ?? null) : null,
                    'payment_status' => 'pending',
                ]);

                $user->cartItems()->delete();

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

        return redirect()->route('orders.confirmation', $order)
            ->with('success', 'Order placed successfully! Our staff will confirm your payment shortly.');
    }
}
