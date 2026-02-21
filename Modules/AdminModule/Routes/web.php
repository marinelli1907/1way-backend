<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\ActivityLogController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\AiController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\DashboardController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\FirebaseSubscribeController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\PlaceholderController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\ReportController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\SettingController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\SharedController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\FleetMapViewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    Route::controller(FirebaseSubscribeController::class)->group(function () {
        Route::post('/subscribe-topic', 'subscribeToTopic')->name('subscribe-topic');
    });
    Route::controller(FleetMapViewController::class)->group(function(){
        Route::get('fleet-map/{type}', 'fleetMap')->name('fleet-map');
        Route::get('fleet-map-driver-list/{type}', 'fleetMapDriverList')->name('fleet-map-driver-list');
        Route::get('fleet-map-driver-details/{id}', 'fleetMapDriverDetails')->name('fleet-map-driver-details');
        Route::get('fleet-map-view-single-driver/{id}', 'fleetMapViewSingleDriver')->name('fleet-map-view-single-driver');
        Route::get('fleet-map-customer-list/{type}', 'fleetMapCustomerList')->name('fleet-map-customer-list');
        Route::get('fleet-map-customer-details/{id}', 'fleetMapCustomerDetails')->name('fleet-map-customer-details');
        Route::get('fleet-map-view-single-customer/{id}', 'fleetMapViewSingleCustomer')->name('fleet-map-view-single-customer');
        Route::get('fleet-map-view-using-ajax', 'fleetMapViewUsingAjax')->name('fleet-map-view-using-ajax');
        Route::get('fleet-map-safety-alert-icon-in-map', 'fleetMapSafetyAlertIconInMap')->name('fleet-map-safety-alert-icon-in-map');
        Route::get('fleet-map-zone-message', 'fleetMapZoneMessage')->name('fleet-map-zone-message');
    });

    Route::controller(DashboardController::class)->group(function () {
        
Route::get('/', 'index')->name('root');
        Route::get('dashboard', 'index')->name('dashboard');
        
        Route::get('heat-map', 'heatMap')->name('heat-map');
        Route::get('heat-map-overview-data', 'heatMapOverview')->name('heat-map-overview-data');
        Route::get('heat-map-compare', 'heatMapCompare')->name('heat-map-compare');
        Route::get('recent-trip-activity', 'recentTripActivity')->name('recent-trip-activity');
        Route::get('leader-board-driver', 'leaderBoardDriver')->name('leader-board-driver');
        Route::get('leader-board-customer', 'leaderBoardCustomer')->name('leader-board-customer');
        Route::get('earning-statistics', 'adminEarningStatistics')->name('earning-statistics');
        Route::get('zone-wise-statistics', 'zoneWiseStatistics')->name('zone-wise-statistics');
        Route::get('chatting', 'chatting')->name('chatting');
        Route::get('driver-conversation/{channelId}', 'getDriverConversation')->name('driver-conversation');
        Route::post('send-message-to-driver', 'sendMessageToDriver')->name('send-message-to-driver');
        Route::get('search-drivers', 'searchDriversList')->name('search-drivers');
        Route::get('search-saved-topic-answers', 'searchSavedTopicAnswer')->name('search-saved-topic-answers');
        Route::put('create-channel-with-admin', 'createChannelWithAdmin')->name('create-channel-with-admin');
    });
    Route::controller(ActivityLogController::class)->group(function () {
        Route::get('log', 'log')->name('log');
    });
    Route::controller(SettingController::class)->group(function () {
        Route::get('settings', 'index')->name('settings');
        Route::post('update-profile/{id}', 'update')->name('update-profile');
    });
    Route::controller(SharedController::class)->group(function () {
        Route::get('seen-notification', 'seenNotification')->name('seen-notification');
        Route::get('get-notifications', 'getNotifications')->name('get-notifications');
        Route::get('get-safety-alert', 'getSafetyAlert')->name('get-safety-alert');
    });

    // ── Part E: AI Section ───────────────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->controller(AiController::class)->group(function () {
        Route::get('/',               'settings')->name('settings');
        Route::post('/settings',      'saveSettings')->name('settings.save');
        Route::get('/logs',           'logs')->name('logs');
        Route::get('/tools',          'toolsView')->name('tools');
        Route::post('/tools/suggest-zones',   'suggestZones')->name('tools.suggest-zones');
        Route::post('/tools/suggest-pricing', 'suggestPricing')->name('tools.suggest-pricing');
    });
    // ── End AI ───────────────────────────────────────────────────────────────

    // ── Placeholder routes for sidebar items not yet fully implemented ────────
    // Dashboard section
    Route::get('kpis',   [PlaceholderController::class, 'show'])->name('kpis.index');
    Route::get('alerts', [PlaceholderController::class, 'show'])->name('alerts.index');

    // Operations
    Route::get('control-room',   [PlaceholderController::class, 'show'])->name('control-room.index');
    Route::get('cancellations',  [PlaceholderController::class, 'show'])->name('cancellations.index');
    Route::get('support/tickets',[PlaceholderController::class, 'show'])->name('support.tickets.index');

    // Calendar & Events
    Route::get('calendar',            [PlaceholderController::class, 'show'])->name('calendar.index');
    Route::get('events',              [PlaceholderController::class, 'show'])->name('events.index');
    Route::get('events/manage',       [PlaceholderController::class, 'show'])->name('events.manage');
    Route::get('event-ride-planner',  [PlaceholderController::class, 'show'])->name('event-ride-planner.index');
    Route::get('venues',              [PlaceholderController::class, 'show'])->name('venues.index');
    Route::get('event-analytics',     [PlaceholderController::class, 'show'])->name('event-analytics.index');

    // Promotions & Partners
    Route::get('businesses',          [PlaceholderController::class, 'show'])->name('businesses.index');
    Route::get('promoted-listings',   [PlaceholderController::class, 'show'])->name('promoted-listings.index');
    Route::get('ride-incentives',     [PlaceholderController::class, 'show'])->name('ride-incentives.index');
    Route::get('promo-performance',   [PlaceholderController::class, 'show'])->name('promo-performance.index');
    Route::get('payout-rules',        [PlaceholderController::class, 'show'])->name('payout-rules.index');

    // Users
    Route::get('roles',               [PlaceholderController::class, 'show'])->name('roles.index');
    Route::get('reviews',             [PlaceholderController::class, 'show'])->name('reviews.index');

    // Driver Ops
    Route::get('driver-applications', [PlaceholderController::class, 'show'])->name('driver-applications.index');
    Route::get('driver-documents',    [PlaceholderController::class, 'show'])->name('driver-documents.index');
    Route::get('driver-payout-splits',[PlaceholderController::class, 'show'])->name('driver-payout-splits.index');
    Route::get('driver-tiers',        [PlaceholderController::class, 'show'])->name('driver-tiers.index');
    Route::get('driver-availability', [PlaceholderController::class, 'show'])->name('driver-availability.index');
    Route::get('driver-performance',  [PlaceholderController::class, 'show'])->name('driver-performance.index');

    // Payments & Finance
    Route::get('cash-collect',        [PlaceholderController::class, 'show'])->name('cash-collect.index');
    Route::get('refunds',             [PlaceholderController::class, 'show'])->name('refunds.index');
    Route::get('commissions',         [PlaceholderController::class, 'show'])->name('commissions.index');
    Route::get('revenue-reports',     [PlaceholderController::class, 'show'])->name('revenue-reports.index');

    // Business Center
    Route::get('business-settings',   [PlaceholderController::class, 'show'])->name('business-settings.index');
    Route::get('pricing-rules',       [PlaceholderController::class, 'show'])->name('pricing-rules.index');
    Route::get('taxes-fees',          [PlaceholderController::class, 'show'])->name('taxes-fees.index');
    Route::get('invoices',            [PlaceholderController::class, 'show'])->name('invoices.index');
    Route::get('subscriptions',       [PlaceholderController::class, 'show'])->name('subscriptions.index');
    Route::get('audit-logs',          [PlaceholderController::class, 'show'])->name('audit-logs.index');

    // AI Center sidebar items (distinct from the built AI routes above)
    Route::get('ai/assistant',        [PlaceholderController::class, 'show'])->name('ai.assistant.index');
    Route::get('ai/fraud',            [PlaceholderController::class, 'show'])->name('ai.fraud.index');
    Route::get('ai/pricing',          [PlaceholderController::class, 'show'])->name('ai.pricing.index');
    Route::get('ai/supply',           [PlaceholderController::class, 'show'])->name('ai.supply.index');
    Route::get('ai/promo',            [PlaceholderController::class, 'show'])->name('ai.promo.index');
    Route::get('ai/autoreplies',      [PlaceholderController::class, 'show'])->name('ai.autoreplies.index');

    // System
    Route::get('system/config',         [PlaceholderController::class, 'show'])->name('system.config.index');
    Route::get('system/notifications',  [PlaceholderController::class, 'show'])->name('system.notifications.index');
    Route::get('system/integrations',   [PlaceholderController::class, 'show'])->name('system.integrations.index');
    Route::get('system/api-keys',       [PlaceholderController::class, 'show'])->name('system.api-keys.index');
    Route::get('system/backups',        [PlaceholderController::class, 'show'])->name('system.backups.index');
    Route::get('system/maintenance',    [PlaceholderController::class, 'show'])->name('system.maintenance.index');
    // ── End placeholder routes ────────────────────────────────────────────────
});
Route::controller(SharedController::class)->group(function () {
    Route::get('lang/{locale}', 'lang')->name('lang');
});

