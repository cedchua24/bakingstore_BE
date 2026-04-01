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
        Schema::create('expenses_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('expense_id');
            $table->integer('shop_id');
            $table->integer('user_id');
            $table->integer('approver_id');
            $table->string('approval_status');
            $table->integer('payment_type_po_id');
            $table->double('amount');
            $table->string('details')->nullable();
            $table->date('expense_date');
            $table->date('date_received')->nullable();
            $table->integer('status');
            $table->integer('is_received');
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
         Schema::dropIfExists('expenses_transaction');
    }
};
