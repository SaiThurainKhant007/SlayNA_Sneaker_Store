@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<h1 class="h4 mb-3">Manage Categories</h1>

<div class="row">
    <div class="col-md-7">
        <div class="card dashboard-card p-3">
            <table class="table align-middle js-datatable" data-empty="No categories yet.">
                <thead><tr><th>Name</th><th>Products</th><th data-orderable="false" data-searchable="false">Actions</th></tr></thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td data-order="{{ $category->name }}" data-search="{{ $category->name }}">
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Save</button>
                                </form>
                            </td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card dashboard-card p-3">
            <h2 class="h6">Add Category</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="d-flex gap-1">
                @csrf
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Category name" required>
                <button type="submit" class="btn btn-dark btn-sm">Add</button>
            </form>
        </div>
    </div>
</div>
@include('partials.datatables')
@endsection
