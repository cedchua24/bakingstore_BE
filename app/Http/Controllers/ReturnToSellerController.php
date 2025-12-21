<?php

namespace App\Http\Controllers;

use App\Models\ReturnToSeller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnToSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('category as c')
        ->join('products as p', 'c.id', '=', 'p.category_id')
        ->join('brand as b', 'b.id', '=', 'p.brand_id')
        ->join('return_to_seller as rts', 'rts.product_id', '=', 'p.id')
        ->join('supplier as s', 's.id', '=', 'rts.supplier_id')
        ->select( 'rts.id','p.category_id', 'p.stock_warning', 'p.brand_id', 'p.variation', 'c.category_name',
         'b.brand_name', 'p.product_name', 'p.price',
          'p.stock', 'p.weight',  'p.stock_pc',  'p.quantity',  'p.packaging', 'p.disabled', 'p.note',
           'rts.reason', 'rts.quantity as rts_quantity', 'rts.total_cost', 
            'rts.updated_at', 'rts.type', 'rts.status', 's.supplier_name')
        ->orderBy('rts.id', 'DESC')
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
        $returnToSeller = new ReturnToSeller;
        $returnToSeller->product_id = $request->input('id'); 
        $returnToSeller->type = $request->input('pack');  
        $returnToSeller->quantity = $request->input('newStocks'); 
        
        $returnToSeller->reason = $request->input('reason'); 
        $returnToSeller->supplier_id = $request->input('supplier_id'); 

        $returnToSeller->status = 0; 
     
        $total_cost =0;
         $products = Product::find($request->input('id'));
        if ($request->input('pack') == 'Pc') {
          $returnToSeller->price = $products->price / $products->quantity;   
          $products->stock_pc  = $products->stock_pc + $request->input('newStocks');
          $products->stock  = floor($products->stock_pc / $products->quantity);
          $total_cost = ($products->price / $products->quantity) * abs($request->input('newStocks'));
        } else {
        $returnToSeller->price = $products->price;     
          $products->stock = $products->stock + $request->input('newStocks'); 
          if ($request->input('quantity') > 1) {
            $wsStocks = $request->input('quantity') * $request->input('newStocks');
            $products->stock_pc = $products->stock_pc + $wsStocks;  
          }
          $total_cost = $products->price * abs($request->input('newStocks'));
        }
         $returnToSeller->total_cost = $total_cost;  
         $returnToSeller->save();
         $products->save();

        return response()->json(abs($request->input('newStocks')));
    }

        public function fetchById($id)
    {
            $data = DB::table('products as p')
            ->join('return_to_seller as rts', 'rts.product_id', '=', 'p.id')
            ->select('rts.id', 'rts.product_id', 'rts.status as current_status', 'rts.status', 'rts.quantity', 'rts.type', 'rts.reason', 'p.product_name' )
            ->where('rts.id', $id)
            ->first();
            return response()->json($data); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReturnToSeller  $returnToSeller
     * @return \Illuminate\Http\Response
     */
    public function show(ReturnToSeller $returnToSeller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReturnToSeller  $returnToSeller
     * @return \Illuminate\Http\Response
     */
    public function edit(ReturnToSeller $returnToSeller)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReturnToSeller  $returnToSeller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReturnToSeller $returnToSeller)
    {

        $returnToSeller = ReturnToSeller::find($request->input('id'));
        
        $returnToSeller->status = $request->input('status');
        $returnToSeller->save();

        $quantity = abs($request->input('quantity'));

        if ( $request->input('status') == 2) {
           $products = Product::find($request->input('product_id'));   
            if ( $request->input('type') === 'Pc') {
             $products->stock_pc  = $products->stock_pc + $quantity;
             $products->stock  = floor($products->stock_pc / $products->quantity);
            } else {
            $products->stock = $products->stock +  $quantity;
                if ($products->quantity > 1) {
                    $wsStocks = $products->quantity * $quantity;
                    $products->stock_pc = $products->stock_pc + $wsStocks;  
                }
            }
            $products->save();
        }

          if ( $request->input('status') == 1 &&  $request->input('current_status') == 2) {
           $products = Product::find($request->input('product_id'));   
            if ( $request->input('type') === 'Pc') {
             $products->stock_pc  = $products->stock_pc + $request->input('quantity');
             $products->stock  = floor($products->stock_pc / $products->quantity);
            } else {
            $products->stock = $products->stock +  $request->input('quantity');
                if ($products->quantity > 1) {
                    $wsStocks = $products->quantity * $request->input('quantity');
                    $products->stock_pc = $products->stock_pc + $wsStocks;  
                }
            }
            $products->save();
        }

         if ( $request->input('status') == 0 &&  $request->input('current_status') == 2) {
           $products = Product::find($request->input('product_id'));   
            if ( $request->input('type') === 'Pc') {
             $products->stock_pc  = $products->stock_pc + $request->input('quantity');
             $products->stock  = floor($products->stock_pc / $products->quantity);
            } else {
            $products->stock = $products->stock +  $request->input('quantity');
                if ($products->quantity > 1) {
                    $wsStocks = $products->quantity * $request->input('quantity');
                    $products->stock_pc = $products->stock_pc + $wsStocks;  
                }
            }
            $products->save();
        }

        return response()->json($request->input('status'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReturnToSeller  $returnToSeller
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReturnToSeller $returnToSeller)
    {
        //
    }
}
