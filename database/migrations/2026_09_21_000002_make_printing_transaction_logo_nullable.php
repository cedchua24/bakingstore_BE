<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // SQLite uses the nullable definition in the creation migration.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE printing_transaction MODIFY logo ENUM('OLD', 'NEW') NULL DEFAULT NULL");
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::table('printing_transaction')->whereNull('logo')->exists()) {
            throw new RuntimeException('Set all null logos to OLD or NEW before rolling back this migration.');
        }

        DB::statement("ALTER TABLE printing_transaction MODIFY logo ENUM('OLD', 'NEW') NOT NULL");
    }
};
