<?php

namespace App\Notifications;

use App\Services\BrevoMailService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CustomResetPasswordNotification extends Notification
{
    protected string $token;
    protected BrevoMailService $mailer;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->mailer = new BrevoMailService();
    }

    public function via($notifiable)
    {
        return ['custom'];
    }

    public function toCustom($notifiable)
    {
        $resetUrl = url("/reset-password/{$this->token}?email={$notifiable->email}");

        $subject = 'Reset Your Password';
        $htmlContent = "<p>Hello {$notifiable->name},</p>
                        <p>Click the link below to reset your password:</p>
                        <p><a href='{$resetUrl}'>Reset Password</a></p>";

        try {
            $this->mailer->sendEmail(
                $notifiable->email,
                $notifiable->name,
                $subject,
                $htmlContent
            );
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('Brevo email failed in notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $notifiable->email,
            ]);

            // Re-throw so Laravel still returns 500
            throw $e;
        }
    }
}