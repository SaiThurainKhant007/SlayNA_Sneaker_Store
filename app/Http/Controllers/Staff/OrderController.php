<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', '');

        $query = Order::with(['customer', 'payment', 'items.variant.product', 'items.variant.size']);

        if (in_array($statusFilter, Order::STATUSES, true)) {
            $query->where('order_status', $statusFilter);
        }

        $orders = $query->latest('order_date')->get();

        return view('staff.orders', compact('orders', 'statusFilter'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'in:'.implode(',', Order::STATUSES)],
        ]);

        if ($validated['order_status'] === 'cancelled') {
            $order->cancel($request->user()->id);   // restocks the items once
        } else {
            $order->update([
                'order_status' => $validated['order_status'],
                'processed_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('staff.orders.index', $request->query('status') ? ['status' => $request->query('status')] : [])
            ->with('success', "Order #{$order->id} updated to {$validated['order_status']}.");
    }
}
