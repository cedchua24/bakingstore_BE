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
        Schema::create('check_list', function (Blueprint $table) {
            $table->id();
            $table->string('check_list_name');
            $table->integer('assignee');
            $table->integer('checker');
            $table->string('time_of_day'); // morning, afternoon, evening
            $table->string('frequency'); // DAILY, WEEKLY, MONTHLY
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
         Schema::dropIfExists('check_list');
    }
};
