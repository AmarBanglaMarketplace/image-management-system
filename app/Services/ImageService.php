<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Resize and cache an image.
     *
     * @param string $path   Original image path (relative to storage/app/public or full path)
     * @param int    $width  Desired width
     * @param int    $height Desired height
     * @param string $format Output format (jpg|png|webp)
     * @return string        URL to cached image
     */
    public static function resizeAndCache(string $path, int $width, int $height, string $format = 'webp'): string
    {
        // Unique hash for this image + size + format
        $hash           = md5($path . "_{$width}x{$height}_{$format}");
        $cacheKey       = "image_cache_" . $hash;
        $cachedFileName = "{$hash}.{$format}";
        $cachedDir      = public_path('cache');
        $cachedPath     = $cachedDir . DIRECTORY_SEPARATOR . $cachedFileName;

        // If cache key exists, return URL
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // If file already exists on disk, re-register cache
        if (file_exists($cachedPath)) {
            $url = asset('cache/' . $cachedFileName);
            Cache::put($cacheKey, $url, now()->addDays(7));
            return $url;
        }

        // Ensure directory exists
        if (! File::exists($cachedDir)) {
            File::makeDirectory($cachedDir, 0755, true);
        }

        // Use ImageManager directly (no facade warnings)
        $manager = new ImageManager(['driver' => 'gd']);
        $image   = $manager->make(public_path($path))
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

        // Save resized image
        $image->save($cachedPath, 100, $format);

        // Generate URL and cache it
        $url = asset('cache/' . $cachedFileName);
        Cache::put($cacheKey, $url, now()->addDays(7));

        return $url;
    }
    public static function cropAndCache(string $path, int $width, int $height, string $format = 'webp'): string
    {
        // Unique hash for this image + size + format
        $hash           = md5($path . "_crop_{$width}x{$height}_{$format}");
        $cacheKey       = "image_cache_" . $hash;
        $cachedFileName = "{$hash}.{$format}";
        $cachedDir      = public_path('cache');
        $cachedPath     = $cachedDir . DIRECTORY_SEPARATOR . $cachedFileName;

        // If cache key exists, return URL
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // If file already exists on disk, re-register cache
        if (file_exists($cachedPath)) {
            $url = asset('cache/' . $cachedFileName);
            Cache::put($cacheKey, $url, now()->addDays(7));
            return $url;
        }

        // Ensure directory exists
        if (! File::exists($cachedDir)) {
            File::makeDirectory($cachedDir, 0755, true);
        }

        // Use ImageManager directly
        $manager = new ImageManager(['driver' => 'gd']);
        $image   = $manager->make(public_path($path))
            ->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });

        // Save cropped image
        $image->save($cachedPath, 100, $format);

        // Generate URL and cache it
        $url = asset('cache/' . $cachedFileName);
        Cache::put($cacheKey, $url, now()->addDays(7));

        return $url;
    }
}
