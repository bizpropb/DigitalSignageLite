<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test WebSocket messaging (no CSRF protection on API routes)
Route::post('/test-message', [TestController::class, 'sendTestMessage']);