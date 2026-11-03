<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Size;
use App\Models\Staff;
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
        $variant = ProductSize::create(['product_id' => $product->id, 'size_id' => Size::create(['label' => '42'])->id, 'stock_quantity' => 3]);
        $customer = Customer::factory()->create();
        $order = Order::create(['customer_id' => $customer->id, 'shipping_address' => 'Yangon', 'total_amount' => 98000, 'order_status' => 'pending']);
        $order->items()->create(['product_size_id' => $variant->id, 'quantity' => 1, 'unit_price' => 98000]);
        Payment::create(['order_id' => $order->id, 'payment_method_id' => PaymentMethod::idFor('cod')]);
        Cart::create(['customer_id' => $customer->id, 'product_size_id' => $variant->id, 'quantity' => 1]);

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
        $admin = Admin::factory()->create();
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
        $this->actingAs(Staff::factory()->create());

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
        $this->actingAs(Staff::factory()->create());
        Payment::query()->delete();
        $this->assertDataTablePage($this->get(route('staff.payments.index')), 1);
    }
}
