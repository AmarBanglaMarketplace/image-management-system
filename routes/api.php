<?php

use App\Http\Controllers\Api\MediaController;
use Illuminate\Support\Facades\Route;

/*Media Management*/

Route::prefix('media')->controller(MediaController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/upload', 'upload')->middleware(['verify.token']);
    Route::post('/file', 'renameFile')->middleware(['verify.token']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.token']);

    Route::post('/create-folder', 'createFolder')->middleware(['verify.token']);
    Route::post('/rename-folder', 'renameFolder')->middleware(['verify.token']);
    Route::delete('/delete-folder', 'deleteFolder')->middleware(['verify.token']);
});
