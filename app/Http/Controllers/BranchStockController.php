<?php

namespace App\Http\Controllers;

use App\Models\BranchStock;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use App\Models\OrderSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branchStock = BranchStock::all();
        return response()->json($branchStock);
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
            'warehouse_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
         $branchStockTransaction = new BranchStockTransaction;
         $data = DB::table('branch_stock_transaction')
            ->select('branch_stock_transaction.id')    
            ->where('branch_stock_transaction.warehouse_id', $request->input('warehouse_id'))
            ->where('branch_stock_transaction.product_id', $request->input('product_id'))
            ->first();

                                              
        if ($data == null) {
          $branchStockTransaction = new BranchStockTransaction;
          $branchStockTransaction->warehouse_id = $request->input('warehouse_id');
          $branchStockTransaction->product_id = $request->input('product_id');
          $branchStockTransaction->branch_stock_transaction = $request->input('branch_stock');
          $branchStockTransaction->status = 1;
          $branchStockTransaction->save();          
        } else {
          $branchStockTransaction = BranchStockTransaction::find($data->id);
          $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction + $request->input('branch_stock'));
          $branchStockTransaction->save();
        }

        $branchStock = new BranchStock;
        $branchStock->warehouse_id = $request->input('warehouse_id');
        $branchStock->product_id = $request->input('product_id');
        $branchStock->branch_stock = $request->input('branch_stock');
        $branchStock->status = 1;
        $branchStock->save();

        $product = Product::find($request->input('product_id'));
        $product->stock = ($product->stock + $request->input('branch_stock'));
        $product->save();

        $orderSupplier = OrderSupplier::find($request->input('order_supplier_id'));
        $orderSupplier->stock_remaining = ($orderSupplier->stock_remaining - $request->input('branch_stock'));
        $orderSupplier->save();

        $response = [
            'id' => $orderSupplier->id,
            'stock' => $orderSupplier->stock_remaining,
            'code' => 200,
            'message' => "Successfully Added"
        ];
            
        
        return  response()->json($response);

        
        // return  $data;
        // $result = '';
        // if ($data == null) {
        //     $result = 'null';
        // } else {
        //     $result = 'not nul';
        // }
        // return ($result);
        // return  response()->json($request->input('order_supplier_id'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BranchStock  $branchStock
     * @return \Illuminate\Http\Response
     */
    public function show(BranchStock $branchStock)
    {
        $branchStock = BranchStock::find($id);
        return  response()->json($branchStock);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BranchStock  $branchStock
     * @return \Illuminate\Http\Response
     */
    public function edit(BranchStock $branchStock)
    {
        $branchStock = BranchStock::find($branchStock->id);
        return response()->json($branchStock);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BranchStock  $branchStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BranchStock $branchStock)
    {
        $branchStock = BranchStock::find($warehouse->id);
        
        $branchStock->warehouse_id = $request->input('warehouse_id');
        $branchStock->product_id = $request->input('product_id');
        $branchStock->branch_stock = $request->input('branch_stock');
        $branchStock->status = 1;
        $branchStock->save();
      

        return response()->json($branchStock);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BranchStock  $branchStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(BranchStock $branchStock)
    {
        $branchStock = BranchStock::find($branchStock->id);
        $branchStock->delete();
        return response()->json($branchStock);
    }
}
