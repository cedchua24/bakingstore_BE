<?php

namespace App\Http\Controllers;

use App\Models\VipProductTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VipProductTransactionController extends Controller
{
    public function index()
    {
        return response()->json($this->transactionQuery()->get());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $vipProductIds = $this->prepareVipProductIds($request);

        $this->validate($request, [
            'vip_product_id' => 'required|array|min:1',
            'vip_product_id.*' => 'required|integer|distinct|exists:vip_product,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $productId = $request->input('product_id');

        foreach ($vipProductIds as $vipProductId) {
            VipProductTransaction::firstOrCreate([
                'vip_product_id' => $vipProductId,
                'product_id' => $productId,
            ]);
        }

        return response()->json($this->transactionsForProduct($productId));
    }

    public function show(VipProductTransaction $vipProductTransaction)
    {
        $data = $this->transactionQuery()
            ->where('vpt.id', $vipProductTransaction->id)
            ->first();

        return response()->json($data);
    }

    public function fetchVipTransactionByVipId($id)
    {
        $data = $this->transactionQuery()
            ->where('vpt.vip_product_id', $id)
            ->orderBy('p.product_name', 'asc')
            ->get();

        return response()->json($data);
    }

    public function fetchVipProductLastOrder(Request $request, $id)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $businessTypes = DB::table('mark_up_product as mup_type')
            ->select(
                'mup_type.product_id',
                DB::raw("GROUP_CONCAT(DISTINCT mup_type.business_type ORDER BY mup_type.business_type SEPARATOR ',') as business_types")
            )
            ->groupBy('mup_type.product_id');

        $completedSupplierOrders = DB::table('order_supplier as os_completed')
            ->join(
                'order_supplier_transaction as ost_completed',
                'ost_completed.id',
                '=',
                'os_completed.order_supplier_transaction_id'
            )
            ->join('supplier as s_completed', 's_completed.id', '=', 'ost_completed.supplier_id')
            ->select(
                'os_completed.product_id',
                DB::raw('MAX(ost_completed.order_date) as last_order_date'),
                DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(ost_completed.id ORDER BY ost_completed.order_date DESC, ost_completed.id DESC), ',', 1) as last_order_supplier_transaction_id"),
                DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(s_completed.supplier_name ORDER BY ost_completed.order_date DESC, ost_completed.id DESC SEPARATOR '||'), '||', 1) as last_order_supplier"),
                DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(os_completed.quantity ORDER BY ost_completed.order_date DESC, ost_completed.id DESC), ',', 1) as last_order_quantity"),
                DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(os_completed.variation ORDER BY ost_completed.order_date DESC, ost_completed.id DESC), ',', 1) as last_order_type")
            )
            ->where('ost_completed.status', 'COMPLETED')
            ->groupBy('os_completed.product_id');

        $pendingSupplierOrderLines = DB::table('order_supplier as os_pending')
            ->join(
                'order_supplier_transaction as ost_pending',
                'ost_pending.id',
                '=',
                'os_pending.order_supplier_transaction_id'
            )
            ->join('supplier as s_pending', 's_pending.id', '=', 'ost_pending.supplier_id')
            ->select(
                'os_pending.product_id',
                'ost_pending.id as transaction_id',
                'ost_pending.order_date',
                's_pending.supplier_name',
                DB::raw('SUM(os_pending.quantity) as quantity')
            )
            ->where('ost_pending.status', 'PENDING')
            ->groupBy(
                'os_pending.product_id',
                'ost_pending.id',
                'ost_pending.order_date',
                's_pending.supplier_name'
            );

        $pendingSupplierOrders = DB::query()
            ->fromSub($pendingSupplierOrderLines, 'pending_order')
            ->select(
                'pending_order.product_id',
                DB::raw('MAX(pending_order.order_date) as pending_order_date'),
                DB::raw("GROUP_CONCAT(pending_order.quantity ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC) as pending_order_quantity"),
                DB::raw("GROUP_CONCAT(pending_order.transaction_id ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC) as pending_order_transaction_ids"),
                DB::raw("GROUP_CONCAT(pending_order.order_date ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC) as pending_order_dates"),
                DB::raw("GROUP_CONCAT(pending_order.supplier_name ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC SEPARATOR '||') as pending_order_suppliers")
            )
            ->groupBy('pending_order.product_id');

        $customerSales = DB::table('shop_order as so_sold')
            ->join(
                'shop_order_transaction as sot_sold',
                'sot_sold.id',
                '=',
                'so_sold.shop_transaction_id'
            )
            ->join('products as sold_product', 'sold_product.id', '=', 'so_sold.product_id')
            ->leftJoin('mark_up_product as mup_sold', 'mup_sold.id', '=', 'so_sold.mark_up_product_id')
            ->select(
                'so_sold.product_id',
                DB::raw('COUNT(DISTINCT sot_sold.requestor) as sold_customer_count'),
                DB::raw('MAX(sot_sold.date) as last_customer_order_date'),
                DB::raw("
                    SUM(
                        CASE
                            WHEN mup_sold.business_type = 'WHOLESALE'
                                THEN so_sold.shop_order_quantity * sold_product.quantity
                            ELSE so_sold.shop_order_quantity
                        END
                    ) as total_sold
                ")
            )
            ->where('sot_sold.type', 0)
            ->where('sot_sold.status', 1);

        if ($dateFrom != '') {
            $customerSales->where('sot_sold.date', '>=', $dateFrom);
        }

        if ($dateTo != '') {
            $customerSales->where('sot_sold.date', '<=', $dateTo);
        }

        $customerSales->groupBy('so_sold.product_id');

        $data = DB::table('vip_product_transaction as vpt')
            ->join('vip_product as vp', 'vp.id', '=', 'vpt.vip_product_id')
            ->join('products as p', 'p.id', '=', 'vpt.product_id')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoinSub($businessTypes, 'product_business_types', function ($join) {
                $join->on('product_business_types.product_id', '=', 'p.id');
            })
            ->leftJoinSub($completedSupplierOrders, 'completed_supplier_orders', function ($join) {
                $join->on('completed_supplier_orders.product_id', '=', 'p.id');
            })
            ->leftJoinSub($pendingSupplierOrders, 'pending_supplier_orders', function ($join) {
                $join->on('pending_supplier_orders.product_id', '=', 'p.id');
            })
            ->leftJoinSub($customerSales, 'customer_sales', function ($join) {
                $join->on('customer_sales.product_id', '=', 'p.id');
            })
            ->select(
                'vpt.vip_product_id',
                'vp.vip_product_name',
                'vp.details as vip_product_details',
                'vp.vip_color',
                'vp.status as vip_status',
                'p.id as product_id',
                'p.product_name',
                'p.category_id',
                'c.category_name',
                'p.brand_id',
                'b.brand_name',
                'p.price',
                'p.sale_price',
                'p.stock',
                'p.stock_pc',
                'p.stock_warning',
                'p.stock_warning_type',
                'p.quantity',
                'p.variation',
                'p.packaging',
                'p.weight',
                'p.disabled',
                DB::raw("COALESCE(product_business_types.business_types, '') as business_type"),
                DB::raw('COALESCE(customer_sales.total_sold, 0) as total_sold'),
                DB::raw('COALESCE(customer_sales.sold_customer_count, 0) as sold_customer_count'),
                DB::raw("COALESCE(customer_sales.last_customer_order_date, '') as last_customer_order_date"),
                DB::raw("COALESCE(completed_supplier_orders.last_order_date, '') as last_order_date"),
                DB::raw('completed_supplier_orders.last_order_supplier_transaction_id'),
                DB::raw("COALESCE(completed_supplier_orders.last_order_supplier, '') as last_order_supplier"),
                DB::raw('COALESCE(completed_supplier_orders.last_order_quantity, 0) as last_order_quantity'),
                DB::raw("COALESCE(completed_supplier_orders.last_order_type, '') as last_order_type"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_date, '') as pending_order_date"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_quantity, '') as pending_order_quantity"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_transaction_ids, '') as pending_order_transaction_ids"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_dates, '') as pending_order_dates"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_suppliers, '') as pending_order_suppliers")
            )
            ->where('vpt.vip_product_id', $id)
            ->distinct()
            ->orderBy('p.product_name', 'asc')
            ->get();

        foreach ($data as $item) {
            $item->business_type = $item->business_type != ''
                ? explode(',', $item->business_type)
                : [];
            $item->pending_order_transaction_ids = $item->pending_order_transaction_ids != ''
                ? array_map('intval', explode(',', $item->pending_order_transaction_ids))
                : [];
            $item->pending_order_quantity = $item->pending_order_quantity != ''
                ? array_map('intval', explode(',', $item->pending_order_quantity))
                : [];
            $item->pending_order_dates = $item->pending_order_dates != ''
                ? explode(',', $item->pending_order_dates)
                : [];
            $item->pending_order_suppliers = $item->pending_order_suppliers != ''
                ? explode('||', $item->pending_order_suppliers)
                : [];
        }

        return response()->json($data);
    }

    public function edit(VipProductTransaction $vipProductTransaction)
    {
        return response()->json($vipProductTransaction);
    }

    public function update(Request $request, VipProductTransaction $vipProductTransaction)
    {
        $vipProductIds = $this->prepareVipProductIds($request);

        $this->validate($request, [
            'vip_product_id' => 'required|array|min:1',
            'vip_product_id.*' => 'required|integer|distinct|exists:vip_product,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $productId = $request->input('product_id');

        DB::transaction(function () use ($vipProductTransaction, $vipProductIds, $productId) {
            if ($vipProductTransaction->product_id != $productId) {
                $vipProductTransaction->delete();
            }

            $removedTransactionIds = VipProductTransaction::where('product_id', $productId)
                ->whereNotIn('vip_product_id', $vipProductIds)
                ->pluck('id');

            if ($removedTransactionIds->isNotEmpty()) {
                DB::table('vip_product_note')
                    ->whereIn('vip_product_transaction_id', $removedTransactionIds)
                    ->delete();

                VipProductTransaction::whereIn('id', $removedTransactionIds)->delete();
            }

            foreach ($vipProductIds as $vipProductId) {
                VipProductTransaction::firstOrCreate([
                    'vip_product_id' => $vipProductId,
                    'product_id' => $productId,
                ]);
            }
        });

        return response()->json($this->transactionsForProduct($productId));
    }

    public function destroy(VipProductTransaction $vipProductTransaction)
    {
        $vipProductTransaction->delete();

        return response()->json($vipProductTransaction);
    }

    private function transactionQuery()
    {
        return DB::table('vip_product_transaction as vpt')
            ->join('vip_product as vp', 'vp.id', '=', 'vpt.vip_product_id')
            ->join('products as p', 'p.id', '=', 'vpt.product_id')
            ->select(
                'vpt.id',
                'vpt.vip_product_id',
                'vpt.product_id',
                'vpt.created_at',
                'vpt.updated_at',
                'vp.vip_product_name',
                'vp.details',
                'vp.vip_color',
                'vp.status',
                'p.product_name'
            );
    }

    private function prepareVipProductIds(Request $request)
    {
        $vipProductIds = $request->input('vip_product_id');

        if (!is_array($vipProductIds)) {
            $vipProductIds = [$vipProductIds];
        }

        $vipProductIds = array_values(array_unique($vipProductIds));
        $request->merge(['vip_product_id' => $vipProductIds]);

        return $vipProductIds;
    }

    private function transactionsForProduct($productId)
    {
        return $this->transactionQuery()
            ->where('vpt.product_id', $productId)
            ->orderBy('vp.vip_product_name', 'asc')
            ->get();
    }
}
