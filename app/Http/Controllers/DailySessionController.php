<?php

namespace App\Http\Controllers;

use App\Models\DailySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DailySessionController extends Controller
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


        // $item = UserProfile::create($data);

        // Create Post
        $dailySession = new DailySession;
        $dailySession->user_id = $request->input('user_id');
        $dailySession->date = $request->input('today');
        $dailySession->status = $request->input('status');
        $dailySession->save();
        return  response()->json($dailySession);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DailySession  $dailySession
     * @return \Illuminate\Http\Response
     */
    public function show(DailySession $dailySession)
    {
        $dailySession = DailySession::find($dailySession->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($dailySession);
    }

    public function fetchDailySessionByDate($date)
      {
         $data = DB::table('daily_session as ds')
            ->select('ds.id', 'ds.user_id', 'ds.status', 'ds.date', 'ds.created_at', 'ds.updated_at') 
            ->where('ds.date', $date) 
            ->get();

         $response = [
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Added"
          ];
            return response()->json($response);       

     }

    public function fetchDailySession($date)
    {
        $exists = DB::table('daily_session as ds')
            ->where('ds.date', $date)
            ->where('ds.status', 0)
            ->exists();

        return response()->json($exists);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DailySession  $dailySession
     * @return \Illuminate\Http\Response
     */
    public function edit(DailySession $dailySession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DailySession  $dailySession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DailySession $dailySession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DailySession  $dailySession
     * @return \Illuminate\Http\Response
     */
    public function destroy(DailySession $dailySession)
    {
        //
    }
}
