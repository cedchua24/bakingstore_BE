<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    protected $table ='customer_type';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
