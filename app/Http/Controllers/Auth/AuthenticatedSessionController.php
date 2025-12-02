<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
        $request->authenticate();
        
        $remember = $request->boolean('remember');
        $expiration = $remember ? now()->addWeeks(2) : now()->addHours(2);
        $tokenResult = $request->user()->createToken('api-token');
        $token = $tokenResult->plainTextToken;
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => Auth::user(),
            'token' => $token,
            'expires_at' => $expiration->toDateTimeString(),
        ]);
    }

    /**
     * Logout (revoke current token)
    */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}