<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
     use HasFactory;
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected $fillable = [
        'user_id',
        'user_name',
        'product_number',
        'variety_of_items',
        'product_name',
        'quantity', 
        'price',
        'total_amount',
    ];
}
