<?php

namespace App\Http\Controllers;

use App\Models\ModeOfPaymentPo;
use App\Models\OrderSupplierTransaction;
use App\Models\CreditCardPayment;
use App\Models\PaymentTypePo;
use App\Models\CreditCardDue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
        $modeOfPaymentPo->status = $request->input('status');
        $modeOfPaymentPo->type = $request->input('type');
        $modeOfPaymentPo->save();

        $timestamp = strtotime($request->input('date'));
        $day = date('d', $timestamp);

        if ($modeOfPaymentPo->payment_term_id == 4) {
            $paymentTypePo = PaymentTypePo::find($request->input('payment_type_po_id'));

            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due + $modeOfPaymentPo->amount;
            $paymentTypePo->save();


            // $month = date('m', $timestamp);
            // $year = date('Y', $timestamp);
            // $addMonth = Carbon::parse($request->input('date'))->addMonths(5);
            // $newDate = date('Y-m-d', strtotime($addMonth));

            $statement_day =  $paymentTypePo->statement_date;

            if ($statement_day > $day ) {
                $addMonth = Carbon::parse($request->input('date'))->addMonths(1);
            } else {
                $minus = $paymentTypePo->statement_date - $day;
                if ($minus >=  $paymentTypePo->buffer_days)
                 {
                    $addMonth = Carbon::parse($request->input('date'))->addMonths(3);
                 } else {
                    $addMonth = Carbon::parse($request->input('date'))->addMonths(2);
                 }
            }                          
            $newDate =  strtotime($addMonth);
            $month = date('m', $newDate);
            $year = date('Y', $newDate);
            $due_date = $year ."-". $month ."-". $paymentTypePo->due_date;  

            // $creditCardDue = CreditCardDue::where('due_date', $due_date)->first();
              $creditCardDue = DB::table('credit_card_due')
              ->where('payment_type_po_id', $request->input('payment_type_po_id'))
              ->where('due_date',  $due_date)
              ->orderBy('due_date', 'asc')
              ->first();

            if ($creditCardDue != null ) {    
                $creditCardDue = CreditCardDue::find($creditCardDue->id);        
                $creditCardDue->amount = $creditCardDue->amount + $request->input('amount');
                $creditCardDue->status = 0;
            } else {
                $creditCardDue = new CreditCardDue;
                $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
                // $creditCardDue->mode_of_payment_po_id = $modeOfPaymentPo->id;
                $creditCardDue->min_amount = 0;
                $creditCardDue->amount =$request->input('amount');
                $creditCardDue->interest_amount =0;
                $creditCardDue->is_installment = 0;
                $creditCardDue->due_date = $due_date;
                $creditCardDue->status = 0;
                $creditCardDue->type = 'CREDIT_CARD';
             
            }
               $creditCardDue->save();

       } else if ($modeOfPaymentPo->payment_term_id == 3) { 
            $creditCardDue = new CreditCardDue;
            $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
            $creditCardDue->mode_of_payment_po_id = $modeOfPaymentPo->id;
            $creditCardDue->amount =$request->input('amount');
            $creditCardDue->due_date = $request->input('date');
            $creditCardDue->type = 'CHEQUE';
            $creditCardDue->save(); 
       }

        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->payment_status = 0;
        $orderSupplierTransaction->save();
    
        $response = [
            //   'date' => $request->input('date'),
            //   'day' => $day,
            //   'month' => $month,
            //   'year' => $year,
            //   'addMonth' => $addMonth,     
            //   'newDate' => $newDate,   
            //   'due_date' => $due_date, 
            //   'creditCardDue' => $creditCardDue,
              'message' => "Successfully Added"
          ];
        return  response()->json($response);
    }

       public function fetchPaymentTypePoByShopTransactionId($id)
    {
         $data = DB::table('mode_of_payment_po as mop')
            ->join('payment_type_po as p', 'p.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'p.bank_id', '=', 'b.id')   
            ->join('payment_term as pt', 'pt.id', '=', 'p.payment_term_id') 
            ->join('order_supplier_transaction as sot', 'sot.id', '=', 'mop.order_supplier_transaction_id')
            ->select('mop.id', 'mop.order_supplier_transaction_id',  'mop.amount',
              'p.account_number', 'p.account_name', 'p.account_description', 'p.due_date', 'p.credit_limit', 'p.status', 'b.bank_name', 'pt.payment_term',
              'p.statement_date', 'p.total_balance_due',
              'sot.total_transaction_price', 'mop.payment_type_po_id')
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

     public function fetchCreditCardPaymentDTO($id)
    {

         $modeOfPaymentPo = ModeOfPaymentPo::find($id);

           $data = DB::table('mode_of_payment_po as mop')
            ->join('payment_type_po as p', 'p.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'p.bank_id', '=', 'b.id')   
            ->join('payment_term as pt', 'pt.id', '=', 'p.payment_term_id') 
            ->select('mop.id', 'mop.order_supplier_transaction_id',  'mop.amount',
              'p.account_number', 'p.account_name', 'p.account_description', 'p.due_date', 'p.credit_limit', 'p.status', 'b.bank_name', 'pt.payment_term',
              'p.statement_date', 'p.total_balance_due', 'p.id as payment_type_po_id',
             'mop.payment_type_po_id')
            ->where('mop.id', '=', $id)    
            ->get();

            $total_payment = 0;

            $total_payment = DB::table('credit_card_payment as ccp')
                ->where('ccp.mode_of_payment_po_id', $id)
                ->sum('ccp.amount');

            $balance = 0;
            if (count($data) == 0 ) {
             $modeOfPaymentPo = ModeOfPaymentPo::find($id);
             $balance = $modeOfPaymentPo->amount;

            } else {
              $balance = $data[0]->amount - $total_payment;
            }

           $response = [
              'data' => $data,
              'balance' => $balance,
              'modeOfPaymentPo' => $modeOfPaymentPo,
              'total_payment' => $total_payment,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

       public function setToCompleteCreditCard($id)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($id);
        $modeOfPaymentPo->status = 0;
        $modeOfPaymentPo->save();
      

        return response()->json($modeOfPaymentPo);
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
        $modeOfPaymentPo->status = $request->input('status');
        $modeOfPaymentPo->type = $request->input('type');
        $modeOfPaymentPo->save();

        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->payment_status = 0;
        $orderSupplierTransaction->save();

        $response = [
              'data' => $orderSupplierTransaction,
              'code' => 200,
              'message' => "Successfully Added ey"
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
        $shopOrderTransaction->payment_status = 0;
        $shopOrderTransaction->save();
        $modeOfPaymentPo->delete();
        
        return response()->json($modeOfPaymentPo);
    }
}
