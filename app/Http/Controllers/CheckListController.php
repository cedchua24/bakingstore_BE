<?php

namespace App\Http\Controllers;

use App\Models\CheckList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('check_list as cl')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'cl.id',
                'cl.check_list_name',
                'cl.assignee',
                'cl.checker',
                'cl.time_of_day',
                'cl.frequency',
                'cl.status',
                'cl.created_at',
                'cl.updated_at',
                DB::raw("asg.name as assignee_name"),
                DB::raw("ch.name as checker_name")
            )
            ->where('asg.status', 0)
            ->where('ch.status', 0)
            ->orderByRaw("
                CASE cl.frequency
                    WHEN 'DAILY' THEN 1
                    WHEN 'WEEKLY' THEN 2
                    WHEN 'MONTHLY' THEN 3
                    ELSE 4
                END
            ")
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
            'check_list_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $checkList = new CheckList;
        $checkList->check_list_name = $request->input('check_list_name');
        $checkList->assignee = $request->input('assignee');
        $checkList->checker = $request->input('checker');
        $checkList->time_of_day = $request->input('time_of_day');
        $checkList->frequency = $request->input('frequency');
        $checkList->status = $request->input('status');
        $checkList->save();
        return  response()->json($checkList);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return \Illuminate\Http\Response
     */
    public function show(CheckList $checkList)
    {
        $data = DB::table('check_list as cl')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'cl.id',
                'cl.check_list_name',
                'cl.assignee',
                'cl.checker',
                'cl.time_of_day',
                'cl.frequency',
                'cl.status',
                'cl.created_at',
                'cl.updated_at',
                DB::raw("asg.name as assignee_name"),
                DB::raw("ch.name as checker_name")
            )
            ->where('cl.id', $checkList->id)
            ->first();

        return  response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return \Illuminate\Http\Response
     */
    public function edit(CheckList $checkList)
    {
        $data = DB::table('check_list as cl')
            ->join('check_list_transaction as chl', 'chl.check_list_id', '=', 'cl.id')
            ->join('users as asg', 'asg.id', '=', 'cl.assignee')
            ->join('users as ch', 'ch.id', '=', 'cl.checker')
            ->select(
                'cl.id',
                'cl.check_list_name',
                'cl.assignee',
                'cl.checker',
                'cl.time_of_day',
                'cl.frequency',
                'cl.status',
                'cl.created_at',
                'cl.updated_at',

                'chl.id as check_list_transaction_id',
                'chl.comment',
                'chl.grade',
                'chl.grade_checker',
                'chl.status as transaction_status',

                DB::raw("asg.name as assignee_name"),
                DB::raw("ch.name as checker_name")
            )
            ->where('chl.id',  $checkList->id) // or $request->id
            ->first();

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CheckList  $checkList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CheckList $checkList)
    {
        $checkList = CheckList::find($checkList->id);
        
        $checkList->check_list_name = $request->input('check_list_name');
        $checkList->assignee = $request->input('assignee');
        $checkList->checker = $request->input('checker');
        $checkList->time_of_day = $request->input('time_of_day');
        $checkList->frequency = $request->input('frequency');
        $checkList->status = $request->input('status');
        $checkList->save();
      
        return  response()->json($checkList);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CheckList  $checkList
     * @return \Illuminate\Http\Response
     */
    public function destroy(CheckList $checkList)
    {
        $checkList = CheckList::find($checkList->id);
        $checkList->delete();
        return response()->json($checkList);
    }
}
