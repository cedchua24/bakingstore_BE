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
        Schema::create('branch_stock_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('warehouse_id');
            $table->integer('product_id');
            $table->integer('branch_stock_transaction');
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
         Schema::dropIfExists('branch_stock_transaction');
    }
};
