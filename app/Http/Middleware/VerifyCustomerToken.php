<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyCustomerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from Authorization header 
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }
        // Call the Auth Server to validate the token 
        $response = Http::withToken($token)->post(env('AUTH_SERVER_URL_CUSTOMER'));
        if ($response->ok() && $response->json('data.valid')) {
            // Optionally attach user info from Auth Server response 
            $request->attributes->add(['user_id' => $response->json('data.user_id')]);
            return $next($request);
        }
        return response()->json(['error' => 'Invalid token'], 401);
    }
}
