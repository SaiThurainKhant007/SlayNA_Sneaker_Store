<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'limited' = a Limited Edition sold only from its own section on the home page. Admin creates it
     * in Manage Promotions (no product to pick); this product row just carries its price, photo and
     * sizes so the cart and checkout work as usual. Every storefront list shows only 'active', so it
     * never appears in the shop, search, new arrivals or brand pages, and it has no product page.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('status', ['active', 'discontinued', 'limited'])->default('active')->change();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('products')->where('status', 'limited')->update(['status' => 'discontinued']);

        Schema::table('products', function (Blueprint $table) {
            $table->enum('status', ['active', 'discontinued'])->default('active')->change();
        });
    }
};
