<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('getImageUrl')) {
    /**
     * Generate a secure and reliable URL for an image stored in public storage.
     *
     * - Returns a fallback default image if path is empty or invalid.
     * - Returns the original URL if it's already absolute (http/https).
     * - Checks file existence in public disk and returns correct storage link.
     *
     * @param string|null $path The relative path from storage or absolute URL
     * @return string The full accessible image URL
     */
    function getImageUrl(?string $path): string
    {
        // Fallback if no path provided
        if (empty($path)) {
            return asset('assets/images/default.svg');
        }

        // If already an absolute URL
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        // Normalize path
        $normalized = ltrim($path, '/');

        // Check if file exists in public storage
        if (Storage::disk('public')->exists($normalized)) {
            return asset("storage/{$normalized}");
        }

        // Fallback if file not found
        return asset('assets/images/default.svg');
    }
}

if (! function_exists('truncateText')) {
    /**
     * Truncate text to a given length with optional ellipsis.
     *
     * Strips HTML tags and truncates at the last space to avoid cutting words.
     *
     * @param string $text   The text to truncate
     * @param int    $length Maximum length before truncation (default: 100)
     * @param string $suffix Suffix to append when truncated (default: "...")
     * @return string Truncated text
     */
    function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        $text = strip_tags($text);

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $length);

        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated . $suffix;
    }
}

if (! function_exists('formatDate')) {
    /**
     * Format a given date/time into a human-readable string using Carbon.
     *
     * @param  string|\DateTimeInterface|null  $date   The date to format
     * @param  string                          $format Desired format (default: 'd M Y')
     * @return string Formatted date or empty string if invalid
     */
    function formatDate($date, string $format = 'd M Y'): string
    {
        if (empty($date)) {
            return '';
        }

        return Carbon::parse($date)->format($format);
    }
}

if (! function_exists('getImagePath')) {
    /**
     * Get the relative or absolute path for an image.
     *
     * Used primarily for generating cache/resized image URLs.
     *
     * @param string|null $path The stored path or external URL
     * @return string Relative path prefixed with /storage or full URL
     */
    function getImagePath(?string $path): string
    {
        if (empty($path)) {
            return '/assets/images/default.svg'; // fallback image
        }

        return (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : '/storage/' . ltrim($path, '/');
    }
}

if (! function_exists('getImageCacheUrl')) {
    /**
     * Generate a dynamic image resize/cache URL.
     *
     * Uses a custom route (e.g., /image/{width}/{height}/{format}/{path})
     * to serve resized/cached versions of images.
     *
     * @param string|null $filePath Original image path from database
     * @param int         $width    Desired width in pixels (default: 200)
     * @param int         $height   Desired height in pixels (default: 200)
     * @param string      $format   Output format (default: 'webp')
     * @return string Full URL to the resized image
     */
    function getImageCacheUrl(?string $filePath, int $width = 200, int $height = 200, string $format = 'webp'): string
    {

        $baseUrl      = env('APP_URL'); // comes from APP_URL in .env
        $relativePath = getImagePath($filePath);
        // If already absolute URL, return as-is
        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return $relativePath;
        }

        // Otherwise build dynamic resize route
        return rtrim($baseUrl, '/') . "/image/{$width}/{$height}/{$format}/" . ltrim($relativePath, '/');
    }
}
