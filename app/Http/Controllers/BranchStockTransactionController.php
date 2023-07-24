<?php

namespace App\Http\Controllers;

use App\Models\BranchStockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchStockTransactionController extends Controller
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

    public function fetchBranchStockWarehouseList($id)
    {

        // $data = DB::table('branch_stock_transaction')
        //     ->join('warehouse', 'warehouse.id', '=', 'branch_stock_transaction.warehouse_id')
        //     ->join('products', 'products.id', '=', 'branch_stock_transaction.product_id')
        //     ->select('branch_stock_transaction.id', 'branch_stock_transaction.branch_stock_transaction',  'branch_stock_transaction.status',
        //      'warehouse.warehouse_name', 'products.product_name')   
        //     ->where('products.id', $id)
        //     ->get();
        //     return response()->json($data);   

        $data = DB::table('branch_stock_transaction')
            ->join('warehouse', 'warehouse.id', '=', 'branch_stock_transaction.warehouse_id')
            ->join('products', 'products.id', '=', 'branch_stock_transaction.product_id')
            ->select('branch_stock_transaction.id', 'branch_stock_transaction.branch_stock_transaction',  'branch_stock_transaction.status',
             'warehouse.warehouse_name', 'products.product_name') 
             ->groupBy('warehouse.id')  
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
     * @param  \App\Models\BranchStockTransaction  $branchStockTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(BranchStockTransaction $branchStockTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BranchStockTransaction  $branchStockTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(BranchStockTransaction $branchStockTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BranchStockTransaction  $branchStockTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BranchStockTransaction $branchStockTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BranchStockTransaction  $branchStockTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(BranchStockTransaction $branchStockTransaction)
    {
        //
    }
}
