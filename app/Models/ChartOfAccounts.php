<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccounts extends Model
{
    protected $table ='chart_of_accounts';
    
    public $primaryKey ='id';

    public $timestamps = true;
}
