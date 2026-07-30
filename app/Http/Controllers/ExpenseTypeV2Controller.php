<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTypeV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseTypeV2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('expenses_type_v2 as et')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'et.chart_of_account_id')
            ->select(
                'et.id as id',
                'et.expense_type',
                'et.expense_type_code',
                'et.is_profit',
                'coa.id as chart_of_account_id',
                'coa.chart_of_account_name',
                'coa.chart_of_account_code'
            )
            ->where('et.status', 0)
            ->where('coa.status', 0)
            ->orderBy('coa.chart_of_account_code')
            ->orderBy('et.expense_type_code')
            ->get();

        return response()->json($data);
    }

        public function fetchExpenseTypeById($id)
    {
          $data = DB::table('expenses_type_v2 as et')
            ->join('expenses_category_v2 as ec', 'et.id', '=', 'ec.expense_type_id')
            ->select('ec.id', 'et.expense_type', 'et.chart_of_account_id', 'et.expense_type_code', 'et.id as expense_type_id', 'et.status', 'ec.expense_category_name')    
            ->where('et.id', $id)   
            ->get();


          $response = [
              'data' => $data,
              'name' => $data[0]->expense_type,
              'code' => 200,
              'message' => "Successfully Added"
          ];

        return response()->json($response);  
    }

       public function fetchTypeByChart($id)
    {
        $data = DB::table('expenses_type_v2 as et')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'et.chart_of_account_id')
            ->select(
                'et.id as id',
                'et.expense_type',
                'et.expense_type_code',
                'coa.id as chart_of_account_id',
                'coa.chart_of_account_name',
                'coa.chart_of_account_code'
            )
            ->where('coa.id', $id)
            ->orderBy('coa.chart_of_account_code')
            ->orderBy('et.expense_type_code')
            ->get();

        return response()->json($data);
    }


        public function fetchExpenseTypeCategoryById($id, $id2)
    {
          $data = DB::table('expenses_type_v2 as et')
            ->join('expenses_category_v2 as ec', 'et.id', '=', 'ec.expense_type_id')
            ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
            ->select('e.id', 'e.expense_name', 'e.is_hidden', 'et.expense_type', 'et.chart_of_account_id', 'et.expense_type_code', 'et.id as expense_type_id', 'et.status', 'ec.expense_category_name')    
            ->where('et.id', $id)   
            ->where('ec.id', $id2) 
            ->get();


          $response = [
              'data' => $data,
              'name' => $data[0]->expense_type,
              'expense_category_name' => $data[0]->expense_category_name,
              'code' => 200,
              'message' => "Successfully Added"
          ];

        return response()->json($response);  
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
        $expensesTypeV2->chart_of_account_id = $request->input('chart_of_account_id'); 
        $expensesTypeV2->expense_type_code = $request->input('expense_type_code'); 
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
