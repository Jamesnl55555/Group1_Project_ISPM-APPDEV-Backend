<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle SPA login.
     */
    public function store(LoginRequest $request)
    {
    $request->ensureIsNotRateLimited();
    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials'],
        ]);
    }

    $request->session()->regenerate();

    return response()->json(['success' => true, 'user' => Auth::user()]);
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
