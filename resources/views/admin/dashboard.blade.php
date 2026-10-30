@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    // 1,549,000 -> "1.55M"; the full amount stays in the tooltip/title
    $compact = function (float $n): string {
        return match (true) {
            $n >= 1_000_000 => rtrim(rtrim(number_format($n / 1_000_000, 2), '0'), '.').'M',
            $n >= 10_000 => rtrim(rtrim(number_format($n / 1_000, 1), '0'), '.').'K',
            default => number_format($n, 0),
        };
    };
    $tiles = [
        ['key' => 'orders', 'label' => 'Total orders', 'icon' => 'bi-bag-check-fill', 'value' => number_format($kpis['orders']['total'])],
        ['key' => 'customers', 'label' => 'Total customers', 'icon' => 'bi-people-fill', 'value' => number_format($kpis['customers']['total'])],
        ['key' => 'products', 'label' => 'Active products', 'icon' => 'bi-box-seam-fill', 'value' => number_format($kpis['products']['total'])],
        ['key' => 'sales', 'label' => 'Total sales (MMK)', 'icon' => 'bi-cash-stack', 'value' => $compact($kpis['sales']['total']), 'title' => number_format($kpis['sales']['total']).' MMK'],
    ];
@endphp

@section('content')
<div class="sl-admin-head">
    <div>
        <p class="sl-admin-eyebrow">Overview</p>
        <h1 class="sl-admin-title">Dashboard</h1>
    </div>
</div>

<nav class="sl-admin-tabs" aria-label="Dashboard shortcuts">
    <a href="{{ route('admin.dashboard') }}" class="is-active" aria-current="page">Overview</a>
    <a href="{{ route('admin.products.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Product</a>
    <a href="{{ route('admin.promotions.index') }}"><i class="bi bi-megaphone" aria-hidden="true"></i> New Promotion</a>
    <a href="{{ route('admin.reports.index') }}"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i> Reports</a>
</nav>

{{-- KPI tiles --}}
<div class="sl-kpis">
    @foreach ($tiles as $tile)
        @php $delta = $kpis[$tile['key']]['delta']; @endphp
        <div class="sl-kpi">
            <i class="bi {{ $tile['icon'] }} sl-kpi-icon" aria-hidden="true"></i>
            <p class="sl-kpi-label">{{ $tile['label'] }}</p>
            <p class="sl-kpi-value" @isset($tile['title']) title="{{ $tile['title'] }}" @endisset>{{ $tile['value'] }}</p>
            @if ($delta === null)
                <p class="sl-kpi-delta is-flat">No change vs last month</p>
            @elseif ($delta['pct'] === null)
                <p class="sl-kpi-delta is-up"><i class="bi bi-arrow-up-short" aria-hidden="true"></i>{{ $tile['key'] === 'sales' ? $compact($delta['current']).' MMK' : number_format($delta['current']) }} new this month</p>
            @else
                <p class="sl-kpi-delta {{ $delta['pct'] >= 0 ? 'is-up' : 'is-down' }}">
                    <i class="bi {{ $delta['pct'] >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}" aria-hidden="true"></i>{{ abs($delta['pct']) }}% vs last month
                </p>
            @endif
        </div>
    @endforeach
    <a class="sl-kpi sl-kpi--alert" href="{{ route('admin.reports.index', ['type' => 'inventory']) }}">
        <i class="bi bi-exclamation-triangle-fill sl-kpi-icon" aria-hidden="true"></i>
        <p class="sl-kpi-label">Low stock products</p>
        <p class="sl-kpi-value">{{ number_format($kpis['lowStock']) }}</p>
        <p class="sl-kpi-delta is-down">
            @if ($kpis['lowStock'] > 0)
                <i class="bi bi-arrow-down-short" aria-hidden="true"></i>Needs restocking
            @else
                <i class="bi bi-check2" aria-hidden="true"></i>All stocked up
            @endif
        </p>
    </a>
</div>

