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
        Schema::create('check_list_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('check_list_id');
            $table->integer('assignee');
            $table->integer('checker');
            $table->string('comment');
            $table->integer('grade');
            $table->string('status');
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
         Schema::dropIfExists('check_list_transaction');
    }
};
