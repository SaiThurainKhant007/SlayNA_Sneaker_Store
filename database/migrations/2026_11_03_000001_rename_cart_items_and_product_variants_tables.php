<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Class diagram names: Cart (was cart_items) and ProductSize (was product_variants),
     * so the foreign keys pointing at a product size become product_size_id.
     * Keys and indexes are dropped and re-added so their names follow the new tables.
     */
    public function up(): void
    {
        $this->dropKeys('cart_items', 'variant_id', 'product_variants', 'customer_id');

        Schema::rename('product_variants', 'product_sizes');
        Schema::rename('cart_items', 'carts');

        // MariaDB 10.4 has no RENAME COLUMN, so CHANGE with the full definition
        DB::statement('ALTER TABLE carts CHANGE variant_id product_size_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE order_items CHANGE variant_id product_size_id BIGINT UNSIGNED NOT NULL');

        $this->addKeys('carts', 'product_size_id', 'product_sizes');
    }

    public function down(): void
    {
        $this->dropKeys('carts', 'product_size_id', 'product_sizes', 'customer_id');

        Schema::rename('product_sizes', 'product_variants');
        Schema::rename('carts', 'cart_items');

        DB::statement('ALTER TABLE cart_items CHANGE product_size_id variant_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE order_items CHANGE product_size_id variant_id BIGINT UNSIGNED NOT NULL');

        $this->addKeys('cart_items', 'variant_id', 'product_variants');
    }

    private function dropKeys(string $cart, string $sizeColumn, string $sizes, string $customerColumn): void
    {
        Schema::table($cart, function (Blueprint $table) use ($cart, $sizeColumn, $customerColumn) {
            $table->dropForeign("{$cart}_{$customerColumn}_foreign");
            $table->dropForeign("{$cart}_{$sizeColumn}_foreign");
            $table->dropUnique("{$cart}_{$customerColumn}_{$sizeColumn}_unique");
            $table->dropIndex("{$cart}_{$sizeColumn}_foreign");
        });

        Schema::table('order_items', function (Blueprint $table) use ($sizeColumn) {
            $table->dropForeign("order_items_{$sizeColumn}_foreign");
            $table->dropIndex("order_items_{$sizeColumn}_foreign");
        });

        Schema::table($sizes, function (Blueprint $table) use ($sizes) {
            $table->dropForeign("{$sizes}_product_id_foreign");
            $table->dropForeign("{$sizes}_size_id_foreign");
            $table->dropUnique("{$sizes}_product_id_size_id_unique");
            $table->dropIndex("{$sizes}_size_id_foreign");
        });
    }

    private function addKeys(string $cart, string $sizeColumn, string $sizes): void
    {
        Schema::table($sizes, function (Blueprint $table) {
            $table->unique(['product_id', 'size_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('size_id')->references('id')->on('sizes')->cascadeOnDelete();
        });

        Schema::table($cart, function (Blueprint $table) use ($sizeColumn, $sizes) {
            $table->unique(['customer_id', $sizeColumn]);
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign($sizeColumn)->references('id')->on($sizes)->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) use ($sizeColumn, $sizes) {
            $table->foreign($sizeColumn)->references('id')->on($sizes)->restrictOnDelete();
        });
    }
};
