<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_update', function (Blueprint $table) {
            $table->date('last_order_date')->nullable()->after('user_id');
        });

        // Best-effort history for old follow-ups. Use an order strictly before
        // the follow-up date because legacy timestamps often have no time part,
        // making same-day order/update sequencing impossible to reconstruct.
        DB::table('customer_update')
            ->select('id', 'customer_id', 'created_at')
            ->orderBy('id')
            ->chunkById(500, function ($updates) {
                foreach ($updates as $update) {
                    $followUpDate = substr((string) $update->created_at, 0, 10);
                    $lastOrderDate = DB::table('shop_order_transaction')
                        ->where('requestor', $update->customer_id)
                        ->where('checker', 0)
                        ->whereDate('date', '<', $followUpDate)
                        ->max('date');

                    if ($lastOrderDate !== null) {
                        DB::table('customer_update')
                            ->where('id', $update->id)
                            ->update(['last_order_date' => $lastOrderDate]);
                    }
                }
            });
    }

    public function down()
    {
        Schema::table('customer_update', function (Blueprint $table) {
            $table->dropColumn('last_order_date');
        });
    }
};
