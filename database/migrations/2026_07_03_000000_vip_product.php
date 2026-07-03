<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vip_product', function (Blueprint $table) {
            $table->id();
            $table->string('vip_product_name');
            $table->string('details')->nullable();
            $table->string('vip_color')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vip_product');
    }
};
