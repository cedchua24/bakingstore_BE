<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipProductNote extends Model
{
    protected $table = 'vip_product_note';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_product_transaction_id',
        'user_id',
        'comment',
        'status',
    ];
}
