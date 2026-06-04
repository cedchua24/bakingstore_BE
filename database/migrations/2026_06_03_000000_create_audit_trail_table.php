<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_trail', function (Blueprint $table) {
            $table->id();
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->string('event_type')->nullable();
            $table->string('request_method')->nullable();
            $table->text('endpoint')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('request_payload')->nullable();
            $table->longText('request_headers')->nullable();
            $table->string('exception_class')->nullable();
            $table->longText('exception_message')->nullable();
            $table->text('exception_file')->nullable();
            $table->integer('exception_line')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_trail');
    }
};
