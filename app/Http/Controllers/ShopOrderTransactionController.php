<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ReducedStock;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use App\Models\ShopOrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ShopOrderTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function index()
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'shop_order_transaction.id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
            ->where('shop.shop_type_id', '!=', 3)    
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();
         

            $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->where('shop.shop_type_id', '!=', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Added"
          ];


        
            return response()->json($response);   
    }

       public function fetchShopOrderTransactionListByDate($date)
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
             ->where('shop.shop_type_id', '!=', 3)
             ->where('shop_order_transaction.date', $date)
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();


            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->where('shop.shop_type_id', '!=', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Added"
          ];
             
            return response()->json($response);    
    }

   public function fetchOnlineShopOrderTransactionList()
    {
        $currentTime = Carbon::now('GMT+8');
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name', 'shop.shop_type_id',
             'c.first_name as requestor_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name')    
             ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', date('Y-m-d'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();
            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->where('pt.type', 2)
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

       public function fetchOnlineShopOrderTransactionListReport()
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') ,
             DB::raw('shop_order_transaction.date'),
             DB::raw('SUM(total_cash) as total_cash'), DB::raw('SUM(total_online) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();


            $total_sales = 0;
            $total_profit = 0;
            $total_cash = 0;
            $total_online = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

           public function fetchShopOrderTransactionListReport()
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') , DB::raw('shop_order_transaction.date'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', '!=', 3) 
            ->where('shop_order_transaction.status', 1)
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();


            $total_sales = 0;
            $total_profit = 0;
            foreach ($shop_order_transaction_list as $datavals) {
               
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
            }
           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

           public function fetchOnlineShopOrderTransactionListReportByDate(Request $request)
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') ,
             DB::raw('shop_order_transaction.date'), DB::raw('SUM(total_cash) as total_cash'), DB::raw('SUM(total_online) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();



            $total_sales = 0;
            $total_profit = 0;
            $total_cash = 0;
            $total_online = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchShopOrderTransactionListReportByDate(Request $request)
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') , DB::raw('shop_order_transaction.date'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', '!=', 3) 
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();



            $total_sales = 0;
            $total_profit = 0;
            foreach ($shop_order_transaction_list as $datavals) {
               
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
            }
           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }


       public function fetchOnlineShopOrderTransactionListByDate($date)
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name', 'shop.shop_type_id',
             'c.first_name as requestor_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status', 
             'shop_order_transaction.date', 'shop_order_transaction.profit', 'shop_order_transaction.total_cash', 'shop_order_transaction.total_online')    
             ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', $date)
             ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();

            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit')) 
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')   
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->first();

              $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 2)
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

    public function fetchShopOrderTransaction($id)
    {

         $shopOrderTransaction = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('shop_type as st', 'st.id', '=', 's.shop_type_id')
            ->select('st.id')    
            ->where('sot.id', $id)
            ->first();

        switch ($shopOrderTransaction->id) {
        case 1:
           $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status')    
            ->where('shop_order_transaction.id', $id)
            ->first();
            break;
        case 2:
           $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name','shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status')    
            ->where('shop_order_transaction.id', $id)
            ->first();
            break;
        case 3 :
          $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name','shop.shop_type_id',
             'r.first_name as requestor_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status'
             , DB::raw('CONCAT(r.first_name, " ", r.last_name) AS requestor_name'))   
            ->where('shop_order_transaction.id', $id)
            ->first();
            break;
        default:
            echo "Error";
        }
            return response()->json($data);   
    }

        public function fetchShopOrderTransactionList()
    {
        $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status')    
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
            'shop_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shopOrderTransaction = new ShopOrderTransaction;
        $shopOrderTransaction->shop_id	 = $request->input('shop_id');
        $shopOrderTransaction->shop_order_transaction_total_quantity = $request->input('shop_order_transaction_total_quantity');
        $shopOrderTransaction->shop_order_transaction_total_price = $request->input('shop_order_transaction_total_price');
        $shopOrderTransaction->requestor = $request->input('requestor');
        $shopOrderTransaction->checker = $request->input('checker');
        $shopOrderTransaction->profit = 0;
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->customer_type_id = $request->input('customer_type_id');
        $shopOrderTransaction->date = $request->input('date');
        $shopOrderTransaction->updated_at = now('GMT+8');
        $shopOrderTransaction->created_at = now('GMT+8');
        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        $shopOrderTransaction->shop_order_transaction_total_quantity = $request->input('shop_order_transaction_total_quantity');
        $shopOrderTransaction->shop_order_transaction_total_price = $request->input('shop_order_transaction_total_price');
        $shopOrderTransaction->requestor = $request->input('requestor');
        $shopOrderTransaction->checker = $request->input('checker');
        $shopOrderTransaction->date = $request->input('date');
        $shopOrderTransaction->rider_name = $request->input('rider_name');
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }


    public function updateShopOrderTransactionStatus($id, Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->id);
        $shopOrderTransaction->status = $request->status;
        

        if ($shopOrderTransaction->checker == 0) {

           $online = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_online'))   
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('mop.shop_order_transaction_id', $request->id)
            ->where('pt.type', 2)
            ->first();

           $cash = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id') 
            ->where('mop.shop_order_transaction_id', $request->id)
            ->where('pt.type', 1)
            ->first();

            $shopOrderTransaction->total_cash = $cash->total_cash;
            $shopOrderTransaction->total_online = $online->total_online;

        }

        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        $shopOrderTransaction->delete();
        return response()->json($shopOrderTransaction);
    }

    public function deleteShopOrderTransaction(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
             return response()->json($request);
    }

    public function cancel(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
  
        // $shopOrderDelete = ShopOrder::find($shopOrder->id);
        // $shopOrderDelete->delete();
   
        // $data = DB::table('shop_order')
        //   ->select(DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
        //   ->where('shop_order.shop_transaction_id', $shopOrder->shop_transaction_id)
        //   ->first();

        //  if ($data->shop_order_transaction_total_price == null) {
        //    $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
        //    $shopOrderTransaction->shop_order_transaction_total_quantity = 0;
        //    $shopOrderTransaction->shop_order_transaction_total_price = 0;
        //    $shopOrderTransaction->save();    
        //  } else {
        //    $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
        //    $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
        //    $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
        //    $shopOrderTransaction->save();           
        //  }
          

        // $reduced_stock_id = DB::table('reduced_stock')
        //   ->select(DB::raw('id'))    
        //   ->where('reduced_stock.shop_order_id', $shopOrder->id)
        //   ->first();
        // $reducedStock = ReducedStock::find($reduced_stock_id->id);
        // $reducedStock->delete();

        // $branchStockTransaction = BranchStockTransaction::find($shopOrder->branch_stock_transaction_id);
        // $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction + $shopOrder->shop_order_quantity);
        // $branchStockTransaction->save();

        // $product = Product::find($shopOrder->product_id);
        // $product->stock = ($product->stock + $shopOrder->shop_order_quantity);
        // $product->save();

        return response()->json($shopOrderTransaction);
    }


}
