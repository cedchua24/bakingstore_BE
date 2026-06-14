<?php

namespace App\Http\Controllers;

use App\Models\VipCustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VipCustomerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('vip_customer_transaction as vct')
            ->join('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->join('customer as c', 'c.id', '=', 'vct.customer_id')
            ->select(
                'vct.id',
                'vct.vip_customer_id',
                'vct.customer_id',
                'vct.created_at',
                'vct.updated_at',
                'vc.vip_name',
                'vc.details',
                'vc.vip_color',
                'vc.status',
                DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, ''))) as customer_name")
            )
            ->get();

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'vip_customer_id' => 'required',
            'customer_id' => 'required',
        ]);

        $vipCustomerTransaction = new VipCustomerTransaction;
        $vipCustomerTransaction->vip_customer_id = $request->input('vip_customer_id');
        $vipCustomerTransaction->customer_id = $request->input('customer_id');
        $vipCustomerTransaction->save();

        return response()->json($vipCustomerTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VipCustomerTransaction  $vipCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(VipCustomerTransaction $vipCustomerTransaction)
    {
        $data = DB::table('vip_customer_transaction as vct')
            ->join('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->join('customer as c', 'c.id', '=', 'vct.customer_id')
            ->select(
                'vct.id',
                'vct.vip_customer_id',
                'vct.customer_id',
                'vct.created_at',
                'vct.updated_at',
                'vc.vip_name',
                'vc.details',
                'vc.vip_color',
                'vc.status',
                DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, ''))) as customer_name")
            )
            ->where('vct.id', $vipCustomerTransaction->id)
            ->first();

        return response()->json($data);
    }

    public function fetchVipTransactionByVipId($id)
    {
        $data = DB::table('vip_customer_transaction as vct')
            ->join('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->join('customer as c', 'c.id', '=', 'vct.customer_id')
            ->select(
                'vct.id',
                'vct.vip_customer_id',
                'vct.customer_id',
                'vct.created_at',
                'vct.updated_at',
                'vc.vip_name',
                'vc.details',
                'vc.vip_color',
                'vc.status',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, ''))) as customer_name")
            )
            ->where('vct.vip_customer_id', $id)
            ->orderBy('c.first_name', 'asc')
            ->get();

        return response()->json($data);
    }

    public function fetchVipCustomerLastOrder(Request $request, $id)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $draftOrders = DB::table('shop_order_transaction as sot_draft')
            ->join('vip_customer_transaction as vct_draft', 'vct_draft.customer_id', '=', 'sot_draft.requestor')
            ->select(
                'vct_draft.customer_id',
                DB::raw('MAX(sot_draft.date) as draft_order_date'),
                DB::raw('GROUP_CONCAT(sot_draft.date ORDER BY sot_draft.date ASC) as draft_order_dates'),
                DB::raw('SUM(sot_draft.shop_order_transaction_total_price) as draft_order_total_price')
            )
            ->where('sot_draft.type', 0)
            ->where('sot_draft.status', 2)
            ->where('vct_draft.vip_customer_id', $id)
            ->groupBy('vct_draft.customer_id');

        $totalOrders = DB::table('shop_order_transaction as sot_total')
            ->join('vip_customer_transaction as vct_total', 'vct_total.customer_id', '=', 'sot_total.requestor')
            ->select(
                'vct_total.customer_id',
                DB::raw('SUM(sot_total.shop_order_transaction_total_price) as total_order_price')
            )
            ->where('sot_total.type', 0)
            ->where('sot_total.status', 1)
            ->where('vct_total.vip_customer_id', $id);

        if ($dateFrom != '') {
            $totalOrders->where('sot_total.date', '>=', $dateFrom);
        }

        if ($dateTo != '') {
            $totalOrders->where('sot_total.date', '<=', $dateTo);
        }

        $totalOrders->groupBy('vct_total.customer_id');

        $data = DB::table('shop_order_transaction as sot')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('vip_customer_transaction as vct', 'vct.customer_id', '=', 'c.id')
            ->join('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->leftJoinSub($draftOrders, 'draft_orders', function ($join) {
                $join->on('draft_orders.customer_id', '=', 'c.id');
            })
            ->leftJoinSub($totalOrders, 'total_orders', function ($join) {
                $join->on('total_orders.customer_id', '=', 'c.id');
            })
            ->select(
                'vct.id as vip_customer_transaction_id',
                'vct.vip_customer_id',
                'vct.customer_id',
                'vc.vip_name',
                'vc.details',
                'vc.vip_color',
                'vc.status as vip_status',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.disabled',
                DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, ''))) as customer_name"),
                DB::raw('MAX(sot.date) as last_order_date'),
                DB::raw("COALESCE(draft_orders.draft_order_date, '') as draft_order_date"),
                DB::raw("COALESCE(draft_orders.draft_order_dates, '') as draft_order_dates"),
                DB::raw('COALESCE(draft_orders.draft_order_total_price, 0) as draft_order_total_price'),
                DB::raw("COALESCE(draft_orders.draft_order_date, MAX(sot.date)) as latest_order_date"),
                DB::raw('COALESCE(total_orders.total_order_price, 0) as total_order_price')
            )
            ->where('sot.type', 0)
            ->where('sot.status', 1)
            ->where('vc.id', $id)
            ->groupBy(
                'vct.id',
                'vct.vip_customer_id',
                'vct.customer_id',
                'vc.vip_name',
                'vc.details',
                'vc.vip_color',
                'vc.status',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.disabled',
                'draft_orders.draft_order_date',
                'draft_orders.draft_order_dates',
                'draft_orders.draft_order_total_price',
                'total_orders.total_order_price'
            )
            ->orderBy('total_order_price', 'desc')
            ->orderBy('latest_order_date', 'desc')
            ->orderBy('last_order_date', 'desc')
            ->get();

        foreach ($data as $item) {
            $item->draft_order_dates = $item->draft_order_dates != ''
                ? explode(',', $item->draft_order_dates)
                : [];
        }

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VipCustomerTransaction  $vipCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(VipCustomerTransaction $vipCustomerTransaction)
    {
        $vipCustomerTransaction = VipCustomerTransaction::find($vipCustomerTransaction->id);
        return response()->json($vipCustomerTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VipCustomerTransaction  $vipCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VipCustomerTransaction $vipCustomerTransaction)
    {
        $vipCustomerTransaction = VipCustomerTransaction::find($vipCustomerTransaction->id);
        $vipCustomerTransaction->vip_customer_id = $request->input('vip_customer_id');
        $vipCustomerTransaction->customer_id = $request->input('customer_id');
        $vipCustomerTransaction->save();

        return response()->json($vipCustomerTransaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VipCustomerTransaction  $vipCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(VipCustomerTransaction $vipCustomerTransaction)
    {
        $vipCustomerTransaction = VipCustomerTransaction::find($vipCustomerTransaction->id);
        $vipCustomerTransaction->delete();

        return response()->json($vipCustomerTransaction);
    }
}
