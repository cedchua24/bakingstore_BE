<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensesType extends Model
{
    protected $table ='expenses_type';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
