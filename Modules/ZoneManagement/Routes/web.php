<?php

use Illuminate\Support\Facades\Route;
use Modules\ZoneManagement\Http\Controllers\Web\New\Admin\ZoneController;
use Modules\ZoneManagement\Http\Controllers\Web\New\Admin\ZoneGeoJsonController;

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
});
