<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OnlineShopOrderOverpaymentListTest extends TestCase
{
    public function test_it_lists_only_overpaid_online_shop_transactions(): void
    {
        $this->createTables();

        $onlineShopId = DB::table('shop')->insertGetId(['shop_type_id' => 3]);
        $physicalShopId = DB::table('shop')->insertGetId(['shop_type_id' => 1]);

        $overpaidId = $this->insertTransaction($onlineShopId, '2026-10-21', 3360);
        $this->insertPayments($overpaidId, [3000, 1200]);

        $equalId = $this->insertTransaction($onlineShopId, '2026-10-20', 1000);
        $this->insertPayments($equalId, [1000]);

        $underpaidId = $this->insertTransaction($onlineShopId, '2026-10-19', 1000);
        $this->insertPayments($underpaidId, [900]);

        $physicalShopTransactionId = $this->insertTransaction($physicalShopId, '2026-10-18', 500);
        $this->insertPayments($physicalShopTransactionId, [1000]);

        $response = $this->postJson(
            '/api/shopOrderTransaction/fetchOnlineShopOrderRefundTransactionListV2'
        );

        $response->assertOk()
            ->assertJsonPath('total_count', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', $overpaidId)
            ->assertJsonPath('data.0.date', '2026-10-21')
            ->assertJsonPath('data.0.total_amount', 3360)
            ->assertJsonPath('data.0.total_account', 4200)
            ->assertJsonPath('data.0.refund_amount', 840);
    }

    private function createTables(): void
    {
        Schema::create('shop', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('shop_type_id');
        });

        Schema::create('shop_order_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->date('date');
            $table->decimal('shop_order_transaction_total_price', 14, 2);
        });

        Schema::create('mode_of_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_order_transaction_id');
            $table->decimal('amount', 14, 2);
        });
    }

    private function insertTransaction(int $shopId, string $date, float $total): int
    {
        return DB::table('shop_order_transaction')->insertGetId([
            'shop_id' => $shopId,
            'date' => $date,
            'shop_order_transaction_total_price' => $total,
        ]);
    }

    private function insertPayments(int $transactionId, array $amounts): void
    {
        foreach ($amounts as $amount) {
            DB::table('mode_of_payment')->insert([
                'shop_order_transaction_id' => $transactionId,
                'amount' => $amount,
            ]);
        }
    }
}
