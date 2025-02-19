<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPaymentTransaction extends Model
{
    protected $table ='installment_payment_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
