@extends('layouts.staff')

@section('title', 'Dashboard')

@php
    $hour = (int) now(config('app.store_timezone'))->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $tasks = [
        ['count' => $pendingOrders, 'label' => 'Orders to process', 'icon' => 'bi-bag-check-fill', 'url' => route('staff.orders.index', ['status' => 'pending'])],
        ['count' => $pendingPayments, 'label' => 'Payments to confirm', 'icon' => 'bi-credit-card-fill', 'url' => route('staff.payments.index')],
        ['count' => $lowStockCount, 'label' => 'Sizes low on stock', 'icon' => 'bi-box-seam-fill', 'url' => route('staff.inventory.index', ['low' => 1])],
    ];
@endphp

@section('content')
<div class="sl-staff-hello">
    <h1>{{ $greeting }}, {{ Str::before(auth()->user()->name, ' ') ?: auth()->user()->name }}</h1>
    <p>
        @if ($pendingOrders + $pendingPayments === 0)
            You're all caught up. Nothing is waiting right now.
        @else
            Here's what needs your attention today.
        @endif
    </p>
</div>

{{-- Three task tiles: tap to go straight to the work --}}
<div class="sl-staff-tasks">
    @foreach ($tasks as $task)
        <a href="{{ $task['url'] }}" class="sl-staff-task {{ $task['count'] > 0 ? 'has-work' : '' }}">
            <i class="bi {{ $task['icon'] }}" aria-hidden="true"></i>
            <span class="sl-staff-task-count">{{ number_format($task['count']) }}</span>
            <span class="sl-staff-task-label">{{ $task['label'] }}</span>
            <span class="sl-staff-task-go">{{ $task['count'] > 0 ? 'Open' : 'View' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </a>
    @endforeach
</div>

<div class="row g-3 g-xl-4">
    {{-- Next orders --}}
    <div class="col-lg-6">
        <section class="sl-panel h-100" aria-labelledby="next-orders">
            <header class="sl-panel-head">
                <h2 id="next-orders" class="sl-panel-title">Next orders</h2>
                <a href="{{ route('staff.orders.index', ['status' => 'pending']) }}" class="sl-panel-link">All orders</a>
            </header>
            <ul class="sl-list">
                @forelse ($nextOrders as $order)
                    <li>
                        <div class="sl-list-main">
                            <span class="sl-list-name">#{{ $order->id }} &middot; {{ $order->customer->name }}</span>
                            <span class="sl-list-meta">
                                {{ $order->order_date->format('j M, g:i A') }} &middot; {{ $order->items->sum('quantity') }} {{ Str::plural('item', $order->items->sum('quantity')) }}
                                @if ($order->payment) &middot; {{ $order->payment->methodLabel() }}@endif
                                @if ($order->hasPromotion()) &middot; <span class="text-danger fw-semibold">PROMO</span>@endif
                            </span>
                        </div>
                        <span class="sl-list-value">{{ $order->formattedTotal() }}</span>
                    </li>
                @empty
                    <li class="sl-list-empty"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>&nbsp;No orders waiting.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Next payments --}}
    <div class="col-lg-6">
        <section class="sl-panel h-100" aria-labelledby="next-payments">
            <header class="sl-panel-head">
                <h2 id="next-payments" class="sl-panel-title">Payments to check</h2>
                <a href="{{ route('staff.payments.index') }}" class="sl-panel-link">All payments</a>
            </header>
            <ul class="sl-list">
                @forelse ($nextPayments as $payment)
                    <li>
                        <div class="sl-list-main">
                            <span class="sl-list-name">Order #{{ $payment->order_id }} &middot; {{ $payment->order->customer->name }}</span>
                            <span class="sl-list-meta">
                                {{ $payment->methodLabel() }}
                                @if ($payment->transaction_ref) &middot; Txn {{ $payment->transaction_ref }}@endif
                                &middot; {{ $payment->proof_image ? 'Screenshot attached' : 'No screenshot' }}
                            </span>
                        </div>
                        <span class="sl-list-value">{{ $payment->order->formattedTotal() }}</span>
                    </li>
                @empty
                    <li class="sl-list-empty"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>&nbsp;No payments waiting.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
