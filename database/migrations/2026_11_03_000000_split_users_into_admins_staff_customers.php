<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Class diagram: Admin, Staff and Customer are separate classes, so each gets its own table
     * with its own login. Rows keep their old users.id, so every existing foreign key value
     * (orders.customer_id, orders.processed_by, payments.confirmed_by, ...) stays valid.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        // Old staff table was only a profile hanging off users; rebuild it as a full account table
        Schema::rename('staff', 'staff_profiles_old');

        // Foreign key names are database-wide in MySQL; free them for the new staff table
        Schema::table('staff_profiles_old', function (Blueprint $table) {
            $table->dropForeign('staff_user_id_foreign');
            $table->dropForeign('staff_managed_by_foreign');
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->foreignId('managed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('position', 100)->nullable();
            $table->date('hired_on')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        $columns = ['id', 'name', 'email', 'password', 'phone', 'status', 'remember_token', 'created_at', 'updated_at'];

        DB::table('users')->where('role', 'admin')->orderBy('id')->get()
            ->each(fn ($user) => DB::table('admins')->insert(collect($user)->only($columns)->all()));

        DB::table('users')->where('role', 'customer')->orderBy('id')->get()
            ->each(fn ($user) => DB::table('customers')->insert(collect($user)->only([...$columns, 'address'])->all()));

        DB::table('users')->where('role', 'staff')->orderBy('id')->get()
            ->each(function ($user) use ($columns) {
                $profile = DB::table('staff_profiles_old')->where('user_id', $user->id)->first();

                DB::table('staff')->insert(collect($user)->only($columns)->merge([
                    'managed_by' => $profile?->managed_by,
                    'position' => $profile?->position,
                    'hired_on' => $profile?->hired_on,
                ])->all());
            });

        // Repoint foreign keys from users to the table of the role that owns the column
        $repoint = [
            ['cart_items', 'customer_id', 'customers', 'cascade'],
            ['orders', 'customer_id', 'customers', 'cascade'],
            ['reviews', 'customer_id', 'customers', 'cascade'],
            ['saved_cards', 'customer_id', 'customers', 'cascade'],
            ['orders', 'processed_by', 'staff', 'null'],
            ['payments', 'confirmed_by', 'staff', 'null'],
        ];

        foreach ($repoint as [$table, $column, $target, $onDelete]) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));

            // Orders cancelled by the customer, or processed by an admin, have no staff member behind them
            if ($onDelete === 'null') {
                DB::table($table)->whereNotNull($column)->whereNotIn($column, DB::table($target)->select('id'))->update([$column => null]);
            }

            Schema::table($table, function (Blueprint $t) use ($column, $target, $onDelete) {
                $fk = $t->foreign($column)->references('id')->on($target);
                $onDelete === 'cascade' ? $fk->cascadeOnDelete() : $fk->nullOnDelete();
            });
        }

        Schema::drop('staff_profiles_old');
        Schema::drop('users');

        // Sessions stored a users.id; everyone signs in again against the new tables
        DB::table('sessions')->truncate();
    }

    public function down(): void
    {
        throw new RuntimeException('Splitting users into admins/staff/customers cannot be rolled back automatically; restore a database backup instead.');
    }
};
