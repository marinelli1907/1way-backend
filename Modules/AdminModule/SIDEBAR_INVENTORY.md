# Admin Sidebar Route Inventory

Generated from _sidebar.blade.php and `php artisan route:list`.  
**exists?** = route name registered. **action** = placeholder (Coming Soon) vs real page. **status** = OK (real page) | FIX (placeholder or missing).

| Label | Route name | exists? | action | status |
|-------|------------|---------|--------|--------|
| Overview | admin.dashboard | yes | real (DashboardController) | OK |
| Live KPIs | admin.kpis.index | yes | real (OpsController) | OK |
| Alerts | admin.alerts.index | yes | real (OpsController) | OK |
| Live Trips (map + queue) | admin.fleet-map | yes | real (FleetMapViewController) | OK |
| Trip Requests | admin.trip.index | yes | real (other module) | OK |
| Scheduled Rides | admin.trip.index | yes | real (other module) | OK |
| Dispatch / Control Room | admin.control-room.index | yes | real (OpsController) | OK |
| Zones & Coverage | admin.zone.index | yes | real (other module) | OK |
| Cancellations / No-Shows | admin.cancellations.index | yes | real (OpsController) | OK |
| Support Inbox / Tickets | admin.support.tickets.index | yes | real (OpsController) | OK |
| Calendar (admin view) | admin.calendar.index | yes | real (CalendarEventsController) | OK |
| Events List | admin.events.index | yes | real (CalendarEventsController) | OK |
| Create / Manage Events | admin.events.manage | yes | real (CalendarEventsController) | OK |
| Event Ride Planner | admin.event-ride-planner.index | yes | real (CalendarEventsController) | OK |
| Venues / Locations | admin.venues.index | yes | real (CalendarEventsController) | OK |
| Event Analytics | admin.event-analytics.index | yes | real (CalendarEventsController) | OK |
| Businesses (restaurants/bars/venues) | admin.businesses.index | yes | real (StubOpsController) | OK |
| Promoted Listings | admin.promoted-listings.index | yes | real (StubOpsController) | OK |
| Ad Campaigns | admin.promotion.banner-setup.index | yes | real (other module) | OK |
| Ride Incentives | admin.ride-incentives.index | yes | real (StubOpsController) | OK |
| Promo Performance (analytics) | admin.promo-performance.index | yes | real (StubOpsController) | OK |
| Payout Rules | admin.payout-rules.index | yes | real (StubOpsController) | OK |
| Customers | admin.customer.index | yes | real (UserManagement) | OK |
| Drivers | admin.driver.index | yes | real (UserManagement) | OK |
| Admin Users / Staff | admin.employee.index | yes | real (other module) | OK |
| Roles & Permissions | admin.roles.index | yes | real (StubOpsController) | OK |
| Reviews & Ratings | admin.reviews.index | yes | real (StubOpsController) | OK |
| Driver Applications / Onboarding | admin.driver-applications.index | yes | real (StubOpsController) | OK |
| Driver Documents | admin.driver-documents.index | yes | real (StubOpsController) | OK |
| Driver Payout Splits | admin.driver-payout-splits.index | yes | real (StubOpsController) | OK |
| Driver Levels / Tiers | admin.driver-tiers.index | yes | real (StubOpsController) | OK |
| Driver Availability / Online Status | admin.driver-availability.index | yes | real (StubOpsController) | OK |
| Driver Performance | admin.driver-performance.index | yes | real (StubOpsController) | OK |
| Transactions | admin.transaction.index | yes | real (other module) | OK |
| Withdrawals | admin.withdraw.index | yes | real (StubOpsController) | OK |
| Cash Collect | admin.cash-collect.index | yes | real (StubOpsController) | OK |
| Refunds / Chargebacks | admin.refunds.index | yes | real (StubOpsController) | OK |
| Commissions & Fees | admin.commissions.index | yes | real (StubOpsController) | OK |
| Coupons / Credits Ledger | admin.coupon.index | yes | real (StubOpsController) | OK |
| Revenue Reports | admin.revenue-reports.index | yes | real (StubOpsController) | OK |
| Settings (company info, branding) | admin.business-settings.index | yes | real (StubOpsController) | OK |
| Pricing Rules | admin.pricing-rules.index | yes | real (StubOpsController) | OK |
| Taxes & Fees | admin.taxes-fees.index | yes | real (StubOpsController) | OK |
| Invoices | admin.invoices.index | yes | real (StubOpsController) | OK |
| Subscriptions / Plans | admin.subscriptions.index | yes | real (StubOpsController) | OK |
| Audit Logs | admin.audit-logs.index | yes | real (StubOpsController) | OK |
| AI Assistant (Ops Copilot) | admin.ai.assistant.index | yes | real (StubOpsController) | OK |
| Fraud / Risk Alerts | admin.ai.fraud.index | yes | real (StubOpsController) | OK |
| Smart Pricing Suggestions | admin.ai.pricing.index | yes | real (StubOpsController) | OK |
| Driver Supply Predictions | admin.ai.supply.index | yes | real (StubOpsController) | OK |
| Promo Optimization | admin.ai.promo.index | yes | real (StubOpsController) | OK |
| Auto Replies (support) | admin.ai.autoreplies.index | yes | real (StubOpsController) | OK |
| App Config | admin.system.config.index | yes | real (StubOpsController) | OK |
| Notifications (push/email/SMS) | admin.system.notifications.index | yes | real (StubOpsController) | OK |
| Integrations | admin.system.integrations.index | yes | real (StubOpsController) | OK |
| API Keys | admin.system.api-keys.index | yes | real (StubOpsController) | OK |
| Backups / Exports | admin.system.backups.index | yes | real (StubOpsController) | OK |
| Maintenance Mode | admin.system.maintenance.index | yes | real (StubOpsController) | OK |
