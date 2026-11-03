<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Class diagram: PaymentMethod 1 — used for — 0..* Payment, replacing the payment_method enum. */
    private const METHODS = [
        'kpay' => 'KPay',
        'ayapay' => 'AYA Pay',
        'cod' => 'Cash on Delivery',
        'saved_card' => 'Saved Card',
        'bank_transfer' => 'Bank Transfer',
    ];

    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 60);
            $table->timestamps();
        });

        foreach (self::METHODS as $code => $name) {
            DB::table('payment_methods')->insert(['code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('order_id')->constrained()->restrictOnDelete();
        });

        DB::statement('UPDATE payments p JOIN payment_methods m ON m.code = p.payment_method SET p.payment_method_id = m.id');
        DB::statement('ALTER TABLE payments MODIFY payment_method_id BIGINT UNSIGNED NOT NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', array_keys(self::METHODS))->default('bank_transfer')->after('order_id');
        });

        DB::statement('UPDATE payments p JOIN payment_methods m ON m.id = p.payment_method_id SET p.payment_method = m.code');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
        });

        Schema::dropIfExists('payment_methods');
    }
};
