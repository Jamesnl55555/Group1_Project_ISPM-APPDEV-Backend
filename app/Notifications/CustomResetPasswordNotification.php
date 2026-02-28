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
        return [];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url("/reset-password/{$this->token}?email={$notifiable->email}");
        $subject = 'Reset Your Password';
        $htmlContent = "<p>Hello {$notifiable->name},</p>
                        <p>Click the link below to reset your password:</p>
                        <a href='{$resetUrl}'>Reset Password</a>";

        try {
            $brevo = new BrevoMailService();
            $brevo->sendEmail($notifiable->email, $notifiable->name, $subject, $htmlContent);
        } catch (\Exception $e) {
            Log::error('Brevo email error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ]);

            throw $e;
        }
    }
}