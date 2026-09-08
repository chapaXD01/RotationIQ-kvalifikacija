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

            DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', '!=', $team->coach_id)
                ->where(function ($query) {
                    $query->whereNull('role')
                        ->orWhere('role', '');
                })
                ->update(['role' => 'student']);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
