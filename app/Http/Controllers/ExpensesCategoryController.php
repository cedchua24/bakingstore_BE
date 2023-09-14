<?php

namespace App\Http\Controllers;

use App\Models\ExpensesCategory;
use Illuminate\Http\Request;

class ExpensesCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $expensesCategorys = ExpensesCategory::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($expensesCategorys);
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
            'expenses_category_name' => 'required'
        ]);

        // Create Post
        $expensesCategory = new ExpensesCategory;
        $expensesCategory->expenses_category_name = $request->input('expenses_category_name');
        $expensesCategory->save();
        return  response()->json($expensesCategory);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpensesCategory  $expensesCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ExpensesCategory $expensesCategory)
    {
        $expensesCategory = ExpensesCategory::find($expensesCategory->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($expensesCategory);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpensesCategory  $expensesCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpensesCategory $expensesCategory)
    {
        $expensesCategory = ExpensesCategory::find($expensesCategory->id);
        return response()->json($expensesCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpensesCategory  $expensesCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpensesCategory $expensesCategory)
    {
        $expensesCategory = ExpensesCategory::find($expensesCategory->id);
        $expensesCategory->expenses_category_name = $request->input('expenses_category_name');
        $expensesCategory->save();
        return  response()->json($expensesCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpensesCategory  $expensesCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpensesCategory $expensesCategory)
    {
        $expensesCategory = ExpensesCategory::find($expensesCategory->id);
        $expensesCategory->delete();
        return response()->json($expensesCategory);
    }
}
