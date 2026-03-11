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

        // Always inside uploads
        $folder = 'uploads/' . trim($request->input('name'));

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
            return response()->json(['message' => 'Folder created', 'folder' => $folder]);
        }

        return response()->json(['message' => 'Folder already exists', 'folder' => $folder]);
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

        $oldFolder = 'uploads/' . trim($request->input('old_name'));
        $newFolder = 'uploads/' . trim($request->input('new_name'));

        if (! Storage::disk('public')->exists($oldFolder)) {
            return response()->json(['error' => 'Source folder not found'], 404);
        }

        if (Storage::disk('public')->exists($newFolder)) {
            return response()->json(['error' => 'Target folder already exists'], 400);
        }

        Storage::disk('public')->move($oldFolder, $newFolder);

        return response()->json([
            'message' => 'Folder renamed',
            'old' => $oldFolder,
            'new' => $newFolder,
        ]);
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

        // Always inside uploads
        $folder = 'uploads/' . trim($request->input('name'));

        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
            return response()->json(['message' => 'Folder deleted', 'folder' => $folder]);
        }

        return response()->json(['error' => 'Folder not found', 'folder' => $folder], 404);
    }
}
