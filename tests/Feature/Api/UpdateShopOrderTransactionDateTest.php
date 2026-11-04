<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpdateShopOrderTransactionDateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('shop_order_transaction', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function test_it_updates_only_the_transaction_date(): void
    {
        $id = DB::table('shop_order_transaction')->insertGetId([
            'date' => '2026-11-01',
            'status' => 'PENDING',
            'created_at' => '2026-11-01 08:00:00',
            'updated_at' => '2026-11-01 08:00:00',
        ]);

        $this->putJson("/api/shopOrderTransaction/updateDate/{$id}", [
            'date' => '2026-11-04',
            'status' => 'COMPLETED',
        ])->assertOk()
            ->assertJsonPath('id', $id)
            ->assertJsonPath('date', '2026-11-04')
            ->assertJsonPath('status', 'PENDING');

        $this->assertDatabaseHas('shop_order_transaction', [
            'id' => $id,
            'date' => '2026-11-04',
            'status' => 'PENDING',
        ]);
    }

    public function test_it_validates_the_date_and_returns_not_found_for_an_unknown_id(): void
    {
        $this->putJson('/api/shopOrderTransaction/updateDate/1', [
            'date' => '04-11-2026',
        ])->assertUnprocessable()->assertJsonValidationErrors('date');

        $this->putJson('/api/shopOrderTransaction/updateDate/999', [
            'date' => '2026-11-04',
        ])->assertNotFound();
    }
}
