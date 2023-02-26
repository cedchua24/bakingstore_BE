<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrderTransaction extends Model
{
    protected $table ='shop_order_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
