<?php

namespace App\Http\Controllers;

use App\Models\VipCustomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VipCustomerNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->getAll();
    }

    public function getAll()
    {
        $data = DB::table('vip_customer_note as vcn')
            ->join('vip_customer_transaction as vct', 'vct.id', '=', 'vcn.vip_customer_transaction_id')
            ->join('users as u', 'u.id', '=', 'vcn.user_id')
            ->select(
                'vcn.id',
                'vcn.vip_customer_transaction_id',
                'vcn.user_id',
                'vcn.comment',
                'vcn.status',
                'vcn.created_at',
                'vcn.updated_at',
                'vct.vip_customer_id',
                'vct.customer_id',
                DB::raw('u.name',)
            )
            ->orderBy('vcn.id', 'desc')
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
            'vip_customer_transaction_id' => 'required',
            'user_id' => 'required',
        ]);

        $vipCustomerNote = new VipCustomerNote;
        $vipCustomerNote->vip_customer_transaction_id = $request->input('vip_customer_transaction_id');
        $vipCustomerNote->user_id = $request->input('user_id');
        $vipCustomerNote->comment = $request->input('comment');
        $vipCustomerNote->status = $request->input('status');
        $vipCustomerNote->save();

        return response()->json($vipCustomerNote);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VipCustomerNote  $vipCustomerNote
     * @return \Illuminate\Http\Response
     */
    public function show(VipCustomerNote $vipCustomerNote)
    {
        $vipCustomerNote = VipCustomerNote::find($vipCustomerNote->id);
        return response()->json($vipCustomerNote);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VipCustomerNote  $vipCustomerNote
     * @return \Illuminate\Http\Response
     */
    public function edit(VipCustomerNote $vipCustomerNote)
    {
        $vipCustomerNote = VipCustomerNote::find($vipCustomerNote->id);
        return response()->json($vipCustomerNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VipCustomerNote  $vipCustomerNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VipCustomerNote $vipCustomerNote)
    {
        $vipCustomerNote = VipCustomerNote::find($vipCustomerNote->id);
        $vipCustomerNote->vip_customer_transaction_id = $request->input('vip_customer_transaction_id');
        $vipCustomerNote->user_id = $request->input('user_id');
        $vipCustomerNote->comment = $request->input('comment');
        $vipCustomerNote->status = $request->input('status');
        $vipCustomerNote->save();

        return response()->json($vipCustomerNote);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VipCustomerNote  $vipCustomerNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(VipCustomerNote $vipCustomerNote)
    {
        $vipCustomerNote = VipCustomerNote::find($vipCustomerNote->id);
        $vipCustomerNote->delete();

        return response()->json($vipCustomerNote);
    }
}
