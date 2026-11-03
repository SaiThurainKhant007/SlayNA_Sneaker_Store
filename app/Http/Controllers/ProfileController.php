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

        if ($user->isStaff()) {
            $user->load('manager');
        }

        return view('profile.show', compact('user', 'layout'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Address is a customer-only column; staff and admins keep name + phone
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
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
