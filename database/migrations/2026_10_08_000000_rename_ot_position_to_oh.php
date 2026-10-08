<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// "OT" (outside hitter) is being renamed to "OH" everywhere for consistent naming —
// this backfills the handful of existing rows so old data matches the new validation
// rules and displays correctly, instead of just changing it for future saves.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('team_user')->where('position', 'OT')->update(['position' => 'OH']);

        foreach (['attack_rotations', 'defence_rotations', 'moving_players'] as $table) {
            DB::table($table)
                ->where('players', 'like', '%"role":"OT"%')
                ->get(['id', 'players'])
                ->each(function ($row) use ($table) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['players' => str_replace('"role":"OT"', '"role":"OH"', $row->players)]);
                });
        }
    }

    public function down(): void
    {
        DB::table('team_user')->where('position', 'OH')->update(['position' => 'OT']);

        foreach (['attack_rotations', 'defence_rotations', 'moving_players'] as $table) {
            DB::table($table)
                ->where('players', 'like', '%"role":"OH"%')
                ->get(['id', 'players'])
                ->each(function ($row) use ($table) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['players' => str_replace('"role":"OH"', '"role":"OT"', $row->players)]);
                });
        }
    }
};
