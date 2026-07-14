<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RejectPendingCheckListTransactionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('check_list_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('check_list_id')->nullable();
            $table->integer('assignee')->nullable();
            $table->integer('checker')->nullable();
            $table->string('comment')->nullable();
            $table->date('date')->nullable();
            $table->integer('grade');
            $table->integer('grade_checker')->default(0);
            $table->string('status');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('check_list_transaction');

        parent::tearDown();
    }

    public function test_it_rejects_only_pending_checklist_transactions(): void
    {
        DB::table('check_list_transaction')->insert([
            [
                'grade' => 0,
                'grade_checker' => 4,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'grade' => 5,
                'grade_checker' => 3,
                'status' => 'APPROVED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->patchJson('/api/checkListTransaction/rejectPending');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Pending checklist transactions rejected successfully.',
                'updated_count' => 1,
            ]);

        $this->assertDatabaseHas('check_list_transaction', [
            'grade' => 1,
            'grade_checker' => 4,
            'status' => 'REJECTED',
        ]);
        $this->assertDatabaseHas('check_list_transaction', [
            'grade' => 5,
            'grade_checker' => 3,
            'status' => 'APPROVED',
        ]);
    }

    public function test_grade_checker_can_be_updated(): void
    {
        $transactionId = DB::table('check_list_transaction')->insertGetId([
            'check_list_id' => 10,
            'assignee' => 20,
            'checker' => 30,
            'comment' => 'Initial review',
            'date' => '2026-07-14',
            'grade' => 2,
            'grade_checker' => 0,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson('/api/checkListTransaction/'.$transactionId, [
            'check_list_id' => 10,
            'assignee' => 20,
            'checker' => 30,
            'comment' => 'Checked',
            'date' => '2026-07-14',
            'grade' => 2,
            'grade_checker' => 5,
            'status' => 'APPROVED',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'id' => $transactionId,
                'grade_checker' => 5,
            ]);

        $this->assertDatabaseHas('check_list_transaction', [
            'id' => $transactionId,
            'grade_checker' => 5,
            'status' => 'APPROVED',
        ]);
    }
}
