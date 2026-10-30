<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** KPay / AYA Pay transfers: new payment methods plus the transaction ID the customer types in. */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('bank_transfer','cod','saved_card','kpay','ayapay') NOT NULL DEFAULT 'bank_transfer'");

        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_ref', 40)->nullable()->after('proof_image');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_ref');
        });

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('bank_transfer','cod','saved_card') NOT NULL DEFAULT 'bank_transfer'");
    }
};
