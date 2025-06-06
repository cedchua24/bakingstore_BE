<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spoilage;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SpoilageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('category')
        ->join('products', 'category.id', '=', 'products.category_id')
        ->join('brand', 'brand.id', '=', 'products.brand_id')
        ->join('stock_order as so', 'so.product_id', '=', 'products.id')
        ->join('spoilage as s', 's.stock_order_id', '=', 'so.id')
        ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
         'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
          'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note',
          'so.id as stock_order_id', 'so.pack', 'so.stock_type', 'so.total_stock', 'so.stock as stock_quantity', 's.reason',
           's.total_cost', 's.id as spoilage_id', 's.updated_at')
        ->orderBy('s.id', 'DESC')
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
        $products = Product::find($request->input('id'));

        $stockOrder = new StockOrder;
        $stockOrder->product_id = $request->input('id');
        if ($request->input('newStocks') != null) {

        $stockOrder->stock_type = $request->input('newStocks') > 0 ? "Add" : "Reduce";
        $stockOrder->stock = $request->input('newStocks');
        $stockOrder->pack = $request->input('pack');    
            
        $total_cost = 0;
        if ($request->input('pack') == 'Pc') {
          $stockOrder->total_stock = $products->stock_pc / $products->quantity;
          $products->stock_pc  = $products->stock_pc + $request->input('newStocks');
          $products->stock  = $products->stock_pc / $products->quantity;
          $total_cost = $products->price / $products->quantity;
        } else {
          $stockOrder->total_stock = $products->stock + $request->input('newStocks');  
          $products->stock = $products->stock + $request->input('newStocks');
          
          if ($request->input('quantity') > 1) {
            $wsStocks = $request->input('quantity') * $request->input('newStocks');
            $products->stock_pc = $products->stock_pc + $wsStocks;  
            $total_cost = $products->price;
          }
        }


        $stockOrder->save();
      }
        $products->save();

        $spoilage = new Spoilage;
        $spoilage->stock_order_id = $stockOrder->id;  
        $spoilage->reason = $request->input('reason');  
        $spoilage->total_cost = $total_cost * $request->input('newStocks'); 
        $spoilage->save();

        return response()->json($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Spoilage  $spoilage
     * @return \Illuminate\Http\Response
     */
    public function show(Spoilage $spoilage)
    {

        return response()->json("test2"); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Spoilage  $spoilage
     * @return \Illuminate\Http\Response
     */
    public function edit(Spoilage $spoilage)
    {

        return response()->json("test"); 
    }

    public function fetchById($id)
    {
            $data = DB::table('products as p')
            ->join('stock_order as so', 'so.product_id', '=', 'p.id')
            ->join('spoilage as s', 's.stock_order_id', '=', 'so.id')
            ->select('so.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at', 'so.pack', 's.reason' )
            ->orderBy('so.id', 'DESC')
            ->where('so.id', $id)
            ->first();
            return response()->json($data); 
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Spoilage  $spoilage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Spoilage $spoilage)
    {
        //
    }

    public function fetchSpoilageToday()
    {
            $expenses_transaction_list = DB::table('spoilage as s')
            ->select(DB::raw('SUM(s.total_cost) as total_cost'), DB::raw('s.created_at'),  DB::raw('s.id'))  
            ->orderBy('s.id', 'DESC')
            ->groupBy('s.created_at')
            ->where('s.created_at', date('Y-m-d'))    
            ->get();


            $total_cost = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_cost += $datavals->total_cost;
            }
           $response = [
              'data' => $expenses_transaction_list,
              'code' => 200,
              'total_cost' => $total_cost,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchSpoilageReport(Request $request)
    {
      
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
            $shop_order_transaction_list = DB::table('spoilage as s')
            ->select(DB::raw('SUM(s.total_cost) as total_cost') , DB::raw('COUNT(so.stock) as total_count'),
             DB::raw('s.created_at as date'))  
            ->join('stock_order as so', 'so.id', '=', 's.stock_order_id')  
            ->orderBy('s.id', 'DESC')
            ->groupBy('s.created_at')
            ->get();

        } else {

            $shop_order_transaction_list = DB::table('spoilage as s')
            ->select(DB::raw('SUM(s.total_cost) as total_cost') , DB::raw('COUNT(so.stock) as total_count'),
             DB::raw('s.created_at as date'))  
            ->join('stock_order as so', 'so.id', '=', 's.stock_order_id')  
            ->where('s.created_at', '>=', $request->input('dateFrom'))
            ->where('s.created_at', '<=', $request->input('dateTo'))
            ->orderBy('s.id', 'DESC')
            ->groupBy('s.created_at')
            ->get();

        }  

        

            $total_cost = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_cost += $datavals->total_cost;
            }

           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_cost' => $total_cost,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchSpoilageReportByDate($date)
    {
      $data = DB::table('category')
      ->join('products', 'category.id', '=', 'products.category_id')
      ->join('brand', 'brand.id', '=', 'products.brand_id')
      ->join('stock_order as so', 'so.product_id', '=', 'products.id')
      ->join('spoilage as s', 's.stock_order_id', '=', 'so.id')
      ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
       'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
        'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note',
        'so.id as stock_order_id', 'so.pack', 'so.stock_type', 'so.total_stock', 'so.stock as stock_quantity', 's.reason',
         's.total_cost', 's.id as spoilage_id', 's.updated_at')
      ->orderBy('s.id', 'DESC')
      ->where('s.created_at', $date)
      ->get();

      $total_cost = DB::table('spoilage as s')
      ->select(DB::raw('SUM(s.total_cost) as total_cost') , DB::raw('COUNT(so.stock) as total_count'),
       DB::raw('s.created_at as date'))  
      ->join('stock_order as so', 'so.id', '=', 's.stock_order_id')  
      ->where('s.created_at', $date)
      ->first();

      $response = [
        'data' => $data,
        'code' => 200,
        'total_cost' => $total_cost,
        'message' => "Successfully Added"
    ];

      return response()->json($response);
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Spoilage  $spoilage
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $spoilage = Spoilage::find($id);

      $stockOrder = StockOrder::find($spoilage->stock_order_id);

      $products = Product::find($stockOrder->product_id);

      if ($stockOrder->pack == 'Pc') {
        $products->stock_pc  = $products->stock_pc + $stockOrder->stock;
        $products->stock  = $products->stock_pc / $products->quantity;
      } else {
        $products->stock = $products->stock +  $stockOrder->stock;
        
        if ($products->quantity > 1) {
          $wsStocks = $products->quantity * $stockOrder->stock;
          $products->stock_pc = $products->stock_pc + $wsStocks;  
        }
      }
      $products->save();
      $stockOrder->delete();
      $spoilage->delete();
      return response()->json("test");
    }


}
