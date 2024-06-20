<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    protected $table ='product_supplier';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
