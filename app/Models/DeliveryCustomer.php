<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCustomer extends Model
{
    protected $table ='delivery_customer';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
