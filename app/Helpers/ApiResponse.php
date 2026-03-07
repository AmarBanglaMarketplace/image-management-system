<?php

namespace App\Helpers;

/**
 * Helper class for standardized JSON API responses.
 *
 * Provides consistent success and error response formats across the application.
 */
class ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param mixed $data    Response data (array, object, collection, etc.)
     * @param string $message Success message
     * @param int $code      HTTP status code (default: 200)
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message Error message
     * @param int $code       HTTP status code (default: 400)
     * @param mixed $data     Additional error data (e.g., validation errors)
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'Error', $code = 400, $data = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
