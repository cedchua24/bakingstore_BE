<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    protected $table ='shop_order';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
