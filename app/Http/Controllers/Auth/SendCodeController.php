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
        $request->validate(['email' => 'required|email|string']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email is not registered.'], 422);
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
            "
            <p><b>Hello!</b></p>
            <p>You are receiving this email because we received a request</p>
            <p>to verify your account.</p>
            <br/>
            <br/>
            <p>your verification code is:</p>
            <h2><b>Code: {$code}</b></h2>
            <br/>
            <br/>

            <p>This code will expire in 15 minutes.</p>
            <p>If you did not request this, no further action is required.</p>
            <br/>
            <br/>
            <p>Regards,</p>
            <p>88 Chocolates</p>
            <br/>
            <br/>
            <p>If you ae having trouble, you can ignore this email or conact support.</p>
            "
        );

        return response()->json(['success' => true]);
    }
}