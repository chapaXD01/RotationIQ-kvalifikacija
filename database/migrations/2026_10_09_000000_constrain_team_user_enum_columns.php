<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// role/position/attendance on team_user were plain strings — only the controllers'
// validation rules stopped a bad value from getting in, so anything writing to this
// table outside those specific methods (a future migration/seeder/tinker session) could
// silently leave the column holding something nonsensical. Raw SQL rather than the
// schema builder's enum()->change(), since that needs doctrine/dbal, which isn't
// installed here, and plain ALTER TABLE is unambiguous for MySQL.
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used by the test suite — see phpunit.xml) doesn't support ALTER COLUMN
        // type changes or native ENUM at all, and is dynamically typed besides; the
        // DB-level constraint only matters for the real MySQL database.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE team_user MODIFY COLUMN role ENUM('manager','assistant_manager','student') NOT NULL DEFAULT 'student'");
        DB::statement("ALTER TABLE team_user MODIFY COLUMN position ENUM('S','MB','OH','RS','L') NULL");
        DB::statement("ALTER TABLE team_user MODIFY COLUMN attendance ENUM('present','absent','substitute') NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE team_user MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'student'");
        DB::statement("ALTER TABLE team_user MODIFY COLUMN position VARCHAR(3) NULL");
        DB::statement("ALTER TABLE team_user MODIFY COLUMN attendance VARCHAR(255) NOT NULL DEFAULT 'present'");
    }
};
