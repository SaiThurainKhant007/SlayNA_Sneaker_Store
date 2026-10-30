@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
<h1 class="h4 mb-3">Manage Staff &amp; Customers</h1>

<div class="card dashboard-card p-3 mb-3">
    <h2 class="h6">Staff &amp; Admin Accounts</h2>
    <div class="table-responsive">
        <table class="table table-sm align-middle text-nowrap js-datatable" data-empty="No staff or admin accounts.">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Position</th><th>Managed By</th><th>Status</th><th data-orderable="false" data-searchable="false">Actions</th></tr></thead>
            <tbody>
                @foreach ($staffAndAdmins as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $user->role }}</span></td>
                        <td>{{ $user->staffProfile?->position ?? '—' }}</td>
                        <td>{{ $user->staffProfile?->manager?->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">{{ $user->status }}</span></td>
                        <td>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.staff.toggle-status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $user->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card dashboard-card p-3 mb-3">
            <h2 class="h6">Customers</h2>
            <table class="table table-sm js-datatable" data-empty="No customers yet.">
                <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td data-order="{{ $customer->created_at->format('Y-m-d H:i:s') }}">{{ $customer->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card dashboard-card p-3 mb-3">
            <h2 class="h6">Add Staff / Admin Account</h2>
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Temporary Password</label>
                    <input type="password" name="password" class="form-control form-control-sm" minlength="8" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Position <span class="text-muted">(staff only, optional)</span></label>
                    <input type="text" name="position" class="form-control form-control-sm" maxlength="100" placeholder="e.g. Sales Associate">
                </div>
                <button type="submit" class="btn btn-dark btn-sm">Create Account</button>
            </form>
        </div>
    </div>
</div>
@include('partials.datatables')
@endsection
