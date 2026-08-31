<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ReducedStock;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use App\Models\ShopOrderTransaction;
use App\Models\Customer;
use App\Http\Controllers\ModeOfPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;




class ShopOrderTransactionController extends Controller
{
    private $usePaymentTypePo = false;

    private function paymentTypeSource()
    {
        if (!$this->usePaymentTypePo) {
            return DB::table('payment_type')
                ->select('id', 'payment_type', 'payment_type_description', 'status', 'type');
        }

        return DB::table('payment_type_po as payment_account')
            ->leftJoin('bank as payment_bank', 'payment_bank.id', '=', 'payment_account.bank_id')
            ->leftJoin('payment_term as payment_term', 'payment_term.id', '=', 'payment_account.payment_term_id')
            ->select(
                'payment_account.id',
                'payment_account.account_name as payment_type',
                'payment_account.account_description as payment_type_description',
                'payment_account.status',
                'payment_account.is_supplier',
                'payment_account.is_customer',
                DB::raw('CASE WHEN payment_account.payment_term_id = 1 THEN 1 ELSE 2 END as type'),
                'payment_account.payment_term_id',
                'payment_account.bank_id',
                'payment_account.account_number',
                'payment_account.account_name',
                'payment_account.account_description',
                'payment_account.due_date',
                'payment_account.buffer_days',
                'payment_account.credit_limit',
                'payment_account.statement_date',
                'payment_account.total_balance_due',
                'payment_account.balance',
                'payment_account.created_at as payment_type_po_created_at',
                'payment_account.updated_at as payment_type_po_updated_at',
                'payment_bank.bank_name',
                'payment_bank.status as bank_status',
                'payment_bank.created_at as bank_created_at',
                'payment_bank.updated_at as bank_updated_at',
                'payment_term.payment_term',
                'payment_term.status as payment_term_status',
                'payment_term.created_at as payment_term_created_at',
                'payment_term.updated_at as payment_term_updated_at'
            );
    }

    private function usePaymentTypePo()
    {
        $this->usePaymentTypePo = true;
    }

    private function modeOfPaymentSelect($includePaidStatus = false)
    {
        $select = [
            'mop.id',
            'mop.payment_type_id',
            'pt.payment_type',
            'mop.amount',
            'mop.shop_order_transaction_id',
        ];

        if ($includePaidStatus) {
            $select[] = 'mop.is_paid';
        }

        if (!$this->usePaymentTypePo) {
            return $select;
        }

        return [
            'mop.id',
            'mop.payment_type_id',
            'mop.amount',
            'mop.is_paid',
            'mop.shop_order_transaction_id',
            'mop.created_at',
            'mop.updated_at',
            'pt.id as payment_type_po_id',
            'pt.payment_term_id',
            'pt.bank_id',
            'pt.account_number',
            'pt.account_name',
            'pt.account_description',
            'pt.due_date',
            'pt.buffer_days',
            'pt.credit_limit',
            'pt.statement_date',
            'pt.total_balance_due',
            'pt.balance as payment_type_po_balance',
            'pt.status as payment_type_po_status',
            'pt.is_supplier',
            'pt.is_customer',
            'pt.payment_type_po_created_at',
            'pt.payment_type_po_updated_at',
            'pt.bank_name',
            'pt.bank_status',
            'pt.bank_created_at',
            'pt.bank_updated_at',
            'pt.payment_term',
            'pt.payment_term_status',
            'pt.payment_term_created_at',
            'pt.payment_term_updated_at',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function index()
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'shop_order_transaction.id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
            ->where('shop.shop_type_id', '!=', 3)    
            // ->where('shop_order_transaction.date', date('Y-m-d'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();
         

            $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->where('shop.shop_type_id', '!=', 3)
            ->where('shop_order_transaction.status', 1)
            // ->where('shop_order_transaction.date', date('Y-m-d'))
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];


        
            return response()->json($response);   
    }


        public function fetchBranchOrder() // branch
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
            ->where('shop_order_transaction.type', '=', 1)    
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();
         

