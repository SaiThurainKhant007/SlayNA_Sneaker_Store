<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\UseSignedInGuard;
use App\Support\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** UC_01 Customer Login Management (also used for Staff/Admin login). */
class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // One login form for all three account tables; emails are unique across them
        $user = Accounts::findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        if (! $user->isActive()) {
            return back()->withErrors(['email' => 'Your account has been disabled.'])->onlyInput('email');
        }

        // Only one account signed in at a time, whichever table it lives in
        foreach (UseSignedInGuard::GUARDS as $guard) {
            Auth::guard($guard)->logout();
        }
        Auth::guard($user->guardName())->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            default => redirect()->intended(route('home')),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        foreach (UseSignedInGuard::GUARDS as $guard) {
            Auth::guard($guard)->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
