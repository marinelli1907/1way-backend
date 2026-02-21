<?php

namespace Modules\AdminModule\Http\Controllers\Web\New\Admin;

use App\Models\AiLog;
use App\Models\AiSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Part E — AI Admin Section
 *
 * Routes:
 *   GET  /admin/ai                       → settings page
 *   POST /admin/ai/settings              → save settings
 *   GET  /admin/ai/logs                  → logs list
 *   GET  /admin/ai/tools                 → tools page
 *   POST /admin/ai/tools/suggest-zones   → zone boundary suggestion tool
 *   POST /admin/ai/tools/suggest-pricing → pricing suggestion tool
 */
class AiController extends Controller
{
    use AuthorizesRequests;

    // ── Settings ─────────────────────────────────────────────────────────────

    public function settings(): View
    {
        $this->authorize('super-admin');

        $features = [
            AiSetting::FEATURE_PRICING_SUGGESTIONS,
            AiSetting::FEATURE_DEMAND_HEATMAP,
            AiSetting::FEATURE_FRAUD_FLAGGING,
            AiSetting::FEATURE_ETA_PREDICTOR,
        ];

        $toggles = collect($features)->mapWithKeys(
            fn($f) => [$f => AiSetting::isEnabled($f)]
        );

        // Never return actual API keys to the view — only show masked placeholder
        $hasOpenAiKey      = (bool) AiSetting::get(AiSetting::KEY_OPENAI_API_KEY);
        $hasAnthropicKey   = (bool) AiSetting::get(AiSetting::KEY_ANTHROPIC_API_KEY);

        return view('adminmodule::admin.ai.settings', compact(
            'toggles', 'hasOpenAiKey', 'hasAnthropicKey'
        ));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $this->authorize('super-admin');

        // Save feature toggles
        $features = [
            AiSetting::FEATURE_PRICING_SUGGESTIONS,
            AiSetting::FEATURE_DEMAND_HEATMAP,
            AiSetting::FEATURE_FRAUD_FLAGGING,
            AiSetting::FEATURE_ETA_PREDICTOR,
        ];
        foreach ($features as $f) {
            AiSetting::set($f, $request->has($f) ? '1' : '0');
        }

        // Save API keys only if provided (non-empty), encrypted
        if ($request->filled('openai_api_key')) {
            $request->validate(['openai_api_key' => 'string|min:20|max:200']);
            AiSetting::set(AiSetting::KEY_OPENAI_API_KEY, $request->input('openai_api_key'), true);
        }
        if ($request->filled('anthropic_api_key')) {
            $request->validate(['anthropic_api_key' => 'string|min:20|max:200']);
            AiSetting::set(AiSetting::KEY_ANTHROPIC_API_KEY, $request->input('anthropic_api_key'), true);
        }

        Toastr::success(translate('AI settings saved.'));
        return back();
    }

    // ── Logs ─────────────────────────────────────────────────────────────────

