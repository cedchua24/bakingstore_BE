<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalesAvailabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->integer('ordering');
            $table->string('tags')->nullable();
        });

        Schema::create('warehouse', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_name');
        });

        Schema::create('branch_stock_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('product_name');
            $table->double('price');
            $table->double('sale_price');
            $table->integer('stock');
            $table->integer('stock_pc');
            $table->integer('quantity');
            $table->integer('weight');
            $table->string('packaging');
            $table->string('variation');
            $table->boolean('disabled')->default(false);
        });

        Schema::create('mark_up_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_stock_transaction_id')->nullable();
            $table->string('business_type');
            $table->double('price');
            $table->string('mark_up_option');
            $table->double('mark_up_price');
            $table->double('new_price');
            $table->double('profit');
            $table->boolean('status')->default(true);
        });

        Schema::create('order_supplier_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->date('order_date');
        });

        Schema::create('order_supplier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_supplier_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->double('price');
            $table->integer('quantity');
            $table->string('variation');
        });

        DB::table('category')->insert([
            'id' => 1,
            'category_name' => 'Syrup',
            'ordering' => 1,
            'tags' => 'coffee',
        ]);

        DB::table('products')->insert([
            'id' => 1,
            'category_id' => 1,
            'product_name' => 'Easy Vanilla Syrup',
            'price' => 900.49,
            'sale_price' => 2280.49,
            'stock' => 2,
            'stock_pc' => 100,
            'quantity' => 50,
            'weight' => 2500,
            'packaging' => 'Box',
            'variation' => 'WHOLESALE',
            'disabled' => 0,
        ]);

        DB::table('mark_up_product')->insert([
            [
                'id' => 1,
                'product_id' => 1,
                'branch_stock_transaction_id' => null,
                'business_type' => 'WHOLESALE',
                'price' => 900.0,
                'mark_up_option' => 'AMOUNT',
                'mark_up_price' => 1380.49,
                'new_price' => 2280.49,
                'profit' => 1380.49,
                'status' => 1,
            ],
            [
                'id' => 2,
                'product_id' => 1,
                'branch_stock_transaction_id' => null,
                'business_type' => 'RETAIL',
                'price' => 18.0,
                'mark_up_option' => 'AMOUNT',
                'mark_up_price' => 27.5,
                'new_price' => 45.5,
                'profit' => 27.5,
                'status' => 1,
            ],
        ]);

        DB::table('order_supplier_transaction')->insert([
            'id' => 1,
            'status' => 'COMPLETED',
            'order_date' => '2026-10-30',
        ]);

        DB::table('order_supplier')->insert([
            'id' => 1,
            'order_supplier_transaction_id' => 1,
            'product_id' => 1,
            'price' => 900.49,
            'quantity' => 2,
            'variation' => 'WHOLESALE',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('order_supplier');
        Schema::dropIfExists('order_supplier_transaction');
        Schema::dropIfExists('mark_up_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('branch_stock_transaction');
        Schema::dropIfExists('warehouse');
        Schema::dropIfExists('category');

        parent::tearDown();
    }

    public function test_updated_whole_number_price_does_not_block_sales(): void
    {
        $response = $this->getJson('/api/markUpPrice/salesAvailability')
            ->assertOk()
            ->assertJsonCount(2);

        foreach ($response->json() as $row) {
            $this->assertFalse($row['price_update_required']);
            $this->assertFalse($row['sale_blocked']);
            $this->assertSame(0, $row['disabled']);
            $this->assertNull($row['sale_block_reason']);
            $this->assertSame(900, $row['product_price']);
            $this->assertSame(900, $row['completed_capital_price']);
            $this->assertSame(900, $row['latest_received_order']['price']);
        }
    }

    public function test_real_whole_number_cost_change_still_blocks_new_stock(): void
    {
        DB::table('products')->where('id', 1)->update(['price' => 901.0]);
        DB::table('order_supplier')->where('id', 1)->update(['price' => 901.0]);

        $response = $this->getJson('/api/markUpPrice/salesAvailability')
            ->assertOk();

        foreach ($response->json() as $row) {
            $this->assertTrue($row['price_update_required']);
            $this->assertTrue($row['sale_blocked']);
            $this->assertSame(1, $row['disabled']);
            $this->assertSame(
                'Update the selling price before selling newly received stock.',
                $row['sale_block_reason']
            );
        }
    }
}
