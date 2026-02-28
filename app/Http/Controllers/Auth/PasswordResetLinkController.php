<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Services\BrevoMailService;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    protected BrevoMailService $mailer;

    public function __construct(BrevoMailService $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle a forgot password request
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => true,
                    'message' => 'If an account with that email exists, a reset link has been sent.',
                ]);
            }

            // This automatically triggers sendPasswordResetNotification($token) on the user
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reset link sent successfully.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ], 500);
        }
    }
}