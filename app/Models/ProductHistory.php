<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'action',
        'changed_data',
    ];
}
