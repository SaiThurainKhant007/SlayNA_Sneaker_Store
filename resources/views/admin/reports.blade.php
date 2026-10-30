@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<h1 class="h4 mb-3">Business Reports</h1>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ $reportType === 'sales' ? 'active' : '' }}" href="?type=sales">Sales</a></li>
    <li class="nav-item"><a class="nav-link {{ $reportType === 'inventory' ? 'active' : '' }}" href="?type=inventory">Inventory</a></li>
    <li class="nav-item"><a class="nav-link {{ $reportType === 'customers' ? 'active' : '' }}" href="?type=customers">Customers</a></li>
</ul>

@if ($reportType === 'sales')
    <div class="card dashboard-card p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h6 mb-0">Daily Sales (last 30 days with orders)</h2>
            <a href="{{ route('admin.reports.export', 'sales') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Download CSV</a>
        </div>
        <table class="table table-sm js-datatable" data-order='[[0, "desc"]]' data-empty="No sales data yet.">
            <thead><tr><th>Date</th><th>Orders</th><th>Total Sales</th></tr></thead>
            <tbody>
                @foreach ($salesReport as $row)
                    <tr><td>{{ $row->day }}</td><td>{{ $row->order_count }}</td><td data-order="{{ (float) $row->total_sales }}">{{ number_format($row->total_sales, 0) }} MMK</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card dashboard-card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h6 mb-0">Top Selling Products</h2>
            <a href="{{ route('admin.reports.export', 'top-products') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Download CSV</a>
        </div>
        <table class="table table-sm js-datatable" data-order='[[1, "desc"]]' data-empty="No sales data yet.">
            <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
                @foreach ($topProducts as $tp)
                    <tr><td>{{ $tp->product_name }}</td><td>{{ $tp->units_sold }}</td><td data-order="{{ (float) $tp->revenue }}">{{ number_format($tp->revenue, 0) }} MMK</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

@elseif ($reportType === 'inventory')
    <div class="card dashboard-card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h6 mb-0">Stock Levels (lowest first)</h2>
            <a href="{{ route('admin.reports.export', 'inventory') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Download CSV</a>
        </div>
        <table class="table table-sm js-datatable" data-empty="No stock records yet.">
            <thead><tr><th>Product</th><th>Size</th><th>Stock</th><th>Threshold</th></tr></thead>
            <tbody>
                @foreach ($inventoryReport as $variant)
                    <tr>
                        <td>{{ $variant->product->name }}</td>
                        <td data-order="{{ (float) $variant->size->label }}">EU {{ $variant->size->label }}</td>
                        <td class="{{ $variant->isLowStock() ? 'low-stock' : '' }}">{{ $variant->stock_quantity }}</td>
                        <td>{{ $variant->product->low_stock_threshold }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@else
    <div class="card dashboard-card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h6 mb-0">Customer Spend Summary</h2>
            <a href="{{ route('admin.reports.export', 'customers') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Download CSV</a>
        </div>
        <table class="table table-sm js-datatable" data-empty="No customers yet.">
            <thead><tr><th>Customer</th><th>Email</th><th>Orders</th><th>Total Spent</th></tr></thead>
            <tbody>
                @foreach ($customerReport as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->orders_count }}</td>
                        <td data-order="{{ (float) $customer->total_spent }}">{{ number_format($customer->total_spent, 0) }} MMK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@include('partials.datatables')
@endsection
