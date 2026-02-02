<?php

use Illuminate\Support\Facades\Route;
use Modules\AuthManagement\Http\Controllers\Web\New\Admin\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {

        // Admin auth routes
        Route::controller(LoginController::class)->group(function () {
            Route::get('login', 'loginView')->name('login');
            Route::post('login', 'login');
            Route::post('external-login-from-mart', 'externalLoginFromMart');
            Route::get('logout', 'logout')->name('logout');

            // Captcha route kept for backward compatibility (admin login no longer depends on it)
            Route::get('/code/captcha/{tmp}', 'captcha')->name('default-captcha');
        });

    });
});
