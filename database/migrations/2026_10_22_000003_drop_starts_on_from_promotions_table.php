<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Promotions now apply as soon as they are switched on, so a start date no longer exists. */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('starts_on');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->date('starts_on')->nullable()->after('hover_image_path');
        });
    }
};
