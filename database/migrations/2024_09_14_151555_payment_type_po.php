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
            $table->integer('payment_term_id');
            $table->integer('bank_id');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('account_description');
            $table->integer('due_date');
            $table->integer('buffer_days');
            $table->double('credit_limit');
            $table->integer('statement_date');
            $table->double('total_balance_due');
            $table->double('balance'); //
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
         Schema::dropIfExists('payment_type_po');
    }
};
