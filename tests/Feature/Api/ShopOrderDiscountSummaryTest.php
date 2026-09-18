<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopOrderDiscountSummaryTest extends TestCase
{
    public function test_summary_filters_orders_and_includes_both_date_boundaries(): void
    {
        Schema::create('shop_order_transaction', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('shop_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_transaction_id');
            $table->string('discount')->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('shop_order_total_price', 12, 2);
            $table->timestamps();
        });

        Schema::create('discount', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_order_id');
            $table->decimal('loss_amount', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2);
        });

        foreach ([
            ['SALE', -10.25, 100.50, '2026-09-01 00:00:00'],
            ['SALE', -5.50, 50.25, '2026-09-19 23:59:59'],
            [null, -20, 200, '2026-09-10 12:00:00'],
            ['SALE', 0, 200, '2026-09-10 12:00:00'],
            ['SALE', 20, 200, '2026-09-10 12:00:00'],
            ['SALE', null, 200, '2026-09-10 12:00:00'],
            ['SALE', -20, 200, '2026-08-31 23:59:59'],
            ['SALE', -20, 200, '2026-09-20 00:00:00'],
        ] as [$discount, $amount, $price, $transactionDate]) {
            $transactionId = DB::table('shop_order_transaction')->insertGetId([
                'date' => substr($transactionDate, 0, 10),
                'created_at' => '2026-08-01 12:00:00',
            ]);

            $orderId = DB::table('shop_order')->insertGetId([
                'shop_transaction_id' => $transactionId,
                'discount' => $discount,
                'discount_amount' => 100,
                'shop_order_total_price' => $price,
                // Creation dates deliberately differ from the transaction date.
                'created_at' => '2026-09-10 12:00:00',
            ]);

            DB::table('discount')->insert([
                'shop_order_id' => $orderId,
                'loss_amount' => $amount,
                'discount_amount' => 100,
            ]);
        }

        $this->getJson('/api/shopOrder/fetchDiscountSummary?date_from=2026-09-01&date_to=2026-09-19')
            ->assertOk()
            ->assertExactJson(['loss_amount' => -35.75, 'shop_order_total_price' => 350.75]);

        $this->getJson('/api/shopOrder/fetchDiscountSummary?date_from=2026-10-01&date_to=2026-10-01')
            ->assertOk()
            ->assertExactJson(['loss_amount' => 0, 'shop_order_total_price' => 0]);
    }

    public function test_dates_are_required_valid_and_ordered(): void
    {
        $this->getJson('/api/shopOrder/fetchDiscountSummary')
            ->assertUnprocessable()->assertJsonValidationErrors(['date_from', 'date_to']);

        $this->getJson('/api/shopOrder/fetchDiscountSummary?date_from=invalid&date_to=2026-09-19')
            ->assertUnprocessable()->assertJsonValidationErrors('date_from');

        $this->getJson('/api/shopOrder/fetchDiscountSummary?date_from=2026-09-19&date_to=2026-09-01')
            ->assertUnprocessable()->assertJsonValidationErrors('date_to');
    }
}
