<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API Controller for Media Management
 *
 * Provides endpoints for browsing, uploading, renaming, and deleting
 * files and folders in the public storage disk. Designed for integration
 * with admin file managers or rich text editors.
 */
class CustomerMediaController extends Controller
{

    /**
     * Upload a file to the `uploads` directory.
     *
     * This endpoint accepts a file and optionally a subfolder name. The file is
     * always stored under the `uploads/` root. The original filename is sanitized
     * and a unique suffix is appended to avoid collisions. Supported file types
     * include images (jpg, jpeg, png, gif, webp), videos (mp4, mov, avi, mkv),
     * and documents (pdf). The maximum file size allowed is 200 MB.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - file: required|file — The file to upload (jpg, jpeg, png, gif, webp, mp4, mov, avi, mkv, pdf).
     *   - folder: optional|string — Subfolder name inside `uploads/` where the file will be stored.
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: "File uploaded successfully" on success.
     *   - path: Relative storage path of the uploaded file.
     *   - url: Public URL to access the file.
     *   - original_name: Original filename provided by the client.
     *   - stored_name: Sanitized and unique filename stored on disk.
     *   - type: File extension/type of the uploaded file.
     */

    public function upload(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10000',
            'folder' => 'nullable|string'
        ]);

        try {
            // Always inside uploads
            $baseFolder = 'uploads';

            // Clean up folder input: remove leading/trailing slashes
            $subFolder = trim($request->input('folder', ''), '/');

            // Build final folder path
            $folder = $subFolder ? $baseFolder . '/' . $subFolder : $baseFolder;

            $originalName = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = $request->file('file')->getClientOriginalExtension();

            // Sanitize filename
            $safeName = preg_replace('/\s+/', '-', $originalName);
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '', $safeName);

            $uniqueSuffix = time();
            $newFileName  = $safeName . '_' . $uniqueSuffix . '.' . $extension;

            // Store file
            $path = $request->file('file')->storeAs($folder, $newFileName, 'public');

            return ApiResponse::success('File uploaded successfully', 200, [
                'path'          => $path,
                'url'           => Storage::url($path),
                'original_name' => $request->file('file')->getClientOriginalName(),
                'stored_name'   => $newFileName,
                'type'          => $extension,
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::error('File upload failed', 500, [
                'exception' => $th->getMessage(),
            ]);
        }
    }
    /**
     * Delete a file from the `public` disk.
     *
     * This endpoint validates the `path` field and deletes the specified file
     * from the storage. The path must be relative to the `public` disk root.
     * A success message is returned once the file is deleted.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - path: required|string — The relative path of the file to delete.
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: "File deleted" on success.
     */

    public function deleteFile(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        $path = $request->input('path');
        if (!str_contains($path, '/')) {
            $path = 'uploads/' . $path;
        }
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return ApiResponse::success('File deleted', 200);
            }

            return ApiResponse::error('File not found', 404);
        } catch (\Throwable $th) {
            // Catch unexpected errors (permissions, misconfigured disk, etc.)
            return ApiResponse::error('Error deleting file', 500, [
                'exception' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Rename an existing file inside the `uploads` directory.
     *
     * This endpoint validates the `old_name` and `new_name` fields, ensuring
     * both are strings. The file is always renamed within the `uploads` root.
     * If the source file does not exist, a 404 error is returned. If a file
     * with the new name already exists, a 400 error is returned. Otherwise,
     * the file is successfully renamed and its new public URL is returned.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - old_name: required|string — The current filename (relative to `uploads/`).
     *   - new_name: required|string — The new filename to assign (relative to `uploads/`).
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: "File renamed" on success.
     *   - old_path: The original file path.
     *   - new_path: The new file path.
     *   - url: Public URL to access the renamed file.
     *   - error: Error message if source file not found (404) or target already exists (400).
     */

      public function renameFile(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string'
        ]);

        // Always inside uploads
        $folder   = 'uploads';
        $oldPath  = $folder . '/' . trim($request->old_name, '/');
        $newPath  = $folder . '/' . trim($request->new_name, '/');

        try {
            if (! Storage::disk('public')->exists($oldPath)) {
                return ApiResponse::error('File not found', 404);
            }

            if (Storage::disk('public')->exists($newPath)) {
                return ApiResponse::error('A file with the new name already exists', 400);
            }

            Storage::disk('public')->move($oldPath, $newPath);

            return ApiResponse::success('File renamed', 200, [
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'url'      => Storage::url($newPath),
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::error('Error renaming file', 500, [
                'exception' => $th->getMessage(),
            ]);
        }
    }
}
