<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Size;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Support\DropAlertList;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    private function orderFor(Customer $customer, int $qty, string $status = 'pending'): array
    {
        $variant = ProductSize::create([
            'product_id' => $this->product('Air Max 90', 200000)->id,
            'size_id' => Size::firstOrCreate(['label' => '42'])->id,
            'stock_quantity' => 3,
        ]);
        $order = Order::create(['customer_id' => $customer->id, 'shipping_address' => 'Yangon', 'total_amount' => 200000 * $qty, 'order_status' => $status]);
        $order->items()->create(['product_size_id' => $variant->id, 'quantity' => $qty, 'unit_price' => 200000]);

        return [$order, $variant];
    }

    // ---- Order tracking and cancelling ----

    public function test_customer_sees_a_status_timeline_for_their_own_order_only(): void
    {
        $customer = Customer::factory()->create();
        [$order] = $this->orderFor($customer, 1, 'shipped');

        $this->actingAs($customer)->get("/account/orders/{$order->id}")
            ->assertOk()
            ->assertSee('sl-steps', false)
            ->assertSee('aria-current="step"', false)
            ->assertDontSee('Cancel order');

        $other = Customer::factory()->create();
        $this->actingAs($other)->get("/account/orders/{$order->id}")->assertForbidden();
    }

    public function test_customer_can_cancel_a_pending_order_and_stock_returns(): void
    {
        $customer = Customer::factory()->create();
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
        $customer = Customer::factory()->create();
        [$confirmed, $variant] = $this->orderFor($customer, 1, 'confirmed');

        $this->actingAs($customer)->patch("/account/orders/{$confirmed->id}/cancel")->assertSessionHas('error');
        $this->assertSame('confirmed', $confirmed->fresh()->order_status);
        $this->assertSame(3, $variant->fresh()->stock_quantity);

        [$pending] = $this->orderFor($customer, 1);
        $other = Customer::factory()->create();
        $this->actingAs($other)->patch("/account/orders/{$pending->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $pending->fresh()->order_status);
    }

    public function test_staff_cancelling_twice_restocks_only_once(): void
    {
        $customer = Customer::factory()->create();
        $staff = Staff::factory()->create();
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
        $customer = Customer::factory()->create();
        [$order, $variant] = $this->orderFor($customer, 1, 'completed');
        $url = "/products/{$variant->product_id}/reviews";

        $this->actingAs($customer)->post($url, ['rating' => 5, 'comment' => 'Great'])->assertSessionHasNoErrors();
        $this->post($url, ['rating' => 1, 'comment' => 'Again'])->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_completed_order_page_has_a_review_form_until_the_product_is_reviewed(): void
    {
        $customer = Customer::factory()->create();
        [$order, $variant] = $this->orderFor($customer, 1, 'completed');
        $page = route('account.orders.show', $order);

        $this->actingAs($customer)->get($page)->assertOk()->assertSee('Rate your items')->assertSee('Submit Review');

        $this->from($page)->post("/products/{$variant->product_id}/reviews", ['rating' => 5])->assertRedirect($page);

        $this->get($page)->assertSee('you have reviewed this product')->assertDontSee('Submit Review');
    }

    public function test_only_completed_orders_show_the_review_form(): void
    {
        $customer = Customer::factory()->create();
        [$order] = $this->orderFor($customer, 1, 'shipped');

        $this->actingAs($customer)->get(route('account.orders.show', $order))
            ->assertOk()->assertDontSee('Rate your items')->assertDontSee('Submit Review');
    }

    // ---- Admin reports ----

    public function test_admin_can_download_reports_as_csv_with_formulas_neutralised(): void
    {
        $admin = Admin::factory()->create();
        $customer = Customer::factory()->create(['name' => '=HYPERLINK("http://evil")']);
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

    // ---- Footer: "Get the Drop" email signup ----

    public function test_anyone_can_sign_up_for_drop_alerts_from_the_footer(): void
    {
        Storage::fake('local');   // the list is a JSON file, not a database table
        $emails = fn () => app(DropAlertList::class)->all()->pluck('email')->all();

        $this->get('/about')->assertOk()
            ->assertSee('Get the Drop')
            ->assertSee('id="drop-email"', false);

        // Saved once, in lower case; signing up again is fine and says so
        $this->from('/about')->post('/subscribe', ['email' => '  Fan@Example.com '])
            ->assertRedirect('/about#drop-alerts')
            ->assertSessionHas('subscribed', "You're on the list! We'll email you when new pairs drop.");
        $this->from('/about')->post('/subscribe', ['email' => 'fan@example.com'])
            ->assertSessionHas('subscribed', "You're already on the list. We'll email you when new pairs drop.");
        $this->assertSame(['fan@example.com'], $emails());
        Storage::disk('local')->assertExists('drop-alerts.json');
        $this->assertFalse(Schema::hasTable('subscribers'));   // no extra table / class

        // The thank-you message replaces the form
        $this->withSession(['subscribed' => "You're on the list!"])->get('/about')
            ->assertSee("You're on the list!")
            ->assertDontSee('id="drop-email"', false);

        // Bad addresses are rejected with a message under the field
        $this->from('/about')->post('/subscribe', ['email' => 'not-an-email'])
            ->assertSessionHasErrorsIn('subscribe', ['email']);
        $this->assertSame(['fan@example.com'], $emails());
    }

    public function test_admin_sees_exports_and_removes_drop_alert_subscribers(): void
    {
        Storage::fake('local');
        app(DropAlertList::class)->add('=cmd@example.com');
        app(DropAlertList::class)->add('keep@example.com');

        $this->get('/admin/subscribers')->assertRedirect();   // not for guests

        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->get('/admin/subscribers')->assertOk()->assertSee('=cmd@example.com')->assertSee('Download CSV');

        $csv = $this->get('/admin/reports/subscribers.csv')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=cmd@example.com", $csv);   // spreadsheet formulas are neutralised

        $this->delete(route('admin.subscribers.destroy'), ['email' => '=cmd@example.com'])->assertRedirect(route('admin.subscribers.index'));
        $this->assertSame(['keep@example.com'], app(DropAlertList::class)->all()->pluck('email')->all());

        $this->actingAs(Staff::factory()->create(), 'staff');
        $this->get('/admin/subscribers')->assertForbidden();
    }

    public function test_only_admins_can_download_reports(): void
    {
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->get('/admin/reports/sales.csv')->assertForbidden();
    }

    // ---- Error pages ----

    public function test_missing_pages_show_the_branded_404_page(): void
    {
        $this->get('/products/999999')->assertNotFound()->assertSee('Page not found')->assertSee('Browse sneakers');
        $this->get('/no-such-page')->assertNotFound()->assertSee('SLAY');
    }

    public function test_home_page_shows_the_sneaker_showcase_for_listed_products_only(): void
    {
        $runner = $this->product('Runner', 1500000, Brand::create(['name' => 'Balenciaga']), Category::firstOrCreate(['name' => 'Lifestyle']));
        $this->product('9060', 210000)->update(['status' => 'discontinued']);
        ProductSize::create(['product_id' => $runner->id, 'size_id' => Size::firstOrCreate(['label' => '42'])->id, 'stock_quantity' => 10]);
        ProductSize::create(['product_id' => $runner->id, 'size_id' => Size::firstOrCreate(['label' => '38'])->id, 'stock_quantity' => 0]);

        $html = $this->get('/')->assertOk()
            ->assertSee('Spin the Wheel')
            ->assertSee('img/showcase/balenciaga-runner.png')
            ->assertSee(route('products.show', $runner))
            ->assertSee('data-orbit-item', false)
            ->assertSee('Spin for me')
            ->assertDontSee('img/showcase/new-balance-9060.png')   // discontinued product is skipped
            ->getContent();

        // Under the wheel: just the front sneaker's name and price (no rating, sizes or button)
        $caption = Str::between($html, 'data-orbit-caption', '</p>');
        $this->assertStringContainsString('Runner', $caption);
        $this->assertStringContainsString('1,500,000 MMK', $caption);
        $this->assertStringNotContainsString('review', $caption);
        $this->assertStringNotContainsString('View Product', $caption);
        $this->assertStringContainsString('data-name="Runner" data-price="1,500,000 MMK"', $html);

        // No listed products at all: the section is left out
        $runner->update(['status' => 'discontinued']);
        $this->get('/')->assertOk()->assertDontSee('Spin the Wheel');
    }
}
