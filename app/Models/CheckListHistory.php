<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckListHistory extends Model
{
    protected $table = 'check_list_history';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'check_list_transaction_id',
        'comment',
        'user_id',
        'status',
    ];
}
