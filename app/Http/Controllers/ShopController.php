<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = Shop::all();
        return response()->json($shop);
    }

    public function fetchShopList()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
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
            'shop_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shop = new Shop;
        $shop->shop_name = $request->input('shop_name');
        $shop->shop_type_id = $request->input('shop_type_id');;
        $shop->save();
        return  response()->json($shop);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        $shop = Shop::find($shop->id);
        return  response()->json($shop);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit(Shop $shop)
    {
        $shop = Shop::find($shop->id);
        return response()->json($shop);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shop $shop)
    {
        $shop = Shop::find($warehouse->id);
        
        $shop->shop_name = $request->input('shop_name');
        $shop->shop_type_id = $request->input('shop_type_id');;
        $shop->save();
      
        return response()->json($shop);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        $shop = Shop::find($shop->id);
        $shop->delete();
        return response()->json($shop);
    }
}
