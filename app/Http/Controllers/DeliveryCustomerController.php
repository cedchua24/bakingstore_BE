<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCustomer;
use App\Models\ShopOrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $deliveryCustomer = DeliveryCustomer::all();
        return response()->json($deliveryCustomer);
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
            'shop_order_transaction_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
       $test;
        $data = DB::table('delivery_customer')->where('shop_order_transaction_id', $request->input('shop_order_transaction_id'))->first();
        if ($data == null) {
             $test =0;
            $deliveryCustomer = new DeliveryCustomer;
        } else {
            $deliveryCustomer = DeliveryCustomer::find($data->id);
             $test =1;
        }
       
        $deliveryCustomer->shop_order_transaction_id = $request->input('shop_order_transaction_id');
        $deliveryCustomer->date = $request->input('date');
        $deliveryCustomer->name = $request->input('name');
        $deliveryCustomer->note = $request->input('note');
        $deliveryCustomer->contact_number = $request->input('contact_number');
        $deliveryCustomer->address = $request->input('address');
        $deliveryCustomer->status = $request->input('status');
        $deliveryCustomer->save();

         $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_order_transaction_id'));
         $shopOrderTransaction->delivery_customer_id = $deliveryCustomer->id;
         $shopOrderTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($test);
    }

    

      public function fetchDeliveryById($id)
    {
            $data = DB::table('delivery_customer')->where('shop_order_transaction_id', $id)->first();
            return response()->json($data);   
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryCustomer  $deliveryCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryCustomer $deliveryCustomer)
    {
        $deliveryCustomer = DeliveryCustomer::find($deliveryCustomer->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($deliveryCustomer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryCustomer  $deliveryCustomer
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryCustomer $deliveryCustomer)
    {
        $deliveryCustomer = DeliveryCustomer::find($deliveryCustomer->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($deliveryCustomer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeliveryCustomer  $deliveryCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryCustomer $deliveryCustomer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryCustomer  $deliveryCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryCustomer $deliveryCustomer)
    {
       $shopOrderTransaction = ShopOrderTransaction::find($deliveryCustomer->id);
       $deliveryCustomer = DeliveryCustomer::find($shopOrderTransaction->delivery_customer_id);
       $deliveryCustomer->delete();

       $shopOrderTransaction->delivery_customer_id = 0;
       $shopOrderTransaction->save();  


        return response()->json($shopOrderTransaction);
    }

        public function deleteTransaction($id)
    {
       $shopOrderTransaction = ShopOrderTransaction::find($id);
       $deliveryCustomer = DeliveryCustomer::find($shopOrderTransaction->delivery_customer_id);
       $deliveryCustomer->delete();

       $shopOrderTransaction->delivery_customer_id = 0;
       $shopOrderTransaction->save();  


        return response()->json($shopOrderTransaction);
    }
    
}
