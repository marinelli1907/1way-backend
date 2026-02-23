<?php

use App\Http\Controllers\Api\AiCarImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// AI car image — auth required; queue job and poll status
Route::middleware('auth:api')->group(function () {
    Route::match(['get', 'post'], 'ai/generate-car-image', [AiCarImageController::class, 'generate']);
    Route::get('ai/generate-car-image/status', [AiCarImageController::class, 'status']);
});
Route::middleware('auth:api')->group(function () {
    Route::match(['get', 'post'], 'v1/ai/generate-car-image', [AiCarImageController::class, 'generate']);
    Route::get('v1/ai/generate-car-image/status', [AiCarImageController::class, 'status']);
});
