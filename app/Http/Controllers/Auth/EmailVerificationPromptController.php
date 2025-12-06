<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt status.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'verified' => $user->hasVerifiedEmail(),
            'status' => session('status') ?? null,
        ]);
    }
}
