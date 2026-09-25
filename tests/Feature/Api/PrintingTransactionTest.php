<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrintingTransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('store_name')->nullable();
        });
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('store_name')->nullable();
        });
        Schema::create('shop_order_transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('is_pickup')->default(0);
            $table->integer('status')->default(0);
            $table->decimal('shop_order_transaction_total_price', 12, 2)->default(0);
            $table->unsignedBigInteger('requestor')->nullable();
        });
        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->string('tags')->nullable();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
        });
        Schema::create('shop_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('shop_order_total_price', 12, 2);
        });
        Schema::create('mode_of_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_order_transaction_id');
            $table->integer('payment_type_id');
            $table->decimal('amount', 12, 2);
            $table->integer('is_paid')->default(1);
            $table->timestamps();
        });
        Schema::create('payment_type_po', function (Blueprint $table) {
            $table->id();
            foreach (['payment_term_id', 'bank_id', 'due_date', 'buffer_days', 'statement_date', 'status', 'is_supplier', 'is_customer'] as $field) {
                $table->integer($field)->nullable();
            }
            foreach (['account_number', 'account_name', 'account_description'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['credit_limit', 'total_balance_due', 'balance'] as $field) {
                $table->decimal($field, 12, 2)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('bank', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('payment_term', function (Blueprint $table) {
            $table->id();
            $table->string('payment_term');
            $table->integer('status')->default(1);
        });
        (require database_path('migrations/2026_09_21_000001_create_printing_transactions.php'))->up();
        DB::table('users')->insert(['id' => 1, 'name' => 'Coordinator', 'store_name' => 'Coordinator Store']);
        DB::table('shop_order_transaction')->insert(['id' => 1]);
    }

    protected function tearDown(): void
    {
        (require database_path('migrations/2026_09_21_000001_create_printing_transactions.php'))->down();
        Schema::dropIfExists('shop_order');
        Schema::dropIfExists('products');
        Schema::dropIfExists('category');
        Schema::dropIfExists('shop_order_transaction');
        Schema::dropIfExists('users');
        Schema::dropIfExists('customer');
        foreach (['mode_of_payment', 'payment_type_po', 'bank', 'payment_term'] as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    private function payload(): array
    {
        return [
            'shop_order_transaction_id' => 1,
            'order_coordinator_id' => 1,
            'logo' => 'NEW',
            'sales_channel' => 'FACEBOOK',
            'order_date' => '2026-09-21',
        ];
    }

    public function test_optional_status_and_inclusive_date_range_filters(): void
    {
        foreach ([['2026-09-20', 'PENDING'], ['2026-09-21', 'PENDING'], ['2026-09-22', 'COMPLETED'], ['2026-09-23', 'PENDING']] as [$date, $status]) {
            $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
                'order_date' => $date, 'order_status' => $status,
            ]))->assertCreated();
        }
        $this->getJson('/api/printingTransaction')->assertOk()->assertJsonCount(4);
        $this->getJson('/api/printingTransaction?date_from=2026-09-21&date_to=2026-09-22')
            ->assertOk()->assertJsonCount(2);
        $this->getJson('/api/printingTransaction?status=PENDING&date_from=2026-09-21&date_to=2026-09-22')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.order_date', '2026-09-21');
        $this->getJson('/api/printingTransaction?date_from=2026-09-22')->assertOk()->assertJsonCount(2);
        $this->getJson('/api/printingTransaction?date_to=2026-09-21')->assertOk()->assertJsonCount(2);
        $this->getJson('/api/printingTransaction?status=COMPLETED')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/printingTransaction?date_from=&date_to=&status=')->assertOk()->assertJsonCount(4);
        $this->getJson('/api/printingTransaction?date_from=2026-09-23&date_to=2026-09-21')
            ->assertUnprocessable()->assertJsonValidationErrors('date_to');
        $this->getJson('/api/printingTransaction?status=INVALID&date_from=bad')
            ->assertUnprocessable()->assertJsonValidationErrors(['status', 'date_from']);
        $this->getJson('/api/printingTransaction?status=PENDING&order_status=COMPLETED')
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_optional_shop_payment_status_and_pickup_filters(): void
    {
        DB::table('shop_order_transaction')->where('id', 1)->update(['status' => 2, 'is_pickup' => 1]);
        DB::table('shop_order_transaction')->insert(['id' => 2, 'status' => 0, 'is_pickup' => 0]);
        $this->postJson('/api/printingTransaction', $this->payload())->assertCreated();
        $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
            'shop_order_transaction_id' => 2,
        ]))->assertCreated();
        $this->getJson('/api/printingTransaction?status=PENDING&payment_status=2&is_pickup=1')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.shop_order_transaction_id', 1);
        $this->getJson('/api/printingTransaction?is_pickup=0')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.shop_order_transaction_id', 2);
        $this->getJson('/api/printingTransaction?payment_status=0')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.shop_order_transaction_id', 2);
        $this->getJson('/api/printingTransaction?payment_status=2&is_pickup=0')
            ->assertOk()->assertExactJson([]);
        $this->getJson('/api/printingTransaction?payment_status=&is_pickup=')
            ->assertOk()->assertJsonCount(2);
        $this->getJson('/api/printingTransaction?payment_status=invalid&is_pickup=2')
            ->assertUnprocessable()->assertJsonValidationErrors(['payment_status', 'is_pickup']);
    }

    public function test_optional_coordinator_filter(): void
    {
        DB::table('users')->insert(['id' => 2, 'name' => 'Other Coordinator']);
        $this->postJson('/api/printingTransaction', $this->payload())->assertCreated();
        $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
            'order_coordinator_id' => 2,
        ]))->assertCreated();
        $this->getJson('/api/printingTransaction?order_coordinator_id=2&status=PENDING')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.order_coordinator_id', 2);
        $this->getJson('/api/printingTransaction?order_coordinator_id=')
            ->assertOk()->assertJsonCount(2);
        $this->getJson('/api/printingTransaction?order_coordinator_id=999')
            ->assertOk()->assertExactJson([]);
        $this->getJson('/api/printingTransaction?order_coordinator_id=invalid')
            ->assertUnprocessable()->assertJsonValidationErrors('order_coordinator_id');
    }

    public function test_list_can_sort_by_order_date(): void
    {
        foreach (['2026-09-22', '2026-09-20', '2026-09-21'] as $date) {
            $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
                'order_date' => $date,
            ]))->assertCreated();
        }
        foreach (['asc' => ['2026-09-20', '2026-09-21', '2026-09-22'],
            'desc' => ['2026-09-22', '2026-09-21', '2026-09-20']] as $direction => $expected) {
            $response = $this->getJson('/api/printingTransaction?order_date_sort='.$direction)->assertOk();
            $this->assertSame($expected, array_column($response->json(), 'order_date'));
        }
        $this->getJson('/api/printingTransaction')->assertOk()->assertJsonPath('0.order_date', '2026-09-21');
        $this->getJson('/api/printingTransaction?order_date_sort=invalid')
            ->assertUnprocessable()->assertJsonValidationErrors('order_date_sort');
    }

    public function test_transaction_lifecycle_and_filters(): void
    {
        $id = $this->postJson('/api/printingTransaction', $this->payload())
            ->assertCreated()->assertJson([
                'plate' => false,
                'mock_up_status' => 'PENDING',
                'order_priority' => 'NORMAL',
                'order_status' => 'PENDING',
                'sent_date' => null,
                'received_date' => null,
            ])->json('id');

        $this->patchJson('/api/printingTransaction/'.$id, [
            'order_status' => 'COMPLETED', 'plate' => true, 'sent_date' => '2026-09-22',
        ])->assertOk()->assertJson([
            'order_status' => 'COMPLETED', 'plate' => true, 'logo' => 'NEW',
        ]);
        $this->putJson('/api/printingTransaction/'.$id, ['sent_date' => null])
            ->assertOk()->assertJsonPath('sent_date', null);
        $this->getJson('/api/printingTransaction?order_status=PENDING')->assertOk()->assertExactJson([]);
        $this->getJson('/api/printingTransaction?shop_order_transaction_id=1&order_status=COMPLETED')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.comments_count', 0)
            ->assertJsonPath('0.order_coordinator_name', 'Coordinator')
            ->assertJsonPath('0.order_coordinator_id', 1)
            ->assertJsonPath('0.id', $id);
        $this->getJson('/api/printingTransaction/'.$id)->assertOk()
            ->assertJsonPath('order_coordinator.name', 'Coordinator')
            ->assertJsonPath('shop_order_transaction.id', 1);
        $this->deleteJson('/api/printingTransaction/'.$id)->assertNoContent();
        $this->getJson('/api/printingTransaction/'.$id)->assertNotFound();
    }

    public function test_fetch_by_shop_order_transaction_id(): void
    {
        DB::table('shop_order_transaction')->insert(['id' => 2]);
        $firstId = $this->postJson('/api/printingTransaction', $this->payload())->assertCreated()->json('id');
        $secondId = $this->postJson('/api/printingTransaction', $this->payload())->assertCreated()->json('id');
        $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
            'shop_order_transaction_id' => 2,
        ]))->assertCreated();
        $this->postJson('/api/printingTransactionComment', [
            'printing_transaction_id' => $secondId, 'user_id' => 1, 'comment' => 'Review logo.',
        ])->assertCreated();

        $this->getJson('/api/printingTransaction/fetchByShopOrderTransactionId/1')
            ->assertOk()->assertJsonCount(2)
            ->assertJsonPath('0.id', $secondId)
            ->assertJsonPath('1.id', $firstId)
            ->assertJsonPath('0.order_coordinator.name', 'Coordinator')
            ->assertJsonPath('0.comments.0.comment', 'Review logo.')
            ->assertJsonPath('0.comments.0.user.name', 'Coordinator');
        $this->getJson('/api/printingTransaction/fetchByShopOrderTransactionId/999')
            ->assertOk()->assertExactJson([]);
        $this->getJson('/api/printingTransaction/fetchByShopOrderTransactionId/invalid')
            ->assertNotFound();
    }

    public function test_comments_lifecycle_and_cascade_delete(): void
    {
        $id = $this->postJson('/api/printingTransaction', $this->payload())->json('id');
        $otherId = $this->postJson('/api/printingTransaction', $this->payload())->json('id');
        $payload = ['printing_transaction_id' => $id, 'user_id' => 1, 'comment' => 'Please review the logo.'];
        $commentId = $this->postJson('/api/printingTransactionComment', $payload)
            ->assertCreated()->assertJsonPath('user.name', 'Coordinator')->json('id');
        $this->getJson('/api/printingTransactionComment?printing_transaction_id='.$id)
            ->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.user.id', 1)->assertJsonPath('0.user.name', 'Coordinator');
        $this->getJson('/api/printingTransactionComment?printing_transaction_id='.$otherId)
            ->assertOk()->assertExactJson([]);
        $this->patchJson('/api/printingTransactionComment/'.$commentId, ['comment' => 'Approved.'])
            ->assertOk()->assertJsonPath('comment', 'Approved.');
        DB::table('users')->insert(['id' => 2, 'name' => 'Reviewer']);
        $this->patchJson('/api/printingTransactionComment/'.$commentId, ['user_id' => 2])
            ->assertOk()->assertJsonPath('user_id', 2)
            ->assertJsonPath('user.id', 2)->assertJsonPath('user.name', 'Reviewer');
        $this->getJson('/api/printingTransactionComment/'.$commentId)->assertOk()
            ->assertJsonPath('user.id', 2)->assertJsonPath('user.name', 'Reviewer');
        $this->getJson('/api/printingTransaction/'.$id)
            ->assertOk()->assertJsonPath('comments.0.comment', 'Approved.');
        $this->deleteJson('/api/printingTransactionComment/'.$commentId)->assertNoContent();
        $this->getJson('/api/printingTransactionComment/'.$commentId)->assertNotFound();
        $this->postJson('/api/printingTransactionComment', $payload)->assertCreated();
        $this->deleteJson('/api/printingTransaction/'.$id)->assertNoContent();
        $this->assertDatabaseCount('printing_transaction_comments', 0);
    }

    public function test_list_includes_customer_and_only_printing_item_totals(): void
    {
        DB::table('customer')->insert(['id' => 515, 'first_name' => 'Aalex', 'last_name' => 'Paraiso', 'store_name' => 'Customer Store']);
        DB::table('shop_order_transaction')->where('id', 1)->update(['user_id' => 1, 'requestor' => 515, 'status' => 2, 'is_pickup' => 1]);
        DB::table('shop_order_transaction')->insert(['id' => 2]);
        DB::table('category')->insert([
            ['id' => 1, 'tags' => 'printing'], ['id' => 2, 'tags' => 'baking'],
        ]);
        DB::table('products')->insert([
            ['id' => 1, 'category_id' => 1], ['id' => 2, 'category_id' => 2],
        ]);
        DB::table('shop_order')->insert([
            ['shop_transaction_id' => 1, 'product_id' => 1, 'shop_order_total_price' => 100.25],
            ['shop_transaction_id' => 1, 'product_id' => 1, 'shop_order_total_price' => 50.50],
            ['shop_transaction_id' => 1, 'product_id' => 2, 'shop_order_total_price' => 999],
            ['shop_transaction_id' => 2, 'product_id' => 2, 'shop_order_total_price' => 200],
        ]);
        $this->postJson('/api/printingTransaction', $this->payload())->assertCreated();
        $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
            'shop_order_transaction_id' => 2,
        ]))->assertCreated();

        $response = $this->getJson('/api/printingTransaction?shop_order_transaction_id=1')
            ->assertOk()->assertJsonCount(1)
            ->assertJsonPath('0.customer_name', 'Aalex Paraiso')
            ->assertJsonPath('0.payment_status', 2)
            ->assertJsonPath('0.store_name', 'Customer Store')
            ->assertJsonPath('0.is_pickup', 1)
            ->assertJsonPath('0.order_coordinator_name', 'Coordinator')
            ->assertJsonPath('0.tags', 'printing');
        $this->assertEquals(150.75, $response->json('0.shop_order_total_price'));
        $response = $this->getJson('/api/printingTransaction?shop_order_transaction_id=2')
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.tags', null)
            ->assertJsonPath('0.customer_name', null);
        $this->assertEquals(0, $response->json('0.shop_order_total_price'));
    }

    public function test_details_include_customer_and_all_order_items_with_products_and_categories(): void
    {
        DB::table('customer')->insert([
            'id' => 515, 'first_name' => 'Aalex', 'last_name' => 'Paraiso',
            'address' => '123 Sample Street', 'contact_number' => '09123456789',
            'store_name' => 'Sample Bakery',
        ]);
        DB::table('shop_order_transaction')->where('id', 1)->update(['requestor' => 515, 'is_pickup' => 1]);
        DB::table('category')->insert([
            ['id' => 1, 'tags' => 'printing'], ['id' => 2, 'tags' => 'baking'],
        ]);
        DB::table('products')->insert([
            ['id' => 1, 'category_id' => 1], ['id' => 2, 'category_id' => 2],
        ]);
        DB::table('shop_order')->insert([
            ['id' => 10, 'shop_transaction_id' => 1, 'product_id' => 1, 'shop_order_total_price' => 100],
            ['id' => 11, 'shop_transaction_id' => 1, 'product_id' => 2, 'shop_order_total_price' => 250],
            ['id' => 12, 'shop_transaction_id' => 999, 'product_id' => 1, 'shop_order_total_price' => 500],
        ]);
        $id = $this->postJson('/api/printingTransaction', $this->payload())->assertCreated()->json('id');
        $this->postJson('/api/printingTransactionComment', [
            'printing_transaction_id' => $id, 'user_id' => 1, 'comment' => 'Review details.',
        ])->assertCreated();

        $this->getJson('/api/printingTransaction/'.$id)->assertOk()
            ->assertJsonPath('id', $id)
            ->assertJsonPath('shop_order_transaction.customer.id', 515)
            ->assertJsonPath('shop_order_transaction.customer.first_name', 'Aalex')
            ->assertJsonPath('shop_order_transaction.is_pickup', 1)
            ->assertJsonPath('shop_order_transaction.customer.address', '123 Sample Street')
            ->assertJsonPath('shop_order_transaction.customer.contact_number', '09123456789')
            ->assertJsonPath('shop_order_transaction.customer.store_name', 'Sample Bakery')
            ->assertJsonCount(2, 'shop_order_transaction.shop_orders')
            ->assertJsonPath('shop_order_transaction.shop_orders.0.id', 10)
            ->assertJsonPath('shop_order_transaction.shop_orders.0.product.id', 1)
            ->assertJsonPath('shop_order_transaction.shop_orders.0.product.category.tags', 'printing')
            ->assertJsonPath('shop_order_transaction.shop_orders.1.product.category.tags', 'baking')
            ->assertJsonPath('order_coordinator.name', 'Coordinator')
            ->assertJsonPath('comments.0.comment', 'Review details.');
    }

    public function test_details_include_payment_history_and_totals_matching_v2(): void
    {
        DB::table('shop_order_transaction')->where('id', 1)->update(['shop_order_transaction_total_price' => 12600]);
        $id = $this->postJson('/api/printingTransaction', $this->payload())->assertCreated()->json('id');
        $this->getJson('/api/printingTransaction/'.$id)->assertOk()
            ->assertJsonPath('payment_history', [])->assertJsonPath('total_payment', 0)
            ->assertJsonPath('balance', 12600);
        DB::table('bank')->insert(['id' => 1, 'bank_name' => 'Test Bank']);
        DB::table('payment_term')->insert(['id' => 1, 'payment_term' => 'Cash']);
        DB::table('payment_type_po')->insert([
            'id' => 1, 'bank_id' => 1, 'payment_term_id' => 1, 'account_name' => 'Store - QC',
        ]);
        DB::table('mode_of_payment')->insert([
            ['shop_order_transaction_id' => 1, 'payment_type_id' => 1, 'amount' => 2000, 'is_paid' => 1],
            ['shop_order_transaction_id' => 1, 'payment_type_id' => 1, 'amount' => 100, 'is_paid' => 0],
            ['shop_order_transaction_id' => 999, 'payment_type_id' => 1, 'amount' => 9999, 'is_paid' => 1],
        ]);
        $detail = $this->getJson('/api/printingTransaction/'.$id)->assertOk()
            ->assertJsonCount(2, 'payment_history')
            ->assertJsonPath('payment_history.0.account_name', 'Store - QC')
            ->assertJsonPath('payment_history.0.bank_name', 'Test Bank')
            ->assertJsonPath('payment_history.0.payment_term', 'Cash')
            ->assertJsonPath('total_payment', 2100)->assertJsonPath('balance', 10500);
        $v2 = $this->getJson('/api/modeOfPayment/fetchPaymentTypeByShopTransactionIdV2/1')->assertOk();
        $this->assertEquals($v2->json('data'), $detail->json('payment_history'));
        $this->assertEquals($v2->json('total_payment'), $detail->json('total_payment'));
        $this->assertEquals($v2->json('balance'), $detail->json('balance'));
    }

    public function test_update_page_saves_all_editable_fields(): void
    {
        $id = $this->postJson('/api/printingTransaction', $this->payload())->assertCreated()->json('id');
        $changes = [
            'logo' => 'OLD',
            'mock_up_status' => 'APPROVED',
            'sales_channel' => 'VIBER',
            'order_priority' => 'RUSH',
            'order_status' => 'COMPLETED',
            'order_date' => '2026-09-22',
            'sent_date' => '2026-09-23',
            'received_date' => '2026-09-24',
            'plate' => true,
        ];

        $this->putJson('/api/printingTransaction/'.$id, $changes)
            ->assertOk()->assertJson($changes)
            ->assertJsonPath('shop_order_transaction_id', 1)
            ->assertJsonPath('order_coordinator_id', 1);
        $this->assertDatabaseHas('printing_transaction', array_merge(['id' => $id], $changes));
        $this->getJson('/api/printingTransaction/'.$id)->assertOk()->assertJson($changes);

        $this->patchJson('/api/printingTransaction/'.$id, [
            'sent_date' => null, 'received_date' => null, 'plate' => false,
        ])->assertOk()->assertJsonPath('sent_date', null)
            ->assertJsonPath('received_date', null)->assertJsonPath('plate', false);
    }

    public function test_logo_defaults_to_pending_and_can_be_cleared(): void
    {
        $payload = $this->payload();
        unset($payload['logo']);
        $id = $this->postJson('/api/printingTransaction', $payload)
            ->assertCreated()->assertJsonPath('logo', 'PENDING')->json('id');
        $this->postJson('/api/printingTransaction', array_merge($payload, ['logo' => 'PENDING']))
            ->assertCreated()->assertJsonPath('logo', 'PENDING');
        $this->patchJson('/api/printingTransaction/'.$id, ['logo' => 'PENDING'])
            ->assertOk()->assertJsonPath('logo', 'PENDING');
        $this->patchJson('/api/printingTransaction/'.$id, ['logo' => 'OLD'])
            ->assertOk()->assertJsonPath('logo', 'OLD');
        $this->patchJson('/api/printingTransaction/'.$id, ['plate' => true])
            ->assertOk()->assertJsonPath('logo', 'OLD');
        $this->patchJson('/api/printingTransaction/'.$id, ['logo' => null])
            ->assertOk()->assertJsonPath('logo', null);
        $this->postJson('/api/printingTransaction', array_merge($payload, ['logo' => null]))
            ->assertCreated()->assertJsonPath('logo', null);
    }

    public function test_invalid_values_and_foreign_keys_are_rejected(): void
    {
        $this->postJson('/api/printingTransaction', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['shop_order_transaction_id', 'order_coordinator_id', 'sales_channel', 'order_date']);
        $this->postJson('/api/printingTransaction', array_merge($this->payload(), [
            'shop_order_transaction_id' => 999, 'order_coordinator_id' => 999,
            'logo' => 'INVALID', 'plate' => 'yes', 'sales_channel' => 'EMAIL',
            'mock_up_status' => 'INVALID', 'order_priority' => 'INVALID',
            'order_status' => 'INVALID', 'order_date' => '2026-02-30',
            'sent_date' => 'invalid', 'received_date' => 'invalid',
        ]))->assertUnprocessable()->assertJsonValidationErrors([
            'shop_order_transaction_id', 'order_coordinator_id', 'logo', 'plate',
            'sales_channel', 'mock_up_status', 'order_priority', 'order_status',
            'order_date', 'sent_date', 'received_date',
        ]);
        $id = $this->postJson('/api/printingTransaction', $this->payload())->json('id');
        $this->patchJson('/api/printingTransaction/'.$id, ['logo' => null, 'order_status' => 'INVALID'])
            ->assertUnprocessable()->assertJsonValidationErrors(['order_status']);
        $this->assertDatabaseHas('printing_transaction', ['id' => $id, 'logo' => 'NEW', 'order_status' => 'PENDING']);
        $this->postJson('/api/printingTransactionComment', [
            'printing_transaction_id' => 999, 'user_id' => 999, 'comment' => ' ',
        ])->assertUnprocessable()->assertJsonValidationErrors(['printing_transaction_id', 'user_id', 'comment']);
        $this->patchJson('/api/printingTransaction/999', ['plate' => true])->assertNotFound();
        $this->deleteJson('/api/printingTransactionComment/999')->assertNotFound();
    }
}
