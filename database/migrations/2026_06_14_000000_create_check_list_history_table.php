<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('check_list_history', function (Blueprint $table) {
            $table->id();
            $table->integer('check_list_transaction_id');
            $table->text('comment')->nullable();
            $table->integer('user_id');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('check_list_history');
    }
};
