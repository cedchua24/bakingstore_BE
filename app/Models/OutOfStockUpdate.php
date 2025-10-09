<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutOfStockUpdate extends Model
{
    use HasFactory;
    
    protected $table ='out_of_stock_update';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
