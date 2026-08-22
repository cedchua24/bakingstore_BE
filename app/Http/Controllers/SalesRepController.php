<?php

namespace App\Http\Controllers;

use App\Models\SalesRep;
use Illuminate\Http\Request;

class SalesRepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $salesRep = SalesRep::all();
        return response()->json($salesRep);
    }

    /**
     * Display pending sales representative requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRequests()
    {
        $salesRepRequests = SalesRep::where('status', 0)->get();

        return response()->json($salesRepRequests);
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
     * @param  \App\Models\SalesRep  $salesRep
     * @return \Illuminate\Http\Response
     */
    public function show(SalesRep $salesRep)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesRep  $salesRep
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesRep $salesRep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesRep  $salesRep
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesRep $salesRep)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesRep  $salesRep
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesRep $salesRep)
    {
        //
    }
}
