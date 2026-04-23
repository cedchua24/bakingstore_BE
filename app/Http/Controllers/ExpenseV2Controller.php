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
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ep.chart_of_account_id')
            ->join('expenses_category_v2 as ec', 'ep.id', '=', 'ec.expense_type_id')
            ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
            ->select(
                'e.id',
                'e.expense_name',
                'e.is_hidden',
                'e.details',
                'e.status',

                // ✅ ADD THESE
                'coa.chart_of_account_code',
                'ep.expense_type_code',
                'ec.expense_category_code',
                'e.expense_code',

                // (optional names)
                'coa.chart_of_account_name',
                'ep.expense_type',
                'ec.expense_category_name'
            )
            ->orderBy('coa.chart_of_account_code')
            ->orderBy('ep.expense_type_code')
            ->orderBy('ec.expense_category_code')
            ->orderBy('e.expense_code')
            ->get();

        return response()->json($data);
    }
    public function fetchExpenseV2ById($id)
    {
        if ($id == 0) {
            $data = DB::table('expenses_category_v2 as ec')
                ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
                ->join('expenses_type_v2 as ep', 'ep.id', '=', 'ec.expense_type_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'ep.chart_of_account_id')
                ->select(
                    'e.id',
                    'e.expense_name',
                    'e.expense_code',
                    'e.is_hidden',
                    'e.details',
                    'e.status',

                    'ec.id as expense_category_id',
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
                ->orderBy('e.expense_code', 'asc')
                ->get();

            return response()->json($data);
        } else {
            $data = DB::table('expenses_category_v2 as ec')
                ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
                ->join('expenses_type_v2 as ep', 'ep.id', '=', 'ec.expense_type_id')
                ->join('chart_of_accounts as coa', 'coa.id', '=', 'ep.chart_of_account_id')
                ->select(
                    'e.id',
                    'e.expense_name',
                    'e.expense_code',
                    'e.is_hidden',
                    'e.details',
                    'e.status',

                    'ec.id as expense_category_id',
                    'ec.expense_category_name',
                    'ec.expense_category_code',

                    'ep.id as expense_type_id',
                    'ep.expense_type',
                    'ep.expense_type_code',

                    'coa.id as chart_of_account_id',
                    'coa.chart_of_account_name',
                    'coa.chart_of_account_code'
                )
                ->where('ec.id', $id)
                ->orderBy('coa.chart_of_account_code', 'asc')
                ->orderBy('ep.expense_type_code', 'asc')
                ->orderBy('ec.expense_category_code', 'asc')
                ->orderBy('e.expense_code', 'asc')
                ->get();

            return response()->json($data);
}

    }

    public function fetchExpenseByTypeaAndCategory(Request $request)
    {
        $request->validate([
            'expense_type_id' => 'required',
            'expense_category_id' => 'nullable',
        ]);

        $data = DB::table('expenses_category_v2 as ec')
            ->join('expenses_v2 as e', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ep', 'ep.id', '=', 'ec.expense_type_id')
            ->select(
                'e.id',
                'e.expense_name', 'e.is_hidden',
                'e.details',
                'e.status',
                'ec.expense_category_name',
                'ep.expense_type'
            )
            ->where('ep.id', $request->expense_type_id)
            ->when($request->expense_category_id, function ($query) use ($request) {
                $query->where('ec.id', $request->expense_category_id);
            })
            ->orderBy('ec.id', 'asc')
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
        $expensesV2->expense_code = $request->input('expense_code');   
        $expensesV2->expense_name = $request->input('expense_name'); 
        $expensesV2->details = $request->input('details');     
        $expensesV2->status = $request->input('status');
        $expensesV2->is_hidden = $request->input('is_hidden');       
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
