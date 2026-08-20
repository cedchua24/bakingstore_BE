<?php

namespace App\Http\Controllers;

use App\Models\ModeOfPaymentPo;
use App\Models\OrderSupplierTransaction;
use App\Models\CreditCardPayment;
use App\Models\PaymentTypePo;
use App\Models\CreditCardDue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ModeOfPaymentPoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modeOfPaymentPo = ModeOfPaymentPo::all();
        // return view('categories.index')->with('categories', $categories);
        return response()->json($modeOfPaymentPo);
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
            'payment_term_id' => 'required'
        ]);

        // $item = UserProfile::create($data);


        // Create Post
        $modeOfPaymentPo = new ModeOfPaymentPo;
        $modeOfPaymentPo->payment_type_po_id = $request->input('payment_type_po_id');
        $modeOfPaymentPo->payment_term_id = $request->input('payment_term_id');
        $modeOfPaymentPo->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $modeOfPaymentPo->amount = $request->input('amount');
        $modeOfPaymentPo->date = $request->input('date');
        $modeOfPaymentPo->status = $request->input('status');
        $modeOfPaymentPo->type = $request->input('type');
        $modeOfPaymentPo->save();



        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->payment_status = 0;
        $orderSupplierTransaction->save();
    
        $response = [
              'message' => "Successfully Added"
          ];
        return  response()->json($response);
    }

        public function updateOnlinePaymentPO(Request $request) //online
    {
        $timestamp = strtotime($request->input('date'));
        $day = date('d', $timestamp);

        if ($request->input('payment_term_id') == 4) {
            $paymentTypePo = PaymentTypePo::find($request->input('payment_type_po_id'));

            $paymentTypePo->total_balance_due = $paymentTypePo->total_balance_due + $request->input('amount');
            $paymentTypePo->save();

            $statement_day =  $paymentTypePo->statement_date;
            $minus = 0;

            if ($statement_day > $day ) {
                $addMonth = Carbon::parse($request->input('date'));
            } else {
                $minus = $paymentTypePo->statement_date - $day;
                    $addMonth = Carbon::parse($request->input('date'))->addMonths(1);
            }                          
            $newDate =  strtotime($addMonth);
            $month = date('m', $newDate);
            $year = date('Y', $newDate);
            $due_date = $year ."-". $month ."-". $paymentTypePo->due_date;  

              $creditCardDue = DB::table('credit_card_due')
              ->where('payment_type_po_id', $request->input('payment_type_po_id'))
              ->where('due_date',  $due_date)
              ->orderBy('due_date', 'asc')
              ->first();

            if ($creditCardDue != null ) {    
                $creditCardDue = CreditCardDue::find($creditCardDue->id);        
                $creditCardDue->amount = $creditCardDue->amount + $request->input('amount');
                $creditCardDue->status = 0;
            } else {
                $creditCardDue = new CreditCardDue;
                $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
                $creditCardDue->min_amount = 0;
                $creditCardDue->amount =$request->input('amount');
                $creditCardDue->interest_amount =0;
                $creditCardDue->is_installment = 0;
                $creditCardDue->due_date = $due_date;
                $creditCardDue->status = 0;
                $creditCardDue->type = 'CREDIT_CARD';
             
            }
               $creditCardDue->save();

       } else if ($request->input('payment_term_id') == 3) { 
            $creditCardDue = new CreditCardDue;
            $creditCardDue->payment_type_po_id = $request->input('payment_type_po_id');
            $creditCardDue->amount = $request->input('amount');
            $creditCardDue->due_date = $request->input('date');
            $creditCardDue->type = 'CHEQUE';
            $creditCardDue->save(); 
       }

         $response = [
              'message' => "Successfully Added",
              'creditCardDue' => $creditCardDue
          ];
        return  response()->json($response);

    }

       public function fetchPaymentTypePoByShopTransactionId($id)
    {
         $data = DB::table('mode_of_payment_po as mop')
            ->join('payment_type_po as p', 'p.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'p.bank_id', '=', 'b.id')   
            ->join('payment_term as pt', 'pt.id', '=', 'p.payment_term_id') 
            ->join('order_supplier_transaction as sot', 'sot.id', '=', 'mop.order_supplier_transaction_id')
            ->select('mop.id', 'mop.order_supplier_transaction_id',  'mop.amount',
              'p.account_number', 'p.account_name', 'p.account_description', 'p.due_date', 'p.credit_limit', 'p.status', 'b.bank_name', 'pt.payment_term',
              'p.statement_date', 'p.total_balance_due',
              'sot.total_transaction_price', 'mop.payment_type_po_id', 'mop.date as payment_date')
            ->where('mop.order_supplier_transaction_id', '=', $id)    
            ->get();

            $total_payment = 0;

            $total_payment = DB::table('mode_of_payment_po as mop')
                ->where('mop.order_supplier_transaction_id', $id)
                ->sum('mop.amount');


            $balance = 0;
            if (count($data) == 0 ) {
             $orderSupplierTransaction = OrderSupplierTransaction::find($id);
             $balance= $orderSupplierTransaction->total_transaction_price;

            } else {
              $balance = $data[0]->total_transaction_price - $total_payment;
            }

           $response = [
              'data' => $data,
              'balance' => $balance,
              'total_payment' => $total_payment,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    public function fetchSupplierPaymentTransactionListByDateRange(Request $request)
    {
        $validated = $request->validate([
            'dateFrom' => ['required', 'date_format:Y-m-d'],
            'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
        ]);

        $payments = DB::table('mode_of_payment_po as mop')
            ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')
            ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_po_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->whereBetween('mop.date', [$validated['dateFrom'], $validated['dateTo']])
            ->orderByDesc('mop.date')
            ->orderByDesc('mop.id')
            ->select(
                'mop.id',
                'mop.order_supplier_transaction_id',
                'mop.payment_type_po_id',
                'mop.payment_term_id as payment_payment_term_id',
                'mop.amount',
                'mop.date as payment_date',
                'mop.type as payment_type',
                'mop.status as payment_status',
                'mop.created_at as payment_created_at',
                'mop.updated_at as payment_updated_at',
                'ost.supplier_id',
                'ost.invoice_number',
                'ost.withTax',
                'ost.total_transaction_price',
                'ost.status as order_status',
                'ost.payment_status as order_payment_status',
                'ost.approval_status',
                'ost.stock_status',
                'ost.order_date',
                'ost.note',
                's.supplier_name',
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

        $banks = $payments
            ->whereNotNull('bank_id')
            ->unique('bank_id')
            ->map(fn ($payment) => [
                'id' => $payment->bank_id,
                'bank_name' => $payment->bank_name,
                'account_number' => $payment->account_number,
                'account_name' => $payment->account_name,
                'account_description' => $payment->account_description,
                'status' => $payment->bank_status,
                'created_at' => $payment->bank_created_at,
                'updated_at' => $payment->bank_updated_at,
            ])
            ->values();

        $paymentTypePo = $payments
            ->unique('payment_type_po_id')
            ->map(fn ($payment) => [
                'id' => $payment->payment_type_po_id,
                'payment_term_id' => $payment->payment_term_id,
                'bank_id' => $payment->bank_id,
                'account_number' => $payment->account_number,
                'account_name' => $payment->account_name,
                'account_description' => $payment->account_description,
                'due_date' => $payment->due_date,
                'buffer_days' => $payment->buffer_days,
                'credit_limit' => $payment->credit_limit,
                'statement_date' => $payment->statement_date,
                'total_balance_due' => $payment->total_balance_due,
                'balance' => $payment->payment_type_po_balance,
                'status' => $payment->payment_type_po_status,
                'is_supplier' => $payment->is_supplier,
                'is_customer' => $payment->is_customer,
                'payment_term' => $payment->payment_term,
                'payment_term_status' => $payment->payment_term_status,
                'created_at' => $payment->payment_type_po_created_at,
                'updated_at' => $payment->payment_type_po_updated_at,
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

        $payments->each(function ($payment) use ($accountDetailFields) {
            foreach ($accountDetailFields as $field) {
                unset($payment->{$field});
            }
        });

        $orderIds = $payments->pluck('order_supplier_transaction_id')->unique()->values();
        $orderItems = collect();

        if ($orderIds->isNotEmpty()) {
            $orderItems = DB::table('order_supplier as os')
                ->leftJoin('products as p', 'p.id', '=', 'os.product_id')
                ->whereIn('os.order_supplier_transaction_id', $orderIds)
                ->orderBy('os.id')
                ->select(
                    'os.id',
                    'os.order_supplier_transaction_id',
                    'os.product_id',
                    'p.product_name',
                    'os.price',
                    'os.quantity',
                    'os.total_price',
                    'os.stock_remaining',
                    'os.stock_pc',
                    'os.stock',
                    'os.variation',
                    'os.expiration',
                    'os.enable'
                )
                ->get()
                ->groupBy('order_supplier_transaction_id');
        }

        $payments->each(function ($payment) use ($orderItems) {
            $payment->order_supplier = $orderItems
                ->get($payment->order_supplier_transaction_id, collect())
                ->values();
        });

        $paymentAccounts = $payments
            ->groupBy('payment_type_po_id')
            ->map(function ($accountPayments) {
                $account = $accountPayments->first();

                return [
                    'payment_type_po_id' => $account->payment_type_po_id,
                    'total_amount' => $accountPayments->sum('amount'),
                    'payment_count' => $accountPayments->count(),
                    'payments' => $accountPayments->values(),
                ];
            })
            ->values();

        return response()->json([
            'dateFrom' => $validated['dateFrom'],
            'dateTo' => $validated['dateTo'],
            'total_amount' => $payments->sum('amount'),
            'total_count' => $payments->count(),
            'payment_account_count' => $paymentAccounts->count(),
            'bank' => $banks->first(),
            'payment_type_po' => $paymentTypePo,
            'data' => $paymentAccounts,
            'code' => 2020,
            'message' => 'Successfully fetched supplier payment transactions',
        ]);
    }

     public function fetchCreditCardPaymentDTO($id)
    {

         $modeOfPaymentPo = ModeOfPaymentPo::find($id);

           $data = DB::table('mode_of_payment_po as mop')
            ->join('payment_type_po as p', 'p.id', '=', 'mop.payment_type_po_id')
            ->join('bank as b', 'p.bank_id', '=', 'b.id')   
            ->join('payment_term as pt', 'pt.id', '=', 'p.payment_term_id') 
            ->select('mop.id', 'mop.order_supplier_transaction_id',  'mop.amount',
              'p.account_number', 'p.account_name', 'p.account_description', 'p.due_date', 'p.credit_limit', 'p.status', 'b.bank_name', 'pt.payment_term',
              'p.statement_date', 'p.total_balance_due', 'p.id as payment_type_po_id',
             'mop.payment_type_po_id')
            ->where('mop.id', '=', $id)    
            ->get();

            $total_payment = 0;

            $total_payment = DB::table('credit_card_payment as ccp')
                ->where('ccp.mode_of_payment_po_id', $id)
                ->sum('ccp.amount');

            $balance = 0;
            if (count($data) == 0 ) {
             $modeOfPaymentPo = ModeOfPaymentPo::find($id);
             $balance = $modeOfPaymentPo->amount;

            } else {
              $balance = $data[0]->amount - $total_payment;
            }

           $response = [
              'data' => $data,
              'balance' => $balance,
              'modeOfPaymentPo' => $modeOfPaymentPo,
              'total_payment' => $total_payment,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

       public function setToCompleteCreditCard($id)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($id);
        $modeOfPaymentPo->status = 0;
        $modeOfPaymentPo->save();
      

        return response()->json($modeOfPaymentPo);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function show(ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPaymentPo);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function edit(ModeOfPaymentPo $modeOfPaymentPo)
    {

        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($modeOfPaymentPo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);
        $modeOfPaymentPo->payment_type_po_id = $request->input('payment_type_po_id');
        $modeOfPaymentPo->order_supplier_transaction_id = $request->input('order_supplier_transaction_id');
        $modeOfPaymentPo->amount = $request->input('amount');
        $modeOfPaymentPo->status = $request->input('status');
        $modeOfPaymentPo->type = $request->input('type');
        $modeOfPaymentPo->save();

        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('order_supplier_transaction_id'));
        $orderSupplierTransaction->payment_status = 0;
        $orderSupplierTransaction->save();

        $response = [
              'data' => $orderSupplierTransaction,
              'code' => 200,
              'message' => "Successfully Added ey"
          ];
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModeOfPaymentPo  $modeOfPaymentPo
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModeOfPaymentPo $modeOfPaymentPo)
    {
        $modeOfPaymentPo = ModeOfPaymentPo::find($modeOfPaymentPo->id);    

        $shopOrderTransaction = OrderSupplierTransaction::find($modeOfPaymentPo->order_supplier_transaction_id);
        $shopOrderTransaction->payment_status = 0;
        $shopOrderTransaction->save();
        $modeOfPaymentPo->delete();
        
        return response()->json($modeOfPaymentPo);
    }
}
