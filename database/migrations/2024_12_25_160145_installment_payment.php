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
        Schema::create('installment_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('installment_payment_transaction_id');
            $table->double('amount');
            $table->double('amount_due');
            $table->double('penalty');
            $table->date('due_date');
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
         Schema::dropIfExists('installment_payment');
    }
};
