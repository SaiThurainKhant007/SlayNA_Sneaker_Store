@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="card dashboard-card p-4">
    <h1 class="h4 mb-3">Thank you! Your order has been placed.</h1>
    <p>Order <strong>#{{ $order->id }}</strong> is currently
        <span class="badge bg-{{ $order->statusColor() }} text-uppercase">{{ $order->order_status }}</span>.
        Our staff will confirm your payment shortly.
    </p>

    <h2 class="h6 mt-3">Items</h2>
    <ul class="list-group list-group-flush mb-2">
        @foreach ($order->items as $item)
            <li class="list-group-item d-flex justify-content-between px-0">
                <span>{{ $item->variant->product->name }} (EU {{ $item->variant->size->label }}) &times; {{ $item->quantity }}
                    @if ($item->isPromotional())
                        <br><span class="badge text-bg-danger">-{{ $item->discount_percent }}%</span>
                        <small class="text-muted">{{ $item->promotion_title }} &middot; saved {{ number_format($item->savings(), 0) }} MMK</small>
                    @endif
                </span>
                <span>{{ number_format($item->subtotal(), 0) }} MMK</span>
            </li>
        @endforeach
    </ul>
    <div class="d-flex justify-content-between fw-bold mb-3">
        <span>Total</span>
        <span>{{ $order->formattedTotal() }}</span>
    </div>

    @if ($order->payment)
        <p class="small mb-1">Payment: <strong>{{ $order->payment->methodLabel() }}</strong>@if ($order->payment->transaction_ref) &middot; Txn ID {{ $order->payment->transaction_ref }}@endif</p>
    @endif
    <p class="small text-muted">Shipping to: {{ $order->shipping_address }}</p>

    <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline-dark">Track This Order</a>
    <a href="{{ route('products.index') }}" class="btn btn-dark">Continue Shopping</a>
</div>
@endsection
