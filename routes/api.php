<?php

use App\Http\Controllers\Api\AiCarImageController;
use App\Http\Controllers\Api\AiPricingController;
use App\Http\Controllers\Api\FlightStatusController;
use App\Http\Controllers\Api\PricingController;
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

// Public pricing quotes (no auth)
Route::post('pricing/quote', [PricingController::class, 'quote']);
Route::post('pricing/ai-quote', [AiPricingController::class, 'quote']);

// Public flight status (rate-limited)
Route::middleware('throttle:30,1')
    ->get('flights/status', [FlightStatusController::class, 'status']);

// AI car image — auth required; queue job and poll status
Route::middleware('auth:api')->group(function () {
    Route::match(['get', 'post'], 'ai/generate-car-image', [AiCarImageController::class, 'generate']);
    Route::get('ai/generate-car-image/status', [AiCarImageController::class, 'status']);
});
// Stripe PaymentIntent for rider app (PaymentSheet) — auth required
Route::middleware('auth:api')->post('payment/stripe/create-intent', [\Modules\Gateways\Http\Controllers\StripePaymentController::class, 'createManualIntent']);
Route::middleware('auth:api')->group(function () {
    Route::match(['get', 'post'], 'v1/ai/generate-car-image', [AiCarImageController::class, 'generate']);
    Route::get('v1/ai/generate-car-image/status', [AiCarImageController::class, 'status']);
});
