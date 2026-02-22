<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Serves all sidebar stubs that don't have a dedicated module yet.
 * Every method follows the same pattern:
 *   1. Try to fetch real data with try/catch
 *   2. Pass $title, $subtitle, $icon, $kpis[], $columns[], $rows, $filters, $lastUpdated
 *   3. Return adminmodule::admin-stub view
 *
 * Add real queries here as tables/models become available.
 */
class GenericAdminController extends Controller
{
    // ── Promotions & Partners ────────────────────────────────────────────────

    public function businesses(Request $request)
    {
        return $this->stub([
            'title'    => 'Businesses',
            'icon'     => 'bi-shop',
            'subtitle' => 'Partner restaurants, bars, and venues',
            'kpis'     => [
                ['label' => 'Total',   'value' => 0, 'icon' => 'bi-shop',            'color' => '#3b82f6'],
                ['label' => 'Active',  'value' => 0, 'icon' => 'bi-check-circle',    'color' => '#10b981'],
                ['label' => 'Pending', 'value' => 0, 'icon' => 'bi-hourglass-split', 'color' => '#f59e0b'],
                ['label' => 'Zones',   'value' => $this->zoneCount(),                'icon' => 'bi-geo-alt',      'color' => '#8b5cf6'],
            ],
            'columns' => ['Name', 'Type', 'Zone', 'Contact', 'Status', 'Created'],
            'filters' => $this->baseFilters($request, ['zone']),
        ]);
    }

    public function promotedListings(Request $request)
    {
        return $this->stub([
            'title'    => 'Promoted Listings',
            'icon'     => 'bi-star',
            'subtitle' => 'Businesses and venues with active promotions',
            'kpis'     => [
                ['label' => 'Total Listings', 'value' => 0, 'icon' => 'bi-star',            'color' => '#f59e0b'],
                ['label' => 'Active',         'value' => 0, 'icon' => 'bi-check-circle',    'color' => '#10b981'],
                ['label' => 'Expiring Soon',  'value' => 0, 'icon' => 'bi-clock',           'color' => '#ef4444'],
                ['label' => 'Revenue',        'value' => '$0', 'icon' => 'bi-currency-dollar', 'color' => '#6b7280'],
            ],
            'columns' => ['Business', 'Package', 'Start', 'End', 'Impressions', 'Status'],
            'filters' => $this->baseFilters($request),
        ]);
    }

    public function rideIncentives(Request $request)
    {
        return $this->stub([
            'title'    => 'Ride Incentives',
            'icon'     => 'bi-ticket-perforated',
            'subtitle' => 'Discounts, credits, and perks for riders',
            'kpis'     => [
                ['label' => 'Active Incentives', 'value' => 0, 'icon' => 'bi-ticket-perforated', 'color' => '#3b82f6'],
                ['label' => 'Claimed Today',     'value' => 0, 'icon' => 'bi-calendar-day',      'color' => '#10b981'],
                ['label' => 'Total Value',       'value' => '$0', 'icon' => 'bi-currency-dollar', 'color' => '#f59e0b'],
                ['label' => 'Expiring Soon',     'value' => 0, 'icon' => 'bi-clock',              'color' => '#ef4444'],
            ],
            'columns' => ['Name', 'Type', 'Value', 'Limit', 'Used', 'Expires', 'Status'],
            'filters' => $this->baseFilters($request),
        ]);
    }

    public function promoPerformance(Request $request)
    {
        return $this->stub([
            'title'    => 'Promo Performance',
            'icon'     => 'bi-bar-chart',
            'subtitle' => 'Analytics for promotions and campaigns',
            'kpis'     => [
                ['label' => 'Campaigns',   'value' => 0,   'icon' => 'bi-megaphone',        'color' => '#3b82f6'],
                ['label' => 'Impressions', 'value' => 0,   'icon' => 'bi-eye',              'color' => '#8b5cf6'],
                ['label' => 'Conversions', 'value' => 0,   'icon' => 'bi-graph-up-arrow',   'color' => '#10b981'],
                ['label' => 'Revenue',     'value' => '$0','icon' => 'bi-currency-dollar',  'color' => '#f59e0b'],
            ],
            'columns' => ['Campaign', 'Start', 'End', 'Budget', 'Spent', 'Conversions', 'ROAS'],
            'filters' => $this->baseFilters($request),
        ]);
    }

