<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A colour/type name for every product photo (e.g. "Black Rose Gold"), set by admin and shown
     * on the product page as "Color/Type: ..." when the customer picks that photo.
     * products.image_label names the main photo; product_images.label names each extra photo.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_label', 60)->nullable()->after('image_path');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('label', 60)->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('label');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_label');
        });
    }
};
