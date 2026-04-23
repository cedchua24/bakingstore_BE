<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceType extends Model
{
        protected $table ='balance_type'; //
    
        public $primaryKey ='id';

        public $timestamps = true;
}