    public function payoutRules(Request $request)
    {
        return $this->stub([
            'title'    => 'Payout Rules',
            'icon'     => 'bi-sliders',
            'subtitle' => 'Define who funds what for each promotion type',
            'kpis'     => [
                ['label' => 'Active Rules', 'value' => 0, 'icon' => 'bi-sliders',    'color' => '#3b82f6'],
                ['label' => 'Partner Split', 'value' => '—%', 'icon' => 'bi-percent', 'color' => '#10b981'],
                ['label' => 'Platform Split', 'value' => '—%', 'icon' => 'bi-percent','color' => '#f59e0b'],
                ['label' => 'Last Modified', 'value' => '—', 'icon' => 'bi-clock',    'color' => '#6b7280'],
            ],
            'columns' => ['Rule Name', 'Type', 'Partner %', 'Platform %', 'Applies To', 'Status'],
            'filters' => $this->baseFilters($request),
        ]);
    }

    // ── Users ────────────────────────────────────────────────────────────────

    public function reviews(Request $request)
    {
        [$total, $rows] = $this->safeReviews($request);
        return $this->stub([
            'title'    => 'Reviews & Ratings',
            'icon'     => 'bi-star-half',
            'subtitle' => 'Customer and driver reviews',
            'kpis'     => [
                ['label' => 'Total Reviews', 'value' => $total['all'],      'icon' => 'bi-star-half',     'color' => '#f59e0b'],
                ['label' => 'Avg Rating',    'value' => $total['avg'],      'icon' => 'bi-star-fill',     'color' => '#10b981'],
                ['label' => 'This Week',     'value' => $total['week'],     'icon' => 'bi-calendar-week', 'color' => '#3b82f6'],
                ['label' => '1-Star',        'value' => $total['one_star'], 'icon' => 'bi-exclamation-triangle', 'color' => '#ef4444'],
            ],
            'columns' => ['Trip', 'Reviewer', 'Recipient', 'Rating', 'Comment', 'Date'],
            'filters' => $this->baseFilters($request, ['status']),
            'rows'     => $rows,
        ]);
    }

    // ── Driver Ops ────────────────────────────────────────────────────────────

    public function driverApplications(Request $request)
    {
        [$counts, $rows] = $this->safeDriverApps($request);
        return $this->stub([
            'title'    => 'Driver Applications / Onboarding',
            'icon'     => 'bi-person-check',
            'subtitle' => 'Review and approve incoming driver sign-ups',
            'kpis'     => [
                ['label' => 'Total Applicants', 'value' => $counts['total'],   'icon' => 'bi-people',         'color' => '#3b82f6'],
                ['label' => 'Pending Review',   'value' => $counts['pending'], 'icon' => 'bi-hourglass-split','color' => '#f59e0b'],
                ['label' => 'Approved Today',   'value' => $counts['today'],   'icon' => 'bi-person-check',   'color' => '#10b981'],
                ['label' => 'Rejected',         'value' => $counts['denied'],  'icon' => 'bi-person-x',       'color' => '#ef4444'],
            ],
            'columns' => ['Name', 'Phone', 'Email', 'Service', 'ID Type', 'Applied', 'Status'],
            'filters' => $this->baseFilters($request, ['status', 'zone']),
            'rows'     => $rows,
        ]);
    }

    public function driverDocuments(Request $request)
    {
        return $this->stub([
            'title'    => 'Driver Documents',
            'icon'     => 'bi-file-earmark-text',
            'subtitle' => 'View and verify uploaded driver identity documents',
            'kpis'     => [
                ['label' => 'Total Docs',   'value' => 0, 'icon' => 'bi-file-earmark-text', 'color' => '#3b82f6'],
                ['label' => 'Pending',      'value' => 0, 'icon' => 'bi-hourglass-split',   'color' => '#f59e0b'],
                ['label' => 'Verified',     'value' => 0, 'icon' => 'bi-patch-check',       'color' => '#10b981'],
                ['label' => 'Expired',      'value' => 0, 'icon' => 'bi-exclamation-circle','color' => '#ef4444'],
            ],
            'columns' => ['Driver', 'Doc Type', 'ID Number', 'Uploaded', 'Expiry', 'Status'],
            'filters' => $this->baseFilters($request, ['status']),
        ]);
    }

