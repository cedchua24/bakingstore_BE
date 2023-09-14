<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensesCategory extends Model
{
    protected $table ='expenses_category';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
