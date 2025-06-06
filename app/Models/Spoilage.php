<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spoilage extends Model
{
    protected $table ='spoilage';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
