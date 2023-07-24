<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table ='product_price';
    
    public $primaryKey ='id';

    public $timestamps = true;
    
}
