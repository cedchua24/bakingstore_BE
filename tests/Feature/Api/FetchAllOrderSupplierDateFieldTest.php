<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FetchAllOrderSupplierDateFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
        });

        Schema::create('order_supplier_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->boolean('payment_status');
            $table->string('invoice_number')->nullable();
            $table->boolean('withTax')->default(false);
            $table->decimal('total_transaction_price', 10, 2);
            $table->date('order_date');
            $table->date('send_date')->nullable();
            $table->text('note')->nullable();
            $table->boolean('approval')->nullable();
            $table->string('approval_status')->nullable();
            $table->string('requestor')->nullable();
            $table->string('status')->nullable();
            $table->string('stock_status')->nullable();
            $table->timestamps();
        });

        Schema::create('bank', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
        });

        Schema::create('payment_type_po', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id');
            $table->string('account_name');
            $table->string('account_number')->nullable();
            $table->string('account_description')->nullable();
        });

        Schema::create('mode_of_payment_po', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_type_po_id');
            $table->unsignedBigInteger('order_supplier_transaction_id');
            $table->decimal('amount', 10, 2);
        });

        DB::table('supplier')->insert(['id' => 1, 'supplier_name' => 'Test Supplier']);
        DB::table('bank')->insert(['id' => 1, 'bank_name' => 'Test Bank']);
        DB::table('payment_type_po')->insert([
            'id' => 1,
            'bank_id' => 1,
            'account_name' => 'Cash',
        ]);

        $this->insertTransaction(1, '2026-10-01', '2026-11-04 23:59:59', 0, 100);
        $this->insertTransaction(2, '2026-11-04', '2026-10-01 08:00:00', 1, 200);
    }

    public function test_it_can_filter_the_list_and_totals_by_updated_at(): void
    {
        $this->postJson('/api/orderSupplierTransaction/fetchAllOrderSupplier', [
            'dateFrom' => '2026-11-04',
            'dateTo' => '2026-11-04',
            'dateField' => 'updated_at',
        ])->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.updated_at', '2026-11-04')
            ->assertJsonPath('total_balance.total_count', 1)
            ->assertJsonPath('total_paid.total_count', 0);
    }

    public function test_it_defaults_to_order_date_and_rejects_an_unknown_date_field(): void
    {
        $this->postJson('/api/orderSupplierTransaction/fetchAllOrderSupplier', [
            'dateFrom' => '2026-11-04',
            'dateTo' => '2026-11-04',
        ])->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 2)
            ->assertJsonPath('total_balance.total_count', 0)
            ->assertJsonPath('total_paid.total_count', 1);

        $this->postJson('/api/orderSupplierTransaction/fetchAllOrderSupplier', [
            'dateFrom' => '2026-11-04',
            'dateTo' => '2026-11-04',
            'dateField' => 'created_at',
        ])->assertUnprocessable()->assertJsonValidationErrors('dateField');
    }

    private function insertTransaction(
        int $id,
        string $orderDate,
        string $updatedAt,
        int $paymentStatus,
        float $total
    ): void {
        DB::table('order_supplier_transaction')->insert([
            'id' => $id,
            'supplier_id' => 1,
            'payment_status' => $paymentStatus,
            'total_transaction_price' => $total,
            'order_date' => $orderDate,
            'created_at' => $updatedAt,
            'updated_at' => $updatedAt,
        ]);
    }
}
