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
        Schema::create('credit_card_due', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_type_po_id');
            $table->double('min_amount');
            $table->double('amount');
            $table->double('amount_paid');
            $table->double('interest_amount');
            $table->integer('is_installment');
            $table->integer('status');
            $table->string('type');
            $table->date('due_date');
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
         Schema::dropIfExists('credit_card_due');
    }
};
