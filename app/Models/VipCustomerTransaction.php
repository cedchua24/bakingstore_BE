<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipCustomerTransaction extends Model
{
    protected $table = 'vip_customer_transaction';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_customer_id',
        'customer_id',
    ];
}
