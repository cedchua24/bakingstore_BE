<?php

namespace App\Http\Controllers;

use App\Models\VipCustomer;
use Illuminate\Http\Request;

class VipCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vipCustomers = VipCustomer::all();
        return response()->json($vipCustomers);
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
            'vip_name' => 'required',
        ]);

        $vipCustomer = new VipCustomer;
        $vipCustomer->vip_name = $request->input('vip_name');
        $vipCustomer->details = $request->input('details');
        $vipCustomer->vip_color = $request->input('vip_color');
        $vipCustomer->status = $request->input('status');
        $vipCustomer->save();

        return response()->json($vipCustomer);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VipCustomer  $vipCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(VipCustomer $vipCustomer)
    {
        $vipCustomer = VipCustomer::find($vipCustomer->id);
        return response()->json($vipCustomer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VipCustomer  $vipCustomer
     * @return \Illuminate\Http\Response
     */
    public function edit(VipCustomer $vipCustomer)
    {
        $vipCustomer = VipCustomer::find($vipCustomer->id);
        return response()->json($vipCustomer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VipCustomer  $vipCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VipCustomer $vipCustomer)
    {
        $vipCustomer = VipCustomer::find($vipCustomer->id);
        $vipCustomer->vip_name = $request->input('vip_name');
        $vipCustomer->details = $request->input('details');
        $vipCustomer->vip_color = $request->input('vip_color');
        $vipCustomer->status = $request->input('status');
        $vipCustomer->save();

        return response()->json($vipCustomer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VipCustomer  $vipCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(VipCustomer $vipCustomer)
    {
        $vipCustomer = VipCustomer::find($vipCustomer->id);
        $vipCustomer->delete();

        return response()->json($vipCustomer);
    }
}
