<?php

use App\Http\Controllers\Api\AgentMediaController;
use App\Http\Controllers\Api\CustomerMediaController;
use App\Http\Controllers\Api\DeliveryBoyMediaController;
use App\Http\Controllers\Api\ShopAdminMediaController;
use App\Http\Controllers\Api\SuperAdminFileController;
use App\Http\Controllers\Api\SuperAdminMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin/media')->controller(SuperAdminMediaController::class)->group(function () {
    // Route::get('/', 'index');
    Route::post('/upload', 'upload')->middleware(['verify.superadmin.token:upload-file']);
    Route::post('/file', 'renameFile')->middleware(['verify.superadmin.token:rename-file']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.superadmin.token:delete-file']);
});
Route::prefix('super-admin/folders')->controller(SuperAdminFileController::class)->group(function () {
    Route::post('/', 'createFolder')->middleware(['verify.superadmin.token']);
    Route::put('/rename', 'renameFolder')->middleware(['verify.superadmin.token']);
    Route::delete('/delete', 'deleteFolder')->middleware(['verify.superadmin.token']);
});
Route::prefix('shop-admin/media')->controller(ShopAdminMediaController::class)->group(function () {
    Route::post('/upload', 'upload')->middleware(['verify.shopadmin.token']);
    Route::post('/file', 'renameFile')->middleware(['verify.shopadmin.token']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.shopadmin.token']);
});
Route::prefix('agent/media')->controller(AgentMediaController::class)->group(function () {
    Route::post('/upload', 'upload')->middleware(['verify.agent.token']);
    Route::post('/file', 'renameFile')->middleware(['verify.agent.token:rename-file']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.agent.token:delete-file']);
});
Route::prefix('delivery-boy/media')->controller(DeliveryBoyMediaController::class)->group(function () {
    Route::post('/upload', 'upload')->middleware(['verify.deliveryboy.token']);
    Route::post('/file', 'renameFile')->middleware(['verify.deliveryboy.token']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.deliveryboy.token']);
});
Route::prefix('customer/media')->controller(CustomerMediaController::class)->group(function () {
    Route::post('/upload', 'upload')->middleware(['verify.customer.token']);
    Route::post('/file', 'renameFile')->middleware(['verify.customer.token']);
    Route::delete('/file', 'deleteFile')->middleware(['verify.customer.token']);
});
