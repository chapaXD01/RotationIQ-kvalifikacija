<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
    }
};
