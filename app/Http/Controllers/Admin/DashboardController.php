<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $now = now();
        // Whole periods (to end of day / month): order_date is stamped by the database clock, which can run ahead of the app clock
        $thisMonth = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $lastMonth = [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()];
        $sales = fn () => Order::where('order_status', '!=', 'cancelled');

        // KPI tiles: each tile has its own optional start/end date (e.g. ?orders[from]=2026-11-01&orders[to]=2026-11-12).
        // No dates: all-time total, this month vs last month. With dates: total inside the range, compared with
        // the period of the same length just before it.
        $metrics = [
            'orders' => fn ($range) => Order::when($range, fn ($q) => $q->whereBetween('order_date', $range))->count(),
            'customers' => fn ($range) => Customer::when($range, fn ($q) => $q->whereBetween('created_at', $range))->count(),
            'products' => fn ($range) => Product::where('status', 'active')->when($range, fn ($q) => $q->whereBetween('created_at', $range))->count(),
            'sales' => fn ($range) => (float) $sales()->when($range, fn ($q) => $q->whereBetween('order_date', $range))->sum('total_amount'),
        ];
        $newThisPeriod = [
            'orders' => fn ($range) => Order::whereBetween('order_date', $range)->count(),
            'customers' => fn ($range) => Customer::whereBetween('created_at', $range)->count(),
            'products' => fn ($range) => Product::whereBetween('created_at', $range)->count(),
            'sales' => fn ($range) => (float) $sales()->whereBetween('order_date', $range)->sum('total_amount'),
        ];

        $kpis = [];
        foreach ($metrics as $key => $metric) {
            $filter = $this->dateFilter($request->input($key));

            if ($filter === null) {
                $kpis[$key] = [
                    'total' => $metric(null),
                    'delta' => $this->change($newThisPeriod[$key]($thisMonth), $newThisPeriod[$key]($lastMonth)),
                    'compare' => 'last month',
                    'period' => 'this month',
                    'from' => null,
                    'to' => null,
                ];
                continue;
            }

            [$from, $to] = $filter;
            $days = (int) $from->diffInDays($to) + 1;
            $previous = [$from->copy()->subDays($days), $from->copy()->subDay()->endOfDay()];

            $kpis[$key] = [
                'total' => $metric([$from, $to]),
                'delta' => $this->change($metric([$from, $to]), $metric($previous)),
                'compare' => 'previous '.$days.' '.str('day')->plural($days),
                'period' => 'in range',
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ];
        }

        $kpis['lowStock'] = Product::lowStock()->count();

        $chart = [
            'week' => ['label' => 'Last 7 days', 'bars' => $this->dailySales($now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'D j')],
            'month' => ['label' => 'This month', 'bars' => $this->dailySales($now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'j M')],
            'half' => ['label' => 'Last 6 months', 'bars' => $this->monthlySales($now->copy()->subMonthsNoOverflow(5)->startOfMonth(), $now->copy()->endOfMonth())],
        ];

        // Low stock, grouped per product: which sizes are low and the smallest stock left
        $lowStock = ProductSize::with(['product', 'size'])
            ->join('products', 'products.id', '=', 'product_sizes.product_id')
            ->where('products.status', 'active')
            ->whereColumn('product_sizes.stock_quantity', '<=', 'products.low_stock_threshold')
            ->orderBy('product_sizes.stock_quantity')
            ->get(['product_sizes.*'])
            ->groupBy('product_id')
            ->map(fn ($variants) => [
                'product' => $variants->first()->product,
                'sizes' => $variants->sortBy(fn ($v) => (float) $v->size->label)
                    ->map(fn ($v) => $v->size->label.($v->color ? ' ('.$v->color.')' : ''))->values()->all(),
                'min' => $variants->min('stock_quantity'),
            ])
            ->sortBy('min')
            ->take(6)
            ->values();

        $recentOrders = Order::with(['customer', 'payment', 'items'])->latest('order_date')->take(6)->get();

        // Best sellers this month: totals per product in a subquery (strict GROUP BY on MariaDB), then joined to Product models
        $soldThisMonth = DB::table('order_items')
            ->join('product_sizes', 'product_sizes.id', '=', 'order_items.product_size_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereBetween('orders.order_date', $thisMonth)
            ->groupBy('product_sizes.product_id')
            ->select('product_sizes.product_id', DB::raw('SUM(order_items.quantity) as units'), DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'));

        $topProducts = Product::query()
            ->joinSub($soldThisMonth, 'sold', 'sold.product_id', '=', 'products.id')
            ->orderByDesc('sold.units')
            ->take(5)
            ->with('brand')
            ->get(['products.*', 'sold.units', 'sold.revenue']);

        return view('admin.dashboard', compact('kpis', 'chart', 'lowStock', 'recentOrders', 'topProducts'));
    }

    /**
     * A tile's [start of from-day, end of to-day] range, or null when no valid date was given.
     * A missing end date means today; a missing start date means the same day as the end.
     */
    private function dateFilter(mixed $input): ?array
    {
        if (! is_array($input)) {
            return null;
        }

        $parse = function ($value): ?Carbon {
            if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            try {
                return Carbon::createFromFormat('!Y-m-d', $value);
            } catch (\Throwable) {
                return null;
            }
        };

        $from = $parse($input['from'] ?? null);
        $to = $parse($input['to'] ?? null);

        if (! $from && ! $to) {
            return null;
        }

        $to ??= now()->startOfDay();
        $from ??= $to->copy();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
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
            ->selectRaw('SUBSTR(order_date, 1, 7) as m, SUM(total_amount) as total, COUNT(*) as orders') // YYYY-MM on both MySQL and SQLite
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
