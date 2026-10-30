<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $orderCount = $user->orders()->count();
        $reviewCount = $user->reviews()->count();

        return view('account.dashboard', compact('user', 'orderCount', 'reviewCount'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('account.dashboard')->with('success', 'Profile updated.');
    }
}
