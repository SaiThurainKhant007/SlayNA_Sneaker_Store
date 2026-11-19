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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC_04 Product Browsing, cart/checkout, UC_02 Review & Rating Management. */
class ShoppingTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(int $stock = 10): ProductSize
    {
        $product = Product::create([
            'name' => 'Air Zoom Pegasus',
            'price' => 145000,
            'brand_id' => Brand::create(['name' => 'Nike'])->id,
            'category_id' => Category::create(['name' => 'Running'])->id,
        ]);

        return ProductSize::create([
            'product_id' => $product->id,
            'size_id' => Size::create(['label' => '41'])->id,
            'stock_quantity' => $stock,
        ]);
    }

    public function test_spinning_logo_badge_is_only_on_the_home_page(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('class="sl-spin-badge"', false)
            ->assertSee('SLAYNA ★ SNEAKER STORE ★')
            ->assertSee('sl-mark-badge', false);

        foreach (['/products', '/contact', '/about', '/brands'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('sl-spin-badge', false);
        }
    }

    public function test_footer_links_to_the_five_social_media_pages(): void
    {
        config(['shop.social' => [
            ['name' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/slayna-test'],
            ['name' => 'Instagram', 'icon' => 'instagram', 'url' => 'https://www.instagram.com/slayna-test'],
            ['name' => 'TikTok', 'icon' => 'tiktok', 'url' => 'https://www.tiktok.com/@slayna-test'],
            ['name' => 'WeChat', 'icon' => 'wechat', 'url' => 'https://www.wechat.com/slayna-test'],
            ['name' => 'Telegram', 'icon' => 'telegram', 'url' => 'https://t.me/slayna-test'],
        ]]);

        foreach (['/', '/contact'] as $url) {
            $response = $this->get($url)->assertOk();
            foreach (['facebook', 'instagram', 'tiktok', 'wechat', 'telegram'] as $icon) {
                $response->assertSee('bi bi-'.$icon, false);
            }
            $response->assertSee('href="https://t.me/slayna-test" target="_blank" rel="noopener"', false)
                ->assertSee('aria-label="SlayNa on WeChat (opens in a new tab)"', false);
        }
    }

    public function test_customer_can_browse_and_search_products(): void
    {
        $this->makeVariant();

        $this->get('/products')->assertOk()->assertSee('Air Zoom Pegasus');
        $this->get('/products?q=Pegasus')->assertOk()->assertSee('Air Zoom Pegasus');
        $this->get('/products?q=Nonexistent')->assertOk()->assertDontSee('Air Zoom Pegasus');
    }

    public function test_checkout_decrements_stock_and_creates_a_pending_order(): void
    {
        $variant = $this->makeVariant(stock: 5);
        $customer = Customer::factory()->create(['address' => 'Yangon']);

        $this->actingAs($customer)->post('/cart', ['product_size_id' => $variant->id, 'quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->actingAs($customer)->post('/checkout', [
            'shipping_address' => 'No. 1, Test Street',
            'payment_method' => 'saved_card',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'total_amount' => 290000,
            'order_status' => 'pending',
        ]);
        $this->assertSame(3, $variant->fresh()->stock_quantity);
        $this->assertSame(0, $customer->cartItems()->count());
    }

    public function test_checkout_rejects_orders_that_exceed_available_stock(): void
    {
        $variant = $this->makeVariant(stock: 1);
        $customer = Customer::factory()->create();

        $customer->cartItems()->create(['product_size_id' => $variant->id, 'quantity' => 1]);
        // Simulate stock disappearing after it was added to the cart (e.g. another customer bought it).
        $variant->update(['stock_quantity' => 0]);

        $this->actingAs($customer)->post('/checkout', [
            'shipping_address' => 'No. 1, Test Street',
            'payment_method' => 'saved_card',
        ])->assertSessionHasErrors('checkout');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_rate_a_product_without_buying_it_first(): void
    {
        $variant = $this->makeVariant();
        $customer = Customer::factory()->create();

        // Stars alone are enough; the written review is optional.
        $this->actingAs($customer)->post("/products/{$variant->product_id}/reviews", ['rating' => 4])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $variant->product_id,
            'customer_id' => $customer->id,
            'rating' => 4,
            'order_id' => null,
        ]);
    }

    public function test_product_card_stars_let_customers_rate_with_one_click(): void
    {
        $variant = $this->makeVariant();
        $name = $variant->product->name;

        // Guests see stars that send them to log in
        $this->get('/products')->assertOk()->assertSee('Log in to rate '.$name);

        // A customer gets one submit button per star, posting to the review route
        $customer = Customer::factory()->create();
        $this->actingAs($customer)->get('/products')
            ->assertSee(route('products.reviews.store', $variant->product_id))
            ->assertSee('Rate '.$name.' 5 out of 5');

        $this->post(route('products.reviews.store', $variant->product_id), ['rating' => 5])
            ->assertSessionHas('success');

        // Once rated, the card shows their rating instead of the buttons
        $this->get('/products')
            ->assertSee('You rated 5★')
            ->assertDontSee('Rate '.$name.' 5 out of 5');
    }

    public function test_review_is_linked_to_the_customers_delivered_order_when_there_is_one(): void
    {
        $variant = $this->makeVariant();
        $customer = Customer::factory()->create();
        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_address' => 'Yangon',
            'total_amount' => 145000,
            'order_status' => 'completed',
        ]);
        $order->items()->create(['product_size_id' => $variant->id, 'quantity' => 1, 'unit_price' => 145000]);

        $this->actingAs($customer)->from(route('account.orders.show', $order))
            ->post("/products/{$variant->product_id}/reviews", ['rating' => 5, 'comment' => 'Great fit!'])
            ->assertRedirect(route('account.orders.show', $order));

        $this->assertDatabaseHas('reviews', ['product_id' => $variant->product_id, 'customer_id' => $customer->id, 'order_id' => $order->id]);
    }

    public function test_review_rating_must_be_one_to_five(): void
    {
        $variant = $this->makeVariant();

        $this->actingAs(Customer::factory()->create())->post("/products/{$variant->product_id}/reviews", ['rating' => 6])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_product_page_shows_no_stars_or_reviews_but_shop_cards_keep_the_stars(): void
    {
        $variant = $this->makeVariant();
        $customer = Customer::factory()->create(['name' => 'Happy Runner']);
        $variant->product->reviews()->create(['customer_id' => $customer->id, 'rating' => 4, 'comment' => 'Secret written review']);
        $url = route('products.show', $variant->product_id);

        foreach ([null, Customer::factory()->create()] as $viewer) {
            if ($viewer) {
                $this->actingAs($viewer);
            }

            $this->get($url)->assertOk()
                ->assertDontSee('★★★★☆')
                ->assertDontSee('(1 review)')
                ->assertDontSee('Submit Review')
                ->assertDontSee('Secret written review')
                ->assertDontSee('Happy Runner');
        }

        // Shop cards keep the stars (now clickable for this customer, 4 lit for the 4-star average)
        $this->get('/products')
            ->assertSee('(1 review)')
            ->assertSee('Rate '.$variant->product->name.' 4 out of 5')
            ->assertSee('class="is-on"', false)
            ->assertDontSee('Secret written review');
    }

    public function test_cart_page_has_no_review_form(): void
    {
        $variant = $this->makeVariant();
        $customer = Customer::factory()->create();

        $this->actingAs($customer)->post('/cart', ['product_size_id' => $variant->id, 'quantity' => 1])
            ->assertRedirect(route('cart.index'));

        $this->get('/cart')->assertOk()
            ->assertSee('Air Zoom Pegasus')
            ->assertDontSee('Customer Reviews')
            ->assertDontSee('Submit Review');
    }

    public function test_contact_page_shows_four_latest_reviews_with_only_rating_and_text(): void
    {
        $variant = $this->makeVariant();
        $customer = Customer::factory()->create(['name' => 'Hidden Reviewer']);

        foreach (range(1, 5) as $n) {
            $variant->product->reviews()->create(['customer_id' => $customer->id, 'rating' => 5, 'comment' => "Review text {$n}"]);
        }
        $variant->product->reviews()->create(['customer_id' => $customer->id, 'rating' => 1, 'comment' => null]);

        $this->get('/contact')
            ->assertSee(['Review text 5', 'Review text 4', 'Review text 3', 'Review text 2', '5 out of 5'])
            ->assertDontSee('Review text 1')
            ->assertDontSee('1 out of 5')
            ->assertDontSee('Hidden Reviewer')
            ->assertDontSee('Air Zoom Pegasus');
    }

    public function test_contact_page_hides_the_reviews_section_when_there_are_none(): void
    {
        $this->get('/contact')->assertOk()->assertDontSee('What our customers say');
    }

}
