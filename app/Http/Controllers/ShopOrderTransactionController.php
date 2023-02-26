<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ReducedStock;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use App\Models\ShopOrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ShopOrderTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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

    public function fetchShopOrderTransaction($id)
    {
        $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status')    
            ->where('shop_order_transaction.id', $id)
            ->first();
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
        $shopOrderTransaction->status = 2;
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
        //
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
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }


    public function updateShopOrderTransactionStatus($id, Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->id);
        $shopOrderTransaction->status = 1;
        $shopOrderTransaction->save();
        return  response()->json($request);
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

    public function cancel(Request $request, ShopOrder $shopOrder)
    {
  
        $shopOrderDelete = ShopOrder::find($shopOrder->id);
        $shopOrderDelete->delete();
   
        $data = DB::table('shop_order')
          ->select(DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
          ->where('shop_order.shop_transaction_id', $shopOrder->shop_transaction_id)
          ->first();

         if ($data->shop_order_transaction_total_price == null) {
           $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
           $shopOrderTransaction->shop_order_transaction_total_quantity = 0;
           $shopOrderTransaction->shop_order_transaction_total_price = 0;
           $shopOrderTransaction->save();    
         } else {
           $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
           $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
           $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
           $shopOrderTransaction->save();           
         }
          

        $reduced_stock_id = DB::table('reduced_stock')
          ->select(DB::raw('id'))    
          ->where('reduced_stock.shop_order_id', $shopOrder->id)
          ->first();
        $reducedStock = ReducedStock::find($reduced_stock_id->id);
        $reducedStock->delete();

        $branchStockTransaction = BranchStockTransaction::find($shopOrder->branch_stock_transaction_id);
        $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction + $shopOrder->shop_order_quantity);
        $branchStockTransaction->save();

        $product = Product::find($shopOrder->product_id);
        $product->stock = ($product->stock + $shopOrder->shop_order_quantity);
        $product->save();

        return response()->json($data);
    }


}
