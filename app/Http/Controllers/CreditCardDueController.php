<?php

namespace App\Http\Controllers;

use App\Models\CreditCardDue;
use App\Models\CreditCardPay;
use App\Models\PaymentTypePo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CreditCardDueController extends Controller
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

       public function fetallCreditDueById($id)
    {
        $creditCards = DB::table('credit_card_due')->where('payment_type_po_id',  $id)->orderBy('due_date', 'asc')->get();

        return response()->json($creditCards);   
    }

    public function fetchCreditCardDueList($id)
    {
            $data = DB::table('credit_card_due as ccd')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccd.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->select( 'ccd.id', 'ccd.min_amount', 'ccd.interest_amount', 'ccd.amount', 'ccd.amount_paid', 'ccd.due_date', 'ccd.type', 'ccd.is_installment',
            'ccd.status', 'ccd.due_date', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.credit_limit', 'ptp.total_balance_due', 'b.bank_name')    
            ->where('ptp.payment_term_id', $id)
            ->where('ccd.status', 0)
            ->orderBy('ccd.due_date', 'asc')
            ->get(); 
            


         return response()->json($data); 
    }

       public function fetchChequeDueList($id)
    {
            $data = DB::table('credit_card_due as ccd')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccd.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->join('mode_of_payment_po as mopp', 'mopp.id', '=', 'ccd.mode_of_payment_po_id')
            ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mopp.order_supplier_transaction_id')
            ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
            ->select( 'ccd.id', 'ccd.min_amount', 'ccd.interest_amount', 'ccd.amount', 'ccd.amount_paid', 'ccd.due_date', 'ccd.type', 'ccd.is_installment',
            'ccd.status', 'ccd.due_date', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'b.bank_name',
              's.supplier_name', 'ost.id as transaction_id')    
            ->where('ptp.payment_term_id', $id)
            ->where('ccd.status', 0)
            ->orderBy('ccd.due_date', 'asc')
            ->get(); 
            

         return response()->json($data); 
    }
    
           public function fetchChequePaidList($id)
    {
            $data = DB::table('credit_card_due as ccd')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccd.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('mode_of_payment_po as mopp', 'mopp.id', '=', 'ccd.mode_of_payment_po_id')
            ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mopp.order_supplier_transaction_id')
            ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
            ->select( 'ccd.id', 'ccd.min_amount', 'ccd.amount', 'ccd.amount_paid', 'ccd.due_date', 'ccd.type', 'ccd.is_installment',
            'ccd.status', 'ccd.due_date', 'ccd.interest_amount', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'b.bank_name',
            'ost.id as transaction_id', 'ost.invoice_number', 's.supplier_name')    
            ->where('ptp.payment_term_id', $id)
            ->where('ccd.status', 1)
            ->orderBy('ccd.due_date', 'asc')
            ->get(); 
            

         return response()->json($data); 
    }

    public function fetchCreditCardPaidList($id)
    {
            $data = DB::table('credit_card_due as ccd')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'ccd.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->select( 'ccd.id', 'ccd.min_amount', 'ccd.amount', 'ccd.amount_paid', 'ccd.due_date', 'ccd.type', 'ccd.is_installment',
            'ccd.status', 'ccd.due_date', 'ccd.interest_amount', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'b.bank_name')    
            ->where('ptp.payment_term_id', $id)
            ->where('ccd.status', 1)
            ->orderBy('ccd.due_date', 'asc')
            ->get(); 
            


         return response()->json($data); 
    }


    
       public function fetchCreditCardDetail($id)
    {
            $data = DB::table('payment_type_po as ptt')
            ->join('payment_term as pt', 'pt.id', '=', 'ptt.payment_term_id')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->join('credit_card_due as ccd', 'ccd.payment_type_po_id', '=', 'ptt.id')
            ->select( 'ptt.id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description',
            'ptt.due_date', 'ptt.buffer_days', 'ptt.credit_limit',
            'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name', 'ccd.amount', 'ccd.min_amount',
             'ccd.interest_amount', 'ccd.due_date as payment_due_date', 'pt.payment_term', 'ptt.payment_term_id')    
            ->where('ccd.id', $id)
            ->first(); 
            
         return response()->json($data); 
    }




    

           public function fetchPaymentTypeDetail($id)
    {
            $data = DB::table('payment_type_po as ptt')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->join('payment_term as pt', 'pt.id', '=', 'ptt.payment_term_id')
            ->select( 'ptt.id', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description',
            'ptt.due_date', 'ptt.buffer_days', 'ptt.credit_limit',
            'ptt.statement_date', 'ptt.total_balance_due', 'ptt.status', 'b.bank_name', 'pt.payment_term')    
            ->where('ptt.id', $id)
            ->first(); 
            


         return response()->json($data); 
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


    public function createCreditDueYearly(Request $request)
    {
        $currentDate = Carbon::now('GMT+8');
        $year = $currentDate->year;
        $due_date = '';
        $due_date_final ='';
        $dueRequest = (string)$request->input('due_date');
        $due_date_list = array();
        

          for($i=1; $i <=12; $i++) { 
            $due_date = $year."-".$i."-".$dueRequest;
            $due_date_final =  date("Y-m-d", strtotime($due_date));

            $data = DB::table('credit_card_due as ccd')
             ->where('ccd.payment_type_po_id', $request->input('payment_type_po_id'))
             ->where('ccd.due_date', $due_date_final)
             ->first();
            if ($data == null) {
                $creditCardDue = new CreditCardDue;
                $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
                $creditCardDue->due_date = $due_date_final;
                $creditCardDue->type = $request->input('type');
                // if ($due_date_final < date('Y-m-d')) {
                    $creditCardDue->status = 1;
                // }
                $creditCardDue->save();          
                array_push($due_date_list, $due_date_final);       
            }
            
          }


           $response = [
              'id' => $request->input('payment_type_po_id'),
              'data' => $due_date_list,
              'date' => $currentDate->year,
              'due_date' => $due_date_final,
              'code' => 200,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditCardDue  $creditCardDue
     * @return \Illuminate\Http\Response
     */
    public function show(CreditCardDue $creditCardDue)
    {
        $creditCardDue = CreditCardDue::find($creditCardDue->id);
        return response()->json($creditCardDue);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditCardDue  $creditCardDue
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditCardDue $creditCardDue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditCardDue  $creditCardDue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditCardDue $creditCardDue)
    {
        $creditCardDue = CreditCardDue::find($creditCardDue->id);

        $paymentTypePo = PaymentTypePo::find($creditCardDue->payment_type_po_id);
       

            $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
            $creditCardDue->min_amount = $request->input('min_amount');
            $creditCardDue->interest_amount = $request->input('interest_amount');
            $creditCardDue->is_installment = $request->input('is_installment'); 
            $creditCardDue->due_date = $request->input('due_date');   
            
            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due  + $creditCardDue->amount ;
            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due  - $request->input('amount');

            $creditCardDue->amount = $request->input('amount');

     
        $paymentTypePo->save();
        $creditCardDue->save();
      

        return response()->json($creditCardDue);
    }

        public function saveCreditCardPay(Request $request)
    {
        $creditCardDue = CreditCardDue::find($request->input('id'));

        $paymentTypePo = PaymentTypePo::find($creditCardDue->payment_type_po_id);
       

            $creditCardPay = new CreditCardPay;
            $creditCardPay->credit_card_due_id = $creditCardDue->id;
            $creditCardPay->amount = $request->input('amount_paid');
            $creditCardPay->payment_type_po_id = $request->input('payment_type_po_id');
            $creditCardPay->status = 1;
           

            $creditCardDue->amount_paid =  $creditCardDue->amount_paid +  $request->input('amount_paid'); 

            if ($paymentTypePo->payment_term_id == 3) {
                $creditCardPay->amount = $request->input('constant_amount');  
            }
           
            $creditCardPay->save();
            $credit_card_pay = DB::table('credit_card_pay')
            ->select(DB::raw('SUM(amount) as total_amount'))  
            ->where('credit_card_due_id', $creditCardDue->id)  
            ->first();

            if ($credit_card_pay->total_amount >= $creditCardDue->amount) {
                $creditCardDue->status = 1; 

                $interest = $credit_card_pay->total_amount -  $creditCardDue->amount;
                if ($interest > 0) {
                   $creditCardDue->interest_amount = $interest;  
                }
            }
        if ($paymentTypePo->payment_term_id == 4) {
            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due  - $request->input('amount_paid');    
            $paymentTypePo->save();    
        }



    
        $creditCardDue->save();
 

        return response()->json($paymentTypePo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditCardDue  $creditCardDue
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditCardDue $creditCardDue)
    {
        //
    }
}
