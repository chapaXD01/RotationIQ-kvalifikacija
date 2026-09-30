<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->string('type')->default('single')->after('players');
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->string('type')->default('single')->after('players');
        });
    }

    public function down(): void
    {
        Schema::table('attack_rotations', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('defence_rotations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
