<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'capital',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }


    // Override default password reset notification
    public function sendPasswordResetNotification($token)
    {
        $resetUrl = env('FRONTEND_URL') . "/reset-password?token=$token&email={$this->email}";

        \App\Helpers\MailerSendHelper::sendEmail(
            $this->email,
            $this->name ?? 'User',
            'Reset Your Password',
            "Click the following link to reset your password:\n\n$resetUrl\n\nIf you did not request this, ignore this email."
        );
    }
}
