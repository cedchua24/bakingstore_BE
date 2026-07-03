<?php

namespace App\Http\Controllers;

use App\Models\VipProduct;
use Illuminate\Http\Request;

class VipProductController extends Controller
{
    public function index()
    {
        return response()->json(VipProduct::all());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'vip_product_name' => 'required',
        ]);

        $vipProduct = new VipProduct;
        $vipProduct->vip_product_name = $request->input('vip_product_name');
        $vipProduct->details = $request->input('details');
        $vipProduct->vip_color = $request->input('vip_color');
        $vipProduct->status = $request->input('status');
        $vipProduct->save();

        return response()->json($vipProduct);
    }

    public function show(VipProduct $vipProduct)
    {
        return response()->json($vipProduct);
    }

    public function edit(VipProduct $vipProduct)
    {
        return response()->json($vipProduct);
    }

    public function update(Request $request, VipProduct $vipProduct)
    {
        $vipProduct->vip_product_name = $request->input('vip_product_name');
        $vipProduct->details = $request->input('details');
        $vipProduct->vip_color = $request->input('vip_color');
        $vipProduct->status = $request->input('status');
        $vipProduct->save();

        return response()->json($vipProduct);
    }

    public function destroy(VipProduct $vipProduct)
    {
        $vipProduct->delete();

        return response()->json($vipProduct);
    }
}
