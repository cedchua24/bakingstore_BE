<?php

namespace App\Http\Controllers;

use App\Models\CheckListTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckListTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'check_list_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $checkListTransaction = new CheckListTransaction;
        $checkListTransaction->check_list_id = $request->input('check_list_id');
        $checkListTransaction->assignee = $request->input('assignee');
        $checkListTransaction->checker = $request->input('checker');
        $checkListTransaction->comment = $request->input('comment');
        $checkListTransaction->grade = 0;
        $checkListTransaction->date = $request->input('date');
        $checkListTransaction->status = $request->input('status');
        $checkListTransaction->save(); 
        return  response()->json($checkListTransaction);
    }



    public function fetchCheckListByDate(Request $request)
    {
        $request->validate([
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date',
            'status' => 'nullable',
            'assignee' => 'nullable',
            'checker' => 'nullable',
            'frequency' => 'nullable',
            'time_of_day' => 'nullable',
        ]);

        $query = DB::table('check_list as cl')
            ->join('check_list_transaction as cht', 'cht.check_list_id', '=', 'cl.id')
            ->join('users as asg', 'asg.id', '=', 'cht.assignee')
            ->join('users as ch', 'ch.id', '=', 'cht.checker')
            ->select(
                'cht.id',
                'cl.check_list_name',
                'cl.time_of_day',
                'cl.frequency',
                'cht.grade',
                'cht.id as check_list_transaction_id',
                'cht.assignee',
                'cht.checker',
                'cht.comment',
                'cht.status',
                'cht.date',

                DB::raw("asg.name as assignee_name"),
                DB::raw("ch.name as checker_name")
            )
            ->whereBetween('cht.date', [
                $request->dateFrom,
                $request->dateTo
            ]);

        // Optional status filter
        if ($request->status && $request->status != 0) {
            $query->where('cht.status', $request->status);
        }

        // Optional assignee filter
        if ($request->assignee && $request->assignee != 0) {
            $query->where('cht.assignee', $request->assignee);
        }

        // Optional checker filter
        if ($request->checker && $request->checker != 0) {
            $query->where('cht.checker', $request->checker);
        }

        // Optional frequency filter
        if ($request->frequency && $request->frequency != 0) {
            $query->where('cl.frequency', $request->frequency);
        }

        // Optional time of day filter
        if ($request->time_of_day && $request->time_of_day != 0) {
            $query->where('cl.time_of_day', $request->time_of_day);
        }

        $data = $query
            ->orderByRaw("
                CASE cl.time_of_day
                    WHEN 'MORNING' THEN 1
                    WHEN 'AFTERNOON' THEN 2
                    WHEN 'EVENING' THEN 3
                    ELSE 4
                END
            ")
            ->get();

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CheckListTransaction  $checkListTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(CheckListTransaction $checkListTransaction)
    {
        $data = DB::table('check_list as cl')
            ->join('check_list_transaction as chl', 'chl.check_list_id', '=', 'cl.id')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'chl.id',
                'chl.date',
                'chl.check_list_id',
                'chl.comment',
                'chl.grade',
                'chl.status',
                'chl.assignee',
                'chl.checker',
                'cl.time_of_day',
                'cl.frequency',
                'cl.check_list_name'
            )
            ->where('chl.id',  $checkListTransaction->id) // or $request->id
            ->first();

        return response()->json($data);
    }
    public function show(CheckListTransaction $checkListTransaction)
    {
        $data = DB::table('check_list as cl')
            ->join('check_list_transaction as chl', 'chl.check_list_id', '=', 'cl.id')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'chl.id',
                'chl.date',
                'chl.check_list_id',
                'chl.comment',
                'chl.status',
                'chl.grade',
                'chl.assignee',
                'chl.checker',
                'cl.time_of_day',
                'cl.frequency',
                'cl.check_list_name'
            )
            ->where('chl.id', $checkListTransaction->id) // or $request->id
            ->first();

        return response()->json($data);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CheckListTransaction  $checkListTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CheckListTransaction $checkListTransaction)
    {
        $checkListTransaction = CheckListTransaction::find($checkListTransaction->id);
        $checkListTransaction->check_list_id = $request->input('check_list_id');
        $checkListTransaction->assignee = $request->input('assignee');
        $checkListTransaction->checker = $request->input('checker');
        $checkListTransaction->comment = $request->input('comment');
        $checkListTransaction->date = $request->input('date');
        $checkListTransaction->grade = $request->input('grade');
        $checkListTransaction->status = $request->input('status');
        $checkListTransaction->save(); 
        return  response()->json($checkListTransaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CheckListTransaction  $checkListTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(CheckListTransaction $checkListTransaction)
    {
        //
    }
}
