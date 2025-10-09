<?php

namespace App\Http\Controllers;

use App\Models\OutOfStockUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutOfStockUpdateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

     public function fetchCustomerToNotify($id)
    {


         $data = DB::table('out_of_stock_update as oos')
            ->join('customer as c', 'c.id', '=', 'oos.customer_id')
            ->join('products as p', 'p.id', '=', 'oos.product_id')
            ->select('oos.id', 'c.first_name', 'c.last_name', 'p.product_name', 'oos.status', 'oos.created_at', 'oos.updated_at')    
            ->where('p.id', $id)
            ->get();


       $response = [
             'data' => $data,
             'code' => 200,
             'message' => "Successfully Added"
          ];

            return response()->json($response);   
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
            'product_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $outOfStockUpdate = new OutOfStockUpdate;
        $outOfStockUpdate->product_id = $request->input('product_id');
        $outOfStockUpdate->customer_id = $request->input('customer_id');
        $outOfStockUpdate->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($outOfStockUpdate);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OutOfStockUpdate  $outOfStockUpdate
     * @return \Illuminate\Http\Response
     */
    public function show(OutOfStockUpdate $outOfStockUpdate)
    {
        $outOfStockUpdate = OutOfStockUpdate::find($outOfStockUpdate->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($outOfStockUpdate);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OutOfStockUpdate  $outOfStockUpdate
     * @return \Illuminate\Http\Response
     */
    public function edit(OutOfStockUpdate $outOfStockUpdate)
    {
        $outOfStockUpdate = OutOfStockUpdate::find($outOfStockUpdate->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($outOfStockUpdate);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OutOfStockUpdate  $outOfStockUpdate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OutOfStockUpdate $outOfStockUpdate)
    {
        $outOfStockUpdate = OutOfStockUpdate::find($outOfStockUpdate->id);
        
        $outOfStockUpdate->product_id = $request->input('product_id');
        $outOfStockUpdate->customer_id = $request->input('customer_id');
        $outOfStockUpdate->status = $request->input('status');
        $outOfStockUpdate->save();
      

        return response()->json($outOfStockUpdate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OutOfStockUpdate  $outOfStockUpdate
     * @return \Illuminate\Http\Response
     */
    public function destroy(OutOfStockUpdate $outOfStockUpdate)
    {
        //
    }
}
