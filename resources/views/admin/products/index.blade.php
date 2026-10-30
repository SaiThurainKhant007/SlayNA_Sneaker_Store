@extends('layouts.admin')

@section('title', 'Manage Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Manage Products</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-dark">+ Add Product</a>
</div>

<div class="card dashboard-card p-3">
    <table class="table align-middle js-datatable" data-empty="No products yet.">
        <thead><tr><th>Name</th><th>Brand</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th data-orderable="false" data-searchable="false">Actions</th></tr></thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->brand->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td data-order="{{ (float) $product->price }}">{{ $product->formattedPrice() }}</td>
                    <td class="{{ $product->totalStock() <= $product->low_stock_threshold ? 'low-stock' : '' }}">{{ $product->totalStock() }}</td>
                    <td><span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">{{ $product->status }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Discontinue this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Discontinue</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables')
@endsection
