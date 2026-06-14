<?php

namespace App\Http\Controllers;

use App\Models\CheckListHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckListHistoryController extends Controller
{
    public function index()
    {
        $data = DB::table('check_list_history as clh')
            ->leftJoin('users as u', 'u.id', '=', 'clh.user_id')
            ->select(
                'clh.id',
                'clh.check_list_transaction_id',
                'clh.comment',
                'clh.user_id',
                'clh.status',
                'clh.created_at',
                'clh.updated_at',
                DB::raw('u.name as user_name')
            )
            ->orderBy('clh.id', 'desc')
            ->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'check_list_transaction_id' => 'required',
            'user_id' => 'required',
            'status' => 'required',
        ]);

        $checkListHistory = new CheckListHistory;
        $checkListHistory->check_list_transaction_id = $request->input('check_list_transaction_id');
        $checkListHistory->comment = $request->input('comment');
        $checkListHistory->user_id = $request->input('user_id');
        $checkListHistory->status = $request->input('status');
        $checkListHistory->save();

        return response()->json($checkListHistory);
    }

    public function show(CheckListHistory $checkListHistory)
    {
        $data = DB::table('check_list_history as clh')
            ->leftJoin('users as u', 'u.id', '=', 'clh.user_id')
            ->select(
                'clh.id',
                'clh.check_list_transaction_id',
                'clh.comment',
                'clh.user_id',
                'clh.status',
                'clh.created_at',
                'clh.updated_at',
                DB::raw('u.name as user_name')
            )
            ->where('clh.id', $checkListHistory->id)
            ->first();

        return response()->json($data);
    }

    public function edit(CheckListHistory $checkListHistory)
    {
        return $this->show($checkListHistory);
    }

    public function update(Request $request, CheckListHistory $checkListHistory)
    {
        $checkListHistory = CheckListHistory::find($checkListHistory->id);
        $checkListHistory->check_list_transaction_id = $request->input('check_list_transaction_id');
        $checkListHistory->comment = $request->input('comment');
        $checkListHistory->user_id = $request->input('user_id');
        $checkListHistory->status = $request->input('status');
        $checkListHistory->save();

        return response()->json($checkListHistory);
    }

    public function destroy(CheckListHistory $checkListHistory)
    {
        $checkListHistory = CheckListHistory::find($checkListHistory->id);
        $checkListHistory->delete();

        return response()->json($checkListHistory);
    }

    public function fetchByCheckListTransactionId($id)
    {
        $data = DB::table('check_list_history as clh')
            ->leftJoin('users as u', 'u.id', '=', 'clh.user_id')
            ->select(
                'clh.id',
                'clh.check_list_transaction_id',
                'clh.comment',
                'clh.user_id',
                'clh.status',
                'clh.created_at',
                'clh.updated_at',
                DB::raw('u.name as user_name')
            )
            ->where('clh.check_list_transaction_id', $id)
            ->orderBy('clh.id', 'desc')
            ->get();

        return response()->json($data);
    }
}
