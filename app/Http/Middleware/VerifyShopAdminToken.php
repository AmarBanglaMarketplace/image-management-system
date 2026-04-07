<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopAdminToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = ''): Response
    {
        // Extract token from Authorization header 
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }
        // Call the Auth Server to validate the token 
        $response = Http::withToken($token)->post(env('AUTH_SERVER_URL_SHOP_ADMIN'));
        if ($response->ok() && $response->json('data.valid')) {
            if ($permission) {
                $permissions = collect($response->json('data.permissions'));
                if (!$permissions->contains($permission)) {
                    return response()->json(['error' => 'Forbidden'], 403);
                }
            }
            // Optionally attach user info from Auth Server response 
            $request->attributes->add(['user_id' => $response->json('data.user_id')]);
            return $next($request);
        }
        return response()->json(['error' => 'Invalid token'], 401);
    }
}
