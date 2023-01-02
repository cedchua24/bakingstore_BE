<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $warehouse = Warehouse::all();
        return response()->json($warehouse);
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
            'warehouse_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $warehouse = new Warehouse;
        $warehouse->warehouse_name = $request->input('warehouse_name');
        $warehouse->status = 1;
        $warehouse->save();
        return  response()->json($warehouse);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse = Warehouse::find($id);
        return  response()->json($warehouse);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit(Warehouse $warehouse)
    {
        $warehouse = Warehouse::find($warehouse->id);
        return response()->json($warehouse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse = Warehouse::find($warehouse->id);
        
        $warehouse->warehouse_name = $request->input('warehouse_name');
        $warehouse->status = $request->input('status');
        $warehouse->save();
      

        return response()->json($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse = Warehouse::find($warehouse->id);
        $warehouse->delete();
        return response()->json($warehouse);
    }
}
