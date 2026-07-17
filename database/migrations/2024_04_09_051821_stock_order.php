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
        Schema::create('stock_order', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('pack');
            $table->double('total_cost');
            $table->double('price');
            $table->string('stock_type');
            $table->string('stock_reason');
            $table->string('type')->default('ADJUSTMENT');
            $table->integer('stock');
            $table->integer('total_stock');
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
        Schema::dropIfExists('customer_type');
    }
};
