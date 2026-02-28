<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'If an account with that email exists, a reset link has been sent.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (\Exception $e) {
            Log::error('Forgot password failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
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