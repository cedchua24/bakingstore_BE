<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    protected $table ='expenses';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
