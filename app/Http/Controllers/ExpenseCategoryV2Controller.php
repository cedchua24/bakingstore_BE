<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategoryV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryV2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('expenses_category_v2 as ec')
            ->join('expenses_type_v2 as et', 'et.id', '=', 'ec.expense_type_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'et.chart_of_account_id')
            ->select(
                'coa.id',
                'coa.chart_of_account_name',
                'coa.chart_of_account_code',

                'et.id as expense_type_id',
                'et.expense_type',
                'et.expense_type_code',

                'ec.id as expense_category_id',
                'ec.expense_category_name',
                'ec.expense_category_code'
            )
            ->orderBy('coa.chart_of_account_code')
            ->orderBy('et.expense_type_code')
            ->orderBy('ec.expense_category_code')
            ->get();

    return response()->json($data);  
    }
    public function fetchExpenseCategoryById($id)
    {
        if ($id == 0) {
            $data = DB::table('expenses_type_v2 as ep')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'ep.chart_of_account_id')
                ->join('expenses_category_v2 as ec', 'ep.id', '=', 'ec.expense_type_id')
                ->select(
                    'ec.id',
                    'ec.expense_category_name',
                    'ec.expense_category_code',

                    'ep.id as expense_type_id',
                    'ep.expense_type',
                    'ep.expense_type_code',

                    'coa.id as chart_of_account_id',
                    'coa.chart_of_account_name',
                    'coa.chart_of_account_code'
                )
                ->orderBy('coa.chart_of_account_code', 'asc')
                ->orderBy('ep.expense_type_code', 'asc')
                ->orderBy('ec.expense_category_code', 'asc')
                ->get();
        } else {
            $data = DB::table('expenses_type_v2 as ep')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'ep.chart_of_account_id')
                ->join('expenses_category_v2 as ec', 'ep.id', '=', 'ec.expense_type_id')
                ->select(
                    'ec.id',
                    'ec.expense_category_name',
                    'ec.expense_category_code',

                    'ep.id as expense_type_id',
                    'ep.expense_type',
                    'ep.expense_type_code',

                    'coa.id as chart_of_account_id',
                    'coa.chart_of_account_name',
                    'coa.chart_of_account_code'
                )
                ->where('ep.id', $id)
                ->orderBy('coa.chart_of_account_code', 'asc')
                ->orderBy('ep.expense_type_code', 'asc')
                ->orderBy('ec.expense_category_code', 'asc')
                ->get();
        }

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
        $expensesCategoryV2 = new ExpenseCategoryV2;
        $expensesCategoryV2->expense_type_id = $request->input('expense_type_id');    
        $expensesCategoryV2->expense_category_code = $request->input('expense_category_code');    
        $expensesCategoryV2->expense_category_name = $request->input('expense_category_name');    
        $expensesCategoryV2->status = $request->input('status');    
        $expensesCategoryV2->save();

        $response = [
              'data' => $expensesCategoryV2,
              'code' => 200,
              'message' => "Successfully Added"
          ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpenseCategoryV2  $expenseCategoryV2
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseCategoryV2 $expenseCategoryV2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpenseCategoryV2  $expenseCategoryV2
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseCategoryV2 $expenseCategoryV2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpenseCategoryV2  $expenseCategoryV2
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpenseCategoryV2 $expenseCategoryV2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpenseCategoryV2  $expenseCategoryV2
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseCategoryV2 $expenseCategoryV2)
    {
        //
    }
}
