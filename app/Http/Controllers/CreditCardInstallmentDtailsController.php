<?php

namespace App\Http\Controllers;

use App\Models\CreditCardInstallmentDtails;
use App\Models\CreditCardDue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditCardInstallmentDtailsController extends Controller
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

      public function fetchCreditCardInstallmentDetail($id)
    {
         $creditCardInstallmentDtails = DB::table('credit_card_installment_details')->where('credit_card_due_id',  $id)->first();
        return response()->json($creditCardInstallmentDtails);  
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $creditCardInstallmentDtails = new CreditCardInstallmentDtails;
        $creditCardInstallmentDtails->credit_card_due_id = $request->input('credit_card_due_id');
        $creditCardInstallmentDtails->number_of_months = $request->input('number_of_months');
        $creditCardInstallmentDtails->total_interest = $request->input('total_interest');
        $creditCardInstallmentDtails->interest_amount = $request->input('interest_amount');
        $creditCardInstallmentDtails->interest_monthly = $request->input('interest_monthly');
        $creditCardInstallmentDtails->amount = $request->input('amount');
        $creditCardInstallmentDtails->start_date = $request->input('start_date');
        $creditCardInstallmentDtails->status = $request->input('status');
        $creditCardInstallmentDtails->save();

        //$creditCardDue = CreditCardDue::where('due_date', $request->input('start_date'))->first();
          $creditCardDue = CreditCardDue::where(['due_date' => $request->input('start_date'),
                                                 'payment_type_po_id' => $request->input('payment_type_po_id')])->first();
        // $creditCardDue = DB::table('credit_card_due')->where('due_date',  $request->input('start_date'))->where('payment_type_po_id', $request->input('payment_type_po_id'))->first();
        $creditCardDue->amount = $creditCardDue->amount - $request->input('amount');
        $creditCardDue->amount = $creditCardDue->amount + $request->input('interest_monthly');
        $creditCardDue->is_installment =$creditCardInstallmentDtails->id;
        $creditCardDue->save();
        
        for ($x = 1; $x < $request->input('number_of_months'); $x++) {
            $date = Carbon::parse($request->input('start_date'));
            $newDueDate = $date->addMonths($x);

            // $creditCardDue = DB::table('credit_card_due')->where('due_date',  $newDueDate)->where('payment_type_po_id', $request->input('payment_type_po_id'))->first();
                      $creditCardDue = CreditCardDue::where(['due_date' => $newDueDate,
                                                 'payment_type_po_id' => $request->input('payment_type_po_id')])->first();
            if ($creditCardDue != null) {
                $creditCardDue->amount = $creditCardDue->amount + $request->input('interest_monthly');
                $creditCardDue->is_installment = $creditCardInstallmentDtails->id;
            } else {
                $creditCardDue = new CreditCardDue;
                $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
                $creditCardDue->min_amount = 0;
                $creditCardDue->amount = $request->input('interest_monthly');
                $creditCardDue->is_installment =$creditCardInstallmentDtails->id;
                $creditCardDue->due_date = $newDueDate;
                $creditCardDue->status = 0;
                $creditCardDue->type = 'CREDIT_CARD';
            }
            $creditCardDue->save();
        }

        return  response()->json($creditCardInstallmentDtails);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditCardInstallmentDtails  $creditCardInstallmentDtails
     * @return \Illuminate\Http\Response
     */
    public function show(CreditCardInstallmentDtails $creditCardInstallmentDtails)
    {
        $creditCardInstallmentDtails = CreditCardInstallmentDtails::find($creditCardInstallmentDtails->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($creditCardInstallmentDtails);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditCardInstallmentDtails  $creditCardInstallmentDtails
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditCardInstallmentDtails $creditCardInstallmentDtails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditCardInstallmentDtails  $creditCardInstallmentDtails
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditCardInstallmentDtails $creditCardInstallmentDtails)
    {
        $creditCardInstallmentDtails = CreditCardInstallmentDtails::find($creditCardInstallmentDtails->id);
        $creditCardInstallmentDtails->credit_card_due_id = $request->input('credit_card_due_id');
        $creditCardInstallmentDtails->number_of_months = $request->input('number_of_months');
        $creditCardInstallmentDtails->total_interest = $request->input('total_interest');
        $creditCardInstallmentDtails->interest_amount = $request->input('interest_amount');
        $creditCardInstallmentDtails->interest_monthly = $request->input('interest_monthly');
        $creditCardInstallmentDtails->amount = $request->input('amount');
        $creditCardInstallmentDtails->start_date = $request->input('start_date');
        $creditCardInstallmentDtails->status = $request->input('status');
        $creditCardInstallmentDtails->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($creditCardInstallmentDtails);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditCardInstallmentDtails  $creditCardInstallmentDtails
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditCardInstallmentDtails $creditCardInstallmentDtails)
    {
        //
    }
}
