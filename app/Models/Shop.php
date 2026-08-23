<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table ='shop';
    
    public $primaryKey ='id';

    public $timestamps = true;

    public function getColorAttribute($value)
    {
        return config('database.connections.mysql.host') === '127.0.0.1'
            ? 'pink'
            : $value;
    }
}
