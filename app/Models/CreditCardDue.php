<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardDue extends Model
{
    protected $table ='credit_card_due';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
