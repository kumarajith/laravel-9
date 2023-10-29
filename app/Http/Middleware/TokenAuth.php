<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiToken = $request->input('api_token');
        if (!$apiToken) {
            return response()->json([
                'message' => 'Token not provided'
            ]);
        }

        $personalAccessToken = PersonalAccessToken::findToken($apiToken);
        if(!$personalAccessToken) {
            return response()->json([
                'message' => 'Token expired'
            ]);
        }
        Auth::login($personalAccessToken->tokenable);

        return $next($request);
    }
}
