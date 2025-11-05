<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSoldDaily extends Model
{
    protected $table ='product_sold_daily';
    
    public $primaryKey ='id';

    public $timestamps = true;
    
        protected $fillable = [
        'product_id',
        'product_code',
        'stock',
        'stock_pc',
        'total_stock',
        'current_stock',
        'stock_input',
        'date',
        'status',
    ];
}
