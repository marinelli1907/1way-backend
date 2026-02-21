{{-- Modules/AdminModule/Resources/views/partials/_sidebar.blade.php --}}
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Request;

    $safeRoute = function ($name, $params = [], $fallback = 'javascript:void(0)') {
        try {
            return Route::has($name) ? route($name, $params) : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $is = function(array $patterns) {
        foreach ($patterns as $p) {
            if (Request::is($p)) return true;
        }
        return false;
    };

    $navItem = function(array $cfg) use ($safeRoute, $is) {
        $label    = $cfg['label'] ?? 'Link';
        $icon     = $cfg['icon'] ?? 'bi bi-dot';
        $route    = $cfg['route'] ?? null;
        $params   = $cfg['params'] ?? [];
        $patterns = $cfg['patterns'] ?? [];
        $forceSoon = (bool)($cfg['coming_soon'] ?? false);

        $exists = $route ? Route::has($route) : false;
        $soon = $forceSoon || !$exists;

        $href = $route ? $safeRoute($route, $params) : 'javascript:void(0)';
        if ($soon) $href = 'javascript:void(0)';

        $active = $patterns ? $is($patterns) : false;

        $cls = 'oneway-nav__link';
        if ($active) $cls .= ' active';
        if ($soon) $cls .= ' is-soon';

        echo '<a class="'.$cls.'" href="'.$href.'">';
        echo '  <i class="'.e($icon).'"></i>';
        echo '  <span class="oneway-nav__text">'.e($label).'</span>';
        if ($soon) echo ' <span class="oneway-pill">Coming Soon</span>';
        echo '</a>';
    };
@endphp

<aside class="oneway-aside">
    <div class="oneway-aside__head">
        <a href="{{ $safeRoute('admin.dashboard') }}" class="oneway-brand">
            <span class="oneway-brand__badge">1W</span>
            <div class="oneway-brand__text">
                <div class="oneway-brand__name">1Way Admin</div>
                <div class="oneway-brand__sub">Control Center</div>
            </div>
        </a>
    </div>

    <div class="oneway-aside__nav">

        {{-- 1) DASHBOARD --}}
        <div class="oneway-nav__section">Dashboard</div>
        @php($navItem([
            'label' => 'Overview',
            'icon' => 'bi bi-grid',
            'route' => 'admin.dashboard',
            'patterns' => ['admin', 'admin/dashboard*']
        ]))
        @php($navItem([
            'label' => 'Live KPIs',
            'icon' => 'bi bi-speedometer2',
            'route' => 'admin.kpis.index',
            'patterns' => ['admin/kpis*']
        ]))
        @php($navItem([
            'label' => 'Alerts',
            'icon' => 'bi bi-bell',
            'route' => 'admin.alerts.index',
            'patterns' => ['admin/alerts*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 2) OPERATIONS --}}
        <div class="oneway-nav__section">Operations</div>
        @php($navItem([
            'label' => 'Live Trips (map + queue)',
            'icon' => 'bi bi-map',
            'route' => 'admin.fleet-map',
            'params' => ['type' => 'all-driver'],
            'patterns' => ['admin/live-trips*','admin/fleet-map*','admin/heat-map*']
        ]))
        @php($navItem([
            'label' => 'Trip Requests',
            'icon' => 'bi bi-inbox',
            'route' => 'admin.trip.index',
            'params' => ['type' => 'pending'],
            'patterns' => ['admin/trip/list/pending*','admin/trip/pending*']
        ]))
        @php($navItem([
            'label' => 'Scheduled Rides',
            'icon' => 'bi bi-calendar2-check',
            'route' => 'admin.trip.index',
            'params' => ['type' => 'scheduled'],
            'patterns' => ['admin/trip/list/scheduled*','admin/trip/scheduled*']
        ]))
        @php($navItem([
            'label' => 'Dispatch / Control Room',
            'icon' => 'bi bi-broadcast',
            'route' => 'admin.control-room.index',
            'patterns' => ['admin/control-room*']
        ]))
        @php($navItem([
            'label' => 'Zones & Coverage',
            'icon' => 'bi bi-geo-alt',
            'route' => 'admin.zone.index',
            'patterns' => ['admin/zone*']
        ]))
        @php($navItem([
            'label' => 'Cancellations / No-Shows',
            'icon' => 'bi bi-x-circle',
            'route' => 'admin.cancellations.index',
            'patterns' => ['admin/cancellations*','admin/no-shows*']
        ]))
        @php($navItem([
            'label' => 'Support Inbox / Tickets',
            'icon' => 'bi bi-headset',
            'route' => 'admin.support.tickets.index',
            'patterns' => ['admin/tickets*','admin/support*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 3) CALENDAR & EVENTS --}}
        <div class="oneway-nav__section">Calendar & Events</div>
        @php($navItem([
            'label' => 'Calendar (admin view)',
            'icon' => 'bi bi-calendar3',
            'route' => 'admin.calendar.index',
            'patterns' => ['admin/calendar*']
        ]))
        @php($navItem([
            'label' => 'Events List',
            'icon' => 'bi bi-list-ul',
            'route' => 'admin.events.index',
            'patterns' => ['admin/events*']
        ]))
        @php($navItem([
            'label' => 'Create / Manage Events',
            'icon' => 'bi bi-plus-circle',
            'route' => 'admin.events.manage',
            'patterns' => ['admin/events/manage*']
        ]))
        @php($navItem([
            'label' => 'Event Ride Planner',
            'icon' => 'bi bi-people',
            'route' => 'admin.event-ride-planner.index',
            'patterns' => ['admin/event-ride-planner*']
        ]))
        @php($navItem([
            'label' => 'Venues / Locations',
            'icon' => 'bi bi-geo',
            'route' => 'admin.venues.index',
            'patterns' => ['admin/venues*','admin/locations*']
        ]))
        @php($navItem([
            'label' => 'Event Analytics',
            'icon' => 'bi bi-graph-up',
            'route' => 'admin.event-analytics.index',
            'patterns' => ['admin/event-analytics*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 4) PROMOTIONS & PARTNERS --}}
        <div class="oneway-nav__section">Promotions & Partners</div>
        @php($navItem([
            'label' => 'Businesses (restaurants/bars/venues)',
            'icon' => 'bi bi-shop',
            'route' => 'admin.businesses.index',
            'patterns' => ['admin/businesses*']
        ]))
        @php($navItem([
            'label' => 'Promoted Listings',
            'icon' => 'bi bi-star',
            'route' => 'admin.promoted-listings.index',
            'patterns' => ['admin/promoted-listings*']
        ]))
        @php($navItem([
            'label' => 'Ad Campaigns',
            'icon' => 'bi bi-megaphone',
            'route' => 'admin.promotion.banner-setup.index',
            'patterns' => ['admin/promotion*','admin/ad-campaigns*']
        ]))
        @php($navItem([
            'label' => 'Ride Incentives (discounts, credits, perks)',
            'icon' => 'bi bi-ticket-perforated',
            'route' => 'admin.ride-incentives.index',
            'patterns' => ['admin/ride-incentives*']
        ]))
        @php($navItem([
            'label' => 'Promo Performance (analytics)',
            'icon' => 'bi bi-bar-chart',
            'route' => 'admin.promo-performance.index',
            'patterns' => ['admin/promo-performance*']
        ]))
        @php($navItem([
            'label' => 'Payout Rules (who funds what)',
            'icon' => 'bi bi-sliders',
            'route' => 'admin.payout-rules.index',
            'patterns' => ['admin/payout-rules*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 5) USERS --}}
        <div class="oneway-nav__section">Users</div>
        @php($navItem([
            'label' => 'Customers',
            'icon' => 'bi bi-person',
            'route' => 'admin.customer.index',
            'patterns' => ['admin/customer*']
        ]))
        @php($navItem([
            'label' => 'Drivers',
            'icon' => 'bi bi-person-badge',
            'route' => 'admin.driver.index',
            'patterns' => ['admin/driver*']
        ]))
        @php($navItem([
            'label' => 'Admin Users / Staff',
            'icon' => 'bi bi-person-gear',
            'route' => 'admin.employee.index',
            'patterns' => ['admin/staff*','admin/employee*','admin/admin-users*']
        ]))
        @php($navItem([
            'label' => 'Roles & Permissions',
            'icon' => 'bi bi-shield-lock',
            'route' => 'admin.roles.index',
            'patterns' => ['admin/roles*','admin/permissions*']
        ]))
        @php($navItem([
            'label' => 'Reviews & Ratings',
            'icon' => 'bi bi-star-half',
            'route' => 'admin.reviews.index',
            'patterns' => ['admin/reviews*','admin/ratings*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 6) DRIVER OPS --}}
        <div class="oneway-nav__section">Driver Ops</div>
        @php($navItem([
            'label' => 'Driver Applications / Onboarding',
            'icon' => 'bi bi-person-check',
            'route' => 'admin.driver-applications.index',
            'patterns' => ['admin/driver-applications*']
        ]))
        @php($navItem([
            'label' => 'Driver Documents',
            'icon' => 'bi bi-file-earmark-text',
            'route' => 'admin.driver-documents.index',
            'patterns' => ['admin/driver-documents*']
        ]))
        @php($navItem([
            'label' => 'Driver Payout Splits (split % system)',
            'icon' => 'bi bi-percent',
            'route' => 'admin.driver-payout-splits.index',
            'patterns' => ['admin/driver-payout-splits*']
        ]))
        @php($navItem([
            'label' => 'Driver Levels / Tiers',
            'icon' => 'bi bi-layers',
            'route' => 'admin.driver-tiers.index',
            'patterns' => ['admin/driver-tiers*']
        ]))
        @php($navItem([
            'label' => 'Driver Availability / Online Status',
            'icon' => 'bi bi-wifi',
            'route' => 'admin.driver-availability.index',
            'patterns' => ['admin/driver-availability*']
        ]))
        @php($navItem([
            'label' => 'Driver Performance',
            'icon' => 'bi bi-graph-up-arrow',
            'route' => 'admin.driver-performance.index',
            'patterns' => ['admin/driver-performance*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 7) PAYMENTS & FINANCE --}}
        <div class="oneway-nav__section">Payments & Finance</div>
        @php($navItem([
            'label' => 'Transactions',
            'icon' => 'bi bi-receipt',
            'route' => 'admin.transaction.index',
            'patterns' => ['admin/transaction*']
        ]))
        @php($navItem([
            'label' => 'Withdrawals',
            'icon' => 'bi bi-bank',
            'route' => 'admin.withdraw.index',
            'patterns' => ['admin/withdraw*']
        ]))
        @php($navItem([
            'label' => 'Cash Collect',
            'icon' => 'bi bi-cash-stack',
            'route' => 'admin.cash-collect.index',
            'patterns' => ['admin/cash-collect*']
        ]))
        @php($navItem([
            'label' => 'Refunds / Chargebacks',
            'icon' => 'bi bi-arrow-counterclockwise',
            'route' => 'admin.refunds.index',
            'patterns' => ['admin/refunds*','admin/chargebacks*']
        ]))
        @php($navItem([
            'label' => 'Commissions & Fees',
            'icon' => 'bi bi-percent',
            'route' => 'admin.commissions.index',
            'patterns' => ['admin/commissions*','admin/fees*']
        ]))
        @php($navItem([
            'label' => 'Coupons / Credits Ledger',
            'icon' => 'bi bi-ticket-detailed',
            'route' => 'admin.coupon.index',
            'patterns' => ['admin/coupon*','admin/coupons*']
        ]))
        @php($navItem([
            'label' => 'Revenue Reports (daily/weekly/monthly)',
            'icon' => 'bi bi-graph-up',
            'route' => 'admin.revenue-reports.index',
            'patterns' => ['admin/revenue*','admin/reports*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 8) BUSINESS CENTER --}}
        <div class="oneway-nav__section">Business Center</div>
        @php($navItem([
            'label' => 'Settings (company info, branding)',
            'icon' => 'bi bi-gear',
            'route' => 'admin.business-settings.index',
            'patterns' => ['admin/business-settings*','admin/settings*']
        ]))
        @php($navItem([
            'label' => 'Pricing Rules (base, surge, min fare)',
            'icon' => 'bi bi-currency-dollar',
            'route' => 'admin.pricing-rules.index',
            'patterns' => ['admin/pricing*','admin/pricing-rules*']
        ]))
        @php($navItem([
            'label' => 'Taxes & Fees (local rules, receipts)',
            'icon' => 'bi bi-receipt-cutoff',
            'route' => 'admin.taxes-fees.index',
            'patterns' => ['admin/taxes*','admin/fees*']
        ]))
        @php($navItem([
            'label' => 'Invoices (for businesses/partners)',
            'icon' => 'bi bi-file-earmark-text',
            'route' => 'admin.invoices.index',
            'patterns' => ['admin/invoices*']
        ]))
        @php($navItem([
            'label' => 'Subscriptions / Plans',
            'icon' => 'bi bi-badge-ad',
            'route' => 'admin.subscriptions.index',
            'patterns' => ['admin/subscriptions*','admin/plans*']
        ]))
        @php($navItem([
            'label' => 'Audit Logs',
            'icon' => 'bi bi-activity',
            'route' => 'admin.audit-logs.index',
            'patterns' => ['admin/audit*','admin/logs*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 9) AI CENTER --}}
        <div class="oneway-nav__section">AI Center</div>
        @php($navItem([
            'label' => 'AI Assistant (Ops Copilot)',
            'icon' => 'bi bi-robot',
            'route' => 'admin.ai.assistant.index',
            'patterns' => ['admin/ai/assistant*','admin/ai/copilot*']
        ]))
        @php($navItem([
            'label' => 'Fraud / Risk Alerts',
            'icon' => 'bi bi-shield-exclamation',
            'route' => 'admin.ai.fraud.index',
            'patterns' => ['admin/ai/fraud*','admin/ai/risk*']
        ]))
        @php($navItem([
            'label' => 'Smart Pricing Suggestions',
            'icon' => 'bi bi-lightning-charge',
            'route' => 'admin.ai.pricing.index',
            'patterns' => ['admin/ai/pricing*']
        ]))
        @php($navItem([
            'label' => 'Driver Supply Predictions',
            'icon' => 'bi bi-diagram-3',
            'route' => 'admin.ai.supply.index',
            'patterns' => ['admin/ai/supply*']
        ]))
        @php($navItem([
            'label' => 'Promo Optimization (“what should we sell?”)',
            'icon' => 'bi bi-magic',
            'route' => 'admin.ai.promo.index',
            'patterns' => ['admin/ai/promo*','admin/ai/optimization*']
        ]))
        @php($navItem([
            'label' => 'Auto Replies (support)',
            'icon' => 'bi bi-chat-dots',
            'route' => 'admin.ai.autoreplies.index',
            'patterns' => ['admin/ai/autoreplies*','admin/ai/support*']
        ]))

        <div class="oneway-nav__divider"></div>

        {{-- 10) SYSTEM --}}
        <div class="oneway-nav__section">System</div>
        @php($navItem([
            'label' => 'App Config',
            'icon' => 'bi bi-sliders2',
            'route' => 'admin.system.config.index',
            'patterns' => ['admin/system/config*','admin/config*']
        ]))
        @php($navItem([
            'label' => 'Notifications (push/email/SMS)',
            'icon' => 'bi bi-bell',
            'route' => 'admin.system.notifications.index',
            'patterns' => ['admin/system/notifications*','admin/notifications*']
        ]))
        @php($navItem([
            'label' => 'Integrations (Stripe, Maps, Twilio, etc.)',
            'icon' => 'bi bi-plug',
            'route' => 'admin.system.integrations.index',
            'patterns' => ['admin/system/integrations*','admin/integrations*']
        ]))
        @php($navItem([
            'label' => 'API Keys',
            'icon' => 'bi bi-key',
            'route' => 'admin.system.api-keys.index',
            'patterns' => ['admin/system/api-keys*','admin/api-keys*']
        ]))
        @php($navItem([
            'label' => 'Backups / Exports',
            'icon' => 'bi bi-cloud-arrow-down',
            'route' => 'admin.system.backups.index',
            'patterns' => ['admin/system/backups*','admin/backups*','admin/exports*']
        ]))
        @php($navItem([
            'label' => 'Maintenance Mode',
            'icon' => 'bi bi-cone-striped',
            'route' => 'admin.system.maintenance.index',
            'patterns' => ['admin/system/maintenance*','admin/maintenance*']
        ]))

                    <!-- Sub Menu -->
                        <!-- End Sub Menu -->
                    </li>
                    <!-- DParcel Attribute Setup-->

                    <!---------- End Parcel Management --------------->
                @endif

                @if(\Illuminate\Support\Facades\Gate::any(['vehicle_view', 'vehicle_add', 'vehicle_edit', 'vehicle_delete', 'vehicle_log', 'vehicle_export']))
                    <!---------- Start Vehicle Management --------------->

                    <li class="nav-category" title="{{ translate('vehicles_management') }}">
                        {{ translate('vehicles_management') }}
                    </li>
                    <li class="{{Request::is('admin/vehicle/attribute-setup/*')?'active open':''}}">
                        <a href="{{ route('admin.vehicle.attribute-setup.brand.index') }}">
                            <i class="bi bi-ev-front-fill"></i>
                            <span class="link-title text-capitalize">{{ translate('vehicle_attribute_setup') }}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('admin/vehicle/log') || Request::is('admin/vehicle') || Request::is('admin/vehicle/show*') || Request::is('admin/vehicle/edit*')?'active open':''}}">
                        <a href="{{ route('admin.vehicle.index') }}">
                            <i class="bi bi-car-front-fill"></i>
                            <span class="link-title text-capitalize">{{ translate('vehicle_list') }}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('admin/vehicle/request/*') ?'active open':''}}">
                        <a href="{{ route('admin.vehicle.request.list') }}">
                            <i class="bi bi-car-front-fill"></i>
                            <span
                                class="link-title text-capitalize">{{ translate('new_vehicle_request_list') }}</span>
                        </a>
                    </li>
                    @if(businessConfig('update_vehicle_status', DRIVER_SETTINGS)?->value == 1)
                        <li class="{{Request::is('admin/vehicle/update/*') ?'active open':''}}">
                            <a href="{{ route('admin.vehicle.update.list') }}">
                                <i class="bi bi-car-front-fill"></i>
                                <span
                                    class="link-title text-capitalize">{{ translate('update_vehicle_request_list') }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="{{Request::is('admin/vehicle/create') ?'active open':''}}">
                        <a href="{{ route('admin.vehicle.create') }}">
                            <i class="bi bi-truck-front-fill"></i>
                            <span class="link-title text-capitalize">{{ translate('add_new_vehicle') }}</span>
                        </a>
                    </li>
                    <!---------- End Vehicle Management --------------->
                @endif

                @if(\Illuminate\Support\Facades\Gate::any(['fare_view', 'fare_add']))
                    <!---------- Start Fare Management --------------->
                    <li class="nav-category"
                        title="{{translate('fare_management')}}">{{translate('fare_management')}}</li>
                    <li class="{{Request::is('admin/fare/trip*')? 'active open' : ''}}">
                        <a href="{{route('admin.fare.trip.index')}}">
                            <i class="bi bi-sign-intersection-y-fill"></i>
                            <span class="link-title text-capitalize">{{translate('trip_fare_setup')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('admin/fare/parcel*')? 'active open' : ''}}">
                        <a href="{{route('admin.fare.parcel.index')}}">
                            <i class="bi bi-box"></i>
                            <span class="link-title text-capitalize">{{translate('parcel_delivery_fare_setup')}}</span>
                        </a>
                    </li>
                    <!---------- End Fare Management --------------->
                @endif

                @if(\Illuminate\Support\Facades\Gate::any(['transaction_view', 'transaction_export']))
                    <!---------- Start Transaction Management --------------->
                    <li class="nav-category"
                        title="{{translate('transactions_&_reports')}}">{{translate('transactions_&_reports')}}</li>
                    <li class="{{Request::is('admin/transaction*')? 'active open' : ''}}">
                        <a href="{{route('admin.transaction.index')}}">
                            <i class="bi bi-cash-stack"></i>
                            <span class="link-title text-capitalize">{{translate('transactions')}}</span>
                        </a>
                    </li>
                    <li class="{{Request::is('admin/report*')? 'active open' : ''}}">
                        <a href="{{route('admin.report.earning')}}">
                            <i class="bi bi-cash-stack"></i>
                            <span class="link-title text-capitalize">{{translate('reports')}}</span>
                        </a>
                    </li>
                    <!---------- End Transaction Management --------------->
                @endif

                @if(\Illuminate\Support\Facades\Gate::any(['chatting_view']))
                    <!---------- Start Help and Support Management --------------->
                    <li class="nav-category"
                        title="{{ translate('help_&_support') }}">{{ translate('help_&_support') }}</li>
                    <li class="{{Request::is('admin/chatting*') ?'active open':''}}">
                        <a href="{{route('admin.chatting')}}">
                            <i class="bi bi-chat-left-dots"></i>
                            <span class="link-title">{{ translate('chatting') }}</span>
                        </a>
                    </li>
                    <!---------- End Help and Support Management --------------->
                @endif

                @if(\Illuminate\Support\Facades\Gate::any(['business_view', 'business_edit', 'business_delete']))
                    <!---------- Start Business Management --------------->
                    <li class="nav-category" title="Business Management">{{translate('business_management')}}</li>
                    <li class="
                {{Request::is('admin/business/setup*')? 'active sub-menu-opened' : ''}}">
                        <a href="{{route('admin.business.setup.info.index')}}">
                            <i class="bi bi-briefcase-fill"></i>
                            <span class="link-title text-capitalize">{{translate('business_setup')}}</span>
                        </a>

                    </li>
                    {{--                <li class="--}}
                    {{--                {{Request::is('admin/business/external*')? 'active sub-menu-opened' : ''}}">--}}
                    {{--                    <a href="{{route('admin.business.external.index')}}">--}}
                    {{--                        <i class="bi bi-gear-wide-connected"></i>--}}
                    {{--                        <span class="link-title text-capitalize">{{translate('Ecommerce Setup and Integration')}}</span>--}}
                    {{--                    </a>--}}

                    {{--                </li>--}}
                    <li class="has-sub-item {{Request::is('admin/business/pages-media/*')? 'active sub-menu-opened' : ''}}">
                        <a href="#">
                            <i class="bi bi-file-earmark-break-fill"></i>
                            <span class="link-title text-capitalize">{{translate('pages_&_media')}}</span>
                        </a>
                        <!-- Sub Menu -->
                        <ul class="nav sub-menu">
                            <li class="{{Request::is('admin/business/pages-media/business-page') ? 'active open' : ''}}">
                                <a class="text-capitalize"
                                   href="{{route('admin.business.pages-media.business-page.index')}}">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('business_pages')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/pages-media/landing-page/*') ? 'active open' : ''}}">
                                <a class="text-capitalize"
                                   href="{{route('admin.business.pages-media.landing-page.intro-section.index')}}">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('landing_Page_Setup')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/pages-media/social-media') ? 'active open' : ''}}">
                                <a class="text-capitalize" href="{{route('admin.business.pages-media.social-media')}}">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('social_media_links')}}
                                </a>
                            </li>
                        </ul>
                        <!-- End Sub Menu -->
                    </li>
                    <li class="has-sub-item  {{Request::is('admin/business/configuration*')? 'active sub-menu-opened' : ''}}">
                        <a href="#">
                            <i class="bi bi-gear-wide-connected"></i>
                            <span class="link-title">{{translate('configurations')}}</span>
                        </a>
                        <!-- Sub Menu -->
                        <ul class="nav sub-menu">
                            <li class="{{Request::is('admin/business/configuration/notification*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.configuration.notification.index')}}">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('Notification')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/configuration/third-party/*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.configuration.third-party.payment-method.index')}}"
                                   class="text-capitalize">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('3rd_party')}}
                                </a>
                            </li>

                        </ul>
                        <!-- End Sub Menu -->
                    </li>
                    <li class="has-sub-item
                {{Request::is('admin/business/environment-setup*') ||Request::is('admin/business/app-version-setup*') ||
                    Request::is('admin/business/clean-database*') || Request::is('admin/business/languages*')? 'active sub-menu-opened' : ''}}">
                        <a href="#">
                            <i class="bi bi-sliders2-vertical"></i>
                            <span class="link-title text-capitalize">{{translate('system_settings')}}</span>
                        </a>
                        <!-- Sub Menu -->
                        <ul class="nav sub-menu">
                            <li class="{{Request::is('admin/business/environment-setup*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.environment-setup.index')}}" class="text-capitalize">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('environment_setup')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/app-version-setup*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.app-version-setup.index')}}" class="text-capitalize">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('app_version_setup')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/clean-database*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.clean-database.index')}}" class="text-capitalize">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('clean_database')}}
                                </a>
                            </li>
                            <li class="{{Request::is('admin/business/languages*') ? 'active open' : ''}}">
                                <a href="{{route('admin.business.languages.index')}}">
                                    <i class="bi bi-dash-lg"></i>
                                    {{translate('languages')}}
                                </a>
                            </li>
                        </ul>
                        <!-- End Sub Menu -->
                    </li>
                    <!---------- End Business Management --------------->
                @endif

                {{-- ── Part E: AI Section (super-admin only) ───────────────── --}}
                @can('super-admin')
                <li class="nav-category">{{ translate('AI') }}</li>
                <li class="{{ Request::is('admin/ai*') ? 'active open' : '' }}">
                    <a href="{{ route('admin.ai.settings') }}">
                        <i class="bi bi-cpu-fill" style="color:#CC0000;"></i>
                        <span class="link-title text-capitalize">{{ translate('AI') }}</span>
                    </a>
                </li>
                @endcan
                {{-- ── End AI ──────────────────────────────────────────────── --}}
            </ul>
            <!-- End Nav -->
        </div>
    </div>
</aside>
