<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductUpdateV2EmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            foreach (['category_id', 'brand_id', 'product_name', 'price', 'sale_price', 'stock', 'stock_pc', 'weight', 'quantity', 'variation', 'packaging', 'stock_warning', 'stock_warning_type', 'disabled', 'note'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('stock_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['product_id', 'stock_reason', 'stock_type', 'stock', 'pack', 'type', 'total_stock', 'price', 'total_cost'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('name')->nullable();
        });
        Schema::create('email', function (Blueprint $table) {
            $table->string('email');
            $table->integer('status');
        });
        DB::table('products')->insert(['id' => 1, 'stock_pc' => 48]);
        DB::table('users')->insert(['id' => 7, 'firstname' => 'Ana', 'lastname' => 'Chua', 'name' => 'Fallback Name']);
    }

    private function payload(): array
    {
        return [
            'product_name' => 'Jolly Cow', 'price' => 2218.10, 'quantity' => 48,
            'stock' => 1, 'newStocks' => -1, 'pack' => 'Pc', 'type' => 'INVENTORY',
            'stock_reason' => "Damaged <package>\nCount verified", 'user_id' => 7,
            'email_price' => 999999, 'email_total_cost' => 999999,
        ];
    }

    public function test_v2_email_contains_user_type_reason_and_calculated_costs(): void
    {
        Mail::shouldReceive('send')->once()->withArgs(function ($view, $data, $callback) {
            $this->assertSame('modify_stock_v2', $view);
            $this->assertSame('Fallback Name', $data['modifiedBy']);
            $html = view($view, $data)->render();
            foreach (['Fallback Name', 'INVENTORY', 'Damaged &lt;package&gt;', 'Count verified', 'STOCK REDUCED', '-1', 'Pc', '46.21', '-46.21'] as $text) {
                $this->assertStringContainsString($text, $html);
            }
            $this->assertStringNotContainsString('999,999', $html);
            return true;
        });
        $this->putJson('/api/products/updateV2/1', $this->payload())->assertOk();
        $this->assertDatabaseHas('products', ['id' => 1, 'stock_pc' => 47]);
        $this->assertDatabaseHas('stock_order', ['type' => 'INVENTORY', 'stock' => -1, 'user_id' => 7]);
    }

    public function test_name_fallback_and_positive_adjustment(): void
    {
        DB::table('users')->where('id', 7)->update(['firstname' => null, 'lastname' => null]);
        Mail::shouldReceive('send')->once()->withArgs(function ($view, $data, $callback) {
            $html = view($view, $data)->render();
            $this->assertStringContainsString('Fallback Name', $html);
            $this->assertStringContainsString('STOCK ADDED', $html);
            $this->assertStringContainsString('+2', $html);
            return true;
        });
        $this->patchJson('/api/products/updateV2/1', array_merge($this->payload(), ['newStocks' => 2]))->assertOk();
    }

    public function test_unknown_user_is_rejected_before_stock_changes(): void
    {
        Mail::shouldReceive('send')->never();
        $this->putJson('/api/products/updateV2/1', array_merge($this->payload(), ['user_id' => 999]))
            ->assertUnprocessable()->assertJsonValidationErrors('user_id');
        $this->assertDatabaseHas('products', ['id' => 1, 'stock_pc' => 48]);
    }

    public function test_original_endpoint_keeps_its_email_template(): void
    {
        Mail::shouldReceive('send')->once()->withArgs(function ($view, $data, $callback) {
            return $view === 'modify_stock' && !array_key_exists('modifiedBy', $data);
        });
        $this->putJson('/api/products/1', $this->payload())->assertOk();
        $this->assertDatabaseHas('stock_order', ['user_id' => 7]);
    }

    public function test_direct_stock_order_insert_and_history_reads_include_user_id(): void
    {
        $controller = app(\App\Http\Controllers\StockOrderController::class);
        foreach ([7, null] as $userId) {
            $response = $controller->store(new \Illuminate\Http\Request([
                'product_id' => 1, 'stock' => 2, 'pack' => 'Pc',
                'stock_type' => 'Add', 'total_stock' => 50, 'user_id' => $userId,
            ]));
            $this->assertEquals($userId, $response->getData()->user_id);
        }
        $this->assertEquals([7, null], array_column($controller->index()->getData(true), 'user_id'));
        $rows = $controller->index()->getData(true);
        $this->assertArrayNotHasKey('firstname', $rows[0]);
        $this->assertArrayNotHasKey('lastname', $rows[0]);
        $this->assertSame('Fallback Name', $rows[0]['user_name']);
        $this->assertArrayNotHasKey('firstname', $rows[0]);
        $this->assertArrayNotHasKey('lastname', $rows[0]);
        $this->assertEquals([7, null], array_column($controller->fetchById(1)->getData(true), 'user_id'));
        $order = \App\Models\StockOrder::first();
        $this->assertEquals(7, $controller->show($order)->getData()[0]->user_id);
        $history = app(\App\Http\Controllers\ProductController::class)->fetchById(1)->getData(true);
        $this->assertEquals([null, 7], array_column($history, 'user_id'));
    }

    public function test_legacy_zero_user_and_name_only_schema_are_supported(): void
    {
        Schema::drop('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('users')->insert(['id' => 7, 'name' => 'Ana Chua']);
        DB::table('stock_order')->insert([
            ['product_id' => 1, 'user_id' => 7],
            ['product_id' => 1, 'user_id' => 0],
        ]);
        $rows = app(\App\Http\Controllers\StockOrderController::class)->index()->getData(true);
        $this->assertCount(2, $rows);
        $this->assertSame('Ana Chua', $rows[0]['user_name']);
        $this->assertArrayNotHasKey('firstname', $rows[0]);
        $this->assertArrayNotHasKey('lastname', $rows[0]);
        $this->assertEquals(0, $rows[1]['user_id']);
        $this->assertNull($rows[1]['user_name']);
        $this->assertArrayNotHasKey('firstname', $rows[0]);
        $this->assertArrayNotHasKey('lastname', $rows[0]);
    }

    public function test_spoilage_and_supplier_return_inserts_record_the_user(): void
    {
        Schema::create('spoilage', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_order_id');
            $table->string('reason')->nullable();
            $table->double('total_cost');
            $table->timestamps();
        });
        Schema::create('return_to_seller', function (Blueprint $table) {
            $table->id();
            foreach (['product_id', 'type', 'quantity', 'reason', 'supplier_id', 'status', 'price', 'total_cost'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        DB::table('products')->where('id', 1)->update(['price' => 48, 'quantity' => 48, 'stock' => 1]);
        $payload = array_merge($this->payload(), ['id' => 1, 'reason' => 'Damaged', 'supplier_id' => 1]);
        $spoilage = app(\App\Http\Controllers\SpoilageController::class);
        $spoilage->store(new \Illuminate\Http\Request($payload));
        $this->assertEquals(7, $spoilage->fetchById(1)->getData()->user_id);
        $returns = app(\App\Http\Controllers\ReturnToSellerController::class);
        $returns->store(new \Illuminate\Http\Request($payload));
        $return = \App\Models\ReturnToSeller::first();
        $returns->update(new \Illuminate\Http\Request([
            'id' => $return->id, 'product_id' => 1, 'status' => 2,
            'quantity' => -1, 'type' => 'Pc', 'user_id' => 7,
        ]), $return);
        $this->assertEquals([7, 7, 7], DB::table('stock_order')->orderBy('id')->pluck('user_id')->all());
    }

    public function test_user_id_migration_preserves_existing_records(): void
    {
        Schema::drop('stock_order');
        Schema::create('stock_order', function (Blueprint $table) { $table->id(); });
        DB::table('stock_order')->insert(['id' => 1]);
        $migration = require database_path('migrations/2026_09_18_000000_add_user_id_to_stock_order_table.php');
        $migration->up();
        $this->assertNull(DB::table('stock_order')->first()->user_id);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('stock_order', 'user_id'));
        $this->assertSame(1, DB::table('stock_order')->count());
    }
}
