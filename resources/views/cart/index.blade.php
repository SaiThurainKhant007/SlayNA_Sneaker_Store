@extends('layouts.app')

@section('title', 'My Cart')

@section('content')
<h1 class="h4 mb-3">Shopping Cart</h1>

@if ($cartItems->isEmpty())
    <p class="text-muted">Your cart is empty. <a href="{{ route('products.index') }}">Continue shopping</a>.</p>
@else
    <div class="table-responsive">
        <table class="table align-middle js-datatable" data-paging="false" data-searching="false" data-info="false">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th data-orderable="false">Quantity</th>
                    <th>Subtotal</th>
                    <th data-orderable="false"><span class="visually-hidden">Remove</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $item->variant->product->imageUrl() }}" width="50" height="50" style="object-fit:cover" onerror="this.src='{{ asset('img/no-image.png') }}'">
                                <a href="{{ route('products.show', $item->variant->product) }}">{{ $item->variant->product->name }}</a>
                            </div>
                        </td>
                        <td data-order="{{ (float) $item->variant->size->label }}">EU {{ $item->variant->size->label }}</td>
                        <td data-order="{{ (float) $item->variant->product->effectivePrice() }}">
                            {{ $item->variant->product->formattedPrice() }}
                            @if ($item->variant->product->livePromotion)
                                <br><small class="text-muted text-decoration-line-through">{{ number_format((float) $item->variant->product->price, 0) }} MMK</small>
                                <span class="badge text-bg-danger">-{{ $item->variant->product->livePromotion->discount_percent }}%</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $item) }}" class="d-flex gap-1">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->variant->stock_quantity }}" class="form-control form-control-sm" style="width:70px">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                            </form>
                        </td>
                        <td data-order="{{ (float) $item->subtotal() }}">{{ number_format($item->subtotal(), 0) }} MMK</td>
                        <td>
                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h5">Total: {{ number_format($total, 0) }} MMK</h2>
        <a href="{{ route('checkout.show') }}" class="btn btn-dark">Proceed to Checkout</a>
    </div>
    @include('partials.datatables')
@endif
@endsection