<div class="row g-3 g-xl-4">
    {{-- Sales chart --}}
    <div class="col-xl-8">
        <section class="sl-panel h-100" aria-labelledby="sales-chart-title">
            <header class="sl-panel-head">
                <div>
                    <h2 id="sales-chart-title" class="sl-panel-title">Sales Overview</h2>
                    <p class="sl-panel-sub" data-chart-summary></p>
                </div>
                <label class="visually-hidden" for="chart-range">Time range</label>
                <select id="chart-range" class="form-select form-select-sm sl-panel-select">
                    @foreach ($chart as $key => $set)
                        <option value="{{ $key }}" @selected($key === 'month')>{{ $set['label'] }}</option>
                    @endforeach
                </select>
            </header>
            <div class="sl-chart" data-chart="{{ json_encode($chart) }}">
                <div class="sl-chart-plot">
                    <div class="sl-chart-grid" aria-hidden="true"></div>
                    <div class="sl-chart-bars" role="list" aria-label="Sales per period"></div>
                </div>
                <div class="sl-chart-x" aria-hidden="true"></div>
                <div class="sl-chart-tip" role="status" hidden></div>
            </div>
            <details class="sl-chart-table">
                <summary>View as table</summary>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th scope="col">Period</th><th scope="col" class="text-end">Orders</th><th scope="col" class="text-end">Sales (MMK)</th></tr></thead>
                        <tbody data-chart-table></tbody>
                    </table>
                </div>
            </details>
        </section>
    </div>

    {{-- Low stock --}}
    <div class="col-xl-4">
        <section class="sl-panel h-100" aria-labelledby="low-stock-title">
            <header class="sl-panel-head">
                <h2 id="low-stock-title" class="sl-panel-title">Low Stock Products</h2>
                <a href="{{ route('admin.reports.index', ['type' => 'inventory']) }}" class="sl-panel-link">View all</a>
            </header>
            <ul class="sl-list">
                @forelse ($lowStock as $row)
                    <li>
                        <img src="{{ $row['product']->imageUrl() }}" alt="" class="sl-list-thumb" onerror="this.src='{{ asset('img/no-image.png') }}'">
                        <div class="sl-list-main">
                            <a href="{{ route('admin.products.edit', $row['product']) }}" class="sl-list-name">{{ $row['product']->name }}</a>
                            <span class="sl-list-meta">Low sizes: EU {{ implode(', ', array_slice($row['sizes'], 0, 5)) }}@if (count($row['sizes']) > 5) +{{ count($row['sizes']) - 5 }}@endif</span>
                        </div>
                        <span class="sl-stock {{ $row['min'] == 0 ? 'is-out' : '' }}">{{ $row['min'] == 0 ? 'Sold out' : 'Stock: '.$row['min'] }}</span>
                    </li>
                @empty
                    <li class="sl-list-empty"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> Every product is well stocked.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Recent orders: full width on laptops, beside Best Sellers only on very wide screens --}}
    <div class="col-xxl-8">
        <section class="sl-panel h-100" aria-labelledby="recent-orders-title">
            <header class="sl-panel-head">
                <h2 id="recent-orders-title" class="sl-panel-title">Recent Orders</h2>
                <a href="{{ route('admin.reports.index') }}" class="sl-panel-link">Sales report</a>
            </header>
            <div class="table-responsive">
                <table class="table sl-table align-middle mb-0 js-datatable" data-paging="false" data-searching="false" data-info="false" data-empty="No orders yet.">
                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Payment</th><th scope="col">Status</th><th scope="col" class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                {{-- Sorting this column sorts by date; the order number and date share one cell to keep the table narrow --}}
                                <td data-order="{{ $order->order_date->format('Y-m-d H:i:s') }}">
                                    <span class="fw-semibold">#{{ $order->id }}</span>
                                    @if ($order->hasPromotion())<span class="badge text-bg-danger ms-1" title="Bought at a promotion price">PROMO</span>@endif
                                    <span class="sl-table-sub">{{ $order->order_date->format('j M Y') }}</span>
                                </td>
                                <td>{{ $order->customer->name }}</td>
                                <td>{{ $order->payment?->methodLabel() ?? '—' }}</td>
                                <td><span class="badge bg-{{ $order->statusColor() }} text-uppercase">{{ $order->order_status }}</span></td>
                                <td class="text-end fw-semibold" data-order="{{ (float) $order->total_amount }}">{{ $order->formattedTotal() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Best sellers --}}
    <div class="col-xxl-4">
        <section class="sl-panel h-100" aria-labelledby="top-products-title">
            <header class="sl-panel-head">
                <h2 id="top-products-title" class="sl-panel-title">Best Sellers <small>this month</small></h2>
            </header>
            <ol class="sl-list sl-list--ranked">
                @forelse ($topProducts as $product)
                    <li>
                        <span class="sl-rank" aria-hidden="true">{{ $loop->iteration }}</span>
                        <img src="{{ $product->imageUrl() }}" alt="" class="sl-list-thumb" onerror="this.src='{{ asset('img/no-image.png') }}'">
                        <div class="sl-list-main">
                            <span class="sl-list-name">{{ $product->name }}</span>
                            <span class="sl-list-meta">{{ $product->brand->name }} &middot; {{ number_format($product->units) }} sold</span>
                        </div>
                        <span class="sl-list-value" title="{{ number_format((float) $product->revenue) }} MMK">{{ $compact((float) $product->revenue) }}</span>
                    </li>
                @empty
                    <li class="sl-list-empty">No sales yet this month.</li>
                @endforelse
            </ol>
        </section>
    </div>
