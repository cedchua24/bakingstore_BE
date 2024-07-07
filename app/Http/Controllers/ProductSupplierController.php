<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
Use Exception;

class ProductSupplierController extends Controller
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
        try {
         $data = DB::table('product_supplier as ps')
          ->where('ps.supplier_id',$request->input('supplier_id'))
          ->where('ps.product_id', $request->input('product_id'))
          ->count();

          if ($data > 0) {
           $response = [
              'message' => "Duplicate Record",
              'code' => 400
          ];

          } else {
            $productSupplier = new ProductSupplier;
            $productSupplier->supplier_id = $request->input('supplier_id');
            $productSupplier->product_id = $request->input('product_id');
            $productSupplier->status = 0;
            $productSupplier->save();
             $response = [
              'message' => "Successfully Added",
              'code' => 200,
              'data' => $productSupplier 
          ];
          }


        } catch (Exception $e) {
            $response = [
              'message' => $e->getMessage(),
              'code' => 500
          ];
        }
        return  response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function show(ProductSupplier $productSupplier)
    {
        $productSupplier = ProductSupplier::find($productSupplier->id);
        return response()->json($productSupplier);
        
    }

        public function fetchProductSupplierById($id)
    {
        $data = DB::table('product_supplier as ps')
          ->join('products as p', 'p.id', '=', 'ps.product_id')
          ->join('supplier as s', 's.id', '=', 'ps.supplier_id')
          ->join('category as c', 'p.category_id', '=', 'c.id')
          ->select('ps.id', 'p.quantity', 's.supplier_name','p.product_name', 'p.price',
            'ps.status', 'p.weight', 'ps.product_id', 'c.category_name')
         ->where('ps.supplier_id',$id)
          ->get();
        return response()->json($data);  
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductSupplier $productSupplier)
    {
        $productSupplier = ProductSupplier::find($productSupplier->id);
        return response()->json($productSupplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductSupplier $productSupplier)
    {
        $productSupplier = ProductSupplier::find($warehouse->id);
        
        $productSupplier->product_id = $request->input('product_id');
        $productSupplier->supplier_id = $request->input('supplier_id');
        $productSupplier->status = $request->input('status');
        $productSupplier->save();
      
        return response()->json($productSupplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductSupplier  $productSupplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductSupplier $productSupplier)
    {
        $productSupplier = ProductSupplier::find($productSupplier->id);
        $productSupplier->delete();
        return response()->json($productSupplier);
    }
}