    public function driverPayoutSplits(Request $request)
    {
        return $this->stub([
            'title'    => 'Driver Payout Splits',
            'icon'     => 'bi-percent',
            'subtitle' => 'Configure driver/platform revenue split percentages',
            'kpis'     => [
                ['label' => 'Driver Cut (avg)', 'value' => '—%', 'icon' => 'bi-percent',          'color' => '#10b981'],
                ['label' => 'Platform Cut',     'value' => '—%', 'icon' => 'bi-percent',          'color' => '#3b82f6'],
                ['label' => 'Active Rules',     'value' => 0,    'icon' => 'bi-sliders',          'color' => '#f59e0b'],
                ['label' => 'Custom Splits',    'value' => 0,    'icon' => 'bi-person-gear',      'color' => '#8b5cf6'],
            ],
            'columns' => ['Rule Name', 'Driver %', 'Platform %', 'Applies To', 'Zone', 'Status'],
            'filters' => $this->baseFilters($request, ['zone']),
        ]);
    }

    public function driverAvailability(Request $request)
    {
        [$counts, $rows] = $this->safeDriverAvailability($request);
        return $this->stub([
            'title'    => 'Driver Availability / Online Status',
            'icon'     => 'bi-wifi',
            'subtitle' => 'Monitor which drivers are currently online',
            'kpis'     => [
                ['label' => 'Online Now',     'value' => $counts['online'],      'icon' => 'bi-wifi',           'color' => '#10b981'],
                ['label' => 'On Trip',        'value' => $counts['on_trip'],     'icon' => 'bi-geo-alt-fill',   'color' => '#3b82f6'],
                ['label' => 'Offline',        'value' => $counts['offline'],     'icon' => 'bi-wifi-off',       'color' => '#6b7280'],
                ['label' => 'On Bidding',     'value' => $counts['on_bidding'],  'icon' => 'bi-activity',       'color' => '#f59e0b'],
            ],
            'columns' => ['Driver', 'Phone', 'Zone', 'Status', 'Last Seen', 'Total Rides'],
            'filters' => $this->baseFilters($request, ['status', 'zone']),
            'rows'     => $rows,
        ]);
    }

    public function driverPerformance(Request $request)
    {
        return $this->stub([
            'title'    => 'Driver Performance',
            'icon'     => 'bi-graph-up-arrow',
            'subtitle' => 'Ratings, acceptance rates, and completion metrics',
            'kpis'     => [
                ['label' => 'Avg Rating',     'value' => '—', 'icon' => 'bi-star-fill',       'color' => '#f59e0b'],
                ['label' => 'Accept Rate',    'value' => '—%','icon' => 'bi-check-circle',    'color' => '#10b981'],
                ['label' => 'Complete Rate',  'value' => '—%','icon' => 'bi-graph-up-arrow',  'color' => '#3b82f6'],
                ['label' => 'Avg Daily Rides','value' => '—', 'icon' => 'bi-car-front',       'color' => '#8b5cf6'],
            ],
            'columns' => ['Driver', 'Total Rides', 'Completed', 'Cancelled', 'Rating', 'Acceptance %', 'Earnings'],
            'filters' => $this->baseFilters($request, ['zone']),
        ]);
    }

    // ── Payments & Finance ────────────────────────────────────────────────────

    public function cashCollect(Request $request)
    {
        [$counts, $rows] = $this->safeCashCollect($request);
        return $this->stub([
            'title'    => 'Cash Collect',
            'icon'     => 'bi-cash-stack',
            'subtitle' => 'Cash rides and pending driver cash settlements',
            'kpis'     => [
                ['label' => 'Total Cash Rides',   'value' => $counts['total'],   'icon' => 'bi-cash-stack',     'color' => '#3b82f6'],
                ['label' => 'Pending Collection', 'value' => $counts['pending'], 'icon' => 'bi-hourglass-split','color' => '#f59e0b'],
                ['label' => 'Collected Today',    'value' => $counts['today'],   'icon' => 'bi-calendar-day',   'color' => '#10b981'],
                ['label' => 'Total Amount',       'value' => '$' . number_format($counts['amount'], 0), 'icon' => 'bi-currency-dollar', 'color' => '#8b5cf6'],
            ],
            'columns' => ['Trip Ref', 'Driver', 'Customer', 'Amount', 'Date', 'Collected'],
            'filters' => $this->baseFilters($request, ['zone']),
            'rows'     => $rows,
        ]);
    }

