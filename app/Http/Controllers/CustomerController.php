<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ShopOrderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('categories.index')->with('categories', $categories);
      $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.store_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
            ->orderBy('c.id', 'desc') 
            ->limit(100)
            ->get();
            return response()->json($data); 
    }

    public function fetchCustomerByDate(Request $request) {
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {
            $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.store_name', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
                ->orderBy('c.first_name', 'asc') 
                ->get();
        }  else {      
            $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.store_name', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
                ->orderBy('c.first_name', 'asc') 
                ->where('c.created_at', '>=', $request->input('dateFrom'))
                ->where('c.created_at', '<=', $request->input('dateTo'))
                ->get();
        }

            return response()->json($data); 
    }

         public function fetchCustomerToDelete($id)
    {
        // return view('categories.index')->with('categories', $categories);
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
            ->where('c.id', '!=', $id)
            ->get();
         return response()->json($data); 
    }

        public function updateAndDeleteCustomer(Request $request)
    {
       ShopOrderTransaction::where('requestor', $request->input('id'))
          ->where('type', 0)
          ->update(['requestor' => $request->input('customer_id')]);

        $customer = Customer::find($request->input('id'));
        $customer->delete();  

        $response = [
                'message' => "Success",
            ];
        return response()->json($response);
    }

    public function fetchCustomerAds(Request $request)
    {

        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {

            $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->orderBy('c.first_name', 'asc') 
                ->groupBy('c.id')
                ->where('c.ads', 1)
                ->get();
         } else {
              $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->orderBy('c.first_name', 'asc') 
                ->groupBy('c.id')
                ->where('c.ads', 1)
                ->where('c.updated_at', '>=', $request->input('dateFrom'))
                ->where('c.updated_at', '<=', $request->input('dateTo'))
                ->get();

          }
            return response()->json($data); 
    }

      public function fetchAllCustomer()
    {
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
            ->orderBy('c.first_name', 'asc') 
            ->get();
            return response()->json($data);  
    }

    public function searchVipCustomerList(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit') ? $request->input('limit') : 50;

        $query = DB::table('customer as c')
            ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'c.id')
            ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.disabled',
                DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, ''))) as customer_name"),
                DB::raw("GROUP_CONCAT(CONCAT(COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
            )
            ->where('c.disabled', 0);

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('c.first_name', 'like', '%' . $search . '%')
                    ->orWhere('c.last_name', 'like', '%' . $search . '%')
                    ->orWhere('c.store_name', 'like', '%' . $search . '%')
                    ->orWhere('c.contact_number', 'like', '%' . $search . '%')
                    ->orWhere('c.email', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("TRIM(CONCAT(c.first_name, ' ', COALESCE(c.last_name, '')))"), 'like', '%' . $search . '%');
            });
        }

        $data = $query
            ->groupBy(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.disabled'
            )
            ->orderBy('c.first_name', 'asc')
            ->limit($limit)
            ->get();

        foreach ($data as $item) {
            $vipCustomers = [];

            if ($item->vip_customer_list != '') {
                foreach (explode('||', $item->vip_customer_list) as $vipCustomer) {
                    $vipCustomerDetails = explode('::', $vipCustomer);

                    if ($vipCustomerDetails[0] != '') {
                        $vipCustomers[] = [
                            'vip_name' => $vipCustomerDetails[0],
                            'vip_color' => isset($vipCustomerDetails[1]) ? $vipCustomerDetails[1] : '',
                        ];
                    }
                }
            }

            $item->vip_customers = $vipCustomers;
            unset($item->vip_customer_list);
        }

        return response()->json($data);
    }

     public function fetchCustomerTransactionList($id)
    {
        // return view('categories.index')->with('categories', $categories);
         $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled', 'c.ads', 'c.created_at')   
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->orderBy('c.first_name', 'asc') 
            ->groupBy('c.id')
            ->get();
            return response()->json($data); 
    }

        public function fetchCustomerTransactionListByDate(Request $request) {

            $type = strtoupper((string) $request->input('type', 'ALL'));

            if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {
            $data = DB::table('customer as c')
                ->select(
                    'c.id',
                    'c.first_name',
                    'c.last_name',
                    'c.contact_number',
                    'c.store_name',
                    'c.email',
                    'c.address',
                    'c.disabled',
                    'c.ads',
                    'c.created_at',
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
                )
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'c.id')
                ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
                ->when($type === 'VIP', function ($query) {
                    $query->whereNotNull('vct.id');
                })
                ->when($type === 'NON_VIP', function ($query) {
                    $query->whereNull('vct.id');
                })
                ->orderBy('c.created_at', 'desc') 
                ->groupBy('c.id')
                ->get();

               for($i=0; $i<= sizeof($data)-1; $i++) {
                   $total_balance = DB::table('shop_order_transaction as sot')
                    ->select( DB::raw('SUM(sot.shop_order_transaction_total_price) as total_balance'), DB::raw('SUM(sot.profit) as total_profit'))   
                    ->where('sot.requestor', $data[$i]->id)
                    ->first();
                    $data[$i]->total_balance = $total_balance->total_balance;
                    $data[$i]->total_profit = $total_balance->total_profit;
                 }  

            }  else {      
            $data = DB::table('customer as c')
                ->select(
                    'c.id',
                    'c.first_name',
                    'c.last_name',
                    'c.contact_number',
                    'c.email',
                    'c.store_name',
                    'c.address',
                    'c.disabled',
                    'c.ads',
                    'c.created_at',
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(vct.id, '::', vct.vip_customer_id, '::', COALESCE(vc.vip_name, ''), '::', COALESCE(vc.vip_color, '')) SEPARATOR '||') as vip_customer_list")
                )
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
                ->leftJoin('vip_customer_transaction as vct', 'vct.customer_id', '=', 'c.id')
                ->leftJoin('vip_customer as vc', 'vc.id', '=', 'vct.vip_customer_id')
                ->where('c.created_at', '>=', $request->input('dateFrom'))
                ->where('c.created_at', '<=', $request->input('dateTo'))
                ->when($type === 'VIP', function ($query) {
                    $query->whereNotNull('vct.id');
                })
                ->when($type === 'NON_VIP', function ($query) {
                    $query->whereNull('vct.id');
                })
                ->orderBy('c.created_at', 'desc') 
                ->groupBy('c.id')
                ->get();

            for($i=0; $i<= sizeof($data)-1; $i++) {
                $total_balance = DB::table('shop_order_transaction as sot')
                ->select( DB::raw('SUM(sot.shop_order_transaction_total_price) as total_balance'), DB::raw('SUM(sot.profit) as total_profit'))   
                ->where('sot.requestor', $data[$i]->id)
                ->where('sot.date', '>=', $request->input('dateFrom'))
                ->where('sot.date', '<=', $request->input('dateTo'))
                ->first();
                    $data[$i]->total_balance = $total_balance->total_balance;
                    $data[$i]->total_profit = $total_balance->total_profit;
                }  
            }

            foreach ($data as $customer) {
                $vipCustomers = [];

                if ($customer->vip_customer_list != '') {
                    foreach (explode('||', $customer->vip_customer_list) as $vipCustomer) {
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

                $customer->vip_customers = $vipCustomers;
                unset($customer->vip_customer_list);
            }
            
            $response = [
                    'data' => $data,
                ];
            return response()->json($response);

    }




    public function customerConvoList($idParam, Request $request) {
    $pageCount = 0;
    $start = 0;
    $minus = 0;
    $max_ids = 0;
    

      
    if ( $request->input('dateFrom') != '' &&  $request->input('dateTo') != '' ) {

        $latestshop_order_transaction = DB::table('shop_order_transaction')
            ->select('requestor', DB::raw('MAX(created_at) as max_date'))
            ->groupBy('requestor');

        $data = DB::table('customer as c')
            ->join('customer_update as cu', 'cu.customer_id', '=', 'c.id')          
            ->join('shop_order_transaction as sot', function ($join) use ($latestshop_order_transaction) {
                $join->on('sot.requestor', '=', 'cu.customer_id')
                    ->joinSub($latestshop_order_transaction, 'latest_shop_order_transaction', function ($join) {
                        $join->on('sot.requestor', '=', 'latest_shop_order_transaction.requestor')
                            ->on('sot.created_at', '=', 'latest_shop_order_transaction.max_date');
                    });
            })
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
                    'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',  'sot.profit',
                    'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update_status', 'cu.created_at as update_date',)
            ->groupBy('c.id') 
            ->where('cu.status', 0)    
            ->where('cu.created_at', '>=', $request->input('dateFrom'))
            ->where('cu.created_at', '<=', $request->input('dateTo'))    
            ->where('sot.checker', 0) 
            ->orderBy('sot.date', 'desc')      
            ->get();

        } else {
        $latestshop_order_transaction = DB::table('shop_order_transaction')
            ->select('requestor', DB::raw('MAX(created_at) as max_date'))
            ->groupBy('requestor');

        $data = DB::table('customer as c')
            ->join('customer_update as cu', 'cu.customer_id', '=', 'c.id')          
            ->join('shop_order_transaction as sot', function ($join) use ($latestshop_order_transaction) {
                $join->on('sot.requestor', '=', 'cu.customer_id')
                    ->joinSub($latestshop_order_transaction, 'latest_shop_order_transaction', function ($join) {
                        $join->on('sot.requestor', '=', 'latest_shop_order_transaction.requestor')
                            ->on('sot.created_at', '=', 'latest_shop_order_transaction.max_date');
                    });
            })
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
                    'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price', 'sot.profit',
                    'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update_status', 'cu.created_at as update_date',)
            ->groupBy('c.id') 
            ->where('cu.status', 0) 
            ->where('sot.checker', 0) 
            ->orderBy('cu.created_at', 'desc')        
            ->get();
    

        }
        $shift_difference = 0;

         $date = Carbon::parse(date('Y-m-d'));
         $diffSearch =  Carbon::parse($request->input('dateFrom'));
         $shift_difference = $date->diffInDays($diffSearch);


        //  if ( $request->input('dateFrom') != '' &&  $request->input('dateTo') != '' ) {
        //      for($i=0; $i<= sizeof($data)-1; $i++) {
        //       $diffDay = $date->diffInDays($data[$i]->date);
        //         if ($shift_difference <= $diffDay) {
        //          $data[$i]->last_order = $diffDay;
        //          $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);
        //         } else {
        //             // unset($data[$i]);  
        //         }
        // }  
        // } else {
            // for($i=0; $i<= sizeof($data)-1; $i++) {
            //    $diffDay = $date->diffInDays($data[$i]->date);
            //    $data[$i]->today = $date;
            //    $data[$i]->last_order = $diffDay;
            //    $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);

            // }

            foreach ($data as $key => $value) {
                $diffDay = $date->diffInDays($value->date);
                $value->last_order = $diffDay;
                $value->date2 = date('Y-m-d');
                $value->last_chat = $date->diffInDays($value->update_date);
                if ($value->date >= $value->update_date ) {
                    // if ($value->last_order == 0) {
                        $data->forget($key);
                    // }
                }
            }

             $data = array_values($data->toArray());

        // }


      $response = [
              'data' => $data,
              'request' =>$request->input('dateFrom')
          ];
      return response()->json($response);
    }

    /**
     * Return customers whose latest follow-up has been completed and who have
     * not placed another completed order since that follow-up.
     */
    public function customerConvoListV2(Request $request)
    {
        $request->merge([
            'followed_up_from' => $request->input('followed_up_from', $request->input('dateFrom')),
            'followed_up_to' => $request->input('followed_up_to', $request->input('dateTo')),
            'amount_min' => $request->input('amount_min', $request->input('required_amount')),
        ]);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'followed_up_from' => ['nullable', 'date'],
            'followed_up_to' => ['nullable', 'date', 'after_or_equal:followed_up_from'],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => ['nullable', 'numeric', 'min:0', 'gte:amount_min'],
        ]);

        $latestCustomerUpdates = DB::table('customer_update')
            ->select('customer_id', DB::raw('MAX(id) as latest_update_id'))
            ->groupBy('customer_id');

        $completedOrderTotals = DB::table('shop_order_transaction')
            ->select(
                'requestor',
                DB::raw('MAX(date) as last_order_date'),
                DB::raw('MAX(created_at) as last_order_created_at'),
                DB::raw('SUM(shop_order_transaction_total_price) as total_sales'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->where('checker', 0)
            ->groupBy('requestor');

        $query = DB::table('customer as c')
            ->joinSub($latestCustomerUpdates, 'latest_cu', function ($join) {
                $join->on('latest_cu.customer_id', '=', 'c.id');
            })
            ->join('customer_update as cu', 'cu.id', '=', 'latest_cu.latest_update_id')
            ->joinSub($completedOrderTotals, 'orders', function ($join) {
                $join->on('orders.requestor', '=', 'c.id');
            })
            ->where('cu.status', 0)
            ->whereColumn('orders.last_order_created_at', '<', 'cu.created_at')
            ->when($validated['followed_up_from'] ?? null, function ($query, $date) {
                $query->whereDate('cu.created_at', '>=', $date);
            })
            ->when($validated['followed_up_to'] ?? null, function ($query, $date) {
                $query->whereDate('cu.created_at', '<=', $date);
            })
            ->when(isset($validated['amount_min']), function ($query) use ($validated) {
                $query->where('orders.total_sales', '>=', $validated['amount_min']);
            })
            ->when(isset($validated['amount_max']), function ($query) use ($validated) {
                $query->where('orders.total_sales', '<=', $validated['amount_max']);
            })
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.address',
                'c.disabled',
                'cu.id as customer_update_id',
                'cu.user_id',
                'cu.chat',
                'cu.promo',
                'cu.status as update_status',
                'cu.created_at as followed_up_at',
                'orders.last_order_date',
                'orders.total_sales',
                'orders.total_orders'
            )
            ->orderByDesc('cu.created_at')
            ->orderByDesc('cu.id');

        $customers = $query->paginate(100, ['*'], 'page', (int) ($validated['page'] ?? 1));
        $today = Carbon::today();

        $customers->getCollection()->transform(function ($customer) use ($today) {
            $customer->update_status = (int) $customer->update_status;
            $customer->total_sales = round((float) $customer->total_sales, 2);
            $customer->total_orders = (int) $customer->total_orders;
            $customer->days_since_last_order = Carbon::parse($customer->last_order_date)
                ->startOfDay()
                ->diffInDays($today);
            $customer->days_since_follow_up = Carbon::parse($customer->followed_up_at)
                ->startOfDay()
                ->diffInDays($today);

            return $customer;
        });

        return response()->json([
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
                'has_more_pages' => $customers->hasMorePages(),
            ],
            'filters' => [
                'followed_up_from' => $validated['followed_up_from'] ?? null,
                'followed_up_to' => $validated['followed_up_to'] ?? null,
                'amount_min' => isset($validated['amount_min']) ? (float) $validated['amount_min'] : null,
                'amount_max' => isset($validated['amount_max']) ? (float) $validated['amount_max'] : null,
            ],
            'sort' => 'followed_up_at_desc',
        ]);
    }


        public function customerReorder($idParam, Request $request) {
        $pageCount = 0;
        $start = 0;
        $minus = 0;
        $max_ids = 0;
    

      
        if ( $request->input('dateFrom') != '' &&  $request->input('dateTo') != '' ) {

            $latestshop_order_transaction = DB::table('shop_order_transaction')
                ->select('requestor', DB::raw('MAX(created_at) as max_date'))
                ->groupBy('requestor');

            $data = DB::table('customer as c')
                ->join('customer_update as cu', 'cu.customer_id', '=', 'c.id')          
                ->join('shop_order_transaction as sot', function ($join) use ($latestshop_order_transaction) {
                    $join->on('sot.requestor', '=', 'cu.customer_id')
                        ->joinSub($latestshop_order_transaction, 'latest_shop_order_transaction', function ($join) {
                            $join->on('sot.requestor', '=', 'latest_shop_order_transaction.requestor')
                                ->on('sot.created_at', '=', 'latest_shop_order_transaction.max_date');
                        });
                })
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
                        'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',  'sot.profit',
                        'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update_status', 'cu.created_at as update_date',)
                ->groupBy('c.id') 
                ->where('cu.status', 0)    
                ->where('sot.checker', 0) 
                ->where('cu.created_at', '>=', $request->input('dateFrom'))
                ->where('cu.created_at', '<=', $request->input('dateTo'))    
                ->orderBy('sot.date', 'desc')      
                ->get();

            } else {
            $latestshop_order_transaction = DB::table('shop_order_transaction')
                ->select('requestor', DB::raw('MAX(created_at) as max_date'))
                ->groupBy('requestor');

            $data = DB::table('customer as c')
                ->join('customer_update as cu', 'cu.customer_id', '=', 'c.id')          
                ->join('shop_order_transaction as sot', function ($join) use ($latestshop_order_transaction) {
                    $join->on('sot.requestor', '=', 'cu.customer_id')
                        ->joinSub($latestshop_order_transaction, 'latest_shop_order_transaction', function ($join) {
                            $join->on('sot.requestor', '=', 'latest_shop_order_transaction.requestor')
                                ->on('sot.created_at', '=', 'latest_shop_order_transaction.max_date');
                        });
                })
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
                        'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price', 'sot.profit',
                        'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update_status', 'cu.created_at as update_date',)
                ->groupBy('c.id') 
                ->where('cu.status', 0) 
                ->where('sot.checker', 0) 
                // ->where('sot.date', '>=', 'cu.created_at')   
                ->orderBy('cu.created_at', 'desc')        
                ->get();
        

            }
            $shift_difference = 0;

            $date = Carbon::parse(date('Y-m-d'));
            $diffSearch =  Carbon::parse($request->input('dateFrom'));
            $shift_difference = $date->diffInDays($diffSearch);


            //  if ( $request->input('dateFrom') != '' &&  $request->input('dateTo') != '' ) {
            //      for($i=0; $i<= sizeof($data)-1; $i++) {
            //       $diffDay = $date->diffInDays($data[$i]->date);
            //         if ($shift_difference <= $diffDay) {
            //          $data[$i]->last_order = $diffDay;
            //          $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);
            //         } else {
            //             // unset($data[$i]);  
            //         }
            // }  
            // } else {
                // for($i=0; $i<= sizeof($data)-1; $i++) {
                //    $diffDay = $date->diffInDays($data[$i]->date);
                //    $data[$i]->today = $date;
                //    $data[$i]->last_order = $diffDay;
                //    $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);

                // }

                foreach ($data as $key => $value) {
                    $diffDay = $date->diffInDays($value->date);
                    $value->last_order = $diffDay;
                    $value->last_chat = $date->diffInDays($value->update_date);
                    if ($value->date < $value->update_date) {
                        $data->forget($key);
                    }
                }

                $data = array_values($data->toArray());

            // }


        $response = [
                'data' => $data,
                'request' =>$request->input('dateFrom')
            ];
        return response()->json($response);
    }

        
    public function customerBacklogList($idParam, Request $request) {
        $newData = [];
        $pageCount = 0;
        $offset = 0;
        $max_ids = 0;
        $required_amount = $request->input('required_amount');
        $dateFrom = $request->input('dateFrom');
        
        if ($request->input('dateFrom') == null ) {
            $total_page =  DB::table('shop_order_transaction as sot')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')   
                ->distinct()
                ->where('sot.checker',  0)
                ->where('c.backlog',  1)
                ->count('sot.requestor');

            if ($idParam == 1) {
                $offset = 0;
            } else {
                $offset = ($idParam - 1) * 100;   // Proper zero-based offset
            }

            $max_ids = DB::table('shop_order_transaction as sot')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')  
                ->select(DB::raw('MAX(sot.id) as id'))
                ->where('sot.checker', 0)
                ->groupBy('sot.requestor')
                ->orderByRaw('MAX(sot. id) DESC')   // Sort before pagination
                ->where('c.backlog',  1)
                ->offset($offset)
                ->limit(100)
                ->get();
            } else {


            $total_page = DB::table('shop_order_transaction as sot')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')  
                ->select('sot.requestor')
                ->where('sot.checker', 0)
                ->where('sot.date', '<=', $dateFrom)
                ->where('c.backlog',  1)
                ->groupBy('sot.requestor')
                ->havingRaw('SUM(sot.shop_order_transaction_total_price) >= ?', [$required_amount])
                ->count();
                
                if ($idParam == 1) {
                    $offset = 0;
                } else {
                    $offset = ($idParam - 1) * 100;  // Correct pagination offset
                }

                $query = DB::table('shop_order_transaction as sot')
                    ->join('customer as c', 'c.id', '=', 'sot.requestor')  
                    ->select(
                        DB::raw('MAX(sot. id) as id'),
                        DB::raw('SUM(sot. shop_order_transaction_total_price) as total_sales')
                    )
                    ->where('sot.checker', 0)
                    ->where('sot.date', '<=', $request->input('dateFrom'))
                    ->where('c.backlog',  1)
                    ->groupBy('sot.requestor')
                    ->orderByRaw('MAX(sot.id) DESC')       // correct sorting
                    ->offset($offset)
                    ->limit(100);

                // Apply SUM filter only on pages after page 1
                if ($idParam != 1) {
                    $query->havingRaw('SUM(sot.shop_order_transaction_total_price) >= ?', [$required_amount]);
                }

                $max_ids = $query->get();

        }


                
            $ids = array();
                foreach ($max_ids as $id) { 
                array_push($ids, $id->id);  
                }

            $sotList = DB::table('shop_order_transaction as sot')
                ->join('customer as c', 'c.id', '=', 'sot.requestor')
                ->select('sot.id')   
                ->where('sot.checker',  0)
                ->where('c.backlog',  1)
                ->whereIn('sot.id', $ids)
                ->get();

            $sots = array();
                foreach ($sotList as $sot) { 
                array_push($sots, $sot->id);  
                }   
        
            if ($request->input('dateFrom') != '' ) {
            $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' ,
                'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',
                'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update status', 'cu.created_at as update_date')
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->leftJoin('customer_update as cu', 'cu.customer_id', '=', 'sot.requestor') 
                ->where('sot.date', '<=', $request->input('dateFrom'))
                ->where('sot.checker',  0)
                ->where('c.backlog',  1)
                // ->where('cu.customer_id', null)  
                ->whereIn('sot.id', $sots)     
                ->groupBy('c.id')
                ->get();
            } else {
            $data = DB::table('customer as c')
                ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
                'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',
                'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update status', 'cu.created_at as update_date')
                ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
                ->leftJoin('customer_update as cu', 'cu.customer_id', '=', 'sot.requestor') 
                ->whereIn('sot.id', $sots)  
                ->where('sot.checker',  0)
                ->where('c.backlog',  1)   
                ->groupBy('c.id')
                ->get();
            }
            $data = $data->toArray();
            $shift_difference = 0;

            $date = Carbon::parse(date('Y-m-d'));
            $diffSearch =  Carbon::parse($request->input('dateFrom'));
            $shift_difference = $date->diffInDays($diffSearch);


            if ($request->input('dateFrom') != '' ) { 
            

                    foreach ($data as $item) {
                                    $diffDay = $date->diffInDays($item->date);
                                    $item->last_order = $diffDay;
                                    $item->last_chat = $date->diffInDays($item->update_date);

                                    $total_sales = DB::table('shop_order_transaction as sot')
                                        ->select(DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales'))
                                        ->where('sot.requestor', $item->id)
                                        ->first();

                                    $item->total_sales = $total_sales->total_sales;

                                    // FILTER LOGIC
                                    if ($request->input('dateFrom') != '') {
                                        if ($shift_difference > $diffDay) {
                                            continue; // skip item
                                        }
                                    }

                                    if ($item->total_sales < $required_amount) {
                                        continue; // skip item
                                    }

                                    $newData[] = $item; // keep item
                                }
            
            } else {
                for($i=0; $i<= sizeof($data)-1; $i++) {
                $diffDay = $date->diffInDays($data[$i]->date);
                $data[$i]->last_order = $diffDay;
                $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);

                $total_sales = DB::table('shop_order_transaction as sot')
                    ->select(DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales'))  
                    ->where('sot.requestor', $data[$i]->id)
                    ->first();

                    $data[$i]->total_sales = $total_sales->total_sales;
                    if ($data[$i]->total_sales < $required_amount) {
                        unset($data[$i]);  
                        $data = array_values($data);
                    }
                }
            }


        $response = [
                'data' => count($newData) == 0 ? $data : $newData,
                'total_page' => $total_page,
                'pageCount' => $pageCount,
                'sots' => $sots,
                'data2' => $data,
                'max_ids' => $max_ids,
                'day_count' => $shift_difference,
                'page' => $idParam,
                'request' =>$request->input('dateFrom')
            ];
        return response()->json($response);
        }


    /**
     * Return customers with completed orders, ordered by longest inactivity.
     *
     * V2 applies all filters before pagination and always returns 100 records per
     * page. Amount filters refer to the customer's lifetime completed sales.
     */
    public function customerLastOrderListV2(Request $request)
    {
        // Keep the old frontend field names as aliases while exposing clearer v2 names.
        $request->merge([
            'last_order_before' => $request->input('last_order_before', $request->input('dateFrom')),
            'amount_min' => $request->input('amount_min', $request->input('required_amount')),
        ]);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'last_order_before' => ['nullable', 'date', 'before_or_equal:today'],
            'inactive_days_min' => ['nullable', 'integer', 'min:0'],
            'amount_min' => ['nullable', 'numeric', 'min:0'],
            'amount_max' => ['nullable', 'numeric', 'min:0', 'gte:amount_min'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = 100;
        $today = Carbon::today();
        $lastOrderBefore = $validated['last_order_before'] ?? null;

        if (isset($validated['inactive_days_min'])) {
            $inactiveDate = $today->copy()->subDays((int) $validated['inactive_days_min'])->toDateString();
            $lastOrderBefore = $lastOrderBefore === null || $inactiveDate < $lastOrderBefore
                ? $inactiveDate
                : $lastOrderBefore;
        }

        $completedOrderTotals = DB::table('shop_order_transaction')
            ->select(
                'requestor',
                DB::raw('MAX(date) as last_order_date'),
                DB::raw('MAX(created_at) as last_order_created_at'),
                DB::raw('SUM(shop_order_transaction_total_price) as total_sales'),
                DB::raw('COUNT(id) as total_orders')
            )
            ->where('checker', 0)
            ->groupBy('requestor');

        $query = DB::table('customer as c')
            ->joinSub($completedOrderTotals, 'orders', function ($join) {
                $join->on('orders.requestor', '=', 'c.id');
            })
            // A follow-up made after the latest order means this customer is done
            // and must stay out of this list until another completed order occurs.
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('customer_update as cu')
                    ->whereColumn('cu.customer_id', 'c.id')
                    ->whereColumn('cu.created_at', '>=', 'orders.last_order_created_at');
            })
            ->select(
                'c.id',
                'c.first_name',
                'c.last_name',
                'c.store_name',
                'c.contact_number',
                'c.email',
                'c.address',
                'c.disabled',
                'orders.last_order_date',
                'orders.total_sales',
                'orders.total_orders'
            )
            ->when($lastOrderBefore, function ($query, $date) {
                $query->where('orders.last_order_date', '<=', $date);
            })
            ->when(isset($validated['amount_min']), function ($query) use ($validated) {
                $query->where('orders.total_sales', '>=', $validated['amount_min']);
            })
            ->when(isset($validated['amount_max']), function ($query) use ($validated) {
                $query->where('orders.total_sales', '<=', $validated['amount_max']);
            })
            ->orderBy('orders.last_order_date')
            ->orderBy('c.id');

        $customers = $query->paginate($perPage, ['*'], 'page', $page);
        $customers->getCollection()->transform(function ($customer) use ($today) {
            $customer->total_sales = round((float) $customer->total_sales, 2);
            $customer->total_orders = (int) $customer->total_orders;
            $customer->days_since_last_order = Carbon::parse($customer->last_order_date)
                ->startOfDay()
                ->diffInDays($today);

            return $customer;
        });

        return response()->json([
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
                'has_more_pages' => $customers->hasMorePages(),
            ],
            'filters' => [
                'last_order_before' => $lastOrderBefore,
                'inactive_days_min' => isset($validated['inactive_days_min'])
                    ? (int) $validated['inactive_days_min']
                    : null,
                'amount_min' => isset($validated['amount_min']) ? (float) $validated['amount_min'] : null,
                'amount_max' => isset($validated['amount_max']) ? (float) $validated['amount_max'] : null,
            ],
            'sort' => 'days_since_last_order_desc',
        ]);
    }


