<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** UC_05 Reports & Staff/Employee Management. */
class UserController extends Controller
{
    public function index(): View
    {
        $staffAndAdmins = User::with('staffProfile.manager')->whereIn('role', ['staff', 'admin'])->orderBy('role')->orderBy('name')->get();
        $customers = User::where('role', 'customer')->latest()->get();

        return view('admin.staff', compact('staffAndAdmins', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:staff,admin'],
            'position' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            // Admin manages Staff: every staff account gets a profile naming the admin who added it
            if ($user->isStaff()) {
                $user->staffProfile()->create([
                    'managed_by' => $request->user()->id,
                    'position' => $validated['position'] ?? null,
                    'hired_on' => now()->toDateString(),
                ]);
            }
        });

        return redirect()->route('admin.staff.index')->with('success', ucfirst($validated['role']).' account created.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id || $user->role === 'customer') {
            return redirect()->route('admin.staff.index');
        }

        $user->update(['status' => $user->status === 'active' ? 'disabled' : 'active']);

        return redirect()->route('admin.staff.index')->with('success', 'Account status updated.');
    }
}
