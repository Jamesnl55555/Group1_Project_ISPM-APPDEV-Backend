<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Http;

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

        // Generate SPA-friendly verification link (Vercel frontend)
        $frontendUrl = env('FRONTEND_URL'); // e.g., https://your-app.vercel.app
        $verificationUrl = $frontendUrl . "/confirm-register?token={$token}";

        // Send email using MailerSend API
        $subject = 'Complete Your Registration';
        $text = "Hi {$pending->name},\n\n";
        $text .= "Click this link to complete your registration:\n";
        $text .= "$verificationUrl\n\n";
        $text .= "If you did not register, ignore this email.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MAILERSEND_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.mailersend.com/v1/email', [
            'from' => [
                'email' => env('MAILERSEND_FROM_EMAIL'),
                'name' => env('MAILERSEND_FROM_NAME'),
            ],
            'to' => [
                ['email' => $pending->email, 'name' => $pending->name]
            ],
            'subject' => $subject,
            'text' => $text, // plain text link
        ]);
        dd($response->body());

        if (!$response->successful()) {
            $pending->delete();
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
