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
        Schema::create('expenses_v2', function (Blueprint $table) {
            $table->id();
            $table->integer('expense_category_id');
            $table->string('expense_name');
            $table->string('expense_code');
            $table->string('details');
            $table->string('account_nature'); //DEBIT / CREDIT
            $table->integer('is_hidden');    
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
         Schema::dropIfExists('expenses_v2');
    }
};
