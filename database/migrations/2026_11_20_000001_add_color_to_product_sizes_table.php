<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stock per colour: one product_sizes row per (product, size, colour), so "574 Core Black with White"
     * and "574 Core Grey with White" each have their own sizes and stock. The colour is the Color/Type name
     * admin gives the product photos. NULL = one stock for the whole product (products without colour names).
     */
    public function up(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->string('color', 60)->nullable()->after('size_id');
        });

        // Add the new key first: in MySQL the old unique key also serves the product_id foreign key
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->unique(['product_id', 'size_id', 'color'], 'product_sizes_product_size_color_unique');
        });
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropUnique($this->oldUniqueName());
        });
    }

    public function down(): void
    {
        // Merge each product's colours back into one row per size before restoring the old key
        foreach (DB::table('product_sizes')->whereNotNull('color')->get() as $row) {
            $base = DB::table('product_sizes')->where('product_id', $row->product_id)->where('size_id', $row->size_id)->whereNull('color')->first();
            if ($base) {
                DB::table('product_sizes')->where('id', $base->id)->increment('stock_quantity', $row->stock_quantity);
                DB::table('product_sizes')->where('id', $row->id)->update(['stock_quantity' => 0]);
            } else {
                DB::table('product_sizes')->where('id', $row->id)->update(['color' => null]);
            }
        }

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->unique(['product_id', 'size_id'], $this->oldUniqueName());
        });
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropUnique('product_sizes_product_size_color_unique');
            $table->dropColumn('color');
        });
    }

    /** SQLite (tests) kept the index name from before the table was renamed. */
    private function oldUniqueName(): string
    {
        return DB::getDriverName() === 'sqlite' ? 'product_variants_product_id_size_id_unique' : 'product_sizes_product_id_size_id_unique';
    }
};
