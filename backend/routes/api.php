<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\Admin\DisplayTestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Secure admin-only test messaging
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/displays/{display}/test', [DisplayTestController::class, 'sendTestMessage'])
        ->name('api.admin.displays.test');
});

// Secured admin-only test endpoint  
Route::post('/test-message', [TestController::class, 'sendTestMessage'])
    ->middleware('auth:web');

// Display registration and management
Route::post('/displays/register', [DisplayController::class, 'register']);
Route::get('/displays/check/{device_id}', [DisplayController::class, 'checkDisplay']);
Route::post('/displays/heartbeat', [DisplayController::class, 'heartbeat']);
Route::post('/displays/find-by-token', [DisplayController::class, 'findByAccessToken']);
Route::post('/displays/find-by-auth', [DisplayController::class, 'findByAuthToken']);