            $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->where('shop_order_transaction.type', '=', 1)    
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', date('Y-m-d'))
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];
 
            return response()->json($response);   
    }

       public function fetchShopOrderTransactionListByDate($date) // branch
    {
        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('users as r', 'r.id', '=', 'sot.requestor')
            ->join('users as c', 'c.id', '=', 'sot.checker')
            ->leftJoin('sales_rep as sr', 'sr.id', '=', 'sot.sales_rep_id')
            ->select('sot.id', 'sot.shop_order_transaction_total_quantity',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at', 's.shop_name', 's.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'sot.checker', 'sot.requestor',
              'sot.status',  'sot.date', 'sot.profit', 'sr.first_name as sr_name')    
             ->where('s.shop_type_id', '!=', 3)
             ->where('sot.date', $date)
             ->where('sot.type', '=', 1)
             ->orderBy('sot.id', 'DESC')
             ->get();

           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Added"
          ];
             
            return response()->json($response);    
    }

       public function fetchSortedProduct($id)
    {
        $currentTime = Carbon::now('GMT+8');
        $param1 = '';
        $param2 = '';

        switch ($id) {
        case "0":
            $param1 = 'total_quantity';
            $param2 = 'DESC';
            break;
        case "1":
            $param1 = 'total_quantity';
            $param2 = 'DESC';
            break;
        case "2":
            $param1 = 'total_quantity';
            $param2 = 'ASC';
            break;
        case "3":
            $param1 = 'total_price';
            $param2 = 'DESC';
            break; 
        case "4":
            $param1 = 'total_price';
            $param2 = 'ASC';
            break;          
        default:
            $param1 = 'total_quantity';
            $param2 = 'DESC';
        }

           $data = DB::table('products as p')
            ->select('mup.id as mark_up_product_id', 'p.id', 'mup.business_type', 'p.product_name' ,DB::raw('SUM(so.shop_order_quantity) as total_quantity'), DB::raw('SUM(so.shop_order_total_price) as total_price'), DB::raw('SUM(so.shop_order_profit) as total_profit') )  
            ->join('shop_order as so', 'so.product_id', '=', 'p.id')  
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->where('sot.date', date('Y-m-d'))
            ->where('sot.status', 1)
            ->groupBy('mup.id') 
            ->orderBy($param1, $param2)
            ->get();

           $response = [
              'data' => $data,
              'code' => 200,
              'date' => date('Y-m-d'),
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

      public function fetchProductSoldToday(Request $request) 
    {
        $today = $request->input('today');
            $data = DB::table('products as p')
                ->select(
                    'p.id',
                    'p.product_name',
                    'p.quantity',
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN mup.business_type = 'WHOLESALE' 
                                    THEN so.shop_order_quantity * p.quantity 
                                ELSE 
                                    so.shop_order_quantity 
                            END
                        ) as total_quantity
                    "),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN sot.created_at != sot.date && sot.is_pickup = 0
                                    THEN CASE
                                            WHEN mup.business_type = 'WHOLESALE' 
                                                THEN so.shop_order_quantity * p.quantity
                                            ELSE
                                                so.shop_order_quantity
                                        END
                                ELSE 0
                            END
                        ) as discrepancy
                    "),
                    DB::raw("
                        CASE 
                            WHEN SUM(CASE WHEN mup.business_type = 'WHOLESALE' THEN 1 ELSE 0 END) > 0 
                                THEN p.stock
                            ELSE 
                                p.stock_pc
                        END as stock
                    "),
                    DB::raw("
                        CASE 
                            WHEN p.quantity = 1 
                                THEN p.stock 
                            ELSE 
                                p.stock_pc 
                        END as stock_all
                    ")
                    )
                ->join('shop_order as so', 'so.product_id', '=', 'p.id')
                ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
                ->where('sot.date', $today)
                ->groupBy('p.id', 'p.product_name', 'p.stock', 'p.stock_pc', 'p.quantity')
                ->get();

           $response = [
              'data' => $data,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }


      public function fetchSortedCustomer($id)
    {
        $currentTime = Carbon::now('GMT+8');
        $param1 = '';
        $param2 = '';

        switch ($id) {
        case "0":
            $param1 = 'total_price';
            $param2 = 'DESC';
            break;
        case "1":
            $param1 = 'total_price';
            $param2 = 'DESC';
            break; 
        case "2":
            $param1 = 'total_price';
            $param2 = 'ASC';
            break;          
        default:
            $param1 = 'total_price';
            $param2 = 'DESC';
        }

           $data = DB::table('customer as c')
            ->select('c.id',  DB::raw("
                                        CONCAT(
                                            c.first_name, ' ', c.last_name,
                                            IFNULL(CONCAT(' (', NULLIF(c.store_name, ''), ')'), '')
                                        ) AS first_name
                                    "),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price'), DB::raw('SUM(sot.profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->where('sot.date', date('Y-m-d'))
            ->where('sot.status', 1)
            ->groupBy('c.id')
            ->orderBy($param1, $param2)
            ->get();

           $response = [
              'data' => $data,
              'code' => 200,
              'date' => date('Y-m-d'),
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

      public function fetchCustomerDetails($id)
    {

           $data = DB::table('customer as c')
            ->select('c.id as customer_id', 'sot.id', 'c.first_name', 'c.last_name', 
            'sot.is_pickup', 'sot.date', 'sot.preparer_id', 'sot.checker_id', 'sot.dispatcher_id',
              DB::raw("IFNULL(c.address, '') as address"), 
              DB::raw("IFNULL(c.contact_number, '') as contact_number"), 
              DB::raw("IFNULL(c.store_name, '') as store_name"), 
              )  
             
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')   
            ->where('sot.id', $id)
            ->first();

            return response()->json($data);   
    }




    public function fetchSortedCustomerReport(Request $request)
    {
        $currentTime = Carbon::now('GMT+8');
        $param1 = '';
        $param2 = '';
        $limit = 1000;

        $id = $request->input('status');

        switch ($id) {
        case "0":
            $param1 = 'total_price';
            $param2 = 'DESC';
            $limit = 1000;
            break;
        case "1":
            $param1 = 'total_price';
            $param2 = 'DESC';
            break; 
        case "2":
            $param1 = 'total_price';
            $param2 = 'ASC';
            break;          
        default:
            $param1 = 'total_price';
            $param2 = 'DESC';
            $limit = 1000;
        }
        if ($id === 0) {
         $data = DB::table('customer as c')
            ->select('c.id', DB::raw("
                                        CONCAT(
                                            c.first_name, ' ', c.last_name,
                                            IFNULL(CONCAT(' (', NULLIF(c.store_name, ''), ')'), '')
                                        ) AS first_name
                                    "),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price') , DB::raw('SUM(sot.profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->where('sot.status', 1)
            ->where('sot.type', 0)
            ->groupBy('c.id')
            ->orderBy($param1, $param2)
            ->get();


        } else {
         $data = DB::table('customer as c')
            ->select('c.id', DB::raw("
                                        CONCAT(
                                            c.first_name, ' ', c.last_name,
                                            IFNULL(CONCAT(' (', NULLIF(c.store_name, ''), ')'), '')
                                        ) AS first_name
                                    "),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price') , DB::raw('SUM(sot.profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->where('sot.status', 1)
            ->where('sot.date', '>=', $request->input('dateFrom'))
            ->where('sot.date', '<=', $request->input('dateTo'))
            ->where('sot.type', 0)
            ->groupBy('c.id')
            ->orderBy($param1, $param2)
            ->limit($request->input('limit'))
            ->get();
        }



           $response = [
              'data' => $data,
              'code' => 200,
              'date' => date('Y-m-d'),
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchMonthlyCustomerSalesComparison(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'limit' => 'nullable|integer|min:1|max:5000',
            'sort' => 'nullable|in:current_sales,current_profit,previous_sales,biggest_increase,biggest_drop,rank_drop,biggest_rank_drop',
            'direction' => 'nullable|in:asc,desc,ASC,DESC',
        ]);

        $reportMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $months = collect([0, 1, 2, 3])->map(function ($monthsAgo) use ($reportMonth) {
            $month = $reportMonth->copy()->subMonths($monthsAgo);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'date_from' => $month->copy()->startOfMonth()->toDateString(),
                'date_to' => $month->copy()->endOfMonth()->toDateString(),
            ];
        });

        $monthCases = $months->map(function ($month, $index) {
            $number = $index + 1;
            $from = $month['date_from'];
            $to = $month['date_to'];

            return [
                "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN sot.shop_order_transaction_total_price ELSE 0 END) as month_{$number}_sales",
                "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN sot.profit ELSE 0 END) as month_{$number}_profit",
                "COUNT(DISTINCT CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN sot.id END) as month_{$number}_orders",
            ];
        })->flatten()->map(function ($expression) {
            return DB::raw($expression);
        })->all();

        $query = DB::table('customer as c')
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')
            ->select(array_merge([
                'c.id as customer_id',
                'c.first_name',
                'c.last_name',
                'c.store_name',
            ], $monthCases))
            ->where('sot.status', 1)
            ->where('sot.type', 0)
            ->whereBetween('sot.date', [
                $months->last()['date_from'],
                $months->first()['date_to'],
            ])
            ->groupBy('c.id', 'c.first_name', 'c.last_name', 'c.store_name');

        $customers = $query->get()->map(function ($customer) use ($months) {
            $history = $months->map(function ($month, $index) use ($customer) {
                $number = $index + 1;

                return array_merge($month, [
                    'sales_amount' => round((float) $customer->{"month_{$number}_sales"}, 2),
                    'profit_amount' => round((float) $customer->{"month_{$number}_profit"}, 2),
                    'order_count' => (int) $customer->{"month_{$number}_orders"},
                ]);
            })->values();

            $current = $history[0];
            $lastMonth = $history[1];
            $previousMonths = $history->slice(1);
            $averageSales = round((float) $previousMonths->avg('sales_amount'), 2);
            $averageProfit = round((float) $previousMonths->avg('profit_amount'), 2);
            $averageOrders = round((float) $previousMonths->avg('order_count'), 2);
            $salesImpact = round($current['sales_amount'] - $averageSales, 2);
            $profitImpact = round($current['profit_amount'] - $averageProfit, 2);
            $lastMonthSalesImpact = round($current['sales_amount'] - $lastMonth['sales_amount'], 2);
            $lastMonthProfitImpact = round($current['profit_amount'] - $lastMonth['profit_amount'], 2);

            if ($current['sales_amount'] == 0 && $averageSales > 0) {
                $status = 'MISSING';
            } elseif ($averageSales == 0 && $current['sales_amount'] > 0) {
                $status = 'NEW_OR_RETURNING';
            } elseif ($salesImpact > 0) {
                $status = 'ABOVE_USUAL';
            } elseif ($salesImpact < 0) {
                $status = 'BELOW_USUAL';
            } else {
                $status = 'UNCHANGED';
            }

            $customerName = trim($customer->first_name.' '.$customer->last_name);

            return [
                'customer_id' => (int) $customer->customer_id,
                'customer_name' => $customerName,
                'store_name' => $customer->store_name,
                'display_name' => $customer->store_name
                    ? $customerName.' ('.$customer->store_name.')'
                    : $customerName,
                'status' => $status,
                'current_month' => $current,
                'last_month' => $lastMonth,
                'comparison_history' => $history,
                'previous_three_month_average' => [
                    'sales_amount' => $averageSales,
                    'profit_amount' => $averageProfit,
                    'order_count' => $averageOrders,
                ],
                'vs_last_month' => [
                    'sales_impact' => $lastMonthSalesImpact,
                    'profit_impact' => $lastMonthProfitImpact,
                    'sales_change_percentage' => $lastMonth['sales_amount'] > 0
                        ? round(($lastMonthSalesImpact / $lastMonth['sales_amount']) * 100, 2)
                        : null,
                    'profit_change_percentage' => $lastMonth['profit_amount'] > 0
                        ? round(($lastMonthProfitImpact / $lastMonth['profit_amount']) * 100, 2)
                        : null,
                ],
                'vs_previous_three_month_average' => [
                    'sales_impact' => $salesImpact,
                    'profit_impact' => $profitImpact,
                    'sales_change_percentage' => $averageSales > 0
                        ? round(($salesImpact / $averageSales) * 100, 2)
                        : null,
                    'profit_change_percentage' => $averageProfit > 0
                        ? round(($profitImpact / $averageProfit) * 100, 2)
                        : null,
                ],
                'sales_impact' => $salesImpact,
                'sales_increase' => round(max(0, $salesImpact), 2),
                'sales_drop' => round(max(0, -$salesImpact), 2),
                'profit_impact' => $profitImpact,
                'sales_change_percentage' => $averageSales > 0
                    ? round(($salesImpact / $averageSales) * 100, 2)
                    : null,
            ];
        });

        $currentRanks = $customers->sortByDesc('current_month.sales_amount')->values()
            ->pluck('customer_id')->flip();
        $previousRanks = $customers->sortByDesc('last_month.sales_amount')->values()
            ->pluck('customer_id')->flip();

        $customers = $customers->map(function ($customer) use ($currentRanks, $previousRanks) {
            $customer['current_rank'] = $currentRanks[$customer['customer_id']] + 1;
            $customer['previous_rank'] = $previousRanks[$customer['customer_id']] + 1;
            $customer['rank_change'] = $customer['previous_rank'] - $customer['current_rank'];
            $customer['rank_drop'] = max(0, $customer['current_rank'] - $customer['previous_rank']);
            $customer['rank'] = $customer['current_rank'];
            $customer['last_month_rank'] = $customer['previous_rank'];
            $customer['rank_movement'] = $customer['rank_change'];
            $customer['rank_movement_direction'] = $customer['rank_change'] > 0
                ? 'UP'
                : ($customer['rank_change'] < 0 ? 'DOWN' : 'UNCHANGED');

            return $customer;
        });

        $limit = (int) ($validated['limit'] ?? 10);
        $sort = $validated['sort'] ?? 'current_sales';
        $direction = strtolower($validated['direction'] ?? 'desc');
        $sortFields = [
            'current_sales' => 'current_month.sales_amount',
            'current_profit' => 'current_month.profit_amount',
            'previous_sales' => 'last_month.sales_amount',
            'biggest_increase' => 'sales_increase',
            'biggest_drop' => 'sales_drop',
            'rank_drop' => 'rank_drop',
            'biggest_rank_drop' => 'rank_drop',
        ];
        $sortableCustomers = $sort === 'biggest_rank_drop'
            ? $customers->where('status', '!=', 'MISSING')->where('rank_drop', '>', 0)
            : $customers;
        $sortedCustomers = $direction === 'asc'
            ? $sortableCustomers->sortBy($sortFields[$sort])
            : $sortableCustomers->sortByDesc($sortFields[$sort]);

        $positiveImpact = $customers
            ->filter(function ($customer) {
                return $customer['sales_impact'] > 0;
            })
            ->sortByDesc('sales_impact')
            ->take($limit)
            ->values();
        $decliningCustomers = $customers
            ->filter(function ($customer) {
                return $customer['sales_impact'] < 0;
            })
            ->sortBy('sales_impact')
            ->take($limit)
            ->values();
        $missingCustomers = $customers
            ->where('status', 'MISSING')
            ->sortBy('sales_impact')
            ->take($limit)
            ->values();
        $biggestRankDropCustomers = $customers
            ->where('status', '!=', 'MISSING')
            ->where('rank_drop', '>', 0)
            ->sortByDesc('rank_drop')
            ->take($limit)
            ->values();

        return response()->json([
            'report_month' => $months->first(),
            'comparison_months' => $months->slice(1)->values(),
            'impact_benchmark' => 'PREVIOUS_THREE_MONTH_AVERAGE',
            'filters' => [
                'limit' => $limit,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'summary' => [
                'customer_count' => $customers->count(),
                'positive_impact_count' => $customers->where('sales_impact', '>', 0)->count(),
                'declining_customer_count' => $customers->where('sales_impact', '<', 0)->count(),
                'missing_customer_count' => $customers->where('status', 'MISSING')->count(),
                'current_month_sales' => round($customers->sum('current_month.sales_amount'), 2),
                'last_month_sales' => round($customers->sum('last_month.sales_amount'), 2),
                'net_sales_impact' => round($customers->sum('sales_impact'), 2),
                'current_month_profit' => round($customers->sum('current_month.profit_amount'), 2),
                'net_profit_impact' => round($customers->sum('profit_impact'), 2),
            ],
            'data' => $sortedCustomers->take($limit)->values(),
            'top_customers' => $customers->sortByDesc('current_month.sales_amount')->take($limit)->values(),
            'positive_impact_customers' => $positiveImpact,
            'declining_customers' => $decliningCustomers,
            'missing_customers' => $missingCustomers,
            'biggest_rank_drop_customers' => $biggestRankDropCustomers,
            'code' => 200,
            'message' => 'Monthly customer sales comparison fetched successfully.',
        ]);
    }

        public function fetchSalesByCategory(Request $request)
        {
            $currentTime = Carbon::now('GMT+8');
            
            $id = $request->input('status');
            $type = $request->input('type');

            // Map status to sort column & direction
            $sortOptions = [
                "0" => ['total_quantity', 'DESC'],
                "1" => ['total_quantity', 'DESC'],
                "2" => ['total_quantity', 'ASC'],
                "3" => ['total_price', 'DESC'],
                "4" => ['total_price', 'ASC'],
            ];

            [$param1, $param2] = $sortOptions[$request->input('status')] ?? ['total_quantity', 'DESC'];

            // Base query
            $query = DB::table('products as p')
                ->select(
                    'mup.id as mark_up_product_id',
                    'p.id',
                     DB::raw("
                            CASE 
                                WHEN '{$type}' = 'ALL' THEN 'ALL'
                                ELSE mup.business_type
                            END AS business_type
                    "),
                    'p.product_name',
                    'p.stock',
                    'p.stock_pc',
                    'p.packaging',
                    'p.quantity',
                    DB::raw("
                            SUM(CASE 
                                WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity
                                ELSE so.shop_order_quantity
                            END) AS total_quantity
                          "),
                    DB::raw('SUM(so.shop_order_total_price) as total_price'),
                    DB::raw('SUM(so.shop_order_profit) as total_profit')
                )
                ->join('shop_order as so', 'so.product_id', '=', 'p.id')
                ->join('category as c', 'c.id', '=', 'p.category_id')
                ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
                ->where('sot.status', 1)
                ->where('sot.type', 0)
                ->groupBy('p.id')
                ->orderBy($param1, $param2);

            $limit = $request->input('limit', 1000);

            // Filters depending on request
            if ($id === 0) {
                // No extra filters
            } else {
                $query->whereBetween('sot.date', [$request->input('dateFrom'), $request->input('dateTo')])
                    ->where('c.id', $request->input('categoryId'));

                if (in_array($type, ['WHOLESALE', 'RETAIL'])) {
                    $query->where('mup.business_type', $type);
                }
            }

            $data = $query->limit($limit)->get();

            return response()->json([
                'data' => $data,
                'code' => 200,
                'date' => date('Y-m-d'),
                'id' => $id,
                'message' => 'Successfully Added'
            ]);
        }


            public function fetchSortedProductReport(Request $request)
            {
                $currentTime = Carbon::now('GMT+8');
                $id = $request->input('status');
                $type = $request->input('type');
                $supplier_id = $request->input('supplier_id');
                $limit = $request->input('limit', 5000); // default 1000 if not set

                // Determine sorting parameters
                $sortOptions = [
                    '0' => ['total_quantity', 'DESC'],
                    '1' => ['total_quantity', 'DESC'],
                    '2' => ['total_quantity', 'ASC'],
                    '3' => ['total_price', 'DESC'],
                    '4' => ['total_price', 'ASC'],
                ];

                [$param1, $param2] = $sortOptions[$id] ?? ['total_quantity', 'DESC'];

                // Base query
                $query = DB::table('products as p')
                    ->select(
                        'mup.id as mark_up_product_id',
                        'p.id',
                        // 'mup.business_type',
                        DB::raw("
                            CASE 
                                WHEN '{$type}' = 'ALL' THEN 'ALL'
                                ELSE mup.business_type
                            END AS business_type
                        "),
                        'p.product_name',
                        'p.stock',
                        'p.stock_pc',
                        'p.packaging',
                         'p.quantity',                       
                        DB::raw("
                                SUM(
                                    CASE WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity
                                        WHEN mup.business_type = 'RETAIL' THEN so.shop_order_quantity
                                        ELSE 0
                                    END
                                ) AS total_quantity
                        "),  
                        DB::raw('SUM(so.shop_order_total_price) as total_price'),
                        DB::raw('SUM(so.shop_order_profit) as total_profit')
                    )
                    ->join('shop_order as so', 'so.product_id', '=', 'p.id')
                    ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                    ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
                    ->where('sot.status', 1)
                    ->where('sot.type', 0)
                    ->groupBy('p.id')
                    ->orderBy($param1, $param2);

                if ($supplier_id) {
                    $query->leftJoin('product_supplier as ps', 'ps.product_id', '=', 'p.id')
                        ->leftJoin('supplier as s', 's.id', '=', 'ps.supplier_id')
                        ->where('ps.supplier_id', $supplier_id);
                }

                // Apply conditional filters
                if ($id === 0) {
                    // No date filter for id = 0
                } else {
                    $query->whereBetween('sot.date', [$request->input('dateFrom'), $request->input('dateTo')]);
                    if ($type !== "All") {
                        $query->where('mup.business_type', $type);
                    }
                    $query->limit($limit);
                }

                $data = $query->get();

                return response()->json([
                    'data' => $data,
                    'code' => 200,
                    'date' => date('Y-m-d'),
                    'id' => $id,
                    'message' => 'Successfully Added',
                ]);
            }

            public function fetchMonthlyProductSalesComparison(Request $request)
            {
                $validated = $request->validate([
                    'month' => 'required|date_format:Y-m',
                    'limit' => 'nullable|integer|min:1|max:5000',
                    'product_group' => 'nullable|string|max:50',
                    'impact_group' => 'nullable|string|max:50',
                    // `direction` is accepted as a temporary frontend alias for
                    // the product group; it no longer controls asc/desc sorting.
                    'direction' => 'nullable|string|max:50',
                    'type' => 'nullable|in:ALL,All,all,WHOLESALE,Wholesale,wholesale,RETAIL,Retail,retail',
                    'supplier_id' => 'nullable|integer|min:1',
                    'category_id' => 'nullable|integer|min:1',
                ]);

                $reportMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
                $months = collect([0, 1, 2, 3])->map(function ($monthsAgo) use ($reportMonth) {
                    $month = $reportMonth->copy()->subMonths($monthsAgo);

                    return [
                        'month' => $month->format('Y-m'),
                        'label' => $month->format('F Y'),
                        'date_from' => $month->copy()->startOfMonth()->toDateString(),
                        'date_to' => $month->copy()->endOfMonth()->toDateString(),
                    ];
                });

                $monthCases = $months->map(function ($month, $index) {
                    $number = $index + 1;
                    $from = $month['date_from'];
                    $to = $month['date_to'];

                    return [
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN so.shop_order_total_price ELSE 0 END) as month_{$number}_sales",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN so.shop_order_profit ELSE 0 END) as month_{$number}_profit",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN CASE WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity ELSE so.shop_order_quantity END ELSE 0 END) as month_{$number}_quantity",
                    ];
                })->flatten()->map(function ($expression) {
                    return DB::raw($expression);
                })->all();

                $query = DB::table('products as p')
                    ->join('shop_order as so', 'so.product_id', '=', 'p.id')
                    ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                    ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
                    ->select(array_merge([
                        'p.id as product_id',
                        'p.product_name',
                        'p.stock',
                        'p.stock_pc',
                        'p.packaging',
                        'p.variation',
                        'p.quantity as pieces_per_package',
                        'p.created_at as product_created_at',
                    ], $monthCases))
                    ->where('p.disabled', 0)
                    ->where('sot.status', 1)
                    ->where('sot.type', 0)
                    ->whereBetween('sot.date', [
                        $months->last()['date_from'],
                        $months->first()['date_to'],
                    ])
                    ->groupBy('p.id', 'p.product_name', 'p.stock', 'p.stock_pc', 'p.packaging', 'p.variation', 'p.quantity', 'p.created_at');

                if (!empty($validated['type']) && strtoupper($validated['type']) !== 'ALL') {
                    $query->where('mup.business_type', strtoupper($validated['type']));
                }

                if (!empty($validated['supplier_id'])) {
                    $supplierId = $validated['supplier_id'];
                    $query->whereExists(function ($supplierQuery) use ($supplierId) {
                        $supplierQuery->select(DB::raw(1))
                            ->from('product_supplier as ps')
                            ->whereColumn('ps.product_id', 'p.id')
                            ->where('ps.supplier_id', $supplierId);
                    });
                }

                if (!empty($validated['category_id'])) {
                    $query->where('p.category_id', $validated['category_id']);
                }

                $products = $query->get()->map(function ($product) use ($months, $reportMonth) {
                    $history = $months->map(function ($month, $index) use ($product) {
                        $number = $index + 1;

                        return array_merge($month, [
                            'sales_amount' => round((float) $product->{"month_{$number}_sales"}, 2),
                            'profit_amount' => round((float) $product->{"month_{$number}_profit"}, 2),
                            'quantity_sold' => (int) $product->{"month_{$number}_quantity"},
                        ]);
                    })->values();

                    $current = $history[0];
                    $previous = $history[1];
                    $salesGap = round($current['sales_amount'] - $previous['sales_amount'], 2);
                    $previousThree = $history->slice(1, 3);
                    $averageSales = round((float) $previousThree->avg('sales_amount'), 2);
                    $averageQuantity = round((float) $previousThree->avg('quantity_sold'), 2);
                    $salesImpact = round($current['sales_amount'] - $averageSales, 2);
                    $isNewProduct = !empty($product->product_created_at)
                        && Carbon::parse($product->product_created_at)->format('Y-m') === $reportMonth->format('Y-m');

                    if ($isNewProduct) {
                        $impactStatus = 'NEW_PRODUCT';
                    } elseif ($current['sales_amount'] == 0.0 && $averageSales > 0) {
                        $impactStatus = 'MISSING';
                    } elseif ($current['sales_amount'] > $averageSales) {
                        $impactStatus = 'WINNING';
                    } elseif ($current['sales_amount'] < $averageSales) {
                        $impactStatus = 'DECLINING';
                    } else {
                        $impactStatus = 'UNCHANGED';
                    }

                    return [
                        'product_id' => (int) $product->product_id,
                        'product_name' => $product->product_name,
                        'current_stock' => (int) $product->stock,
                        'current_stock_pc' => (int) $product->stock_pc,
                        'packaging' => $product->packaging,
                        'quantity' => (int) $product->pieces_per_package,
                        'variation' => $product->variation,
                        'pieces_per_package' => (int) $product->pieces_per_package,
                        'product_created_at' => $product->product_created_at,
                        'is_new_product' => $isNewProduct,
                        'impact_status' => $impactStatus,
                        'current_month' => $current,
                        'previous_month' => $previous,
                        'three_month_comparison' => $history,
                        'sales_change' => $salesGap,
                        'previous_three_month_average_sales' => $averageSales,
                        'previous_three_month_average_quantity' => $averageQuantity,
                        'sales_impact' => $salesImpact,
                        'sales_drop' => round(max(0, -$salesGap), 2),
                        'sales_change_percentage' => $previous['sales_amount'] > 0
                            ? round(($salesGap / $previous['sales_amount']) * 100, 2)
                            : null,
                        'quantity_change' => $current['quantity_sold'] - $previous['quantity_sold'],
                        'trend' => $salesGap > 0 ? 'HIGHER' : ($salesGap < 0 ? 'LOWER' : 'UNCHANGED'),
                    ];
                });

                $currentRanks = $products->sortByDesc('current_month.sales_amount')->values()
                    ->pluck('product_id')->flip();
                $previousRanks = $products->sortByDesc('previous_month.sales_amount')->values()
                    ->pluck('product_id')->flip();

                $products = $products->map(function ($product) use ($currentRanks, $previousRanks) {
                    $product['current_rank'] = $currentRanks[$product['product_id']] + 1;
                    $product['previous_rank'] = $previousRanks[$product['product_id']] + 1;
                    $product['rank_change'] = $product['previous_rank'] - $product['current_rank'];
                    $product['rank_drop'] = max(0, $product['current_rank'] - $product['previous_rank']);
                    $product['rank'] = $product['current_rank'];
                    $product['last_month_rank'] = $product['previous_rank'];
                    $product['rank_movement'] = $product['rank_change'];
                    return $product;
                });

                $limit = (int) ($validated['limit'] ?? 10);
                $productGroup = strtolower(trim((string) (
                    $validated['product_group']
                    ?? $validated['impact_group']
                    ?? $validated['direction']
                    ?? 'all'
                )));
                $productGroup = str_replace([' ', '-'], '_', $productGroup);
                $productGroup = [
                    'all_results' => 'all',
                    'winning_products' => 'winning',
                    'highest' => 'highest_sales',
                    'highest_sales_products' => 'highest_sales',
                    'new' => 'new_product',
                    'new_products' => 'new_product',
                    'lowest' => 'lowest_sales',
                    'lowest_sales_products' => 'lowest_sales',
                    'declining_products' => 'declining',
                    'losing' => 'declining',
                    'missing_products' => 'missing',
                    // Old direction values now simply mean the default group.
                    'asc' => 'all',
                    'desc' => 'all',
                ][$productGroup] ?? $productGroup;

                if (!in_array($productGroup, ['all', 'winning', 'highest_sales', 'new_product', 'lowest_sales', 'declining', 'missing'], true)) {
                    return response()->json([
                        'message' => 'The selected product group is invalid.',
                        'errors' => [
                            'product_group' => ['Use all, winning, highest_sales, new_product, lowest_sales, declining, or missing.'],
                        ],
                    ], 422);
                }

                $filteredProducts = $products;
                if ($productGroup === 'winning') {
                    $filteredProducts = $filteredProducts->where('impact_status', 'WINNING');
                } elseif ($productGroup === 'new_product') {
                    $filteredProducts = $filteredProducts->where('impact_status', 'NEW_PRODUCT');
                } elseif ($productGroup === 'lowest_sales') {
                    $filteredProducts = $filteredProducts->where('impact_status', '!=', 'MISSING');
                } elseif ($productGroup === 'declining') {
                    $filteredProducts = $filteredProducts->where('impact_status', 'DECLINING');
                } elseif ($productGroup === 'missing') {
                    $filteredProducts = $filteredProducts->where('impact_status', 'MISSING');
                }

                if ($productGroup === 'lowest_sales') {
                    $filteredProducts = $filteredProducts->sortBy('current_month.sales_amount');
                } elseif ($productGroup === 'declining') {
                    $filteredProducts = $filteredProducts->sortBy('sales_impact');
                } elseif ($productGroup === 'missing') {
                    $filteredProducts = $filteredProducts->sortByDesc('previous_three_month_average_sales');
                } elseif ($productGroup === 'winning') {
                    $filteredProducts = $filteredProducts->sortByDesc('sales_impact');
                } else {
                    $filteredProducts = $filteredProducts->sortByDesc('current_month.sales_amount');
                }
                $filteredProducts = $filteredProducts->values();
                $filteredTotal = $filteredProducts->count();

                $decliningProducts = $products
                    ->filter(function ($product) {
                        return $product['impact_status'] === 'DECLINING';
                    })
                    ->sort(function ($left, $right) {
                        return [$right['sales_drop'], $left['previous_rank']]
                            <=> [$left['sales_drop'], $right['previous_rank']];
                    })
                    ->take($limit)
                    ->values();

                return response()->json([
                    'report_month' => $months->first(),
                    'comparison_months' => $months->slice(1)->values(),
                    'filters' => [
                        'limit' => $limit,
                        'product_group' => $productGroup,
                        'type' => strtoupper($validated['type'] ?? 'ALL'),
                        'supplier_id' => $validated['supplier_id'] ?? null,
                        'category_id' => $validated['category_id'] ?? null,
                    ],
                    'total_products' => $products->count(),
                    'filtered_total' => $filteredTotal,
                    'product_group_counts' => [
                        'all' => $products->count(),
                        'winning' => $products->where('impact_status', 'WINNING')->count(),
                        'highest_sales' => $products->count(),
                        'new_product' => $products->where('impact_status', 'NEW_PRODUCT')->count(),
                        'lowest_sales' => $products->where('impact_status', '!=', 'MISSING')->count(),
                        'declining' => $products->where('impact_status', 'DECLINING')->count(),
                        'missing' => $products->where('impact_status', 'MISSING')->count(),
                    ],
                    'data' => $filteredProducts->take($limit)->values(),
                    'top_products' => $products->sortByDesc('current_month.sales_amount')->take($limit)->values(),
                    'declining_products' => $decliningProducts,
                    'code' => 200,
                    'message' => 'Monthly product sales comparison fetched successfully.',
                ]);
            }

            public function fetchMonthlyProductCustomerImpact(Request $request)
            {
                $validated = $request->validate([
                    'product_id' => 'required|integer|min:1',
                    'month' => 'required|date_format:Y-m',
                    'limit' => 'nullable|integer|min:1|max:5000',
                    'type' => 'nullable|in:ALL,All,all,WHOLESALE,Wholesale,wholesale,RETAIL,Retail,retail',
                    'supplier_id' => 'nullable|integer|min:1',
                    'category_id' => 'nullable|integer|min:1',
                ]);

                $reportMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
                $months = collect([0, 1, 2, 3])->map(function ($monthsAgo) use ($reportMonth) {
                    $month = $reportMonth->copy()->subMonths($monthsAgo);

                    return [
                        'month' => $month->format('Y-m'),
                        'label' => $month->format('F Y'),
                        'date_from' => $month->copy()->startOfMonth()->toDateString(),
                        'date_to' => $month->copy()->endOfMonth()->toDateString(),
                    ];
                });

                $productQuery = DB::table('products as p')
                    ->select(
                        'p.id as product_id',
                        'p.product_name',
                        'p.packaging',
                        'p.quantity',
                        'p.variation',
                        'p.stock',
                        'p.stock_pc',
                        'p.category_id'
                    )
                    ->where('p.id', $validated['product_id'])
                    ->where('p.disabled', 0);

                if (!empty($validated['category_id'])) {
                    $productQuery->where('p.category_id', $validated['category_id']);
                }

                if (!empty($validated['supplier_id'])) {
                    $supplierId = $validated['supplier_id'];
                    $productQuery->whereExists(function ($supplierQuery) use ($supplierId) {
                        $supplierQuery->select(DB::raw(1))
                            ->from('product_supplier as ps')
                            ->whereColumn('ps.product_id', 'p.id')
                            ->where('ps.supplier_id', $supplierId);
                    });
                }

                $product = $productQuery->first();

                if (!$product) {
                    return response()->json([
                        'code' => 404,
                        'message' => 'Enabled product not found for the selected filters.',
                    ], 404);
                }

                $monthCases = $months->map(function ($month, $index) {
                    $number = $index + 1;
                    $from = $month['date_from'];
                    $to = $month['date_to'];

                    return [
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN so.shop_order_total_price ELSE 0 END) as month_{$number}_sales",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN CASE WHEN UPPER(mup.business_type) = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity ELSE so.shop_order_quantity END ELSE 0 END) / NULLIF(p.quantity, 0) as month_{$number}_ordered_quantity",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN CASE WHEN UPPER(mup.business_type) = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity ELSE so.shop_order_quantity END ELSE 0 END) as month_{$number}_pieces",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' AND UPPER(mup.business_type) = 'WHOLESALE' THEN so.shop_order_quantity ELSE 0 END) as month_{$number}_wholesale_boxes",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' AND UPPER(mup.business_type) = 'RETAIL' THEN so.shop_order_quantity ELSE 0 END) as month_{$number}_retail_pieces",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' AND UPPER(mup.business_type) = 'WHOLESALE' THEN so.shop_order_total_price ELSE 0 END) as month_{$number}_wholesale_sales",
                        "SUM(CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' AND UPPER(mup.business_type) = 'RETAIL' THEN so.shop_order_total_price ELSE 0 END) as month_{$number}_retail_sales",
                        "COUNT(DISTINCT CASE WHEN sot.date BETWEEN '{$from}' AND '{$to}' THEN sot.id END) as month_{$number}_orders",
                    ];
                })->flatten()->map(function ($expression) {
                    return DB::raw($expression);
                })->all();

                $customerQuery = DB::table('customer as c')
                    ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')
                    ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                    ->join('products as p', 'p.id', '=', 'so.product_id')
                    ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                    ->select(array_merge([
                        'c.id as customer_id',
                        'c.first_name',
                        'c.last_name',
                        'c.store_name',
                    ], $monthCases))
                    ->where('p.id', $validated['product_id'])
                    ->where('p.disabled', 0)
                    ->where('sot.status', 1)
                    ->where('sot.type', 0)
                    ->whereBetween('sot.date', [
                        $months->last()['date_from'],
                        $months->first()['date_to'],
                    ])
                    ->groupBy('c.id', 'c.first_name', 'c.last_name', 'c.store_name');

                if (!empty($validated['type']) && strtoupper($validated['type']) !== 'ALL') {
                    $customerQuery->where('mup.business_type', strtoupper($validated['type']));
                }

                $customers = $customerQuery->get()->map(function ($customer) use ($months) {
                    $history = $months->map(function ($month, $index) use ($customer) {
                        $number = $index + 1;

                        return array_merge($month, [
                            'sales_amount' => round((float) $customer->{"month_{$number}_sales"}, 2),
                            'ordered_quantity' => round((float) $customer->{"month_{$number}_ordered_quantity"}, 2),
                            'pieces_sold' => (int) $customer->{"month_{$number}_pieces"},
                            'wholesale_boxes' => (int) $customer->{"month_{$number}_wholesale_boxes"},
                            'retail_pieces' => (int) $customer->{"month_{$number}_retail_pieces"},
                            'wholesale_sales' => round((float) $customer->{"month_{$number}_wholesale_sales"}, 2),
                            'retail_sales' => round((float) $customer->{"month_{$number}_retail_sales"}, 2),
                            'order_count' => (int) $customer->{"month_{$number}_orders"},
                        ]);
                    })->values();

                    $current = $history[0];
                    $lastMonth = $history[1];
                    $previousMonths = $history->slice(1);
                    $previousTwoMonths = $history->slice(1, 2);
                    $usualSales = round((float) $previousMonths->avg('sales_amount'), 2);
                    $usualQuantity = round((float) $previousMonths->avg('ordered_quantity'), 2);
                    $usualPieces = round((float) $previousMonths->avg('pieces_sold'), 2);
                    $previousTwoSales = round((float) $previousTwoMonths->avg('sales_amount'), 2);
                    $previousTwoQuantity = round((float) $previousTwoMonths->avg('ordered_quantity'), 2);
                    $previousTwoPieces = round((float) $previousTwoMonths->avg('pieces_sold'), 2);
                    $salesImpact = round($current['sales_amount'] - $usualSales, 2);
                    $quantityImpact = round($current['ordered_quantity'] - $usualQuantity, 2);
                    $lastMonthSalesImpact = round($current['sales_amount'] - $lastMonth['sales_amount'], 2);
                    $lastMonthQuantityImpact = round($current['ordered_quantity'] - $lastMonth['ordered_quantity'], 2);

                    if ($current['ordered_quantity'] == 0 && $usualQuantity > 0) {
                        $status = 'MISSING';
                    } elseif ($usualQuantity == 0 && $current['ordered_quantity'] > 0) {
                        $status = 'NEW_OR_RETURNING';
                    } elseif ($quantityImpact > 0) {
                        $status = 'ABOVE_USUAL';
                    } elseif ($quantityImpact < 0) {
                        $status = 'BELOW_USUAL';
                    } else {
                        $status = 'UNCHANGED';
                    }

                    $customerName = trim($customer->first_name.' '.$customer->last_name);

                    return [
                        'customer_id' => (int) $customer->customer_id,
                        'customer_name' => $customerName,
                        'store_name' => $customer->store_name,
                        'display_name' => $customer->store_name
                            ? $customerName.' ('.$customer->store_name.')'
                            : $customerName,
                        'status' => $status,
                        'current_month' => $current,
                        'last_month' => $lastMonth,
                        'three_month_history' => $history->take(3)->values(),
                        'comparison_history' => $history,
                        'usual_previous_two_months' => [
                            'sales_amount' => $previousTwoSales,
                            'ordered_quantity' => $previousTwoQuantity,
                            'pieces_sold' => $previousTwoPieces,
                        ],
                        'previous_three_month_average' => [
                            'sales_amount' => $usualSales,
                            'ordered_quantity' => $usualQuantity,
                            'pieces_sold' => $usualPieces,
                        ],
                        'vs_last_month' => [
                            'sales_impact' => $lastMonthSalesImpact,
                            'quantity_impact' => $lastMonthQuantityImpact,
                            'sales_change_percentage' => $lastMonth['sales_amount'] > 0
                                ? round(($lastMonthSalesImpact / $lastMonth['sales_amount']) * 100, 2)
                                : null,
                            'quantity_change_percentage' => $lastMonth['ordered_quantity'] > 0
                                ? round(($lastMonthQuantityImpact / $lastMonth['ordered_quantity']) * 100, 2)
                                : null,
                        ],
                        'vs_previous_three_month_average' => [
                            'sales_impact' => $salesImpact,
                            'quantity_impact' => $quantityImpact,
                            'pieces_impact' => round($current['pieces_sold'] - $usualPieces, 2),
                            'sales_change_percentage' => $usualSales > 0
                                ? round(($salesImpact / $usualSales) * 100, 2)
                                : null,
                            'quantity_change_percentage' => $usualQuantity > 0
                                ? round(($quantityImpact / $usualQuantity) * 100, 2)
                                : null,
                        ],
                        'sales_impact' => $salesImpact,
                        'quantity_impact' => $quantityImpact,
                        'pieces_impact' => round($current['pieces_sold'] - $usualPieces, 2),
                        'sales_change_percentage' => $usualSales > 0
                            ? round(($salesImpact / $usualSales) * 100, 2)
                            : null,
                        'quantity_change_percentage' => $usualQuantity > 0
                            ? round(($quantityImpact / $usualQuantity) * 100, 2)
                            : null,
                    ];
                });

                $limit = (int) ($validated['limit'] ?? 10);
                $positiveImpact = $customers
                    ->filter(function ($customer) {
                        return $customer['sales_impact'] > 0;
                    })
                    ->sortByDesc('sales_impact')
                    ->take($limit)
                    ->values();
                $negativeImpact = $customers
                    ->filter(function ($customer) {
                        return $customer['sales_impact'] < 0;
                    })
                    ->sortBy('sales_impact')
                    ->take($limit)
                    ->values();
                $missingCustomers = $customers
                    ->where('status', 'MISSING')
                    ->sortBy('sales_impact')
                    ->take($limit)
                    ->values();

                return response()->json([
                    'product' => [
                        'product_id' => (int) $product->product_id,
                        'product_name' => $product->product_name,
                        'packaging' => $product->packaging,
                        'quantity' => (int) $product->quantity,
                        'variation' => $product->variation,
                        'current_stock' => (int) $product->stock,
                        'current_stock_pc' => (int) $product->stock_pc,
                        'category_id' => (int) $product->category_id,
                    ],
                    'report_month' => $months->first(),
                    'comparison_months' => $months->slice(1)->values(),
                    'impact_benchmark' => 'PREVIOUS_THREE_MONTH_AVERAGE',
                    'filters' => [
                        'limit' => $limit,
                        'type' => strtoupper($validated['type'] ?? 'ALL'),
                        'supplier_id' => $validated['supplier_id'] ?? null,
                        'category_id' => $validated['category_id'] ?? null,
                    ],
                    'summary' => [
                        'customer_count' => $customers->count(),
                        'positive_impact_count' => $customers->where('sales_impact', '>', 0)->count(),
                        'negative_impact_count' => $customers->where('sales_impact', '<', 0)->count(),
                        'missing_customer_count' => $customers->where('status', 'MISSING')->count(),
                        'net_sales_impact' => round($customers->sum('sales_impact'), 2),
                        'net_quantity_impact' => round($customers->sum('quantity_impact'), 2),
                    ],
                    'positive_impact_customers' => $positiveImpact,
                    'negative_impact_customers' => $negativeImpact,
                    'missing_customers' => $missingCustomers,
                    'code' => 200,
                    'message' => 'Monthly product customer impact fetched successfully.',
                ]);
            }

            public function fetchMonthlySalesImpactAnalysis(Request $request)
            {
                $validated = $request->validate([
                    'month' => 'required|date_format:Y-m',
                    'limit' => 'nullable|integer|min:1|max:5000',
                    'supplier_id' => 'nullable|integer|min:1',
                    'category_id' => 'nullable|integer|min:1',
                    'impact_group' => 'nullable|string|max:50',
                    'product_impact_group' => 'nullable|string|max:50',
                    'customer_impact_group' => 'nullable|string|max:50',
                    'search' => 'nullable|string|max:100',
                    'product_search' => 'nullable|string|max:100',
                    'customer_search' => 'nullable|string|max:100',
                ]);

                $limit = (int) ($validated['limit'] ?? 10);
                $reportMonth = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
                $months = collect([0, 1, 2, 3])->map(function ($monthsAgo) use ($reportMonth) {
                    $month = $reportMonth->copy()->subMonths($monthsAgo);

                    return [
                        'month' => $month->format('Y-m'),
                        'label' => $month->format('F Y'),
                        'date_from' => $month->copy()->startOfMonth()->toDateString(),
                        'date_to' => $month->copy()->endOfMonth()->toDateString(),
                    ];
                });

                $salesCases = $months->map(function ($month, $index) {
                    $number = $index + 1;

                    return DB::raw("SUM(CASE WHEN sot.date BETWEEN '{$month['date_from']}' AND '{$month['date_to']}' THEN so.shop_order_total_price ELSE 0 END) as month_{$number}_sales");
                })->all();
                $quantityCases = $months->map(function ($month, $index) {
                    $number = $index + 1;

                    return DB::raw("SUM(CASE WHEN sot.date BETWEEN '{$month['date_from']}' AND '{$month['date_to']}' THEN CASE WHEN UPPER(mup.business_type) = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity ELSE so.shop_order_quantity END ELSE 0 END) as month_{$number}_quantity");
                })->all();
                $orderCases = $months->map(function ($month, $index) {
                    $number = $index + 1;

                    return DB::raw("COUNT(DISTINCT CASE WHEN sot.date BETWEEN '{$month['date_from']}' AND '{$month['date_to']}' THEN sot.id END) as month_{$number}_orders");
                })->all();

                $baseQuery = function () use ($months, $validated) {
                    $query = DB::table('shop_order as so')
                        ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
                        ->join('products as p', 'p.id', '=', 'so.product_id')
                        ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                        ->where('p.disabled', 0)
                        ->where('sot.status', 1)
                        ->where('sot.type', 0)
                        ->whereBetween('sot.date', [$months->last()['date_from'], $months->first()['date_to']]);

                    if (!empty($validated['category_id'])) {
                        $query->where('p.category_id', $validated['category_id']);
                    }
                    if (!empty($validated['supplier_id'])) {
                        $supplierId = $validated['supplier_id'];
                        $query->whereExists(function ($supplierQuery) use ($supplierId) {
                            $supplierQuery->select(DB::raw(1))
                                ->from('product_supplier as ps')
                                ->whereColumn('ps.product_id', 'p.id')
                                ->where('ps.supplier_id', $supplierId);
                        });
                    }

                    return $query;
                };

                $totals = $baseQuery()->select(array_merge($salesCases, $quantityCases, $orderCases))->first();
                $monthSummaries = $months->map(function ($month, $index) use ($totals) {
                    $number = $index + 1;

                    return array_merge($month, [
                        'total_sales' => round((float) ($totals->{"month_{$number}_sales"} ?? 0), 2),
                        'total_quantity' => (int) ($totals->{"month_{$number}_quantity"} ?? 0),
                        'total_orders' => (int) ($totals->{"month_{$number}_orders"} ?? 0),
                    ]);
                })->values();

                $usesReceivedPaymentSales = empty($validated['category_id'])
                    && empty($validated['supplier_id']);

                if ($usesReceivedPaymentSales) {
                    $this->usePaymentTypePo();
                    $paymentSalesCases = $months->map(function ($month, $index) {
                        $number = $index + 1;

                        return DB::raw("SUM(CASE WHEN DATE(mop.created_at) BETWEEN '{$month['date_from']}' AND '{$month['date_to']}' THEN mop.amount ELSE 0 END) as month_{$number}_sales");
                    })->all();

                    $paymentTotals = DB::table('shop_order_transaction as sot')
                        ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
                        ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                        ->join('shop', 'shop.id', '=', 'sot.shop_id')
                        ->select($paymentSalesCases)
                        ->where('shop.shop_type_id', 3)
                        ->whereDate('mop.created_at', '>=', $months->last()['date_from'])
                        ->whereDate('mop.created_at', '<=', $months->first()['date_to'])
                        ->first();

                    $monthSummaries = $monthSummaries->map(function ($summary, $index) use ($paymentTotals) {
                        $number = $index + 1;
                        $summary['total_sales'] = round((float) ($paymentTotals->{"month_{$number}_sales"} ?? 0), 2);

                        return $summary;
                    });
                }

                $current = $monthSummaries[0];
                $lastMonth = $monthSummaries[1];
                $previousThree = $monthSummaries->slice(1, 3);
                $averageSales = round((float) $previousThree->avg('total_sales'), 2);
                $averageQuantity = round((float) $previousThree->avg('total_quantity'), 2);
                $averageOrders = round((float) $previousThree->avg('total_orders'), 2);

                $products = $baseQuery()
                    ->select(array_merge([
                        'p.id as product_id',
                        'p.product_name',
                        'p.packaging',
                        'p.variation',
                        'p.created_at as product_created_at',
                    ], $salesCases, $quantityCases))
                    ->groupBy('p.id', 'p.product_name', 'p.packaging', 'p.variation', 'p.created_at')
                    ->get()
                    ->map(function ($product) use ($reportMonth) {
                        return $this->buildMonthlyImpactItem(
                            $product,
                            'product',
                            false,
                            $reportMonth
                        );
                    });

                $customers = $baseQuery()
                    ->join('customer as c', 'c.id', '=', 'sot.requestor')
                    ->select(array_merge([
                        'c.id as customer_id',
                        'c.first_name',
                        'c.last_name',
                        'c.store_name',
                        'c.created_at as customer_created_at',
                    ], $salesCases, $quantityCases))
                    ->groupBy('c.id', 'c.first_name', 'c.last_name', 'c.store_name', 'c.created_at')
                    ->get()
                    ->map(function ($customer) use ($reportMonth) {
                        return $this->buildMonthlyImpactItem(
                            $customer,
                            'customer',
                            true,
                            $reportMonth
                        );
                    });

                $products = $this->addMonthlyImpactRanks($products);
                $customers = $this->addMonthlyImpactRanks($customers);

                $defaultImpactGroup = $validated['impact_group'] ?? 'all';
                $productImpactGroup = $this->normalizeMonthlyImpactGroup(
                    $validated['product_impact_group'] ?? $defaultImpactGroup
                );
                $customerImpactGroup = $this->normalizeMonthlyImpactGroup(
                    $validated['customer_impact_group'] ?? $defaultImpactGroup
                );

                if ($productImpactGroup === null || $customerImpactGroup === null) {
                    return response()->json([
                        'message' => 'The selected impact group is invalid.',
                        'errors' => [
                            'impact_group' => [
                                'Use winning, new_customer, new_product, highest_sales, lowest_sales, declining, missing, or all.',
                            ],
                        ],
                    ], 422);
                }

                $productDrivers = $this->buildImpactDrivers(
                    $products,
                    $limit,
                    $productImpactGroup,
                    $validated['product_search'] ?? ($validated['search'] ?? null),
                    'product'
                );
                $customerDrivers = $this->buildImpactDrivers(
                    $customers,
                    $limit,
                    $customerImpactGroup,
                    $validated['customer_search'] ?? ($validated['search'] ?? null),
                    'customer'
                );
                $salesGap = round($current['total_sales'] - $averageSales, 2);
                $lastMonthGap = round($current['total_sales'] - $lastMonth['total_sales'], 2);

                return response()->json([
                    'report_month' => $months->first(),
                    'comparison_months' => $months->slice(1)->values(),
                    'filters' => [
                        'limit' => $limit,
                        'type' => 'ALL',
                        'supplier_id' => $validated['supplier_id'] ?? null,
                        'category_id' => $validated['category_id'] ?? null,
                        'product_impact_group' => $productImpactGroup,
                        'customer_impact_group' => $customerImpactGroup,
                        'product_search' => $validated['product_search'] ?? ($validated['search'] ?? null),
                        'customer_search' => $validated['customer_search'] ?? ($validated['search'] ?? null),
                    ],
                    'impact_benchmark' => 'PREVIOUS_THREE_MONTH_AVERAGE',
                    'rank_scope' => 'all_filtered_sales',
                    'sales_summary' => [
                        'sales_basis' => $usesReceivedPaymentSales
                            ? 'RECEIVED_PAYMENTS'
                            : 'FILTERED_ORDER_LINES',
                        'current' => $current,
                        'last_month' => $lastMonth,
                        'previous_three_month_average' => [
                            'total_sales' => $averageSales,
                            'total_quantity' => $averageQuantity,
                            'total_orders' => $averageOrders,
                        ],
                        'vs_last_month' => $this->buildSalesComparison($current['total_sales'], $lastMonth['total_sales']),
                        'vs_previous_three_month_average' => $this->buildSalesComparison($current['total_sales'], $averageSales),
                        'trend' => $salesGap > 0 ? 'HIGHER' : ($salesGap < 0 ? 'LOWER' : 'UNCHANGED'),
                    ],
                    'why_sales_changed' => [
                        'headline' => $salesGap < 0
                            ? 'Sales are below the previous three-month average.'
                            : ($salesGap > 0 ? 'Sales are above the previous three-month average.' : 'Sales match the previous three-month average.'),
                        'sales_gap_vs_last_month' => $lastMonthGap,
                        'sales_gap_vs_previous_three_month_average' => $salesGap,
                        'product_net_impact' => round($products->sum('sales_impact'), 2),
                        'customer_net_impact' => round($customers->sum('sales_impact'), 2),
                        'missing_customer_count' => $customers->where('status', 'MISSING')->count(),
                        'new_customer_count' => $customers->where('status', 'NEW_CUSTOMER')->count(),
                        'declining_customer_count' => $customers->where('status', 'DECLINING')->count(),
                        'missing_product_count' => $products->where('status', 'MISSING')->count(),
                        'new_product_count' => $products->where('status', 'NEW_PRODUCT')->count(),
                        'declining_product_count' => $products->where('status', 'DECLINING')->count(),
                    ],
                    'product_impact' => $productDrivers,
                    'customer_impact' => $customerDrivers,
                    'code' => 200,
                    'message' => 'Monthly sales impact analysis fetched successfully.',
                ]);
            }

            private function buildMonthlyImpactItem(
                $row,
                $kind,
                $includeCustomerName,
                $reportMonth = null
            )
            {
                $sales = collect([1, 2, 3, 4])->map(function ($number) use ($row) {
                    return round((float) ($row->{"month_{$number}_sales"} ?? 0), 2);
                });
                $quantities = collect([1, 2, 3, 4])->map(function ($number) use ($row) {
                    return (int) ($row->{"month_{$number}_quantity"} ?? 0);
                });
                $usualSales = round((float) $sales->slice(1)->avg(), 2);
                $usualQuantity = round((float) $quantities->slice(1)->avg(), 2);
                $salesImpact = round($sales[0] - $usualSales, 2);
                $quantityImpact = round($quantities[0] - $usualQuantity, 2);

                if ($sales[0] == 0 && $usualSales > 0) {
                    $status = 'MISSING';
                } elseif ($usualSales == 0 && $sales[0] > 0) {
                    $status = 'NEW_OR_RETURNING';
                } elseif ($salesImpact < 0) {
                    $status = 'DECLINING';
                } elseif ($salesImpact > 0) {
                    $status = 'GROWING';
                } else {
                    $status = 'UNCHANGED';
                }

                $isNewCustomer = $includeCustomerName
                    && $reportMonth !== null
                    && !empty($row->customer_created_at)
                    && Carbon::parse($row->customer_created_at)->format('Y-m') === $reportMonth->format('Y-m');
                $isNewProduct = !$includeCustomerName
                    && $reportMonth !== null
                    && !empty($row->product_created_at)
                    && Carbon::parse($row->product_created_at)->format('Y-m') === $reportMonth->format('Y-m');

                if ($isNewCustomer) {
                    $status = 'NEW_CUSTOMER';
                } elseif ($includeCustomerName && $status === 'NEW_OR_RETURNING') {
                    $status = 'RETURNING_CUSTOMER';
                } elseif ($isNewProduct) {
                    $status = 'NEW_PRODUCT';
                } elseif (!$includeCustomerName && $status === 'NEW_OR_RETURNING') {
                    $status = 'RETURNING_PRODUCT';
                }

                $item = [
                    $kind.'_id' => (int) $row->{$kind.'_id'},
                    'status' => $status,
                    'current_sales' => $sales[0],
                    'last_month_sales' => $sales[1],
                    'previous_three_month_average_sales' => $usualSales,
                    'sales_impact' => $salesImpact,
                    'sales_change_percentage' => $usualSales > 0 ? round(($salesImpact / $usualSales) * 100, 2) : null,
                    'current_quantity' => $quantities[0],
                    'previous_three_month_average_quantity' => $usualQuantity,
                    'quantity_impact' => $quantityImpact,
                ];

                if ($includeCustomerName) {
                    $name = trim($row->first_name.' '.$row->last_name);
                    $item['customer_name'] = $name;
                    $item['store_name'] = $row->store_name;
                    $item['display_name'] = $row->store_name ? $name.' ('.$row->store_name.')' : $name;
                    $item['customer_created_at'] = $row->customer_created_at;
                    $item['is_new_customer'] = $isNewCustomer;
                } else {
                    $item['product_name'] = $row->product_name;
                    $item['packaging'] = $row->packaging;
                    $item['variation'] = $row->variation;
                    $item['product_created_at'] = $row->product_created_at;
                    $item['is_new_product'] = $isNewProduct;
                }

                return $item;
            }

            private function addMonthlyImpactRanks($items)
            {
                $sortBySales = function ($first, $second, $field) {
                    $salesComparison = $second[$field] <=> $first[$field];
                    if ($salesComparison !== 0) {
                        return $salesComparison;
                    }

                    $firstId = $first['product_id'] ?? $first['customer_id'];
                    $secondId = $second['product_id'] ?? $second['customer_id'];

                    return $firstId <=> $secondId;
                };

                $currentRanks = $items->sort(function ($first, $second) use ($sortBySales) {
                    return $sortBySales($first, $second, 'current_sales');
                })
                    ->values()
                    ->mapWithKeys(function ($item, $index) {
                        $id = $item['product_id'] ?? $item['customer_id'];

                        return [$id => $index + 1];
                    });
                $previousRanks = $items->sort(function ($first, $second) use ($sortBySales) {
                    return $sortBySales($first, $second, 'last_month_sales');
                })
                    ->values()
                    ->mapWithKeys(function ($item, $index) {
                        $id = $item['product_id'] ?? $item['customer_id'];

                        return [$id => $index + 1];
                    });

                return $items->map(function ($item) use ($currentRanks, $previousRanks) {
                    $id = $item['product_id'] ?? $item['customer_id'];
                    $item['rank'] = $currentRanks->get($id);
                    $item['current_rank'] = $item['rank'];
                    $item['previous_rank'] = $previousRanks->get($id);
                    $item['last_month_rank'] = $item['previous_rank'];
                    $item['rank_change'] = $item['previous_rank'] - $item['rank'];
                    $item['rank_movement'] = $item['rank_change'];
                    $item['rank_movement_direction'] = $item['rank_change'] > 0
                        ? 'UP'
                        : ($item['rank_change'] < 0 ? 'DOWN' : 'UNCHANGED');

                    return $item;
                });
            }

            private function normalizeMonthlyImpactGroup($impactGroup)
            {
                $impactGroup = strtolower(trim((string) $impactGroup));
                $impactGroup = str_replace([' ', '-'], '_', $impactGroup);
                $impactGroup = [
                    'winning_products' => 'winning',
                    'winning_customers' => 'winning',
                    'new' => 'new_customer',
                    'new_customers' => 'new_customer',
                    'new_product' => 'new_product',
                    'new_products' => 'new_product',
                    'highest' => 'highest_sales',
                    'highest_products' => 'highest_sales',
                    'highest_customers' => 'highest_sales',
                    'highest_sales_products' => 'highest_sales',
                    'highest_sales_customers' => 'highest_sales',
                    'lowest' => 'lowest_sales',
                    'lowest_products' => 'lowest_sales',
                    'lowest_customers' => 'lowest_sales',
                    'lowest_sales_products' => 'lowest_sales',
                    'lowest_sales_customers' => 'lowest_sales',
                    'lowest_declining' => 'declining',
                    'losing' => 'declining',
                    'declining_products' => 'declining',
                    'declining_customers' => 'declining',
                    'missing_products' => 'missing',
                    'missing_customers' => 'missing',
                    'all_results' => 'all',
                ][$impactGroup] ?? $impactGroup;

                return in_array(
                    $impactGroup,
                    ['winning', 'new_customer', 'new_product', 'highest_sales', 'lowest_sales', 'declining', 'missing', 'all'],
                    true
                ) ? $impactGroup : null;
            }

            private function buildImpactDrivers($items, $limit, $impactGroup = 'all', $search = null, $kind = null)
            {
                $filtered = $items;

                if ($impactGroup === 'winning') {
                    $filtered = $filtered->where('sales_impact', '>', 0);
                } elseif ($impactGroup === 'new_customer') {
                    $filtered = $filtered->where('status', 'NEW_CUSTOMER');
                } elseif ($impactGroup === 'new_product') {
                    $filtered = $filtered->where('status', 'NEW_PRODUCT');
                } elseif ($impactGroup === 'declining') {
                    $filtered = $filtered->where('status', 'DECLINING');
                } elseif ($impactGroup === 'missing') {
                    $filtered = $filtered->where('status', 'MISSING');
                }

                if ($search !== null && trim($search) !== '') {
                    $needle = strtolower(trim($search));
                    $filtered = $filtered->filter(function ($item) use ($needle, $kind) {
                        $values = $kind === 'product'
                            ? [$item['product_name'] ?? '', $item['packaging'] ?? '', $item['variation'] ?? '']
                            : [$item['customer_name'] ?? '', $item['store_name'] ?? '', $item['display_name'] ?? ''];

                        return collect($values)->contains(function ($value) use ($needle) {
                            return str_contains(strtolower((string) $value), $needle);
                        });
                    });
                }

                if ($impactGroup === 'lowest_sales') {
                    $filtered = $filtered->sortBy('current_sales');
                } elseif ($impactGroup === 'declining' || $impactGroup === 'missing') {
                    $filtered = $filtered->sortBy('sales_impact');
                } else {
                    $filtered = $filtered->sortByDesc('current_sales');
                }

                $filtered = $filtered->values();
                $filteredTotal = $filtered->count();

                return [
                    'total_count' => $items->count(),
                    'filtered_total' => $filteredTotal,
                    'impact_group' => $impactGroup,
                    'counts' => [
                        'winning' => $items->where('sales_impact', '>', 0)->count(),
                        'new_customer' => $items->where('status', 'NEW_CUSTOMER')->count(),
                        'new_product' => $items->where('status', 'NEW_PRODUCT')->count(),
                        'declining' => $items->where('status', 'DECLINING')->count(),
                        'missing' => $items->where('status', 'MISSING')->count(),
                        'all' => $items->count(),
                    ],
                    'negative_net_impact' => round($items->where('sales_impact', '<', 0)->sum('sales_impact'), 2),
                    'positive_net_impact' => round($items->where('sales_impact', '>', 0)->sum('sales_impact'), 2),
                    'biggest_declines' => $items->where('sales_impact', '<', 0)->sortBy('sales_impact')->take($limit)->values(),
                    'missing' => $items->where('status', 'MISSING')->sortBy('sales_impact')->take($limit)->values(),
                    'biggest_growth' => $items->where('sales_impact', '>', 0)->sortByDesc('sales_impact')->take($limit)->values(),
                    'new_customers' => $items->where('status', 'NEW_CUSTOMER')->sortByDesc('current_sales')->take($limit)->values(),
                    'new_products' => $items->where('status', 'NEW_PRODUCT')->sortByDesc('current_sales')->take($limit)->values(),
                    'highest_sales' => $items->sortByDesc('current_sales')->take($limit)->values(),
                    'lowest_sales' => $items->sortBy('current_sales')->take($limit)->values(),
                    'data' => $filtered->take($limit)->values(),
                ];
            }

            private function buildSalesComparison($currentSales, $benchmarkSales)
            {
                $difference = round($currentSales - $benchmarkSales, 2);

                return [
                    'sales_difference' => $difference,
                    'sales_change_percentage' => $benchmarkSales > 0
                        ? round(($difference / $benchmarkSales) * 100, 2)
                        : null,
                ];
            }


   public function fetchOnlineShopOrderTransactionList(Request $request)
    {
        // $request->input('date') = date('Y-m-d');
        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'sot.id')
            ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'sot.requestor')
            ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->leftJoin('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->leftJoin('products as p', 'p.id', '=', 'mup.product_id')
            ->leftJoin('category as category', 'category.id', '=', 'p.category_id')
            ->select('shop.shop_name','sot.id', 'sot.shop_order_transaction_total_quantity',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at', 'sot.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'c.created_at as customer_created_date', 'sot.checker', 'sot.requestor',
              'sot.status', 'sot.date', 'sot.profit',
              'sot.total_cash', 'sot.total_online',
             'ct.customer_type', 'sot.rider_name', 'sot.delivery_customer_id', 'ds.status as delivery_status',
             DB::raw("GROUP_CONCAT(DISTINCT NULLIF(category.tags, '')) as tags"),
             DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list"))    
             ->where('shop.shop_type_id', 3)
             ->where('sot.date', $request->input('date'))
             ->groupBy('sot.id')
             ->orderBy('sot.id', 'DESC')
             ->get();

            foreach ($shop_order_transaction_list as $sotl) {
                $vipCustomers = [];

                if ($sotl->vip_customer_list != '') {
                    foreach (explode('||', $sotl->vip_customer_list) as $vipCustomer) {
                        $vipCustomerDetails = explode('::', $vipCustomer);

                        if (isset($vipCustomerDetails[0]) && $vipCustomerDetails[0] != '') {
                            $vipCustomers[] = [
                                'vip_customer_transaction_id' => $vipCustomerDetails[0],
                                'vip_customer_id' => isset($vipCustomerDetails[1]) ? $vipCustomerDetails[1] : '',
                                'vip_name' => isset($vipCustomerDetails[2]) ? $vipCustomerDetails[2] : '',
                                'vip_color' => isset($vipCustomerDetails[3]) ? $vipCustomerDetails[3] : '',
                            ];
                        }
                    }
                }

                $sotl->vip_customers = $vipCustomers;
                unset($sotl->vip_customer_list);
            }
            
            $total_profit = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(so.shop_order_profit) as total_profit'))  
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')  
            ->join('shop', 'shop.id', '=', 'sot.shop_id') 
            ->where('shop.shop_type_id', 3)
            ->where('sot.status', 1)
            ->where('sot.date', $request->input('date'))
            ->first();


            $total_sales_completed = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales_completed'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id') 
            ->where('shop.shop_type_id', 3)
            ->where('sot.status', 1)
            ->where('sot.date', $request->input('date'))
            ->first();


           $cash = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->where('pt.type', 1)
            ->first();
    
            $online = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->where('pt.type', 2)
            ->first();
            
            $total_paid = DB::table('mode_of_payment as mop')
                 ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id') 
                 ->select(DB::raw('SUM(mop.amount) as total_paid'))  
                 ->where('mop.created_at', $request->input('date'))
                 ->where('sot.date', $request->input('date'))
                 ->first();



           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('SUM(mop.is_paid) as total_paid_count'), DB::raw('COUNT(mop.id) as total_count'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('mop.created_at', $request->input('date'))
            ->groupBy('pt.id')
            ->get();

           $total_count = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('COUNT(shop_id) as total_count'),)  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('sot.date', $request->input('date'))
            ->first();

            $emails = DB::table('email as e')
            ->select('e.email')  
            ->where('e.status', 1)
            ->get();

            $array_email = array();
            foreach ($emails as $email) { 
             array_push($array_email, $email->email);  
            }




         $total_paid_prev = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_paid_prev'))  
            ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id') 
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', '<', $request->input('date'))
            ->first();

            $online_prev = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online_prev'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', '<', $request->input('date'))
            ->where('pt.type', 2)
            ->first();  
            
             $cash_prev = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash_prev'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', '<', $request->input('date'))
            ->where('pt.type', 1)
            ->first();

////////////////////////////
            
            $online_outdated = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online_outdated'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date',  $request->input('date'))
            ->where('pt.type', 2)
            ->first();
            
            $cash_oudated = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash_outdated'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            // ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->where('pt.type', 1)
            ->first();                
           
            $total_paid_outdated = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_paid_outdated'))  
            ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id') 
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->first();      

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }

           $response = [
              'shop_name' => $shop_order_transaction_list->count() != 0 ? $shop_order_transaction_list[0]->shop_name: '',
              'emails' => $array_email,
              'total_sales_completed' =>$total_sales_completed->total_sales_completed != 0 ? $total_sales_completed->total_sales_completed : 0,
              'total_profit' =>$total_profit->total_profit!= 0 ? $total_profit->total_profit : 0,
              'total_count' =>$total_count->total_count,
              'total_cash' =>$cash->total_cash!= 0 ? $cash->total_cash : 0,
              'total_cash_prev' =>$cash_prev->total_cash_prev!= 0 ? $cash_prev->total_cash_prev : 0,
              'total_cash_outdated' =>$cash_oudated->total_cash_outdated!= 0 ? $cash_oudated->total_cash_outdated : 0,
              'total_online' =>$online->total_online!= 0 ? $online->total_online : 0,
              'total_online_prev' =>$online_prev->total_online_prev!= 0 ? $online_prev->total_online_prev : 0,
              'total_online_outdated' =>$online_outdated->total_online_outdated!= 0 ? $online_outdated->total_online_outdated : 0,
              'total_paid' =>$total_paid->total_paid!= 0 ? $total_paid->total_paid : 0,
              'total_paid_prev' =>$total_paid_prev->total_paid_prev!= 0 ? $total_paid_prev->total_paid_prev : 0,
              'total_paid_outdated' =>$total_paid_outdated->total_paid_outdated!= 0 ? $total_paid_outdated->total_paid_outdated : 0,
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'date' => $request->input('date'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchOnlineShopOrderTransactionListV2(Request $request)
    {
        $date = $request->input('date') ?: date('Y-m-d');
        $transactionId = $request->attributes->get('transaction_id_filter');

        $shopOrderTransactionList = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'sot.id')
            ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'sot.requestor')
            ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->leftJoin('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->leftJoin('products as p', 'p.id', '=', 'mup.product_id')
            ->leftJoin('category as category', 'category.id', '=', 'p.category_id')
            ->select(
                'shop.shop_name',
                'sot.id',
                'sot.shop_order_transaction_total_quantity',
                'sot.shop_order_transaction_total_price',
                'sot.created_at',
                'sot.updated_at',
                'sot.is_pickup',
                'shop.shop_type_id',
                DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"),
                'c.store_name',
                'c.created_at as customer_created_date',
                'sot.checker',
                'sot.requestor',
                'sot.status',
                'sot.date',
                'sot.profit',
                'sot.total_cash',
                'sot.total_online',
                'ct.customer_type',
                'sot.rider_name',
                'sot.delivery_customer_id',
                'ds.status as delivery_status',
                DB::raw("GROUP_CONCAT(DISTINCT NULLIF(category.tags, '')) as tags"),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
            )
            ->where('shop.shop_type_id', 3)
            ->where('sot.date', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->groupBy('sot.id')
            ->orderBy('sot.id', 'DESC')
            ->get();

        foreach ($shopOrderTransactionList as $shopOrderTransaction) {
            $vipCustomers = [];

            if ($shopOrderTransaction->vip_customer_list != '') {
                foreach (explode('||', $shopOrderTransaction->vip_customer_list) as $vipCustomer) {
                    $details = explode('::', $vipCustomer);

                    if (isset($details[0]) && $details[0] != '') {
                        $vipCustomers[] = [
                            'vip_customer_transaction_id' => $details[0],
                            'vip_customer_id' => $details[1] ?? '',
                            'vip_name' => $details[2] ?? '',
                            'vip_color' => $details[3] ?? '',
                        ];
                    }
                }
            }

            $shopOrderTransaction->vip_customers = $vipCustomers;
            unset($shopOrderTransaction->vip_customer_list);
        }

        $totalProfit = DB::table('shop_order_transaction as sot')
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->where('shop.shop_type_id', 3)
            ->where('sot.status', 1)
            ->where('sot.date', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->sum('so.shop_order_profit');

        $totalSalesCompleted = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->where('shop.shop_type_id', 3)
            ->where('sot.status', 1)
            ->where('sot.date', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->sum('sot.shop_order_transaction_total_price');

        $paymentSummary = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->selectRaw(
                'SUM(CASE WHEN sot.date = ? AND ptp.payment_term_id = 1 THEN mop.amount ELSE 0 END) as total_cash,
                 SUM(CASE WHEN sot.date < ? AND ptp.payment_term_id = 1 THEN mop.amount ELSE 0 END) as total_cash_prev,
                 SUM(CASE WHEN sot.date = ? AND ptp.payment_term_id <> 1 THEN mop.amount ELSE 0 END) as total_online,
                 SUM(CASE WHEN sot.date < ? AND ptp.payment_term_id <> 1 THEN mop.amount ELSE 0 END) as total_online_prev',
                [$date, $date, $date, $date]
            )
            ->first();

        $outdatedPaymentSummary = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $date)
            ->where('sot.date', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->selectRaw(
                'SUM(CASE WHEN ptp.payment_term_id = 1 THEN mop.amount ELSE 0 END) as total_cash_outdated,
                 SUM(CASE WHEN ptp.payment_term_id <> 1 THEN mop.amount ELSE 0 END) as total_online_outdated,
                 SUM(mop.amount) as total_paid_outdated'
            )
            ->first();

        $totalPaid = DB::table('mode_of_payment as mop')
            ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->where('mop.created_at', $date)
            ->where('sot.date', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->sum('mop.amount');

        $totalPaidPrev = DB::table('mode_of_payment as mop')
            ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->where('mop.created_at', $date)
            ->where('sot.date', '<', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->sum('mop.amount');

        $paymentTypes = DB::table('shop_order_transaction as sot')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->where('mop.created_at', $date)
            ->when($transactionId !== null, function ($query) use ($transactionId) {
                $query->where('sot.id', $transactionId);
            })
            ->select(
                DB::raw('SUM(mop.amount) as total_amount'),
                DB::raw('SUM(mop.is_paid) as total_paid_count'),
                DB::raw('COUNT(mop.id) as total_count'),
                'ptp.id as payment_type_po_id',
                'ptp.payment_term_id',
                'ptp.bank_id',
                'ptp.account_number',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.due_date',
                'ptp.buffer_days',
                'ptp.credit_limit',
                'ptp.statement_date',
                'ptp.total_balance_due',
                'ptp.balance',
                'ptp.status',
                'ptp.is_supplier',
                'ptp.is_customer',
                'ptp.created_at as payment_type_po_created_at',
                'ptp.updated_at as payment_type_po_updated_at',
                'b.bank_name',
                'b.status as bank_status',
                'b.created_at as bank_created_at',
                'b.updated_at as bank_updated_at',
                'pt.payment_term',
                'pt.status as payment_term_status',
                'pt.created_at as payment_term_created_at',
                'pt.updated_at as payment_term_updated_at'
            )
            ->groupBy('ptp.id')
            ->get();

        foreach ($shopOrderTransactionList as $shopOrderTransaction) {
            $shopOrderTransaction->mode_of_payment = DB::table('mode_of_payment as mop')
                ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
                ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
                ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
                // ->where('ptp.payment_term_id', '<>', 1)
                ->where('mop.shop_order_transaction_id', $shopOrderTransaction->id)
                ->select(
                    'mop.id',
                    'mop.payment_type_id',
                    'mop.amount',
                    'mop.is_paid',
                    'mop.shop_order_transaction_id',
                    'mop.created_at',
                    'mop.updated_at',
                    'ptp.id as payment_type_po_id',
                    'ptp.payment_term_id',
                    'ptp.bank_id',
                    'ptp.account_number',
                    'ptp.account_name',
                    'ptp.account_description',
                    'ptp.due_date',
                    'ptp.buffer_days',
                    'ptp.credit_limit',
                    'ptp.statement_date',
                    'ptp.total_balance_due',
                    'ptp.balance',
                    'ptp.status',
                    'ptp.is_supplier',
                    'ptp.is_customer',
                    'ptp.created_at as payment_type_po_created_at',
                    'ptp.updated_at as payment_type_po_updated_at',
                    'b.bank_name',
                    'b.status as bank_status',
                    'b.created_at as bank_created_at',
                    'b.updated_at as bank_updated_at',
                    'pt.payment_term',
                    'pt.status as payment_term_status',
                    'pt.created_at as payment_term_created_at',
                    'pt.updated_at as payment_term_updated_at'
                )
                ->get();
        }

        $emails = DB::table('email')->where('status', 1)->pluck('email')->all();

        return response()->json([
            'shop_name' => $shopOrderTransactionList->count() !== 0 ? $shopOrderTransactionList[0]->shop_name : '',
            'emails' => $emails,
            'total_sales_completed' => $totalSalesCompleted ?: 0,
            'total_profit' => $totalProfit ?: 0,
            'total_count' => $shopOrderTransactionList->count(),
            'total_cash' => $paymentSummary->total_cash ?? 0,
            'total_cash_prev' => $paymentSummary->total_cash_prev ?? 0,
            'total_cash_outdated' => $outdatedPaymentSummary->total_cash_outdated ?? 0,
            'total_online' => $paymentSummary->total_online ?? 0,
            'total_online_prev' => $paymentSummary->total_online_prev ?? 0,
            'total_online_outdated' => $outdatedPaymentSummary->total_online_outdated ?? 0,
            'total_paid' => $totalPaid ?: 0,
            'total_paid_prev' => $totalPaidPrev ?: 0,
            'total_paid_outdated' => $outdatedPaymentSummary->total_paid_outdated ?? 0,
            'data' => $shopOrderTransactionList,
            'payment' => $paymentTypes,
            'code' => 200,
            'date' => $date,
            'message' => 'Successfully fetched online shop order transactions',
        ]);
    }

    public function fetchOnlineShopOrderTransactionListByIdV2($id, Request $request)
    {
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            return response()->json([
                'code' => 422,
                'message' => 'The transaction ID must be a positive integer.',
            ], 422);
        }

        $transaction = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->where('shop.shop_type_id', 3)
            ->where('sot.id', (int) $id)
            ->select('sot.id', 'sot.date')
            ->first();

        if (!$transaction) {
            return response()->json([
                'code' => 404,
                'message' => 'Online shop order transaction not found.',
            ], 404);
        }

        $request->merge(['date' => $transaction->date]);
        $request->attributes->set('transaction_id_filter', $transaction->id);

        return $this->fetchOnlineShopOrderTransactionListV2($request);
    }

            public function fetctPendingProductOrderTransaction($id, Request $request)
        {
            $status   = $request->input('status');
            $queryDate = function ($query) use ($request) {
                if ($request->filled('dateFrom') && $request->filled('dateTo')) {
                    $query->whereBetween('sot.date', [$request->input('dateFrom'), $request->input('dateTo')]);
                }
            };

            // Main transaction list
            $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->join('products as p', 'p.id', '=', 'mup.product_id')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')
                ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
                ->select(
                    'shop.shop_name', 'sot.id', 'sot.shop_order_transaction_total_quantity',
                    'sot.shop_order_transaction_total_price', 'sot.created_at',
                    'sot.updated_at', 'sot.is_pickup', 'shop.shop_type_id',
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'sot.checker', 'sot.requestor',
                    'sot.status', 'sot.date', 'sot.profit', 'sot.total_cash',
                    'sot.total_online', 'ct.customer_type', 'sot.rider_name',
                    'so.shop_order_quantity', 'mup.business_type', 'p.quantity'
                )
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('shop.shop_type_id', 3)
                ->where('sot.is_pickup', 0)
                ->where('so.product_id', $id)
                ->when(true, $queryDate)
                ->orderByDesc('sot.id')
                ->get();

            // Summary data
            $data = DB::table('shop_order_transaction as sot')
                ->selectRaw('
                    SUM(shop_order_transaction_total_price) as total_price,
                    SUM(profit) as total_profit,
                    COUNT(shop_id) as total_count
                ')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->where('shop.shop_type_id', 3)
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('so.product_id', $id)
                ->where('sot.is_pickup', 0)
                ->when(true, $queryDate)
                ->first();

            // Cash totals
            $cash = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_cash')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('so.product_id', $id)
                ->where('sot.is_pickup', 0)
                ->where('pt.type', 1)
                ->when(true, $queryDate)
                ->first();

            // Online totals
            $online = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_online')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('so.product_id', $id)
                ->where('sot.is_pickup', 0)
                ->where('pt.type', 2)
                ->when(true, $queryDate)
                ->first();

            // Payment breakdown
            $payment_type = DB::table('shop_order_transaction as sot')
                ->selectRaw('
                    SUM(mop.amount) as total_amount,
                    SUM(mop.is_paid) as total_paid_count,
                    COUNT(mop.id) as total_count,
                    pt.payment_type, pt.payment_type_description, pt.id
                ')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('so.product_id', $id)
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('sot.is_pickup', 0)
                ->when(true, $queryDate)
                ->groupBy('pt.id')
                ->get();

            // Total count
            $total = DB::table('shop_order_transaction as sot')
                ->selectRaw('COUNT(shop_id) as total_count')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($query) use ($status) {
                        $query->where('sot.status', (int) $status);
                    }
                )
                ->where('sot.is_pickup', 0)
                ->when(true, $queryDate)
                ->first();

            // Add mode of payment and quantity calc
            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->total_order_quantity = $sotl->business_type === 'WHOLESALE'
                    ? $sotl->shop_order_quantity * $sotl->quantity
                    : $sotl->shop_order_quantity;

                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                    ->where('pt.id', '!=', 1)
                    ->where('mop.shop_order_transaction_id', $sotl->id)
                    ->get();
            }

            // Build response
            $response = [
                'shop_name'   => $shop_order_transaction_list->count() ? $shop_order_transaction_list[0]->shop_name : '',
                'total_price' => $data->total_price ?? 0,
                'total_profit'=> $data->total_profit ?? 0,
                'total_count' => $total->total_count ?? 0,
                'total_cash'  => $cash->total_cash ?? 0,
                'total_online'=> $online->total_online ?? 0,
                'data'        => $shop_order_transaction_list,
                'payment'     => $payment_type,
                'code'        => 200,
                'date'        => date('Y-m-d'),
                'message'     => "Successfully Added"
            ];

            return response()->json($response);
        }


        public function fetctProductOrderTransaction($id, Request $request)
        {
            $isPickup = $request->input('is_pickup');
            $status = $request->input('status');

            $applyFilters = function ($query) use ($request, $isPickup, $status) {
                if ($request->filled('dateFrom') && $request->filled('dateTo')) {
                    $query->whereBetween('sot.date', [$request->input('dateFrom'), $request->input('dateTo')]);
                }

                if ($isPickup !== null && $isPickup !== '' && $isPickup !== 'null') {
                    $query->where('sot.is_pickup', (int) $isPickup);
                }

                if ($status !== null && $status !== '' && $status !== 'null') {
                    $query->where('sot.status', (int) $status);
                }
            };

            // Main transaction list
            $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->join('products as p', 'p.id', '=', 'mup.product_id')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')
                ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
                ->select(
                    'shop.shop_name', 'sot.id', 'sot.shop_order_transaction_total_quantity',
                    'sot.shop_order_transaction_total_price', 'sot.created_at',
                    'sot.updated_at', 'sot.is_pickup', 'shop.shop_type_id',
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'sot.checker', 'sot.requestor',
                    'sot.status', 'sot.date', 'sot.profit', 'sot.total_cash',
                    'sot.total_online', 'ct.customer_type', 'sot.rider_name',
                    'so.shop_order_quantity', 'mup.business_type', 'p.quantity'
                )
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->when(true, $applyFilters)
                ->orderByDesc('sot.id')
                ->get();

            // Summary data
            $data = DB::table('shop_order_transaction as sot')
                ->selectRaw('
                    SUM(shop_order_transaction_total_price) as total_price,
                    SUM(profit) as total_profit,
                    COUNT(shop_id) as total_count
                ')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->when(true, $applyFilters)
                ->first();

            // Cash totals
            $cash = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_cash')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->where('pt.type', 1)
                ->when(true, $applyFilters)
                ->first();

            // Online totals
            $online = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_online')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->where('pt.type', 2)
                ->when(true, $applyFilters)
                ->first();

            // Payment breakdown
            $payment_type = DB::table('shop_order_transaction as sot')
                ->selectRaw('
                    SUM(mop.amount) as total_amount,
                    SUM(mop.is_paid) as total_paid_count,
                    COUNT(mop.id) as total_count,
                    pt.payment_type, pt.payment_type_description, pt.id
                ')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('so.product_id', $id)
                ->when(true, $applyFilters)
                ->groupBy('pt.id')
                ->get();

            // Total count
            $total = DB::table('shop_order_transaction as sot')
                ->selectRaw('COUNT(shop_id) as total_count')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->where('shop.shop_type_id', 3)
                ->where('so.product_id', $id)
                ->when(true, $applyFilters)
                ->first();

            // Add mode of payment and quantity calc
            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->total_order_quantity = $sotl->business_type === 'WHOLESALE'
                    ? $sotl->shop_order_quantity * $sotl->quantity
                    : $sotl->shop_order_quantity;

                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                    ->where('pt.id', '!=', 1)
                    ->where('mop.shop_order_transaction_id', $sotl->id)
                    ->get();
            }

            // Build response
            $response = [
                'shop_name'   => $shop_order_transaction_list->count() ? $shop_order_transaction_list[0]->shop_name : '',
                'total_price' => $data->total_price ?? 0,
                'total_profit'=> $data->total_profit ?? 0,
                'total_count' => $total->total_count ?? 0,
                'total_cash'  => $cash->total_cash ?? 0,
                'total_online'=> $online->total_online ?? 0,
                'data'        => $shop_order_transaction_list,
                'payment'     => $payment_type,
                'code'        => 200,
                'date'        => date('Y-m-d'),
                'message'     => "Successfully Added"
            ];

            return response()->json($response);
        }

        /**
         * Return a product's sold quantity grouped into chart-friendly periods.
         * Orders are normalized to stock_pc first, then converted to stock units.
         */
        public function fetchProductSoldHistory($id, Request $request)
        {
            $validated = $request->validate([
                'dateFrom' => ['required', 'date_format:Y-m-d'],
                'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
                'groupBy' => ['required', 'in:day,week,month,year'],
                'type' => ['nullable', 'integer', 'in:0,1'],
            ]);

            $product = DB::table('products')
                ->select('id', 'product_name', 'weight', 'quantity')
                ->where('id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Product not found.',
                ], 404);
            }

            $dateFrom = Carbon::createFromFormat('Y-m-d', $validated['dateFrom'])->startOfDay();
            $dateTo = Carbon::createFromFormat('Y-m-d', $validated['dateTo'])->startOfDay();
            $groupBy = $validated['groupBy'];
            $type = isset($validated['type']) ? (int) $validated['type'] : null;

            // Aggregate by date in SQL first, then combine dates into the requested
            // period in PHP. This keeps the endpoint compatible across DB drivers.
            $dailySales = DB::table('shop_order_transaction as sot')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->join('products as p', 'p.id', '=', 'mup.product_id')
                ->when($type !== null, function ($query) use ($type) {
                    if ($type === 0) {
                        $query->where('shop.shop_type_id', 3);
                    } else {
                        $query->where('shop.shop_type_id', '!=', 3);
                    }
                })
                ->where('so.product_id', $id)
                ->where('sot.status', 1)
                ->when($type !== null, function ($query) use ($type) {
                    $query->where('sot.type', $type);
                })
                ->whereBetween('sot.date', [$validated['dateFrom'], $validated['dateTo']])
                ->groupBy('sot.date')
                ->orderBy('sot.date')
                ->selectRaw("sot.date,
                    SUM(CASE
                        WHEN mup.business_type = 'WHOLESALE'
                            THEN so.shop_order_quantity * p.quantity
                        ELSE so.shop_order_quantity
                    END) as sold_stock_pc")
                ->get();

            $salesByPeriod = [];
            foreach ($dailySales as $sale) {
                $periodKey = $this->soldHistoryPeriodStart(Carbon::parse($sale->date), $groupBy)
                    ->format('Y-m-d');

                if (!isset($salesByPeriod[$periodKey])) {
                    $salesByPeriod[$periodKey] = ['sold_stock_pc' => 0];
                }

                $salesByPeriod[$periodKey]['sold_stock_pc'] += (float) $sale->sold_stock_pc;
            }

            $history = [];
            $cursor = $this->soldHistoryPeriodStart($dateFrom->copy(), $groupBy);
            $lastPeriod = $this->soldHistoryPeriodStart($dateTo->copy(), $groupBy);

            while ($cursor->lte($lastPeriod)) {
                $periodStart = $cursor->copy();
                $periodEnd = $this->soldHistoryPeriodEnd($periodStart->copy(), $groupBy);
                $key = $periodStart->format('Y-m-d');
                $soldStockPc = round($salesByPeriod[$key]['sold_stock_pc'] ?? 0, 4);
                $soldStock = round($soldStockPc / max((int) $product->quantity, 1), 4);

                $history[] = [
                    'period' => $key,
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d'),
                    'sold_stock_pc' => $soldStockPc,
                    'sold_quantity' => $soldStock,
                    'total_order_quantity' => $soldStock,
                ];

                $cursor = $this->incrementSoldHistoryPeriod($cursor, $groupBy);
            }

            return response()->json([
                'product_id' => (int) $product->id,
                'product_name' => $product->product_name,
                'product_weight' => (float) $product->weight,
                'pieces_per_box' => (int) $product->quantity,
                'weight_per_piece' => $product->quantity > 1 && $product->weight > 0
                    ? round($product->weight / $product->quantity, 4)
                    : (float) $product->weight,
                'date_from' => $validated['dateFrom'],
                'date_to' => $validated['dateTo'],
                'group_by' => $groupBy,
                'type' => $type,
                'quantity_unit' => 'stock',
                'total_sold_stock_pc' => round(collect($history)->sum('sold_stock_pc'), 4),
                'total_sold_quantity' => round(collect($history)->sum('sold_quantity'), 4),
                'data' => $history,
                'code' => 200,
                'message' => 'Product sold history fetched successfully.',
            ]);
        }

        private function soldHistoryPeriodStart(Carbon $date, string $groupBy): Carbon
        {
            if ($groupBy === 'week') {
                return $date->startOfWeek(Carbon::MONDAY);
            }

            if ($groupBy === 'month') {
                return $date->startOfMonth();
            }

            if ($groupBy === 'year') {
                return $date->startOfYear();
            }

            return $date->startOfDay();
        }

        private function soldHistoryPeriodEnd(Carbon $date, string $groupBy): Carbon
        {
            if ($groupBy === 'week') {
                return $date->endOfWeek(Carbon::SUNDAY);
            }

            if ($groupBy === 'month') {
                return $date->endOfMonth();
            }

            if ($groupBy === 'year') {
                return $date->endOfYear();
            }

            return $date->endOfDay();
        }

        private function incrementSoldHistoryPeriod(Carbon $date, string $groupBy): Carbon
        {
            if ($groupBy === 'week') {
                return $date->addWeek();
            }

            if ($groupBy === 'month') {
                return $date->addMonth();
            }

            if ($groupBy === 'year') {
                return $date->addYear();
            }

            return $date->addDay();
        }

     public function fetchPendingPickUp(Request $request)
        {
            $id = $request->input('is_pickup_status'); // or your pickup id source
            $dateFrom = $request->input('dateFrom');
            $dateTo = $request->input('dateTo');
            $status = $request->input('status');
            $categoryId = $request->input('category_id');

            $applyFilters = function ($query) use ($dateFrom, $dateTo, $status, $categoryId) {
                $query->when(
                    !empty($dateFrom) && !empty($dateTo) && $dateFrom !== 'null' && $dateTo !== 'null',
                    function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('shop_order_transaction.date', [
                            $dateFrom,
                            $dateTo
                        ]);
                    }
                );

                $query->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($q) use ($status) {
                        $q->where('shop_order_transaction.status', $status);
                    }
                );

                $query->when(
                    $categoryId !== null && $categoryId !== '' && $categoryId !== 'null',
                    function ($q) use ($categoryId) {
                        $q->whereExists(function ($categoryQuery) use ($categoryId) {
                            $categoryQuery->select(DB::raw(1))
                                ->from('shop_order as category_so')
                                ->join('mark_up_product as category_mup', 'category_mup.id', '=', 'category_so.mark_up_product_id')
                                ->join('products as category_product', 'category_product.id', '=', 'category_mup.product_id')
                                ->join('category as category_filter', 'category_filter.id', '=', 'category_product.category_id')
                                ->whereColumn('category_so.shop_transaction_id', 'shop_order_transaction.id')
                                ->where('category_filter.id', $categoryId);
                        });
                    }
                );
            };

            $shop_order_transaction_list = DB::table('shop_order_transaction')
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
                ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
                ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'shop_order_transaction.id')
                ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'shop_order_transaction.requestor')
                ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
                ->leftJoin('shop_order as so', 'so.shop_transaction_id', '=', 'shop_order_transaction.id')
                ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->leftJoin('products as p', 'p.id', '=', 'mup.product_id')
                ->leftJoin('category as category', 'category.id', '=', 'p.category_id')
                ->select(
                    'shop_order_transaction.id',
                    'shop_order_transaction.shop_order_transaction_total_quantity',
                    'shop_order_transaction.shop_order_transaction_total_price',
                    'shop_order_transaction.created_at',
                    'shop_order_transaction.updated_at',
                    'shop_order_transaction.is_pickup',
                    'shop.shop_name',
                    'shop.shop_type_id',
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name',
                    'shop_order_transaction.checker',
                    'shop_order_transaction.requestor',
                    'shop_order_transaction.status',
                    'shop_order_transaction.date',
                    'shop_order_transaction.profit',
                    'shop_order_transaction.total_cash',
                    'shop_order_transaction.total_online',
                    'ct.customer_type',
                    'shop_order_transaction.rider_name',
                    'shop_order_transaction.delivery_customer_id',
                    'ds.status as delivery_status',
                    DB::raw("GROUP_CONCAT(DISTINCT NULLIF(category.tags, '')) as tags"),
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
                )
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.is_pickup', $id)
                ->when(true, $applyFilters)
                ->groupBy('shop_order_transaction.id')
                ->orderBy('shop_order_transaction.id', 'DESC')
                ->get();

            foreach ($shop_order_transaction_list as $sotl) {
                $vipCustomers = [];

                if ($sotl->vip_customer_list != '') {
                    foreach (explode('||', $sotl->vip_customer_list) as $vipCustomer) {
                        $vipCustomerDetails = explode('::', $vipCustomer);

                        if (isset($vipCustomerDetails[0]) && $vipCustomerDetails[0] != '') {
                            $vipCustomers[] = [
                                'vip_customer_transaction_id' => $vipCustomerDetails[0],
                                'vip_customer_id' => isset($vipCustomerDetails[1]) ? $vipCustomerDetails[1] : '',
                                'vip_name' => isset($vipCustomerDetails[2]) ? $vipCustomerDetails[2] : '',
                                'vip_color' => isset($vipCustomerDetails[3]) ? $vipCustomerDetails[3] : '',
                            ];
                        }
                    }
                }

                $sotl->vip_customers = $vipCustomers;
                unset($sotl->vip_customer_list);
            }

            $data = DB::table('shop_order_transaction')
                ->select(
                    DB::raw('SUM(shop_order_transaction_total_price) as total_price'),
                    DB::raw('SUM(profit) as total_profit')
                )
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.is_pickup', $id)
                ->when(true, $applyFilters)
                ->first();

            $cash = DB::table('shop_order_transaction')
                ->select(DB::raw('SUM(mop.amount) as total_cash'))
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.is_pickup', $id)
                ->where('pt.type', 1)
                ->when(true, $applyFilters)
                ->first();

            $online = DB::table('shop_order_transaction')
                ->select(DB::raw('SUM(mop.amount) as total_online'))
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.is_pickup', $id)
                ->where('pt.type', 2)
                ->when(true, $applyFilters)
                ->first();

            $payment_type = DB::table('shop_order_transaction as sot')
                ->select(
                    DB::raw('SUM(mop.amount) as total_amount'),
                    'pt.payment_type',
                    'pt.payment_type_description',
                    'pt.id'
                )
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('shop.shop_type_id', 3)
                ->where('sot.is_pickup', $id)
                ->when(
                    !empty($dateFrom) && !empty($dateTo) && $dateFrom !== 'null' && $dateTo !== 'null',
                    function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('sot.date', [
                            Carbon::parse($dateFrom)->format('Y-m-d'),
                            Carbon::parse($dateTo)->format('Y-m-d')
                        ]);
                    }
                )
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($q) use ($status) {
                        $q->where('sot.status', $status);
                    }
                )
                ->when(
                    $categoryId !== null && $categoryId !== '' && $categoryId !== 'null',
                    function ($q) use ($categoryId) {
                        $q->whereExists(function ($categoryQuery) use ($categoryId) {
                            $categoryQuery->select(DB::raw(1))
                                ->from('shop_order as category_so')
                                ->join('mark_up_product as category_mup', 'category_mup.id', '=', 'category_so.mark_up_product_id')
                                ->join('products as category_product', 'category_product.id', '=', 'category_mup.product_id')
                                ->join('category as category_filter', 'category_filter.id', '=', 'category_product.category_id')
                                ->whereColumn('category_so.shop_transaction_id', 'sot.id')
                                ->where('category_filter.id', $categoryId);
                        });
                    }
                )
                ->groupBy('pt.id', 'pt.payment_type', 'pt.payment_type_description')
                ->get();

            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                    ->where('pt.id', '!=', 1)
                    ->where('mop.shop_order_transaction_id', $sotl->id)
                    ->get();
            }

            return response()->json([
                'total_price' => $data->total_price ?? 0,
                'total_profit' => $data->total_profit ?? 0,
                'total_cash' => $cash->total_cash ?? 0,
                'total_online' => $online->total_online ?? 0,
                'data' => $shop_order_transaction_list,
                'payment' => $payment_type,
                'code' => 200,
                'date' => date('Y-m-d'),
                'message' => "Successfully Added"
            ]);
        }


     public function fetchPendingTransactionList(Request $request)
        {
            $id = $request->input('status'); // or your pickup id source
            $dateFrom = $request->input('dateFrom');
            $dateTo = $request->input('dateTo');
            $status = $request->input('is_pickup_status');

            $applyFilters = function ($query) use ($dateFrom, $dateTo, $status) {
                $query->when(
                    !empty($dateFrom) && !empty($dateTo) && $dateFrom !== 'null' && $dateTo !== 'null',
                    function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('shop_order_transaction.date', [
                            $dateFrom,
                            $dateTo
                        ]);
                    }
                );

                $query->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($q) use ($status) {
                        $q->where('shop_order_transaction.is_pickup', $status);
                    }
                );
            };

            $shop_order_transaction_list = DB::table('shop_order_transaction')
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
                ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
                ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'shop_order_transaction.id')
                ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'shop_order_transaction.requestor')
                ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
                ->leftJoin('shop_order as so', 'so.shop_transaction_id', '=', 'shop_order_transaction.id')
                ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
                ->leftJoin('products as p', 'p.id', '=', 'mup.product_id')
                ->leftJoin('category as category', 'category.id', '=', 'p.category_id')
                ->select(
                    'shop_order_transaction.id',
                    'shop_order_transaction.shop_order_transaction_total_quantity',
                    'shop_order_transaction.shop_order_transaction_total_price',
                    'shop_order_transaction.created_at',
                    'shop_order_transaction.updated_at',
                    'shop_order_transaction.is_pickup',
                    'shop.shop_name',
                    'shop.shop_type_id',
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name',
                    'shop_order_transaction.checker',
                    'shop_order_transaction.requestor',
                    'shop_order_transaction.status',
                    'shop_order_transaction.date',
                    'shop_order_transaction.profit',
                    'shop_order_transaction.total_cash',
                    'shop_order_transaction.total_online',
                    'ct.customer_type',
                    'shop_order_transaction.rider_name',
                    'shop_order_transaction.delivery_customer_id',
                    'ds.status as delivery_status',
                    DB::raw("GROUP_CONCAT(DISTINCT NULLIF(category.tags, '')) as tags"),
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
                )
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.status', $id)
                ->when(true, $applyFilters)
                ->groupBy('shop_order_transaction.id')
                ->orderBy('shop_order_transaction.id', 'DESC')
                ->get();

            foreach ($shop_order_transaction_list as $sotl) {
                $vipCustomers = [];

                if ($sotl->vip_customer_list != '') {
                    foreach (explode('||', $sotl->vip_customer_list) as $vipCustomer) {
                        $vipCustomerDetails = explode('::', $vipCustomer);

                        if (isset($vipCustomerDetails[0]) && $vipCustomerDetails[0] != '') {
                            $vipCustomers[] = [
                                'vip_customer_transaction_id' => $vipCustomerDetails[0],
                                'vip_customer_id' => isset($vipCustomerDetails[1]) ? $vipCustomerDetails[1] : '',
                                'vip_name' => isset($vipCustomerDetails[2]) ? $vipCustomerDetails[2] : '',
                                'vip_color' => isset($vipCustomerDetails[3]) ? $vipCustomerDetails[3] : '',
                            ];
                        }
                    }
                }

                $sotl->vip_customers = $vipCustomers;
                unset($sotl->vip_customer_list);
            }

            $data = DB::table('shop_order_transaction')
                ->select(
                    DB::raw('SUM(shop_order_transaction_total_price) as total_price'),
                    DB::raw('SUM(profit) as total_profit')
                )
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.status', $id)
                ->when(true, $applyFilters)
                ->first();

            $cash = DB::table('shop_order_transaction')
                ->select(DB::raw('SUM(mop.amount) as total_cash'))
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.status', $id)
                ->where('pt.type', 1)
                ->when(true, $applyFilters)
                ->first();

            $online = DB::table('shop_order_transaction')
                ->select(DB::raw('SUM(mop.amount) as total_online'))
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.status', $id)
                ->where('pt.type', 2)
                ->when(true, $applyFilters)
                ->first();

            $payment_type = DB::table('shop_order_transaction as sot')
                ->select(
                    DB::raw('SUM(mop.amount) as total_amount'),
                    'pt.payment_type',
                    'pt.payment_type_description',
                    'pt.id'
                )
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('shop.shop_type_id', 3)
                ->where('sot.status', $id)
                ->when(
                    !empty($dateFrom) && !empty($dateTo) && $dateFrom !== 'null' && $dateTo !== 'null',
                    function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('sot.date', [
                            Carbon::parse($dateFrom)->format('Y-m-d'),
                            Carbon::parse($dateTo)->format('Y-m-d')
                        ]);
                    }
                )
                ->when(
                    $status !== null && $status !== '' && $status !== 'null',
                    function ($q) use ($status) {
                        $q->where('sot.status', $status);
                    }
                )
                ->groupBy('pt.id', 'pt.payment_type', 'pt.payment_type_description')
                ->get();

            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                    ->where('pt.id', '!=', 1)
                    ->where('mop.shop_order_transaction_id', $sotl->id)
                    ->get();
            }

            return response()->json([
                'total_price' => $data->total_price ?? 0,
                'total_profit' => $data->total_profit ?? 0,
                'total_cash' => $cash->total_cash ?? 0,
                'total_online' => $online->total_online ?? 0,
                'data' => $shop_order_transaction_list,
                'payment' => $payment_type,
                'code' => 200,
                'date' => date('Y-m-d'),
                'message' => "Successfully Added"
            ]);
        }


      public function fetchDeliveryTransaction(Request $request)
    {
      if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
        $currentTime = Carbon::now('GMT+8');
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name'
              , 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
            //  ->where('ds.delivery_status', $request->input('status'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();

            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            // ->where('ds.delivery_status', $request->input('status'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            // ->where('ds.delivery_status', $request->input('status'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            // ->where('ds.delivery_status', $request->input('status'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            // ->where('ds.delivery_status', $request->input('status'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }     
            
        } else {

                    $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name'
              , 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();

            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
             ->where('sot.date', '>=', $request->input('dateFrom'))
             ->where('sot.date', '<=', $request->input('dateTo'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }    
            
        }


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

     public function fetchPendingDeliveryTransaction(Request $request)
    {
     if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name'
              , 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
             ->where('ds.status', $request->input('status'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();

            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            ->where('ds.status', $request->input('status'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }           

        } else {
                    $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name'
              , 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
             ->where('ds.status', $request->input('status'))
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();

            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))                        
            ->where('ds.status', $request->input('status'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))                         
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            ->where('ds.status', $request->input('status'))
            ->where('sot.date', '>=', $request->input('dateFrom'))
             ->where('sot.date', '<=', $request->input('dateTo'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            } 

        }

           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

        public function fetchPrev($id, $today, $type)
    {
        $conditions = function ($q) use ($type, $today) {

            if ($type == 1) {
                // TYPE 1
                $q->where('mop.created_at', $today)
                ->where('sot.date', '<', $today);
            } else {
                // TYPE OTHER
                $q->where('mop.created_at', '>', $today)
                ->where('sot.date', $today);
            }

        };

        // --------------------------
        // Main transaction list
        // --------------------------
        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->select(
                'mop.id', 'mop.amount', 'sot.id as shop_order_transaction_id',
                'sot.shop_order_transaction_total_quantity',
                'sot.shop_order_transaction_total_price',
                'sot.created_at', 'sot.is_pickup', 'shop.shop_name',
                'shop.shop_type_id', DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name',
                'sot.checker', 'sot.requestor', 'sot.status', 'sot.updated_at',
                'sot.date', 'sot.profit', 'mop.is_paid', 'sot.total_cash',
                'sot.total_online', 'ct.customer_type', 'sot.rider_name',
                'pt.payment_type', 'pt.payment_type_description'
            )
            ->where('shop.shop_type_id', 3)
            ->where($conditions)
            ->where('pt.type', $id)
            ->orderBy('sot.id', 'DESC')
            ->get();

        // --------------------------
        // SUMMARY: totals & profit
        // --------------------------
        $data = DB::table('shop_order_transaction as sot')
            ->select(
                DB::raw('SUM(shop_order_transaction_total_price) as total_price'),
                DB::raw('SUM(profit) as total_profit'),
                DB::raw('SUM(mop.amount) as total_amount')
            )
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('shop.shop_type_id', 3)
            ->where($conditions)
            ->where('pt.type', $id)
            ->first();

        foreach ($shop_order_transaction_list as $sotl) {
            $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->select($this->modeOfPaymentSelect(true))
                ->where('mop.shop_order_transaction_id', $sotl->shop_order_transaction_id)
                ->where('mop.payment_type_id', $id)
                ->get();
        }

        // --------------------------
        // RESPONSE
        // --------------------------
        return response()->json([
            'total_amount' => $data->total_amount ?? 0,
            'data' => $shop_order_transaction_list,
            'code' => 2020,
            'date' => $today,
            'id' => $id,
            'message' => "Successfully Loaded"
        ]);
    }


        
       public function fetchOnlineShopOrderTransactionListByIdDate($id, $date)
    {
        $currentTime = date('Y-m-d');
        $newDate = '';
        if ($date == 0) {
            $newDate = $currentTime;
        } else {
            $newDate =$date;
        }
        
        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->select('mop.id', 'sot.id as transaction_id', 'mop.amount','sot.id as shop_order_transaction_id','sot.shop_order_transaction_total_quantity',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at', 'sot.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'sot.checker', 'sot.requestor',
              'sot.status', 'sot.date', 'sot.profit', 'mop.is_paid',
              'sot.total_cash', 'sot.total_online', 'ct.customer_type', 'sot.rider_name')    
             ->where('shop.shop_type_id', 3)
             ->where('mop.created_at', $newDate)
             ->where('mop.payment_type_id', $id)
             ->orderBy('sot.id', 'DESC')
             ->get();


            
            $data = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $newDate)
            ->where('mop.payment_type_id', $id)
            ->first();


           $cash = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $newDate)
            ->where('pt.type', 1)
            ->where('mop.payment_type_id', $id)
            ->first();

            $online = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $newDate)
            ->where('pt.type', 2)
            ->where('mop.payment_type_id', $id)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
           ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
           ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('SUM(mop.is_paid) as total_paid_count'), 'pt.payment_type',  'pt.payment_type_description')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('mop.created_at', $newDate)
            // ->where('sot.status', 1)
            ->where('mop.payment_type_id', $id)
            ->groupBy('pt.id')
            ->first();

           $total = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('COUNT(shop_id) as total_count'),)  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $newDate)
            ->where('mop.payment_type_id', $id)
            ->first();


            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect(true))
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                // ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->shop_order_transaction_id)
                ->where('mop.payment_type_id', $id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }            


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_count' =>$total->total_count,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 2020,
              'date' =>$newDate,
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchOnlineShopOrderTransactionListByIdDateV2($id, $date)
    {
        $newDate = $date == 0 ? date('Y-m-d') : $date;

        $shopOrderTransactionList = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $newDate)
            ->where('ptp.id', $id)
            ->orderBy('sot.id', 'DESC')
            ->select(
                'mop.id',
                'sot.id as transaction_id',
                'mop.amount',
                'mop.is_paid',
                'mop.created_at as payment_created_at',
                'mop.updated_at as payment_updated_at',
                'sot.id as shop_order_transaction_id',
                'sot.shop_order_transaction_total_quantity',
                'sot.shop_order_transaction_total_price',
                'sot.created_at',
                'sot.updated_at',
                'sot.is_pickup',
                'shop.shop_name',
                'shop.shop_type_id',
                DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"),
                'c.store_name',
                'sot.checker',
                'sot.requestor',
                'sot.status',
                'sot.date',
                'sot.profit',
                'sot.total_cash',
                'sot.total_online',
                'ct.customer_type',
                'sot.rider_name',
                'ptp.id as payment_type_po_id',
                'ptp.payment_term_id',
                'ptp.bank_id',
                'ptp.account_number',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.due_date',
                'ptp.buffer_days',
                'ptp.credit_limit',
                'ptp.statement_date',
                'ptp.total_balance_due',
                'ptp.balance',
                'ptp.status as payment_type_po_status',
                'ptp.is_supplier',
                'ptp.is_customer',
                'ptp.is_supplier',
                'ptp.is_customer',
                'ptp.created_at as payment_type_po_created_at',
                'ptp.updated_at as payment_type_po_updated_at',
                'b.bank_name',
                'b.status as bank_status',
                'b.created_at as bank_created_at',
                'b.updated_at as bank_updated_at',
                'pt.payment_term',
                'pt.status as payment_term_status',
                'pt.created_at as payment_term_created_at',
                'pt.updated_at as payment_term_updated_at'
            )
            ->get();

        $totals = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $newDate)
            ->where('ptp.id', $id)
            ->selectRaw(
                'SUM(sot.shop_order_transaction_total_price) as total_price,
                 SUM(sot.profit) as total_profit,
                 SUM(CASE WHEN ptp.payment_term_id = 1 THEN mop.amount ELSE 0 END) as total_cash,
                 SUM(CASE WHEN ptp.payment_term_id <> 1 THEN mop.amount ELSE 0 END) as total_online,
                 COUNT(sot.shop_id) as total_count'
            )
            ->first();

        $paymentType = DB::table('shop_order_transaction as sot')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->where('mop.created_at', $newDate)
            ->where('ptp.id', $id)
            ->select(
                DB::raw('SUM(mop.amount) as total_amount'),
                DB::raw('SUM(mop.is_paid) as total_paid_count'),
                DB::raw('COUNT(mop.id) as total_count'),
                'ptp.id as payment_type_po_id',
                'ptp.payment_term_id',
                'ptp.bank_id',
                'ptp.account_number',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.due_date',
                'ptp.buffer_days',
                'ptp.credit_limit',
                'ptp.statement_date',
                'ptp.total_balance_due',
                'ptp.balance',
                'ptp.status as payment_type_po_status',
                'ptp.created_at as payment_type_po_created_at',
                'ptp.updated_at as payment_type_po_updated_at',
                'b.bank_name',
                'b.status as bank_status',
                'b.created_at as bank_created_at',
                'b.updated_at as bank_updated_at',
                'pt.payment_term',
                'pt.status as payment_term_status',
                'pt.created_at as payment_term_created_at',
                'pt.updated_at as payment_term_updated_at'
            )
            ->groupBy('ptp.id')
            ->first();

        foreach ($shopOrderTransactionList as $shopOrderTransaction) {
            $shopOrderTransaction->mode_of_payment = DB::table('mode_of_payment as mop')
                ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
                ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
                ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
                ->where('mop.shop_order_transaction_id', $shopOrderTransaction->shop_order_transaction_id)
                ->where('ptp.id', $id)
                ->select(
                    'mop.id',
                    'mop.payment_type_id',
                    'mop.amount',
                    'mop.is_paid',
                    'mop.shop_order_transaction_id',
                    'mop.created_at',
                    'mop.updated_at',
                    'ptp.id as payment_type_po_id',
                    'ptp.payment_term_id',
                    'ptp.bank_id',
                    'ptp.account_number',
                    'ptp.account_name',
                    'ptp.account_description',
                    'ptp.due_date',
                    'ptp.buffer_days',
                    'ptp.credit_limit',
                    'ptp.statement_date',
                    'ptp.total_balance_due',
                    'ptp.balance',
                    'ptp.status as payment_type_po_status',
                    'ptp.is_supplier',
                    'ptp.is_customer',
                    'b.bank_name',
                    'b.status as bank_status',
                    'pt.payment_term',
                    'pt.status as payment_term_status'
                )
                ->get();
        }

        return response()->json([
            'total_price' => $totals->total_price ?? 0,
            'total_profit' => $totals->total_profit ?? 0,
            'total_cash' => $totals->total_cash ?? 0,
            'total_count' => $totals->total_count ?? 0,
            'total_online' => $totals->total_online ?? 0,
            'data' => $shopOrderTransactionList,
            'payment' => $paymentType,
            'code' => 2020,
            'date' => $newDate,
            'id' => $id,
            'message' => 'Successfully fetched payment account transactions',
        ]);
    }

    public function fetchOnlineShopOrderTransactionListByDateRangeV2(Request $request)
    {
        $validated = $request->validate([
            'dateFrom' => ['required', 'date_format:Y-m-d'],
            'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
            'payment_type_po_id' => ['required', 'integer', 'exists:payment_type_po,id'],
            'is_paid' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $dateFrom = Carbon::createFromFormat('Y-m-d', $validated['dateFrom'])->startOfDay();
        $dateTo = Carbon::createFromFormat('Y-m-d', $validated['dateTo'])->endOfDay();

        $transactions = DB::table('mode_of_payment as mop')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'mop.shop_order_transaction_id')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->where('shop.shop_type_id', 3)
            ->where('ptp.id', $validated['payment_type_po_id'])
            ->whereBetween('mop.created_at', [$dateFrom, $dateTo])
            ->when(
                array_key_exists('is_paid', $validated) && $validated['is_paid'] !== null,
                fn ($query) => $query->where('mop.is_paid', $validated['is_paid'])
            )
            ->orderByDesc('mop.created_at')
            ->orderByDesc('mop.id')
            ->select(
                'mop.id',
                'mop.amount',
                'mop.is_paid',
                'mop.created_at as payment_created_at',
                'mop.updated_at as payment_updated_at',
                'sot.id as shop_order_transaction_id',
                'sot.shop_order_transaction_total_quantity',
                'sot.shop_order_transaction_total_price',
                'sot.date as transaction_date',
                'sot.status as transaction_status',
                'sot.profit',
                'sot.is_pickup',
                'sot.rider_name',
                'shop.id as shop_id',
                'shop.shop_name',
                DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"),
                'c.store_name',
                'ct.customer_type',
                'ptp.id as payment_type_po_id',
                'ptp.payment_term_id',
                'ptp.bank_id',
                'ptp.account_number',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.due_date',
                'ptp.buffer_days',
                'ptp.credit_limit',
                'ptp.statement_date',
                'ptp.total_balance_due',
                'ptp.balance as payment_type_po_balance',
                'ptp.status as payment_type_po_status',
                'ptp.is_supplier',
                'ptp.is_customer',
                'ptp.created_at as payment_type_po_created_at',
                'ptp.updated_at as payment_type_po_updated_at',
                'b.bank_name',
                'b.status as bank_status',
                'b.created_at as bank_created_at',
                'b.updated_at as bank_updated_at',
                'pt.payment_term',
                'pt.status as payment_term_status'
            )
            ->get();

        $banks = $transactions
            ->whereNotNull('bank_id')
            ->unique('bank_id')
            ->map(fn ($transaction) => [
                'id' => $transaction->bank_id,
                'bank_name' => $transaction->bank_name,
                'account_number' => $transaction->account_number,
                'account_name' => $transaction->account_name,
                'account_description' => $transaction->account_description,
                'status' => $transaction->bank_status,
                'created_at' => $transaction->bank_created_at,
                'updated_at' => $transaction->bank_updated_at,
            ])
            ->values();

        $paymentTypePo = $transactions
            ->unique('payment_type_po_id')
            ->map(fn ($transaction) => [
                'id' => $transaction->payment_type_po_id,
                'payment_term_id' => $transaction->payment_term_id,
                'bank_id' => $transaction->bank_id,
                'account_number' => $transaction->account_number,
                'account_name' => $transaction->account_name,
                'account_description' => $transaction->account_description,
                'due_date' => $transaction->due_date,
                'buffer_days' => $transaction->buffer_days,
                'credit_limit' => $transaction->credit_limit,
                'statement_date' => $transaction->statement_date,
                'total_balance_due' => $transaction->total_balance_due,
                'balance' => $transaction->payment_type_po_balance,
                'status' => $transaction->payment_type_po_status,
                'is_supplier' => $transaction->is_supplier,
                'is_customer' => $transaction->is_customer,
                'payment_term' => $transaction->payment_term,
                'payment_term_status' => $transaction->payment_term_status,
                'created_at' => $transaction->payment_type_po_created_at,
                'updated_at' => $transaction->payment_type_po_updated_at,
            ])
            ->values();

        $accountDetailFields = [
            'payment_term_id', 'bank_id', 'account_number', 'account_name',
            'account_description', 'due_date', 'buffer_days', 'credit_limit',
            'statement_date', 'total_balance_due', 'payment_type_po_balance',
            'payment_type_po_status', 'is_supplier', 'is_customer',
            'payment_type_po_created_at', 'payment_type_po_updated_at',
            'bank_name', 'bank_status', 'bank_created_at', 'bank_updated_at',
            'payment_term', 'payment_term_status',
        ];

        $transactions->each(function ($transaction) use ($accountDetailFields) {
            foreach ($accountDetailFields as $field) {
                unset($transaction->{$field});
            }
        });

        $paymentAccounts = $transactions
            ->groupBy('payment_type_po_id')
            ->map(function ($accountTransactions) {
                $account = $accountTransactions->first();

                return [
                    'payment_type_po_id' => $account->payment_type_po_id,
                    'total_amount' => $accountTransactions->sum('amount'),
                    'paid_amount' => $accountTransactions->where('is_paid', 1)->sum('amount'),
                    'unpaid_amount' => $accountTransactions->where('is_paid', '!=', 1)->sum('amount'),
                    'transaction_count' => $accountTransactions->count(),
                    'transactions' => $accountTransactions->values(),
                ];
            })
            ->values();

        return response()->json([
            'dateFrom' => $validated['dateFrom'],
            'dateTo' => $validated['dateTo'],
            'payment_type_po_id' => $validated['payment_type_po_id'],
            'is_paid' => $validated['is_paid'] ?? null,
            'total_amount' => $transactions->sum('amount'),
            'total_paid_amount' => $transactions->where('is_paid', 1)->sum('amount'),
            'total_unpaid_amount' => $transactions->where('is_paid', '!=', 1)->sum('amount'),
            'total_count' => $transactions->count(),
            'payment_account_count' => $paymentAccounts->count(),
            'bank' => $banks->first(),
            'payment_type_po' => $paymentTypePo->first(),
            'data' => $paymentAccounts,
            'code' => 2020,
            'message' => 'Successfully fetched all incoming payment transactions',
        ]);
    }

    public function fetctPendingProductOrderTransactionV2($id, Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetctPendingProductOrderTransaction($id, $request);
    }

    public function fetctProductOrderTransactionV2($id, Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetctProductOrderTransaction($id, $request);
    }

    public function fetchPendingPickUpV2(Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetchPendingPickUp($request);
    }

    public function fetchPendingTransactionListV2(Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetchPendingTransactionList($request);
    }

    public function fetchDeliveryTransactionV2(Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetchDeliveryTransaction($request);
    }

    public function fetchPendingDeliveryTransactionV2(Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetchPendingDeliveryTransaction($request);
    }

    public function fetchPrevV2($id, $today, $type)
    {
        $this->usePaymentTypePo();
        return $this->fetchPrev($id, $today, $type);
    }

    public function fetchSalesListV2(Request $request)
    {
        $this->usePaymentTypePo();
        return $this->fetchSalesList($request);
    }

    public function fetchOnlineShopOrderTransactionListByDateV2($date)
    {
        $this->usePaymentTypePo();
        return $this->fetchOnlineShopOrderTransactionListByDate($date);
    }

    public function fetchOnlineShopOrderTransactionListByStatusV2($status)
    {
        $this->usePaymentTypePo();
        return $this->fetchOnlineShopOrderTransactionListByStatus($status);
    }

    public function updateShopOrderTransactionStatusV2($id, Request $request)
    {
        $this->usePaymentTypePo();
        return $this->updateShopOrderTransactionStatus($id, $request);
    }

       public function fetchOnlineShopOrderTransactionListReport()
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') ,
             DB::raw('shop_order_transaction.date'),  DB::raw('COUNT(shop_order_transaction.id) as total_count'),
             DB::raw('SUM(total_cash) as total_cash'), DB::raw('SUM(total_online) as total_online'), 'shop_order_transaction.date')  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.type', 0)
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();

             $expenses_mandatory = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->where('ec.id', '<=', 1)  
            ->first();

            $expenses_non_mandatory = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->where('ec.id', '<=', 2)  
            ->first();


            $total_sales = 0;
            $total_profit = 0;
            $total_cash = 0;
            $total_online = 0;
            $total_count = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
                $total_count += $datavals->total_count;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'total_count' =>$total_count,
              'expenses_mandatory' => $expenses_mandatory->total_expenses,
              'expenses_non_mandatory' =>$expenses_non_mandatory->total_expenses,
              'total_expenses' =>$expenses_non_mandatory->total_expenses + $expenses_mandatory->total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

       public function fetchEmployeePrepare(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $data = DB::table('shop_order_transaction as sot')

            ->leftJoin('users as preparer', 'preparer.id', '=', 'sot.preparer_id')

            ->select(
                'preparer.id',
                'sot.preparer_id',
                'preparer.name as preparer_name',
                DB::raw('COUNT(sot.id) as total_transaction_count'),
                DB::raw('SUM(sot.shop_order_transaction_total_quantity) as total_quantity'),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_amount')
            )

            ->when(
                !empty($dateFrom) &&
                !empty($dateTo) &&
                $dateFrom !== 'null' &&
                $dateTo !== 'null',
                function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sot.date', [$dateFrom, $dateTo]);
                }
            )
            ->whereNotNull('sot.preparer_id')
            ->groupBy(
                'sot.preparer_id',
                'preparer.name'
            )

            ->orderByDesc('total_amount')

            ->get();


            return response()->json($data);
    }    

        public function fetchEmployeeChecker(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $data = DB::table('shop_order_transaction as sot')

            ->leftJoin('users as preparer', 'preparer.id', '=', 'sot.checker_id')

            ->select(
                'preparer.id',
                'sot.checker_id',
                'preparer.name as preparer_name',
                DB::raw('COUNT(sot.id) as total_transaction_count'),
                DB::raw('SUM(sot.shop_order_transaction_total_quantity) as total_quantity'),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_amount')
            )

            ->when(
                !empty($dateFrom) &&
                !empty($dateTo) &&
                $dateFrom !== 'null' &&
                $dateTo !== 'null',
                function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sot.date', [$dateFrom, $dateTo]);
                }
            )
            ->whereNotNull('sot.checker_id')
            ->groupBy(
                'sot.checker_id',
                'preparer.name'
            )

            ->orderByDesc('total_amount')

            ->get();


            return response()->json($data);
    }   
    
      public function fetchEmployeeDispatcher(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $data = DB::table('shop_order_transaction as sot')

            ->leftJoin('users as preparer', 'preparer.id', '=', 'sot.dispatcher_id')

            ->select(
                'preparer.id',
                'sot.dispatcher_id',
                'preparer.name as preparer_name',
                DB::raw('COUNT(sot.id) as total_transaction_count'),
                DB::raw('SUM(sot.shop_order_transaction_total_quantity) as total_quantity'),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_amount')
            )

            ->when(
                !empty($dateFrom) &&
                !empty($dateTo) &&
                $dateFrom !== 'null' &&
                $dateTo !== 'null',
                function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sot.date', [$dateFrom, $dateTo]);
                }
            )
            ->whereNotNull('sot.dispatcher_id')
            ->groupBy(
                'sot.dispatcher_id',
                'preparer.name'
            )

            ->orderByDesc('total_amount')

            ->get();


            return response()->json($data);
    }   

       public function fetchEmployeeSales(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $data = DB::table('shop_order_transaction as sot')

            ->leftJoin('sales_rep as preparer', 'preparer.id', '=', 'sot.sales_rep_id')

            ->select(
                'preparer.id',
                'sot.sales_rep_id',
                'preparer.first_name as preparer_name',
                DB::raw('COUNT(sot.id) as total_transaction_count'),
                DB::raw('SUM(sot.shop_order_transaction_total_quantity) as total_quantity'),
                DB::raw('SUM(sot.shop_order_transaction_total_price) as total_amount')
            )

            ->when(
                !empty($dateFrom) &&
                !empty($dateTo) &&
                $dateFrom !== 'null' &&
                $dateTo !== 'null',
                function ($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sot.date', [$dateFrom, $dateTo]);
                }
            )
            ->whereNotNull('sot.sales_rep_id')
            ->where('sot.sales_rep_id', '!=', 0)
            ->groupBy(
                'sot.sales_rep_id',
                'preparer.first_name'
            )

            ->orderByDesc('total_amount')

            ->get();


            return response()->json($data);
    }    

      public function fetchSalesList(Request $request)
    {

        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->select(
                DB::raw('DATE(mop.created_at) as date'),
                DB::raw('SUM(mop.amount) as total_sales'),
                DB::raw('SUM(CASE WHEN pt.type = 1 THEN mop.amount ELSE 0 END) as total_cash'),
                DB::raw('SUM(CASE WHEN pt.type = 2 THEN mop.amount ELSE 0 END) as total_online'),
                DB::raw('COUNT(sot.id) as total_count'),
            )
            ->where('shop.shop_type_id', 3)
            ->whereBetween('mop.created_at', [
                $request->input('dateFrom'),
                $request->input('dateTo')
            ])
            ->groupBy(DB::raw('DATE(mop.created_at)'))
            ->orderBy('date', 'DESC')
            ->get();


            $total_sales = 0;
            $total_cash = 0;
            $total_online = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    } 
    


           public function fetchOnlineShopOrderTransactionListReportByDate(Request $request)
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('COUNT(sot.id) as total_count'), DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(sot.profit) as total_profit') ,
             DB::raw('sot.date'), DB::raw('SUM(sot.total_cash) as total_cash'), DB::raw('SUM(sot.total_online) as total_online'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            // ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->where('shop.shop_type_id', 3)
            ->where('sot.status', 1)
            ->whereBetween('sot.date', [
                $request->input('dateFrom'),
                $request->input('dateTo')
            ])
            ->orderBy('sot.date', 'DESC')
            ->groupBy('sot.date')
            ->get();

            //         $shop_order_transaction_list = DB::table('shop_order_transaction')
            // ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            // ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            // ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            // ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            // ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
            //  'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
            //  'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
            //  DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status', 
            //  'shop_order_transaction.date', 'shop_order_transaction.profit','shop_order_transaction.total_cash',
            //  'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name', 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status' ) 
                
            //  ->where('shop.shop_type_id', 3)
            //  ->where('shop_order_transaction.date', $date)
            //  ->orderBy('shop_order_transaction.id', 'DESC')
            // ->get();



            // $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            // ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            // ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            // ->join('shop', 'shop.id', '=', 'sot.shop_id')
            // ->select(
            //     DB::raw('DATE(mop.created_at) as date'),
            //     DB::raw('SUM(mop.amount) as total_sales'),
            //     DB::raw('SUM(CASE WHEN pt.type = 1 THEN mop.amount ELSE 0 END) as total_cash'),
            //     DB::raw('SUM(CASE WHEN pt.type = 2 THEN mop.amount ELSE 0 END) as total_online')
            // )
            // ->where('shop.shop_type_id', 3)
            // ->whereBetween('mop.created_at', [
            //     $request->input('dateFrom'),
            //     $request->input('dateTo')
            // ])
            // ->groupBy(DB::raw('DATE(mop.created_at)'))
            // ->orderBy('date', 'DESC')
            // ->get();


            $expenses_mandatory = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->where('e.date', '>=', $request->input('dateFrom'))
            ->where('e.date', '<=', $request->input('dateTo'))  
            ->where('ec.id', '<=', 1)  
            ->first();

            $expenses_non_mandatory = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->where('e.date', '>=', $request->input('dateFrom'))
            ->where('e.date', '<=', $request->input('dateTo'))  
            ->where('ec.id', '<=', 2)  
            ->first();



            $total_sales = 0;
            $total_profit = 0;
            $total_cash = 0;
            $total_online = 0;
            $total_count = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
                $total_count += $datavals->total_count;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'total_count' =>$total_count,
              'expenses_mandatory' => $expenses_mandatory->total_expenses,
              'expenses_non_mandatory' =>$expenses_non_mandatory->total_expenses,
              'total_expenses' =>$expenses_non_mandatory->total_expenses + $expenses_mandatory->total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchShopOrderTransactionListReportByDate(Request $request)
    {
      
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
            //
         $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
            ->where('shop_order_transaction.type', '=', 1)    
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();
         
        } else {
             $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop.shop_name', 'shop.shop_type_id',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status',  'shop_order_transaction.date', 'shop_order_transaction.profit')    
            ->where('shop_order_transaction.type', '=', 1)    
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();
         
        }

        
           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];
 
            return response()->json($response);
    }


       public function fetchOnlineShopOrderTransactionListByDate($date)
    {
         $currentTime = Carbon::now('GMT+8');
        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status', 
             'shop_order_transaction.date', 'shop_order_transaction.profit','shop_order_transaction.total_cash',
             'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name', 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status' ) 
                
             ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', $date)
             ->orderBy('shop_order_transaction.id', 'DESC')
            ->get();

            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit')) 
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')   
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->first();

              $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('SUM(mop.is_paid) as total_paid_count') , DB::raw('COUNT(mop.id) as total_count'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('sot.date', $date)
            ->where('sot.status', 1)
            ->groupBy('pt.id')
            ->get();



          $total = DB::table('shop_order_transaction')
            ->select(DB::raw('COUNT(shop_id) as total_count'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')   
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.date', $date)
            ->first();


            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
            }            


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'total_count' =>$total->total_count,
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }
        public function fetchOnlineShopOrderTransactionListByStatus($status)
        {
            $today = date('Y-m-d');

            // Determine filtering: pickup (status 4/5) vs regular status
            $isPickupMode = in_array($status, [4, 5]);
            $pickupStatus = $isPickupMode ? ($status == 5 ? 1 : 0) : null;

            // Base transaction list query
            $baseListQuery = DB::table('shop_order_transaction')
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
                ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
                ->select(
                    'shop_order_transaction.id',
                    'shop_order_transaction.shop_order_transaction_total_quantity',
                    'shop_order_transaction.shop_order_transaction_total_price',
                    'shop_order_transaction.created_at',
                    'shop_order_transaction.updated_at',
                    'shop_order_transaction.is_pickup',
                    'shop.shop_name',
                    'shop.shop_type_id',
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'c.store_name',
                    'shop_order_transaction.checker',
                    'shop_order_transaction.requestor',
                    'shop_order_transaction.status',
                    'shop_order_transaction.date',
                    'shop_order_transaction.profit',
                    'shop_order_transaction.total_cash',
                    'shop_order_transaction.total_online',
                    'ct.customer_type',
                    'shop_order_transaction.rider_name'
                )
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.date', $today);

            // Apply condition
            if ($isPickupMode) {
                $baseListQuery->where('shop_order_transaction.is_pickup', $pickupStatus);
            } else {
                $baseListQuery->where('shop_order_transaction.status', $status);
            }

            $shop_order_transaction_list = $baseListQuery
                ->orderBy('shop_order_transaction.id', 'DESC')
                ->get();

            // Base totals query
            $baseTotalQuery = DB::table('shop_order_transaction')
                ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
                ->where('shop.shop_type_id', 3)
                ->where('shop_order_transaction.date', $today);

            if ($isPickupMode) {
                $baseTotalQuery->where('shop_order_transaction.is_pickup', $pickupStatus);
            } else {
                $baseTotalQuery->where('shop_order_transaction.status', $status);
            }

            // Totals
            $data = (clone $baseTotalQuery)
                ->select(
                    DB::raw('SUM(shop_order_transaction_total_price) as total_price'),
                    DB::raw('SUM(profit) as total_profit')
                )
                ->first();

            $cash = (clone $baseTotalQuery)
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.type', 1)
                ->select(DB::raw('SUM(mop.amount) as total_cash'))
                ->first();

            $online = (clone $baseTotalQuery)
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.type', 2)
                ->select(DB::raw('SUM(mop.amount) as total_online'))
                ->first();

            $payment_type = DB::table('shop_order_transaction as sot')
                ->select(
                    DB::raw('SUM(mop.amount) as total_amount'),
                    'pt.payment_type',
                    'pt.payment_type_description',
                    'pt.id'
                )
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->joinSub($this->paymentTypeSource(), 'pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('sot.date', $today);

            if ($isPickupMode) {
                $payment_type->where('sot.is_pickup', $pickupStatus);
            } else {
                $payment_type->where('sot.status', $status);
            }

            $payment_type = $payment_type->groupBy('pt.id')->get();

            // Count
            $totalCount = (clone $baseTotalQuery)
                ->select(DB::raw('COUNT(shop_id) as total_count'))
                ->first();

            // Attach mode of payment per transaction
            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                    ->select($this->modeOfPaymentSelect())
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
                    ->where('pt.id', '!=', 1)
                    ->where('mop.shop_order_transaction_id', $sotl->id)
                    ->get();
            }

            return response()->json([
                'total_price'  => $data->total_price,
                'total_profit' => $data->total_profit,
                'total_cash'   => $cash->total_cash,
                'total_online' => $online->total_online,
                'total_count'  => $totalCount->total_count,
                'data'         => $shop_order_transaction_list,
                'payment'      => $payment_type,
                'code'         => 200,
                'status'       => $status,
                'date'         => $today,
                'message'      => "Successfully Added"
            ]);
        }



    public function fetchShopOrderTransaction($id)
    {

         $shopOrderTransaction = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('shop_type as st', 'st.id', '=', 's.shop_type_id')
            ->select('st.id')    
            ->where('sot.id', $id)
            ->first();

        switch ($shopOrderTransaction->id) {
        case 1:
           $data = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('users as r', 'r.id', '=', 'sot.requestor')
            ->join('users as c', 'c.id', '=', 'sot.checker')
            ->select('sot.id', 'sot.shop_order_transaction_total_quantity', 'sot.date',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at',  's.shop_name', 's.shop_type_id', 's.status', 's.address', 's.contact_number', 
             'r.name as requestor_name', 'c.name as checker_name', 'sot.checker', 'sot.requestor', 'sot.status')    
            ->where('sot.id', $id)
            ->first();
            break;
        case 2:
           $data = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('users as r', 'r.id', '=', 'sot.requestor')
            ->join('users as c', 'c.id', '=', 'sot.checker')
            ->select('sot.id', 'sot.shop_order_transaction_total_quantity', 'sot.date',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at',  's.shop_name','s.shop_type_id', 's.status', 's.address', 's.contact_number',
             'r.name as requestor_name', 'c.name as checker_name', 'sot.checker', 'sot.requestor', 'sot.status')    
            ->where('sot.id', $id)
            ->first();
            break;
        case 3 :
          $data = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('customer as r', 'r.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->leftJoin('sales_rep as sr', 'sr.id', '=', 'sot.sales_rep_id')
            ->select('sot.id', 'sot.shop_order_transaction_total_quantity', 'sr.first_name as sr_name', 'sot.date',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at',  's.shop_name','s.shop_type_id', 's.status', 's.address', 's.contact_number',
             'r.first_name as requestor_name', 'sot.checker', 'sot.requestor', 'ct.customer_type', 'sot.status'
             , DB::raw('CONCAT(r.first_name, " ", r.last_name) AS requestor_name'))   
            ->where('sot.id', $id)
            ->first();
            break;
         case 4 :
          $data = DB::table('shop_order_transaction as sot')
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->join('customer as r', 'r.id', '=', 'sot.requestor')
            ->select('sot.id', 'sot.shop_order_transaction_total_quantity', 'sot.date',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at',  's.shop_name','s.shop_type_id', 's.status', 's.address', 's.contact_number',
             'r.first_name as requestor_name', 'sot.checker', 'sot.requestor', 'sot.status'
             , DB::raw('CONCAT(r.first_name, " ", r.last_name) AS requestor_name'))   
            ->where('sot.id', $id)
            ->first();
            break;
        default:
            echo "Error";
        }

        if ($data) {
            $data->vip_customers = DB::table('vip_customer_transaction as vct')
                ->join('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
                ->select(
                    'vct.id as vip_customer_transaction_id',
                    'vct.vip_customer_id',
                    'vc.vip_name',
                    'vc.vip_color'
                )
                ->where('vct.customer_id', $data->requestor)
                ->get();
        }

            return response()->json($data);   
    }

        public function fetchShopOrderTransactionList()
    {
        $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('users as r', 'r.id', '=', 'shop_order_transaction.requestor')
            ->join('users as c', 'c.id', '=', 'shop_order_transaction.checker')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at',  'shop.shop_name',
             'r.name as requestor_name', 'c.name as checker_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status')    
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
            'shop_id' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shopOrderTransaction = new ShopOrderTransaction;
        $shopOrderTransaction->shop_id	 = $request->input('shop_id');
        $shopOrderTransaction->shop_order_transaction_total_quantity = $request->input('shop_order_transaction_total_quantity');
        $shopOrderTransaction->shop_order_transaction_total_price = $request->input('shop_order_transaction_total_price');
        $shopOrderTransaction->requestor = $request->input('requestor');
        $shopOrderTransaction->checker = $request->input('checker');
        $shopOrderTransaction->sales_rep_id = $request->input('sales_rep_id');
        $shopOrderTransaction->user_id = $request->input('user_id');
        $shopOrderTransaction->profit = 0;
        $shopOrderTransaction->status = 2;
        $shopOrderTransaction->type = $request->input('type');
        $shopOrderTransaction->customer_type_id = $request->input('customer_type_id');
        $shopOrderTransaction->date = $request->input('date');
        $shopOrderTransaction->updated_at = now('GMT+8');
        $shopOrderTransaction->created_at = now('GMT+8');
        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        return  response()->json($shopOrderTransaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        $shopOrderTransaction->shop_order_transaction_total_quantity = $request->input('shop_order_transaction_total_quantity');
        $shopOrderTransaction->shop_order_transaction_total_price = $request->input('shop_order_transaction_total_price');
        $shopOrderTransaction->requestor = $request->input('requestor');
        $shopOrderTransaction->checker = $request->input('checker');
        $shopOrderTransaction->date = $request->input('date');
        $shopOrderTransaction->rider_name = $request->input('rider_name');
        $shopOrderTransaction->is_pickup = $request->input('is_pickup');
        $shopOrderTransaction->status = $request->input('status');
        $shopOrderTransaction->save();
        return  response()->json($shopOrderTransaction);
    }


        public function pickUpAndCustomerUpdate(Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->input('id'));
        $shopOrderTransaction->is_pickup = $request->input('is_pickup');
        $shopOrderTransaction->preparer_id = $request->input('preparer_id');
        $shopOrderTransaction->checker_id = $request->input('checker_id');
        $shopOrderTransaction->dispatcher_id = $request->input('dispatcher_id');
        $shopOrderTransaction->save();

        $customer = Customer::find($request->input('customer_id'));
        $customer->last_name = $request->input('last_name');
        $customer->store_name = $request->input('store_name');
        $customer->contact_number = $request->input('contact_number');
        $customer->address = $request->input('address');
        $customer->save();


        return  response()->json($shopOrderTransaction);
    }

        public function updateShopBranchStatus($id, Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->id);
        $shopOrderTransaction->status = $request->status;
        $shopOrderTransaction->is_pickup = $request->status;
        
        $shopOrderTransaction->save();

          $response = [
              'message' => "Successfully Added"
          ];

            return response()->json($response);
    }



    public function updateShopOrderTransactionStatus($id, Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->id);
        $shopOrderTransaction->status = $request->status;
        

        if ($shopOrderTransaction->checker == 0) {

           $online = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_online'))   
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('mop.shop_order_transaction_id', $request->id)
            ->where('pt.type', 2)
            ->first();

           $cash = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->joinSub($this->paymentTypeSource(), 'pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('mop.shop_order_transaction_id', $request->id)
            ->where('pt.type', 1)
            ->first();

            $shopOrderTransaction->total_cash = $cash->total_cash;
            $shopOrderTransaction->total_online = $online->total_online;


        }

        $shopOrderTransaction->save();

          $response = [
              'message' => "Successfully Added"
          ];

            return response()->json($response);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShopOrderTransaction  $shopOrderTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShopOrderTransaction $shopOrderTransaction)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($shopOrderTransaction->id);
        $shopOrderTransaction->delete();
        return response()->json($shopOrderTransaction);
    }

    public function deleteShopOrderTransaction(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
             return response()->json($request);
    }

    public function cancel(Request $request, ShopOrderTransaction $shopOrderTransaction)
    {
  
        // $shopOrderDelete = ShopOrder::find($shopOrder->id);
        // $shopOrderDelete->delete();
   
        // $data = DB::table('shop_order')
        //   ->select(DB::raw('SUM(shop_order_quantity) as shop_order_transaction_total_quantity'), DB::raw('SUM(shop_order_total_price) as shop_order_transaction_total_price'))    
        //   ->where('shop_order.shop_transaction_id', $shopOrder->shop_transaction_id)
        //   ->first();

        //  if ($data->shop_order_transaction_total_price == null) {
        //    $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
        //    $shopOrderTransaction->shop_order_transaction_total_quantity = 0;
        //    $shopOrderTransaction->shop_order_transaction_total_price = 0;
        //    $shopOrderTransaction->save();    
        //  } else {
        //    $shopOrderTransaction = ShopOrderTransaction::find($shopOrder->shop_transaction_id);
        //    $shopOrderTransaction->shop_order_transaction_total_quantity = $data->shop_order_transaction_total_quantity;
        //    $shopOrderTransaction->shop_order_transaction_total_price = $data->shop_order_transaction_total_price;
        //    $shopOrderTransaction->save();           
        //  }
          

        // $reduced_stock_id = DB::table('reduced_stock')
        //   ->select(DB::raw('id'))    
        //   ->where('reduced_stock.shop_order_id', $shopOrder->id)
        //   ->first();
        // $reducedStock = ReducedStock::find($reduced_stock_id->id);
        // $reducedStock->delete();

        // $branchStockTransaction = BranchStockTransaction::find($shopOrder->branch_stock_transaction_id);
        // $branchStockTransaction->branch_stock_transaction = ($branchStockTransaction->branch_stock_transaction + $shopOrder->shop_order_quantity);
        // $branchStockTransaction->save();

        // $product = Product::find($shopOrder->product_id);
        // $product->stock = ($product->stock + $shopOrder->shop_order_quantity);
        // $product->save();

        return response()->json($shopOrderTransaction);
    }


}
