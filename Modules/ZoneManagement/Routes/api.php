<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ZoneManagement\Http\Controllers\Api\New\Driver\ZoneController;
use Modules\ZoneManagement\Http\Controllers\Api\ZonePointController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'driver'], function () {
    Route::group(['prefix' => 'zone', 'middleware' => ['auth:api', 'maintenance_mode']], function () {

        Route::controller(ZoneController::class)->group(function () {
            Route::get('/list', 'list');
        });
    });
});

// ── Part B: Point-in-Polygon API (auth:api required) ─────────────────────────
Route::group(['prefix' => 'zone', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
    Route::controller(ZonePointController::class)->group(function () {
        Route::post('point-in-zone',  'pointInZone');   // POST /api/zone/point-in-zone
        Route::post('trip-zones',     'tripZones');     // POST /api/zone/trip-zones
    });
});
