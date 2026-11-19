<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Size;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC_03 Product Management, UC_05 Staff Management. */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_with_per_size_stock(): void
    {
        $admin = Admin::factory()->create();
        $brand = Brand::create(['name' => 'Nike']);
        $category = Category::create(['name' => 'Running']);
        $size = Size::create(['label' => '41']);

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Air Zoom Pegasus',
            'description' => 'Everyday trainer.',
            'price' => 145000,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'low_stock_threshold' => 5,
            'status' => 'active',
            'stock' => [$size->id => 12],
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Air Zoom Pegasus']);
        $this->assertDatabaseHas('product_sizes', ['size_id' => $size->id, 'stock_quantity' => 12]);
    }

    public function test_admin_can_create_a_staff_account_and_toggle_its_status(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'New Staffer',
            'email' => 'staffer@slayna.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ])->assertRedirect(route('admin.staff.index'));

        $newStaff = Staff::where('email', 'staffer@slayna.com')->firstOrFail();
        $this->assertSame('active', $newStaff->status);

        $this->actingAs($admin)->patch("/admin/staff/{$newStaff->id}/toggle-status")
            ->assertRedirect(route('admin.staff.index'));

        $this->assertSame('disabled', $newStaff->fresh()->status);
    }

    public function test_staff_cannot_access_admin_only_routes(): void
    {
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
    }

    public function test_admin_sidebar_has_customer_feedback_but_no_reviews_or_item_rating_pages(): void
    {
        $this->actingAs(Admin::factory()->create())->get(route('admin.dashboard'))
            ->assertSee('Customer Feedback')
            ->assertDontSee('Customer Reviews')
            ->assertDontSee('Item Rating');

        $this->get('/admin/reviews')->assertNotFound();
        $this->get('/admin/item-ratings')->assertNotFound();
    }

    public function test_customer_feedback_reaches_the_admin_and_stays_off_the_public_page(): void
    {
        $customer = Customer::factory()->create(['name' => 'Happy Runner', 'email' => 'runner@example.com']);

        $this->actingAs($customer)->get('/contact')->assertSee('Send Feedback');

        $this->post(route('contact.feedback'), ['rating' => 2, 'message' => '<b>Delivery was slow</b>'])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', ['customer_id' => $customer->id, 'rating' => 2, 'message' => '<b>Delivery was slow</b>']);
        $this->get('/contact')->assertDontSee('Delivery was slow');

        $this->actingAs(Admin::factory()->create())->get(route('admin.feedback.index'))
            ->assertOk()
            ->assertSee('Happy Runner')
            ->assertSee('runner@example.com')
            ->assertSee('2 out of 5')
            ->assertSee('&lt;b&gt;Delivery was slow&lt;/b&gt;', false)
            ->assertDontSee('<b>Delivery was slow</b>', false);
    }

    public function test_feedback_needs_a_rating_and_a_message(): void
    {
        $this->actingAs(Customer::factory()->create())->post(route('contact.feedback'), ['rating' => 6, 'message' => ''])
            ->assertSessionHasErrors(['rating', 'message']);

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_only_customers_can_send_feedback(): void
    {
        $url = route('contact.feedback');
        $data = ['rating' => 5, 'message' => 'Hello'];

        $this->get('/contact')->assertSee('to send us your feedback')->assertDontSee('Send Feedback');
        $this->post($url, $data)->assertRedirect(route('login'));
        $this->actingAs(Staff::factory()->create())->post($url, $data)->assertForbidden();

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_admin_can_delete_feedback_and_only_admins_can_see_or_delete_it(): void
    {
        $customer = Customer::factory()->create();
        $feedback = $customer->feedback()->create(['rating' => 1, 'message' => 'Unwanted']);
        $url = route('admin.feedback.destroy', $feedback);

        $this->get(route('admin.feedback.index'))->assertRedirect(route('login'));
        $this->delete($url)->assertRedirect(route('login'));
        $this->actingAs(Staff::factory()->create())->get(route('admin.feedback.index'))->assertForbidden();
        $this->actingAs($customer)->delete($url)->assertForbidden();
        $this->assertDatabaseHas('feedback', ['id' => $feedback->id]);

        $this->actingAs(Admin::factory()->create())->delete($url)->assertRedirect(route('admin.feedback.index'));
        $this->assertDatabaseMissing('feedback', ['id' => $feedback->id]);
    }

    public function test_each_dashboard_tile_can_be_filtered_by_its_own_start_and_end_date(): void
    {
        $this->travelTo('2026-11-12 10:00:00');
        $old = Customer::factory()->create(['created_at' => '2026-09-05 09:00:00']);
        Customer::factory()->count(2)->create(['created_at' => '2026-11-03 09:00:00']);

        $admin = Admin::factory()->create();

        // No dates: all-time total
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('kpis', fn ($kpis) => $kpis['customers']['total'] === 3 && $kpis['customers']['from'] === null);

        // Customers tile filtered to November only; the orders tile keeps its own (empty) range
        $this->get(route('admin.dashboard', ['customers' => ['from' => '2026-11-01', 'to' => '2026-11-12']]))
            ->assertOk()
            ->assertSee('Up from 0 in previous 12 days')
            ->assertViewHas('kpis', fn ($kpis) => $kpis['customers']['total'] === 2
                && $kpis['customers']['from'] === '2026-11-01'
                && $kpis['customers']['to'] === '2026-11-12'
                && $kpis['orders']['from'] === null);

        // Reversed dates are swapped; junk input is ignored
        $this->get(route('admin.dashboard', ['customers' => ['from' => '2026-09-30', 'to' => '2026-09-01']]))
            ->assertViewHas('kpis', fn ($kpis) => $kpis['customers']['total'] === 1);
        $this->get(route('admin.dashboard', ['customers' => 'nope', 'orders' => ['from' => 'bad']]))
            ->assertOk()
            ->assertViewHas('kpis', fn ($kpis) => $kpis['customers']['total'] === 3);
    }
}
