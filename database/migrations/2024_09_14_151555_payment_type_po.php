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
        Schema::create('payment_type_po', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type');
            $table->string('payment_type_description');
            $table->integer('status');
            $table->integer('type');
            $table->integer('due_date');
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
         Schema::dropIfExists('payment_type_po');
    }
};
