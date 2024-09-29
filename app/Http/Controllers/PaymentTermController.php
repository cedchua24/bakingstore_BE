<?php

namespace App\Http\Controllers;

use App\Models\PaymentTerm;
use Illuminate\Http\Request;

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
