<?php

namespace App\Http\Controllers;

use App\Models\ModeOfPaymentPo;
use App\Models\OrderSupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ModeOfPaymentPoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modeOfPaymentPo = ModeOfPaymentPo::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($modeOfPaymentPo);
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
            'payment_term_id' => 'required'
        ]);

        // $item = UserProfile::create($data);


        // Create Post
        $modeOfPaymentPo = new ModeOfPaymentPo;
        $modeOfPaymentPo->payment_type_po_id = $request->input('payment_type_po_id');
        $modeOfPaymentPo->payment_term_id = $request->input('payment_term_id');
        $modeOfPaymentPo->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $modeOfPaymentPo->amount = $request->input('amount');
        $modeOfPaymentPo->date = $request->input('date');
        $modeOfPaymentPo->save();

        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->status = 2;
        $orderSupplierTransaction->save();
        return  response()->json($orderSupplierTransaction);
    }

       public function fetchPaymentTypePoByShopTransactionId($id)
    {
         $data = DB::table('mode_of_payment_po as mop')
            ->join('payment_type_po as p', 'p.id', '=', 'mop.payment_type_po_id')
            ->join('order_supplier_transaction as sot', 'sot.id', '=', 'mop.order_supplier_transaction_id')
            ->select('mop.id', 'mop.order_supplier_transaction_id',  'mop.amount', 'p.payment_type',
              'p.payment_type_description', 'p.status', 'sot.total_transaction_price', 'mop.payment_type_po_id')
            ->where('mop.order_supplier_transaction_id', '=', $id)    
            ->get();

            $total_payment = 0;

              $total_payment = DB::table('mode_of_payment_po as mop')
                ->where('mop.order_supplier_transaction_id', $id)
                ->sum('mop.amount');


            $balance = 0;
            if (count($data) == 0 ) {
             $orderSupplierTransaction = OrderSupplierTransaction::find($id);
             $balance= $orderSupplierTransaction->total_transaction_price;

            } else {
              $balance = $data[0]->total_transaction_price - $total_payment;
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
     * Display the specified resource.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function show(ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPaymentPo);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function edit(ModeOfPaymentPo $modeOfPaymentPo)
    {

        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPaymentPo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        $modeOfPaymentPo->payment_type_po_id = $request->input('payment_type_po_id');
        $modeOfPaymentPo->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $modeOfPaymentPo->amount = $request->input('amount');
        $modeOfPaymentPo->save();

        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->status = 'IN_PROGRESS';
        $orderSupplierTransaction->save();

        $response = [
              'data' => $modeOfPaymentPo,
              'code' => 200,
              'message' => "Successfully Added"
          ];
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);    

        $shopOrderTransaction = OrderSupplierTransaction::find($modeOfPaymentPo->order_supplier_transaction_id);
        $shopOrderTransaction->status = 'IN_PROGRESS';
        $shopOrderTransaction->save();
        $modeOfPaymentPo->delete();
        
        return response()->json($modeOfPaymentPo);
    }
}
