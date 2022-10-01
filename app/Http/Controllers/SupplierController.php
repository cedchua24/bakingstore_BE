<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($suppliers);
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
            'supplier_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $supplier = new Supplier;
        $supplier->supplier_name = $request->input('supplier_name');
        $supplier->address = $request->input('address');
        $supplier->contact_number = $request->input('contact_number');
        $supplier->email = $request->input('email');
        $supplier->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($supplier);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $suppliers = Supplier::find($supplier->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($suppliers);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $suppliers = Supplier::find($supplier->id);
        return response()->json($suppliers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $suppliers = Supplier::find($supplier->id);
        
        $suppliers->supplier_name = $request->input('supplier_name');
        $suppliers->address = $request->input('address');
        $suppliers->contact_number = $request->input('contact_number');
        $suppliers->email = $request->input('email');
        $suppliers->save();
      

        return response()->json($suppliers);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Supplier $supplier)
    {
        $suppliers = Supplier::find($supplier->id);
        $suppliers->delete();
        return response()->json($suppliers);
    }
}
