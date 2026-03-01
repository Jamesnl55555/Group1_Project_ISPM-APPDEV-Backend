<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RefreshTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $absoluteDays = $token->remember ? 30 : 7;
            $absoluteExpiry = $token->created_at->addDays($absoluteDays);

            if (now()->greaterThan($absoluteExpiry)) {
                $token->delete();
                return response()->json(['message' => 'Session expired.'], 401);
            }

            if ($token->expires_at && $token->expires_at->lt(now()->addMinutes(10))) {
                $token->forceFill([
                    'expires_at' => now()->addHour(),
                ])->save();
            }
        }

        return $response;
    }
}