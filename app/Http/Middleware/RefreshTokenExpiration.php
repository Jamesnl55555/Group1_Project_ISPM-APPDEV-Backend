<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RefreshTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {

            $lastUsed = $token->last_used_at ?? $token->created_at;

            $expiry = $lastUsed->copy()->addDays(30);

            if (now()->greaterThan($expiry)) {
                $token->delete();

                return response()->json([
                    'message' => 'Session expired due to inactivity.'
                ], 401);
            }

            $token->forceFill([
                'last_used_at' => now(),
            ])->save();
        }

        return $next($request);
    }
}