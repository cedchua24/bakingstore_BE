<?php

namespace App\Http\Controllers;

use App\Models\ReducedStock;
use Illuminate\Http\Request;

class ReducedStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reducedStock = ReducedStock::all();
        return response()->json($reducedStock); 
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
            'product_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $reducedStock = new ReducedStock;
        $reducedStock->product_id = $request->input('product_id');
        $reducedStock->reduced_stock = $request->input('reduced_stock');
        $reducedStock->reduced_stock_by_shop_id = $request->input('reduced_stock_by_shop_id');
        $reducedStock->save();
        return  response()->json($reducedStock); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReducedStock  $reducedStock
     * @return \Illuminate\Http\Response
     */
    public function show(ReducedStock $reducedStock)
    {
        $reducedStock = ReducedStock::find($reducedStock->id);
        return  response()->json($reducedStock);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReducedStock  $reducedStock
     * @return \Illuminate\Http\Response
     */
    public function edit(ReducedStock $reducedStock)
    {
        $shop = ReducedStock::find($reducedStock->id);
        return  response()->json($reducedStock);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReducedStock  $reducedStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReducedStock $reducedStock)
    {
        $reducedStock = ReducedStock::find($reducedStock->id);
        
        $reducedStock->product_id = $request->input('product_id');
        $reducedStock->reduced_stock = $request->input('reduced_stock');
        $reducedStock->reduced_stock_by_shop_id = $request->input('reduced_stock_by_shop_id');
        $reducedStock->save();
        return  response()->json($reducedStock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReducedStock  $reducedStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReducedStock $reducedStock)
    {
        $reducedStock = ReducedStock::find($reducedStock->id);
        $reducedStock->delete();
        return response()->json($reducedStock);
    }
}
