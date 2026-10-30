<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Counts for the admin sidebar badge and notification bell, shared by every admin page
        View::composer('layouts.admin', function ($view) {
            $alerts = [
                'lowStock' => Product::lowStock()->count(),
                'pendingOrders' => Order::where('order_status', 'pending')->count(),
                'pendingPayments' => Payment::where('payment_status', 'pending')->count(),
            ];
            $alerts['total'] = array_sum($alerts);

            $view->with('adminAlerts', $alerts);
        });

        // "Waiting" counts for the staff sidebar badges
        View::composer('layouts.staff', function ($view) {
            $view->with('staffCounts', [
                'orders' => Order::where('order_status', 'pending')->count(),
                'payments' => Payment::where('payment_status', 'pending')->count(),
                'lowStock' => ProductVariant::lowStock()->count(),
            ]);
        });
    }
}
