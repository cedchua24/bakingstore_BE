<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $table ='product_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
