<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckListTransaction extends Model
{
    protected $table ='check_list_transaction';
    
    public $primaryKey ='id';

    public $timestamps = true;

    protected $fillable = [
        'check_list_id',
        'check_list_name',
        'assignee',
        'checker',
        'time_of_day',
        'comment',
        'date',
        'status',
    ];
}
