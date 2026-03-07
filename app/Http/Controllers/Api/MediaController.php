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
class MediaController extends Controller
{
    /**
     * List folders and files in a specified directory.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $folder = $request->input('folder', 'uploads');

        return response()->json([
            'files'   => Storage::disk('public')->files($folder),
        ]);
    }

    /**
     * Upload a file to the specified folder.
     *
     * Generates a safe, unique filename to prevent conflicts.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * Delete a file from public storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        Storage::disk('public')->delete($request->path);

        return response()->json(['message' => 'File deleted']);
    }

    /**
     * Rename an existing file.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
