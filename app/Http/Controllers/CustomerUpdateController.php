<?php

namespace App\Http\Controllers;

use App\Models\CustomerUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerUpdateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customerUpdate = CustomerUpdate::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($customerUpdate);
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
            'customer_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        CustomerUpdate::where('customer_id', '=',  $request->input('customer_id'))->update(['status' => 1]);


        // Create Post
        $customerUpdate = new CustomerUpdate;
        $customerUpdate->customer_id = $request->input('customer_id');
        $customerUpdate->user_id = $request->input('user_id');
        $customerUpdate->status = 0;
        $customerUpdate->chat = $request->input('chat');
        $customerUpdate->promo = $request->input('promo');
        $customerUpdate->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($customerUpdate);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerUpdate  $customerUpdate
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerUpdate $customerUpdate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerUpdate  $customerUpdate
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerUpdate $customerUpdate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerUpdate  $customerUpdate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerUpdate $customerUpdate)
    {
        $customerUpdate = CustomerUpdate::find($brand->id);
        
        $customerUpdate->customer_id = $request->input('customer_id');
        $customerUpdate->status = $request->input('status');
        $customerUpdate->chat = $request->input('chat');
        $customerUpdate->promo = $request->input('promo');
        $customerUpdate->save();
      

        return response()->json($customerUpdate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerUpdate  $customerUpdate
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerUpdate $customerUpdate)
    {
        $customerUpdate = CustomerUpdate::find($customerUpdate->id);
        $customerUpdate->delete();
        return response()->json($customerUpdate);
    }
}
