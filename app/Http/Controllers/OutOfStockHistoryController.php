<?php

namespace App\Http\Controllers;

use App\Models\OutOfStockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutOfStockHistoryController extends Controller
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OutOfStockHistory  $outOfStockHistory
     * @return \Illuminate\Http\Response
     */
    public function show(OutOfStockHistory $outOfStockHistory)
    {
        //
    }

         public function fetchOOSbyProductId($id)
    {


         $data = DB::table('out_of_stock_history as oos')
            ->join('products as p', 'p.id', '=', 'oos.product_id')
            ->select('oos.id', 'p.product_name', 'oos.comment', 'oos.created_at', 'oos.updated_at')    
            ->where('p.id', $id)
            ->get();


       $response = [
             'data' => $data,
             'product_name' => $data[0]->product_name ? $data[0]->product_name : '',
             'code' => 200,
             'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OutOfStockHistory  $outOfStockHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(OutOfStockHistory $outOfStockHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OutOfStockHistory  $outOfStockHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OutOfStockHistory $outOfStockHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OutOfStockHistory  $outOfStockHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(OutOfStockHistory $outOfStockHistory)
    {
        //
    }
}
