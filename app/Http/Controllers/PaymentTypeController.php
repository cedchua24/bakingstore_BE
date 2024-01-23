<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paymentType = PaymentType::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($paymentType);
    }


    public function fetchEnablePaymentType()
    {
         $data = DB::table('payment_type as pt')
            ->select('pt.id', 'pt.payment_type', 'pt.payment_type_description', 'pt.status', 'pt.type')
            ->where('pt.status', '=', 1)    
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
            'payment_type' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $paymentType = new PaymentType;
        $paymentType->payment_type = $request->input('payment_type');
        $paymentType->payment_type_description = $request->input('payment_type_description');
        $paymentType->status = $request->input('status');
        $paymentType->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($paymentType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentType  $paymentType
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentType $paymentType)
    {
        $paymentType = PaymentType::find($paymentType->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($paymentType);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentType  $paymentType
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentType $paymentType)
    {
        $paymentType = PaymentType::find($paymentType->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($paymentType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentType  $paymentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentType $paymentType)
    {
        $paymentType = PaymentType::find($paymentType->id);
        $paymentType->payment_type = $request->input('payment_type');
        $paymentType->payment_type_description = $request->input('payment_type_description');
        $paymentType->status = $request->input('status');
        $paymentType->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($paymentType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaymentType  $paymentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentType $paymentType)
    {
        $paymentType = PaymentType::find($paymentType->id);
        $paymentType->delete();
        return response()->json($paymentType);
    }
}
