@extends('layouts.staff')

@section('title', 'Payment Confirmation')

@section('content')
<h1 class="h4 mb-3">Payment Confirmation Management</h1>

<div class="card dashboard-card p-3">
    <table class="table align-middle js-datatable" data-empty="No payments found.">
        <thead><tr><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th data-orderable="false" data-searchable="false">Proof</th><th>Status</th><th data-orderable="false" data-searchable="false">Action</th></tr></thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td data-order="{{ $payment->order_id }}">#{{ $payment->order_id }}</td>
                    <td>{{ $payment->order->customer->name }}<br><span class="small text-muted">{{ $payment->order->customer->email }}</span></td>
                    <td>
                        {{ $payment->methodLabel() }}
                        @if ($payment->transaction_ref)
                            <br><span class="small text-muted">Txn ID: <code>{{ $payment->transaction_ref }}</code></span>
                        @endif
                    </td>
                    <td data-order="{{ (float) $payment->order->total_amount }}">
                        {{ $payment->order->formattedTotal() }}
                        @if ($payment->order->hasPromotion())
                            <br><span class="badge text-bg-danger" title="Promotion price: a lower amount than the list price is expected"><i class="bi bi-tag-fill" aria-hidden="true"></i> PROMO</span>
                            <span class="small text-muted">{{ number_format($payment->order->promotionSavings(), 0) }} MMK discount applied</span>
                        @endif
                    </td>
                    <td>
                        @if ($payment->proof_image)
                            <a href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener" class="sl-proof-thumb" title="Open full screenshot">
                                <img src="{{ asset('storage/'.$payment->proof_image) }}" alt="Transfer screenshot for order #{{ $payment->order_id }}" loading="lazy">
                            </a>
                        @else
                            <span class="text-muted small">No file</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $payment->payment_status === 'confirmed' ? 'success' : ($payment->payment_status === 'rejected' ? 'danger' : 'secondary') }}">{{ $payment->payment_status }}</span></td>
                    <td>
                        @if ($payment->payment_status === 'pending')
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('staff.payments.update', $payment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="confirmed">
                                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                </form>
                                <form method="POST" action="{{ route('staff.payments.update', $payment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="decision" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="small text-muted">Reviewed</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables')
@endsection
