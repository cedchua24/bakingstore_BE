<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintingTransactionComment extends Model
{
    protected $table = 'printing_transaction_comments';

    protected $fillable = ['printing_transaction_id', 'user_id', 'comment'];

    public function printingTransaction()
    {
        return $this->belongsTo(PrintingTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
