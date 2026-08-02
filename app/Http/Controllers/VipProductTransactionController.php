<?php

namespace App\Http\Controllers;

use App\Models\VipProductTransaction;
use Carbon\Carbon;
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
                'ost_pending.status',
                'ost_pending.send_date',
                's_pending.supplier_name',
                DB::raw('SUM(os_pending.quantity) as quantity')
            )
            ->whereIn('ost_pending.status', ['PENDING', 'SEND_TO_SUPPLIER'])
            ->groupBy(
                'os_pending.product_id',
                'ost_pending.id',
                'ost_pending.order_date',
                'ost_pending.status',
                'ost_pending.send_date',
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
                DB::raw("GROUP_CONCAT(pending_order.supplier_name ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC SEPARATOR '||') as pending_order_suppliers"),
                DB::raw("GROUP_CONCAT(pending_order.status ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC) as pending_order_status"),
                DB::raw("GROUP_CONCAT(CASE WHEN pending_order.status = 'SEND_TO_SUPPLIER' THEN COALESCE(pending_order.send_date, '') ELSE '' END ORDER BY pending_order.order_date ASC, pending_order.transaction_id ASC SEPARATOR '||') as pending_order_send_dates")
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
                DB::raw("COALESCE(pending_supplier_orders.pending_order_suppliers, '') as pending_order_suppliers"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_status, '') as pending_order_status"),
                DB::raw("COALESCE(pending_supplier_orders.pending_order_send_dates, '') as pending_order_send_dates")
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
            $item->pending_order_status = $item->pending_order_status != ''
                ? explode(',', $item->pending_order_status)
                : [];
            $item->pending_order_send_dates = count($item->pending_order_status) > 0
                ? array_map(function ($sendDate) {
                    return $sendDate !== '' ? $sendDate : null;
                }, explode('||', $item->pending_order_send_dates))
                : [];
        }

        return response()->json($data);
    }

    public function fetchVIPProductMonthlySold(Request $request, $id)
    {
        $this->validate($request, [
            'month' => 'nullable|date_format:Y-m',
            'next_months' => 'nullable|integer|min:0|max:120',
            'comparison_page' => 'nullable|integer|min:0|max:40',
        ]);

        $reportMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth()->addMonths((int) $request->input('next_months', 0));

        $comparisonPage = (int) $request->input('comparison_page', 0);
        $firstComparisonMonthAgo = ($comparisonPage * 3) + 1;
        $monthOffsets = collect([0])->concat(
            range($firstComparisonMonthAgo, $firstComparisonMonthAgo + 2)
        );

        $months = $monthOffsets->map(function ($monthsAgo) use ($reportMonth) {
            $month = $reportMonth->copy()->subMonths($monthsAgo);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'date_from' => $month->copy()->startOfMonth()->toDateString(),
                'date_to' => $month->copy()->endOfMonth()->toDateString(),
            ];
        });

        $vipProductIds = DB::table('vip_product_transaction')
            ->select('product_id')
            ->where('vip_product_id', $id)
            ->distinct();

        $salesByProductAndMonth = DB::table('shop_order as so')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('products as sold_product', 'sold_product.id', '=', 'so.product_id')
            ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->joinSub($vipProductIds, 'vip_products', function ($join) {
                $join->on('vip_products.product_id', '=', 'so.product_id');
            })
            ->select(
                'so.product_id',
                DB::raw("DATE_FORMAT(sot.date, '%Y-%m') as sold_month"),
                DB::raw('SUM(so.shop_order_quantity) as quantity_sold'),
                DB::raw("SUM(CASE WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * sold_product.quantity ELSE so.shop_order_quantity END) as pieces_sold"),
                DB::raw('SUM(so.shop_order_total_price) as sales_amount'),
                DB::raw('SUM(so.shop_order_profit) as profit_amount'),
                DB::raw('COUNT(DISTINCT sot.id) as order_count'),
                DB::raw('COUNT(DISTINCT sot.requestor) as customer_count')
            )
            ->where('sot.type', 0)
            ->where('sot.status', 1)
            ->whereBetween('sot.date', [
                $reportMonth->copy()->subMonths($firstComparisonMonthAgo + 2)->startOfMonth()->toDateString(),
                $reportMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('so.product_id', DB::raw("DATE_FORMAT(sot.date, '%Y-%m')"))
            ->get()
            ->groupBy('product_id');

        $businessTypes = DB::table('mark_up_product')
            ->select(
                'product_id',
                DB::raw("GROUP_CONCAT(DISTINCT business_type ORDER BY business_type SEPARATOR ',') as business_types")
            )
            ->groupBy('product_id');

        $products = DB::table('vip_product_transaction as vpt')
            ->join('vip_product as vp', 'vp.id', '=', 'vpt.vip_product_id')
            ->join('products as p', 'p.id', '=', 'vpt.product_id')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoinSub($businessTypes, 'product_business_types', function ($join) {
                $join->on('product_business_types.product_id', '=', 'p.id');
            })
            ->select(
                'vpt.vip_product_id',
                'vp.vip_product_name',
                'vp.vip_color',
                'p.id as product_id',
                'p.product_name',
                'p.stock',
                'p.stock_pc',
                'p.packaging',
                'p.variation',
                'p.quantity',
                'c.category_name',
                'b.brand_name',
                DB::raw("COALESCE(product_business_types.business_types, '') as business_types")
            )
            ->where('vpt.vip_product_id', $id)
            ->distinct()
            ->orderBy('p.product_name')
            ->get()
            ->map(function ($product) use ($months, $salesByProductAndMonth) {
                $productSales = $salesByProductAndMonth
                    ->get($product->product_id, collect())
                    ->keyBy('sold_month');

                $monthlySales = $months->map(function ($month) use ($productSales) {
                    $sales = $productSales->get($month['month']);

                    return array_merge($month, [
                        'quantity_sold' => (int) ($sales->quantity_sold ?? 0),
                        'pieces_sold' => (int) ($sales->pieces_sold ?? 0),
                        'sales_amount' => round((float) ($sales->sales_amount ?? 0), 2),
                        'profit_amount' => round((float) ($sales->profit_amount ?? 0), 2),
                        'order_count' => (int) ($sales->order_count ?? 0),
                        'customer_count' => (int) ($sales->customer_count ?? 0),
                    ]);
                })->values();

                $current = $monthlySales->first();
                $previousMonths = $monthlySales->slice(1)->values();
                $lastMonth = $previousMonths->first();
                $averageSales = round((float) $previousMonths->avg('sales_amount'), 2);
                $averageProfit = round((float) $previousMonths->avg('profit_amount'), 2);
                $averageQuantity = round((float) $previousMonths->avg('quantity_sold'), 2);
                $averagePieces = round((float) $previousMonths->avg('pieces_sold'), 2);

                $product->business_types = $product->business_types !== ''
                    ? explode(',', $product->business_types)
                    : [];
                $product->current_month = $current;
                $product->previous_months = $previousMonths;
                $product->average_sales = $averageSales;
                $product->average_profit = $averageProfit;
                $product->average_quantity = $averageQuantity;
                $product->average_pieces = $averagePieces;
                $product->sales_average_gap = round($current['sales_amount'] - $averageSales, 2);
                $product->profit_average_gap = round($current['profit_amount'] - $averageProfit, 2);
                $product->quantity_average_gap = round($current['quantity_sold'] - $averageQuantity, 2);
                $product->pieces_average_gap = round($current['pieces_sold'] - $averagePieces, 2);
                $product->sales_last_month_gap = round($current['sales_amount'] - $lastMonth['sales_amount'], 2);
                $product->profit_last_month_gap = round($current['profit_amount'] - $lastMonth['profit_amount'], 2);
                $product->quantity_last_month_gap = round($current['quantity_sold'] - $lastMonth['quantity_sold'], 2);
                $product->pieces_last_month_gap = round($current['pieces_sold'] - $lastMonth['pieces_sold'], 2);
                $product->sales_change_percentage = $lastMonth['sales_amount'] > 0
                    ? round((($current['sales_amount'] - $lastMonth['sales_amount']) / $lastMonth['sales_amount']) * 100, 2)
                    : null;
                $product->sales_trend = $product->sales_last_month_gap > 0
                    ? 'HIGHER'
                    : ($product->sales_last_month_gap < 0 ? 'LOWER' : 'UNCHANGED');

                return $product;
            });

        $previousMonthTotals = $months->slice(1)->values()->map(function ($month) use ($products) {
            return array_merge($month, [
                'quantity_sold' => $products->sum(function ($product) use ($month) {
                    return collect($product->previous_months)->firstWhere('month', $month['month'])['quantity_sold'] ?? 0;
                }),
                'pieces_sold' => $products->sum(function ($product) use ($month) {
                    return collect($product->previous_months)->firstWhere('month', $month['month'])['pieces_sold'] ?? 0;
                }),
                'sales_amount' => round($products->sum(function ($product) use ($month) {
                    return collect($product->previous_months)->firstWhere('month', $month['month'])['sales_amount'] ?? 0;
                }), 2),
                'profit_amount' => round($products->sum(function ($product) use ($month) {
                    return collect($product->previous_months)->firstWhere('month', $month['month'])['profit_amount'] ?? 0;
                }), 2),
            ]);
        });

        $currentMonth = [
            'quantity_sold' => $products->sum('current_month.quantity_sold'),
            'pieces_sold' => $products->sum('current_month.pieces_sold'),
            'sales_amount' => round($products->sum('current_month.sales_amount'), 2),
            'profit_amount' => round($products->sum('current_month.profit_amount'), 2),
        ];
        $averageSales = round((float) $previousMonthTotals->avg('sales_amount'), 2);
        $averageProfit = round((float) $previousMonthTotals->avg('profit_amount'), 2);
        $averageQuantity = round((float) $previousMonthTotals->avg('quantity_sold'), 2);
        $averagePieces = round((float) $previousMonthTotals->avg('pieces_sold'), 2);
        $lastMonthTotals = $previousMonthTotals->first();
        $earliestSaleDate = DB::table('shop_order as earliest_so')
            ->join(
                'shop_order_transaction as earliest_sot',
                'earliest_sot.id',
                '=',
                'earliest_so.shop_transaction_id'
            )
            ->join(
                'vip_product_transaction as earliest_vpt',
                'earliest_vpt.product_id',
                '=',
                'earliest_so.product_id'
            )
            ->where('earliest_vpt.vip_product_id', $id)
            ->where('earliest_sot.type', 0)
            ->where('earliest_sot.status', 1)
            ->min('earliest_sot.date');
        $oldestComparisonMonth = $reportMonth->copy()
            ->subMonths($firstComparisonMonthAgo + 2)
            ->startOfMonth();
        $hasOlderComparison = $comparisonPage < 40
            && $earliestSaleDate
            && Carbon::parse($earliestSaleDate)->startOfMonth()->lt($oldestComparisonMonth);

        return response()->json([
            'report_month' => $months->first(),
            'comparison' => [
                'page' => $comparisonPage,
                'previous_page' => $comparisonPage > 0 ? $comparisonPage - 1 : null,
                'next_page' => $hasOlderComparison ? $comparisonPage + 1 : null,
                'newer_page' => $comparisonPage > 0 ? $comparisonPage - 1 : null,
                'older_page' => $hasOlderComparison ? $comparisonPage + 1 : null,
                'has_newer' => $comparisonPage > 0,
                'has_older' => (bool) $hasOlderComparison,
                'months' => $months->slice(1)->values(),
            ],
            'filters' => [
                'comparison_page' => $comparisonPage,
            ],
            'current_month' => $currentMonth,
            'previous_months' => $previousMonthTotals,
            'average_sales' => $averageSales,
            'average_profit' => $averageProfit,
            'average_quantity' => $averageQuantity,
            'average_pieces' => $averagePieces,
            'sales_average_gap' => round($currentMonth['sales_amount'] - $averageSales, 2),
            'profit_average_gap' => round($currentMonth['profit_amount'] - $averageProfit, 2),
            'sales_last_month_gap' => round($currentMonth['sales_amount'] - ($lastMonthTotals['sales_amount'] ?? 0), 2),
            'profit_last_month_gap' => round($currentMonth['profit_amount'] - ($lastMonthTotals['profit_amount'] ?? 0), 2),
            'data' => $products,
        ]);
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
