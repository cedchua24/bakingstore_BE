<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintingTransaction extends Model
{
    protected $table = 'printing_transaction';

    protected $fillable = [
        'shop_order_transaction_id', 'order_coordinator_id', 'logo', 'plate',
        'mock_up_status', 'sales_channel', 'order_priority', 'order_status',
        'order_date', 'sent_date', 'received_date',
    ];

    protected $casts = ['plate' => 'boolean'];

    public function shopOrderTransaction()
    {
        return $this->belongsTo(ShopOrderTransaction::class);
    }

    public function orderCoordinator()
    {
        return $this->belongsTo(User::class, 'order_coordinator_id');
    }

    public function comments()
    {
        return $this->hasMany(PrintingTransactionComment::class)->orderBy('id');
    }
}
