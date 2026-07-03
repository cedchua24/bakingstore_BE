<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipProductTransaction extends Model
{
    protected $table = 'vip_product_transaction';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_product_id',
        'product_id',
    ];
}
