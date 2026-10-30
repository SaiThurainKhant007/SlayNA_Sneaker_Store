@extends('layouts.app')

@section('title', 'Order History')

@section('content')
<h1 class="h4 mb-3">Order History</h1>

@forelse ($orders as $order)
    <div class="card dashboard-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <strong>Order #{{ $order->id }}</strong>
                <span class="text-muted small">&middot; {{ $order->order_date->format('d M Y') }}</span>
            </div>
            <span class="badge bg-{{ $order->statusColor() }} text-uppercase">{{ $order->order_status }}</span>
        </div>
        <ul class="list-group list-group-flush mb-2">
            @foreach ($order->items as $item)
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span>{{ $item->variant->product->name }} &times; {{ $item->quantity }}
                    @if ($item->isPromotional())
                        <br><span class="badge text-bg-danger">-{{ $item->discount_percent }}%</span>
                        <small class="text-muted">{{ $item->promotion_title }} &middot; saved {{ number_format($item->savings(), 0) }} MMK</small>
                    @endif
                </span>
                    <span>{{ number_format($item->subtotal(), 0) }} MMK</span>
                </li>
            @endforeach
        </ul>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Payment: {{ $order->payment ? ucfirst($order->payment->payment_status) : 'n/a' }}
            </span>
            <span class="d-flex align-items-center gap-3">
                <strong>{{ $order->formattedTotal() }}</strong>
                <a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-dark">Track order</a>
            </span>
        </div>
    </div>
@empty
    <p class="text-muted">You haven't placed any orders yet.</p>
@endforelse
@endsection
