<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** My Profile and the account menus behind each profile icon. */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_menu_links_to_profile_and_page_uses_admin_panel(): void
    {
        $admin = Admin::factory()->create(['name' => 'Ada Admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('sl-admin-greeting', false)
            ->assertSeeText('Ada')
            ->assertSeeText(now()->format('l, j F Y'))
            ->assertSee('sl-admin-user-menu', false)
            ->assertSee(route('profile.show'), false);

        $this->actingAs($admin)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertDontSee('sl-admin-greeting', false)
            ->assertSee('My Profile')
            ->assertSee('ADMIN PANEL')
            ->assertSee('Ada Admin');
    }

    public function test_staff_profile_button_opens_a_menu_and_page_shows_manager(): void
    {
        $admin = Admin::factory()->create(['name' => 'Ada Admin']);
        $staff = Staff::factory()->create();
        $staff->update(['managed_by' => $admin->id, 'position' => 'Cashier', 'hired_on' => now()]);

        $this->actingAs($staff)->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('data-bs-toggle="dropdown"', false)
            ->assertSee(route('profile.show'), false);

        $this->actingAs($staff)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('STAFF')
            ->assertSee('Cashier')
            ->assertSee('Ada Admin');
    }

    public function test_storefront_person_icon_opens_a_menu_for_each_role(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer)->get(route('home'))
            ->assertOk()
            ->assertSee('sl-user-menu', false)
            ->assertSee(route('account.orders'), false);

        $staff = Staff::factory()->create();
        $this->actingAs($staff)->get(route('home'))
            ->assertOk()
            ->assertSee('Staff Dashboard')
            ->assertSee(route('profile.show'), false);

        $this->post(route('logout'));
        $this->get(route('home'))->assertOk()->assertSee(route('login'), false);
    }

    public function test_customer_profile_goes_to_my_account(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer)->get(route('profile.show'))->assertRedirect(route('account.dashboard'));
        $this->actingAs($customer)->get(route('account.dashboard'))->assertOk()->assertSee('Change Password');
    }

    public function test_profile_details_can_be_updated_but_not_role(): void
    {
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->put(route('profile.update'), [
            'name' => 'New Name', 'phone' => '0912345', 'address' => 'Mandalay', 'role' => 'admin',
        ])->assertRedirect();

        $staff->refresh();
        $this->assertSame('New Name', $staff->name);
        $this->assertSame('0912345', $staff->phone);
        $this->assertSame('staff', $staff->role);
    }

    public function test_password_change_needs_the_current_password(): void
    {
        $user = Admin::factory()->create(['password' => 'OldPassword1!']);

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrong', 'password' => 'NewPassword1!', 'password_confirmation' => 'NewPassword1!',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'OldPassword1!', 'password' => 'NewPassword1!', 'password_confirmation' => 'NewPassword1!',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
    }

    public function test_guests_cannot_open_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }
}
