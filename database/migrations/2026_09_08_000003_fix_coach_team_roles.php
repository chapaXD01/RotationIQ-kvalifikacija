<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $teams = DB::table('teams')->select('id', 'coach_id')->get();

        foreach ($teams as $team) {
            DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', $team->coach_id)
                ->update(['role' => 'manager']);
        }
    }

    public function down(): void
    {
        // no-op
    }
};