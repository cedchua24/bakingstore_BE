<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySession extends Model
{
    protected $table ='daily_session';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
