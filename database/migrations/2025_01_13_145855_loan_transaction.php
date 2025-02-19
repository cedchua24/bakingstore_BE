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
        Schema::create('loan_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('details');
            $table->string('borrower');
            $table->integer('bank_id');
            $table->integer('payment_type_po_id');
            $table->integer('number_of_months');
            $table->double('total_interest');
            $table->double('interest');
            $table->double('interest_monthly');
            $table->double('amount');
            $table->double('amount_monthly');
            $table->date('start_date');
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
         Schema::dropIfExists('loan_transaction');
    }
};
