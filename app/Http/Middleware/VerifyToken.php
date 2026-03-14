<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from Authorization header 
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }
        $role = $request->route()->getPrefix(); // e.g. 'super-admin/media'
        $url = match ($role) {
            'super-admin/media' => env('AUTH_SERVER_URL_SUPER_ADMIN'),
            'shop-admin/media'  => env('AUTH_SERVER_URL_SHOP_ADMIN'),
            'agent/media'       => env('AUTH_SERVER_URL_AGENT'),
            'customer/media'    => env('AUTH_SERVER_URL_CUSTOMER'),
            'delivery/media'    => env('AUTH_SERVER_URL_DELIVERY'),
            default             => env('AUTH_SERVER_URL') . '/api/admin/validate-token',
        };
        // Call the Auth Server to validate the token 
        $response = Http::withToken($token)->post($url);
        if ($response->ok() && $response->json('data.valid')) {
            // Optionally attach user info from Auth Server response 
            $request->attributes->add(['user_id' => $response->json('data.user_id')]);
            return $next($request);
        }
        return response()->json(['error' => 'Invalid token'], 401);
    }
}