    public function refunds(Request $request)
    {
        [$counts, $rows] = $this->safeRefunds($request);
        return $this->stub([
            'title'    => 'Refunds / Chargebacks',
            'icon'     => 'bi-arrow-counterclockwise',
            'subtitle' => 'Trip refund requests and chargeback tracking',
            'kpis'     => [
                ['label' => 'Total Refunds', 'value' => $counts['total'],   'icon' => 'bi-arrow-counterclockwise', 'color' => '#3b82f6'],
                ['label' => 'Pending',       'value' => $counts['pending'], 'icon' => 'bi-hourglass-split',       'color' => '#f59e0b'],
                ['label' => 'Approved',      'value' => $counts['approved'],'icon' => 'bi-check-circle',          'color' => '#10b981'],
                ['label' => 'Total Amount',  'value' => '$' . number_format($counts['amount'], 0), 'icon' => 'bi-currency-dollar', 'color' => '#ef4444'],
            ],
            'columns' => ['Trip Ref', 'Customer', 'Amount', 'Reason', 'Status', 'Date'],
            'filters' => $this->baseFilters($request, ['status']),
            'rows'     => $rows,
        ]);
    }

    public function commissions(Request $request)
    {
        [$counts, $rows] = $this->safeCommissions($request);
        return $this->stub([
            'title'    => 'Commissions & Fees',
            'icon'     => 'bi-percent',
            'subtitle' => 'Platform fee and driver commission breakdown',
            'kpis'     => [
                ['label' => 'Total Earned',    'value' => '$' . number_format($counts['earned'], 0),    'icon' => 'bi-currency-dollar', 'color' => '#10b981'],
                ['label' => 'Today',           'value' => '$' . number_format($counts['today'], 0),     'icon' => 'bi-calendar-day',    'color' => '#3b82f6'],
                ['label' => 'This Week',       'value' => '$' . number_format($counts['week'], 0),      'icon' => 'bi-calendar-week',   'color' => '#8b5cf6'],
                ['label' => 'Avg per Trip',    'value' => '$' . number_format($counts['avg_trip'], 2),  'icon' => 'bi-car-front',       'color' => '#f59e0b'],
            ],
            'columns' => ['Trip Ref', 'Driver', 'Gross Fare', 'Driver Payout', 'Platform Fee', 'Date'],
            'filters' => $this->baseFilters($request, ['zone']),
            'rows'     => $rows,
        ]);
    }

    public function revenueReports(Request $request)
    {
        [$counts, $rows] = $this->safeRevenue($request);
        return $this->stub([
            'title'    => 'Revenue Reports',
            'icon'     => 'bi-graph-up',
            'subtitle' => 'Daily, weekly, and monthly revenue breakdown',
            'kpis'     => [
                ['label' => 'Today',      'value' => '$' . number_format($counts['today'], 0),   'icon' => 'bi-calendar-day',   'color' => '#3b82f6'],
                ['label' => 'This Week',  'value' => '$' . number_format($counts['week'], 0),    'icon' => 'bi-calendar-week',  'color' => '#10b981'],
                ['label' => 'This Month', 'value' => '$' . number_format($counts['month'], 0),   'icon' => 'bi-calendar-month', 'color' => '#8b5cf6'],
                ['label' => 'Total',      'value' => '$' . number_format($counts['total'], 0),   'icon' => 'bi-currency-dollar','color' => '#f59e0b'],
            ],
            'columns' => ['Date', 'Trips', 'Gross Revenue', 'Refunds', 'Platform Revenue', 'Driver Payouts'],
            'filters' => $this->baseFilters($request, ['zone']),
            'rows'     => $rows,
        ]);
    }

    // ── Business Center ───────────────────────────────────────────────────────

    public function taxesFees(Request $request)
    {
        return $this->stub([
            'title'    => 'Taxes & Fees',
            'icon'     => 'bi-receipt-cutoff',
            'subtitle' => 'Configure local tax rules and fee structures',
            'kpis'     => [
                ['label' => 'Tax Rules',     'value' => 0,   'icon' => 'bi-receipt-cutoff', 'color' => '#3b82f6'],
                ['label' => 'Active',        'value' => 0,   'icon' => 'bi-check-circle',   'color' => '#10b981'],
                ['label' => 'Zones Covered', 'value' => $this->zoneCount(), 'icon' => 'bi-geo-alt', 'color' => '#8b5cf6'],
                ['label' => 'Default Rate',  'value' => '—%','icon' => 'bi-percent',        'color' => '#f59e0b'],
            ],
            'columns' => ['Rule Name', 'Type', 'Rate', 'Zone', 'Applies To', 'Status'],
            'filters' => $this->baseFilters($request, ['zone']),
        ]);
    }

