<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ReducedStock;
use App\Models\ShopOrderTransaction;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shopOrder = ShopOrder::all();
        return response()->json($shopOrder); 
    }
    

      public function fetchShopOrder($id)
    {
        
        $data = DB::table('shop_order')
            ->join('mark_up_product as mup', 'mup.id', '=', 'shop_order.mark_up_product_id')
            ->join('products', 'products.id', '=', 'mup.product_id')
            ->select('shop_order.id', 'shop_order.branch_stock_transaction_id', 'shop_order.shop_order_price', 'shop_order.shop_order_profit', 'shop_order.shop_order_quantity', 'shop_order.shop_transaction_id',
             'shop_order.shop_order_total_price','products.variation', 'products.product_name', 'products.id as product_id', 'mup.business_type', 'mup.id as mark_up_product_id')    
            ->where('shop_order.id', $id)
            ->first();
            return response()->json($data);   
    }

       public function fetchShopOrderDTO($id)
    {
        $data = DB::table('shop_order')
            ->join('mark_up_product as mup', 'mup.id', '=', 'shop_order.mark_up_product_id')
            ->join('products', 'products.id', '=', 'mup.product_id')
            ->select('shop_order.id', 'shop_order.shop_order_price',  'shop_order.shop_order_quantity', 'shop_order.shop_transaction_id',
             'shop_order.shop_order_total_price', 'products.product_name', 'products.id as product_id', 'products.quantity',
              'products.weight', 'products.packaging', 'products.variation', 'mup.id as mark_up_product_id', 'mup.business_type')    
            ->where('shop_order.shop_transaction_id', $id)
            ->get();

        
            $shopOrderTransaction = ShopOrderTransaction::find($id);    
            $response = [
              'shopOrderList'=> $data,
              'shopOrderTransaction'=> $shopOrderTransaction  
            ];      
            return response()->json($response);   
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
            'shop_transaction_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shopOrder = new ShopOrder; 
        $shopOrder->shop_transaction_id	= $request->input('shop_transaction_id');
        $shopOrder->branch_stock_transaction_id	= $request->input('branch_stock_transaction_id');
        $shopOrder->mark_up_product_id = $request->input('mark_up_product_id');
        $shopOrder->shop_order_profit = $request->input('shop_order_profit');
        $shopOrder->product_id = $request->input('product_id');
        $shopOrder->shop_order_quantity = $request->input('shop_order_quantity');
        $shopOrder->shop_order_price = $request->input('shop_order_price');
        $shopOrder->shop_order_total_price = $request->input('shop_order_total_price');
        $shopOrder->shop_order_profit = $request->input('shop_order_profit');;
        $shopOrder->save();

        $data = DB::table('shop_order')
          ->select(DB::raw('SUM(shop_order_profit) as shop_order_total_profit'),DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
          ->where('shop_order.shop_transaction_id', $request->input('shop_transaction_id'))
          ->first();
    

        $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_transaction_id'));
        $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
        $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
        $shopOrderTransaction->profit = $data->shop_order_total_profit;
        $shopOrderTransaction->save();

        $reducedStock = new ReducedStock;
        $reducedStock->shop_order_id = $shopOrder->id;
        $reducedStock->mark_up_product_id = $request->input('mark_up_product_id');
        $reducedStock->reduced_stock = $request->input('shop_order_quantity');
        $reducedStock->reduced_stock_by_shop_id = $shopOrderTransaction->shop_id;
        $reducedStock->save();

        // $branchStockTransaction = BranchStockTransaction::find($request->input('branch_stock_transaction_id'));
        // $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction - $request->input('shop_order_quantity'));
        // $branchStockTransaction->save();

        $product = Product::find($request->input('product_id'));
        if ($request->input('business_type') === 'WHOLESALE') {
          $product->stock = ($product->stock - $request->input('shop_order_quantity'));
          $product->stock_pc =  $product->stock * $product->quantity;
          $product->save();
        } else {
          $newStock = $product->stock_pc - $request->input('shop_order_quantity');
          $product->stock_pc = $newStock;
          $product->stock = ($product->stock_pc / $product->quantity);
          $product->save();
        }



        $response = [
              'message' => "Successfully Added"
          ];
        return  response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShopOrder  $shopOrder
     * @return \Illuminate\Http\Response
     */
    public function show(ShopOrder $shopOrder)
    {
        $shopOrder = ShopOrder::find($shopOrder->id);
        return  response()->json($shopOrder);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShopOrder  $shopOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(ShopOrder $shopOrder)
    {
        $reducedStock = ShopOrder::find($shopOrder->id);
        return  response()->json($shopOrder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShopOrder  $shopOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopOrder $shopOrder)
    {
        $shopOrder = ShopOrder::find($shopOrder->id);
        $product = Product::find($request->input('product_id'));
        
        $newStocks = ($shopOrder->shop_order_quantity - $request->input('shop_order_quantity'));
        $newStocks_pc = 0;
        $currentStock = 0;
         

        if ($request->input('business_type') === 'WHOLESALE') {
           $currentStock = $product->stock;
           $newStocks_pc = ($shopOrder->shop_order_quantity * $product->weight) - ($request->input('shop_order_quantity') * $product->weight);
        } else {
          $currentStock = $product->stock_pc;
          $newStocks_pc =  $product->stock_pc - ($request->input('shop_order_quantity') - $shopOrder->shop_order_quantity);
        }
       
        if ($request->input('shop_order_quantity') > $currentStock) {
          $response = [
            'id' => $request->input('product_id'),
            'stock' => $product->stock,
            'code' => 400,
            'message' => "Product is greater than Stock"
          ];
        } else {
         $shopOrderQuantity =  $shopOrder->shop_order_quantity;

          $shopOrder->product_id = $request->input('product_id');
          $shopOrder->shop_order_quantity = $request->input('shop_order_quantity');
          $shopOrder->shop_order_price = $request->input('shop_order_price');
          $shopOrder->shop_order_profit = $request->input('shop_order_profit');
          $shopOrder->shop_order_total_price = $request->input('shop_order_total_price');
          $shopOrder->save();

          $data = DB::table('shop_order')
            ->select(DB::raw('SUM(shop_order_profit) as shop_order_total_profit'), DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
            ->where('shop_order.shop_transaction_id', $request->input('shop_transaction_id'))
            ->first();
          
          

          $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_transaction_id'));
          $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
          $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
          $shopOrderTransaction->profit = $data->shop_order_total_profit;
          $shopOrderTransaction->save();

          $reducedStock = new ReducedStock;
          $reducedStock->shop_order_id = $shopOrder->id;
          $reducedStock->mark_up_product_id = $request->input('mark_up_product_id');
          $reducedStock->reduced_stock = $request->input('shop_order_quantity');
          $reducedStock->reduced_stock_by_shop_id = $shopOrderTransaction->shop_id;
          $reducedStock->save();

          // $branchStockTransaction = BranchStockTransaction::find($request->input('branch_stock_transaction_id'));
          // $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction - $request->input('shop_order_quantity'));
          // $branchStockTransaction->save();

        
        if ($request->input('business_type') === 'WHOLESALE') {
           $product->stock = ($product->stock + $newStocks);
          if ($product->stock_pc != null) {
              $product->stock_pc =  $product->stock * $product->quantity;
          }   
          
        } else {
          if ($product->stock_pc != null) {
            $stock_pc = ($product->stock_pc + $shopOrderQuantity) - $request->input('shop_order_quantity');
            $retailStock = ($shopOrderQuantity / $product->weight) + $product->stock;   
            $stock = $retailStock - ($request->input('shop_order_quantity') / $product->weight); 
            //
            $product->stock_pc = $stock_pc;
            $product->stock = ($product->stock_pc / $product->quantity);
          } 
        }  

          $product->save();
          

            $response = [
              'id' => $request->input('product_id'),
              'id' => $request->input('product_id'),
              'id' => $request->input('product_id'),
              'stock' => $shopOrder,
              'code' => 200,
              'message' => "Successfully Added"
          ];
      }
      return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShopOrder  $shopOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ShopOrder $shopOrder)
    {
  
        $shopOrderDelete = ShopOrder::find($shopOrder->id);
        $shopOrderDelete->delete();
   

        $data = DB::table('shop_order')
            ->select(DB::raw('SUM(shop_order_profit) as shop_order_total_profit'), DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
            ->where('shop_order.shop_transaction_id', $shopOrder->shop_transaction_id)
            ->first();

         if ($data->shop_order_transaction_total_price == null) {
          $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
          $shopOrderTransaction->shop_order_transaction_total_quantity = 0;
          $shopOrderTransaction->shop_order_transaction_total_price = 0;
          $shopOrderTransaction->profit = $data->shop_order_total_profit;
          $shopOrderTransaction->save();    
         } else {
          $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
          $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
          $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
          $shopOrderTransaction->profit = $data->shop_order_total_profit;
          $shopOrderTransaction->save();           
         }
          

        $reduced_stock_id = DB::table('reduced_stock')
          ->join('mark_up_product as mup', 'mup.id', '=', 'reduced_stock.mark_up_product_id')
          ->select('reduced_stock.id', 'mup.business_type', 'mup.id', 'reduced_stock.id')    
          ->where('reduced_stock.shop_order_id', $shopOrder->id)
          ->first();
        $reducedStock = ReducedStock::find($reduced_stock_id->id);
       

        // $branchStockTransaction = BranchStockTransaction::find($shopOrder->branch_stock_transaction_id);
        // $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction + $shopOrder->shop_order_quantity);
        // $branchStockTransaction->save();

        // $product = Product::find($shopOrder->product_id);
        // $product->stock = ($product->stock + $shopOrder->shop_order_quantity);
        // $product->save();

         $product = Product::find($shopOrder->product_id);
        if ($reduced_stock_id->business_type === 'WHOLESALE') {
          $product->stock = ($product->stock + $shopOrder->shop_order_quantity);
          $product->stock_pc =  $product->stock * $product->quantity;
          $product->save();
        } else {
          $newStock = $product->stock_pc + $shopOrder->shop_order_quantity;
          $product->stock_pc = $newStock;
          $product->stock = ($newStock / $product->quantity);
          $product->save();
        }
         $reducedStock->delete();

        
        $response = [
              'message' => "Successfully Added",
              '$shopOrder' => $shopOrder
          ];
        return  response()->json($response);
        // return response()->json($reduced_stock_id);
    }

    
}
