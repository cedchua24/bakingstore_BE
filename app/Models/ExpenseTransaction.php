<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseTransaction extends Model
{
    protected $table ='expenses_transaction'; //
    
    public $primaryKey ='id';

    public $timestamps = true;
}
