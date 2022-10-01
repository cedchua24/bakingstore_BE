<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSupplierTransaction extends Model
{
    use HasFactory;
    
    protected $table ='order_supplier_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
