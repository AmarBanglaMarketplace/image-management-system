<?php

namespace App\Http\Controllers\Api;

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
class ShopAdminMediaController extends Controller
{
    /**
     * List all files inside a specified folder.
     *
     * This endpoint retrieves the list of files stored in the given folder
     * on the `public` disk. If no folder is provided, it defaults to the
     * `uploads` directory. The response contains an array of file paths.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - folder: optional|string — The folder path to list files from (defaults to `uploads`).
     *
     * @return \Illuminate\Http\JsonResponse
     *   - files: An array of file paths inside the specified folder.
     */
    public function index(Request $request)
    {
        $folder = $request->input('folder', 'uploads');

        return response()->json([
            'files'   => Storage::disk('public')->files($folder),
        ]);
    }

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
            'file'   => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,pdf|max:204800',
            'folder' => 'nullable|string'
        ]);

        // Always inside uploads 
        $baseFolder = 'uploads/';
        // If folder name provided, append it as subfolder 
        $subFolder = trim($request->input('folder', ''));
        $folder = $baseFolder . $subFolder;

        $originalName = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $request->file('file')->getClientOriginalExtension();

        $safeName = preg_replace('/\s+/', '-', $originalName);
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '', $safeName);

        $uniqueSuffix = time();
        $newFileName  = $safeName . '_' . $uniqueSuffix . '.' . $extension;

        $path = $request->file('file')->storeAs($folder, $newFileName, 'public');

        return response()->json([
            'message'       => 'File uploaded successfully',
            'path'          => $path,
            'url'           => Storage::url($path),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'stored_name'   => $newFileName,
            'type'          => $extension
        ]);
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
        Storage::disk('public')->delete($request->path);

        return response()->json(['message' => 'File deleted']);
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
        $oldPath  = $folder . '/' . trim($request->old_name);
        $newPath  = $folder . '/' . trim($request->new_name);

        if (! Storage::disk('public')->exists($oldPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if (Storage::disk('public')->exists($newPath)) {
            return response()->json(['error' => 'A file with the new name already exists'], 400);
        }

        Storage::disk('public')->move($oldPath, $newPath);

        return response()->json([
            'message'   => 'File renamed',
            'old_path'  => $oldPath,
            'new_path'  => $newPath,
            'url'       => Storage::url($newPath)
        ]);
    }
}
