<?php

namespace App\Notifications;

use App\Services\BrevoMailService;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        // Using custom Brevo mail service
        return ['custom'];
    }

    public function toCustom($notifiable)
    {
        $brevo = new BrevoMailService();
        $resetUrl = url("/reset-password/{$this->token}?email={$notifiable->email}");

        $subject = 'Reset Your Password';
        $htmlContent = "<p>Hello {$notifiable->name},</p>
                        <p>Click the link below to reset your password:</p>
                        <a href='{$resetUrl}'>Reset Password</a>";

        $brevo->sendEmail($notifiable->email, $notifiable->name, $subject, $htmlContent);
    }
}