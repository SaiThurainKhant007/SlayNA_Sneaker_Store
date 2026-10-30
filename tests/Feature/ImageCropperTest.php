<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every image upload opens the cropper; the library loads once and only where needed. */
class ImageCropperTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT = 'cropperjs@1.6.2/dist/cropper.min.js';

    private function assertCropperPage($response, array $ratios): void
    {
        $html = $response->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, self::SCRIPT), 'cropper script loaded once');
        $this->assertSame(1, substr_count($html, 'id="slCropModal"'), 'one crop modal');
        preg_match_all('/<input type="file"[^>]*>/', $html, $inputs);
        $this->assertNotEmpty($inputs[0]);
        foreach ($inputs[0] as $input) {
            $this->assertStringContainsString('data-crop=', $input, 'every image input is croppable');
        }
        foreach ($ratios as $ratio) {
            $this->assertStringContainsString('data-crop="'.$ratio.'"', $html);
        }
    }

    public function test_admin_upload_forms_use_the_cropper(): void
    {
        $brand = Brand::create(['name' => 'Puma']);
        $product = Product::create(['name' => 'Suede', 'price' => 98000, 'brand_id' => $brand->id, 'category_id' => Category::create(['name' => 'Lifestyle'])->id]);
        Promotion::create(['title' => 'Sale', 'product_id' => $product->id, 'discount_percent' => 10, 'is_active' => true]);
        GalleryImage::create(['image_path' => 'gallery/x.jpg', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->assertCropperPage($this->get(route('admin.brands.index')), ['free']);
        $this->assertCropperPage($this->get(route('admin.gallery.index')), ['4/5']);
        $this->assertCropperPage($this->get(route('admin.products.create')), ['4/3']);
        $this->assertCropperPage($this->get(route('admin.products.edit', $product)), ['4/3']);
        $this->assertCropperPage($this->get(route('admin.promotions.index')), ['4/3']);

        // Pages without uploads don't load it
        $this->assertStringNotContainsString(self::SCRIPT, $this->get(route('admin.dashboard'))->getContent());
    }

    public function test_checkout_payment_screenshot_uses_the_cropper(): void
    {
        $product = Product::create(['name' => 'Suede', 'price' => 98000, 'brand_id' => Brand::create(['name' => 'Puma'])->id, 'category_id' => Category::create(['name' => 'Lifestyle'])->id]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'size_id' => Size::create(['label' => '42'])->id, 'stock_quantity' => 3]);
        $customer = User::factory()->create(['role' => 'customer']);
        CartItem::create(['customer_id' => $customer->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        $this->actingAs($customer);
        $this->assertCropperPage($this->get(route('checkout.show')), ['free']);
    }
}
