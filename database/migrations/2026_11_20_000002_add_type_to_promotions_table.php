<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promotion type: 'promotion' (a discount) or 'limited' (Limited Edition: featured and labelled,
     * always sold at full price, so its discount_percent is 0). Existing rows stay promotions.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('type', 20)->default('promotion')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
