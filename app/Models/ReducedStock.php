<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReducedStock extends Model
{
    protected $table ='reduced_stock';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
