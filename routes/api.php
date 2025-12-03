<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RidePaymentController;
use App\Http\Controllers\AIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file registers all API routes for your application.
| Laravel automatically prefixes these routes with /api.
|
*/

// Default route for authenticated user (optional)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| PUBLIC TEST ROUTE (keep this)
|--------------------------------------------------------------------------
*/
Route::get('/ping', function () {
    return response()->json(['message' => '1Way Backend Online']);
});

/*
|--------------------------------------------------------------------------
| JOB ROUTES (NO AUTH FOR NOW)
|--------------------------------------------------------------------------
|
| These routes are temporarily public so you can test them easily.
| Later we will re-enable Sanctum protection.
|
*/

Route::prefix('jobs')->group(function () {
    Route::get('/available', [JobController::class, 'available']);
    Route::get('/my', [JobController::class, 'myJobs']);
    Route::get('/{job}', [JobController::class, 'show']);

    Route::post('/{job}/accept', [JobController::class, 'accept']);
    Route::post('/{job}/start', [JobController::class, 'start']);
    Route::post('/{job}/pickup', [JobController::class, 'pickup']);
    Route::post('/{job}/complete', [JobController::class, 'complete']);
    Route::post('/{job}/cancel', [JobController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| EXISTING PROJECT ROUTES (FROM YOUR BACKEND)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Events (LifeBook)
    Route::get('events', [EventController::class, 'index']);
    Route::post('events', [EventController::class, 'store']);
    Route::get('events/{event}', [EventController::class, 'show']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);

    // Payment Routes
    Route::post('rides/{tripRequest}/hold-payment', [RidePaymentController::class, 'holdPayment']);
    Route::post('rides/{tripRequest}/capture-payment', [RidePaymentController::class, 'capturePayment']);
    Route::post('rides/{tripRequest}/cancel-payment', [RidePaymentController::class, 'cancelPayment']);

    // AI / Uno
    Route::post('ai/uno', [AIController::class, 'handleUno']);
});

