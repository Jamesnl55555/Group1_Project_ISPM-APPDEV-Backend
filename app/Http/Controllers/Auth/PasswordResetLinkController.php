<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Helpers\MailerSendHelper;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Check if user exists
        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => true, // do NOT reveal that email does not exist
                'message' => 'If your email exists, a reset link was sent.'
            ]);
        }

        // Create reset token
        $token = Str::random(64);

        // Store token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Build reset link URL
        $resetUrl = env('FRONTEND_URL') . "/reset-password?token=" . $token . "&email=" . $request->email;

        // Send email using MailerSend API
        MailerSendHelper::sendEmail(
            $request->email,
            $user->name ?? "User",
            "Reset Your Password",
            "Click the following link to reset your password:\n\n$resetUrl\n\nIf you did not request this, ignore this email."
        );

        return response()->json([
            'success' => true,
            'message' => 'Reset link sent!'
        ]);
    }
}
