<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnToSeller extends Model
{
    protected $table ='return_to_seller';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
