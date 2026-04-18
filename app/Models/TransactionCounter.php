<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCounter extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_number',
    ];
}
