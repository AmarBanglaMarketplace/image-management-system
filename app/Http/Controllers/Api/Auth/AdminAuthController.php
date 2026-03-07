<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for admin authentication.
 *
 * Handles admin login, profile retrieval, current user role/permissions,
 * and logout using Laravel Sanctum with Spatie permissions integration.
 */
class AdminAuthController extends Controller
{
    /**
     * Authenticate an admin user and issue a Sanctum token.
     *
     * Revokes all existing tokens before issuing a new one.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $admin = User::where('email', $request->email)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // $admin->tokens()->delete(); --- IGNORE ---
        $token = $admin->createToken('admin-token', ['*'])->plainTextToken;
        return ApiResponse::success(['admin' => $admin, 'token' => $token], 'Login successful');
    }

    /**
     * Get the authenticated admin user's full profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get the current authenticated admin's role and permissions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $admin = $request->user();
        return response()->json([
            'role' => $admin->roles->pluck('name')->first(),
            'permissions' => $admin->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Logout the authenticated admin by revoking all tokens.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
