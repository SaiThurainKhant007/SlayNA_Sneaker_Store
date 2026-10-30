<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use Illuminate\View\View;

/** Staff home: what is waiting today, oldest first. */
class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $pendingPayments = Payment::where('payment_status', 'pending')->count();
        $lowStockCount = ProductVariant::lowStock()->count();

        $nextOrders = Order::with(['customer', 'payment', 'items'])
            ->where('order_status', 'pending')
            ->oldest('order_date')
            ->take(5)
            ->get();

        $nextPayments = Payment::with('order.customer')
            ->where('payment_status', 'pending')
            ->oldest('id')
            ->take(5)
            ->get();

        return view('staff.dashboard', compact('pendingOrders', 'pendingPayments', 'lowStockCount', 'nextOrders', 'nextPayments'));
    }
}
