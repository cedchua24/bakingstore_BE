<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutOfStockHistory extends Model
{    
    protected $table ='out_of_stock_history';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
