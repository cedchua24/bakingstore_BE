<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipProduct extends Model
{
    protected $table = 'vip_product';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_product_name',
        'details',
        'vip_color',
        'status',
    ];
}
