<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSupplier extends Model
{
    use HasFactory;
    
    protected $table ='order_supplier';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
