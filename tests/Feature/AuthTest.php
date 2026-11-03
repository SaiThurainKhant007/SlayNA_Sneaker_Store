<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** UC_01 Customer Login Management. */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_as_a_customer(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        Admin::factory()->create(['email' => 'admin@test.com', 'password' => Hash::make('Password123!')]);
        Staff::factory()->create(['email' => 'staff@test.com', 'password' => Hash::make('Password123!')]);
        Customer::factory()->create(['email' => 'customer@test.com', 'password' => Hash::make('Password123!')]);

        $this->post('/login', ['email' => 'admin@test.com', 'password' => 'Password123!'])
            ->assertRedirect(route('admin.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['email' => 'staff@test.com', 'password' => 'Password123!'])
            ->assertRedirect(route('staff.dashboard'));
        $this->post('/logout');

        $this->post('/login', ['email' => 'customer@test.com', 'password' => 'Password123!'])
            ->assertRedirect(route('home'));
    }

    public function test_disabled_accounts_cannot_log_in(): void
    {
        Customer::factory()->create([
            'email' => 'disabled@test.com',
            'password' => Hash::make('Password123!'),
            'status' => 'disabled',
        ]);

        $this->post('/login', ['email' => 'disabled@test.com', 'password' => 'Password123!'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_cannot_access_admin_or_staff_areas(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/staff/dashboard')->assertForbidden();
    }
}
