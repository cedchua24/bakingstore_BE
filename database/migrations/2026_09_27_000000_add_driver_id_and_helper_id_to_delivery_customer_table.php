<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_customer', function (Blueprint $table) {
            $table->integer('driver_id')->nullable()->after('status');
            $table->integer('helper_id')->nullable()->after('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_customer', function (Blueprint $table) {
            $table->dropColumn(['driver_id', 'helper_id']);
        });
    }
};
