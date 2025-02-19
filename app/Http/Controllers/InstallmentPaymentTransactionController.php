<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPaymentTransaction;
use App\Models\InstallmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class InstallmentPaymentTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $installmentPaymentTransaction = InstallmentPaymentTransaction::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($installmentPaymentTransaction); 
    }

           public function fetchPromoInstallmentList($id)
    {

         $data = DB::table('installment_payment_transaction as ipt')
            ->join('mode_of_payment_po as mop', 'mop.id', '=', 'ipt.mode_of_payment_po_id')
            ->join('order_supplier_transaction as osp', 'osp.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier', 'supplier.id', '=', 'osp.supplier_id')
            ->join('payment_type_po as ptt', 'ptt.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'b.id', '=', 'ptt.bank_id')
            ->select('ipt.id', 'mop.id as mode_of_payment_po_id','mop.amount', 'mop.date', 'mop.order_supplier_transaction_id', 'supplier.supplier_name',
             'b.bank_name', 'ptt.account_number', 'ptt.account_name', 'ptt.account_description', 'ipt.status', 'ipt.number_of_months'
             , 'ipt.amount', 'ipt.interest', 'ipt.interest_monthly', 'ipt.amount_monthly', 'ipt.start_date')    
            ->get();

       $response = [

              'data' => $data,
            //   'details' => $details,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

      public function fetchInstallmentTransactionByMOP($id)
    {
           $data = DB::table('installment_payment_transaction as ipt')
            ->select('ipt.id', 'ipt.mode_of_payment_po_id', 'ipt.number_of_months', 'ipt.interest', 'ipt.interest_monthly'
            , 'ipt.amount', 'ipt.amount_monthly', 'ipt.status', 'ipt.start_date')
            ->where('ipt.mode_of_payment_po_id', '=', $id)    
            ->get();

           $response = [
              'data' => $data,
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
            'mode_of_payment_po_id' => 'required'
        ]);

        $installmentPaymentTransaction = new InstallmentPaymentTransaction;
        $installmentPaymentTransaction->mode_of_payment_po_id = $request->input('mode_of_payment_po_id');
        $installmentPaymentTransaction->number_of_months = $request->input('number_of_months');
        $installmentPaymentTransaction->interest = $request->input('interest');
        $installmentPaymentTransaction->interest_monthly = $request->input('interest_monthly');
        $installmentPaymentTransaction->amount = $request->input('amount');
        $installmentPaymentTransaction->amount_monthly = $request->input('amount') / $request->input('number_of_months');



        $installmentPaymentTransaction->start_date = $request->input('start_date');
        $installmentPaymentTransaction->status = $request->input('status');
        $installmentPaymentTransaction->save();

        for ($x = 0; $x < $request->input('number_of_months'); $x++) {
            $date = Carbon::parse($request->input('start_date'));

            $installmentPayment = new InstallmentPayment;
            $installmentPayment->installment_payment_transaction_id = $installmentPaymentTransaction->id;
            $installmentPayment->status = 0;
            $installmentPayment->amount_due = $request->input('interest_monthly');
            $installmentPayment->due_date = $date->addMonths($x);
            $installmentPayment->save();
        }


        return  response()->json();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InstallmentPaymentTransaction  $installmentPaymentTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(InstallmentPaymentTransaction $installmentPaymentTransaction)
    {
        $installmentPaymentTransaction = InstallmentPaymentTransaction::find($installmentPaymentTransaction->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($installmentPaymentTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InstallmentPaymentTransaction  $installmentPaymentTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(InstallmentPaymentTransaction $installmentPaymentTransaction)
    {
        $installmentPaymentTransaction = InstallmentPaymentTransaction::find($installmentPaymentTransaction->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($installmentPaymentTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InstallmentPaymentTransaction  $installmentPaymentTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InstallmentPaymentTransaction $installmentPaymentTransaction)
    {
        $installmentPaymentTransaction = InstallmentPaymentTransaction::find($installmentPaymentTransaction->id);
        $installmentPaymentTransaction->mode_of_payment_po_id = $request->input('mode_of_payment_po_id');
        $installmentPaymentTransaction->number_of_months = $request->input('number_of_months');
        $installmentPaymentTransaction->interest = $request->input('interest');
        $installmentPaymentTransaction->interest_monthly = $request->input('interest_monthly');
        $installmentPaymentTransaction->amount = $request->input('amount');
        $installmentPaymentTransaction->amount_monthly = $request->input('amount') / $request->input('number_of_months');
        $installmentPaymentTransaction->start_date = $request->input('start_date');
        $installmentPaymentTransaction->status = $request->input('status');
        $installmentPaymentTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($installmentPayment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InstallmentPaymentTransaction  $installmentPaymentTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(InstallmentPaymentTransaction $installmentPaymentTransaction)
    {
        $installmentPaymentTransaction = InstallmentPaymentTransaction::find($installmentPaymentTransaction->id);
    
        DB::table('installment_payment')->where('installment_payment_transaction_id', $installmentPaymentTransaction->id)->delete();
        $installmentPaymentTransaction->delete();

        return response()->json($installmentPaymentTransaction);
    }
}
