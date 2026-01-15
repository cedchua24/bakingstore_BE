<?php

namespace App\Http\Controllers;

use App\Models\OrderSupplier;
use App\Models\OrderSupplierTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $data = DB::table('order_supplier')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.brand_id', 'products.weight',  'products.product_name',
             'brand.brand_name', 'order_supplier.price', 'order_supplier.quantity', 'order_supplier.stock_remaining')    
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
        return view('orderSuppliers.create');
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
            'order_supplier_transaction_id' => 'required',
            'product_id' => 'required',
            'price' => 'required',
            'quantity_order' => 'required' 
        ]);
 
        $orderSupplier = new OrderSupplier;
        $orderSupplier->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $orderSupplier->product_id = $request->input('product_id');
        $orderSupplier->price = $request->input('price');
        $orderSupplier->quantity = $request->input('quantity_order');
        $orderSupplier->total_price = $request->input('price') * $request->input('quantity_order');
        $orderSupplier->stock_remaining = $request->input('quantity_order');
        $orderSupplier->expiration = $request->input('expiration');
       

        $orderSupplier_result = DB::table('order_supplier')
        ->select(DB::raw('COUNT(id) as result'))  
        ->where('product_id', $request->input('product_id'))  
        ->where('enable', 1)  
        ->first();

        if ($orderSupplier_result->result == 0 ) {
            $orderSupplier->enable = 1;
        } else {
            $orderSupplier->enable = 0;
        }

        if ($request->input('quantity') == 1) {
            $orderSupplier->variation = 'WHOLESALE';
        } else {
            $orderSupplier->variation = $request->input('variation');
        }
 
        $orderSupplier->save();

        $products = Product::find($request->input('product_id'));
        if ($request->input('variation') === 'WHOLESALE') {
            $products->price = $request->input('price');
        } else {
            $products->price = $request->input('price') * $products->quantity;
        }
        
        $products->save();

        return  response()->json($orderSupplier_result);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderSupplier  $orderSupplier
     * @return \Illuminate\Http\Response
     */
    public function show(OrderSupplier $orderSupplier)
    {
        $orderSupplier = OrderSupplier::find($orderSupplier->id);
        return  response()->json($orderSupplier);
    }

     public function fetchOrderByTransactionId($id)
    {
        $data = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.id', 'order_supplier.order_supplier_transaction_id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.expiration', 'order_supplier.stock_remaining',
             'order_supplier.total_price', 'products.product_name', 'products.id as product_id')    
             ->selectRaw("(CASE WHEN (order_supplier.variation = 'WHOLESALE') THEN products.variation ELSE products.packaging END) as variation")
            ->where('order_supplier_transaction.id', $id)
            ->get();
            return response()->json($data);   
    }

      public function fetchOrderBySupplierId($id)
    {
        $data = DB::table('order_supplier')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.order_supplier_transaction_id',
             'order_supplier.total_price', 'products.product_name', 'order_supplier.product_id', 'order_supplier.stock_remaining',
              'order_supplier.expiration', 'order_supplier.enable')    
            ->where('order_supplier.id', $id)
            ->first();
            return response()->json($data);   
    }

     public function fetchOrderByProductId($id)
    {
        $data = DB::table('order_supplier')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.order_supplier_transaction_id',
             'order_supplier.total_price', 'products.product_name', 'order_supplier.product_id', 'order_supplier.created_at', 'order_supplier.expiration')    
            ->where('order_supplier.product_id', $id)
            ->get();
            return response()->json($data);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderSupplier  $orderSupplier
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderSupplier $orderSupplier)
    {
        $orderSupplier = OrderSupplier::find($orderSupplier->id);
        return response()->json($orderSupplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderSupplier  $orderSupplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderSupplier $orderSupplier)
    {
        $orderSupplier = OrderSupplier::find($orderSupplier->id);

        $orderSupplier->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $orderSupplier->product_id = $request->input('product_id');
        $orderSupplier->price = $request->input('price');
        $orderSupplier->quantity = $request->input('quantity');
        $orderSupplier->total_price = $request->input('price') * $request->input('quantity');
        $orderSupplier->expiration = $request->input('expiration');
        $orderSupplier->enable = $request->input('enable');

        $orderSupplier->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($orderSupplier);
    }

    public function setToActiveExpiration(Request $request)
    {
        DB::table('order_supplier')->where('product_id', $request->input('product_id'))->update(array('enable' => 0));  //update query

        $orderSupplier = OrderSupplier::find($request->input('id'));

        $orderSupplier->enable = $request->input('enable');
        $orderSupplier->save();
        return  response()->json($orderSupplier);
    }

        public function saveAutoPo(Request $request)
    {
          $data = DB::table('product_supplier as ps')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->select('ps.id', 'ps.product_id', 'p.product_name', 'p.price', 'p.stock', 'p.stock_warning')    
            ->where('ps.supplier_id', $request->input('supplier_id'))
            ->where('p.stock', '=<', 'p.stock_warning')
            ->where('p.disabled', 0)
            ->get();
          $added_product = '';
          for($i=0; $i<= sizeof($data)-1; $i++) {
            $orderSupplier = OrderSupplier::where(['order_supplier_transaction_id' => $request->input('order_supplier_transaction_id'),
                                 'product_id' => $data[$i]->product_id])->first();
            if ( is_null($orderSupplier) ) {
                $orderSupplier = new OrderSupplier;
                $orderSupplier->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
                $orderSupplier->product_id = $data[$i]->product_id;
                $orderSupplier->price = $data[$i]->price;
                $orderSupplier->quantity = 1;
                $orderSupplier->total_price = $data[$i]->price * 1;
                $orderSupplier->stock_remaining = 1;
                $orderSupplier->variation = 'WHOLESALE';

                 $orderSupplier_result = DB::table('order_supplier')
                ->select(DB::raw('COUNT(id) as result'))  
                ->where('product_id', $request->input('product_id'))  
                ->where('enable', 1)  
                ->first();

                if ($orderSupplier_result->result == 0 ) {
                    $orderSupplier->enable = 1;
                } else {
                    $orderSupplier->enable = 0;
                }

                $added_product = $added_product.", ".$data[$i]->product_name;
                $orderSupplier->save();
            } 

          }
          
          if ($added_product === "") {
            $added_product = "All Auto PO product already Added!";
            $code = 202;
          }  else {
            $code = 200;
          }

           $response = [
              'data' => $data,
              'code' => $code,
              'added_product' => $added_product,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderSupplier  $orderSupplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderSupplier $orderSupplier)
    {
        $orderSupplier = OrderSupplier::find($orderSupplier->id);
        $orderSupplier->delete();
 
        return response()->json($orderSupplier);
    }
}
