<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** My Profile for every signed-in user: details + password, shown inside their own panel. */
class ProfileController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Customers already edit their details on My Account
        if ($user->isCustomer()) {
            return redirect()->route('account.dashboard');
        }

        $layout = $user->isAdmin() ? 'layouts.admin' : 'layouts.staff';
        $user->load('staffProfile.manager');

        return view('profile.show', compact('user', 'layout'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'Password changed.');
    }
}
