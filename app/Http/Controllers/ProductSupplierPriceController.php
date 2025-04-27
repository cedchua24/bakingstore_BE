<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplierPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSupplierPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('product_supplier as ps')
          ->join('products as p', 'p.id', '=', 'ps.product_id')
          ->join('supplier as s', 's.id', '=', 'ps.supplier_id')
          ->join('category as c', 'p.category_id', '=', 'c.id')
          ->select('ps.id',  's.supplier_name','p.product_name', 'p.price',
            'ps.status', 'c.category_name', 'p.quantity')
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductSupplierPrice  $productSupplierPrice
     * @return \Illuminate\Http\Response
     */
    public function show(ProductSupplierPrice $productSupplierPrice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductSupplierPrice  $productSupplierPrice
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductSupplierPrice $productSupplierPrice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductSupplierPrice  $productSupplierPrice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductSupplierPrice $productSupplierPrice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductSupplierPrice  $productSupplierPrice
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductSupplierPrice $productSupplierPrice)
    {
        //
    }
}
