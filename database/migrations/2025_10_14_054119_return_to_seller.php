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

       Schema::create('return_to_seller', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('type');
            $table->string('reason');
            $table->integer('quantity');
            $table->double('price');
            $table->double('total_cost');
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
         Schema::dropIfExists('return_to_seller');
    }
};
