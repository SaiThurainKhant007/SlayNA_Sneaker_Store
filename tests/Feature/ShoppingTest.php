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

/** UC_04 Product Browsing, cart/checkout, UC_02 Review & Rating Management. */
class ShoppingTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(int $stock = 10): ProductVariant
    {
        $product = Product::create([
            'name' => 'Air Zoom Pegasus',
            'price' => 145000,
            'brand_id' => Brand::create(['name' => 'Nike'])->id,
            'category_id' => Category::create(['name' => 'Running'])->id,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => Size::create(['label' => '41'])->id,
            'stock_quantity' => $stock,
        ]);
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
        $customer = User::factory()->create(['role' => 'customer', 'address' => 'Yangon']);

        $this->actingAs($customer)->post('/cart', ['variant_id' => $variant->id, 'quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->actingAs($customer)->post('/checkout', [
            'shipping_address' => 'No. 1, Test Street',
            'payment_method' => 'cod',
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
        $customer = User::factory()->create(['role' => 'customer']);

        $customer->cartItems()->create(['variant_id' => $variant->id, 'quantity' => 1]);
        // Simulate stock disappearing after it was added to the cart (e.g. another customer bought it).
        $variant->update(['stock_quantity' => 0]);

        $this->actingAs($customer)->post('/checkout', [
            'shipping_address' => 'No. 1, Test Street',
            'payment_method' => 'cod',
        ])->assertSessionHasErrors('checkout');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_only_review_a_product_from_a_completed_order(): void
    {
        $variant = $this->makeVariant();
        $customer = User::factory()->create(['role' => 'customer']);

        // No order yet — review must be rejected.
        $this->actingAs($customer)->post("/products/{$variant->product_id}/reviews", [
            'rating' => 5,
            'comment' => 'Too soon!',
        ])->assertSessionHasErrors('review');

        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_address' => 'Yangon',
            'total_amount' => 145000,
            'order_status' => 'completed',
        ]);
        $order->items()->create(['variant_id' => $variant->id, 'quantity' => 1, 'unit_price' => 145000]);

        $this->actingAs($customer)->post("/products/{$variant->product_id}/reviews", [
            'rating' => 5,
            'comment' => 'Great fit!',
        ])->assertRedirect(route('products.show', $variant->product_id));

        $this->assertDatabaseHas('reviews', ['product_id' => $variant->product_id, 'customer_id' => $customer->id]);
    }
}
