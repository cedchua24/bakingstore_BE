<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('printing_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_order_transaction_id')->constrained('shop_order_transaction');
            $table->foreignId('order_coordinator_id')->constrained('users');
            $table->enum('logo', ['OLD', 'NEW', 'PENDING'])->nullable()->default('PENDING');
            $table->boolean('plate')->default(false);
            $table->enum('mock_up_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->enum('sales_channel', ['FACEBOOK', 'VIBER']);
            $table->enum('order_priority', ['NORMAL', 'RUSH'])->default('NORMAL');
            $table->enum('order_status', ['PENDING', 'COMPLETED'])->default('PENDING');
            $table->date('order_date');
            $table->date('sent_date')->nullable();
            $table->date('received_date')->nullable();
            $table->timestamps();
        });

        Schema::create('printing_transaction_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printing_transaction_id')->constrained('printing_transaction')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('printing_transaction_comments');
        Schema::dropIfExists('printing_transaction');
    }
};
