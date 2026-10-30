@extends('layouts.staff')

@section('title', 'Inventory Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Inventory Management</h1>
    <div>
        <a href="{{ route('staff.inventory.index') }}" class="btn btn-sm btn-outline-dark {{ !$onlyLow ? 'active' : '' }}">All</a>
        <a href="{{ route('staff.inventory.index', ['low' => 1]) }}" class="btn btn-sm btn-outline-danger {{ $onlyLow ? 'active' : '' }}">Low Stock Only</a>
    </div>
</div>

<div class="card dashboard-card p-3">
    <table class="table align-middle js-datatable" data-page-length="25" data-empty="No items to show.">
        <thead><tr><th>Product</th><th>Size</th><th>Stock</th><th data-orderable="false" data-searchable="false">Update</th></tr></thead>
        <tbody>
            @foreach ($variants as $variant)
                <tr>
                    <td><a href="{{ route('products.show', $variant->product) }}" target="_blank">{{ $variant->product->name }}</a></td>
                    <td data-order="{{ (float) $variant->size->label }}">EU {{ $variant->size->label }}</td>
                    <td class="{{ $variant->isLowStock() ? 'low-stock' : '' }}">{{ $variant->stock_quantity }}</td>
                    <td>
                        <form method="POST" action="{{ route('staff.inventory.update', $variant) }}" class="d-flex gap-1">
                            @csrf
                            @method('PATCH')
                            @if($onlyLow)<input type="hidden" name="low" value="1">@endif
                            <input type="number" name="stock_quantity" value="{{ $variant->stock_quantity }}" min="0" class="form-control form-control-sm" style="width:90px">
                            <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables')
@endsection
