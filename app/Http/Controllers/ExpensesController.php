<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $expenses = Expenses::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($expenses);

        $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name')    
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
            'expenses_type_id' => 'required'
        ]);

        // Create Post
        $expenses = new Expenses;
        $expenses->expenses_type_id = $request->input('expenses_type_id');
        $expenses->details = $request->input('details');
        $expenses->amount = $request->input('amount');    
        $expenses->date = $request->input('date');   
        $expenses->save();
        return  response()->json($expenses);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function show(Expenses $expenses)
    {
        $expenses = Expenses::find($expenses->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($expenses);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function edit(Expenses $expenses)
    {
        $expenses = Expenses::find($expenses->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($expenses);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expenses $expenses)
    {
        $expenses = Expenses::find($expenses->id);
        $expenses->expenses_type_id = $request->input('expenses_type_id');
        $expenses->details = $request->input('details');
        $expenses->amount = $request->input('amount');    
        $expenses->date = $request->input('date');   
        $expenses->save();
        return  response()->json($expenses);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expenses $expenses)
    {
        $expenses = Expenses::find($expenses->id);
        $expenses->delete();
        return response()->json($expenses);
    }
}
