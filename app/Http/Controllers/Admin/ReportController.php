<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** UC_06 Reports Management. */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $reportType = $request->query('type', 'sales');

        $salesReport = $this->salesReport();
        $topProducts = $this->topProducts();
        $inventoryReport = $this->inventoryReport();
        $customerReport = $this->customerReport();

        return view('admin.reports', compact('reportType', 'salesReport', 'topProducts', 'inventoryReport', 'customerReport'));
    }

    /** Download one report as a CSV file that opens in Excel. */
    public function export(string $report): StreamedResponse
    {
        [$headers, $rows] = match ($report) {
            'sales' => [['Date', 'Orders', 'Total Sales (MMK)'], $this->salesReport()
                ->map(fn ($r) => [$r->day, $r->order_count, (float) $r->total_sales])],
            'top-products' => [['Product', 'Units Sold', 'Revenue (MMK)'], $this->topProducts()
                ->map(fn ($r) => [$r->product_name, $r->units_sold, (float) $r->revenue])],
            'inventory' => [['Product', 'Size (EU)', 'Stock', 'Low Stock Threshold'], $this->inventoryReport()
                ->map(fn ($v) => [$v->product->name, $v->size->label, $v->stock_quantity, $v->product->low_stock_threshold])],
            'customers' => [['Customer', 'Email', 'Orders', 'Total Spent (MMK)'], $this->customerReport()
                ->map(fn ($c) => [$c->name, $c->email, $c->orders_count, (float) $c->total_spent])],
            default => abort(404),
        };

        $filename = 'slayna-'.$report.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // UTF-8 marker so Excel shows Myanmar names correctly
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, array_map($this->safeCell(...), $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stop Excel treating text such as a customer name "=HYPERLINK(...)" as a formula. */
    private function safeCell(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    private function salesReport(): Collection
    {
        return Order::where('order_status', '!=', 'cancelled')
            ->selectRaw('DATE(order_date) as day, COUNT(*) as order_count, SUM(total_amount) as total_sales')
            ->groupBy('day')
            ->orderByDesc('day')
            ->take(30)
            ->get();
    }

    private function topProducts(): Collection
    {
        return OrderItem::query()
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->take(10)
            ->get([
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
            ]);
    }

    private function inventoryReport(): Collection
    {
        return ProductVariant::with(['product', 'size'])
            ->orderBy('stock_quantity')
            ->take(30)
            ->get();
    }

    private function customerReport(): Collection
    {
        return User::where('role', 'customer')
            ->withCount(['orders' => fn ($q) => $q->where('order_status', '!=', 'cancelled')])
            ->get()
            ->map(function ($customer) {
                $customer->total_spent = $customer->orders()->where('order_status', '!=', 'cancelled')->sum('total_amount');
                return $customer;
            })
            ->sortByDesc('total_spent')
            ->take(20);
    }
}
