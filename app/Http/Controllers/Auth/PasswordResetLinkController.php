<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle a forgot password request
     */
    public function store(Request $request)
    {
        // Validate email input
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Attempt to send the password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset email. Please check logs.',
            ], 500);
        }
    }
}