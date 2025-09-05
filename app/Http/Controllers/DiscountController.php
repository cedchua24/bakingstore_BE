<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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


        public function fetchDiscountReport(Request $request)
    {
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' &&  $request->input('today') == '') {
               $test = 1;
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount','sot.id as transaction_id', 'sot.date', 'mup.business_type',
             'p.product_name')    
            ->get();

            $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.discount_amount) as discount_amount'))
            ->first();

        } else if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' &&  $request->input('today') != '') {
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount', 'sot.date','sot.id as transaction_id', 'mup.business_type',
             'p.product_name')    
            ->where('sot.date',  $request->input('today'))
            ->get();

            $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.discount_amount) as discount_amount'))
            ->where('sot.date', $request->input('today'))
            ->first();

        } 
        
        else {
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount', 'sot.date', 'mup.business_type',
             'p.product_name')      
            ->where('sot.date', '>=', $request->input('dateFrom'))
            ->where('sot.date', '<=', $request->input('dateTo'))
            ->get();

             $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.discount_amount) as discount_amount'))    
            ->where('sot.date', '>=', $request->input('dateFrom'))
            ->where('sot.date', '<=', $request->input('dateTo'))
            ->first();
        }  

           $response = [
              'data' => $data,
              'date' => $request->input('today'),
              'code' => 200,
              'total_amount' => $sum->discount_amount,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

            public function fetchDiscountLossReport(Request $request)
    {
      
       if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' &&  $request->input('today') == '') {
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount', 'sot.id as transaction_id', 'sot.date', 'mup.business_type',
             'p.product_name')    
             ->where('d.loss_amount', '<', 0)
            ->get();

            $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.loss_amount) as loss_amount'))
            ->where('d.loss_amount', '<', 0)
            ->first();

        } else  if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' &&  $request->input('today') != '') {
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount', 'sot.id as transaction_id', 'sot.date', 'mup.business_type',
             'p.product_name')    
             ->where('d.loss_amount', '<', 0)
             ->where('sot.date', $request->input('today'))
            ->get();

            $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.loss_amount) as loss_amount'))
            ->where('d.loss_amount', '<', 0)
            ->where('sot.date', $request->input('today'))
            ->first();

        }
        
        else {
             $data = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select('d.id', 'd.discount_amount', 'd.loss_amount', 'so.shop_order_quantity', 'so.discount_amount as so_discount_amount','sot.id as transaction_id', 'sot.date', 'mup.business_type',
             'p.product_name')      
            ->where('sot.date', '>=', $request->input('dateFrom'))
            ->where('sot.date', '<=', $request->input('dateTo'))
            ->where('d.loss_amount', '<', 0)
            ->get();

             $sum = DB::table('discount as d')
            ->join('shop_order as so', 'so.id', '=', 'd.shop_order_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(DB::raw('SUM(d.loss_amount) as loss_amount'))    
            ->where('sot.date', '>=', $request->input('dateFrom'))
            ->where('sot.date', '<=', $request->input('dateTo'))
            ->where('d.loss_amount', '<', 0)
            ->first();
        }  

           $response = [
              'data' => $data,
              'code' => 200,
              'total_amount' => $sum->loss_amount,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit(Discount $discount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discount $discount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        //
    }
}
