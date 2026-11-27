<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Discount promotions become home-page Hot Deals cards only: brand + original price,
 * no link to a shop product (so the catalogue stays at full price). Limited Editions
 * still keep their hidden product_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('description')->constrained()->nullOnDelete();
            $table->decimal('original_price', 12, 2)->nullable()->after('brand_id');
        });

        // Drop the required FK so discount promotions can stand alone.
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        // Copy brand/price off the linked product, then unlink discount promotions from the shop.
        $rows = DB::table('promotions')->whereNotNull('product_id')->get();
        foreach ($rows as $row) {
            $product = DB::table('products')->where('id', $row->product_id)->first();
            if (! $product) {
                continue;
            }

            $type = $row->type ?? 'promotion';
            DB::table('promotions')->where('id', $row->id)->update([
                'brand_id' => $product->brand_id,
                'original_price' => $type === 'limited' ? null : $product->price,
                'product_id' => $type === 'limited' ? $row->product_id : null,
            ]);
        }
    }

    public function down(): void
    {
        // Re-attach orphaned promotions to any product of the same brand so the old NOT NULL FK can return.
        $orphans = DB::table('promotions')->whereNull('product_id')->get();
        foreach ($orphans as $row) {
            $productId = DB::table('products')
                ->where('brand_id', $row->brand_id)
                ->where('status', 'active')
                ->value('id')
                ?? DB::table('products')->value('id');

            if ($productId) {
                DB::table('promotions')->where('id', $row->id)->update(['product_id' => $productId]);
            }
        }

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn('original_price');
        });
    }
};
