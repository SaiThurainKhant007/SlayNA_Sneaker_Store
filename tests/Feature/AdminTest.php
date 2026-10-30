<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC_03 Product Management, UC_05 Staff Management. */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_with_per_size_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
        $this->assertDatabaseHas('product_variants', ['size_id' => $size->id, 'stock_quantity' => 12]);
    }

    public function test_admin_can_create_a_staff_account_and_toggle_its_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/staff', [
            'name' => 'New Staffer',
            'email' => 'staffer@slayna.com',
            'password' => 'Password123!',
            'role' => 'staff',
        ])->assertRedirect(route('admin.staff.index'));

        $newStaff = User::where('email', 'staffer@slayna.com')->firstOrFail();
        $this->assertSame('active', $newStaff->status);

        $this->actingAs($admin)->patch("/admin/staff/{$newStaff->id}/toggle-status")
            ->assertRedirect(route('admin.staff.index'));

        $this->assertSame('disabled', $newStaff->fresh()->status);
    }

    public function test_staff_cannot_access_admin_only_routes(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
    }
}
