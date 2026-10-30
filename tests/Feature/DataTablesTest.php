<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every list page renders its table as a DataTable, with assets loaded once. */
class DataTablesTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT = 'datatables.net@2.1.8/js/dataTables.min.js';

    private function seedShop(): array
    {
        $product = Product::create([
            'name' => 'Suede Classic', 'price' => 98000,
            'brand_id' => Brand::create(['name' => 'Puma'])->id,
            'category_id' => Category::create(['name' => 'Lifestyle'])->id,
        ]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'size_id' => Size::create(['label' => '42'])->id, 'stock_quantity' => 3]);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create(['customer_id' => $customer->id, 'shipping_address' => 'Yangon', 'total_amount' => 98000, 'order_status' => 'pending']);
        $order->items()->create(['variant_id' => $variant->id, 'quantity' => 1, 'unit_price' => 98000]);
        Payment::create(['order_id' => $order->id, 'payment_method' => 'cod']);
        CartItem::create(['customer_id' => $customer->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        return compact('customer');
    }

    private function assertDataTablePage($response, int $tables): void
    {
        $html = $response->assertOk()->getContent();
        $this->assertSame($tables, substr_count($html, 'js-datatable"'), 'number of DataTables');
        $this->assertSame(1, substr_count($html, self::SCRIPT), 'DataTables script loaded once');
        $this->assertStringNotContainsString('colspan=', $html, 'placeholder rows break DataTables');
    }

    public function test_admin_tables_are_datatables(): void
    {
        $this->seedShop();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->assertDataTablePage($this->get(route('admin.dashboard')), 1);
        $this->assertDataTablePage($this->get(route('admin.products.index')), 1);
        $this->assertDataTablePage($this->get(route('admin.categories.index')), 1);
        $this->assertDataTablePage($this->get(route('admin.staff.index')), 2);
        $this->assertDataTablePage($this->get(route('admin.reports.index', ['type' => 'sales'])), 2);
        $this->assertDataTablePage($this->get(route('admin.reports.index', ['type' => 'inventory'])), 1);
        $this->assertDataTablePage($this->get(route('admin.reports.index', ['type' => 'customers'])), 1);
    }

    public function test_staff_tables_are_datatables(): void
    {
        $this->seedShop();
        $this->actingAs(User::factory()->create(['role' => 'staff']));

        $this->assertDataTablePage($this->get(route('staff.orders.index')), 1);
        $this->assertDataTablePage($this->get(route('staff.payments.index')), 1);
        $this->assertDataTablePage($this->get(route('staff.inventory.index')), 1);
    }

    public function test_cart_is_a_datatable_and_empty_pages_still_render(): void
    {
        ['customer' => $customer] = $this->seedShop();
        $this->actingAs($customer);
        $this->assertDataTablePage($this->get(route('cart.index')), 1);

        // Pages without tables don't load the library
        $this->assertStringNotContainsString(self::SCRIPT, $this->get(route('home'))->getContent());

        // Empty lists render without placeholder rows
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        Payment::query()->delete();
        $this->assertDataTablePage($this->get(route('staff.payments.index')), 1);
    }
}
