<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** KPay / AYA Pay transfers: new payment methods plus the transaction ID the customer types in. */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_transfer', 'cod', 'saved_card', 'kpay', 'ayapay'])->default('bank_transfer')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_ref', 40)->nullable()->after('proof_image');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_ref');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_transfer', 'cod', 'saved_card'])->default('bank_transfer')->change();
        });
    }
};
