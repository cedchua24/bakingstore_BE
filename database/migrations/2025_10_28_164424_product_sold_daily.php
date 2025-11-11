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
        Schema::create('product_sold_daily', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('product_code');
            $table->integer('stock');
            $table->integer('stock_pc');
            $table->integer('total_stock');
            $table->integer('current_stock');
            $table->integer('stock_input');
            $table->integer('discrepancy');
            $table->date('date');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('product_sold_daily');
    }
};
