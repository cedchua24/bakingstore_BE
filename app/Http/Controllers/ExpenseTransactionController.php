<?php

namespace App\Http\Controllers;

use App\Models\ExpenseTransaction;
use App\Http\Controllers\BalanceTransactionController;
use Carbon\Carbon;
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

    public function fetchExpenseTransactionListV2(Request $request, $date)
    {
        $request->merge(['date' => $date]);

        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

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
            ->whereDate('et.expense_date', $validated['date'])
            ->orderBy('et.id', 'desc')
            ->limit(20)
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

    /**
     * Search expense transactions using optional multi-select filters.
     *
     * An omitted or empty filter array means "all" for that filter.
     */
    public function searchExpenseTransactionListV2(Request $request)
    {
        $filters = $request->validate([
            'ids' => 'sometimes|array',
            'ids.*' => 'integer|min:1',
            'chart_of_account_ids' => 'sometimes|array',
            'chart_of_account_ids.*' => 'integer|min:1',
            'expense_type_ids' => 'sometimes|array',
            'expense_type_ids.*' => 'integer|min:1',
            'expense_category_ids' => 'sometimes|array',
            'expense_category_ids.*' => 'integer|min:1',
            'expense_ids' => 'sometimes|array',
            'expense_ids.*' => 'integer|min:1',
            'approval_statuses' => 'sometimes|array',
            'approval_statuses.*' => 'string',
            'is_received' => 'sometimes|array',
            'is_received.*' => 'boolean',
            'dateFrom' => 'sometimes|nullable|date',
            'dateTo' => 'sometimes|nullable|date',
        ]);

        if (!empty($filters['dateFrom']) && !empty($filters['dateTo'])) {
            $request->validate([
                'dateTo' => 'after_or_equal:dateFrom',
            ]);
        }

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
            ->when(!empty($filters['ids']), fn ($query) => $query->whereIn('et.id', $filters['ids']))
            ->when(!empty($filters['chart_of_account_ids']), fn ($query) => $query->whereIn('coa.id', $filters['chart_of_account_ids']))
            ->when(!empty($filters['expense_type_ids']), fn ($query) => $query->whereIn('ett.id', $filters['expense_type_ids']))
            ->when(!empty($filters['expense_category_ids']), fn ($query) => $query->whereIn('ec.id', $filters['expense_category_ids']))
            ->when(!empty($filters['expense_ids']), fn ($query) => $query->whereIn('e.id', $filters['expense_ids']))
            ->when(!empty($filters['approval_statuses']), fn ($query) => $query->whereIn('et.approval_status', $filters['approval_statuses']))
            ->when(!empty($filters['is_received']), fn ($query) => $query->whereIn('et.is_received', $filters['is_received']))
            ->when(!empty($filters['dateFrom']), fn ($query) => $query->whereDate('et.expense_date', '>=', $filters['dateFrom']))
            ->when(!empty($filters['dateTo']), fn ($query) => $query->whereDate('et.expense_date', '<=', $filters['dateTo']))
            ->orderBy('et.id', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Compare the current calendar month's expenses with the previous month and
     * the average of the previous three complete calendar months.
     */
    public function getMonthlyExpenseComparisonV2(Request $request)
    {
        $filters = $request->validate([
            'month' => 'sometimes|date_format:Y-m',
            'ids' => 'sometimes|array',
            'ids.*' => 'integer|min:1',
            'chart_of_account_ids' => 'sometimes|array',
            'chart_of_account_ids.*' => 'integer|min:1',
            'expense_type_ids' => 'sometimes|array',
            'expense_type_ids.*' => 'integer|min:1',
            'expense_category_ids' => 'sometimes|array',
            'expense_category_ids.*' => 'integer|min:1',
            'expense_ids' => 'sometimes|array',
            'expense_ids.*' => 'integer|min:1',
            'approval_statuses' => 'sometimes|array',
            'approval_statuses.*' => 'string',
            'is_received' => 'sometimes|array',
            'is_received.*' => 'boolean',
        ]);

        $currentMonth = !empty($filters['month'])
            ? Carbon::createFromFormat('Y-m-d', $filters['month'].'-01', 'Asia/Singapore')->startOfMonth()
            : Carbon::now('Asia/Singapore')->startOfMonth();
        $months = collect(range(0, 3))->map(function ($monthsAgo) use ($currentMonth) {
            $month = $currentMonth->copy()->subMonthsNoOverflow($monthsAgo);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'date_from' => $month->copy()->startOfMonth()->toDateString(),
                'date_to' => $month->copy()->endOfMonth()->toDateString(),
            ];
        });

        $query = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'ett.chart_of_account_id')
            ->select(
                'e.id as expense_id',
                'e.expense_name',
                'e.expense_code',
                'e.is_hidden',
                'ec.id as expense_category_id',
                'ec.expense_category_name',
                'ett.id as expense_type_id',
                'ett.expense_type',
                'coa.id as chart_of_account_id',
                'coa.chart_of_account_name'
            );

        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $query->selectRaw(
                "SUM(CASE WHEN et.expense_date BETWEEN ? AND ? THEN et.amount ELSE 0 END) as month_{$monthNumber}_amount",
                [$month['date_from'], $month['date_to']]
            );
        }

        $expenses = $query
            ->whereBetween('et.expense_date', [
                $months->last()['date_from'],
                $months->first()['date_to'],
            ])
            ->when(!empty($filters['ids']), fn ($q) => $q->whereIn('et.id', $filters['ids']))
            ->when(!empty($filters['chart_of_account_ids']), fn ($q) => $q->whereIn('coa.id', $filters['chart_of_account_ids']))
            ->when(!empty($filters['expense_type_ids']), fn ($q) => $q->whereIn('ett.id', $filters['expense_type_ids']))
            ->when(!empty($filters['expense_category_ids']), fn ($q) => $q->whereIn('ec.id', $filters['expense_category_ids']))
            ->when(!empty($filters['expense_ids']), fn ($q) => $q->whereIn('e.id', $filters['expense_ids']))
            ->when(!empty($filters['approval_statuses']), fn ($q) => $q->whereIn('et.approval_status', $filters['approval_statuses']))
            ->when(!empty($filters['is_received']), fn ($q) => $q->whereIn('et.is_received', $filters['is_received']))
            ->groupBy(
                'e.id',
                'e.expense_name',
                'e.expense_code',
                'e.is_hidden',
                'ec.id',
                'ec.expense_category_name',
                'ett.id',
                'ett.expense_type',
                'coa.id',
                'coa.chart_of_account_name'
            )
            ->get()
            ->map(function ($expense) use ($months) {
                $history = $months->map(function ($month, $index) use ($expense) {
                    return array_merge($month, [
                        'amount' => round((float) $expense->{'month_'.($index + 1).'_amount'}, 2),
                    ]);
                })->values();

                $currentAmount = $history[0]['amount'];
                $previousAmount = $history[1]['amount'];
                $previousThreeMonthAverage = round((float) $history->slice(1)->avg('amount'), 2);
                $differenceFromPrevious = round($currentAmount - $previousAmount, 2);
                $differenceFromAverage = round($currentAmount - $previousThreeMonthAverage, 2);
                $isNew = $currentAmount > 0 && $history->slice(1)->every(fn ($month) => $month['amount'] == 0);
                $isMissing = $currentAmount == 0
                    && $history->slice(1)->contains(fn ($month) => $month['amount'] > 0);
                $isUnusual = !$isNew
                    && $currentAmount > $previousAmount
                    && $previousThreeMonthAverage > 0
                    && $currentAmount >= ($previousThreeMonthAverage * 1.5);

                if ($isNew) {
                    $status = 'NEW';
                } elseif ($isMissing) {
                    $status = 'MISSING';
                } elseif ($isUnusual) {
                    $status = 'UNUSUAL';
                } elseif ($differenceFromPrevious > 0) {
                    $status = 'INCREASED';
                } elseif ($differenceFromPrevious < 0) {
                    $status = 'DECREASED';
                } else {
                    $status = 'UNCHANGED';
                }

                return [
                    'expense_id' => (int) $expense->expense_id,
                    'expense_name' => $expense->expense_name,
                    'expense_code' => $expense->expense_code,
                    'is_hidden' => (int) $expense->is_hidden,
                    'expense_category_id' => (int) $expense->expense_category_id,
                    'expense_category_name' => $expense->expense_category_name,
                    'expense_type_id' => (int) $expense->expense_type_id,
                    'expense_type' => $expense->expense_type,
                    'chart_of_account_id' => (int) $expense->chart_of_account_id,
                    'chart_of_account_name' => $expense->chart_of_account_name,
                    'status' => $status,
                    'is_new' => $isNew,
                    'is_missing' => $isMissing,
                    'is_unusual' => $isUnusual,
                    'is_increased' => $status === 'INCREASED',
                    'is_decreased' => $status === 'DECREASED',
                    'current_month_amount' => $currentAmount,
                    'previous_month_amount' => $previousAmount,
                    'previous_three_month_average' => $previousThreeMonthAverage,
                    'difference_from_previous_month' => $differenceFromPrevious,
                    'change_from_previous_month_percentage' => $previousAmount > 0
                        ? round(($differenceFromPrevious / $previousAmount) * 100, 2)
                        : null,
                    'difference_from_three_month_average' => $differenceFromAverage,
                    'change_from_three_month_average_percentage' => $previousThreeMonthAverage > 0
                        ? round(($differenceFromAverage / $previousThreeMonthAverage) * 100, 2)
                        : null,
                    'monthly_history' => $history,
                ];
            })
            ->sortByDesc('current_month_amount')
            ->values();

        $currentTotal = round($expenses->sum('current_month_amount'), 2);
        $previousTotal = round($expenses->sum('previous_month_amount'), 2);
        $averageTotal = round($expenses->sum('previous_three_month_average'), 2);

        return response()->json([
            'report_month' => $months->first(),
            'previous_month' => $months[1],
            'average_months' => $months->slice(1)->values(),
            'unusual_threshold_percentage' => 50,
            'filters' => $filters,
            'summary' => [
                'current_month_total' => $currentTotal,
                'previous_month_total' => $previousTotal,
                'previous_three_month_average_total' => $averageTotal,
                'difference_from_previous_month' => round($currentTotal - $previousTotal, 2),
                'difference_from_three_month_average' => round($currentTotal - $averageTotal, 2),
                'unusual_expense_count' => $expenses->where('is_unusual', true)->count(),
                'increased_expense_count' => $expenses->where('is_increased', true)->count(),
                'decreased_expense_count' => $expenses->where('is_decreased', true)->count(),
                'new_expense_count' => $expenses->where('is_new', true)->count(),
                'missing_expense_count' => $expenses->where('is_missing', true)->count(),
            ],
            'data' => $expenses,
            'unusual_expenses' => $expenses->where('is_unusual', true)->values(),
            'increased_expenses' => $expenses->where('is_increased', true)->values(),
            'decreased_expenses' => $expenses->where('is_decreased', true)->values(),
            'new_expenses' => $expenses->where('is_new', true)->values(),
            'missing_expenses' => $expenses->where('is_missing', true)->values(),
            'code' => 200,
            'message' => 'Monthly expense comparison fetched successfully.',
        ]);
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

    public function getTotalExpenseWithFilters(Request $request)
    {
        $request->validate([
            'is_profit' => 'sometimes|nullable|boolean',
            'expense_transaction_ids' => 'required|array|min:1',
            'expense_transaction_ids.*' => 'integer',
        ]);

        $totalExpense = DB::table('expenses_transaction as et')
            ->join('expenses_v2 as e', 'e.id', '=', 'et.expense_id')
            ->join('expenses_category_v2 as ec', 'ec.id', '=', 'e.expense_category_id')
            ->join('expenses_type_v2 as ett', 'ett.id', '=', 'ec.expense_type_id')
            ->select(DB::raw('SUM(et.amount) as total_expense'))
            ->when($request->filled('approval_status'), function ($query) use ($request) {
                $query->where('et.approval_status', $request->input('approval_status'));
            })
            ->when($request->filled('dateFrom'), function ($query) use ($request) {
                $query->where('et.expense_date', '>=', $request->input('dateFrom'));
            })
            ->when($request->filled('dateTo'), function ($query) use ($request) {
                $query->where('et.expense_date', '<=', $request->input('dateTo'));
            })
            ->when($request->has('is_profit') && $request->input('is_profit') !== null, function ($query) use ($request) {
                $query->where('ett.is_profit', $request->boolean('is_profit'));
            })
            ->whereIn('ett.id', $request->input('expense_transaction_ids'))
            ->where('et.is_received', 1)
            ->first();

        return response()->json($totalExpense);
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
