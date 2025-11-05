<?php

namespace App\Http\Controllers;

use App\Models\ProductSoldDaily;
use Illuminate\Http\Request;
use App\Http\Controllers\ShopOrderTransactionController;
use Illuminate\Support\Facades\DB;

class ProductSoldDailyController extends Controller
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
    $shopOrderTransactionController = new ShopOrderTransactionController();

    // ✅ Convert JsonResponse to associative array
    $productSoldTodayList = $shopOrderTransactionController
        ->fetchProductSoldToday($request)
        ->getData(true);

    // ✅ Check if data key exists to avoid undefined index error
    if (empty($productSoldTodayList['data'])) {
        return response()->json([
            'code' => 404,
            'message' => 'No product data found for today.'
        ]);
    }

    foreach ($productSoldTodayList['data'] as $item) {
        $id = $item['id'];
        $name = $item['product_name'];
        $quantity = $item['quantity'];
        $totalQuantity = $item['total_quantity'];
        $stock = $item['stock'];
        $stockAll = $item['stock_all'];

        $today = $request->input('today');
        $productCode = "{$id}-{$today}";

        // ✅ Find existing record or create new instance
        $productSoldDaily = ProductSoldDaily::firstOrNew(['product_code' => $productCode]);

        // ✅ Fill data (avoids duplicate code)
        $productSoldDaily->fill([
            'product_id' => $id,
            'product_code' => $productCode,
            'stock' => floor($stockAll / $quantity),
            'stock_pc' => $stockAll % $quantity,
            'total_stock' => $totalQuantity,
            'current_stock' => $stockAll,
            'stock_input' => 0,
            'date' => $today,
            'status' => 0,
        ]);

        $productSoldDaily->save();
    }

    return response()->json([
        'code' => 200,
        'date' => now()->toDateString(),
        'message' => 'Successfully added.'
    ]);
}

public function updateMultiple(Request $request)
{
    $products = $request->input('products');

    foreach ($products as $product) {
        ProductSoldDaily::where('id', $product['id'])
            ->update(['stock_input' => $product['stock_input']]);
    }

    return response()->json(['message' => 'Products updated successfully']);
}

public function fetchProductSoldListByDate($date)
{
    // Join product_sold_daily with products table
    $productSoldDaily = DB::table('product_sold_daily as psd')
        ->join('products as p', 'p.id', '=', 'psd.product_id')
        ->select(
            'psd.id',
            'psd.product_id',
            'p.product_name',
            'p.quantity',
            'psd.product_code',
            'psd.stock',
            'psd.stock_pc',
            'psd.total_stock',
            'psd.current_stock',
            'psd.stock_input',
            'psd.date',
            'psd.status'
        )
        ->where('psd.date', $date)
        ->orderBy('psd.id', 'asc')
        ->get();

    // If no records found
    // if ($productSoldDaily->isEmpty()) {
    //     return response()->json([
    //         'data' => [],
    //         'code' => 404,
    //         'message' => "No product sold records found for {$date}"
    //     ], 404);
    // }

    // Return JSON response
    return response()->json([
        'data' => $productSoldDaily,
        'code' => 200,
        'message' => "Successfully fetched product sold list for {$date}"
    ]);
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductSoldDaily  $productSoldDaily
     * @return \Illuminate\Http\Response
     */
    public function show(ProductSoldDaily $productSoldDaily)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductSoldDaily  $productSoldDaily
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductSoldDaily $productSoldDaily)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductSoldDaily  $productSoldDaily
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductSoldDaily $productSoldDaily)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductSoldDaily  $productSoldDaily
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductSoldDaily $productSoldDaily)
    {
        //
    }
}
