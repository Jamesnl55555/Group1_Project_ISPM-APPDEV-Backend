<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MailerSendService
{
    public function sendEmail($toEmail, $toName, $subject, $text)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MAILERSEND_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.mailersend.com/v1/email', [
            'from' => ['email' => env('MAILERSEND_FROM_EMAIL'), 'name' => env('MAILERSEND_FROM_NAME')],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'subject' => $subject,
            'text' => $text,
        ]);

        return $response->json();
    }
}
