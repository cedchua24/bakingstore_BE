<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
        protected $table ='balance_transaction';
    
        public $primaryKey ='id';

        public $timestamps = true;
}
