<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** One Photos field: pick one or more images, one of them is the main photo. */
class ProductPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function photo(string $name = 'shoe.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, file_get_contents(public_path('img/no-image.png')));
    }

    private function fields(array $extra = []): array
    {
        return array_merge([
            'name' => 'Air Jordan 4', 'price' => 280000,
            'brand_id' => Brand::firstOrCreate(['name' => 'Nike'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Basketball'])->id,
            'status' => 'active',
        ], $extra);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Size::create(['label' => '42']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_first_chosen_photo_is_main_and_the_rest_are_extras(): void
    {
        $this->post(route('admin.products.store'), $this->fields([
            'photos' => [$this->photo('a.png'), $this->photo('b.png'), $this->photo('c.png')],
        ]))->assertRedirect(route('admin.products.index'));

        $product = Product::with('images')->firstOrFail();
        Storage::disk('public')->assertExists($product->image_path);
        $this->assertCount(2, $product->images);
        $this->assertCount(3, $product->galleryUrls());

        $html = $this->get(route('products.show', $product))->assertOk()->getContent();
        $this->assertSame(3, substr_count($html, 'data-pdp-thumb='));
        $this->assertStringContainsString('1 / 3', $html);
    }

    public function test_any_chosen_photo_can_be_picked_as_main(): void
    {
        $this->post(route('admin.products.store'), $this->fields([
            'photos' => [$this->photo('a.png'), $this->photo('b.png')],
            'main_photo' => 'new:1',
        ]));

        $product = Product::with('images')->firstOrFail();
        $this->assertCount(1, $product->images);
        $this->assertNotSame($product->images->first()->image_path, $product->image_path);

        // Picking an index that doesn't exist falls back to the first photo
        $this->post(route('admin.products.store'), $this->fields(['name' => 'Second', 'photos' => [$this->photo()], 'main_photo' => 'new:7']))
            ->assertRedirect(route('admin.products.index'));
        $second = Product::where('name', 'Second')->firstOrFail();
        $this->assertStringStartsWith('products/', $second->image_path);
        $this->assertSame(0, $second->images()->count());
    }

    public function test_edit_adds_photos_removes_ticked_ones_and_switches_main(): void
    {
        $this->post(route('admin.products.store'), $this->fields(['photos' => [$this->photo(), $this->photo(), $this->photo()]]));
        $product = Product::with('images')->firstOrFail();
        $oldMain = $product->image_path;
        [$keep, $drop] = $product->images->all();

        $this->put(route('admin.products.update', $product), $this->fields([
            'remove_photos' => [$drop->id],
            'main_photo' => 'existing:'.$keep->id,
            'photos' => [$this->photo('new.png')],
        ]))->assertRedirect();

        $product->refresh()->load('images');
        $this->assertSame($keep->image_path, $product->image_path, 'picked photo is now main');
        $extras = $product->images->pluck('image_path');
        $this->assertCount(2, $extras);
        $this->assertTrue($extras->contains($oldMain), 'old main photo stays as an extra');
        Storage::disk('public')->assertMissing($drop->image_path);
    }

    public function test_removing_the_main_photo_promotes_the_next_one(): void
    {
        $this->post(route('admin.products.store'), $this->fields(['photos' => [$this->photo(), $this->photo()]]));
        $product = Product::with('images')->firstOrFail();
        $oldMain = $product->image_path;
        $next = $product->images->first()->image_path;

        $this->put(route('admin.products.update', $product), $this->fields(['remove_main' => '1']))->assertRedirect();

        $product->refresh();
        $this->assertSame($next, $product->image_path);
        $this->assertSame(0, $product->images()->count());
        Storage::disk('public')->assertMissing($oldMain);
    }

    public function test_photos_are_limited_and_other_products_photos_are_untouchable(): void
    {
        $max = ProductController::MAX_PHOTOS;
        $this->post(route('admin.products.store'), $this->fields(['photos' => array_map(fn () => $this->photo(), range(1, $max))]));
        $product = Product::firstOrFail();
        $this->assertCount($max, $product->galleryUrls());

        $this->put(route('admin.products.update', $product), $this->fields(['photos' => [$this->photo()]]))
            ->assertSessionHasErrors('photos');

        $other = Product::create($this->fields(['name' => 'Other']));
        $foreign = $other->images()->create(['image_path' => 'products/x.png']);
        $this->put(route('admin.products.update', $product), $this->fields(['remove_photos' => [$foreign->id], 'main_photo' => 'existing:'.$foreign->id]));
        $this->assertDatabaseHas('product_images', ['id' => $foreign->id, 'product_id' => $other->id]);
        $this->assertNotSame('products/x.png', $product->fresh()->image_path);
    }

    public function test_form_has_one_photos_field_with_current_photos(): void
    {
        $this->get(route('admin.products.create'))->assertOk()
            ->assertSee('name="photos[]"', false)
            ->assertSee('data-crop-main="main_photo"', false)
            ->assertDontSee('name="image"', false);

        $this->post(route('admin.products.store'), $this->fields(['photos' => [$this->photo(), $this->photo()]]));
        $product = Product::firstOrFail();

        $this->get(route('admin.products.edit', $product))->assertOk()
            ->assertSee('name="remove_main"', false)
            ->assertSee('name="remove_photos[]"', false)
            ->assertSee('value="main" class="form-check-input mt-0" checked', false)
            ->assertSee('data-crop-limit="'.(ProductController::MAX_PHOTOS - 2).'"', false);
    }

    public function test_product_without_extra_photos_shows_no_thumbnails(): void
    {
        $product = Product::create($this->fields());
        $this->get(route('products.show', $product))->assertOk()->assertDontSee('data-pdp-thumb=', false);
    }
}
