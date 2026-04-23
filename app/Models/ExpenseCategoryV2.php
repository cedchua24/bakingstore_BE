<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategoryV2 extends Model
{
    protected $table ='expenses_category_v2'; //
    
    public $primaryKey ='id';

    public $timestamps = true;
}
