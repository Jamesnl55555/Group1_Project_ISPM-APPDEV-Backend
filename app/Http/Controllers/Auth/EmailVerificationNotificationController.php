<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
            'success' => true,
            'verified' => true,
            'absolute' => false,
            'message' => 'Email already verified'
        ]);
        }

        $request->user()->sendEmailVerificationNotification();

        // return back()->with('status', 'verification-link-sent');
        
        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => 'Password confirmed',
        ]);
    }
}
