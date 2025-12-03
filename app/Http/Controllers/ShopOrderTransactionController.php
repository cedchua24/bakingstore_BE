<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\ReducedStock;
use App\Models\BranchStockTransaction;
use App\Models\Product;
use App\Models\ShopOrderTransaction;
use App\Http\Controllers\ModeOfPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;




class ShopOrderTransactionController extends Controller
{
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


            $data = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->join('shop as s', 's.id', '=', 'sot.shop_id')
            ->leftJoin('sales_rep as sr', 'sr.id', '=', 'sot.sales_rep_id')
            ->where('s.shop_type_id', '!=', 3)
            ->where('sot.type', '=', 1)
            ->where('sot.status', 1)
            ->where('sot.date', $date)
            ->first();


           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
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
                                WHEN sot.created_at != sot.date
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
            ->select('c.id', 'c.first_name', DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price'), DB::raw('SUM(sot.profit) as total_profit'))  
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
            ->select('c.id', 'c.first_name', DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price') , DB::raw('SUM(sot.profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->where('sot.status', 1)
            ->where('sot.type', 0)
            ->groupBy('c.id')
            ->orderBy($param1, $param2)
            ->get();


        } else {
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', DB::raw('SUM(sot.shop_order_transaction_total_price) as total_price') , DB::raw('SUM(sot.profit) as total_profit'))  
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


   public function fetchOnlineShopOrderTransactionList(Request $request)
    {
        // $request->input('date') = date('Y-m-d');
        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->join('customer as c', 'c.id', '=', 'sot.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'sot.customer_type_id')
            ->leftJoin('delivery_customer as ds', 'ds.shop_order_transaction_id', '=', 'sot.id')
            ->select('shop.shop_name','sot.id', 'sot.shop_order_transaction_total_quantity',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at', 'sot.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'sot.checker', 'sot.requestor',
              'sot.status', 'sot.date', 'sot.profit',
              'sot.total_cash', 'sot.total_online',
             'ct.customer_type', 'sot.rider_name', 'sot.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
             ->where('sot.date', $request->input('date'))
             ->orderBy('sot.id', 'DESC')
             ->get();
            
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->where('pt.type', 1)
            ->first();
    
            $online = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', '<', $request->input('date'))
            ->where('pt.type', 2)
            ->first();  
            
             $cash_prev = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash_prev'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $request->input('date'))
            ->where('sot.date', '<', $request->input('date'))
            ->where('pt.type', 1)
            ->first();

////////////////////////////
            
            $online_outdated = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_online_outdated'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date',  $request->input('date'))
            ->where('pt.type', 2)
            ->first();
            
            $cash_oudated = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_cash_outdated'))  
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            // ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->where('pt.type', 1)
            ->first();                
           
            $total_paid_outdated = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_paid_outdated'))  
            ->join('shop_order_transaction as sot', 'mop.shop_order_transaction_id', '=', 'sot.id') 
            ->join('shop', 'shop.id', '=', 'sot.shop_id')  
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('mop.created_at', '>', $request->input('date'))
            ->where('sot.date', $request->input('date'))
            ->first();      

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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

        public function fetctProductOrderTransaction($id, Request $request)
        {
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
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'sot.checker', 'sot.requestor',
                    'sot.status', 'sot.date', 'sot.profit', 'sot.total_cash',
                    'sot.total_online', 'ct.customer_type', 'sot.rider_name',
                    'so.shop_order_quantity', 'mup.business_type', 'p.quantity'
                )
                ->where('shop.shop_type_id', 3)
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
                ->where('sot.status', 1)
                ->where('so.product_id', $id)
                ->when(true, $queryDate)
                ->first();

            // Cash totals
            $cash = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_cash')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('sot.status', 1)
                ->where('so.product_id', $id)
                ->where('pt.type', 1)
                ->when(true, $queryDate)
                ->first();

            // Online totals
            $online = DB::table('shop_order_transaction as sot')
                ->selectRaw('SUM(mop.amount) as total_online')
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->join('shop', 'shop.id', '=', 'sot.shop_id')
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('shop.shop_type_id', 3)
                ->where('sot.status', 1)
                ->where('so.product_id', $id)
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
                ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('so.product_id', $id)
                ->where('sot.status', 1)
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
                ->when(true, $queryDate)
                ->first();

            // Add mode of payment and quantity calc
            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->total_order_quantity = $sotl->business_type === 'WHOLESALE'
                    ? $sotl->shop_order_quantity * $sotl->quantity
                    : $sotl->shop_order_quantity;

                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                    ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')
                    ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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


       public function fetchPendingTransactionList(Request $request)
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type', 'shop_order_transaction.rider_name'
              , 'shop_order_transaction.delivery_customer_id', 'ds.status as delivery_status')    
             ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.status', $request->input('status'))
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();
            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', $request->input('status'))
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', $request->input('status'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', $request->input('status'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('sot.status', 2)
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            // ->where('ds.delivery_status', $request->input('status'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            // ->where('ds.delivery_status', $request->input('status'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            // ->where('ds.delivery_status', $request->input('status'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
             ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
             ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
             ->where('sot.date', '>=', $request->input('dateFrom'))
             ->where('sot.date', '<=', $request->input('dateTo'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'shop_order_transaction.delivery_customer_id')
            ->where('shop.shop_type_id', 3)
            ->where('ds.status', $request->input('status'))
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            ->where('ds.status', $request->input('status'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('delivery_customer as ds', 'ds.id', '=', 'sot.delivery_customer_id')
            ->where('ds.status', $request->input('status'))
            ->where('sot.date', '>=', $request->input('dateFrom'))
             ->where('sot.date', '<=', $request->input('dateTo'))
            ->groupBy('pt.id')
            ->get();

            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
            ->select('mop.id', 'mop.amount','sot.id as shop_order_transaction_id','sot.shop_order_transaction_total_quantity',
             'sot.shop_order_transaction_total_price',  'sot.created_at',
             'sot.updated_at', 'sot.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'sot.checker', 'sot.requestor',
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            // ->where('sot.status', 1)
            ->where('mop.created_at', $newDate)
            ->where('pt.type', 2)
            ->where('mop.payment_type_id', $id)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
           ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
           ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('SUM(mop.is_paid) as total_paid_count'), 'pt.payment_type',  'pt.payment_type_description')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
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
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id', 'mop.is_paid')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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

       public function fetchOnlineShopOrderTransactionListReport()
    {
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') ,
             DB::raw('shop_order_transaction.date'),
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
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
              'expenses_mandatory' => $expenses_mandatory->total_expenses,
              'expenses_non_mandatory' =>$expenses_non_mandatory->total_expenses,
              'total_expenses' =>$expenses_non_mandatory->total_expenses + $expenses_mandatory->total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

      public function fetchSalesList(Request $request)
    {

        $shop_order_transaction_list = DB::table('shop_order_transaction as sot')
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->select(
                DB::raw('DATE(mop.created_at) as date'),
                DB::raw('SUM(mop.amount) as total_sales'),
                DB::raw('SUM(CASE WHEN pt.type = 1 THEN mop.amount ELSE 0 END) as total_cash'),
                DB::raw('SUM(CASE WHEN pt.type = 2 THEN mop.amount ELSE 0 END) as total_online')
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
      
            $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_sales'), DB::raw('SUM(profit) as total_profit') ,
             DB::raw('shop_order_transaction.date'), DB::raw('SUM(total_cash) as total_cash'), DB::raw('SUM(total_online) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))
            ->orderBy('shop_order_transaction.date', 'DESC')
            ->groupBy('shop_order_transaction.date')
            ->get();

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
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
                $total_profit += $datavals->total_profit;
                $total_cash += $datavals->total_cash;
                $total_online += $datavals->total_online;
            }


           $response = [
              'data' => $shop_order_transaction_list,
              'code' => 200,
              'total_sales' => $total_sales,
              'total_profit' => $total_profit,
              'total_cash' => $total_cash,
              'total_online' => $total_online,
              'total_cash' =>$total_cash,
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
         

            $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->where('shop_order_transaction.type', '=', 1)    
            ->where('shop_order_transaction.status', 1)
            ->first();

   
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
         

            $data = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'))    
            ->where('shop_order_transaction.type', '=', 1)    
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', '>=', $request->input('dateFrom'))
            ->where('shop_order_transaction.date', '<=', $request->input('dateTo'))             
            ->first();

        }

        
           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
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
             DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"), 'shop_order_transaction.checker', 'shop_order_transaction.requestor', 'shop_order_transaction.status', 
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
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 1)
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop.shop_type_id', 3)
            ->where('shop_order_transaction.status', 1)
            ->where('shop_order_transaction.date', $date)
            ->where('pt.type', 2)
            ->first();

           $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('SUM(mop.is_paid) as total_paid_count') , DB::raw('COUNT(mop.id) as total_count'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
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
                ->select('mop.id', 'mop.payment_type_id', 'pt.payment_type', 'mop.amount', 'mop.shop_order_transaction_id')    
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
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
                    DB::raw("CONCAT(c.first_name, ' ', c.last_name) as requestor_name"),
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
            $data = $baseTotalQuery
                ->select(
                    DB::raw('SUM(shop_order_transaction_total_price) as total_price'),
                    DB::raw('SUM(profit) as total_profit')
                )
                ->first();

            $cash = $baseTotalQuery
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
                ->where('pt.type', 1)
                ->select(DB::raw('SUM(mop.amount) as total_cash'))
                ->first();

            $online = $baseTotalQuery
                ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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
                ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
                ->where('sot.date', $today);

            if ($isPickupMode) {
                $payment_type->where('sot.is_pickup', $pickupStatus);
            } else {
                $payment_type->where('sot.status', $status);
            }

            $payment_type = $payment_type->groupBy('pt.id')->get();

            // Count
            $totalCount = $baseTotalQuery
                ->select(DB::raw('COUNT(shop_id) as total_count'))
                ->first();

            // Attach mode of payment per transaction
            foreach ($shop_order_transaction_list as $sotl) {
                $sotl->mode_of_payment = DB::table('mode_of_payment as mop')
                    ->select(
                        'mop.id',
                        'mop.payment_type_id',
                        'pt.payment_type',
                        'mop.amount',
                        'mop.shop_order_transaction_id'
                    )
                    ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
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


    public function updateShopOrderTransactionStatus($id, Request $request)
    {
        $shopOrderTransaction = ShopOrderTransaction::find($request->id);
        $shopOrderTransaction->status = $request->status;
        

        if ($shopOrderTransaction->checker == 0) {

           $online = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_online'))   
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('mop.shop_order_transaction_id', $request->id)
            ->where('pt.type', 2)
            ->first();

           $cash = DB::table('mode_of_payment as mop')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id') 
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
