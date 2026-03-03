<?php

use Illuminate\Support\Facades\Route;
use Modules\ZoneManagement\Http\Controllers\Web\New\Admin\ZoneController;
use Modules\ZoneManagement\Http\Controllers\Web\New\Admin\ZoneGeoJsonController;
use Modules\ZoneManagement\Http\Controllers\Web\New\Admin\ServiceZoneController;

//New Route Mamun
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    Route::group(['prefix' => 'zone', 'as' => 'zone.'], function () {
        Route::controller(ZoneController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('store', 'store')->name('store');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::put('update/{id}', 'update')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('delete');
            Route::get('status', 'status')->name('status');
            Route::get('trashed', 'trashed')->name('trashed');
            Route::get('restore/{id}', 'restore')->name('restore');
            Route::delete('permanent-delete/{id}', 'permanentDelete')->name('permanent-delete');
            Route::get('boundary-search', 'boundarySearch')->name('boundary-search');
            Route::get('get-zones', 'getZones')->name('get-zones');
            Route::get('get-coordinates/{id}', 'getCoordinates')->name('getCoordinates');
            Route::get('export', 'export')->name('export');
            Route::get('log', 'log')->name('log');
            Route::group(['prefix' => 'extra-fare', 'as' => 'extra-fare.'], function () {
                Route::post('store-all-zone', 'storeAllZoneExtraFare')->name('store-all-zone');
                Route::post('store', 'storeExtraFare')->name('store');
                Route::get('edit/{id}', 'editExtraFare')->name('edit');
                Route::get('status', 'statusExtraFare')->name('status');
            });
        });

        // ── Part B: GeoJSON import/export ─────────────────────────────────
        Route::controller(ZoneGeoJsonController::class)->group(function () {
            Route::get('geojson-import',        'importForm')->name('geojson-import');
            Route::post('geojson-import',        'import')->name('geojson-import.store');
            Route::get('{id}/export-geojson',    'export')->name('export-geojson');
            Route::get('export-all-geojson',     'exportAll')->name('geojson-export-all');
        });
        // ── End GeoJSON ──────────────────────────────────────────────────

    });

    // ── Service Zones (Map Builder) ─────────────────────────────────────
    Route::group(['prefix' => 'service-zone', 'as' => 'service-zone.'], function () {
        Route::controller(ServiceZoneController::class)->group(function () {
            Route::get('/',             'index')->name('index');
            Route::get('create',       'create')->name('create');
            Route::post('store',       'store')->name('store');
            Route::get('edit/{id}',    'edit')->name('edit');
            Route::put('update/{id}',  'update')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('delete');
            Route::get('status',       'toggleStatus')->name('status');
            Route::get('lookup-boundary', 'lookupBoundary')->name('lookup-boundary');
            Route::get('test-contains',   'testContains')->name('test-contains');
            Route::post('import-boundary', 'importBoundary')->name('import-boundary');
            Route::get('{id}/pricing',    'pricingEdit')->name('pricing');
            Route::put('{id}/pricing',    'pricingUpdate')->name('pricing.update');
            Route::get('{id}/drivers/search', 'searchDrivers')->name('drivers.search');
            Route::put('{id}/drivers',        'syncDrivers')->name('drivers.sync');
        });
    });
});
