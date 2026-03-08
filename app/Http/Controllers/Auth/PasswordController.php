<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function confirmPass(Request $request){
        $validated = $request->validate([
            'password' => ['required']
        ]);
        if(!Hash::check($validated['password'], $request->user()->password)){
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Password confirmed successfully',
        ]); 
    }

    public function changePass(Request $request){
        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }
}
