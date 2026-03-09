<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;
    public function capitals(): HasMany
    {
        return $this->hasMany(Capital::class);
    }

    public function product_histories(): HasMany
    {
        return $this->hasMany(ProductHistory::class);
    }
    public function transaction_histories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'capital',
        'storeName',
        'profile_image'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


    // Override default password reset notification
    public function sendPasswordResetNotification($token){
    $this->notify(new CustomResetPasswordNotification($token));
    }
}
