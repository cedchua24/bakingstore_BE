<?php

namespace App\Http\Controllers;

use App\Models\ModeOfPayment;
use App\Models\ShopOrderTransaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModeOfPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $modeOfPayment = ModeOfPayment::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($modeOfPayment);

         $data = DB::table('mode_of_payment as mop')
            ->join('payment_type as p', 'p.id', '=', 'mop.payment_type_id')
            ->select('mop.id', 'mop.shop_order_transaction_id',  'mop.amount', 'p.payment_type',
              'p.payment_type_description', 'p.expenses_categstatusory_name')
            //    ->where('e.date', date('Y-m-d'))    
            ->get();
        return response()->json($data);   
    }

   public function fetchPaymentTypeByShopTransactionId($id)
    {
         $data = DB::table('mode_of_payment as mop')
            ->join('payment_type as p', 'p.id', '=', 'mop.payment_type_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'mop.shop_order_transaction_id')
            ->select('mop.id', 'mop.shop_order_transaction_id',  'mop.amount', 'p.payment_type',
              'p.payment_type_description', 'p.status', 'sot.shop_order_transaction_total_price', 'mop.payment_type_id')
           ->where('mop.shop_order_transaction_id', '=', $id)    
            ->get();

            $total_payment = 0;

              $total_payment = DB::table('mode_of_payment as mop')
                ->where('mop.shop_order_transaction_id', $id)
                ->sum('mop.amount');


            $balance = 0;
            if (count($data) == 0 ) {
             $shopOrderTransaction = ShopOrderTransaction::find($id);
             $balance= $shopOrderTransaction->shop_order_transaction_total_price;

            } else {
              $balance = $data[0]->shop_order_transaction_total_price - $total_payment;
            }

           $response = [
              'data' => $data,
              'balance' => $balance,
              'total_payment' => $total_payment,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
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
            'payment_type_id' => 'required'
        ]);

        // $item = UserProfile::create($data);


        // Create Post
        $modeOfPayment = new ModeOfPayment;
        $modeOfPayment->payment_type_id = $request->input('payment_type_id');
        $modeOfPayment->shop_order_transaction_id = $request->input('shop_order_transaction_id');
        $modeOfPayment->amount = $request->input('amount');
        $modeOfPayment->save();

        $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_order_transaction_id'));
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($modeOfPayment);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModeOfPayment  $modeOfPayment
     * @return \Illuminate\Http\Response
     */
    public function show(ModeOfPayment $modeOfPayment)
    {
        $modeOfPayment = ModeOfPayment::find($modeOfPayment->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPayment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ModeOfPayment  $modeOfPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(ModeOfPayment $modeOfPayment)
    {
        $modeOfPayment = ModeOfPayment::find($modeOfPayment->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPayment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ModeOfPayment  $modeOfPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModeOfPayment $modeOfPayment)
    {

        // Create Post
        $modeOfPayment = ModeOfPayment::find($modeOfPayment->id);
        $modeOfPayment->payment_type_id = $request->input('payment_type_id');
        $modeOfPayment->shop_order_transaction_id = $request->input('shop_order_transaction_id');
        $modeOfPayment->amount = $request->input('amount');
        $modeOfPayment->save();

        $shopOrderTransaction = ShopOrderTransaction::find($request->input('shop_order_transaction_id'));
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->save();

        $response = [
              'data' => $modeOfPayment,
              'code' => 200,
              'message' => "Successfully Added"
          ];
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModeOfPayment  $modeOfPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModeOfPayment $modeOfPayment)
    {
        $modeOfPayment = ModeOfPayment::find($modeOfPayment->id);
        

        $shopOrderTransaction = ShopOrderTransaction::find($modeOfPayment->shop_order_transaction_id);
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->save();
        $modeOfPayment->delete();
        
        return response()->json($modeOfPayment);
    }
}
