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
class SuperAdminFileController extends Controller
{
    /**
     * Create a new folder inside the `uploads` directory.
     *
     * This endpoint validates the `name` field and ensures the folder
     * is always created under the `uploads` root. If the folder does
     * not already exist, it will be created. If it exists, a message
     * confirming its existence will be returned.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - name: required|string — The name of the folder to create.
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: Status of the operation ("Folder created" or "Folder already exists").
     *   - folder: The relative path of the folder inside `uploads/`.
     */

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        try {
            // Always inside uploads
            $base = 'uploads';

            // Clean folder name (remove leading/trailing slashes)
            $folderName = trim($request->input('name'), '/');

            // Final folder path
            $folder = $base . '/' . $folderName;

            // Check if folder exists
            if (Storage::disk('public')->exists($folder)) {
                return ApiResponse::error('Folder already exists', 400, [
                    'folder' => $folder
                ]);
            }

            // Create folder
            Storage::disk('public')->makeDirectory($folder);

            return ApiResponse::success('Folder created', 201, [
                'folder' => $folder
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::error('Error creating folder', 500, [
                'exception' => $th->getMessage()
            ]);
        }
    }

    /**
     * Rename an existing folder inside the `uploads` directory.
     *
     * This endpoint validates the `old_name` and `new_name` fields, ensuring
     * both are strings. The folder is always renamed within the `uploads` root.
     * If the source folder does not exist, a 404 error is returned. If the
     * target folder already exists, a 400 error is returned. Otherwise, the
     * folder is successfully renamed.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - old_name: required|string — The current folder name.
     *   - new_name: required|string — The new folder name to assign.
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: "Folder renamed" on success.
     *   - old: The original folder path.
     *   - new: The new folder path.
     *   - error: Error message if source not found (404) or target exists (400).
     */
    public function renameFolder(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string',
        ]);

        try {
            // Always inside uploads
            $base = 'uploads';

            // Normalize folder names (remove leading/trailing slashes)
            $oldName = trim($request->input('old_name'), '/');
            $newName = trim($request->input('new_name'), '/');

            $oldFolder = $base . '/' . $oldName;
            $newFolder = $base . '/' . $newName;

            // Check if old folder exists
            if (! Storage::disk('public')->exists($oldFolder)) {
                return ApiResponse::error('Source folder not found', 404, [
                    'old_folder' => $oldFolder
                ]);
            }

            // Check if new folder already exists
            if (Storage::disk('public')->exists($newFolder)) {
                return ApiResponse::error('Target folder already exists', 400, [
                    'new_folder' => $newFolder
                ]);
            }

            // Rename folder
            Storage::disk('public')->move($oldFolder, $newFolder);

            return ApiResponse::success('Folder renamed', 200, [
                'old' => $oldFolder,
                'new' => $newFolder,
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::error('Error renaming folder', 500, [
                'exception' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Delete an existing folder inside the `uploads` directory.
     *
     * This endpoint validates the `name` field and ensures the folder
     * is always targeted within the `uploads` root. If the folder exists,
     * it will be deleted recursively. If the folder does not exist, a 404
     * error response is returned.
     *
     * @group File Management
     * @authenticated
     *
     * @header Authorization Bearer <token>
     *
     * @param \Illuminate\Http\Request $request
     *   - name: required|string — The name of the folder to delete.
     *
     * @return \Illuminate\Http\JsonResponse
     *   - message: "Folder deleted" on success.
     *   - folder: The relative path of the deleted folder.
     *   - error: "Folder not found" if the folder does not exist (404).
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        try {
            // Always inside uploads
            $base = 'uploads';

            // Normalize folder name
            $folderName = trim($request->input('name'), '/');
            
            // Final folder path
            $folder = $base . '/' . $folderName;

            // Check if folder exists
            if (! Storage::disk('public')->exists($folder)) {
                return ApiResponse::error('Folder not found', 404, [
                    'folder' => $folder
                ]);
            }

            // Delete folder
            Storage::disk('public')->deleteDirectory($folder);

            return ApiResponse::success('Folder deleted', 200, [
                'folder' => $folder
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::error('Error deleting folder', 500, [
                'exception' => $th->getMessage()
            ]);
        }
    }
}
