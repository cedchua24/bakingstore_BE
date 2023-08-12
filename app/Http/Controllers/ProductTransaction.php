<?php

namespace App\Http\Controllers;

use App\Models\ProductTransaction as ProductTransactionModel;
use App\Models\Product as ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductTransaction extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productTransaction = ProductTransactionModel::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($productTransaction);
    }

        public function fetchProductTransactionList($id)
    {
            $data = DB::table('product_transaction as pt')
            ->join('products as p', 'p.id', '=', 'pt.product_id')
            ->join('users as u', 'u.id', '=', 'pt.user_id')
            ->select('pt.quantity', 'pt.user_id','pt.status',
             'pt.product_price', 'pt.created_at', 'pt.id', 'p.product_name', 'u.name')
             ->where('pt.product_id', $id)
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
            'id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $productTransaction = new ProductTransactionModel;
        $productTransaction->product_id = $request->input('id');
        $productTransaction->product_price = 0;
        $productTransaction->quantity = $request->input('shop_order_quantity');
        $productTransaction->user_id = $request->input('user_id');
        $productTransaction->status = 1;
        $productTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');

        $products = ProductModel::find($request->input('id'));
        $products->stock = $products->stock + $request->input('shop_order_quantity');

         if ($products->quantity > 1) {
          $newStock = 0;
          $newStock = $products->weight * $request->input('shop_order_quantity');  
          if ($product->stock_pc != null) {
            $products->stock_pc = $products->stock_pc + $newStock;
          }
        }
        
        $products->save();
        $response = [
              'id' => $request->input('$productTransaction->product_id '),
              'stock' => $products->stock,
              'code' => 200,
              'message' => "Successfully Added"
          ];
        return  response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductTransaction  $ 
     * @return \Illuminate\Http\Response
     */
    public function show(ProductTransaction $productTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductTransaction  $productTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductTransaction $productTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductTransaction  $productTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductTransaction $productTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductTransaction  $productTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductTransaction $productTransaction)
    {
        //
    }
}
