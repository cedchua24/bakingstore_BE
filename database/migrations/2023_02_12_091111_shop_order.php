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
        Schema::create('shop_order', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_transaction_id');
            $table->integer('branch_stock_transaction_id');  
            $table->integer('product_id');
            $table->integer('mark_up_product_id');
            $table->integer('shop_order_quantity');
            $table->integer('shop_order_price');
            $table->double('shop_order_total_price');
            $table->double('shop_order_profit');
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
  Schema::dropIfExists('shop_order');
    }
};
