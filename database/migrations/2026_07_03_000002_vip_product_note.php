<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vip_product_note', function (Blueprint $table) {
            $table->id();
            $table->integer('vip_product_transaction_id');
            $table->integer('user_id');
            $table->string('comment')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vip_product_note');
    }
};
