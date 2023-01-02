<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    
        protected $table ='branch_stock';
    
        public $primaryKey ='id';

        public $timestamps = true;
}
