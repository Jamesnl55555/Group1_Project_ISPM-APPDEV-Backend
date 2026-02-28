<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use App\Models\User;


class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$record) {
        throw ValidationException::withMessages([
            'code' => ['Invalid code.'],
        ]);
    }

    if (!Hash::check($request->code, $record->token)) {
        throw ValidationException::withMessages([
            'code' => ['Invalid code.'],
        ]);
    }

    if (now()->diffInMinutes($record->created_at) > 15) {
        throw ValidationException::withMessages([
            'code' => ['Code expired.'],
        ]);
    }

    $user = User::where('email', $request->email)->first();

    $user->forceFill([
        'password' => Hash::make($request->password),
        'remember_token' => Str::random(60),
    ])->save();

    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Password successfully reset!',
    ]);
    }
}
