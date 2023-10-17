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
        $customers = Customer::all();
        // return view('categories.index')->with('categories', $categories);
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address')   
            ->orderBy('c.first_name', 'asc') 
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
