<?php

namespace App\Http\Controllers;

use App\Models\LoanTransaction;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class LoanTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loanTransaction = LoanTransaction::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($loanTransaction);
    }

       public function fetchloanTransactionV2($id)
    {
  
         $data = DB::table('loan_transaction as lt')
            ->leftJoin('bank as b', 'b.id', '=', 'lt.bank_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'lt.payment_type_po_id')
            ->leftJoin('bank as bb', 'bb.id', '=', 'ptp.bank_id')
            ->select('lt.id', 'lt.borrower', 'lt.details', 'lt.bank_id', 'lt.payment_type_po_id', 'lt.number_of_months', 'lt.total_interest',
             'lt.interest', 'lt.interest_monthly', 'lt.amount', 'lt.amount_monthly', 'lt.start_date', 'lt.status', 'b.bank_name as b_bank_name', 'bb.bank_name', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description')    
            ->get();
        
            

       $response = [

              'data' => $data,
            //   'details' => $details,
              'code' => 200,
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
            'amount' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $loanTransaction = new LoanTransaction;
        $loanTransaction->details = $request->input('details') != '' ? $request->input('details') : "";
        $loanTransaction->borrower = $request->input('borrower') != '' ? $request->input('borrower') : "";
        $loanTransaction->bank_id = $request->input('bank_id');
        $loanTransaction->payment_type_po_id = $request->input('payment_type_po_id');
        
        $loanTransaction->number_of_months = $request->input('number_of_months');
        $loanTransaction->total_interest = $request->input('total_interest');
        $loanTransaction->interest = $request->input('interest');
        $loanTransaction->interest_monthly = $request->input('interest_monthly');
        $loanTransaction->amount = $request->input('amount');
        $loanTransaction->amount_monthly = $request->input('amount_monthly');
        $loanTransaction->start_date = $request->input('start_date');
        $loanTransaction->status = $request->input('status');
        $loanTransaction->save();

        for ($x = 0; $x < $request->input('number_of_months'); $x++) {
            $date = Carbon::parse($request->input('start_date'));

            $loan = new Loan;
            $loan->loan_transaction_id = $loanTransaction->id;
            $loan->status = 0;
            $loan->amount_due = $request->input('interest_monthly');
            $loan->due_date = $date->addMonths($x);
            $loan->save();
        }
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($loanTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LoanTransaction  $loanTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(LoanTransaction $loanTransaction)
    {
        // $loanTransaction = LoanTransaction::find($loanTransaction->id);
        // //return view('categories.show')->with('category', $category);
        // return  response()->json($loanTransaction);

         $data = DB::table('loan_transaction as lt')
            ->leftJoin('bank as b', 'b.id', '=', 'lt.bank_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'lt.payment_type_po_id')
            ->leftJoin('bank as bb', 'bb.id', '=', 'ptp.bank_id')
            ->select('lt.id', 'lt.borrower', 'lt.details', 'lt.bank_id', 'lt.payment_type_po_id', 'lt.number_of_months', 'lt.total_interest',
             'lt.interest', 'lt.interest_monthly', 'lt.amount', 'lt.amount_monthly', 'lt.start_date', 'lt.status', 'b.bank_name as b_bank_name', 'bb.bank_name', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description')    
          ->where('lt.id', '=', $loanTransaction->id) 
          ->first();
        
            

       $response = [

              'data' => $data,
            //   'details' => $details,
              'code' => 200,
              'message' => "Successfully Added"
          ];

            return response()->json($data);   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LoanTransaction  $loanTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(LoanTransaction $loanTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LoanTransaction  $loanTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoanTransaction $loanTransaction)
    {
        // Create Post
        $loanTransaction = LoanTransaction::find($loan->id);
        $loanTransaction->details = $request->input('details');
        $loanTransaction->bank_id = $request->input('bank_id');
        $loanTransaction->payment_type_po_id = $request->input('payment_type_po_id');
        $loanTransaction->number_of_months = $request->input('number_of_months');
        $loanTransaction->total_interest = $request->input('total_interest');
        $loanTransaction->interest = $request->input('interest');
        $loanTransaction->interest_monthly = $request->input('interest_monthly');
        $loanTransaction->amount = $request->input('amount');
        $loanTransaction->amount_monthly = $request->input('amount_monthly');
        $loanTransaction->start_date = $request->input('start_date');
        $loanTransaction->status = $request->input('status');
        $loanTransaction->save();
        
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($loanTransaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoanTransaction  $loanTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoanTransaction $loanTransaction)
    {
        $loanTransaction = LoanTransaction::find($loanTransaction->id);
        $loanTransaction->delete();

         Loan::where('loan_transaction_id', $loanTransaction->id)->delete();
        return response()->json($loanTransaction);
    }
}
