<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\PaymentTypePo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceTransactionController extends Controller
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

        public function fetchBalanceTransactionById(Request $request)
    {
        $query = DB::table('balance_transaction as bt')
            ->join('balance_type as bty', 'bty.id', '=', 'bt.balance_type_id')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'bt.payment_type_po_id')
            ->join('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->join('shop as s', 's.id', '=', 'bt.shop_id')
            ->select(
                'bt.id',
                'bt.name',
                'bty.balance_type_name',
                'bt.transaction',
                'bt.amount',
                'bt.total_balance',
                'bt.payment_type_po_id',
                'bt.join_id',
                'bt.created_at',
                'pt.payment_term',
                'b.bank_name',
                'ptp.account_name',
                'ptp.account_description',
                'ptp.account_number',
                's.shop_name'
            )
            ->where('bt.payment_type_po_id', $request->input('id'))

            // ✅ Filters
            ->when(
                filled($request->input('balance_type_id')) && $request->input('balance_type_id') != 0,
                fn ($q) => $q->where('bt.balance_type_id', $request->input('balance_type_id'))
            )
            ->when(
                filled($request->input('dateFrom')) && filled($request->input('dateTo')),
                fn ($q) => $q->whereBetween('bt.created_at', [
                    $request->input('dateFrom'),
                    $request->input('dateTo')
                ])
            )

            ->orderByDesc('bt.id');

        $data = $query->get();

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Create Post
        $balanceTransaction = new BalanceTransaction;
        $balanceTransaction->payment_type_po_id = $request->input('payment_type_po_id');    
        $balanceTransaction->shop_id = $request->input('shop_id'); 
        $balanceTransaction->join_id = $request->input('join_id');     
        $balanceTransaction->name = $request->input('name');   
        $balanceTransaction->balance_type_id = $request->input('balance_type_id');
        $balanceTransaction->amount = $request->input('amount');
        $balanceTransaction->status = $request->input('status');
        $balanceTransaction->transaction = $request->input('transaction');
       

        $paymentTypePo = PaymentTypePo::find($request->input('payment_type_po_id'));
        if ($request->input('balance_type_id') == 3) { // EXPENSE
            $paymentTypePo->balance = $paymentTypePo->balance - $request->input('amount');
        }
       
        $balanceTransaction->total_balance = $paymentTypePo->balance;
        $balanceTransaction->save();
        $paymentTypePo->save();

        $response = [
              'data' => $balanceTransaction,
              'code' => 200,
              'message' => "Successfully Added"
          ];

        return response()->json($response);   
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BalanceTransaction  $balanceTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(BalanceTransaction $balanceTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BalanceTransaction  $balanceTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(BalanceTransaction $balanceTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BalanceTransaction  $balanceTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BalanceTransaction $balanceTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BalanceTransaction  $balanceTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(BalanceTransaction $balanceTransaction)
    {
        //
    }
}
