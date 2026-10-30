<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Order tracking & self-cancel, shop pagination & price filter, related products, one review each, CSV reports, SEO tags, error pages. */
class StoreImprovementsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, int $price, ?Brand $brand = null, ?Category $category = null): Product
    {
        return Product::create([
            'name' => $name,
            'price' => $price,
            'brand_id' => ($brand ?? Brand::firstOrCreate(['name' => 'Nike']))->id,
            'category_id' => ($category ?? Category::firstOrCreate(['name' => 'Running']))->id,
        ]);
    }

    private function orderFor(User $customer, int $qty, string $status = 'pending'): array
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product('Air Max 90', 200000)->id,
            'size_id' => Size::firstOrCreate(['label' => '42'])->id,
            'stock_quantity' => 3,
        ]);
        $order = Order::create(['customer_id' => $customer->id, 'shipping_address' => 'Yangon', 'total_amount' => 200000 * $qty, 'order_status' => $status]);
        $order->items()->create(['variant_id' => $variant->id, 'quantity' => $qty, 'unit_price' => 200000]);

        return [$order, $variant];
    }

    // ---- Order tracking and cancelling ----

    public function test_customer_sees_a_status_timeline_for_their_own_order_only(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order] = $this->orderFor($customer, 1, 'shipped');

        $this->actingAs($customer)->get("/account/orders/{$order->id}")
            ->assertOk()
            ->assertSee('sl-steps', false)
            ->assertSee('aria-current="step"', false)
            ->assertDontSee('Cancel order');

        $other = User::factory()->create(['role' => 'customer']);
        $this->actingAs($other)->get("/account/orders/{$order->id}")->assertForbidden();
    }

    public function test_customer_can_cancel_a_pending_order_and_stock_returns(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order, $variant] = $this->orderFor($customer, 2);

        $this->actingAs($customer)->get("/account/orders/{$order->id}")->assertSee('Cancel order');
        $this->patch("/account/orders/{$order->id}/cancel")->assertRedirect(route('account.orders.show', $order));

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame(5, $variant->fresh()->stock_quantity);

        // Cancelling again must not add the stock back a second time
        $this->patch("/account/orders/{$order->id}/cancel");
        $this->assertSame(5, $variant->fresh()->stock_quantity);
    }

    public function test_confirmed_orders_cannot_be_cancelled_by_the_customer_or_someone_else(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$confirmed, $variant] = $this->orderFor($customer, 1, 'confirmed');

        $this->actingAs($customer)->patch("/account/orders/{$confirmed->id}/cancel")->assertSessionHas('error');
        $this->assertSame('confirmed', $confirmed->fresh()->order_status);
        $this->assertSame(3, $variant->fresh()->stock_quantity);

        [$pending] = $this->orderFor($customer, 1);
        $other = User::factory()->create(['role' => 'customer']);
        $this->actingAs($other)->patch("/account/orders/{$pending->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $pending->fresh()->order_status);
    }

    public function test_staff_cancelling_twice_restocks_only_once(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $staff = User::factory()->create(['role' => 'staff']);
        [$order, $variant] = $this->orderFor($customer, 2, 'confirmed');

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled']);
        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled']);

        $this->assertSame(5, $variant->fresh()->stock_quantity);
        $this->assertSame($staff->id, $order->fresh()->processed_by);
    }

    // ---- Shop page ----

    public function test_shop_is_paginated_and_keeps_filters_in_page_links(): void
    {
        foreach (range(1, 14) as $i) {
            $this->product(sprintf('Runner %02d', $i), 100000);
        }

        $this->get('/products?sort=name')->assertOk()->assertSee('14 product(s) found')
            ->assertSee('Runner 12')->assertDontSee('Runner 13')
            ->assertSee('sort=name&amp;page=2', false);
        $this->get('/products?sort=name&page=2')->assertOk()->assertSee('Runner 14')->assertDontSee('Runner 01');
    }

    public function test_shop_filters_by_price_range(): void
    {
        $this->product('Budget Sneaker', 80000);
        $this->product('Mid Sneaker', 150000);
        $this->product('Premium Sneaker', 400000);

        $this->get('/products?min_price=100000&max_price=200000')->assertOk()
            ->assertSee('Mid Sneaker')->assertDontSee('Budget Sneaker')->assertDontSee('Premium Sneaker');
        $this->get('/products?min_price=abc')->assertOk()->assertSee('Budget Sneaker');
    }

    // ---- Product page ----

    public function test_product_page_shows_related_products_and_link_preview_tags(): void
    {
        $nike = Brand::create(['name' => 'Nike']);
        $running = Category::create(['name' => 'Running']);
        $other = Category::create(['name' => 'Basketball']);
        $product = $this->product('Pegasus 41', 145000, $nike, $running);
        $this->product('Vomero 18', 160000, Brand::create(['name' => 'Asics']), $running);   // same category
        $this->product('Dunk Low', 120000, $nike, $other);                                    // same brand
        $this->product('Gel Lyte', 110000, Brand::create(['name' => 'Puma']), $other);        // unrelated

        $this->get("/products/{$product->id}")->assertOk()
            ->assertSee('You May Also Like')
            ->assertSee('Vomero 18')->assertSee('Dunk Low')->assertDontSee('Gel Lyte')
            ->assertSee('<meta property="og:type" content="product">', false)
            ->assertSee('<meta property="og:image" content="'.$product->imageUrl().'">', false)
            ->assertSee('Nike Pegasus 41', false);
    }

    public function test_a_customer_can_review_a_product_only_once(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order, $variant] = $this->orderFor($customer, 1, 'completed');
        $url = "/products/{$variant->product_id}/reviews";

        $this->actingAs($customer)->post($url, ['rating' => 5, 'comment' => 'Great'])->assertSessionHasNoErrors();
        $this->post($url, ['rating' => 1, 'comment' => 'Again'])->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);
        $this->get("/products/{$variant->product_id}")->assertSee('you have reviewed this product')->assertDontSee('Submit Review');
    }

    // ---- Admin reports ----

    public function test_admin_can_download_reports_as_csv_with_formulas_neutralised(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer', 'name' => '=HYPERLINK("http://evil")']);
        $this->orderFor($customer, 1, 'completed');

        $csv = $this->actingAs($admin)->get('/admin/reports/customers.csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBFCustomer,Email,Orders,\"Total Spent (MMK)\"", $csv);
        $this->assertStringContainsString("\"'=HYPERLINK(\"\"http://evil\"\")\"", $csv);

        foreach (['sales', 'top-products', 'inventory'] as $report) {
            $this->get("/admin/reports/{$report}.csv")->assertOk();
        }
        $this->get('/admin/reports/passwords.csv')->assertNotFound();
        $this->get('/admin/reports')->assertSee('Download CSV');
    }

    public function test_only_admins_can_download_reports(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get('/admin/reports/sales.csv')->assertForbidden();
    }

    // ---- Error pages ----

    public function test_missing_pages_show_the_branded_404_page(): void
    {
        $this->get('/products/999999')->assertNotFound()->assertSee('Page not found')->assertSee('Browse sneakers');
        $this->get('/no-such-page')->assertNotFound()->assertSee('SLAY');
    }
}