</div>

<script>
// Sales chart: one red series, bars rebuilt when the range changes; hover/focus shows a tooltip,
// and the same numbers are in the "View as table" section.
(() => {
    const root = document.querySelector('.sl-chart');
    const sets = JSON.parse(root.dataset.chart);
    const plot = root.querySelector('.sl-chart-plot');
    const barsEl = root.querySelector('.sl-chart-bars');
    const gridEl = root.querySelector('.sl-chart-grid');
    const xEl = root.querySelector('.sl-chart-x');
    const tip = root.querySelector('.sl-chart-tip');
    const tableBody = document.querySelector('[data-chart-table]');
    const summary = document.querySelector('[data-chart-summary]');
    const select = document.getElementById('chart-range');
    const fmt = n => Math.round(n).toLocaleString('en-US');
    const compact = n => n >= 1e6 ? +(n / 1e6).toFixed(2) + 'M' : n >= 1e4 ? +(n / 1e3).toFixed(1) + 'K' : fmt(n);

    // Round the top of the axis up to a clean number (1, 2, 2.5 or 5 x 10^k)
    const niceMax = max => {
        if (max <= 0) return 100000;
        const pow = 10 ** Math.floor(Math.log10(max));
        return [1, 2, 2.5, 5, 10].map(m => m * pow).find(v => v >= max);
    };

    const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; };

    function render(key) {
        const bars = sets[key].bars;
        const max = niceMax(Math.max(...bars.map(b => b.value)));
        const total = bars.reduce((s, b) => s + b.value, 0);
        const orders = bars.reduce((s, b) => s + b.orders, 0);
        summary.textContent = `${fmt(total)} MMK from ${orders} ${orders === 1 ? 'order' : 'orders'}`;

        gridEl.replaceChildren(...[1, 0.75, 0.5, 0.25, 0].map(f => {
            const line = el('div', 'sl-chart-gridline');
            line.style.bottom = (f * 100) + '%';
            line.append(el('span', null, compact(max * f)));
            return line;
        }));

        // Label every bar when there are few, otherwise about 6 evenly spaced labels plus the last
        const step = bars.length <= 8 ? 1 : Math.ceil(bars.length / 6);
        barsEl.replaceChildren();
        xEl.replaceChildren();
        bars.forEach((b, i) => {
            const last = i === bars.length - 1;
            const slot = el('div', 'sl-chart-slot');
            slot.setAttribute('role', 'listitem');
            const bar = el('button', 'sl-chart-bar' + (last ? ' is-current' : ''));
            bar.type = 'button';
            bar.style.height = Math.max(b.value / max * 100, b.value > 0 ? 1.5 : 0) + '%';
            bar.setAttribute('aria-label', `${b.label}: ${fmt(b.value)} MMK, ${b.orders} ${b.orders === 1 ? 'order' : 'orders'}`);
            bar.dataset.index = i;
            if (last && b.value > 0) bar.append(el('span', 'sl-chart-cap', compact(b.value)));   // label only the current period
            slot.append(bar);
            barsEl.append(slot);
            const x = el('span', null, (i % step === 0 || last) ? b.label : '');
            xEl.append(x);
        });

        tableBody.replaceChildren(...bars.map(b => {
            const tr = el('tr');
            tr.append(el('td', null, b.label), el('td', 'text-end', String(b.orders)), el('td', 'text-end', fmt(b.value)));
            return tr;
        }));
        root.dataset.current = key;
    }

    function showTip(bar) {
        const b = sets[root.dataset.current].bars[bar.dataset.index];
        tip.replaceChildren(el('strong', null, fmt(b.value) + ' MMK'), el('span', null, `${b.label} · ${b.orders} ${b.orders === 1 ? 'order' : 'orders'}`));
        tip.hidden = false;
        const r = bar.getBoundingClientRect(), p = root.getBoundingClientRect();
        const left = Math.min(Math.max(r.left - p.left + r.width / 2, 70), p.width - 70);
        tip.style.left = left + 'px';
        tip.style.top = Math.max(r.top - p.top, 0) + 'px';
    }
    barsEl.addEventListener('pointerover', e => { const bar = e.target.closest('.sl-chart-slot')?.querySelector('.sl-chart-bar'); if (bar) showTip(bar); });
    barsEl.addEventListener('focusin', e => { if (e.target.classList.contains('sl-chart-bar')) showTip(e.target); });
    barsEl.addEventListener('pointerleave', () => { tip.hidden = true; });
    barsEl.addEventListener('focusout', () => { tip.hidden = true; });

    select.addEventListener('change', () => render(select.value));
    render(select.value);
})();
</script>
@include('partials.datatables')
@endsection
