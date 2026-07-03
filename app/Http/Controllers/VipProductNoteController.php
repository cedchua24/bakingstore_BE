<?php

namespace App\Http\Controllers;

use App\Models\VipProductNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VipProductNoteController extends Controller
{
    public function index()
    {
        return $this->getAll();
    }

    public function getAll()
    {
        $data = DB::table('vip_product_note as vpn')
            ->join('vip_product_transaction as vpt', 'vpt.id', '=', 'vpn.vip_product_transaction_id')
            ->join('users as u', 'u.id', '=', 'vpn.user_id')
            ->select(
                'vpn.id',
                'vpn.vip_product_transaction_id',
                'vpn.user_id',
                'vpn.comment',
                'vpn.status',
                'vpn.created_at',
                'vpn.updated_at',
                'vpt.vip_product_id',
                'vpt.product_id',
                'u.name'
            )
            ->orderBy('vpn.id', 'desc')
            ->get();

        return response()->json($data);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'vip_product_transaction_id' => 'required',
            'user_id' => 'required',
        ]);

        $vipProductNote = new VipProductNote;
        $vipProductNote->vip_product_transaction_id = $request->input('vip_product_transaction_id');
        $vipProductNote->user_id = $request->input('user_id');
        $vipProductNote->comment = $request->input('comment');
        $vipProductNote->status = $request->input('status');
        $vipProductNote->save();

        return response()->json($vipProductNote);
    }

    public function show(VipProductNote $vipProductNote)
    {
        return response()->json($vipProductNote);
    }

    public function edit(VipProductNote $vipProductNote)
    {
        return response()->json($vipProductNote);
    }

    public function update(Request $request, VipProductNote $vipProductNote)
    {
        $vipProductNote->vip_product_transaction_id = $request->input('vip_product_transaction_id');
        $vipProductNote->user_id = $request->input('user_id');
        $vipProductNote->comment = $request->input('comment');
        $vipProductNote->status = $request->input('status');
        $vipProductNote->save();

        return response()->json($vipProductNote);
    }

    public function destroy(VipProductNote $vipProductNote)
    {
        $vipProductNote->delete();

        return response()->json($vipProductNote);
    }
}