    public function invoices(Request $request)
    {
        return $this->stub([
            'title'    => 'Invoices',
            'icon'     => 'bi-file-earmark-text',
            'subtitle' => 'Invoices issued to partner businesses',
            'kpis'     => [
                ['label' => 'Total',   'value' => 0,    'icon' => 'bi-file-earmark-text', 'color' => '#3b82f6'],
                ['label' => 'Pending', 'value' => 0,    'icon' => 'bi-hourglass-split',  'color' => '#f59e0b'],
                ['label' => 'Paid',    'value' => 0,    'icon' => 'bi-check-circle',     'color' => '#10b981'],
                ['label' => 'Overdue', 'value' => 0,    'icon' => 'bi-exclamation-circle','color' => '#ef4444'],
            ],
            'columns' => ['Invoice #', 'Business', 'Amount', 'Issued', 'Due', 'Status'],
            'filters' => $this->baseFilters($request, ['status']),
        ]);
    }

    public function subscriptions(Request $request)
    {
        return $this->stub([
            'title'    => 'Subscriptions / Plans',
            'icon'     => 'bi-badge-ad',
            'subtitle' => 'Manage business and partner subscription plans',
            'kpis'     => [
                ['label' => 'Active Subs',  'value' => 0,    'icon' => 'bi-badge-ad',      'color' => '#3b82f6'],
                ['label' => 'Expiring Soon','value' => 0,    'icon' => 'bi-clock',         'color' => '#f59e0b'],
                ['label' => 'MRR',          'value' => '$0', 'icon' => 'bi-currency-dollar','color' => '#10b981'],
                ['label' => 'Cancelled',    'value' => 0,    'icon' => 'bi-x-circle',      'color' => '#ef4444'],
            ],
            'columns' => ['Business', 'Plan', 'Price', 'Started', 'Renews', 'Status'],
            'filters' => $this->baseFilters($request, ['status']),
        ]);
    }

    // ── AI Center ─────────────────────────────────────────────────────────────

    public function aiAssistant(Request $request)
    {
        return $this->stub([
            'title'    => 'AI Assistant (Ops Copilot)',
            'icon'     => 'bi-robot',
            'subtitle' => 'AI-powered operational insights and recommendations',
            'kpis'     => [
                ['label' => 'Queries Today', 'value' => 0, 'icon' => 'bi-robot',          'color' => '#3b82f6'],
                ['label' => 'Actions Taken', 'value' => 0, 'icon' => 'bi-lightning-charge','color' => '#10b981'],
                ['label' => 'Saved Hours',   'value' => 0, 'icon' => 'bi-clock',          'color' => '#8b5cf6'],
                ['label' => 'Accuracy',      'value' => '—%','icon' => 'bi-graph-up',     'color' => '#f59e0b'],
            ],
            'columns'  => ['Query', 'Category', 'Confidence', 'Action Taken', 'Time'],
            'filters'  => $this->baseFilters($request),
            'notice'   => 'AI Ops Copilot is being configured. Recommendations will appear here once the AI service is active.',
        ]);
    }

    public function aiFraud(Request $request)
    {
        return $this->stub([
            'title'    => 'Fraud / Risk Alerts',
            'icon'     => 'bi-shield-exclamation',
            'subtitle' => 'AI-detected anomalies and risk signals',
            'kpis'     => [
                ['label' => 'Alerts Today',   'value' => 0, 'icon' => 'bi-shield-exclamation', 'color' => '#ef4444'],
                ['label' => 'Under Review',   'value' => 0, 'icon' => 'bi-hourglass-split',    'color' => '#f59e0b'],
                ['label' => 'Cleared',        'value' => 0, 'icon' => 'bi-shield-check',       'color' => '#10b981'],
                ['label' => 'Blocked Accounts','value' => 0,'icon' => 'bi-person-x',           'color' => '#6b7280'],
            ],
            'columns'  => ['Type', 'Severity', 'User', 'Trip', 'Signal', 'Detected', 'Status'],
            'filters'  => $this->baseFilters($request, ['status']),
            'notice'   => 'AI fraud detection is being configured. Risk alerts will appear here once the model is active.',
        ]);
    }

