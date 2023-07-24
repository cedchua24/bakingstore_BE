<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomer;
use App\Models\Product;
use App\Models\OrderCustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $data = DB::table('order_customer')
            ->join('products', 'products.id', '=', 'order_customer.product_id')
            ->select( 'products.weight',  'products.product_name',
              'order_customer.price', 'order_customer.quantity')    
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
        $orderCustomer = new OrderCustomer; 
        $orderCustomer->shop_transaction_id	= $request->input('shop_transaction_id');
        $orderCustomer->branch_stock_transaction_id	= $request->input('branch_stock_transaction_id');
        $orderCustomer->product_id = $request->input('product_id');
        $orderCustomer->shop_order_quantity = $request->input('shop_order_quantity');
        $orderCustomer->shop_order_price = $request->input('shop_order_price');
        $orderCustomer->shop_order_total_price = $request->input('shop_order_total_price');
        $orderCustomer->save();

        $data = DB::table('shop_order')
          ->select(DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
          ->where('shop_order.shop_transaction_id', $request->input('shop_transaction_id'))
          ->first();
    

        $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_transaction_id'));
        $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
        $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
        $shopOrderTransaction->save();

        $reducedStock = new ReducedStock;
        $reducedStock->shop_order_id = $orderCustomer->id;
        $reducedStock->product_id = $request->input('product_id');
        $reducedStock->reduced_stock = $request->input('shop_order_quantity');
        $reducedStock->reduced_stock_by_shop_id = $shopOrderTransaction->shop_id;
        $reducedStock->save();

        $branchStockTransaction = BranchStockTransaction::find($request->input('branch_stock_transaction_id'));
        $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction - $request->input('shop_order_quantity'));
        $branchStockTransaction->save();

        
        $product = Product::find($request->input('product_id'));
        $product->stock = ($product->stock - $request->input('shop_order_quantity'));
        $product->save();


        return  response()->json($orderCustomer);


    }
     public function saveCustomerTransactionV2(Request $request)
    {
        $this->validate($request, [
            'shop_transaction_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $orderCustomer = new OrderCustomer; 
        $orderCustomer->shop_transaction_id	= $request->input('shop_transaction_id');
        $orderCustomer->mark_up_id = 0;
        $orderCustomer->branch_stock_transaction_id	= $request->input('branch_stock_transaction_id');
        $orderCustomer->product_id = $request->input('product_id');
        $orderCustomer->shop_order_quantity = $request->input('shop_order_quantity');
        $orderCustomer->shop_order_price = $request->input('shop_order_price');
        $orderCustomer->shop_order_total_price = $request->input('shop_order_total_price');
        $orderCustomer->save();

        $data = DB::table('shop_order')
          ->select(DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
          ->where('shop_order.shop_transaction_id', $request->input('shop_transaction_id'))
          ->first();
    

        $orderCustomerTransaction = OrderCustomerTransaction::find($request->input('shop_transaction_id'));
        $orderCustomerTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
        $orderCustomerTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
        $orderCustomerTransaction->save();

        $reducedStock = new ReducedStock;
        $reducedStock->shop_order_id = $orderCustomer->id;
        $reducedStock->product_id = $request->input('product_id');
        $reducedStock->reduced_stock = $request->input('shop_order_quantity');
        $reducedStock->reduced_stock_by_shop_id = $orderCustomerTransaction->shop_id;
        $reducedStock->save();

        $branchStockTransaction = BranchStockTransaction::find($request->input('branch_stock_transaction_id'));
        $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction - $request->input('shop_order_quantity'));
        $branchStockTransaction->save();

        
        $product = Product::find($request->input('product_id'));
        $product->stock = ($product->stock - $request->input('shop_order_quantity'));
        $product->save();


        return  response()->json($product);

    }

        public function saveCustomerTransaction(Request $request)
    {
            $data = $request ->json()->all();
            $orderCustomerList = $data['orderCustomerList'];    
            $grandTotal = $data['grandTotal'];

            $orderCustomerTransaction = new OrderCustomerTransaction;
            $orderCustomerTransaction->total_transaction_price = $grandTotal;
            $orderCustomerTransaction->customer_id = 0;
            $orderCustomerTransaction->status = 'COMPLETED';
            $orderCustomerTransaction->save();

                foreach ($orderCustomerList as $row) {
                    // $result = $row['id'];
                    $orderCustomer = new OrderCustomer;
                    $orderCustomer->order_customer_transaction_id = $orderCustomerTransaction->id;
                    $orderCustomer->mark_up_id = $row['mark_up_id'];
                    $orderCustomer->branch_stock_transaction_id = 0;
                    $orderCustomer->price = $row['price'];
                    $orderCustomer->quantity = $row['quantity'];
                    $orderCustomer->total_price = $row['total_price'];
                    $orderCustomer->discount = $row['discount'];
                    $orderCustomer->save(); 

                    // update stock
                    $id = $row['product_id'];
                    $product = Product::find($id);
                    $product->stock = $product->stock - $row['quantity'];
                    $product->save();
            }
        
          $response = [
              'data' => $orderCustomerTransaction,
              'code' => 200,
              'message' => "Successfully Added"
          ];
       return  $response;

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderCustomer  $orderCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(OrderCustomer $orderCustomer)
    {
        $orderCustomer = OrderCustomer::find($orderCustomer->id);
        return  response()->json($orderCustomer);
    }

    public function fetchOrderByTransactionId($id)
      {
        $data = DB::table('order_customer')
            ->join('order_customer_transaction', 'order_customer_transaction.id', '=', 'order_customer.order_customer_transaction_id')
            ->join('mark_up_product', 'mark_up_product.id', '=', 'order_customer.mark_up_id')
            ->join('products', 'products.id', '=', 'mark_up_product.product_id')
            ->select('order_customer.id', 'order_customer.order_customer_transaction_id', 'order_customer.price',  'order_customer.quantity',
             'order_customer.total_price', 'products.product_name')    
            ->where('order_customer_transaction.id', $id)
            ->get();
            return response()->json($data);   
      }


     public function fetchOrderByProductId($id)
    {
        $data = DB::table('order_customer')
            ->join('products', 'products.id', '=', 'order_customer.product_id')
            ->select('order_customer.id', 'order_customer.price',  'order_customer.quantity', 'order_customer.order_customer_transaction_id',
             'order_customer.total_price', 'products.product_name', 'order_customer.product_id', 'order_customer.created_at')    
            ->where('order_customer.product_id', $id)
            ->get();
            return response()->json($data);   
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderCustomer  $orderCustomer
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderCustomer $orderCustomer)
    {
        $orderCustomer = OrderCustomer::find($orderCustomer->id);
        return response()->json($orderCustomer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderCustomer  $orderCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderCustomer $orderCustomer)
    {
        $orderCustomer = OrderCustomer::find($orderCustomer->id);
        $orderCustomer->price = $request->input('price');
        $orderCustomer->quantity = $request->input('quantity');
        $orderCustomer->total_price = $request->input('price') * $request->input('quantity');
        $orderCustomer->discount = 0;

        $orderCustomer->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($orderCustomer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderCustomer  $orderCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderCustomer $orderCustomer)
    {
        $orderCustomer = OrderCustomer::find($orderCustomer->id);
        $orderCustomer->delete();

        $orderCustomerTransaction = OrderCustomerTransaction::find($orderCustomer->order_customer_transaction_id);

        $total_transaction_price = DB::table('order_customer')
            ->join('order_customer_transaction', 'order_customer_transaction.id', '=', 'order_customer.order_customer_transaction_id')
            ->where('order_customer_transaction.id', $orderCustomer->order_customer_transaction_id)
            ->sum('order_customer.total_price');
        

        $orderCustomerTransaction->total_transaction_price = $total_transaction_price; 

        $orderCustomerTransaction->save();
 
        return response()->json($orderCustomer);
    }
}  
