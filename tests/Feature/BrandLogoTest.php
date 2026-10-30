<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** UC_03 Product Management: brand logos and the home page logo strip. */
class BrandLogoTest extends TestCase
{
    use RefreshDatabase;

    private function logo(string $name = 'logo.png'): UploadedFile
    {
        // Real PNG bytes, so the test does not depend on the GD extension
        return UploadedFile::fake()->createWithContent($name, file_get_contents(public_path('img/no-image.png')));
    }

    public function test_admin_can_add_a_brand_with_a_logo_that_appears_on_home_and_brands_pages(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Converse',
            'logo' => $this->logo(),
            'show_in_marquee' => '1',
        ])->assertRedirect(route('admin.brands.index'));

        $brand = Brand::where('name', 'Converse')->firstOrFail();
        $this->assertTrue($brand->show_in_marquee);
        Storage::disk('public')->assertExists($brand->logo_path);

        $this->get('/')->assertOk()->assertSee('sl-marquee', false)->assertSee($brand->logoUrl(), false);
        $this->get('/brands')->assertOk()->assertSee($brand->logoUrl(), false);
    }

    public function test_admin_can_replace_hide_and_remove_a_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $brand = Brand::create(['name' => 'Vans', 'logo_path' => $this->logo('old.png')->store('brands', 'public'), 'show_in_marquee' => true]);
        $oldPath = $brand->logo_path;

        // Replace the logo and hide it from the strip (switch unticked = field absent)
        $this->actingAs($admin)->put("/admin/brands/{$brand->id}", ['name' => 'Vans', 'logo' => $this->logo('new.png')])
            ->assertRedirect(route('admin.brands.index'));

        $brand->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($brand->logo_path);
        $this->assertFalse($brand->show_in_marquee);
        $this->get('/')->assertOk()->assertDontSee('class="sl-marquee-track"', false);

        // Remove the logo entirely
        $newPath = $brand->logo_path;
        $this->put("/admin/brands/{$brand->id}", ['name' => 'Vans', 'remove_logo' => '1', 'show_in_marquee' => '1']);

        $this->assertNull($brand->fresh()->logo_path);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_deleting_a_brand_deletes_its_logo_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $brand = Brand::create(['name' => 'Fila', 'logo_path' => $this->logo()->store('brands', 'public')]);

        $this->actingAs($admin)->delete("/admin/brands/{$brand->id}")->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
        Storage::disk('public')->assertMissing($brand->logo_path);
    }

    public function test_non_image_files_are_rejected_and_customers_cannot_manage_brands(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Bad Logo',
            'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors('logo');
        $this->assertDatabaseMissing('brands', ['name' => 'Bad Logo']);

        $this->actingAs($customer)->post('/admin/brands', ['name' => 'Sneaky'])->assertForbidden();
    }
}
