<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerUpdate extends Model
{
    protected $table ='customer_update';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