public function customerLastOrderList($idParam, Request $request) {
    $newData = [];
    $pageCount = 0;
    $offset = 0;
    $max_ids = 0;
    $required_amount = $request->input('required_amount');
    $dateFrom = $request->input('dateFrom');
    
     if ($request->input('dateFrom') == null ) {
           $total_page =  DB::table('shop_order_transaction')
            ->distinct()
            ->where('checker',  0)
            ->count('requestor');

        if ($idParam == 1) {
            $offset = 0;
        } else {
            $offset = ($idParam - 1) * 100;   // Proper zero-based offset
        }

        $max_ids = DB::table('shop_order_transaction')
            ->select(DB::raw('MAX(id) as id'))
            ->where('checker', 0)
            ->groupBy('requestor')
            ->orderByRaw('MAX(id) DESC')   // Sort before pagination
            ->offset($offset)
            ->limit(100)
            ->get();
        } else {


           $total_page = DB::table('shop_order_transaction as sot')
            ->select('sot.requestor')
            ->where('sot.checker', 0)
            ->where('sot.date', '<=', $dateFrom)
            ->groupBy('sot.requestor')
            ->havingRaw('SUM(sot.shop_order_transaction_total_price) >= ?', [$required_amount])
            ->count();
            
            if ($idParam == 1) {
                $offset = 0;
            } else {
                $offset = ($idParam - 1) * 100;  // Correct pagination offset
            }

            $query = DB::table('shop_order_transaction')
                ->select(
                    DB::raw('MAX(id) as id'),
                    DB::raw('SUM(shop_order_transaction_total_price) as total_sales')
                )
                ->where('checker', 0)
                ->where('date', '<=', $request->input('dateFrom'))
                ->groupBy('requestor')
                ->orderByRaw('MAX(id) DESC')       // correct sorting
                ->offset($offset)
                ->limit(100);

            // Apply SUM filter only on pages after page 1
            if ($idParam != 1) {
                $query->havingRaw('SUM(shop_order_transaction_total_price) >= ?', [$required_amount]);
            }

            $max_ids = $query->get();

    }


            
        $ids = array();
            foreach ($max_ids as $id) { 
             array_push($ids, $id->id);  
            }

         $sotList = DB::table('shop_order_transaction')
            ->select('id')   
            ->where('checker',  0)
            ->whereIn('id', $ids)
            ->get();

        $sots = array();
            foreach ($sotList as $sot) { 
             array_push($sots, $sot->id);  
            }   
      
        if ($request->input('dateFrom') != '' ) {
          $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' ,
             'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',
             'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update status', 'cu.created_at as update_date')
             ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
             ->leftJoin('customer_update as cu', 'cu.customer_id', '=', 'sot.requestor') 
            ->where('sot.date', '<=', $request->input('dateFrom'))
            ->where('sot.checker',  0)
             ->where('cu.customer_id', null)  
            ->whereIn('sot.id', $sots)     
            ->groupBy('c.id')
            ->get();
        } else {
           $data = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email',
             'c.address' , 'c.disabled', 'sot.date', 'sot.shop_order_transaction_total_price',
             'cu.user_id', 'cu.chat', 'cu.promo', 'cu.status as update status', 'cu.created_at as update_date')
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->leftJoin('customer_update as cu', 'cu.customer_id', '=', 'sot.requestor') 
            ->whereIn('sot.id', $sots)  
            ->where('sot.checker',  0)
            ->where('cu.customer_id', null)   
            ->groupBy('c.id')
            ->get();
        }
        $data = $data->toArray();
        $shift_difference = 0;

         $date = Carbon::parse(date('Y-m-d'));
         $diffSearch =  Carbon::parse($request->input('dateFrom'));
         $shift_difference = $date->diffInDays($diffSearch);


        if ($request->input('dateFrom') != '' ) { 
           

                foreach ($data as $item) {
                                $diffDay = $date->diffInDays($item->date);
                                $item->last_order = $diffDay;
                                $item->last_chat = $date->diffInDays($item->update_date);

                                $total_sales = DB::table('shop_order_transaction as sot')
                                    ->select(DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales'))
                                    ->where('sot.requestor', $item->id)
                                    ->first();

                                $item->total_sales = $total_sales->total_sales;

                                // FILTER LOGIC
                                if ($request->input('dateFrom') != '') {
                                    if ($shift_difference > $diffDay) {
                                        continue; // skip item
                                    }
                                }

                                if ($item->total_sales < $required_amount) {
                                    continue; // skip item
                                }

                                $newData[] = $item; // keep item
                            }
        
        } else {
            for($i=0; $i<= sizeof($data)-1; $i++) {
               $diffDay = $date->diffInDays($data[$i]->date);
               $data[$i]->last_order = $diffDay;
               $data[$i]->last_chat = $date->diffInDays($data[$i]->update_date);

               $total_sales = DB::table('shop_order_transaction as sot')
                ->select(DB::raw('SUM(sot.shop_order_transaction_total_price) as total_sales'))  
                ->where('sot.requestor', $data[$i]->id)
                ->first();

                 $data[$i]->total_sales = $total_sales->total_sales;
                 if ($data[$i]->total_sales < $required_amount) {
                    unset($data[$i]);  
                    $data = array_values($data);
                 }
            }
        }


      $response = [
              'data' => count($newData) == 0 ? $data : $newData,
              'total_page' => $total_page,
              'pageCount' => $pageCount,
              'offset' => $offset,
              'max_ids' => $max_ids,
              'day_count' => $shift_difference,
              'page' => $idParam,
              'request' =>$request->input('dateFrom')
          ];
      return response()->json($response);
    }




    

       public function fetchCustomerEnabled($id)
    {
         $data = DB::table('customer as c')
            ->select('c.id', 'c.disabled', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address', 'c.ads', 'c.created_at')   
            ->orderBy('c.first_name', 'asc') 
            ->where('c.disabled', 0)
            ->get();
            return response()->json($data); 

    }

        public function fetchCustomerProduct(Request $request)
    {
        $request->merge([
            'dateFrom' => in_array($request->input('dateFrom'), ['', 'null'], true) ? null : $request->input('dateFrom'),
            'dateTo' => in_array($request->input('dateTo'), ['', 'null'], true) ? null : $request->input('dateTo'),
        ]);

        $validated = $request->validate([
            'id' => 'required',
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date',
        ]);

        $id = $validated['id'];
        $dateFrom = $validated['dateFrom'] ?? null;
        $dateTo = $validated['dateTo'] ?? null;

           $data = DB::table('customer as c')
            ->select('p.product_name', 'mup.business_type', 'mup.new_price', DB::raw('SUM(so.shop_order_quantity) as total_quantity'), DB::raw('SUM(so.shop_order_total_price) as total_price'), DB::raw('SUM(so.shop_order_profit) as total_profit'))  
            ->join('shop_order_transaction as sot', 'sot.requestor', '=', 'c.id')  
            ->join('shop_order as so', 'so.shop_transaction_id', '=', 'sot.id')
            ->join('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->where('c.id', $id) 
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('sot.date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('sot.date', '<=', $dateTo);
            })
            ->groupBy('mup.id') 
            ->orderBy('total_quantity', 'desc')
            ->get();

            $customerDetails = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled')   
            ->where('c.id', $id) 
            ->first();
   
           $response = [
              'data' => $data,
              'customerDetails' => $customerDetails,
              'code' => 200,
              'date' => date('Y-m-d'),
              'id' => $id,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }


       public function fetchCustomerTransaction(Request $request)
    {
        $request->merge([
            'dateFrom' => in_array($request->input('dateFrom'), ['', 'null'], true) ? null : $request->input('dateFrom'),
            'dateTo' => in_array($request->input('dateTo'), ['', 'null'], true) ? null : $request->input('dateTo'),
        ]);

        $validated = $request->validate([
            'id' => 'required',
            'dateFrom' => 'nullable|date',
            'dateTo' => 'nullable|date',
        ]);

        $id = $validated['id'];
        $dateFrom = $validated['dateFrom'] ?? null;
        $dateTo = $validated['dateTo'] ?? null;
        $applyDateFilter = function ($query, $column) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->whereDate($column, '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate($column, '<=', $dateTo);
            }

            return $query;
        };

        $shop_order_transaction_list = DB::table('shop_order_transaction')
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')
            ->join('customer as c', 'c.id', '=', 'shop_order_transaction.requestor')
            ->join('customer_type as ct', 'ct.id', '=', 'shop_order_transaction.customer_type_id')
            ->select('shop_order_transaction.id', 'shop_order_transaction.shop_order_transaction_total_quantity',
             'shop_order_transaction.shop_order_transaction_total_price',  'shop_order_transaction.created_at',
             'shop_order_transaction.updated_at', 'shop_order_transaction.is_pickup',  'shop.shop_name', 'shop.shop_type_id',
             'c.first_name as requestor_name', 'shop_order_transaction.checker', 'shop_order_transaction.requestor',
              'shop_order_transaction.status', 'shop_order_transaction.date', 'shop_order_transaction.profit',
              'shop_order_transaction.total_cash', 'shop_order_transaction.total_online', 'ct.customer_type',
              'shop_order_transaction.rider_name', 'c.ads', 'c.created_at')    
             ->where('shop_order_transaction.requestor', $id)
             ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'shop_order_transaction.date');
             })
             ->orderBy('shop_order_transaction.id', 'DESC')
             ->get();
            
            $data = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(shop_order_transaction_total_price) as total_price'), DB::raw('SUM(profit) as total_profit'),  DB::raw('COUNT(shop_id) as total_count'),)  
            ->where('shop_order_transaction.requestor', $id)
            ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'shop_order_transaction.date');
            })
            ->first();


           $cash = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_cash'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop_order_transaction.requestor', $id)
            ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'shop_order_transaction.date');
            })
            ->first();

            $online = DB::table('shop_order_transaction')
            ->select(DB::raw('SUM(mop.amount) as total_online'))  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'shop_order_transaction.id')
            ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')
            ->where('shop_order_transaction.requestor', $id)
            ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'shop_order_transaction.date');
            })
            ->first();

           $total = DB::table('shop_order_transaction')
            ->select(DB::raw('COUNT(shop_id) as total_count'),)  
            ->join('shop', 'shop.id', '=', 'shop_order_transaction.shop_id')  
            ->where('shop_order_transaction.requestor', $id)
            ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'shop_order_transaction.date');
            })
            ->first();

            $payment_type = DB::table('shop_order_transaction as sot')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), 'pt.payment_type',  'pt.payment_type_description', 'pt.id')  
            ->join('mode_of_payment as mop', 'mop.shop_order_transaction_id', '=', 'sot.id')  
            ->join('payment_type as pt', 'mop.payment_type_id', '=', 'pt.id')
            ->where('sot.requestor', $id)
            ->tap(function ($query) use ($applyDateFilter) {
                $applyDateFilter($query, 'sot.date');
            })
            ->groupBy('pt.id')
            ->get();


            foreach ($shop_order_transaction_list as $sotl) { 
                
               $mode_of_payment = DB::table('mode_of_payment as mop')
                ->select(
                    'mop.id',
                    'mop.payment_type_id',
                    'pt.payment_type',
                    'pt.payment_type as bank_name',
                    'pt.payment_type_description',
                    'mop.amount',
                    'mop.shop_order_transaction_id'
                )
                ->join('payment_type as pt', 'pt.id', '=', 'mop.payment_type_id')  
                ->where('pt.id', '!=', 1)
                ->where('mop.shop_order_transaction_id', $sotl->id)
                ->get();
                
                $sotl->mode_of_payment = $mode_of_payment;
                $sotl->bank = $mode_of_payment->pluck('payment_type')->filter()->implode(', ');
                $sotl->bank_name = $sotl->bank;
                $sotl->bank_paid_amount = round((float) $mode_of_payment->sum('amount'), 2);
            }

             $customerDetails = DB::table('customer as c')
            ->select('c.id', 'c.first_name', 'c.last_name', 'c.contact_number', 'c.email', 'c.address' , 'c.disabled')   
            ->where('c.id', $id) 
            ->first();
   

           $response = [
              'total_price' =>$data->total_price,
              'total_profit' =>$data->total_profit,
              'total_count' =>$total->total_count,
              'total_cash' =>$cash->total_cash,
              'total_online' =>$online->total_online,
              'data' => $shop_order_transaction_list,
              'customerDetails' => $customerDetails,
              'payment' => $payment_type,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    /**
     * Return a customer's completed sales grouped into chart-friendly periods.
     */
    public function fetchCustomerSalesHistory($id, Request $request)
    {
        $validated = $request->validate([
            'dateFrom' => ['required', 'date_format:Y-m-d'],
            'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
            'groupBy' => ['required', 'in:day,week,month,year'],
        ]);

        $customer = DB::table('customer')
            ->select('id', 'first_name', 'last_name', 'store_name')
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found.',
            ], 404);
        }

        $dateFrom = Carbon::createFromFormat('Y-m-d', $validated['dateFrom'])->startOfDay();
        $dateTo = Carbon::createFromFormat('Y-m-d', $validated['dateTo'])->startOfDay();
        $groupBy = $validated['groupBy'];

        $dailySales = DB::table('shop_order_transaction as sot')
            ->where('sot.requestor', $id)
            ->where('sot.status', 1)
            ->whereBetween('sot.date', [$validated['dateFrom'], $validated['dateTo']])
            ->groupBy('sot.date')
            ->orderBy('sot.date')
            ->selectRaw('sot.date,
                SUM(sot.shop_order_transaction_total_price) as total_sales,
                SUM(sot.profit) as total_profit,
                SUM(sot.total_cash) as total_cash,
                SUM(sot.total_online) as total_online')
            ->get();

        $salesByPeriod = [];
        foreach ($dailySales as $sale) {
            $periodKey = $this->customerSalesPeriodStart(Carbon::parse($sale->date), $groupBy)
                ->format('Y-m-d');

            if (!isset($salesByPeriod[$periodKey])) {
                $salesByPeriod[$periodKey] = [
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'total_cash' => 0,
                    'total_online' => 0,
                ];
            }

            $salesByPeriod[$periodKey]['total_sales'] += (float) $sale->total_sales;
            $salesByPeriod[$periodKey]['total_profit'] += (float) $sale->total_profit;
            $salesByPeriod[$periodKey]['total_cash'] += (float) $sale->total_cash;
            $salesByPeriod[$periodKey]['total_online'] += (float) $sale->total_online;
        }

        $history = [];
        $cursor = $this->customerSalesPeriodStart($dateFrom->copy(), $groupBy);
        $lastPeriod = $this->customerSalesPeriodStart($dateTo->copy(), $groupBy);

        while ($cursor->lte($lastPeriod)) {
            $periodStart = $cursor->copy();
            $periodEnd = $this->customerSalesPeriodEnd($periodStart->copy(), $groupBy);
            $key = $periodStart->format('Y-m-d');
            $periodSales = $salesByPeriod[$key] ?? [
                'total_sales' => 0,
                'total_profit' => 0,
                'total_cash' => 0,
                'total_online' => 0,
            ];

            $history[] = [
                'period' => $key,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'total_sales' => round($periodSales['total_sales'], 2),
                'total_profit' => round($periodSales['total_profit'], 2),
                'total_cash' => round($periodSales['total_cash'], 2),
                'total_online' => round($periodSales['total_online'], 2),
            ];

            $cursor = $this->incrementCustomerSalesPeriod($cursor, $groupBy);
        }

        return response()->json([
            'customer_id' => (int) $customer->id,
            'customer_name' => trim($customer->first_name . ' ' . $customer->last_name),
            'store_name' => $customer->store_name,
            'date_from' => $validated['dateFrom'],
            'date_to' => $validated['dateTo'],
            'group_by' => $groupBy,
            'total_sales' => round(collect($history)->sum('total_sales'), 2),
            'total_profit' => round(collect($history)->sum('total_profit'), 2),
            'total_cash' => round(collect($history)->sum('total_cash'), 2),
            'total_online' => round(collect($history)->sum('total_online'), 2),
            'data' => $history,
            'code' => 200,
            'message' => 'Customer sales history fetched successfully.',
        ]);
    }

    private function customerSalesPeriodStart(Carbon $date, string $groupBy): Carbon
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

    private function customerSalesPeriodEnd(Carbon $date, string $groupBy): Carbon
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

    private function incrementCustomerSalesPeriod(Carbon $date, string $groupBy): Carbon
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
            'first_name' => 'required'
        ]);

        $exists = Customer::where('first_name', $request->first_name)
                        ->where('last_name', $request->last_name)
                        ->exists();

        if ($exists) {
               return response()->json([
            'message' => 'Customer already exists',
            'code' => 400
            ], 400);
        }

        $customer = new Customer;
        $customer->first_name = $request->input('first_name');
        $customer->last_name = $request->input('last_name');
        $customer->contact_number = $request->input('contact_number');
        $customer->store_name = $request->input('store_name');
        $customer->email = $request->input('email');
        $customer->address = $request->input('address');
        $customer->ads = $request->input('ads');
        $customer->backlog = 0;
        $customer->user_id = $request->input('user_id');
        $customer->save();

        return response()->json([
            'message' => 'Success',
            'code' => 200
            ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($customer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer = Customer::find($customer->id);
        
        $customer->first_name = $request->input('first_name');
        $customer->last_name = $request->input('last_name');
        $customer->store_name = $request->input('store_name');
        $customer->contact_number = $request->input('contact_number');
        $customer->email = $request->input('email');
        $customer->address = $request->input('address');
        $customer->disabled = $request->input('disabled');
        $customer->ads = $request->input('ads');
        $customer->save();
      

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer = Customer::find($customer->id);
        $customer->delete();
        return response()->json($customer);
    }
}
