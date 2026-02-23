# Admin Control Panel — Sidebar Inventory (Phase 1)

**Generated:** 2026-02-22  
**Purpose:** Map every sidebar tab to route, controller, view, and data status.  
**"Coming Soon" rule:** Sidebar shows "Coming Soon" when `Route::has($name)` is false OR when `coming_soon` is set true in the nav config (`_sidebar.blade.php`).

---

## Sidebar config summary

- **File:** `Modules/AdminModule/Resources/views/partials/_sidebar.blade.php`
- **Logic:** `$navItem(['label','icon','route','params','patterns','coming_soon'])`. Link is active if `Request::is($patterns)`. If `!Route::has($route)` or `coming_soon === true`, link href is `javascript:void(0)` and pill "Coming Soon" is shown.

---

## Route dump

- `php artisan route:list` → **831** routes (saved to `routes_dump.txt` in project root for reference).

---

## Inventory table

| Label | Route name | URI | Controller @ method | View | Real data sources | Status |
|-------|------------|-----|---------------------|------|-------------------|--------|
| Overview | admin.dashboard | admin/dashboard | AdminModule\...\DashboardController | adminmodule::* | — | REAL |
| Live KPIs | admin.kpis.index | admin/kpis | OpsController@kpis | ops.kpis | TripRequest, DriverDetail, User | REAL |
| Alerts | admin.alerts.index | admin/alerts | OpsController@alerts | ops.alerts | SafetyAlert, TripRequest | REAL |
| Live Trips (map + queue) | admin.fleet-map | admin/fleet-map/{type} | FleetMapController | — | drivers/customers map | REAL |
| Trip Requests | admin.trip.index | admin/trip/list/{type} | TripController@tripList | tripmanagement::admin.trip.* | TripRequest | REAL |
| Scheduled Rides | admin.trip.index | admin/trip/list/scheduled | TripController@tripList | tripmanagement::admin.trip.* | TripRequest | REAL |
| Dispatch / Control Room | admin.control-room.index | admin/control-room | OpsController@controlRoom | ops.control-room | TripRequest, DriverDetail | REAL |
| Zones & Coverage | admin.zone.index | admin/zone | ZoneManagement\...\ZoneController | — | Zone | REAL |
| Cancellations / No-Shows | admin.cancellations.index | admin/cancellations | OpsController@cancellations | ops.cancellations | TripRequest, CancellationReason, fee | REAL |
| Support Inbox / Tickets | admin.support.tickets.index | admin/support/tickets | OpsController@supportTickets | ops.support-tickets | ChannelList (chat) | REAL |
| Calendar (admin view) | admin.calendar.index | admin/calendar | CalendarEventsController@calendar | calendar.calendar | TripRequest (scheduled) | REAL |
| Events List | admin.events.index | admin/events | CalendarEventsController@eventsList | calendar.events-list | TripRequest, Zone | REAL |
| Create / Manage Events | admin.events.manage | admin/events/manage | CalendarEventsController@manageEvents | calendar.manage-events | Zone | PARTIAL |
| Event Ride Planner | admin.event-ride-planner.index | admin/event-ride-planner | CalendarEventsController@eventRidePlanner | calendar.event-ride-planner | Zone, TripRequest, DriverDetail | REAL |
| Venues / Locations | admin.venues.index | admin/venues | CalendarEventsController@venues | calendar.venues | Zone (as venues) | REAL |
| Event Analytics | admin.event-analytics.index | admin/event-analytics | CalendarEventsController@eventAnalytics | calendar.event-analytics | TripRequest, Zone | REAL |
| Businesses | admin.businesses.index | admin/businesses | PromotionsController@businesses | — | — | STUB |
| Promoted Listings | admin.promoted-listings.index | admin/promoted-listings | PromotionsController@promotedListings | — | — | STUB |
| Ad Campaigns | admin.promotion.banner-setup.index | admin/promotion/banner-setup | — | — | — | REAL |
| Ride Incentives | admin.ride-incentives.index | admin/ride-incentives | PromotionsController@rideIncentives | — | — | STUB |
| Promo Performance | admin.promo-performance.index | admin/promo-performance | PromotionsController@promoPerformance | — | — | STUB |
| Payout Rules | admin.payout-rules.index | admin/payout-rules | PromotionsController@payoutRules | — | — | STUB |
| Customers | admin.customer.index | admin/customer | UserManagement\...\CustomerController | — | User | REAL |
| Drivers | admin.driver.index | admin/driver | UserManagement\...\DriverController | — | User, DriverDetail | REAL |
| Admin Users / Staff | admin.employee.index | admin/employee | UserManagement\...\EmployeeController | — | User (employees) | REAL |
| Roles & Permissions | admin.roles.index | admin/roles | UsersOpsController@roles | — | — | REAL |
| Reviews & Ratings | admin.reviews.index | admin/reviews | UsersOpsController@reviews | — | Review | REAL |
| Driver Applications | admin.driver-applications.index | admin/driver-applications | DriverOpsController@driverApplications | — | — | STUB |
| Driver Documents | admin.driver-documents.index | admin/driver-documents | DriverOpsController@driverDocuments | — | — | STUB |
| Driver Payout Splits | admin.driver-payout-splits.index | admin/driver-payout-splits | DriverOpsController@driverPayoutSplits | — | — | STUB |
| Driver Tiers | admin.driver-tiers.index | admin/driver-tiers | DriverOpsController@driverTiers | — | — | STUB |
| Driver Availability | admin.driver-availability.index | admin/driver-availability | DriverOpsController@driverAvailability | — | — | STUB |
| Driver Performance | admin.driver-performance.index | admin/driver-performance | DriverOpsController@driverPerformance | — | — | STUB |
| Transactions | admin.transaction.index | admin/transaction | TransactionManagement | — | Transaction | REAL |
| Withdrawals | admin.withdraw.index | admin/withdraw | PaymentsFinanceController@withdraw | — | — | REAL |
| Cash Collect | admin.cash-collect.index | admin/cash-collect | PaymentsFinanceController@cashCollect | — | — | REAL |
| Refunds | admin.refunds.index | admin/refunds | PaymentsFinanceController@refunds | — | — | REAL |
| Commissions & Fees | admin.commissions.index | admin/commissions | PaymentsFinanceController@commissions | — | — | REAL |
| Coupons / Credits | admin.coupon.index | admin/coupon | PaymentsFinanceController@coupon | — | — | REAL |
| Revenue Reports | admin.revenue-reports.index | admin/revenue-reports | PaymentsFinanceController@revenueReports | — | — | REAL |
| Business Settings | admin.business-settings.index | admin/business-settings | — | — | — | STUB |
| Pricing Rules | admin.pricing-rules.index | admin/pricing-rules | — | — | — | STUB |
| Taxes & Fees | admin.taxes-fees.index | admin/taxes-fees | — | — | — | STUB |
| Invoices | admin.invoices.index | admin/invoices | — | — | — | STUB |
| Subscriptions | admin.subscriptions.index | admin/subscriptions | — | — | — | STUB |
| Audit Logs | admin.audit-logs.index | admin/audit-logs | — | — | — | STUB |
| AI Assistant | admin.ai.assistant.index | admin/ai/assistant | — | — | — | STUB |
| AI Fraud | admin.ai.fraud.index | admin/ai/fraud | — | — | — | STUB |
| AI Pricing | admin.ai.pricing.index | admin/ai/pricing | — | — | — | STUB |
| AI Supply | admin.ai.supply.index | admin/ai/supply | — | — | — | STUB |
| AI Promo | admin.ai.promo.index | admin/ai/promo | — | — | — | STUB |
| AI Auto Replies | admin.ai.autoreplies.index | admin/ai/autoreplies | — | — | — | STUB |
| App Config | admin.system.config.index | admin/system/config | — | — | — | STUB |
| Notifications | admin.system.notifications.index | admin/system/notifications | — | — | — | STUB |
| Integrations | admin.system.integrations.index | admin/system/integrations | — | — | — | STUB |
| API Keys | admin.system.api-keys.index | admin/system/api-keys | — | — | — | STUB |
| Backups | admin.system.backups.index | admin/system/backups | — | — | — | STUB |
| Maintenance | admin.system.maintenance.index | admin/system/maintenance | — | — | — | STUB |

---

## Notes

- **Trip details link:** Views reference `route('admin.trip.details', $id)` but the registered route name is **admin.trip.show** (URI: `admin/trip/details/{id}`). Use `admin.trip.show` in Blade or add a route alias.
- **REAL:** Page exists, uses real models/tables (with safe try/catch where applied).  
- **PARTIAL:** Page exists but uses minimal or placeholder data.  
- **STUB:** Route exists but page is stub or placeholder; no real data wiring yet.
