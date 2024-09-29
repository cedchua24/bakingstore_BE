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
 $data = DB::table('payment_type_po')
            ->select('id', 'payment_type',
              'payment_type_description', 'status', 'type', 'due_date')
            ->where('payment_type_po.status', '=', 1)    
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
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTypePo $paymentTypePo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentTypePo $paymentTypePo)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentTypePo  $paymentTypePo
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTypePo $paymentTypePo)
    {
        //
    }
}