    public function logs(Request $request): View
    {
        $this->authorize('super-admin');

        $logs = AiLog::query()
            ->when($request->tool,   fn($q, $t) => $q->where('tool', $t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $tools    = AiLog::distinct()->pluck('tool');
        $statuses = [AiLog::STATUS_SUCCESS, AiLog::STATUS_FAILED, AiLog::STATUS_PENDING, AiLog::STATUS_RUNNING];

        return view('adminmodule::admin.ai.logs', compact('logs', 'tools', 'statuses'));
    }

    // ── Tools ─────────────────────────────────────────────────────────────────

    /** GET /admin/ai/tools */
    public function toolsView(): View
    {
        $this->authorize('super-admin');
        return view('adminmodule::admin.ai.tools');
    }

    /**
     * POST /admin/ai/tools/suggest-zones
     * Accepts CSV of lat,lng ride coordinates and returns suggested polygons.
     * This is an MVP placeholder using a clustering heuristic.
     */
    public function suggestZones(Request $request): JsonResponse
    {
        $this->authorize('super-admin');

        if (! AiSetting::isEnabled(AiSetting::FEATURE_PRICING_SUGGESTIONS)) {
            return response()->json(['error' => translate('AI zone suggestions are disabled.')], 403);
        }

        $request->validate(['coordinates_csv' => 'required|string|max:100000']);

        $startMs = now()->getTimestampMs();
        $log = AiLog::create([
            'tool'         => 'suggest_zone_boundaries',
            'status'       => AiLog::STATUS_RUNNING,
            'input'        => ['rows' => substr_count($request->coordinates_csv, "\n") + 1],
            'triggered_by' => auth()->id(),
        ]);

        try {
            $points = $this->parseCsvCoordinates($request->coordinates_csv);

            if (count($points) < 3) {
                throw new \InvalidArgumentException('Need at least 3 coordinate points.');
            }

            // MVP heuristic: bounding box of all points → one suggested zone
            $lats = array_column($points, 0);
            $lngs = array_column($points, 1);

            $minLat = min($lats); $maxLat = max($lats);
            $minLng = min($lngs); $maxLng = max($lngs);

            // Pad 5% to give breathing room
            $padLat = ($maxLat - $minLat) * 0.05;
            $padLng = ($maxLng - $minLng) * 0.05;

            $suggested = [
                'type'     => 'Feature',
                'properties' => ['name' => 'Suggested Zone', 'note' => 'Bounding box of input rides'],
                'geometry' => [
                    'type'        => 'Polygon',
                    'coordinates' => [[
                        [$minLng - $padLng, $minLat - $padLat],
                        [$maxLng + $padLng, $minLat - $padLat],
                        [$maxLng + $padLng, $maxLat + $padLat],
                        [$minLng - $padLng, $maxLat + $padLat],
                        [$minLng - $padLng, $minLat - $padLat],
                    ]],
                ],
            ];

            $log->update([
                'status'      => AiLog::STATUS_SUCCESS,
                'output'      => $suggested,
                'duration_ms' => now()->getTimestampMs() - $startMs,
            ]);

            return response()->json(['geojson' => $suggested, 'points_analysed' => count($points)]);

        } catch (\Throwable $e) {
            $log->update([
                'status'      => AiLog::STATUS_FAILED,
                'error'       => $e->getMessage(),
                'duration_ms' => now()->getTimestampMs() - $startMs,
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/ai/tools/suggest-pricing
     * MVP heuristic pricing suggestion based on zone stats.
     */
    public function suggestPricing(Request $request): JsonResponse
    {
        $this->authorize('super-admin');

        if (! AiSetting::isEnabled(AiSetting::FEATURE_PRICING_SUGGESTIONS)) {
            return response()->json(['error' => translate('AI pricing suggestions are disabled.')], 403);
        }

        $request->validate(['zone_id' => 'required|exists:zones,id']);

        $startMs = now()->getTimestampMs();
        $log = AiLog::create([
            'tool'         => 'suggest_pricing',
            'status'       => AiLog::STATUS_RUNNING,
            'input'        => ['zone_id' => $request->zone_id],
            'triggered_by' => auth()->id(),
        ]);

        try {
            $zone = Zone::withCount('tripRequest')->findOrFail($request->zone_id);

            // MVP heuristic: high-demand zones get a modest multiplier
            $tripCount   = $zone->trip_request_count;
            $multiplier  = match (true) {
                $tripCount > 1000 => 1.25,
                $tripCount > 500  => 1.15,
                $tripCount > 100  => 1.05,
                default           => 1.00,
            };

            $suggestion = [
                'zone_id'             => $zone->id,
                'zone_name'           => $zone->name,
                'trip_count'          => $tripCount,
                'suggested_multiplier'=> $multiplier,
                'reason'              => "Based on $tripCount historical trips in this zone.",
                'confidence'          => 'low',
                'note'                => 'This is a basic heuristic. Connect an AI service for better accuracy.',
            ];

            $log->update([
                'status'      => AiLog::STATUS_SUCCESS,
                'output'      => $suggestion,
                'duration_ms' => now()->getTimestampMs() - $startMs,
            ]);

            return response()->json($suggestion);

        } catch (\Throwable $e) {
            $log->update(['status' => AiLog::STATUS_FAILED, 'error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function parseCsvCoordinates(string $csv): array
    {
        $lines  = preg_split('/[\r\n]+/', trim($csv));
        $points = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) continue;

            $parts = preg_split('/[\s,;]+/', $line);
            if (count($parts) >= 2) {
                $lat = (float) $parts[0];
                $lng = (float) $parts[1];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    $points[] = [$lat, $lng];
                }
            }
        }

        return $points;
    }
}
