<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function confirmation(Request $request, Order $order): View
    {
        if ($order->customer_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['items.variant.product', 'items.variant.size', 'payment']);

        return view('orders.confirmation', compact('order'));
    }

    /** One order with its status timeline — Customer Scope. */
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $order->load(['items.variant.product', 'items.variant.size', 'payment']);

        return view('orders.show', compact('order'));
    }

    /** Customers can cancel their own order while it is still pending; the items go back in stock. */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        if (! $order->canBeCancelledByCustomer()) {
            return back()->with('error', 'This order has already been confirmed, so it can no longer be cancelled online. Please contact us.');
        }

        $order->cancel();

        return redirect()->route('account.orders.show', $order)->with('success', "Order #{$order->id} has been cancelled.");
    }

    /** Customer order history — Customer Scope. */
    public function history(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['items.variant.product', 'payment'])
            ->latest('order_date')
            ->get();

        return view('orders.history', compact('orders'));
    }
}
