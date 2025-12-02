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
        $request->authenticate(); // validates credentials

        $request->session()->regenerate(); // regenerate session ID

        return response()->json([
            'success' => true,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Logout (revoke current token)
    */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}