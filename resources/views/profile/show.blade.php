@extends($layout)

@section('title', 'My Profile')

@section('content')
<h1 class="h4 mb-3">My Profile</h1>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card dashboard-card p-3 h-100">
            <h2 class="h6">Profile Details</h2>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label small" for="profile-name">Full Name</label>
                    <input type="text" id="profile-name" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" maxlength="100" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small" for="profile-email">Email</label>
                    <input type="email" id="profile-email" class="form-control form-control-sm" value="{{ $user->email }}" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label small" for="profile-phone">Phone</label>
                    <input type="text" id="profile-phone" name="phone" class="form-control form-control-sm" value="{{ old('phone', $user->phone) }}" maxlength="30">
                </div>
                <div class="mb-3">
                    <label class="form-label small" for="profile-address">Address</label>
                    <textarea id="profile-address" name="address" class="form-control form-control-sm" rows="2" maxlength="255">{{ old('address', $user->address) }}</textarea>
                </div>
                <button type="submit" class="btn btn-dark btn-sm">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card dashboard-card p-3 mb-3">
            <h2 class="h6">Account</h2>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted fw-normal">Role</dt>
                <dd class="col-7 text-capitalize">{{ $user->role }}</dd>
                @if ($user->staffProfile)
                    <dt class="col-5 text-muted fw-normal">Position</dt>
                    <dd class="col-7">{{ $user->staffProfile->position ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Managed by</dt>
                    <dd class="col-7">{{ $user->staffProfile->manager?->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Hired</dt>
                    <dd class="col-7">{{ $user->staffProfile->hired_on?->format('d M Y') ?? '—' }}</dd>
                @endif
                <dt class="col-5 text-muted fw-normal">Member since</dt>
                <dd class="col-7 mb-0">{{ $user->created_at->format('d M Y') }}</dd>
            </dl>
        </div>

        @include('profile._password')
    </div>
</div>
@endsection
