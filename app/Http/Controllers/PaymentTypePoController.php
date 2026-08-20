<?php

namespace App\Http\Controllers;

use App\Models\PaymentTypePo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentTypePoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data = DB::table('payment_type_po as ptp')
            ->select( 'pt.payment_term', 'ptp.id', 'ptp.bank_id', 'b.bank_name', 'ptp.payment_term_id',
              'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.buffer_days', 'ptp.due_date', 'ptp.credit_limit', 'ptp.status',
              'ptp.is_supplier', 'ptp.is_customer',
              'ptp.statement_date', 'ptp.total_balance_due')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')    
            ->join('bank as b', 'ptp.bank_id', '=', 'b.id')   
            ->get();

        return response()->json($data);
    }



    public function findByCategory($id)
    {
         $data = DB::table('payment_type_po as ptp')
            ->select( 'pt.payment_term', 'ptp.id', 'ptp.bank_id', 'b.bank_name', 'ptp.payment_term_id',
              'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.due_date','ptp.buffer_days',  'ptp.credit_limit', 'ptp.status',
              'ptp.is_supplier', 'ptp.is_customer',
               'ptp.statement_date', 'ptp.total_balance_due')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')    
            ->join('bank as b', 'ptp.bank_id', '=', 'b.id')  
            ->where('ptp.status', '=', 0)    
            ->where('ptp.payment_term_id', '=', $id) 
            ->get();

        return response()->json($data);  
    }

    public function findByCategoryV2($id, Request $request)
    {
        $validated = $request->validate([
            'is_supplier' => 'required|integer|in:0,1',
            'is_customer' => 'required|integer|in:0,1',
            'status' => 'required|integer'
        ]);

        $data = DB::table('payment_type_po as ptp')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->where('ptp.payment_term_id', $id)
            ->where('ptp.status', $validated['status'])
            ->when((int) $validated['is_supplier'] === 1, function ($query) {
                $query->where('ptp.is_supplier', 1);
            })
            ->when((int) $validated['is_customer'] === 1, function ($query) {
                $query->where('ptp.is_customer', 1);
            })
            ->select(
                'ptp.id',
                'ptp.payment_term_id',
                'pt.payment_term',
                'pt.status as payment_term_status',
                'ptp.bank_id',
                'b.bank_name',
                'b.status as bank_status',
                'ptp.account_number',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.due_date',
                'ptp.buffer_days',
                'ptp.credit_limit',
                'ptp.statement_date',
                'ptp.total_balance_due',
                'ptp.balance',
                'ptp.status',
                'ptp.is_supplier',
                'ptp.is_customer',
                'ptp.created_at',
                'ptp.updated_at'
            )
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
        $this->validate($request, [
            'payment_term_id' => 'required',
            'is_supplier' => 'nullable|integer|in:0,1',
            'is_customer' => 'nullable|integer|in:0,1'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $paymentTypePo = new PaymentTypePo;
        $paymentTypePo->payment_term_id = $request->input('payment_term_id');
        $paymentTypePo->bank_id = $request->input('bank_id');
        $paymentTypePo->account_number = $request->input('account_number');
        $paymentTypePo->account_name = $request->input('account_name');
        $paymentTypePo->account_description = $request->input('account_description') ? $request->input('account_description') : " " ;
        $paymentTypePo->due_date = $request->input('due_date');
        $paymentTypePo->buffer_days = $request->input('buffer_days');
        $paymentTypePo->credit_limit = $request->input('credit_limit');
        $paymentTypePo->statement_date = $request->input('statement_date');
        $paymentTypePo->total_balance_due = $request->input('total_balance_due');
        $paymentTypePo->status = $request->input('status');
        $paymentTypePo->is_supplier = (int) $request->input('is_supplier', 0);
        $paymentTypePo->is_customer = (int) $request->input('is_customer', 0);
        $paymentTypePo->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($paymentTypePo);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTypePo $paymentTypePo)
    {

        $data = DB::table('payment_type_po as ptp')
            ->select( 'pt.payment_term', 'ptp.id', 'ptp.bank_id', 'b.bank_name', 'ptp.payment_term_id',
              'ptp.account_number', 'ptp.account_name', 'ptp.account_description', 'ptp.due_date', 'ptp.buffer_days', 'ptp.credit_limit', 'ptp.status',
              'ptp.is_supplier', 'ptp.is_customer',
              'ptp.statement_date', 'ptp.total_balance_due')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')    
            ->join('bank as b', 'ptp.bank_id', '=', 'b.id')  
            ->where('ptp.id', '=', $paymentTypePo->id)    
            ->first();

        return response()->json($data);  
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentTypePo $paymentTypePo)
    {
        $paymentTypePo = PaymentTypePo::find($paymentTypePo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($paymentTypePo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentTypePo $paymentTypePo)
    {
        $this->validate($request, [
            'is_supplier' => 'nullable|integer|in:0,1',
            'is_customer' => 'nullable|integer|in:0,1'
        ]);

        $paymentTypePo = PaymentTypePo::find($paymentTypePo->id);
        $paymentTypePo->payment_term_id = $request->input('payment_term_id');
        $paymentTypePo->bank_id = $request->input('bank_id');
        $paymentTypePo->account_number = $request->input('account_number');
        $paymentTypePo->account_name = $request->input('account_name');
        $paymentTypePo->account_description = $request->input('account_description');
        $paymentTypePo->due_date = $request->input('due_date');
        $paymentTypePo->buffer_days = $request->input('buffer_days');
        $paymentTypePo->credit_limit = $request->input('credit_limit');
        $paymentTypePo->statement_date = $request->input('statement_date');
        $paymentTypePo->total_balance_due = $request->input('total_balance_due');
        $paymentTypePo->status = $request->input('status');
        $paymentTypePo->is_supplier = (int) $request->input('is_supplier', $paymentTypePo->is_supplier);
        $paymentTypePo->is_customer = (int) $request->input('is_customer', $paymentTypePo->is_customer);
        $paymentTypePo->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($paymentTypePo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTypePo $paymentTypePo)
    {
        $paymentTypePo = PaymentTypePo::find($paymentTypePo->id);
        $paymentTypePo->delete();
        return response()->json($paymentTypePo);
    }
}
