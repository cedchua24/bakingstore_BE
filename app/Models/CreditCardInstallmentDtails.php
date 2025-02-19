<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardInstallmentDtails extends Model
{
    protected $table ='credit_card_installment_details';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