    public function aiPricing(Request $request)
    {
        return $this->stub([
            'title'    => 'Smart Pricing Suggestions',
            'icon'     => 'bi-lightning-charge',
            'subtitle' => 'AI surge pricing and demand-based recommendations',
            'kpis'     => [
                ['label' => 'Active Surges', 'value' => 0,    'icon' => 'bi-lightning-charge', 'color' => '#f59e0b'],
                ['label' => 'Suggestions',   'value' => 0,    'icon' => 'bi-lightbulb',        'color' => '#3b82f6'],
                ['label' => 'Applied',       'value' => 0,    'icon' => 'bi-check-circle',     'color' => '#10b981'],
                ['label' => 'Revenue Lift',  'value' => '—%', 'icon' => 'bi-graph-up-arrow',   'color' => '#8b5cf6'],
            ],
            'columns'  => ['Zone', 'Time', 'Suggested Multiplier', 'Demand Score', 'Applied', 'Revenue Impact'],
            'filters'  => $this->baseFilters($request, ['zone']),
            'notice'   => 'AI pricing engine is being configured.',
        ]);
    }

    public function aiSupply(Request $request)
    {
        return $this->stub([
            'title'    => 'Driver Supply Predictions',
            'icon'     => 'bi-diagram-3',
            'subtitle' => 'Forecast driver availability by zone and time',
            'kpis'     => [
                ['label' => 'Forecast Accuracy', 'value' => '—%', 'icon' => 'bi-graph-up',    'color' => '#10b981'],
                ['label' => 'Shortage Zones',    'value' => 0,    'icon' => 'bi-exclamation-triangle', 'color' => '#ef4444'],
                ['label' => 'Surplus Zones',     'value' => 0,    'icon' => 'bi-check-circle', 'color' => '#3b82f6'],
                ['label' => 'Predictions Today', 'value' => 0,    'icon' => 'bi-diagram-3',    'color' => '#8b5cf6'],
            ],
            'columns'  => ['Zone', 'Hour', 'Predicted Demand', 'Predicted Supply', 'Gap', 'Action'],
            'filters'  => $this->baseFilters($request, ['zone']),
            'notice'   => 'Supply prediction model is being trained.',
        ]);
    }

    public function aiPromo(Request $request)
    {
        return $this->stub([
            'title'    => 'Promo Optimization',
            'icon'     => 'bi-magic',
            'subtitle' => 'AI recommendations for promotions and offers',
            'kpis'     => [
                ['label' => 'Suggestions', 'value' => 0,    'icon' => 'bi-magic',            'color' => '#8b5cf6'],
                ['label' => 'Applied',     'value' => 0,    'icon' => 'bi-check-circle',     'color' => '#10b981'],
                ['label' => 'Revenue Lift','value' => '—%', 'icon' => 'bi-graph-up-arrow',   'color' => '#f59e0b'],
                ['label' => 'Rides Driven','value' => 0,    'icon' => 'bi-car-front',        'color' => '#3b82f6'],
            ],
            'columns'  => ['Offer', 'Target Segment', 'Suggested Value', 'Confidence', 'Expires', 'Status'],
            'filters'  => $this->baseFilters($request),
            'notice'   => 'Promo optimization AI is being configured.',
        ]);
    }

    public function aiAutoreplies(Request $request)
    {
        return $this->stub([
            'title'    => 'Auto Replies (Support)',
            'icon'     => 'bi-chat-dots',
            'subtitle' => 'AI-generated support auto-reply templates',
            'kpis'     => [
                ['label' => 'Templates',      'value' => 0,    'icon' => 'bi-chat-dots',    'color' => '#3b82f6'],
                ['label' => 'Used Today',     'value' => 0,    'icon' => 'bi-calendar-day', 'color' => '#10b981'],
                ['label' => 'Avg Resolution', 'value' => '—h', 'icon' => 'bi-clock',        'color' => '#f59e0b'],
                ['label' => 'CSAT',           'value' => '—%', 'icon' => 'bi-emoji-smile',  'color' => '#8b5cf6'],
            ],
            'columns'  => ['Trigger', 'Category', 'Response', 'Uses', 'CSAT', 'Active'],
            'filters'  => $this->baseFilters($request),
            'notice'   => 'Auto-reply AI module is being configured.',
        ]);
    }

