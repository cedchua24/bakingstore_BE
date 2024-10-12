<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('categories.index')->with('categories', $categories);
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled')   
            ->orderBy('c.first_name', 'asc') 
            ->get();
            return response()->json($data); 
    }

       public function fetchCustomerEnabled($id)
    {
         $data = DB::table('customer as c')
            ->select('c.id', 'c.disabled', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address')   
            ->orderBy('c.first_name', 'asc') 
            ->where('c.disabled', 0)
            ->get();
            return response()->json($data); 

    }

        public function fetchCustomerProduct($id)
    {

           $data = DB::table('customer as c')
            ->select('p.product_name', 'mup.business_type', 'mup.new_price', DB::raw('SUM(so.shop_order_quantity) as total_quantity'), DB::raw('SUM(so.shop_order_total_price) as total_price'), DB::raw('SUM(so.shop_order_profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->where('c.id', $id) 
            ->groupBy('mup.id') 
            ->orderBy('total_quantity', 'desc')
            ->get();

            $customerDetails = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled')   
            ->where('c.id', $id) 
            ->first();
   
           $response = [
              'data' => $data,
              'customerDetails' => $customerDetails,
              'code' => 200,
              'date' => date('Y-m-d'),
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }


       public function fetchCustomerTransaction($id)
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             'c.first_name as requestor_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name')    
             ->where('shop_order_transaction.requestor', $id)
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();
            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'),  DB::raw('COUNT(shop_id) as total_count'),)  
            ->where('shop_order_transaction.requestor', $id)
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop_order_transaction.requestor', $id)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop_order_transaction.requestor', $id)
            ->first();

           $total = DB::table('shop_order_transaction')
            ->select(DB::raw('COUNT(shop_id) as total_count'),)  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop_order_transaction.requestor', $id)
            ->first();

            $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('sot.requestor', $id)
            ->groupBy('pt.id')
            ->get();


            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }

             $customerDetails = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled')   
            ->where('c.id', $id) 
            ->first();
   

           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_count' =>$total->total_count,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'customerDetails' => $customerDetails,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
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
            'first_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $customer = new Customer;
        $customer->first_name = $request->input('first_name');
        $customer->last_name = $request->input('last_name');
        $customer->contact_number = $request->input('contact_number');
        $customer->email = $request->input('email');
        $customer->address = $request->input('address');
        $customer->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($customer);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($customer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer = Customer::find($customer->id);
        
        $customer->first_name = $request->input('first_name');
        $customer->last_name = $request->input('last_name');
        $customer->contact_number = $request->input('contact_number');
        $customer->email = $request->input('email');
        $customer->address = $request->input('address');
        $customer->disabled = $request->input('disabled');
        $customer->save();
      

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        $customer->delete();
        return response()->json($customer);
    }
}
