<?php

namespace App\Http\Controllers;

use App\Models\PaymentTerm;
use App\Models\CreditCardDue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentTermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paymentTerm = PaymentTerm::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($paymentTerm);
    }

      public function fetchPaymentTermCreditCard()
    {
         $data = DB::table('payment_term as pt')
            ->select('pt.id', 'pt.payment_term', 'pt.status')
            ->whereIn('pt.id', [1,2,5])   
            ->where('pt.status', '=', 1) 
            ->get();

        return response()->json($data);  
    }

          public function fetchCashAndOnline()
    {
         $data = DB::table('payment_term as pt')
            ->select('pt.id', 'pt.payment_term', 'pt.status')
            ->whereIn('pt.id', [1,2])   
            ->where('pt.status', '=', 1) 
            ->get();

        return response()->json($data);  
    }

        public function fetchNotCashList()
    {
         $data = DB::table('payment_term as pt')
            ->select('pt.id', 'pt.payment_term', 'pt.status')
            ->where('pt.id', '!=', 1)   
            ->where('pt.status', '=', 1) 
            ->get();

        return response()->json($data);  
    }

         public function fetchByPaymentTerm($id)
    {

        $data = DB::table('bank as b')
            ->join('payment_type_po as ptt', 'ptt.bank_id', '=', 'b.id')
             ->join('payment_term as pt', 'pt.id', '=', 'ptt.payment_term_id')
            ->select('ptt.id', 'pt.payment_term', 'ptt.bank_id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'ptt.due_date',
             'ptt.credit_limit', 'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name')    
            ->where('pt.id', $id)
            ->get();

            return response()->json($data);   
    }

       public function fetchInstallmenttList($id)
    {

         $data = DB::table('credit_card_payment as ccp')
            ->join('mode_of_payment_po as mop', 'mop.id', '=', 'ccp.mode_of_payment_po_id')
            ->join('order_supplier_transaction as osp', 'osp.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier', 'supplier.id', '=', 'osp.supplier_id')
            ->join('payment_type_po as ptt', 'ptt.id', '=', 'ccp.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select('mop.id', 'mop.amount', 'mop.date', 'mop.order_supplier_transaction_id', 'supplier.supplier_name',
             'b.bank_name', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'mop.status')    
            ->where('ccp.is_installment', 1)
            ->get();

       $response = [

              'data' => $data,
            //   'details' => $details,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }


       public function fetchCreditCardPaymentList($id)
    {

        if ($id == 2) {
         $data = DB::table('mode_of_payment_po as mop')
            ->join('order_supplier_transaction as osp', 'osp.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier', 'supplier.id', '=', 'osp.supplier_id')
            ->join('payment_type_po as ptt', 'ptt.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select('mop.id', 'mop.amount', 'mop.date', 'mop.order_supplier_transaction_id', 'supplier.supplier_name',
             'b.bank_name', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'mop.status')    
            ->where('mop.payment_term_id', 4)
            ->get();

        } else {
         
         $data = DB::table('mode_of_payment_po as mop')
            ->join('order_supplier_transaction as osp', 'osp.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier', 'supplier.id', '=', 'osp.supplier_id')
            ->join('payment_type_po as ptt', 'ptt.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select('mop.id', 'mop.amount', 'mop.date', 'mop.order_supplier_transaction_id', 'supplier.supplier_name',
             'b.bank_name', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'mop.status')    
            ->where('mop.payment_term_id', 4)
            ->where('mop.status', $id)
            ->get();        
        }
       $response = [

              'data' => $data,
            //   'details' => $details,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

           public function fetchCreditCardPaymentListV2($id)
    {

        if ($id == 2) {
         $data = DB::table('payment_type_po as ptt')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select( 'ptt.id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description',
            'ptt.due_date', 'ptt.buffer_days', 'ptt.credit_limit',
            'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name')    
            ->where('ptt.payment_term_id', 4)
            ->get();

         for($x=0; $x<= sizeof($data)-1; $x++) {
              $creditCards = DB::table('credit_card_due')
               ->select( 'amount', 'amount_paid', 'due_date')
              ->orderBy('due_date', 'asc')
              ->where('status', 0)
              ->where('payment_type_po_id',  $data[$x]->id)
              ->first();  
              if ($creditCards != null) {
                $data[$x]->amount_due = $creditCards->amount - $creditCards->amount_paid;
              }   else {
                $data[$x]->amount_due = 0;
              }
                $data[$x]->due = $creditCards->due_date;
            //   $data[$x]->due_date = $creditCards->due_date ;
         }


        } 
       $response = [
             'data' => $data,
              'code' => $creditCards,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

       public function fetchCreditCardPaymentListV3($id)
    {

        if ($id == 2) {
         $data = DB::table('payment_type_po as ptt')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select( 'ptt.id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description',
            'ptt.due_date', 'ptt.buffer_days', 'ptt.credit_limit',
            'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name')    
            ->where('ptt.payment_term_id', 3)
            ->get();
        }
       $response = [
             'data' => $data,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

     public function fetchByPaymentTypePo($id)
    {
        $details = DB::table('bank as b')
            ->join('payment_type_po as ptt', 'ptt.bank_id', '=', 'b.id')
             ->join('payment_term as pt', 'pt.id', '=', 'ptt.payment_term_id')
             ->select( 'pt.payment_term', 'ptt.bank_id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'ptt.due_date',
             'ptt.credit_limit', 'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name')  
            ->where('ptt.id', $id)
            ->first();

        $data = DB::table('mode_of_payment_po as mop')
            ->join('order_supplier_transaction as osp', 'osp.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier', 'supplier.id', '=', 'osp.supplier_id')
            ->select('mop.id', 'mop.amount', 'mop.date', 'mop.order_supplier_transaction_id', 'supplier.supplier_name')    
            ->where('mop.payment_type_po_id', $id)
            ->get();

       $response = [

              'data' => $data,
              'details' => $details,
              'code' => 200,
              'message' => "Successfully "
          ];

            return response()->json($response);   
    }

         public function fetchOrderSupplierByPaymentType($id)
    {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status','order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.id', $id)
            ->get();

            return response()->json($data);   
    }

             public function fetchOrderSupplierByPaymentTypeV2($id)
    {
            $data = DB::table('credit_card_due ccd')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status','order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.id', $id)
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
     * @param  \App\Models\PaymentTerm  $paymentTerm
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTerm $paymentTerm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentTerm  $paymentTerm
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentTerm $paymentTerm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentTerm  $paymentTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentTerm $paymentTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentTerm  $paymentTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTerm $paymentTerm)
    {
        //
    }
}
