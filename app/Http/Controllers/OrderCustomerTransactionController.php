<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderCustomerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            // $data = DB::table('order_customer_transaction')

            // ->select('order_customer_transaction.id', 'order_customer_transaction.supplier_id', 'order_customer_transaction.withTax',  'order_customer_transaction.total_transaction_price',
            //  'order_customer_transaction.order_date', 'supplier.supplier_name', 'order_customer_transaction.status')    
            // ->get();
            // return response()->json($data);   

        $orderCustomerTransaction = OrderCustomerTransaction::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($orderCustomerTransaction);
    }

    public function fetchCustomerOrderTransaction($id)
    {
        $data = DB::table('order_customer_transaction as oct')
            ->join('customer as c', 'c.id', '=', 'oct.customer_id')
            ->select('c.first_name', 'c.last_name',
             'c.address','c.email','oct.updated_at','oct.created_at',
             'oct.customer_id',  'oct.total_transaction_price', 'oct.status')    
            ->where('oct.id', $id)
            ->first();
            return response()->json($data);   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('orderCustomerTransaction.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'total_transaction_price' => 'required'
        // ]); 
        // $orderCustomerTransaction = new OrderCustomerTransaction;
        // $orderCustomerTransaction->customer_id = 0;
        // $orderCustomerTransaction->total_transaction_price = $request->input('total_transaction_price');
        // $orderCustomerTransaction->status = 0;
          
        // $orderCustomerTransaction->save();                    
        // return  response()->json($orderCustomerTransaction);

        $this->validate($request, [
            'customer_id' => 'required'
        ]); 
        $orderCustomerTransaction = new OrderCustomerTransaction;
        $orderCustomerTransaction->customer_id = $request->input('customer_id');
        $orderCustomerTransaction->total_transaction_price = 0;
        $orderCustomerTransaction->status = 0;
          
        $orderCustomerTransaction->save();                    
        return  response()->json($orderCustomerTransaction);


        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderCustomerTransaction  $orderCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(OrderCustomerTransaction $orderCustomerTransaction)
    {
        $orderCustomerTransaction = OrderCustomerTransaction::find($orderCustomerTransaction->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($orderCustomerTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderCustomerTransaction  $orderCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderCustomerTransaction $orderCustomerTransaction)
    {
        $orderCustomerTransaction = OrderCustomerTransaction::find($orderCustomerTransaction->id);
        return response()->json($orderCustomerTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\orderCustomerTransaction  $orderCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $order_customer_transaction_id)
    {
        $orderCustomerTransaction = OrderCustomerTransaction::find($order_customer_transaction_id);

        $total_transaction_price = DB::table('order_customer')
            ->join('order_customer_transaction', 'order_customer_transaction.id', '=', 'order_customer.order_customer_transaction_id')
            ->where('order_customer_transaction.id', $order_customer_transaction_id)
            ->sum('order_customer.total_price');
        
        // $orderCustomerTransaction->supplier_id = $request->input('supplier_id');
        // $orderCustomerTransaction->withTax = $request->input('withTax');
        $orderCustomerTransaction->total_transaction_price = $total_transaction_price; 
        // $orderCustomerTransaction->order_date = $request->input('order_date');
        // $orderCustomerTransaction->status = $request->input('status');
        $orderCustomerTransaction->save();
      

         return response()->json($orderCustomerTransaction);
        //  return $order_customer_transaction_id;
    }

        public function setToCompleteTransaction($id)
    {
        $orderCustomerTransaction = OrderCustomerTransaction::find($id);

            $total_transaction_price = DB::table('order_customer')
            ->join('order_customer_transaction', 'order_customer_transaction.id', '=', 'order_customer.order_customer_transaction_id')
            ->join('products', 'products.id', '=', 'order_customer.product_id')
            ->select('order_customer.product_id', 'order_customer.quantity')
            ->where('order_customer_transaction.id', $id)
            ->get();

            foreach ($total_transaction_price as $row) {
                $product = Product::find($row->product_id);
                $initialStock = $product->stock;
                $product->stock = ($initialStock + $row->quantity);
                $product->save();
            }

        $orderCustomerTransaction->status = 'COMPLETED';
        $orderCustomerTransaction->save();
      

        return response()->json($orderCustomerTransaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\orderCustomerTransaction  $orderCustomerTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderCustomerTransaction $orderCustomerTransaction)
    {
        $orderCustomerTransaction = OrderCustomerTransaction::find($orderCustomerTransaction->id);
        $orderCustomerTransaction->delete();

        //  $orderSupplier = OrderSupplier::find($orderSupplier->id);
        // $orderSupplier->delete();
        return response()->json($orderCustomerTransaction);
    }
}
