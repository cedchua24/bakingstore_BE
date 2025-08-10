<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table ='discount';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
