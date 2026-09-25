<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // SQLite uses the current definition in the creation migration.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE printing_transaction MODIFY logo ENUM('OLD', 'NEW', 'PENDING') NULL DEFAULT 'PENDING'");
    }

    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (DB::table('printing_transaction')->where('logo', 'PENDING')->exists()) {
            throw new RuntimeException('Change PENDING logos to OLD, NEW, or null before rolling back this migration.');
        }

        DB::statement("ALTER TABLE printing_transaction MODIFY logo ENUM('OLD', 'NEW') NULL DEFAULT NULL");
    }
};
