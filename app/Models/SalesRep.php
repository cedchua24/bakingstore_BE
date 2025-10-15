<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesRep extends Model
{
    protected $table ='sales_rep';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
