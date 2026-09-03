<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        });

        // Set existing rotations to the first user (ID 1) if no user exists, set to 1
        $firstUserId = DB::table('users')->first()?->id ?? 1;
        DB::table('attack_rotations')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        DB::table('defence_rotations')->whereNull('user_id')->update(['user_id' => $firstUserId]);

        // Make user_id NOT NULLABLE after setting defaults
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->dropForeignIdFor('User');
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->dropForeignIdFor('User');
        });
    }
};
