<?php

namespace App\Notifications;

use App\Services\BrevoMailService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomResetPasswordNotification extends Notification
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        // Laravel won't try to use its default mailer
        return ['mail'];
    }

    public function toMail($notifiable)
    {
    $resetUrl = url("/reset-password/{$this->token}?email={$notifiable->email}");

    $brevo = new BrevoMailService();

    $brevo->sendEmail(
        $notifiable->email,
        $notifiable->name,
        'Reset Your Password',
        "<a href='{$resetUrl}'>Reset Password</a>"
    );

    return (new \Illuminate\Notifications\Messages\MailMessage)
        ->subject('Reset Password')
        ->line('If you do not see the email, check spam.');
    }
}