@extends('layouts.app')

@section('title', 'My Account')

@section('content')
<h1 class="h4 mb-3">My Account</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dashboard-card p-3 text-center">
            <div class="h3 mb-0">{{ $orderCount }}</div>
            <div class="text-muted small">Orders Placed</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card p-3 text-center">
            <div class="h3 mb-0">{{ $reviewCount }}</div>
            <div class="text-muted small">Reviews Written</div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="{{ route('account.orders') }}" class="text-decoration-none">
            <div class="card dashboard-card p-3 text-center h-100 d-flex justify-content-center">
                <div class="fw-bold">View Order History &rarr;</div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card dashboard-card p-3 mb-3">
            <h2 class="h6">Profile Details</h2>
            <form method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label small">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Email</label>
                    <input type="email" class="form-control form-control-sm" value="{{ $user->email }}" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Phone</label>
                    <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Address</label>
                    <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address', $user->address) }}</textarea>
                </div>
                <button type="submit" class="btn btn-dark btn-sm">Save Changes</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card dashboard-card p-3 mb-3">
            <h2 class="h6">Saved Payment Cards</h2>
            <p class="small text-muted">Manage the payment cards saved to your account (masked for security &mdash; demo only).</p>
            <a href="{{ route('account.saved-cards') }}" class="btn btn-outline-dark btn-sm">Manage Saved Cards</a>
        </div>
        @include('profile._password')
    </div>
</div>
@endsection
