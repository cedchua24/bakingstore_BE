<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_type_po', function (Blueprint $table) {
            $table->integer('is_supplier')->default(0)->after('status');
            $table->integer('is_customer')->default(0)->after('is_supplier');
        });
    }

    public function down()
    {
        Schema::table('payment_type_po', function (Blueprint $table) {
            $table->dropColumn(['is_supplier', 'is_customer']);
        });
    }
};
