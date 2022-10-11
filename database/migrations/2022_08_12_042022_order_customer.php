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
        Schema::create('order_customer', function (Blueprint $table) {
            $table->id();
            $table->integer('order_customer_transaction_id');
            $table->integer('mark_up_id');
            $table->double('price');
            $table->integer('quantity');
            $table->double('total_price');
            $table->double('discount');
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
       Schema::dropIfExists('order_customer');
    }
};
