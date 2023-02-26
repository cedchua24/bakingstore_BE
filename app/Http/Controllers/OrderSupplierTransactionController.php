<?php

namespace App\Http\Controllers;

use App\Models\OrderSupplierTransaction;
use App\Models\OrderSupplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderSupplierTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
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
        return view('orderSupplierTransaction.create');
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
            'supplier_id' => 'required',
            'withTax' => 'required',
            'order_date' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $orderSupplierTransaction = new OrderSupplierTransaction;
        $orderSupplierTransaction->supplier_id = $request->input('supplier_id');
        $orderSupplierTransaction->withTax = $request->input('withTax');
        $orderSupplierTransaction->total_transaction_price = $request->input('total_transaction_price');
        $orderSupplierTransaction->status = $request->input('status');
        $orderSupplierTransaction->stock_status = 0;
        $orderSupplierTransaction->order_date = $request->input('order_date');
          
        $orderSupplierTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($orderSupplierTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($orderSupplierTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        return response()->json($orderSupplierTransaction);
    }

    public function fetchByOrderSupplierTransactionId($id)
    {
        $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status')    
            ->where('order_supplier_transaction.id', $id)
            ->first();
            return response()->json($data);   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);

        $total_transaction_price = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->where('order_supplier_transaction.id', $request->id)
            ->sum('order_supplier.total_price');
        
        $orderSupplierTransaction->supplier_id = $request->input('supplier_id');
        $orderSupplierTransaction->withTax = $request->input('withTax');
        $orderSupplierTransaction->total_transaction_price = $total_transaction_price;
        // $orderSupplierTransaction->total_transaction_price = 3000;
        $orderSupplierTransaction->order_date = $request->input('order_date');
        $orderSupplierTransaction->status = $request->input('status');
        $orderSupplierTransaction->save();
      

        return response()->json($orderSupplierTransaction);
    }

        public function setToCompleteTransaction($id)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($id);

            $total_transaction_price = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.product_id', 'order_supplier.quantity')
            ->where('order_supplier_transaction.id', $id)
            ->get();

            foreach ($total_transaction_price as $row) {
                $product = Product::find($row->product_id);
                $initialStock = $product->stock;
                $product->stock = ($initialStock + $row->quantity);
                $product->save();
            }

        $orderSupplierTransaction->status = 'COMPLETED';
        $orderSupplierTransaction->save();
      

        return response()->json($orderSupplierTransaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        $orderSupplierTransaction->delete();

        //  $orderSupplier = OrderSupplier::find($orderSupplier->id);
        // $orderSupplier->delete();
        return response()->json($orderSupplierTransaction);
    }
}
