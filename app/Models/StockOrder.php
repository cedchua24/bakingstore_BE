<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class StockOrder extends Model
{
    protected $table ='stock_order';
    
    public $primaryKey ='id';

    public $timestamps = true;

    /** Add attribution to a detail query using the stock_order alias "so". */
    public static function withUserDetails($query)
    {
        $query->leftJoin('users as stock_user', 'stock_user.id', '=', 'so.user_id')
            ->addSelect('stock_user.name as user_name');
    }
}