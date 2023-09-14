<?php

namespace App\Http\Controllers;

use App\Models\ExpensesType;
use App\Models\ExpensesCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $expensesTypes = ExpensesType::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($expensesTypes);

          $data = DB::table('expenses_type as ep')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('ep.id', 'ep.expenses_name',  'ep.details', 'ep.expenses_category_id',
              'ec.expenses_category_name')    
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
            'expenses_category_id' => 'required'
        ]);

        // Create Post
        $expensesType = new ExpensesType;
        $expensesType->expenses_category_id = $request->input('expenses_category_id');
        $expensesType->expenses_name = $request->input('expenses_name');
        $expensesType->details = $request->input('details');       
        $expensesType->save();
        return  response()->json($expensesType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpensesType  $expensesType
     * @return \Illuminate\Http\Response
     */
    public function show(ExpensesType $expensesType)
    {
        $expensesType = ExpensesType::find($expensesType->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($expensesType);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpensesType  $expensesType
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpensesType $expensesType)
    {
        $expensesType = ExpensesType::find($expensesType->id);
        return response()->json($expensesType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpensesType  $expensesType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpensesType $expensesType)
    {
        $expensesType = ExpensesType::find($expensesType->id);
        $expensesType->expensesCateogryId = $request->input('expensesCateogryId');
        $expensesType->expenses_name = $request->input('expenses_name');
        $expensesType->details = $request->input('details');       
        $expensesType->save();
        return  response()->json($expensesType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpensesType  $expensesType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpensesType $expensesType)
    {
        $expensesType = ExpensesType::find($expensesType->id);
        $expensesType->delete();
        return response()->json($expensesType);
    }
}
