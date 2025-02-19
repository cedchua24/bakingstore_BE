<?php

namespace App\Http\Controllers;

use App\Models\CreditCardPay;
use App\Models\CreditCardDue;
use App\Models\PaymentTypePo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditCardPayController extends Controller
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

     public function fetchCreditCardPayById($id)
    {
        $creditCards = DB::table('credit_card_pay as ccp')
        ->select( 'ccp.id', 'ccp.credit_card_due_id', 'ccp.amount', 'ccp.status', 'ccp.updated_at', 'ptp.bank_id', 'b.bank_name', 'ptp.payment_term_id',
              'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.due_date', 'ptp.buffer_days', 'ptp.credit_limit',
              'ptp.statement_date', 'ptp.total_balance_due')        
        ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccp.payment_type_po_id')    
        ->join('bank as b', 'ptp.bank_id', '=', 'b.id')         
        ->where('ccp.credit_card_due_id',  $id)
        ->orderBy('ccp.id', 'desc')
        ->get();


        return response()->json($creditCards);   
    }


         public function fetchCreditCardDueByInstallment($id)
    {
        $creditCardDue = DB::table('credit_card_due')->where('is_installment',  $id)->get();
        return response()->json($creditCardDue);  
    }
    
         public function fetchCreditCardPayByPaymentType($id)
    {
        $creditCards = DB::table('credit_card_pay as ccp')
        ->select( 'ccp.id', 'ccp.credit_card_due_id', 'ccp.amount', 'ccp.status', 'ccp.updated_at', 'ptp.bank_id', 'b.bank_name', 'ptp.payment_term_id',
              'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.due_date', 'ptp.buffer_days', 'ptp.credit_limit',
              'ptp.statement_date', 'ptp.total_balance_due')       
        ->join('credit_card_due as ccd', 'ccd.id', '=', 'ccp.credit_card_due_id')           
        ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccd.payment_type_po_id')    
        ->join('bank as b', 'ptp.bank_id', '=', 'b.id')         
        ->where('ptp.id',  $id)
        ->orderBy('ccp.id', 'desc')
        ->get();


        return response()->json($creditCards);   
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
     * @param  \App\Models\CreditCardPay  $creditCardPay
     * @return \Illuminate\Http\Response
     */
    public function show(CreditCardPay $creditCardPay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditCardPay  $creditCardPay
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditCardPay $creditCardPay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditCardPay  $creditCardPay
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditCardPay $creditCardPay)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditCardPay  $creditCardPay
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditCardPay $creditCardPay)
    {
        $creditCardPay = CreditCardPay::find($creditCardPay->id);

        $creditCardDue = CreditCardDue::find($creditCardPay->credit_card_due_id);
        $creditCardDue->amount_paid = $creditCardDue->amount_paid - $creditCardPay->amount; 
        $creditCardDue->status = 0; 

        $paymentTypePo = PaymentTypePo::find($creditCardDue->payment_type_po_id);
        if ($paymentTypePo->payment_term_id == 4) {
            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due  + $creditCardPay->amount;
            $paymentTypePo->save();
        }
      

        $creditCardDue->save();
        $creditCardPay->delete();
        return response()->json("deleted");
    }
}
