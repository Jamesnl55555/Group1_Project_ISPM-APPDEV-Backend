<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\BrevoMailService;

class SendCodeController extends Controller
{
    protected BrevoMailService $mailer;

    public function __construct(BrevoMailService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => true]);
        }

        $code = random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => bcrypt($code),
                'created_at' => now()
            ]
        );

        $this->mailer->sendEmail(
            $user->email,
            $user->name,
            'Your Reset Code',
            "<p>Your password reset code is:</p><h2>{$code}</h2>"
        );

        return response()->json(['success' => true]);
    }
}