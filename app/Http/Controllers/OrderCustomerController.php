<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomer;
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
        $this->validate($request, [
            'product_id' => 'required',
            'price' => 'required',
            'total_price' => 'required',
            'quantity' => 'required'
        ]);
        

        $orderCustomer = new OrderCustomer;
        $orderCustomer->order_customer_transaction_id = 0;
        $orderCustomer->product_id = $request->input('product_id');
        $orderCustomer->price = $request->input('price');
        $orderCustomer->quantity = $request->input('quantity');
        $orderCustomer->total_price = $request->input('total_price');
        $orderCustomer->discount = $request->input('discount');

        $orderCustomer->save();

        return  response()->json($orderCustomer);
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
            ->join('products', 'products.id', '=', 'order_customer.product_id')
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

        $orderCustomer->order_customer_transaction_id = $request->input('order_customer_transaction_id');
        $orderCustomer->product_id = $request->input('product_id');
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
 
        return response()->json($orderCustomer);
    }
}
