<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
       public function index()
    {
        $loan = Loan::all();
     
         $data = DB::table('loan as ipt')
            ->select('ipt.id', 'ipt.loan_transaction_id', 'ipt.amount', 'ipt.amount_due', 'ipt.penalty', 'ipt.due_date'
            , 'ipt.status', 'ipt.created_at', 'ipt.updated_at')
            ->orderBy('ipt.due_date', 'asc')  
            ->get();

    //    $data = DB::table('loan as ipt')
    //         ->join('loan_transaction as lt', 'lt.id', '=', 'ipt.loan_transaction_id')  
    //         ->rightJoin('bank as b', 'b.id', '=', 'lt.bank_id')
    //         ->rightJoin('payment_type_po as ptp', 'ptp.id', '=', 'lt.payment_type_po_id')
    //         ->rightJoin('bank as bb', 'bb.id', '=', 'ptp.bank_id')
    //         ->select('ipt.id', 'ipt.loan_transaction_id', 'ipt.amount', 'ipt.amount_due', 'ipt.penalty', 'ipt.due_date'
    //         , 'ipt.status', 'ipt.created_at', 'ipt.updated_at')
    //         ->orderBy('ipt.due_date', 'asc')  
    //         ->get();
   

          $response = [
              'data' => $data,
              'message' => "Successfully Added"
          ];

        return response()->json($response);
    }

         public function fetchInstallmentList($id)
    {
           $data = DB::table('loan as ipt')
            ->select('ipt.id', 'ipt.loan_transaction_id', 'ipt.amount', 'ipt.amount_due', 'ipt.penalty', 'ipt.due_date'
            , 'ipt.status', 'ipt.created_at', 'ipt.updated_at', 'b.bank_name', 'ptp.account_number', 'ptp.account_name', 'ptp.account_description')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'ipt.payment_type_po_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->where('ipt.loan_transaction_id', '=', $id)    
            ->get();

           $total = DB::table('loan as ipt')
            ->select(DB::raw('COUNT(id) as total_count'),)  
            ->where('ipt.loan_transaction_id', '=', $id) 
            ->where('ipt.status', '=', 1) 
            ->first();

           $response = [
              'data' => $data,
              'paid_count' => $total->total_count,
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
            'loan_transaction_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $loan = new Loan;
        $loan->loan_transaction_id = $request->input('loan_transaction_id');
        $loan->payment_type_po_id = $request->input('payment_type_po_id');
        $loan->amount = $request->input('amount');
        $loan->amount_due = $request->input('amount_due');
        $loan->penalty = $request->input('penalty');
        $loan->due_date = $request->input('due_date');
        $loan->status = $request->input('status');
        $loan->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($loan);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        $loan = Loan::find($loan->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($loan);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function edit(Loan $loan)
    {
        $loan = Loan::find($loan->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($loan);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Loan $loan)
    {
        $loan = Loan::find($loan->id);
        $loan->loan_transaction_id = $request->input('loan_transaction_id');
        $loan->payment_type_po_id = $request->input('payment_type_po_id');

        $loan->penalty = $request->input('amount') - $loan->amount_due;
    
        $loan->amount = $request->input('amount');
        if ($request->input('status') == 0) {
             $loan->status = 1;
        } else {
             $loan->status = 0;
             $loan->penalty = 0;
             $loan->amount = 0;
            $loan->payment_type_po_id = 0;
        }
      
        $loan->save();

       $total = DB::table('loan')
            ->select(DB::raw('COUNT(id) as total_count'),)  
            ->where('loan_transaction_id', $loan->loan_transaction_id )
            ->where('status', 0)
            ->first();

        $loanTransaction = LoanTransaction::find($loan->loan_transaction_id);
        if ($total->total_count == 0) {
          $loanTransaction->status = 1;               
        } else {
           $loanTransaction->status = 0;
        }
         $loanTransaction->save();

       $response = [
              'payment_status' => $loan->status,  
              'transaction_status' => $loanTransaction->status,         
              'message' => "Successfully Added"
          ];
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        $loan = Loan::find($loan->id);
        $loan->delete();
        return response()->json($loan);
    }
}
