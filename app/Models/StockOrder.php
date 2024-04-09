<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrder extends Model
{
    protected $table ='stock_order';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
