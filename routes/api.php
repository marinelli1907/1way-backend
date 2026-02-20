<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\EventController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/ping', function () {
    return response()->json(['message' => '1Way Backend Online']);
});

// TODO: uncomment when JobController is implemented
// Route::prefix('jobs')->group(function () { ... });

Route::prefix('v1')->group(function () {

    // Events (LifeBook)
    Route::get('events', [EventController::class, 'index']);
    Route::post('events', [EventController::class, 'store']);
    Route::get('events/{event}', [EventController::class, 'show']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);

    // TODO: uncomment when RidePaymentController is implemented
    // Route::post('rides/{tripRequest}/hold-payment', [RidePaymentController::class, 'holdPayment']);
    // Route::post('rides/{tripRequest}/capture-payment', [RidePaymentController::class, 'capturePayment']);
    // Route::post('rides/{tripRequest}/cancel-payment', [RidePaymentController::class, 'cancelPayment']);

    // TODO: uncomment when AIController is implemented
    // Route::post('ai/uno', [AIController::class, 'handleUno']);
});