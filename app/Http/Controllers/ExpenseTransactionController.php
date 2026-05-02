<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTransaction;
use App\Http\Controllers\BalanceTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExpenseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

        public function fetchExpenseTransactionList($id)
    {
        $data = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ett.chart_of_account_id') // added
            ->join('users as u', 'u.id', '=', 'et.user_id')
            ->join('users as us', 'us.id', '=', 'et.approver_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'et.payment_type_po_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->select(
                'et.id',
                'et.amount',
                'et.details',
                'et.shop_id',
                'et.approval_status',
                'et.status',
                'et.is_received',
                'et.payment_type_po_id',
                'et.expense_date',

                'e.expense_name',
                'e.expense_code',
                'e.is_hidden',

                'ec.expense_category_name',
                'ec.expense_category_code',

                'ett.expense_type',
                'ett.expense_type_code',

                'coa.chart_of_account_name',
                'coa.chart_of_account_code',

                'u.name',
                'us.name as approver_name',

                'pt.payment_term',
                'b.bank_name',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.account_number'
            )
            ->orderBy('et.id', 'desc')
            ->limit(100)
            ->get();

        return response()->json($data);
    }

    public function searchAllExpenseTransactionList(Request $request)
    {
        $data = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ett.chart_of_account_id')
            ->join('users as u', 'u.id', '=', 'et.user_id')
            ->leftJoin('users as us', 'us.id', '=', 'et.approver_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'et.payment_type_po_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')

            ->select(
                'et.id',
                'et.amount',
                'et.details',
                'et.shop_id',
                'et.approval_status',
                'et.status',
                'et.is_received',
                'et.payment_type_po_id',
                'et.expense_date',
                'et.date_received',

                'e.id as expense_id',
                'e.expense_name',
                'e.expense_code',
                'e.is_hidden',

                'ec.id as expense_category_id',
                'ec.expense_category_name',
                'ec.expense_category_code',

                'ett.id as expense_type_id',
                'ett.expense_type',
                'ett.expense_type_code',

                'coa.id as chart_of_account_id',
                'coa.chart_of_account_name',
                'coa.chart_of_account_code',

                'u.name',
                'us.name as approver_name',

                'pt.payment_term',
                'b.bank_name',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.account_number'
            )

            ->when($request->filled('is_received'), function ($q) use ($request) {
                $q->where('et.is_received', $request->is_received);
            })

            ->when(
                $request->filled('approval_status') && $request->approval_status !== 'ALL',
                function ($q) use ($request) {
                    $q->where('et.approval_status', $request->approval_status);
                }
            )

            ->when($request->id != 0, fn($q) => $q->where('et.id', $request->id))

            ->when($request->chart_of_account_id != 0, fn($q) => $q->where('coa.id', $request->chart_of_account_id))

            ->when($request->expense_type_id != 0, fn($q) => $q->where('ett.id', $request->expense_type_id))

            ->when($request->expense_category_id != 0, fn($q) => $q->where('ec.id', $request->expense_category_id))

            ->when($request->expense_id != 0, fn($q) => $q->where('e.id', $request->expense_id))

            ->when($request->dateFrom && $request->dateTo, fn($q) =>
                $q->whereBetween('et.expense_date', [$request->dateFrom, $request->dateTo])
            )

            ->when($request->dateFrom && !$request->dateTo, fn($q) =>
                $q->whereDate('et.expense_date', '>=', $request->dateFrom)
            )

            ->when(!$request->dateFrom && $request->dateTo, fn($q) =>
                $q->whereDate('et.expense_date', '<=', $request->dateTo)
            )

            ->orderBy('et.id', 'desc')
            ->get();

        return response()->json($data);
    }


    public function searchExpenseTransactionList(Request $request)
    {
        $data = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ett.chart_of_account_id')
            ->join('users as u', 'u.id', '=', 'et.user_id')
            ->leftJoin('users as us', 'us.id', '=', 'et.approver_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'et.payment_type_po_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->where('et.is_received', 1)
            ->select(
                'et.id',
                'et.amount',
                'et.details',
                'et.shop_id',
                'et.approval_status',
                'et.status',
                'et.is_received',
                'et.payment_type_po_id',
                'et.expense_date',
                'et.date_received',

                'e.id as expense_id',
                'e.expense_name',
                'e.expense_code',
                'e.is_hidden',

                'ec.id as expense_category_id',
                'ec.expense_category_name',
                'ec.expense_category_code',

                'ett.id as expense_type_id',
                'ett.expense_type',
                'ett.expense_type_code',

                'coa.id as chart_of_account_id',
                'coa.chart_of_account_name',
                'coa.chart_of_account_code',

                'u.name',
                'us.name as approver_name',

                'pt.payment_term',
                'b.bank_name',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.account_number'
            )
            ->when($request->approval_status != '', function ($q) use ($request) {
                $q->where('et.approval_status', $request->approval_status);
            })
            ->when($request->id != 0, function ($q) use ($request) {
                $q->where('et.id', $request->id);
            })
            ->when($request->chart_of_account_id != 0, function ($q) use ($request) {
                $q->where('coa.id', $request->chart_of_account_id);
            })
            ->when($request->expense_type_id != 0, function ($q) use ($request) {
                $q->where('ett.id', $request->expense_type_id);
            })
            ->when($request->expense_category_id != 0, function ($q) use ($request) {
                $q->where('ec.id', $request->expense_category_id);
            })
            ->when($request->expense_id != 0, function ($q) use ($request) {
                $q->where('e.id', $request->expense_id);
            })
            ->when($request->dateFrom && $request->dateTo, function ($q) use ($request) {
                $q->whereBetween('et.expense_date', [$request->dateFrom, $request->dateTo]);
            })
            ->when($request->dateFrom && !$request->dateTo, function ($q) use ($request) {
                $q->whereDate('et.expense_date', '>=', $request->dateFrom);
            })
            ->when(!$request->dateFrom && $request->dateTo, function ($q) use ($request) {
                $q->whereDate('et.expense_date', '<=', $request->dateTo);
            })
            ->orderBy('et.id', 'desc')
            ->get();

        return response()->json($data);
    }

    public function getTotalExpense(Request $request)
    {
           $total_balance = DB::table('expenses_transaction as e')
            ->select(DB::raw('SUM(e.amount) as total_expense')) 
            ->when($request->filled('approval_status'), function ($q) use ($request) {
            $q->where('e.approval_status', $request->approval_status);
            })
            ->where('e.expense_date', '>=', $request->input('dateFrom'))
            ->where('e.expense_date', '<=', $request->input('dateTo'))
            ->where('e.is_received', 1)
            
            ->first();;
         return response()->json($total_balance);
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
        $expenseTransaction = new ExpenseTransaction;
        $expenseTransaction->expense_id = $request->input('expense_id');    
        $expenseTransaction->user_id = $request->input('user_id'); 
        $expenseTransaction->approver_id = $request->input('approver_id'); 
        $expenseTransaction->approval_status = $request->input('approval_status'); 
        $expenseTransaction->payment_type_po_id = $request->input('payment_type_po_id');     
        $expenseTransaction->amount = $request->input('amount');   
        $expenseTransaction->details = $request->input('details');   
        $expenseTransaction->expense_date = $request->input('expense_date');   
        $expenseTransaction->shop_id = $request->input('shop_id');    
        $expenseTransaction->status = $request->input('status');  
        $expenseTransaction->is_received = $request->input('is_received');   
        
        if ($request->input('is_received') == 1) {
            $expenseTransaction->date_received = now('GMT+8');
        }
        
        $expenseTransaction->save();

        // $response = Http::post('http://127.0.0.1:8000/api/balanceTransaction/store', [
        //     'payment_type_po_id' => $expenseTransaction->payment_type_po_id,
        //     'shop_id'            => $request->input('shop_id'),
        //     'join_id'             => $expenseTransaction->id, // usually reference to expense
        //     'name'               => 'Expense Transaction',
        //     'type'               => 'Expense', // or CREDIT depending on logic
        //     'total_balance'      => 0, // optional / compute if needed
        //     'amount'             => $expenseTransaction->amount,
        //     'status'             => $expenseTransaction->status,
        // ]);
        
        if ($request->input('payment_type_po_id') != 0 && $request->input('is_received') == 1 ) {
          $request->merge([
            'payment_type_po_id' => $expenseTransaction->payment_type_po_id,
            'shop_id'            => $request->input('shop_id'),
            'join_id'             => $expenseTransaction->id,
            'name'               => $request->input('name'),
            'balanceTransaction' => $request->input('balanceTransaction'),
            'balance_type_id'    => $request->input('balance_type_id'),
            'total_balance'      => 0,
            'amount'             => $expenseTransaction->amount,
            'status'             => $expenseTransaction->status,
         ]);

            $balanceTransactionController = new BalanceTransactionController();
            $balanceTransactionController->store($request);

        }

         $response = [
              'data' => $expenseTransaction,
              'code' => 200,
              'message' => "Successfully Added"
          ];
            return response()->json($response);      

        
    }

       public function fetchExpenseTransactionById($id)
    {
        $data = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->join('users as u', 'u.id', '=', 'et.user_id')
            ->join('users as us', 'us.id', '=', 'et.approver_id')
            ->leftJoin('payment_type_po as ptp', 'ptp.id', '=', 'et.payment_type_po_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->select('et.id', 'et.amount', 'et.details', 'et.shop_id', 'et.approval_status', 'et.approver_id', 'et.status', 'et.is_received', 'et.payment_type_po_id', 'et.expense_date', 'e.expense_name', 'e.is_hidden', 'ec.expense_category_name', 'ett.expense_type',
            'u.name as requestor_name', 'us.name as approver_name', 'pt.payment_term', 'b.bank_name', 'ptp.account_name', 'ptp.account_description', 'ptp.account_number')      
            ->where('et.id', $id)   
            ->first(); 
          return  response()->json($data);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpenseTransaction  $expenseTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseTransaction $expenseTransaction)
    {
        $expenseTransaction = ExpenseTransaction::find($expenseTransaction->id);
        
        //return view('categories.show')->with('category', $category);
        return  response()->json($expenseTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpenseTransaction  $expenseTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseTransaction $expenseTransaction)
    {
        $expenseTransaction = ExpenseTransaction::find($expenseTransaction->id);
        
        //return view('categories.show')->with('category', $category);
        return  response()->json($expenseTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpenseTransaction  $expenseTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpenseTransaction $expenseTransaction)
    {
        $expenseTransaction = ExpenseTransaction::find($expenseTransaction->id);
        


        $expenseTransaction->approver_id = $request->input('approver_id'); 
        $expenseTransaction->approval_status = $request->input('approval_status'); 
        $expenseTransaction->payment_type_po_id = $request->input('payment_type_po_id');     
        $expenseTransaction->amount = $request->input('amount');   
        $expenseTransaction->details = $request->input('details');     
        $expenseTransaction->is_received = $request->input('is_received');   

        if ($request->input('is_received') == 1) {
            $expenseTransaction->date_received = now('GMT+8');
        }
        
        $expenseTransaction->save();

        // $response = Http::post('http://127.0.0.1:8000/api/balanceTransaction/store', [
        //     'payment_type_po_id' => $expenseTransaction->payment_type_po_id,
        //     'shop_id'            => $request->input('shop_id'),
        //     'join_id'             => $expenseTransaction->id, // usually reference to expense
        //     'name'               => 'Expense Transaction',
        //     'type'               => 'Expense', // or CREDIT depending on logic
        //     'total_balance'      => 0, // optional / compute if needed
        //     'amount'             => $expenseTransaction->amount,
        //     'status'             => $expenseTransaction->status,
        // ]);
        
        if ($request->input('payment_type_po_id') != 0 && $request->input('is_received') == 1 ) {
          $request->merge([ 
            'payment_type_po_id' => $expenseTransaction->payment_type_po_id,
            'shop_id'            => $request->input('shop_id'),
            'join_id'             => $expenseTransaction->id,
            'name'               => $request->input('name'),
            'balanceTransaction' => $request->input('balanceTransaction'),
            'balance_type_id'    => $request->input('balance_type_id'),
            'transaction'        => $request->input('expense_name'),
            'total_balance'      => 0,
            'amount'             => $expenseTransaction->amount,
            'status'             => $expenseTransaction->status,
         ]);

            $balanceTransactionController = new BalanceTransactionController();
            $balanceTransactionController->store($request);
        }

         $response = [
              'data' => $expenseTransaction,
              'code' => 200,
              'message' => "Successfully Updated"
          ];
            return response()->json($response);    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpenseTransaction  $expenseTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseTransaction $expenseTransaction)
    {
        //
    }
}
