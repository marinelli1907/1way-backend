<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\ActivityLogController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\CalendarEventsController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\DashboardController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\FirebaseSubscribeController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\GenericAdminController;
use Modules\AdminModule\Http\Controllers\Web\New\Admin\OpsController;
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
    // ── Ops: Live KPIs, Alerts, Control Room, Cancellations, Support ───────────
    Route::controller(OpsController::class)->group(function () {
        Route::get('kpis',                  'kpis')->name('kpis.index');
        Route::get('alerts',                'alerts')->name('alerts.index');
        Route::get('control-room',          'controlRoom')->name('control-room.index');
        Route::get('cancellations',         'cancellations')->name('cancellations.index');
        Route::get('support/tickets',       'supportTickets')->name('support.tickets.index');
    });

    // ── Calendar & Events ────────────────────────────────────────────────────
    Route::controller(CalendarEventsController::class)->group(function () {
        Route::get('calendar',              'calendar')->name('calendar.index');
        Route::get('events',                'events')->name('events.index');
        Route::get('events/manage',         'manageEvents')->name('events.manage');
        Route::get('event-ride-planner',    'eventRidePlanner')->name('event-ride-planner.index');
        Route::get('venues',                'venues')->name('venues.index');
        Route::get('event-analytics',       'eventAnalytics')->name('event-analytics.index');
    });

    // ── Promotions & Partners (stub pages) ──────────────────────────────────
    Route::controller(GenericAdminController::class)->group(function () {
        Route::get('businesses',         'businesses')->name('businesses.index');
        Route::get('promoted-listings',  'promotedListings')->name('promoted-listings.index');
        Route::get('ride-incentives',    'rideIncentives')->name('ride-incentives.index');
        Route::get('promo-performance',  'promoPerformance')->name('promo-performance.index');
        Route::get('payout-rules',       'payoutRules')->name('payout-rules.index');

        // ── Users ────────────────────────────────────────────────────────────
        Route::get('reviews',            'reviews')->name('reviews.index');

        // ── Driver Ops ───────────────────────────────────────────────────────
        Route::get('driver-applications','driverApplications')->name('driver-applications.index');
        Route::get('driver-documents',   'driverDocuments')->name('driver-documents.index');
        Route::get('driver-payout-splits','driverPayoutSplits')->name('driver-payout-splits.index');
        Route::get('driver-availability','driverAvailability')->name('driver-availability.index');
        Route::get('driver-performance', 'driverPerformance')->name('driver-performance.index');

        // ── Payments & Finance (stubs) ───────────────────────────────────────
        Route::get('cash-collect',       'cashCollect')->name('cash-collect.index');
        Route::get('refunds',            'refunds')->name('refunds.index');
        Route::get('commissions',        'commissions')->name('commissions.index');
        Route::get('revenue-reports',    'revenueReports')->name('revenue-reports.index');

        // ── Business Center (stubs) ──────────────────────────────────────────
        Route::get('taxes-fees',         'taxesFees')->name('taxes-fees.index');
        Route::get('invoices',           'invoices')->name('invoices.index');
        Route::get('subscriptions',      'subscriptions')->name('subscriptions.index');

        // ── AI Center ────────────────────────────────────────────────────────
        Route::get('ai/assistant',       'aiAssistant')->name('ai.assistant.index');
        Route::get('ai/fraud',           'aiFraud')->name('ai.fraud.index');
        Route::get('ai/pricing',         'aiPricing')->name('ai.pricing.index');
        Route::get('ai/supply',          'aiSupply')->name('ai.supply.index');
        Route::get('ai/promo',           'aiPromo')->name('ai.promo.index');
        Route::get('ai/autoreplies',     'aiAutoreplies')->name('ai.autoreplies.index');

        // ── System (stubs) ────────────────────────────────────────────────────
        Route::get('system/api-keys',    'apiKeys')->name('system.api-keys.index');
        Route::get('system/backups',     'backups')->name('system.backups.index');
    });

    // ── Alias routes: redirect to existing real pages ────────────────────────
    // Users
    Route::get('roles', fn() => redirect()->route('admin.employee.role.index'))->name('roles.index');
    // Driver Ops
    Route::get('driver-tiers', fn() => redirect()->route('admin.driver.level.index'))->name('driver-tiers.index');
    // Payments & Finance
    Route::get('withdraw',     fn() => redirect()->route('admin.driver.withdraw.requests'))->name('withdraw.index');
    Route::get('coupon',       fn() => redirect()->route('admin.promotion.coupon-setup.index'))->name('coupon.index');
    // Business Center
    Route::get('business-settings', fn() => redirect()->route('admin.business.setup.info.settings'))->name('business-settings.index');
    Route::get('pricing-rules',     fn() => redirect()->route('admin.business.setup.trip-fare.trips'))->name('pricing-rules.index');
    Route::get('audit-logs',        fn() => redirect()->route('admin.log'))->name('audit-logs.index');
    // System (alias to existing Business config pages)
    Route::get('system/config',         fn() => redirect()->route('admin.business.environment-setup.index'))->name('system.config.index');
    Route::get('system/notifications',  fn() => redirect()->route('admin.business.configuration.notification.index'))->name('system.notifications.index');
    Route::get('system/integrations',   fn() => redirect()->route('admin.business.configuration.third-party.payment-method.index'))->name('system.integrations.index');
    Route::get('system/maintenance',    fn() => redirect()->route('admin.business.setup.info.maintenance'))->name('system.maintenance.index');

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
});
Route::controller(SharedController::class)->group(function () {
    Route::get('lang/{locale}', 'lang')->name('lang');
});

