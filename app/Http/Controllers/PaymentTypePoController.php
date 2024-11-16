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
            ->select('ptp.id', 'ptp.payment_type',
              'ptp.payment_type_description', 'ptp.status', 'ptp.type', 'ptp.due_date', 'pt.payment_term')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.type')    
            ->where('ptp.status', '=', 1)    
            ->get();

        return response()->json($data);
    }

    public function findByCategory($id)
    {
         $data = DB::table('payment_type_po as ptp')
            ->select('ptp.id', 'ptp.payment_type',
              'ptp.payment_type_description', 'ptp.status', 'ptp.type', 'ptp.due_date', 'pt.payment_term')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.type')    
            ->where('ptp.status', '=', 1)    
             ->where('ptp.type', '=', $id) 
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
        $paymentTypePo = new PaymentTypePo;
        $paymentTypePo->payment_type = $request->input('payment_type');
        $paymentTypePo->payment_type_description = $request->input('payment_type_description');
        $paymentTypePo->status = $request->input('status');
        $paymentTypePo->type = $request->input('type');
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
        $paymentTypePo = PaymentTypePo::find($paymentTypePo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($paymentTypePo);
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
        $paymentTypePo = PaymentTypePo::find($paymentTypePo->id);
        $paymentTypePo->payment_type = $request->input('payment_type');
        $paymentTypePo->payment_type_description = $request->input('payment_type_description');
        $paymentTypePo->status = $request->input('status');
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
