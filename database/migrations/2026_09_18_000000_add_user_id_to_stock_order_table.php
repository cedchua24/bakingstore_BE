<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_order', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('stock_order', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
