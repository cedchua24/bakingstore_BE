<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkUpProduct extends Model
{
    protected $table ='mark_up_product';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
