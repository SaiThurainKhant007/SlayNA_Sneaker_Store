<div class="card dashboard-card p-3">
    <h2 class="h6">Change Password</h2>
    <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label class="form-label small" for="current-password">Current Password</label>
            <input type="password" id="current-password" name="current_password" class="form-control form-control-sm" autocomplete="current-password" required>
        </div>
        <div class="mb-2">
            <label class="form-label small" for="new-password">New Password</label>
            <input type="password" id="new-password" name="password" class="form-control form-control-sm" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
            <label class="form-label small" for="new-password-confirm">Confirm New Password</label>
            <input type="password" id="new-password-confirm" name="password_confirmation" class="form-control form-control-sm" minlength="8" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-outline-dark btn-sm">Change Password</button>
    </form>
</div>
