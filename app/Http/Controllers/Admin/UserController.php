<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Staff;
use App\Support\Accounts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** UC_05 Reports & Staff/Employee Management. */
class UserController extends Controller
{
    public function index(): View
    {
        $admins = Admin::orderBy('name')->get();
        $staff = Staff::with('manager')->orderBy('name')->get();
        $customers = Customer::latest()->get();

        return view('admin.staff', compact('admins', 'staff', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Accounts::uniqueEmail()],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:staff,admin'],
            'position' => ['nullable', 'string', 'max:100'],
        ]);

        $account = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // Admin manages Staff: every staff account records the admin who added it
        if ($validated['role'] === 'staff') {
            Staff::create($account + [
                'managed_by' => $request->user()->id,
                'position' => $validated['position'] ?? null,
                'hired_on' => now()->toDateString(),
            ]);
        } else {
            Admin::create($account);
        }

        return redirect()->route('admin.staff.index')->with('success', ucfirst($validated['role']).' account created.');
    }

    public function toggleStaff(Staff $staff): RedirectResponse
    {
        return $this->toggle($staff);
    }

    public function toggleAdmin(Request $request, Admin $admin): RedirectResponse
    {
        // An admin cannot lock themselves out
        if ($admin->is($request->user())) {
            return redirect()->route('admin.staff.index');
        }

        return $this->toggle($admin);
    }

    private function toggle(Model $account): RedirectResponse
    {
        $account->update(['status' => $account->status === 'active' ? 'disabled' : 'active']);

        return redirect()->route('admin.staff.index')->with('success', 'Account status updated.');
    }
}
