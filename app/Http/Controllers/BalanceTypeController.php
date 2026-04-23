<?php

namespace App\Http\Controllers;

use App\Models\BalanceType;
use Illuminate\Http\Request;

class BalanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $balanceType = BalanceType::all();
        return response()->json($balanceType);
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
     * @param  \App\Models\BalanceType  $balanceType
     * @return \Illuminate\Http\Response
     */
    public function show(BalanceType $balanceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BalanceType  $balanceType
     * @return \Illuminate\Http\Response
     */
    public function edit(BalanceType $balanceType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BalanceType  $balanceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BalanceType $balanceType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceType  $balanceType
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceType $balanceType)
    {
        //
    }
}
