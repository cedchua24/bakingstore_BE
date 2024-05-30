<?php

namespace App\Http\Controllers;

use App\Models\MarkUpProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkUpProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    
    { 
            $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('branch_stock_transaction as b', 'b.id', '=', 'mup.branch_stock_transaction_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select('mup.id', 'mup.product_id', 'mup.price', 'p.disabled',
             'mup.mark_up_option', 'mup.profit', 'mup.mark_up_price', 'mup.new_price', 'mup.profit', 'mup.mark_up_option', 'p.product_name', 'p.quantity',
              'p.weight', 'p.category_id', 'p.variation', 'p.packaging', 'c.category_name', 'w.warehouse_name', 'mup.branch_stock_transaction_id', 'mup.business_type')    
            ->selectRaw("(CASE WHEN (mup.business_type = 'WHOLESALE') THEN p.stock ELSE p.stock_pc END) as stock")
            ->where('mup.status', 1) 
            // ->where('p.stock_pc', '!=', 0)
            ->where('p.disabled', '=', 0)
            // ->where('p.stock', '!=', 0)
            // ->orWhere('p.stock_pc', '!=', 0)
            ->orderBy('mup.id', 'DESC')
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
            'mark_up_option' => 'required',
            'mark_up_price' => 'required',
            'new_price' => 'required',
        ]);

           $data = DB::table('mark_up_product as mup')
           ->join('products as p', 'mup.product_id', '=', 'p.id')
           ->where('mup.status', 1)
           ->where('p.id', '=', $request->input('product_id'))
           ->update(['mup.status' => 0]);

        $markUpProduct = new MarkUpProduct;
        $markUpProduct->product_id = $request->input('product_id');
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');
        $markUpProduct->branch_stock_transaction_id = $request->input('branch_stock_transaction_id');
        $markUpProduct->status = 1;
        $markUpProduct->business_type = $request->input('business_type');

        $markUpProduct->save();

        return  response()->json($markUpProduct);
    }

 public function saveMarkUp(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'price' => 'required',
            'mark_up_option' => 'required',
            'mark_up_price' => 'required',
            'new_price' => 'required',
        ]);

        $markUpProduct = new MarkUpProduct;
        $markUpProduct->product_id = $request->input('product_id');
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');
        $markUpProduct->branch_stock_transaction_id = $request->input('branch_stock_transaction_id');
        $markUpProduct->status = 1;
        $markUpProduct->business_type = $request->input('business_type');

        $markUpProduct->save();

        return  response()->json($markUpProduct);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = DB::table('mark_up_product as mup')
          ->join('products as p', 'mup.product_id', '=', 'p.id')
          ->select('p.product_name', 'p.id as product_id', 'p.stock', 'p.stock_pc', 'mup.id', 'mup.price', 'mup.mark_up_price', 'mup.mark_up_option',
           'mup.new_price', 'mup.profit', 'mup.status', 'mup.business_type')    
          ->where('mup.id', $id)
          ->first();
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(MarkUpProduct $markUpProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $markUpProduct = MarkUpProduct::find($id);
        
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');

        $markUpProduct->save();
    
        return response()->json($markUpProduct);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarkUpProduct $markUpProduct)
    {
        $markUpProduct = MarkUpProduct::find($markUpProduct->id);
        $markUpProduct->delete();
        return response()->json($markUpProduct);
    }
}
