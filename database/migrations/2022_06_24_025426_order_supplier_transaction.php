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
            $table->string('invoice_number');
            $table->boolean('withTax')->default(0);
            $table->double('total_transaction_price');
            $table->string('status');
            $table->integer('payment_status');
            $table->integer('requestor');
            $table->integer('approval');
            $table->string('approval_status');
            $table->string('note');
            $table->integer('stock_status');
            $table->date('order_date');
            $table->date('due_date')->nullable();
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
