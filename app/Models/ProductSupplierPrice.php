<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSupplierPrice extends Model
{
    protected $table ='product_supplier_price';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
