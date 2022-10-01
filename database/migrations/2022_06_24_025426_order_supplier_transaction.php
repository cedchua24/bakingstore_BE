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
        Schema::create('order_supplier_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('supplier_id');
            $table->boolean('withTax')->default(0);
            $table->double('total_transaction_price');
            $table->string('status');
            $table->date('order_date');
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
        Schema::dropIfExists('order_supplier_transaction');
    }
};
