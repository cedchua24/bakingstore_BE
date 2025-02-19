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
        Schema::create('credit_card_installment_details', function (Blueprint $table) {
            $table->id();
            $table->integer('credit_card_due_id');
            $table->integer('number_of_months');
            $table->double('total_interest');
            $table->double('interest_amount');
            $table->double('interest_monthly');
            $table->double('amount');
            $table->string('start_date');
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
         Schema::dropIfExists('credit_card_installment_details');
    }
};
