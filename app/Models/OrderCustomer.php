<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCustomer extends Model
{
    use HasFactory;
    
    protected $table ='order_customer';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