    // ── System ────────────────────────────────────────────────────────────────

    public function apiKeys(Request $request)
    {
        return $this->stub([
            'title'    => 'API Keys',
            'icon'     => 'bi-key',
            'subtitle' => 'Manage external API credentials',
            'kpis'     => [
                ['label' => 'Total Keys',    'value' => 0,  'icon' => 'bi-key',          'color' => '#3b82f6'],
                ['label' => 'Active',        'value' => 0,  'icon' => 'bi-check-circle', 'color' => '#10b981'],
                ['label' => 'Expiring',      'value' => 0,  'icon' => 'bi-clock',        'color' => '#f59e0b'],
                ['label' => 'Revoked',       'value' => 0,  'icon' => 'bi-x-circle',     'color' => '#ef4444'],
            ],
            'columns'  => ['Service', 'Key (masked)', 'Created', 'Expires', 'Last Used', 'Status'],
            'filters'  => $this->baseFilters($request),
        ]);
    }

    public function backups(Request $request)
    {
        return $this->stub([
            'title'    => 'Backups / Exports',
            'icon'     => 'bi-cloud-arrow-down',
            'subtitle' => 'Database backups and data exports',
            'kpis'     => [
                ['label' => 'Last Backup',   'value' => '—',   'icon' => 'bi-cloud-arrow-down','color' => '#3b82f6'],
                ['label' => 'Backup Size',   'value' => '—MB', 'icon' => 'bi-database',        'color' => '#8b5cf6'],
                ['label' => 'Auto Backups',  'value' => 'Off', 'icon' => 'bi-arrow-repeat',    'color' => '#f59e0b'],
                ['label' => 'Exports Today', 'value' => 0,     'icon' => 'bi-download',        'color' => '#10b981'],
            ],
            'columns'  => ['Filename', 'Type', 'Size', 'Created', 'Expires', 'Actions'],
            'filters'  => $this->baseFilters($request),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function stub(array $cfg)
    {
        return view('adminmodule::admin-stub', [
            'title'       => $cfg['title'],
            'icon'        => $cfg['icon'],
            'subtitle'    => $cfg['subtitle'],
            'kpis'        => $cfg['kpis'],
            'columns'     => $cfg['columns'],
            'rows'        => $cfg['rows'] ?? collect(),
            'filters'     => $cfg['filters'] ?? [],
            'notice'      => $cfg['notice'] ?? null,
            'lastUpdated' => now(),
        ]);
    }

    private function baseFilters(Request $request, array $extras = []): array
    {
        $f = [
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
            'search' => $request->get('search'),
        ];
        if (in_array('status', $extras)) $f['status'] = $request->get('status');
        if (in_array('zone', $extras))   $f['zone_id'] = $request->get('zone_id');
        return $f;
    }

    private function zoneCount(): int
    {
        try {
            return \Modules\ZoneManagement\Entities\Zone::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeReviews(Request $request): array
    {
        try {
            $q = \Modules\ReviewModule\Entities\Review::query();
            $total = [
                'all'      => (clone $q)->count(),
                'avg'      => round((clone $q)->avg('rating') ?? 0, 1),
                'week'     => (clone $q)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'one_star' => (clone $q)->where('rating', 1)->count(),
            ];
            $rows = (clone $q)->with(['trip:id,ref_id'])->orderByDesc('created_at')->paginate(25)->withQueryString();
            return [$total, $rows];
        } catch (\Throwable $e) {
            return [['all' => 0, 'avg' => 0, 'week' => 0, 'one_star' => 0], collect()];
        }
    }

    private function safeDriverApps(Request $request): array
    {
        try {
            $q = \Modules\UserManagement\Entities\User::query()->where('user_type', 'driver');
            $counts = [
                'total'   => (clone $q)->count(),
                'pending' => (clone $q)->where('is_active', false)->count(),
                'today'   => (clone $q)->where('is_active', true)->whereDate('updated_at', today())->count(),
                'denied'  => 0,
            ];
            $rows = (clone $q)->select('id','first_name','last_name','phone','email','is_active','created_at')
                              ->orderByDesc('created_at')->paginate(25)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['total' => 0, 'pending' => 0, 'today' => 0, 'denied' => 0], collect()];
        }
    }

    private function safeDriverAvailability(Request $request): array
    {
        try {
            $q = \Modules\UserManagement\Entities\DriverDetail::query();
            $counts = [
                'online'     => (clone $q)->where('availability_status', 'available')->count(),
                'on_trip'    => (clone $q)->whereIn('availability_status', ['on_trip', 'on_bidding'])->count(),
                'offline'    => (clone $q)->where('availability_status', 'unavailable')->count(),
                'on_bidding' => (clone $q)->where('availability_status', 'on_bidding')->count(),
            ];
            $rows = (clone $q)->with(['user:id,first_name,last_name,phone'])
                              ->orderBy('availability_status')->paginate(30)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['online' => 0, 'on_trip' => 0, 'offline' => 0, 'on_bidding' => 0], collect()];
        }
    }

    private function safeCashCollect(Request $request): array
    {
        try {
            $q = \Modules\TripManagement\Entities\TripRequest::query()
                    ->where('payment_method', 'cash');
            $counts = [
                'total'   => (clone $q)->count(),
                'pending' => (clone $q)->whereIn('current_status', ['completed'])->where('payment_status', '!=', 'paid')->count(),
                'today'   => (clone $q)->whereDate('updated_at', today())->count(),
                'amount'  => (clone $q)->where('current_status', 'completed')->sum('actual_fare'),
            ];
            $rows = (clone $q)->with(['driver:id,first_name,last_name', 'customer:id,first_name,last_name'])
                              ->orderByDesc('created_at')->paginate(25)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['total' => 0, 'pending' => 0, 'today' => 0, 'amount' => 0], collect()];
        }
    }

    private function safeRefunds(Request $request): array
    {
        try {
            $model = class_exists('\\Modules\\TripManagement\\Entities\\ParcelRefund')
                   ? \Modules\TripManagement\Entities\ParcelRefund::query()
                   : null;

            if (!$model) {
                return [['total' => 0, 'pending' => 0, 'approved' => 0, 'amount' => 0], collect()];
            }

            $counts = [
                'total'    => (clone $model)->count(),
                'pending'  => (clone $model)->where('status', 'pending')->count(),
                'approved' => (clone $model)->where('status', 'approved')->count(),
                'amount'   => (clone $model)->where('status', 'approved')->sum('amount'),
            ];
            $rows = (clone $model)->orderByDesc('created_at')->paginate(25)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['total' => 0, 'pending' => 0, 'approved' => 0, 'amount' => 0], collect()];
        }
    }

    private function safeCommissions(Request $request): array
    {
        try {
            $q = \Modules\TripManagement\Entities\TripRequestFee::query();
            $counts = [
                'earned'    => (clone $q)->sum('admin_commission'),
                'today'     => (clone $q)->whereDate('created_at', today())->sum('admin_commission'),
                'week'      => (clone $q)->whereBetween('created_at', [now()->startOfWeek(), now()])->sum('admin_commission'),
                'avg_trip'  => (clone $q)->avg('admin_commission') ?? 0,
            ];
            $rows = (clone $q)->with(['tripRequest:id,ref_id,driver_id'])
                              ->orderByDesc('created_at')->paginate(25)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['earned' => 0, 'today' => 0, 'week' => 0, 'avg_trip' => 0], collect()];
        }
    }

    private function safeRevenue(Request $request): array
    {
        try {
            $q = \Modules\TripManagement\Entities\TripRequest::query()->where('current_status', 'completed');
            $counts = [
                'today'  => (clone $q)->whereDate('updated_at', today())->sum('actual_fare'),
                'week'   => (clone $q)->whereBetween('updated_at', [now()->startOfWeek(), now()])->sum('actual_fare'),
                'month'  => (clone $q)->whereMonth('updated_at', now()->month)->sum('actual_fare'),
                'total'  => (clone $q)->sum('actual_fare'),
            ];
            // Group by date for the table rows
            $rows = \Modules\TripManagement\Entities\TripRequest::query()
                        ->selectRaw('DATE(updated_at) as date, COUNT(*) as trips, SUM(actual_fare) as revenue, SUM(cancellation_fee) as fees')
                        ->where('current_status', 'completed')
                        ->groupByRaw('DATE(updated_at)')
                        ->orderByDesc('date')
                        ->paginate(30)->withQueryString();
            return [$counts, $rows];
        } catch (\Throwable $e) {
            return [['today' => 0, 'week' => 0, 'month' => 0, 'total' => 0], collect()];
        }
    }
}
