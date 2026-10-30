<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Promotional model photos shown in the home page gallery below Hot Deals. */
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('title', 80)->nullable();        // e.g. "Street Ready"
            $table->string('subtitle', 120)->nullable();    // e.g. "adidas Superstar"
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();   // "Shop this look"
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
