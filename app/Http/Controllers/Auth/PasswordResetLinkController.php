<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    try {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'status' => $status
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
}
}
