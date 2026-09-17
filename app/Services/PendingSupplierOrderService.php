<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PendingSupplierOrderService
{
    public function forProduct($productId)
    {
        $products = collect([(object) ['id' => $productId]]);
        $this->attach($products);

        return $products->first()->pending_orders;
    }

    public function attach($data)
    {
        $productIds = $data->pluck('id')->unique()->values();
        $pendingOrdersByProduct = collect();

        if ($productIds->isNotEmpty()) {
            $pendingOrdersByProduct = DB::table('order_supplier as os')
                ->join(
                    'order_supplier_transaction as ost',
                    'ost.id',
                    '=',
                    'os.order_supplier_transaction_id'
                )
                ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
                ->join('products as p', 'p.id', '=', 'os.product_id')
                ->select(
                    'os.product_id',
                    'os.order_supplier_transaction_id',
                    'ost.order_date as date',
                    'ost.status',
                    'ost.send_date',
                    's.supplier_name as supplier'
                )
                ->selectRaw("
                    CONCAT(
                        os.quantity,
                        ' ',
                        CASE
                            WHEN os.variation = 'WHOLESALE' THEN p.packaging
                            ELSE p.variation
                        END
                    ) as quantity
                ")
                ->whereIn('os.product_id', $productIds)
                ->whereIn('ost.status', ['PENDING', 'SEND_TO_SUPPLIER'])
                ->orderBy('ost.order_date', 'desc')
                ->orderBy('ost.id', 'desc')
                ->get()
                ->groupBy('product_id');
        }

        foreach ($data as $product) {
            $product->pending_orders = $pendingOrdersByProduct
                ->get($product->id, collect())
                ->map(function ($pendingOrder) {
                    return [
                        'order_supplier_transaction_id' => $pendingOrder->order_supplier_transaction_id,
                        'date' => $pendingOrder->date,
                        'supplier' => $pendingOrder->supplier,
                        'quantity' => $pendingOrder->quantity,
                        'status' => $pendingOrder->status,
                        'send_date' => $pendingOrder->status === 'SEND_TO_SUPPLIER'
                            ? $pendingOrder->send_date
                            : null,
                    ];
                })
                ->values();
        }
    }

}
