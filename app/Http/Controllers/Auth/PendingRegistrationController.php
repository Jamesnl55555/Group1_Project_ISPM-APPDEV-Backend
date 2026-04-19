<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Jobs\SendVerificationEmail;

    class PendingRegistrationController extends Controller
    {

    public function store(Request $request)
    {
    $request->merge([
        'email' => strtolower(trim($request->email))
    ]);
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);
    

    $domain = substr(strrchr($request->email, "@"), 1);

    if (!checkdnsrr($domain, "MX")) {
    return response()->json([
        'errors' => [
            'email' => ['Please enter a valid email address that can receive emails.']
        ]
    ], 422);
    }

    $code = random_int(100000, 999999);

    $pending = PendingRegistration::updateOrCreate(
        ['email' => $request->email],
        [
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'code' => bcrypt($code),
            'code_expires_at' => now()->addMinutes(15),
        ]
    );

    SendVerificationEmail::dispatch(
    $request->email,
    $request->name,
    'Verify Your Account',
    "
    <p>Hello {$request->name},</p>
    <p>Your verification code is:</p>
    <h2>{$code}</h2>
    <p>This will expire in 15 minutes.</p>
    "
    );

    return response()->json([
        'success' => true,
        'message' => 'Verification code sent to email.'
    ]);
    }

    public function confirm(Request $request)
    {
    $request->merge([
        'email' => strtolower(trim($request->email))
    ]);
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|digits:6',
    ]);

    $pending = PendingRegistration::where('email', $request->email)->first();

    if (!$pending) {
        return response()->json(['message' => 'Invalid request'], 422);
    }

    if ($pending->code_expires_at < now()) {
        return response()->json(['message' => 'Code expired'], 422);
    }

    if (!Hash::check($request->code, $pending->code)) {
        return response()->json(['message' => 'Invalid code'], 422);
    }

    User::create([
        'name' => $pending->name,
        'email' => $pending->email,
        'password' => $pending->password,
    ]);

    $pending->delete();

    return response()->json([
        'success' => true,
        'message' => 'Registration complete'
    ]);
    }

    public function resend(Request $request)
    {
    $request->merge([
        'email' => strtolower(trim($request->email))
    ]);
    $request->validate([
        'email' => 'required|email',
    ]);

    $pending = PendingRegistration::where('email', $request->email)->first();

    if (!$pending) {
        return response()->json(['message' => 'No pending registration found'], 404);
    }

    $code = random_int(100000, 999999);

    $pending->update([
        'code' => bcrypt($code),
        'code_expires_at' => now()->addMinutes(15),
    ]);

    SendVerificationEmail::dispatch(
    $pending->email,
    $pending->name,
    'Your New Verification Code',
    "
    <p>Hello {$pending->name},</p>
    <p>Your new verification code is:</p>
    <h2>{$code}</h2>
    <p>This will expire in 15 minutes.</p>
    "
    );

    return response()->json([
        'success' => true,
        'message' => 'New code sent to email.'
    ]);
    }
}
