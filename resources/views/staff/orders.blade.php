@extends('layouts.staff')

@section('title', 'Order Processing')

@section('content')
<h1 class="h4 mb-3">Order Processing Management</h1>

<div class="mb-3">
    <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-outline-dark {{ $statusFilter === '' ? 'active' : '' }}">All</a>
    @foreach (\App\Models\Order::STATUSES as $s)
        <a href="{{ route('staff.orders.index', ['status' => $s]) }}" class="btn btn-sm btn-outline-dark {{ $statusFilter === $s ? 'active' : '' }}">{{ ucfirst($s) }}</a>
    @endforeach
</div>

<div class="card dashboard-card p-3">
    <table class="table align-middle js-datatable" data-empty="No orders found.">
        <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th data-orderable="false" data-searchable="false">Update</th></tr></thead>
        <tbody>
            @foreach ($orders as $order)
                <tr @if ($order->hasPromotion()) class="sl-staff-promo-row" @endif>
                    <td data-order="{{ $order->id }}">#{{ $order->id }}</td>
                    <td>{{ $order->customer->name }}<br><span class="small text-muted">{{ $order->customer->email }}</span></td>
                    <td data-order="{{ $order->order_date->format('Y-m-d H:i:s') }}">{{ $order->order_date->format('d M Y') }}</td>
                    <td data-order="{{ (float) $order->total_amount }}">
                        {{ $order->formattedTotal() }}
                        @if ($order->hasPromotion())
                            <br><span class="badge text-bg-danger" title="Contains promotional items"><i class="bi bi-tag-fill" aria-hidden="true"></i> PROMO</span>
                            <span class="small text-success">saved {{ number_format($order->promotionSavings(), 0) }} MMK</span>
                        @endif
                        @include('staff._promo_items', ['order' => $order])
                    </td>
                    <td><span class="badge bg-{{ $order->payment && $order->payment->payment_status === 'confirmed' ? 'success' : 'secondary' }}">{{ $order->payment->payment_status ?? 'n/a' }}</span></td>
                    <td><span class="badge bg-{{ $order->statusColor() }} text-uppercase">{{ $order->order_status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('staff.orders.update', $order) }}" class="d-flex gap-1">
                            @csrf
                            @method('PATCH')
                            <select name="order_status" class="form-select form-select-sm">
                                @foreach (\App\Models\Order::STATUSES as $s)
                                    <option value="{{ $s }}" @selected($order->order_status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-dark">Go</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables')
@endsection
