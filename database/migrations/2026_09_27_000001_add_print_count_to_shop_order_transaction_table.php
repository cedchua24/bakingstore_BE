<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('shop_order_transaction', 'print_count')) {
            Schema::table('shop_order_transaction', function (Blueprint $table) {
                $table->unsignedInteger('print_count')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('shop_order_transaction', 'print_count')) {
            Schema::table('shop_order_transaction', function (Blueprint $table) {
                $table->dropColumn('print_count');
            });
        }
    }
};
