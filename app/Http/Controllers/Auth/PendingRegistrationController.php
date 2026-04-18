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

use Illuminate\Support\Facades\DB;

    class PendingRegistrationController extends Controller
    {

    public function store(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:pending_registrations,email|unique:users,email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

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

    app(\App\Services\BrevoMailService::class)->sendEmail(
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

    $user = User::create([
        'name' => $pending->name,
        'email' => $pending->email,
        'password' => $pending->password,
    ]);

    $pending->delete();

    return response()->json([
        'success' => true,
        'message' => 'Registration complete',
        'user' => $user,
    ]);
    }
}
