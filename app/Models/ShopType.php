<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopType extends Model
{
    protected $table ='shop_type';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
