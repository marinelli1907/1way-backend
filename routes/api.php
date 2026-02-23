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

// AI car image (driver app) — stub; supports GET and POST
Route::match(['get', 'post'], 'ai/generate-car-image', AiCarImageController::class);
Route::match(['get', 'post'], 'v1/ai/generate-car-image', AiCarImageController::class);
