<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use Modules\TripManagement\Http\Controllers\Api\Driver\TripRequestController as DriverTripController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'message' => '1Way Backend Online']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes (Passport Bearer token)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'maintenance_mode'])->group(function () {

    /*
     * GET /api/user/profile
     * Universal profile endpoint — returns the authenticated user's data.
     * Driver app calls this on launch to restore session.
     */
    Route::get('/user/profile', function () {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'data' => $user->load([
                'driverDetail',
                'userAccount',
                'lastLocations',
            ])->toArray(),
        ]);
    });

    /*
     * Jobs (driver ride management)
     * Maps to the existing TripRequest module endpoints.
     *
     * GET  /api/jobs/available      — pending rides available for driver to accept
     * GET  /api/jobs/my             — driver's own active/history trips
     * GET  /api/jobs/{id}           — single trip detail
     * POST /api/jobs/{id}/accept    — driver accepts ride
     * POST /api/jobs/{id}/start     — driver starts trip (en-route)
     * POST /api/jobs/{id}/pickup    — driver picked up passenger
     * POST /api/jobs/{id}/complete  — driver completes trip
     * POST /api/jobs/{id}/cancel    — driver cancels trip
     */
    Route::prefix('jobs')->controller(DriverTripController::class)->group(function () {
        Route::get('/available', 'pendingRideList');
        Route::get('/my', 'rideList');
        Route::get('/{trip}', 'rideDetails');
        Route::post('/{trip}/accept', 'requestAction');
        Route::post('/{trip}/start', 'rideStatusUpdate');
        Route::post('/{trip}/pickup', 'rideStatusUpdate');
        Route::post('/{trip}/complete', 'rideStatusUpdate');
        Route::post('/{trip}/cancel', 'rideStatusUpdate');
    });

});

/*
|--------------------------------------------------------------------------
| Events (LifeBook integration)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::apiResource('events', EventController::class);

    // TODO: uncomment when RidePaymentController is implemented
    // Route::post('rides/{tripRequest}/hold-payment', ...);

    // TODO: uncomment when AIController is implemented
    // Route::post('ai/uno', ...);
});
