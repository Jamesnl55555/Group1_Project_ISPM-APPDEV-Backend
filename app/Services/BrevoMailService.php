<?php

namespace App\Services;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\Exceptions\BrevoApiException;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    protected Brevo $client;

    public function __construct()
    {
        // Initialize Brevo client with REST API key (xkeysib-...)
        $this->client = new Brevo(env('BREVO_API_KEY'));
    }

    /**
     * Send a transactional email
     */
    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlContent): void
    {
        $sender = new SendTransacEmailRequestSender([
            'email' => env('MAIL_FROM_ADDRESS', 'jamesnl55555@gmail.com'),
            'name' => env('MAIL_FROM_NAME', '88Chocolates'),
        ]);

        $toItem = new SendTransacEmailRequestToItem([
            'email' => $toEmail,
            'name' => $toName,
        ]);

        $request = new SendTransacEmailRequest([
            'sender' => $sender,
            'to' => [$toItem],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        try {
            $this->client->transactionalEmails->sendTransacEmail($request);
        } catch (BrevoApiException $e) {
            Log::error('Brevo email failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw for debugging
            throw $e;
        }
    }
}