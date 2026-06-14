<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipCustomer extends Model
{
    protected $table = 'vip_customer';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'vip_name',
        'details',
        'vip_color',
        'status',
    ];
}
