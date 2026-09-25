<?php

namespace App\Http\Controllers;

use App\Models\PrintingTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintingTransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'shop_order_transaction_id' => 'sometimes|integer',
            'order_coordinator_id' => 'sometimes|nullable|integer',
            'order_status' => 'sometimes|in:PENDING,COMPLETED',
            'status' => 'sometimes|nullable|in:PENDING,COMPLETED',
            'date_from' => 'sometimes|nullable|date_format:Y-m-d',
            'date_to' => 'sometimes|nullable|date_format:Y-m-d'.($request->filled('date_from') ? '|after_or_equal:date_from' : ''),
            'mock_up_status' => 'sometimes|in:PENDING,APPROVED,REJECTED',
            'order_priority' => 'sometimes|in:NORMAL,RUSH',
            'sales_channel' => 'sometimes|in:FACEBOOK,VIBER',
            'order_date' => 'sometimes|date_format:Y-m-d',
            'order_date_sort' => 'sometimes|nullable|in:asc,desc',
            'payment_status' => 'sometimes|nullable|integer',
            'is_pickup' => 'sometimes|nullable|boolean',
        ]);

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $orderDateSort = $filters['order_date_sort'] ?? null;
        if (isset($filters['status'])) {
            if (isset($filters['order_status']) && $filters['order_status'] !== $filters['status']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'The status and order_status filters must match.',
                ]);
            }
            $filters['order_status'] = $filters['status'];
        }
        unset($filters['status'], $filters['date_from'], $filters['date_to'], $filters['order_date_sort']);

        $printingOrders = DB::table('shop_order as so')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->join('category as category', 'category.id', '=', 'p.category_id')
            ->where('category.tags', 'printing')
            ->select('so.shop_transaction_id')
            ->selectRaw('MAX(category.tags) as tags, SUM(so.shop_order_total_price) as shop_order_total_price')
            ->groupBy('so.shop_transaction_id');

        $qualifiedFilters = [];
        foreach ($filters as $field => $value) {
            if ($field === 'order_coordinator_id' && $value === null) {
                continue;
            }
            if ($field === 'payment_status' || $field === 'is_pickup') {
                if ($value !== null) {
                    $qualifiedFilters[$field === 'payment_status' ? 'sot.status' : 'sot.is_pickup'] = $value;
                }
            } else {
                $qualifiedFilters['printing_transaction.'.$field] = $value;
            }
        }

        return response()->json(PrintingTransaction::with('orderCoordinator:id,name')
            ->join('users as coordinator', 'coordinator.id', '=', 'printing_transaction.order_coordinator_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'printing_transaction.shop_order_transaction_id')
            ->leftJoin('customer as customer', 'customer.id', '=', 'sot.requestor')
            ->leftJoinSub($printingOrders, 'printing_orders', function ($join) {
                $join->on('printing_orders.shop_transaction_id', '=', 'sot.id');
            })
            ->select('printing_transaction.*', 'coordinator.name as order_coordinator_name',
                'customer.store_name as store_name',
                'sot.status as payment_status',
                'sot.is_pickup',
                'customer.first_name as customer_first_name',
                'customer.last_name as customer_last_name', 'printing_orders.tags')
            ->selectRaw('COALESCE(printing_orders.shop_order_total_price, 0) as shop_order_total_price')
            ->withCount('comments')->where($qualifiedFilters)
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->where('printing_transaction.order_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->where('printing_transaction.order_date', '<=', $dateTo);
            })
            ->when($orderDateSort, function ($query) use ($orderDateSort) {
                $query->orderBy('printing_transaction.order_date', $orderDateSort);
            })
            ->orderByDesc('printing_transaction.id')->get()
            ->map(function ($transaction) {
                $transaction->customer_name = trim(($transaction->customer_first_name ?? '').' '.($transaction->customer_last_name ?? '')) ?: null;
                unset($transaction->customer_first_name, $transaction->customer_last_name);

                return $transaction;
            }));
    }

    public function fetchByShopOrderTransactionId($id)
    {
        return response()->json(PrintingTransaction::with([
            'orderCoordinator:id,name', 'comments.user:id,name',
        ])->where('shop_order_transaction_id', $id)->orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        $transaction = PrintingTransaction::create($request->validate($this->rules()));

        return response()->json($transaction->refresh()->load('orderCoordinator:id,name'), 201);
    }

    public function show(PrintingTransaction $printingTransaction)
    {
        $printingTransaction->load([
            'shopOrderTransaction.customer',
            'shopOrderTransaction.shopOrders.product.category',
            'orderCoordinator:id,name', 'comments.user:id,name',
        ]);

        $payments = app(\App\Services\ShopOrderPaymentService::class)
            ->fetch($printingTransaction->shop_order_transaction_id);
        $printingTransaction->payment_history = $payments['data'];
        $printingTransaction->total_payment = $payments['total_payment'];
        $printingTransaction->balance = $payments['balance'];

        return response()->json($printingTransaction);
    }

    public function update(Request $request, PrintingTransaction $printingTransaction)
    {
        $printingTransaction->update($request->validate($this->rules(true)));

        return response()->json($printingTransaction->refresh()->load('orderCoordinator:id,name'));
    }

    public function destroy(PrintingTransaction $printingTransaction)
    {
        $printingTransaction->delete();

        return response()->noContent();
    }

    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return [
            'shop_order_transaction_id' => $required.'|integer|exists:shop_order_transaction,id',
            'order_coordinator_id' => $required.'|integer|exists:users,id',
            'logo' => 'sometimes|nullable|in:OLD,NEW,PENDING',
            'plate' => 'sometimes|required|boolean',
            'mock_up_status' => 'sometimes|required|in:PENDING,APPROVED,REJECTED',
            'sales_channel' => $required.'|in:FACEBOOK,VIBER',
            'order_priority' => 'sometimes|required|in:NORMAL,RUSH',
            'order_status' => 'sometimes|required|in:PENDING,COMPLETED',
            'order_date' => $required.'|date_format:Y-m-d',
            'sent_date' => 'sometimes|nullable|date_format:Y-m-d',
            'received_date' => 'sometimes|nullable|date_format:Y-m-d',
        ];
    }
}
