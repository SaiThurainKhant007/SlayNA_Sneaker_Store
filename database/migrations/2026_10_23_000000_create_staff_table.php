<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('position', 100)->nullable();
            $table->date('hired_on')->nullable();
            $table->timestamps();
        });

      
        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        DB::table('users')->where('role', 'staff')->orderBy('id')->get(['id', 'created_at'])
            ->each(fn ($user) => DB::table('staff')->insert([
                'user_id' => $user->id,
                'managed_by' => $adminId,
                'hired_on' => $user->created_at ? substr($user->created_at, 0, 10) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
