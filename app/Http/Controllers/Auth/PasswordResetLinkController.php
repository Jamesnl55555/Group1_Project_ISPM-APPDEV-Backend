<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Services\BrevoMailService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    protected BrevoMailService $mailer;

    public function __construct(BrevoMailService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                $this->mailer->sendEmail(
                    $request->email,
                    'User',
                    'Reset Your Password',
                    "<p>Click this link to reset your password: <a href='#'>Reset Link</a></p>"
                );

                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset email: ' . $e->getMessage(),
            ], 500);
        }
    }
    
}
