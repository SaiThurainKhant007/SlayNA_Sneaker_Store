<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Snapshot of the promotion applied to each order line, so staff see it even after the promotion ends or is deleted. */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->nullable()->after('unit_price');
            $table->unsignedTinyInteger('discount_percent')->nullable()->after('original_price');
            $table->foreignId('promotion_id')->nullable()->after('discount_percent')->constrained()->nullOnDelete();
            $table->string('promotion_title', 120)->nullable()->after('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['original_price', 'discount_percent', 'promotion_title']);
        });
    }
};
