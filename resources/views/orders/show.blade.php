@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
<a href="{{ route('account.orders') }}" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> All orders</a>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-3">
    <div>
        <h1 class="h4 mb-0">Order #{{ $order->id }}</h1>
        <span class="text-muted small">Placed {{ $order->order_date->format('j M Y, g:i A') }}</span>
    </div>
    <span class="badge bg-{{ $order->statusColor() }} text-uppercase">{{ $order->order_status }}</span>
</div>

{{-- Status timeline --}}
<div class="card dashboard-card p-3 mb-3">
    @if ($order->order_status === 'cancelled')
        <p class="mb-0 text-danger"><i class="bi bi-x-circle-fill" aria-hidden="true"></i> This order was cancelled. Any items were returned to stock.</p>
    @else
        @php $current = $order->stepIndex(); @endphp
        <ol class="sl-steps" aria-label="Order progress">
            @foreach (\App\Models\Order::STEPS as $status => $label)
                @php $state = $loop->index < $current ? 'is-done' : ($loop->index === $current ? 'is-current' : ''); @endphp
                <li class="{{ $state }}" @if ($state === 'is-current') aria-current="step" @endif>
                    <span class="sl-steps-dot" aria-hidden="true">
                        @if ($state === 'is-done')<i class="bi bi-check-lg"></i>@else{{ $loop->iteration }}@endif
                    </span>
                    <span class="sl-steps-label">{{ $label }}</span>
                    <span class="visually-hidden">{{ $state === 'is-done' ? '(done)' : ($state === 'is-current' ? '(current)' : '') }}</span>
                </li>
            @endforeach
        </ol>
        @if ($order->order_status === 'pending')
            <p class="small text-muted mb-0 mt-3">Our staff will check your payment and confirm the order soon.</p>
        @endif
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Items</h2>
            <ul class="list-group list-group-flush">
                @foreach ($order->items as $item)
                    <li class="list-group-item d-flex justify-content-between gap-3 px-0">
                        <span>
                            <a href="{{ route('products.show', $item->variant->product) }}">{{ $item->variant->product->name }}</a>
                            <span class="text-muted small">&middot; EU {{ $item->variant->size->label }} &times; {{ $item->quantity }}</span>
                            @if ($item->isPromotional())
                                <br><span class="badge text-bg-danger">-{{ $item->discount_percent }}%</span>
                                <small class="text-muted">{{ $item->promotion_title }} &middot; saved {{ number_format($item->savings(), 0) }} MMK</small>
                            @endif
                        </span>
                        <span class="text-nowrap">{{ number_format($item->subtotal(), 0) }} MMK</span>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex justify-content-between fw-bold border-top pt-2">
                <span>Total</span>
                <span>{{ $order->formattedTotal() }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Payment &amp; delivery</h2>
            @if ($order->payment)
                <p class="small mb-1">Method: <strong>{{ $order->payment->methodLabel() }}</strong></p>
                <p class="small mb-1">Payment status: <strong>{{ ucfirst($order->payment->payment_status) }}</strong></p>
                @if ($order->payment->transaction_ref)<p class="small mb-1">Txn ID: {{ $order->payment->transaction_ref }}</p>@endif
            @endif
            <p class="small mb-0">Shipping to: {{ $order->shipping_address }}</p>

            @if ($order->canBeCancelledByCustomer())
                <form method="POST" action="{{ route('account.orders.cancel', $order) }}" class="mt-3" onsubmit="return confirm('Cancel order #{{ $order->id }}? This cannot be undone.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Cancel order</button>
                    <p class="form-text mb-0">You can cancel until our staff confirm the order.</p>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
