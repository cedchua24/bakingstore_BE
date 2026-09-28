<?php

namespace Tests\Feature\Api;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OnlineShopMonthlySalesForecastTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_returns_twelve_months_and_forecasts_future_months_using_linear_trend(): void
    {
        Carbon::setTestNow('2026-02-28 12:00:00');
        $this->createTables();

        $onlineShopId = DB::table('shop')->insertGetId(['shop_type_id' => 3]);
        $physicalShopId = DB::table('shop')->insertGetId(['shop_type_id' => 1]);

        $this->insertTransaction($onlineShopId, '2026-01-10', 1000000, 100000, 1);
        $this->insertTransaction($onlineShopId, '2026-02-10', 2000000, 200000, 1);
        $this->insertTransaction($onlineShopId, '2026-02-11', 9000000, 900000, 0);
        $this->insertTransaction($physicalShopId, '2026-02-12', 9000000, 900000, 1);

        $response = $this->postJson(
            '/api/shopOrderTransaction/fetchOnlineShopMonthlySalesForecast',
            ['year' => 2026]
        );

        $response->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('data.0.month', 'January')
            ->assertJsonPath('data.0.type', 'actual')
            ->assertJsonPath('data.0.total_sales', 1000000)
            ->assertJsonPath('data.1.total_sales', 2000000)
            ->assertJsonPath('data.2.month', 'March')
            ->assertJsonPath('data.2.type', 'forecast')
            ->assertJsonPath('data.2.total_sales', 3000000)
            ->assertJsonPath('data.3.total_sales', 4000000)
            ->assertJsonPath('summary.actual_sales', 3000000)
            ->assertJsonPath('summary.forecast_sales', 75000000)
            ->assertJsonPath('summary.projected_annual_sales', 78000000);
    }

    public function test_it_validates_the_year_and_does_not_forecast_historical_years(): void
    {
        Carbon::setTestNow('2026-09-29 12:00:00');
        $this->createTables();

        $this->postJson('/api/shopOrderTransaction/fetchOnlineShopMonthlySalesForecast', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('year');

        $this->postJson('/api/shopOrderTransaction/fetchOnlineShopMonthlySalesForecast', ['year' => 2027])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('year');

        $response = $this->postJson(
            '/api/shopOrderTransaction/fetchOnlineShopMonthlySalesForecast',
            ['year' => 2025]
        );

        $response->assertOk()->assertJsonCount(12, 'data');
        $this->assertSame([], collect($response->json('data'))->where('type', 'forecast')->values()->all());
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
            $table->unsignedInteger('status');
            $table->decimal('shop_order_transaction_total_price', 14, 2);
            $table->decimal('profit', 14, 2);
            $table->decimal('total_cash', 14, 2)->default(0);
            $table->decimal('total_online', 14, 2)->default(0);
        });
    }

    private function insertTransaction(int $shopId, string $date, float $sales, float $profit, int $status): void
    {
        DB::table('shop_order_transaction')->insert([
            'shop_id' => $shopId,
            'date' => $date,
            'status' => $status,
            'shop_order_transaction_total_price' => $sales,
            'profit' => $profit,
            'total_cash' => $sales,
            'total_online' => 0,
        ]);
    }
}
