<?php

namespace App\Http\Controllers;

use App\Models\ExpenseV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseV2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
          $data = DB::table('expenses_type_v2 as ep')
            ->join('expenses_category_v2 as ec', 'ep.id', '=', 'ec.expense_type_id')
            ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
            ->select('e.id', 'e.expense_name', 'e.details', 'e.status', 'ec.expense_category_name',  'ep.expense_type' )    
            ->get();
            return response()->json($data);  
    }

    public function fetchExpenseV2ById($id)
    {
          $data = DB::table('expenses_category_v2 as ec')
            ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
            ->select('e.id', 'e.expense_name', 'e.details', 'e.status', 'ec.expense_category_name')    
            ->where('ec.id', $id)   
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
            'expense_type_id' => 'required'
        ]);

        // Create Post
        $expensesV2 = new ExpenseV2;
        $expensesV2->expense_category_id = $request->input('expense_category_id');    
        $expensesV2->expense_name = $request->input('expense_name'); 
        $expensesV2->details = $request->input('details');     
        $expensesV2->status = $request->input('status');    
        $expensesV2->save();

        $response = [
              'data' => $expensesV2,
              'code' => 200,
              'message' => "Successfully Added"
          ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpenseV2  $expenseV2
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseV2 $expenseV2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpenseV2  $expenseV2
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseV2 $expenseV2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpenseV2  $expenseV2
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpenseV2 $expenseV2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpenseV2  $expenseV2
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseV2 $expenseV2)
    {
        //
    }
}
