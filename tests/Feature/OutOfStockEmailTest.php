<?php

namespace Tests\Feature;

use App\Services\PendingSupplierOrderService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OutOfStockEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function (...$parts) {
            return implode('', $parts);
        });
        DB::statement('CREATE TABLE products (id INTEGER, packaging TEXT, variation TEXT)');
        DB::statement('CREATE TABLE supplier (id INTEGER, supplier_name TEXT)');
        DB::statement('CREATE TABLE order_supplier_transaction (id INTEGER, supplier_id INTEGER, order_date TEXT, status TEXT, send_date TEXT)');
        DB::statement('CREATE TABLE order_supplier (product_id INTEGER, order_supplier_transaction_id INTEGER, quantity INTEGER, variation TEXT)');
        DB::table('products')->insert([
            ['id' => 1, 'packaging' => 'Box', 'variation' => 'kg'],
            ['id' => 2, 'packaging' => 'Bag', 'variation' => 'kg'],
        ]);
        DB::table('supplier')->insert(['id' => 1, 'supplier_name' => 'Supplier <One>']);
    }

    private function addOrder($id, $status, $productId = 1, $variation = 'WHOLESALE')
    {
        DB::table('order_supplier_transaction')->insert([
            'id' => $id, 'supplier_id' => 1, 'order_date' => '2026-09-14',
            'status' => $status, 'send_date' => '2026-09-15',
        ]);
        DB::table('order_supplier')->insert([
            'product_id' => $productId, 'order_supplier_transaction_id' => $id,
            'quantity' => 3, 'variation' => $variation,
        ]);
    }

    private function renderEmail($orders)
    {
        return view('no_stock', [
            'params' => new \Illuminate\Http\Request([
                'product_name' => 'Coffee Distributor', 'quantity' => 1, 'weight' => 1,
                'variation' => 'kg', 'price' => 259, 'email_date' => '2026-09-18 10:00:00',
            ]),
            'pendingOrders' => $orders,
        ])->render();
    }

    public function test_only_open_orders_for_this_product_appear_with_correct_units_and_details(): void
    {
        $this->addOrder(1, 'SEND_TO_SUPPLIER');
        $this->addOrder(2, 'PENDING', 1, 'RETAIL');
        $this->addOrder(3, 'COMPLETED');
        $this->addOrder(4, 'CANCELLED');
        $this->addOrder(5, 'SEND_TO_SUPPLIER', 2);

        $orders = app(PendingSupplierOrderService::class)->forProduct(1);
        $this->assertSame([2, 1], $orders->pluck('order_supplier_transaction_id')->all());
        $this->assertSame(['3 kg', '3 Box'], $orders->pluck('quantity')->all());
        $this->assertNull($orders[0]['send_date']);
        $html = $this->renderEmail($orders);
        foreach (['Replenishment ordered', 'PO #1', 'PO #2', 'Supplier &lt;One&gt;', '2026-09-14', 'Sent Sep 15, 2026', 'Incoming', '3 Box', 'PENDING — NOT YET SENT'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('No purchase order yet', $html);
    }

    public function test_pending_po_is_not_presented_as_sent_or_incoming(): void
    {
        $this->addOrder(1, 'PENDING');
        $html = $this->renderEmail(app(PendingSupplierOrderService::class)->forProduct(1));
        $this->assertStringContainsString('Purchase order pending', $html);
        $this->assertStringContainsString('has not yet been sent', $html);
        $this->assertStringContainsString('Ordered quantity', $html);
        $this->assertStringNotContainsString('Incoming:', $html);
        $this->assertStringNotContainsString('Sent Sep', $html);
    }

    public function test_completed_po_does_not_hide_the_no_po_warning(): void
    {
        $this->addOrder(1, 'COMPLETED');
        $html = $this->renderEmail(app(PendingSupplierOrderService::class)->forProduct(1));
        $this->assertStringContainsString('No purchase order yet', $html);
        $this->assertStringContainsString('Please create a purchase order.', $html);
        $this->assertStringNotContainsString('PO #1', $html);
    }
}
