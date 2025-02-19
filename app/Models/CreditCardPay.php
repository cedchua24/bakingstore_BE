<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardPay extends Model
{
    protected $table ='credit_card_pay';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
