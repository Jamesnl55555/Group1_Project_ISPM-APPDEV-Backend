<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Helpers\MailerSendHelper;

class PendingRegistrationController extends Controller
{
    // STEP 1: Store pending registration & send verification email
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pending_registrations,email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $token = Str::random(64);

        $pending = PendingRegistration::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        // Generate SPA-friendly verification link
        $verificationUrl = url("/confirm-register/verify?token={$token}");

        // Send email using your MailerSendHelper
        $subject = 'Complete Your Registration';
        $text = "Hi {$pending->name},<br><br>";
        $text .= "<a href='{$verificationUrl}' style='display:inline-block;padding:10px 20px;background:#422912;color:white;border-radius:6px;text-decoration:none;'>Verify Email</a><br><br>";
        $text .= "If you did not register, ignore this email.";

        $sent = MailerSendHelper::sendEmail($pending->email, $pending->name, $subject, $text);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again later.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A verification email has been sent. Please check your inbox.',
        ]);
    }

    // STEP 2: Confirm email & create the real user
    public function confirm(Request $request)
    {
        $pending = PendingRegistration::where('token', $request->token)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$pending) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }

        // Create the actual user
        $user = User::create([
            'name' => $pending->name,
            'email' => $pending->email,
            'password' => $pending->password,
        ]);

        // Delete pending registration
        $pending->delete();

        // Create login token for SPA auto-login
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration complete!',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
