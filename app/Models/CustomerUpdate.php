<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerUpdate extends Model
{
    protected $table ='customer_update';

    protected $casts = [
        'customer_id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
    ];
    
    public $primaryKey ='id';

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
