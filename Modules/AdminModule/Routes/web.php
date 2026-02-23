<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\ActivityLogController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\AiController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\DashboardController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\FirebaseSubscribeController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\PlaceholderController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\PromotionsController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\StubOpsController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\ReportController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\SettingController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\SharedController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\FleetMapViewController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\OpsController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\CalendarEventsController;

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

    // ── Dashboard & Operations ────────────────────────────────────────────────
    Route::controller(OpsController::class)->group(function () {
        Route::get('kpis', 'kpis')->name('kpis.index');
        Route::get('alerts', 'alerts')->name('alerts.index');
        Route::get('control-room', 'controlRoom')->name('control-room.index');
        Route::get('cancellations', 'cancellations')->name('cancellations.index');
        Route::get('support/tickets', 'supportTickets')->name('support.tickets.index');
    });

    // ── Calendar & Events ──────────────────────────────────────────────────────
    Route::controller(CalendarEventsController::class)->group(function () {
        Route::get('calendar', 'calendar')->name('calendar.index');
        Route::get('events', 'eventsList')->name('events.index');
        Route::get('events/manage', 'manageEvents')->name('events.manage');
        Route::get('event-ride-planner', 'eventRidePlanner')->name('event-ride-planner.index');
        Route::get('venues', 'venues')->name('venues.index');
        Route::get('event-analytics', 'eventAnalytics')->name('event-analytics.index');
    });

    // Promotions & Partners — real ops pages
    Route::get('businesses',          [PromotionsController::class, 'businesses'])->name('businesses.index');
    Route::get('promoted-listings',   [PromotionsController::class, 'promotedListings'])->name('promoted-listings.index');
    Route::get('ride-incentives',     [PromotionsController::class, 'rideIncentives'])->name('ride-incentives.index');
    Route::get('promo-performance',   [PromotionsController::class, 'promoPerformance'])->name('promo-performance.index');
    Route::get('payout-rules',        [PromotionsController::class, 'payoutRules'])->name('payout-rules.index');

    // Users
    Route::get('roles',               [StubOpsController::class, 'roles'])->name('roles.index');
    Route::get('reviews',             [StubOpsController::class, 'reviews'])->name('reviews.index');

    // Driver Ops
    Route::get('driver-applications', [StubOpsController::class, 'driverApplications'])->name('driver-applications.index');
    Route::get('driver-documents',    [StubOpsController::class, 'driverDocuments'])->name('driver-documents.index');
    Route::get('driver-payout-splits',[StubOpsController::class, 'driverPayoutSplits'])->name('driver-payout-splits.index');
    Route::get('driver-tiers',        [StubOpsController::class, 'driverTiers'])->name('driver-tiers.index');
    Route::get('driver-availability', [StubOpsController::class, 'driverAvailability'])->name('driver-availability.index');
    Route::get('driver-performance',  [StubOpsController::class, 'driverPerformance'])->name('driver-performance.index');

    // Payments & Finance (incl. withdraw + coupon so sidebar route names exist)
    Route::get('withdraw',            [StubOpsController::class, 'withdraw'])->name('withdraw.index');
    Route::get('cash-collect',        [StubOpsController::class, 'cashCollect'])->name('cash-collect.index');
    Route::get('refunds',             [StubOpsController::class, 'refunds'])->name('refunds.index');
    Route::get('commissions',         [StubOpsController::class, 'commissions'])->name('commissions.index');
    Route::get('coupon',              [StubOpsController::class, 'coupon'])->name('coupon.index');
    Route::get('revenue-reports',     [StubOpsController::class, 'revenueReports'])->name('revenue-reports.index');

    // Business Center
    Route::get('business-settings',   [StubOpsController::class, 'businessSettings'])->name('business-settings.index');
    Route::get('pricing-rules',       [StubOpsController::class, 'pricingRules'])->name('pricing-rules.index');
    Route::get('taxes-fees',          [StubOpsController::class, 'taxesFees'])->name('taxes-fees.index');
    Route::get('invoices',            [StubOpsController::class, 'invoices'])->name('invoices.index');
    Route::get('subscriptions',       [StubOpsController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('audit-logs',          [StubOpsController::class, 'auditLogs'])->name('audit-logs.index');

    // AI Center sidebar items — real ops pages
    Route::get('ai/assistant',        [StubOpsController::class, 'aiAssistant'])->name('ai.assistant.index');
    Route::get('ai/fraud',            [StubOpsController::class, 'aiFraud'])->name('ai.fraud.index');
    Route::get('ai/pricing',          [StubOpsController::class, 'aiPricing'])->name('ai.pricing.index');
    Route::get('ai/supply',           [StubOpsController::class, 'aiSupply'])->name('ai.supply.index');
    Route::get('ai/promo',            [StubOpsController::class, 'aiPromo'])->name('ai.promo.index');
    Route::get('ai/autoreplies',      [StubOpsController::class, 'aiAutoreplies'])->name('ai.autoreplies.index');

    // System
    Route::get('system/config',         [StubOpsController::class, 'systemConfig'])->name('system.config.index');
    Route::get('system/notifications',  [StubOpsController::class, 'systemNotifications'])->name('system.notifications.index');
    Route::get('system/integrations',   [StubOpsController::class, 'systemIntegrations'])->name('system.integrations.index');
    Route::get('system/api-keys',       [StubOpsController::class, 'systemApiKeys'])->name('system.api-keys.index');
    Route::get('system/backups',        [StubOpsController::class, 'systemBackups'])->name('system.backups.index');
    Route::get('system/maintenance',    [StubOpsController::class, 'systemMaintenance'])->name('system.maintenance.index');
});
Route::controller(SharedController::class)->group(function () {
    Route::get('lang/{locale}', 'lang')->name('lang');
});

