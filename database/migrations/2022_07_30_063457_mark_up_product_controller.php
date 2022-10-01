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
        Schema::create('mark_up_product', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->double('price');
            $table->string('mark_up_option');
            $table->double('mark_up_price');
            $table->double('new_price');
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
        Schema::dropIfExists('mark_up_product');
    }
};
