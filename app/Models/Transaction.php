<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
     use HasFactory;
    
    protected $fillable = [
        'user_name',
        'product_number',
        'product_name',
        'quantity', 
        'price',
        'total_amount',
    ];
}
