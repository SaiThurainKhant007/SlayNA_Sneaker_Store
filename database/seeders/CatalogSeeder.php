<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;

/**
 * Expands the shop catalogue with a wide range of brands and sneakers.
 * Safe to run on an existing database: brands, categories and products are
 * matched by name, so nothing is duplicated or overwritten.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // brand => [[name, category, price, description], ...]
            'Nike' => [
                ['Air Force 1 \'07', 'Lifestyle', 155000, 'The legendary white-on-white court classic with crisp leather overlays.'],
                ['Air Max 90', 'Lifestyle', 185000, 'Visible Max Air cushioning and waffle outsole in an iconic 90s silhouette.'],
                ['Vomero 17', 'Running', 175000, 'Plush ZoomX foam for long, comfortable training miles.'],
                ['SB Dunk Low Pro', 'Skateboarding', 160000, 'Padded tongue and grippy cupsole tuned for the skatepark.'],
            ],
            'Jordan' => [
                ['Air Jordan 1 Mid', 'Basketball', 195000, 'The shoe that started it all, in a versatile mid-top cut.'],
                ['Air Jordan 4 Retro', 'Basketball', 260000, 'Mesh panels, visible Air and the famous wing eyelets.'],
                ['Jordan Luka 3', 'Basketball', 205000, 'Responsive cushioning and lockdown support for quick cuts.'],
            ],
            'Adidas' => [
                ['Samba OG', 'Lifestyle', 150000, 'Terrace-ready leather upper with a gum rubber sole.'],
                ['Gazelle Indoor', 'Lifestyle', 145000, 'Soft suede and a translucent outsole straight from the archive.'],
                ['Adizero Adios Pro 3', 'Running', 280000, 'Carbon-infused race-day shoe built for personal bests.'],
                ['Forum Low', 'Basketball', 140000, '80s basketball heritage with the signature ankle strap.'],
            ],
            'Puma' => [
                ['Palermo', 'Lifestyle', 115000, 'Retro terrace sneaker with a suede upper and T-toe overlay.'],
                ['Velocity Nitro 3', 'Running', 160000, 'Nitrogen-infused foam for a light, springy everyday run.'],
                ['MB.03', 'Basketball', 195000, 'LaMelo Ball\'s signature shoe with bold, out-of-this-world styling.'],
            ],
            'New Balance' => [
                ['550', 'Lifestyle', 165000, 'Clean late-80s basketball design reborn for the street.'],
                ['9060', 'Lifestyle', 210000, 'Chunky, sculpted sole with ABZORB and SBS cushioning.'],
                ['Fresh Foam X 1080v13', 'Running', 225000, 'Ultra-soft Fresh Foam X for daily training comfort.'],
            ],
            'Converse' => [
                ['Chuck Taylor All Star Hi', 'Lifestyle', 85000, 'The timeless canvas high-top worn by every generation.'],
                ['Chuck 70 Low', 'Lifestyle', 105000, 'Premium canvas and extra cushioning on a vintage profile.'],
                ['One Star Pro', 'Skateboarding', 110000, 'Suede skate shoe with a durable, grippy vulcanised sole.'],
            ],
            'Vans' => [
                ['Old Skool', 'Skateboarding', 90000, 'The original side-stripe skate shoe with a waffle outsole.'],
                ['Sk8-Hi', 'Skateboarding', 100000, 'Padded high-top collar for support on and off the board.'],
                ['Authentic', 'Lifestyle', 80000, 'Simple, low-profile canvas lace-up from Vans\' early days.'],
            ],
            'Asics' => [
                ['Gel-Kayano 31', 'Running', 215000, 'Stability running shoe with FF Blast+ cushioning.'],
                ['Gel-1130', 'Lifestyle', 135000, 'Y2K-inspired runner with breathable mesh and GEL comfort.'],
                ['Novablast 4', 'Running', 175000, 'Bouncy trampoline-inspired midsole for energetic runs.'],
            ],
            'Onitsuka Tiger' => [
                ['Mexico 66', 'Lifestyle', 160000, 'Slim, iconic profile with the famous Tiger stripes.'],
            ],
            'Reebok' => [
                ['Club C 85', 'Lifestyle', 110000, 'Minimal leather tennis sneaker with a soft terry lining.'],
                ['Classic Leather', 'Lifestyle', 105000, 'Soft garment leather upper and EVA midsole since 1983.'],
            ],
            'Hoka' => [
                ['Clifton 9', 'Running', 200000, 'Lightweight, max-cushioned trainer for every distance.'],
                ['Bondi 8', 'Running', 225000, 'Hoka\'s most cushioned road shoe for ultimate comfort.'],
            ],
            'Under Armour' => [
                ['Curry 11', 'Basketball', 210000, 'Stephen Curry\'s signature shoe with UA Flow grip.'],
            ],
            'Skechers' => [
                ['D\'Lites', 'Lifestyle', 95000, 'Chunky, comfortable sneaker with a memory foam insole.'],
            ],
            'Fila' => [
                ['Disruptor II', 'Lifestyle', 98000, 'Bold, serrated chunky sole that defined 90s street style.'],
            ],
        ];

        $categoryIds = collect(['Running', 'Basketball', 'Lifestyle', 'Skateboarding'])
            ->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['name' => $name])->id]);

        $sizes = Size::all();

        foreach ($catalog as $brandName => $items) {
            $brandId = Brand::firstOrCreate(['name' => $brandName])->id;

            foreach ($items as [$name, $category, $price, $description]) {
                $product = Product::firstOrCreate(
                    ['name' => $name],
                    [
                        'description' => $description,
                        'price' => $price,
                        'brand_id' => $brandId,
                        'category_id' => $categoryIds[$category],
                        'low_stock_threshold' => 5,
                    ]
                );

                if (! $product->wasRecentlyCreated) {
                    continue;
                }

                foreach ($sizes as $size) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'stock_quantity' => random_int(0, 15),
                    ]);
                }
            }
        }

        $this->command?->info('Catalogue now has '.Brand::count().' brands and '.Product::count().' products.');
    }
}
