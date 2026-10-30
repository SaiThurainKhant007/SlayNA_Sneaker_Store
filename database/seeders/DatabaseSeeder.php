<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Mirrors database/slayna_db.sql from the original PHP prototype so demo
     * accounts, catalogue and a sample completed order carry over unchanged.
     */
    public function run(): void
    {
        $password = Hash::make('Password123!');

        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@slayna.com',
            'password' => $password,
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Su Su Staff',
            'email' => 'staff@slayna.com',
            'password' => $password,
            'role' => 'staff',
        ]);

        $staff->staffProfile()->create([
            'managed_by' => $admin->id,
            'position' => 'Sales Associate',
            'hired_on' => now()->toDateString(),
        ]);

        $customer = User::create([
            'name' => 'Aung Customer',
            'email' => 'customer@slayna.com',
            'password' => $password,
            'role' => 'customer',
            'address' => 'No. 12, Bo Aung Kyaw Street, Yangon',
        ]);

        $brands = collect(['Nike', 'Adidas', 'Puma', 'New Balance'])
            ->mapWithKeys(fn ($name) => [$name => Brand::create(['name' => $name])->id]);

        $categories = collect(['Running', 'Basketball', 'Lifestyle', 'Skateboarding'])
            ->mapWithKeys(fn ($name) => [$name => Category::create(['name' => $name])->id]);

        $sizes = collect(['38', '39', '40', '41', '42', '43', '44'])
            ->map(fn ($label) => Size::create(['label' => $label]));

        $products = [
            ['name' => 'Air Zoom Pegasus', 'description' => 'Lightweight everyday running shoe with responsive cushioning.', 'price' => 145000, 'brand' => 'Nike', 'category' => 'Running'],
            ['name' => 'Ultraboost Light', 'description' => 'Energy-returning running shoe with Boost midsole.', 'price' => 210000, 'brand' => 'Adidas', 'category' => 'Running'],
            ['name' => 'Dunk Low Retro', 'description' => 'Classic low-top basketball-inspired lifestyle sneaker.', 'price' => 165000, 'brand' => 'Nike', 'category' => 'Lifestyle'],
            ['name' => 'Suede Classic', 'description' => 'Iconic Puma suede sneaker for everyday street style.', 'price' => 98000, 'brand' => 'Puma', 'category' => 'Lifestyle'],
            ['name' => '574 Core', 'description' => 'New Balance heritage running silhouette, breathable mesh upper.', 'price' => 125000, 'brand' => 'New Balance', 'category' => 'Running'],
        ];

        $firstVariant = null;

        foreach ($products as $data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'brand_id' => $brands[$data['brand']],
                'category_id' => $categories[$data['category']],
                'low_stock_threshold' => 5,
            ]);

            foreach ($sizes as $size) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'size_id' => $size->id,
                    'stock_quantity' => random_int(3, 15),
                ]);

                $firstVariant ??= $variant;
            }
        }

        // Sample completed order + confirmed payment + review, so reviews/reports have demo content
        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_address' => $customer->address,
            'total_amount' => $firstVariant->product->price,
            'order_status' => 'completed',
            'processed_by' => $staff->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $firstVariant->id,
            'quantity' => 1,
            'unit_price' => $firstVariant->product->price,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'confirmed',
            'confirmed_by' => $staff->id,
            'confirmed_at' => now(),
        ]);

        Review::create([
            'product_id' => $firstVariant->product_id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Very comfortable for daily runs, true to size!',
        ]);

        $this->call(CatalogSeeder::class);

        $this->command?->info('Seeded demo accounts (password: Password123!): admin@slayna.com, staff@slayna.com, customer@slayna.com');
    }
}
