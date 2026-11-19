<?php

namespace Tests\Feature;

use App\Models\Brand;
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
        $variant = ProductSize::create([
            'product_id' => $product->id,
            'size_id' => Size::create(['label' => '42'])->id,
            'stock_quantity' => $stock,
        ]);
        $customer = Customer::factory()->create();
        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_address' => 'Yangon',
            'total_amount' => 98000 * $qty,
            'order_status' => 'pending',
        ]);
        $order->items()->create(['product_size_id' => $variant->id, 'quantity' => $qty, 'unit_price' => 98000]);
        $payment = Payment::create(['order_id' => $order->id, 'payment_method_id' => PaymentMethod::idFor('cod'), 'payment_status' => 'pending']);

        return compact('order', 'variant', 'payment');
    }

    public function test_staff_can_update_stock_quantity(): void
    {
        ['variant' => $variant] = $this->makeOrder();
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->patch("/staff/inventory/{$variant->id}", ['stock_quantity' => 20])
            ->assertRedirect(route('staff.inventory.index'));

        $this->assertSame(20, $variant->fresh()->stock_quantity);
    }

    public function test_cancelling_an_order_restocks_its_items(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->makeOrder(stock: 5, qty: 2);
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    public function test_a_cancelled_order_cannot_be_reopened_so_stock_is_not_restocked_twice(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->makeOrder(stock: 5, qty: 2);
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled']);

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'processing'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('cancelled', $order->fresh()->order_status);

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}", ['order_status' => 'cancelled']);
        $this->assertSame(7, $variant->fresh()->stock_quantity);

        // The page offers no status form for it
        $this->actingAs($staff)->get(route('staff.orders.index'))
            ->assertOk()
            ->assertDontSee(route('staff.orders.update', $order), false)
            ->assertSee('Cannot be changed');
    }

    public function test_confirming_payment_advances_a_pending_order_to_confirmed(): void
    {
        ['order' => $order, 'payment' => $payment] = $this->makeOrder();
        $staff = Staff::factory()->create();

        $this->actingAs($staff)->patch("/staff/payments/{$payment->id}", ['decision' => 'confirmed'])
            ->assertRedirect(route('staff.payments.index'));

        $this->assertSame('confirmed', $payment->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->order_status);
    }

    public function test_a_reviewed_payment_cannot_be_decided_again(): void
    {
        ['order' => $order, 'payment' => $payment] = $this->makeOrder();
        $firstStaff = Staff::factory()->create();
        $url = "/staff/payments/{$payment->id}";

        $this->actingAs($firstStaff)->patch($url, ['decision' => 'rejected']);

        $this->actingAs(Staff::factory()->create())->patch($url, ['decision' => 'confirmed'])
            ->assertRedirect()
            ->assertSessionHas('error');

        // The first decision and who made it stay as they were
        $payment->refresh();
        $this->assertSame('rejected', $payment->payment_status);
        $this->assertSame($firstStaff->id, $payment->confirmed_by);
        $this->assertSame('pending', $order->fresh()->order_status);

        // The page offers no buttons for it
        $this->get(route('staff.payments.index'))
            ->assertOk()
            ->assertDontSee(route('staff.payments.update', $payment), false)
            ->assertSee('Reviewed');
    }
}
