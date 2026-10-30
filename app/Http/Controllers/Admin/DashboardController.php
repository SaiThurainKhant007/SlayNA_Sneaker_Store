<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        // Whole periods (to end of day / month): order_date is stamped by the database clock, which can run ahead of the app clock
        $thisMonth = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $lastMonth = [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()];
        $sales = fn () => Order::where('order_status', '!=', 'cancelled');

        // KPI tiles: an all-time total plus how this month compares with last month
        $kpis = [
            'orders' => [
                'total' => Order::count(),
                'delta' => $this->change(Order::whereBetween('order_date', $thisMonth)->count(), Order::whereBetween('order_date', $lastMonth)->count()),
            ],
            'customers' => [
                'total' => User::where('role', 'customer')->count(),
                'delta' => $this->change(User::where('role', 'customer')->whereBetween('created_at', $thisMonth)->count(), User::where('role', 'customer')->whereBetween('created_at', $lastMonth)->count()),
            ],
            'products' => [
                'total' => Product::where('status', 'active')->count(),
                'delta' => $this->change(Product::whereBetween('created_at', $thisMonth)->count(), Product::whereBetween('created_at', $lastMonth)->count()),
            ],
            'sales' => [
                'total' => (float) $sales()->sum('total_amount'),
                'delta' => $this->change((float) $sales()->whereBetween('order_date', $thisMonth)->sum('total_amount'), (float) $sales()->whereBetween('order_date', $lastMonth)->sum('total_amount')),
            ],
            'lowStock' => Product::lowStock()->count(),
        ];

        $chart = [
            'week' => ['label' => 'Last 7 days', 'bars' => $this->dailySales($now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'D j')],
            'month' => ['label' => 'This month', 'bars' => $this->dailySales($now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'j M')],
            'half' => ['label' => 'Last 6 months', 'bars' => $this->monthlySales($now->copy()->subMonthsNoOverflow(5)->startOfMonth(), $now->copy()->endOfMonth())],
        ];

        // Low stock, grouped per product: which sizes are low and the smallest stock left
        $lowStock = ProductVariant::with(['product', 'size'])
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.status', 'active')
            ->whereColumn('product_variants.stock_quantity', '<=', 'products.low_stock_threshold')
            ->orderBy('product_variants.stock_quantity')
            ->get(['product_variants.*'])
            ->groupBy('product_id')
            ->map(fn ($variants) => [
                'product' => $variants->first()->product,
                'sizes' => $variants->sortBy(fn ($v) => (float) $v->size->label)->pluck('size.label')->all(),
                'min' => $variants->min('stock_quantity'),
            ])
            ->sortBy('min')
            ->take(6)
            ->values();

        $recentOrders = Order::with(['customer', 'payment', 'items'])->latest('order_date')->take(6)->get();

        // Best sellers this month: totals per product in a subquery (strict GROUP BY on MariaDB), then joined to Product models
        $soldThisMonth = DB::table('order_items')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereBetween('orders.order_date', $thisMonth)
            ->groupBy('product_variants.product_id')
            ->select('product_variants.product_id', DB::raw('SUM(order_items.quantity) as units'), DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'));

        $topProducts = Product::query()
            ->joinSub($soldThisMonth, 'sold', 'sold.product_id', '=', 'products.id')
            ->orderByDesc('sold.units')
            ->take(5)
            ->with('brand')
            ->get(['products.*', 'sold.units', 'sold.revenue']);

        return view('admin.dashboard', compact('kpis', 'chart', 'lowStock', 'recentOrders', 'topProducts'));
    }

    /** Percent change, or null when there is no previous figure to compare with. */
    private function change(float $current, float $previous): ?array
    {
        if ($previous == 0) {
            return $current > 0 ? ['pct' => null, 'current' => $current] : null;
        }

        return ['pct' => round(($current - $previous) / $previous * 100, 1), 'current' => $current];
    }

    /** One bar per day between $from and $to (days without sales are 0). */
    private function dailySales(Carbon $from, Carbon $to, string $format): array
    {
        $totals = Order::where('order_status', '!=', 'cancelled')
            ->whereBetween('order_date', [$from, $to])
            ->selectRaw('DATE(order_date) as d, SUM(total_amount) as total, COUNT(*) as orders')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $bars = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $row = $totals->get($day->toDateString());
            $bars[] = ['label' => $day->format($format), 'value' => (float) ($row->total ?? 0), 'orders' => (int) ($row->orders ?? 0)];
        }

        return $bars;
    }

    /** One bar per calendar month between $from and $to. */
    private function monthlySales(Carbon $from, Carbon $to): array
    {
        $totals = Order::where('order_status', '!=', 'cancelled')
            ->whereBetween('order_date', [$from, $to])
            ->selectRaw("DATE_FORMAT(order_date, '%Y-%m') as m, SUM(total_amount) as total, COUNT(*) as orders")
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $bars = [];
        for ($month = $from->copy(); $month->lte($to); $month->addMonthNoOverflow()) {
            $row = $totals->get($month->format('Y-m'));
            $bars[] = ['label' => $month->format('M Y'), 'value' => (float) ($row->total ?? 0), 'orders' => (int) ($row->orders ?? 0)];
        }

        return $bars;
    }
}
