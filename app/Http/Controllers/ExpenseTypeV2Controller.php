<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTypeV2;
use Illuminate\Http\Request;

class ExpenseTypeV2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expensesTypes = ExpenseTypeV2::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($expensesTypes);
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
            'expense_type' => 'required'
        ]);

        // Create Post
        $expensesTypeV2 = new ExpenseTypeV2;
        $expensesTypeV2->expense_type = $request->input('expense_type');    
        $expensesTypeV2->save();

        $response = [
              'data' => $expensesTypeV2,
              'code' => 200,
              'message' => "Successfully Added"
          ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpenseTypeV2  $expenseTypeV2
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseTypeV2 $expenseTypeV2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpenseTypeV2  $expenseTypeV2
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseTypeV2 $expenseTypeV2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpenseTypeV2  $expenseTypeV2
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpenseTypeV2 $expenseTypeV2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpenseTypeV2  $expenseTypeV2
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseTypeV2 $expenseTypeV2)
    {
        //
    }
}
