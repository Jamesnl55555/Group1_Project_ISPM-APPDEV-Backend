<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    public function via($notifiable)
    {
        // We’re not using mail channels, just API
        return ['custom_mailersend'];
    }

    public function toMailersend($notifiable)
    {
        // Generate the signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // your verify route
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey()]
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MAILERSEND_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.mailersend.com/v1/email', [
            'from' => [
                'email' => env('MAILERSEND_FROM_EMAIL'),
                'name' => env('MAILERSEND_FROM_NAME'),
            ],
            'to' => [
                [
                    'email' => $notifiable->email,
                    'name' => $notifiable->name,
                ]
            ],
            'subject' => 'Verify Your Email Address',
            'text' => "Click the link to verify your email: $verificationUrl",
        ]);

        return $response->json();
    }
}
