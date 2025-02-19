<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPaymentTransaction;
use App\Models\ModeOfPaymentPo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $installmentPayment = InstallmentPayment::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($installmentPayment); 
    }

    

     public function fetchInstallmentPayment($id)
    {
           $data = DB::table('installment_payment as ipt')
            ->select('ipt.id', 'ipt.installment_payment_transaction_id', 'ipt.amount', 'ipt.amount_due', 'ipt.penalty', 'ipt.due_date'
            , 'ipt.status', 'ipt.created_at', 'ipt.updated_at')
            ->where('ipt.installment_payment_transaction_id', '=', $id)    
            ->get();

           $total = DB::table('installment_payment as ipt')
            ->select(DB::raw('COUNT(id) as total_count'),)  
            ->where('ipt.installment_payment_transaction_id', '=', $id) 
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
            'installment_payment_transaction_id' => 'required'
        ]);


        // $item = UserProfile::create($data);

        // Create Post
        $installmentPayment = new InstallmentPayment;
        $installmentPayment->installment_payment_transaction_id = $request->input('installment_payment_transaction_id');
        $installmentPayment->amount = $request->input('amount');
        $installmentPayment->amount_due = $request->input('amount_due');
        $installmentPayment->penalty = $request->input('penalty');
        $installmentPayment->status = $request->input('status');
        $installmentPayment->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($installmentPayment);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function show(InstallmentPayment $installmentPayment)
    {
        $installmentPayment = InstallmentPayment::find($installmentPayment->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($installmentPayment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(InstallmentPayment $installmentPayment)
    {
        $installmentPayment = InstallmentPayment::find($installmentPayment->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($installmentPayment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InstallmentPayment $installmentPayment)
    {
        $installmentPayment = InstallmentPayment::find($installmentPayment->id);
        $installmentPayment->installment_payment_transaction_id = $request->input('installment_payment_transaction_id');

        $installmentPayment->penalty = $request->input('amount') - $installmentPayment->amount_due;
    
        $installmentPayment->amount = $request->input('amount');
        if ($request->input('status') == 0) {
             $installmentPayment->status = 1;
        } else {
             $installmentPayment->status = 0;
             $installmentPayment->penalty = 0;
             $installmentPayment->amount = 0;
        }
      
        $installmentPayment->save();

       $total = DB::table('installment_payment')
            ->select(DB::raw('COUNT(id) as total_count'),)  
            ->where('installment_payment_transaction_id', $installmentPayment->installment_payment_transaction_id )
            ->where('status', 0)
            ->first();

        $installmentPaymentTransaction = InstallmentPaymentTransaction::find($installmentPayment->installment_payment_transaction_id);
        $modeOfPaymentPo = ModeOfPaymentPo::find($installmentPaymentTransaction->mode_of_payment_po_id);
        if ($total->total_count == 0) {
          $installmentPaymentTransaction->status = 1;       
            $modeOfPaymentPo->status = 1;
            $modeOfPaymentPo->save();            
        } else {
           $installmentPaymentTransaction->status = 0;
           $modeOfPaymentPo->status = 0;
        }
         $modeOfPaymentPo->save();
         $installmentPaymentTransaction->save();

       $response = [
              'transaction_status' => $modeOfPaymentPo->status,
              'payment_status' => $installmentPayment->status,          
              'message' => "Successfully Added"
          ];
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InstallmentPayment  $installmentPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(InstallmentPayment $installmentPayment)
    {
        $installmentPayment = InstallmentPayment::find($installmentPayment->id);
        $installmentPayment->delete();
        return response()->json($installmentPayment);
    }
}
