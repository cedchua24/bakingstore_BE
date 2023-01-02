<?php

namespace App\Http\Controllers;

use App\Models\OrderSupplier;
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
            'quantity' => 'required'
        ]);

        $orderSupplier = new OrderSupplier;
        $orderSupplier->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $orderSupplier->product_id = $request->input('product_id');
        $orderSupplier->price = $request->input('price');
        $orderSupplier->quantity = $request->input('quantity');
        $orderSupplier->total_price = $request->input('price') * $request->input('quantity');
        $orderSupplier->stock_remaining = $request->input('quantity');

        $orderSupplier->save();

        return  response()->json($orderSupplier);
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
            ->select('order_supplier.id', 'order_supplier.order_supplier_transaction_id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.stock_remaining',
             'order_supplier.total_price', 'products.product_name')    
            ->where('order_supplier_transaction.id', $id)
            ->get();
            return response()->json($data);   
    }

      public function fetchOrderBySupplierId($id)
    {
        $data = DB::table('order_supplier')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.order_supplier_transaction_id',
             'order_supplier.total_price', 'products.product_name', 'order_supplier.product_id', 'order_supplier.stock_remaining')    
            ->where('order_supplier.id', $id)
            ->first();
            return response()->json($data);   
    }

     public function fetchOrderByProductId($id)
    {
        $data = DB::table('order_supplier')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.id', 'order_supplier.price',  'order_supplier.quantity', 'order_supplier.order_supplier_transaction_id',
             'order_supplier.total_price', 'products.product_name', 'order_supplier.product_id', 'order_supplier.created_at')    
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

        $orderSupplier->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($orderSupplier);
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
