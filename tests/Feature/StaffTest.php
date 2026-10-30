<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** UC_07 Inventory, UC_08 Order Processing, UC_09 Payment Confirmation. */
class StaffTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(int $stock = 5, int $qty = 2): array
    {
        $product = Product::create([
            'name' => 'Suede Classic',
            'price' => 98000,
            'brand_id' => Brand::create(['name' => 'Puma'])->id,
            'category_id' => Category::create(['name' => 'Lifestyle'])->id,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => Size::create(['label' => '42'])->id,
            'stock_quantity' => $stock,
        ]);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_address' => 'Yangon',
            'total_amount' => 98000 * $qty,
            'order_status' => 'pending',
        ]);
        $order->items()->create(['variant_id' => $variant->id, 'quantity' => $qty, 'unit_price' => 98000]);
        $payment = Payment::create(['order_id' => $order->id, 'payment_method' => 'cod', 'payment_status' => 'pending']);

        return compact('order', 'variant', 'payment');
    }

    public function test_staff_can_update_stock_quantity(): void
    {
        ['variant' => $variant] = $this->makeOrder();
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch("/staff/inventory/{$variant->id}", ['stock_quantity' => 20])
            ->assertRedirect(route('staff.inventory.index'));

        $this->assertSame(20, $variant->fresh()->stock_quantity);
    }

    public function test_cancelling_an_order_restocks_its_items(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->makeOrder(stock: 5, qty: 2);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    public function test_confirming_payment_advances_a_pending_order_to_confirmed(): void
    {
        ['order' => $order, 'payment' => $payment] = $this->makeOrder();
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch("/staff/payments/{$payment->id}", ['decision' => 'confirmed'])
            ->assertRedirect(route('staff.payments.index'));

        $this->assertSame('confirmed', $payment->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->order_status);
    }
}
