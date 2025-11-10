<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    // public function create(): Response
    // {
    //     return Inertia::render('Auth/Login', [
    //         'canResetPassword' => Route::has('password.request'),
    //         'status' => session('status'),
    //     ]);
    // }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->json([
        'success' => true,
        'user' => $request->user(),
        'token' => $request->user()->createToken('api-token')->plainTextToken,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
        'success' => true
        ]);
    }
}
