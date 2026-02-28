<?php

namespace App\Notifications;

use App\Services\BrevoMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    protected string $token;
    protected BrevoMailService $mailer;

    public function __construct(string $token)
    {
        $this->token = $token;
        // Inject BrevoMailService via service container
        $this->mailer = app(BrevoMailService::class);
    }

    public function via($notifiable)
    {
        return ['mail']; // Use the mail channel
    }

    public function toMail($notifiable)
    {
        $resetUrl = url("/reset-password/{$this->token}?email={$notifiable->email}");

        $subject = 'Reset Your Password';
        $htmlContent = "<p>Hello {$notifiable->name},</p>
                        <p>Click the link below to reset your password:</p>
                        <a href='{$resetUrl}'>Reset Password</a>";

        // Send email via Brevo
        $this->mailer->sendEmail(
            $notifiable->email,
            $notifiable->name,
            $subject,
            $htmlContent
        );
    }
}