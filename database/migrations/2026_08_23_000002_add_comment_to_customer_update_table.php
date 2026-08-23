<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('customer_update', 'comment')) {
            Schema::table('customer_update', function (Blueprint $table) {
                $table->text('comment')->nullable()->after('promo');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('customer_update', 'comment')) {
            Schema::table('customer_update', function (Blueprint $table) {
                $table->dropColumn('comment');
            });
        }
    }
};
