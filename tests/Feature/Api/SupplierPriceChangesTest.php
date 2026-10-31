<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplierPriceChangesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->double('price');
            $table->double('sale_price');
            $table->integer('stock');
            $table->integer('stock_pc');
            $table->integer('quantity');
            $table->string('packaging');
            $table->string('variation');
            $table->boolean('disabled')->default(false);
        });

        Schema::create('mark_up_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('business_type');
            $table->double('price');
            $table->string('mark_up_option');
            $table->double('mark_up_price');
            $table->double('new_price');
            $table->double('profit');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
        });

        Schema::create('order_supplier_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('status');
            $table->date('order_date');
            $table->date('send_date')->nullable();
        });

        Schema::create('order_supplier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_supplier_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->double('price');
            $table->integer('quantity');
            $table->string('variation');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('order_supplier');
        Schema::dropIfExists('order_supplier_transaction');
        Schema::dropIfExists('supplier');
        Schema::dropIfExists('mark_up_product');
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    public function test_decimal_product_price_that_rounds_to_markup_price_is_unchanged(): void
    {
        $this->insertProduct(900.49);
        $this->insertMarkup('WHOLESALE', 900.0, 600.0, 1500.0, 600.0);

        $this->getJson('/api/markUpPrice/supplierPriceChanges?include_unchanged=0')
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_all_monetary_values_are_returned_as_whole_numbers(): void
    {
        $this->insertProduct(900.5, 1500.5);
        $this->insertMarkup('WHOLESALE', 900.4, 600.5, 1500.5, 600.5);
        $this->insertMarkup('RETAIL', 18.4, 27.5, 45.5, 27.5);

        DB::table('supplier')->insert([
            'id' => 1,
            'supplier_name' => 'Test Supplier',
        ]);

        DB::table('order_supplier_transaction')->insert([
            [
                'id' => 1,
                'supplier_id' => 1,
                'status' => 'COMPLETED',
                'order_date' => '2026-10-01',
                'send_date' => '2026-10-02',
            ],
            [
                'id' => 2,
                'supplier_id' => 1,
                'status' => 'PENDING',
                'order_date' => '2026-10-03',
                'send_date' => null,
            ],
        ]);

        DB::table('order_supplier')->insert([
            [
                'id' => 1,
                'order_supplier_transaction_id' => 1,
                'product_id' => 1,
                'price' => 900.5,
                'quantity' => 3,
                'variation' => 'WHOLESALE',
            ],
            [
                'id' => 2,
                'order_supplier_transaction_id' => 2,
                'product_id' => 1,
                'price' => 18.5,
                'quantity' => 100,
                'variation' => 'RETAIL',
            ],
        ]);

        $response = $this->getJson(
            '/api/markUpPrice/supplierPriceChanges?include_unchanged=0'
        )->assertOk()->assertJsonPath('count', 2);

        $rows = collect($response->json('data'))->keyBy('business_type');
        $wholesale = $rows->get('WHOLESALE');
        $retail = $rows->get('RETAIL');

        $this->assertSame(901, $wholesale['product_price']);
        $this->assertSame(1501, $wholesale['current_sale_price']);
        $this->assertSame(900, $wholesale['mark_up_product_price']);
        $this->assertSame(601, $wholesale['mark_up_price']);
        $this->assertSame(1501, $wholesale['new_price']);
        $this->assertSame(601, $wholesale['profit']);
        $this->assertSame(1, $wholesale['price_difference']);
        $this->assertSame(901, $wholesale['latest_received_order']['price']);
        $this->assertSame(
            901,
            $wholesale['latest_received_order']['completed_price_per_pack']
        );
        $this->assertSame(19, $wholesale['incoming_orders'][0]['upcoming_price']);
        $this->assertSame(
            925,
            $wholesale['incoming_orders'][0]['upcoming_price_per_pack']
        );

        $this->assertSame(18, $retail['mark_up_product_price']);
        $this->assertSame(0, $retail['price_difference']);
        $this->assertIsFloat($wholesale['price_change_percent']);
        $this->assertSame(2, $wholesale['incoming_orders'][0]['incoming_stock']);
    }

    private function insertProduct(float $price, float $salePrice = 1500.0): void
    {
        DB::table('products')->insert([
            'id' => 1,
            'product_name' => 'Test Product',
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => 3,
            'stock_pc' => 165,
            'quantity' => 50,
            'packaging' => 'Box',
            'variation' => 'WHOLESALE',
            'disabled' => 0,
        ]);
    }

    private function insertMarkup(
        string $businessType,
        float $price,
        float $markupPrice,
        float $newPrice,
        float $profit
    ): void {
        DB::table('mark_up_product')->insert([
            'product_id' => 1,
            'business_type' => $businessType,
            'price' => $price,
            'mark_up_option' => 'AMOUNT',
            'mark_up_price' => $markupPrice,
            'new_price' => $newPrice,
            'profit' => $profit,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
