<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCustomerTransaction extends Model
{
    use HasFactory;
    
    protected $table ='order_customer_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
