<?php

namespace App\Http\Controllers;

use App\Models\StockOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $data = DB::table('products as p')
            ->join('stock_order as so', 'so.product_id', '=', 'p.id')
            ->select('p.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at')
            ->orderBy('p.updated_at', 'DESC')
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
            'stock' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $stockOrder = new StockOrder;
        $stockOrder->product_id = $request->input('product_id');
        $stockOrder->pack = $request->input('pack');
        $stockOrder->stock_type = $request->input('stock_type');
        $stockOrder->stock = $request->input('stock');
        $stockOrder->total_stock = $request->input('total_stock');
        $stockOrder->save();
        return  response()->json($stockOrder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockOrder  $stockOrder
     * @return \Illuminate\Http\Response
     */
    public function show(StockOrder $stockOrder)
    {
            $data = DB::table('products as p')
            ->join('stock_order as so', 'so.product_id', '=', 'p.id')
            ->select('p.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at')
            ->orderBy('p.updated_at', 'DESC')
            ->where('p.id', $id)
            ->get();
            return response()->json($data); 
    }

        public function fetchById($id)
    {
            $data = DB::table('products p')
            ->join('stock_order so', 'so.product_id', '=', 'p.id')
            ->select('p.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at')
            ->orderBy('p.updated_at', 'DESC')
            ->where('p.id', $id)
            ->get();
            return response()->json($data); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockOrder  $stockOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(StockOrder $stockOrder)
    {
            $data = DB::table('products p')
            ->join('stock_order so', 'so.product_id', '=', 'p.id')
            ->select('p.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at')
            ->orderBy('p.updated_at', 'DESC')
            ->where('p.id', $stockOrder->id)
            ->get();
            return response()->json($data); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockOrder  $stockOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockOrder $stockOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockOrder  $stockOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockOrder $stockOrder)
    {
        //
    }
}
