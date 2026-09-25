<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrderTransaction extends Model
{
    protected $table ='shop_order_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'requestor');
    }

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class, 'shop_transaction_id')->orderBy('id');
    }
}
