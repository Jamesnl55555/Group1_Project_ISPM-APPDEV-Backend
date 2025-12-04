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

        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
             RateLimiter::hit($request->throttleKey());

             throw ValidationException::withMessages([
                 'email' => [trans('auth.failed')],
             ]);
        }

        RateLimiter::clear($request->throttleKey());

        $user = User::where('email', $request->email)->firstOrFail(); 

        $token = $user->createToken('auth-token')->plainTextToken;


        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
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