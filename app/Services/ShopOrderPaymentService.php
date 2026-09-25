<?php

namespace App\Services;

use App\Models\ShopOrderTransaction;
use Illuminate\Support\Facades\DB;

class ShopOrderPaymentService
{
    public function fetch($id): array
    {
        $shopOrderTransaction = ShopOrderTransaction::findOrFail($id);

        $data = DB::table('mode_of_payment as mop')
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_id')
            ->leftJoin('bank as b', 'b.id', '=', 'ptp.bank_id')
            ->leftJoin('payment_term as pt', 'pt.id', '=', 'ptp.payment_term_id')
            ->where('mop.shop_order_transaction_id', $id)
            ->select(
                'mop.id',
                'mop.shop_order_transaction_id',
                'mop.payment_type_id',
                'mop.amount',
                'mop.is_paid',
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

        $totalPayment = (float) DB::table('mode_of_payment')
            ->where('shop_order_transaction_id', $id)
            ->sum('amount');

        return [
            'data' => $data,
            'balance' => (float) $shopOrderTransaction->shop_order_transaction_total_price - $totalPayment,
            'total_payment' => $totalPayment,
            'message' => 'Successfully fetched payment details',
        ];
    }
}
