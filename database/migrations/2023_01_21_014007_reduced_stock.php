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
        Schema::create('reduced_stock', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_order_id');
            $table->integer('mark_up_product_id');
            $table->integer('reduced_stock');
            $table->integer('reduced_stock_by_shop_id');
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
         Schema::dropIfExists('reduced_stock');
    }
};
