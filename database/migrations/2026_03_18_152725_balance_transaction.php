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
        Schema::create('balance_transaction', function (Blueprint $table) {
            $table->id(); //
            $table->integer('payment_type_po_id');
            $table->integer('shop_id');
            $table->integer('join_id');
            $table->string('name');
            $table->string('transaction');
            $table->string('balance_type_id');
            $table->double('total_balance');
            $table->double('amount');
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
         Schema::dropIfExists('balance_transaction');
    }
};
