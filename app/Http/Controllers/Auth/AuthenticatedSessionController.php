<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
/**
     * Display the login view.
     */
    public function create()
    {
        return response()->json([
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status')
        ]);
    }

    /**
     * Handle an incoming authentication request.

    */
    public function store(LoginRequest $request)
    {
    $request->ensureIsNotRateLimited();

    $email = $request->email;
    $password = $request->password;

    // 1. Check if user exists
    $user = User::where('email', $email)->first();

    if (!$user) {
        RateLimiter::hit($request->throttleKey());

        throw ValidationException::withMessages([
            'email' => ['Email does not exist.'],
        ]);
    }

    // 2. Check password separately
    if (!Auth::attempt(['email' => $email, 'password' => $password])) {
        RateLimiter::hit($request->throttleKey());

        throw ValidationException::withMessages([
            'password' => ['Wrong password.'],
        ]);
    }

    RateLimiter::clear($request->throttleKey());

    $user = $request->user()->fresh();

    $remember = $request->boolean('remember');

    $token = $user->createToken('auth-token');

    $token->accessToken->forceFill([
        'remember' => $remember,
    ])->save();

    return response()->json([
        'success' => true,
        'user' => [
            'name' => $user->name,
            'storeName' => $user->storeName,
            'email' => $user->email,
            'profile_image' => $user->profile_image
        ],
        'token' => $token->plainTextToken,
    ]);
    }
    
    /**
     * Logout (revoke current token)
    */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}