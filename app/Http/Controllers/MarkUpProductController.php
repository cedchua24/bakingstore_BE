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
            ->join('branch_stock_transaction as b', 'b.id', '=', 'mup.branch_stock_transaction_id')
            ->join('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select('mup.id', 'mup.product_id', 'mup.price',
             'mup.mark_up_option', 'mup.mark_up_price', 'mup.new_price', 'p.product_name', 'p.quantity',
              'p.weight', 'p.stock','p.stock_pc', 'p.category_id', 'c.category_name', 'w.warehouse_name', 'mup.branch_stock_transaction_id', 'mup.business_type')
             ->where('mup.status', 1)
             ->where('p.stock', '!=', 0)
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

        $markUpProduct = new MarkUpProduct;
        $markUpProduct->product_id = $request->input('product_id');
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
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
    public function show(MarkUpProduct $markUpProduct)
    {
        $markUpProduct = MarkUpProduct::find($markUpProduct->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($markUpProduct);
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
    public function update(Request $request, MarkUpProduct $markUpProduct)
    {
        $markUpProduct = MarkUpProduct::find($markUpProduct->id);
        
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->status = $request->input('status');

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
