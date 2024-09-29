<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeOfPaymentPo extends Model
{
    use HasFactory;
    
    protected $table ='mode_of_payment_po';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
