<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('check_list_transaction', function (Blueprint $table) {
            $table->integer('grade_checker')->default(0)->after('grade');
        });
    }

    public function down()
    {
        Schema::table('check_list_transaction', function (Blueprint $table) {
            $table->dropColumn('grade_checker');
        });
    }
};
