<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification via MailerSend API.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'verified' => true,
                'absolute' => false,
                'message' => 'Email already verified'
            ]);
        }

        // Generate signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->getKey()]
        );

        // Send email via MailerSend API
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
                    'email' => $user->email,
                    'name' => $user->name,
                ]
            ],
            'subject' => 'Verify Your Email Address',
            'text' => "Click this link to verify your email: $verificationUrl",
        ]);

        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => 'Verification email sent',
            'api_response' => $response->json(),
        ]);
    }
}
