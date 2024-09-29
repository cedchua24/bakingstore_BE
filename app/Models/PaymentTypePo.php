<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTypePo extends Model
{
    use HasFactory;
    
    protected $table ='payment_type_po';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
