<?php

namespace App\Http\Controllers;

use App\Models\ShopType;
use Illuminate\Http\Request;

class ShopTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shopType = ShopType::all();
        return response()->json($shopType);
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
            'shop_type_description' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shopType = new ShopType;
        $shopType->shop_type_description = $request->input('shop_type_description');
        $shopType->save();
        return  response()->json($shopType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShopType  $shopType
     * @return \Illuminate\Http\Response
     */
    public function show(ShopType $shopType)
    {
        $shopType = ShopType::find($shopType->id);
        return  response()->json($shopType);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShopType  $shopType
     * @return \Illuminate\Http\Response
     */
    public function edit(ShopType $shopType)
    {
        $shopType = ShopType::find($shopType->id);
        return  response()->json($shopType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShopType  $shopType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopType $shopType)
    {
        $shopType = ShopType::find($shopType->id);
        
        $shopType->shop_type_description = $request->input('shop_type_description');
        $shopType->save();

        return  response()->json($shopType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShopType  $shopType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShopType $shopType)
    {
        $shopType = ShopType::find($shopType->id);
        $shopType->delete();
        return response()->json($shopType);
    }
}
