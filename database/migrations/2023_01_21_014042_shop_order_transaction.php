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
        Schema::create('shop_order_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('shop_order_transaction_total_quantity');
            $table->double('shop_order_transaction_total_price');
            $table->integer('requestor');
            $table->integer('checker');
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
         Schema::dropIfExists('shop_order_transaction');
    }
};
