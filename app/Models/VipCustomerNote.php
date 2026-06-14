<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipCustomerNote extends Model
{
    protected $table = 'vip_customer_note';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_customer_transaction_id',
        'user_id',
        'comment',
        'status',
    ];
}
