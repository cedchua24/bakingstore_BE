<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseV2 extends Model
{
    protected $table ='expenses_v2'; //
    
    public $primaryKey ='id';

    public $timestamps = true;
}
