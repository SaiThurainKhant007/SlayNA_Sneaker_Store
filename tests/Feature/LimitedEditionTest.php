<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Manage Promotions: Type = Promotion (home Hot Deals card: brand + original price + discount)
 * or Limited Edition (its own name, brand, price, photo and sizes; sold only on the home page).
 */
class LimitedEditionTest extends TestCase
{
    use RefreshDatabase;

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('le.png', file_get_contents(public_path('img/no-image.png')));
    }

    public function test_limited_edition_is_created_without_a_product_and_sold_on_the_home_page(): void
    {
        Storage::fake('public');
        $s42 = Size::create(['label' => '42']);
        $s43 = Size::create(['label' => '43']);
        $jordan = Brand::create(['name' => 'Jordan']);
        $shoe = Product::create(['name' => 'Air Jordan 1 Mid', 'price' => 200000, 'brand_id' => $jordan->id, 'category_id' => Category::create(['name' => 'Basketball'])->id]);
        ProductSize::create(['product_id' => $shoe->id, 'size_id' => $s42->id, 'stock_quantity' => 5]);

        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->get(route('admin.promotions.index'))->assertOk()->assertSee('name="type"', false)->assertSee('Limited Edition');

        // Limited Edition: no product to pick, no discount, no end date; a photo is required
        $fields = [
            'type' => 'limited', 'title' => 'Air Jordan 4 Holiday', 'description' => '1 of 100',
            'brand_id' => $jordan->id, 'price' => 1500000, 'stock' => [$s42->id => 2, $s43->id => 0], 'is_active' => 1,
            'discount_percent' => 40, 'ends_on' => '2026-12-31',   // ignored
        ];
        $this->post(route('admin.promotions.store'), $fields)->assertSessionHasErrors('image');
        $this->post(route('admin.promotions.store'), $fields + ['image' => $this->photo()])->assertRedirect(route('admin.promotions.index'));

        $limited = Promotion::where('type', 'limited')->firstOrFail();
        $this->assertSame(0, $limited->discount_percent);
        $this->assertNull($limited->ends_on);
        $hidden = $limited->product;
        $this->assertSame('limited', $hidden->status);
        $this->assertSame('Air Jordan 4 Holiday', $hidden->name);
        $this->assertSame(1500000.0, $hidden->effectivePrice());

        // A normal Promotion is a home Hot Deals card — brand + price + discount, no shop product link
        $this->post(route('admin.promotions.store'), [
            'type' => 'promotion', 'title' => 'Big Sale', 'brand_id' => $jordan->id, 'is_active' => 1,
        ])->assertSessionHasErrors(['discount_percent', 'original_price', 'image']);
        $this->post(route('admin.promotions.store'), [
            'type' => 'promotion', 'title' => 'Big Sale', 'brand_id' => $jordan->id,
            'original_price' => 200000, 'discount_percent' => 25, 'is_active' => 1, 'image' => $this->photo(),
        ])->assertRedirect(route('admin.promotions.index'));
        $deal = Promotion::where('type', 'promotion')->firstOrFail();
        $this->assertNull($deal->product_id);
        $this->assertSame(150000.0, $deal->discountedPrice());
        $this->assertSame(200000.0, $shoe->fresh()->effectivePrice()); // shop price unchanged

        // Edit: price and stock change, the type stays Limited Edition
        $this->put(route('admin.promotions.update', $limited), ['type' => 'promotion', 'title' => 'Air Jordan 4 Holiday', 'brand_id' => $jordan->id, 'price' => 1400000, 'stock' => [$s42->id => 3, $s43->id => 1], 'is_active' => 1])
            ->assertRedirect(route('admin.promotions.index'));
        $this->assertTrue($limited->fresh()->isLimited());
        $this->assertSame(1400000.0, (float) $hidden->fresh()->price);
        $this->assertSame(3, $hidden->variants()->where('size_id', $s42->id)->value('stock_quantity'));

        // Not in Admin > Products, the shop, or search; no product page (goes to the home section)
        $this->get(route('admin.products.index'))->assertDontSee('Air Jordan 4 Holiday');
        $this->get(route('admin.products.edit', $hidden))->assertNotFound();
        auth('admin')->logout();
        $customer = Customer::factory()->create();
        $this->actingAs($customer);
        $this->get('/products')->assertDontSee('Air Jordan 4 Holiday');
        $this->get('/products?q=Holiday')->assertDontSee('Air Jordan 4 Holiday');
        $this->get(route('products.show', $hidden))->assertRedirect(route('home').'#limited-title');

        // Home: Hot Deals labelled Promotion; Limited Edition below it with its own size buttons and Add to Cart
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<span class="sl-type-pill">Promotion</span>', $home);
        $this->assertLessThan(strpos($home, 'id="limited-title"'), strpos($home, 'id="promo-title"'));
        $section = \Illuminate\Support\Str::after($home, 'id="limited-title"');
        $this->assertStringContainsString('Air Jordan 4 Holiday', $section);
        $this->assertStringContainsString('1,400,000 MMK', $section);
        $this->assertStringContainsString('data-limited-buy', $section);
        $this->assertStringContainsString('value="'.$hidden->variants()->where('size_id', $s42->id)->value('id').'"', $section);
        $this->assertStringNotContainsString(route('products.show', $hidden), \Illuminate\Support\Str::before($section, '</section>'));

        // Buying from the home page: cart and checkout as usual, at full price
        $variant = $hidden->variants()->where('size_id', $s42->id)->first();
        $this->post('/cart', ['product_size_id' => $variant->id, 'quantity' => 1])->assertRedirect(route('cart.index'));
        $this->post('/checkout', ['shipping_address' => 'Yangon', 'payment_method' => 'saved_card'])->assertRedirect();
        $this->assertDatabaseHas('order_items', ['product_size_id' => $variant->id, 'unit_price' => 1400000, 'promotion_id' => null]);
        $this->assertSame(2, $variant->fresh()->stock_quantity);

        // Deleting it after a sale keeps the hidden product for the order history, but stops selling it
        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->delete(route('admin.promotions.destroy', $limited))->assertRedirect();
        $this->assertSame('discontinued', $hidden->fresh()->status);
    }
}
