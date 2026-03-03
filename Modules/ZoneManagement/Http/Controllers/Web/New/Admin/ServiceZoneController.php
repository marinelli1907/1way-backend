<?php

namespace Modules\ZoneManagement\Http\Controllers\Web\New\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\ServiceZone;
use Modules\ZoneManagement\Entities\ServiceZoneComponent;
use Modules\ZoneManagement\Entities\ServiceZoneExclusion;
use Modules\ZoneManagement\Entities\ServiceZoneInclusion;
use Modules\ZoneManagement\Service\GeoZoneService;

class ServiceZoneController extends Controller
{
    use AuthorizesRequests;
    protected GeoZoneService $geo;

    public function __construct(GeoZoneService $geo)
    {
        $this->geo = $geo;
    }

    // ─── Index ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('service_zone_view');
        $query = ServiceZone::withCount(['components', 'zoneExclusions', 'zoneInclusions']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $zones = $query->orderByDesc('priority')->orderBy('name')->paginate(15);
        $enforced = $this->geo->isEnforcementEnabled();

        return view('zonemanagement::admin.service-zone.index', compact('zones', 'enforced'));
    }

    // ─── Create ──────────────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('service_zone_add');
        $mapKey = $this->getMapKey();
        return view('zonemanagement::admin.service-zone.create', compact('mapKey'));
    }

    // ─── Store ───────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $this->authorize('service_zone_add');
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'country_code'  => 'required|string|size:2',
            'state_code'    => 'nullable|string|max:5',
            'priority'      => 'nullable|integer|min:0',
            'is_active'     => 'nullable',
            'components'    => 'required|array|min:1',
            'components.*.label'          => 'required|string',
            'components.*.component_type' => 'required|in:city,county,zip,custom,import,state',
            'components.*.geometry'       => 'required|array',
            'exclusions'    => 'nullable|array',
            'exclusions.*.label'    => 'required|string',
            'exclusions.*.geometry' => 'required|array',
            'inclusions'    => 'nullable|array',
            'inclusions.*.label'    => 'required|string',
            'inclusions.*.geometry' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return $this->saveZone($request);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('ServiceZone store failed', [
                'zone_name'  => $request->name,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile() . ':' . $e->getLine(),
                'components' => count($request->components ?? []),
                'exclusions' => count($request->exclusions ?? []),
                'inclusions' => count($request->inclusions ?? []),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save zone: ' . $this->safeErrorMessage($e),
            ], 500);
        }
    }

    // ─── Edit ────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $this->authorize('service_zone_edit');
        $zone = ServiceZone::with(['components', 'zoneExclusions', 'zoneInclusions', 'drivers'])->findOrFail($id);
        $mapKey = $this->getMapKey();

        // Pre-serialize for blade — arrow fns inside @json() break Blade's bracket parser
        $zoneComponents = $zone->components->map(function ($c) {
            return ['label' => $c->label, 'component_type' => $c->component_type, 'source' => $c->source, 'geometry' => $c->geometry];
        })->values();
        $zoneExclusions = $zone->zoneExclusions->map(function ($e) {
            return ['label' => $e->label, 'component_type' => 'custom', 'source' => 'manual', 'geometry' => $e->geometry];
        })->values();
        $zoneInclusions = $zone->zoneInclusions->map(function ($i) {
            return ['label' => $i->label, 'component_type' => 'custom', 'source' => 'manual', 'geometry' => $i->geometry];
        })->values();

        $assignedDrivers = $zone->drivers->map(function ($d) {
            return ['id' => $d->id, 'name' => $d->first_name . ' ' . $d->last_name, 'phone' => $d->phone, 'email' => $d->email];
        })->values();

        return view('zonemanagement::admin.service-zone.edit', compact(
            'zone', 'mapKey', 'zoneComponents', 'zoneExclusions', 'zoneInclusions', 'assignedDrivers'
        ));
    }

    // ─── Update ──────────────────────────────────────────────────────────

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorize('service_zone_edit');
        $zone = ServiceZone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'country_code'  => 'required|string|size:2',
            'state_code'    => 'nullable|string|max:5',
            'priority'      => 'nullable|integer|min:0',
            'is_active'     => 'nullable',
            'components'    => 'required|array|min:1',
            'components.*.label'          => 'required|string',
            'components.*.component_type' => 'required|in:city,county,zip,custom,import,state',
            'components.*.geometry'       => 'required|array',
            'exclusions'    => 'nullable|array',
            'exclusions.*.label'    => 'required|string',
            'exclusions.*.geometry' => 'required|array',
            'inclusions'    => 'nullable|array',
            'inclusions.*.label'    => 'required|string',
            'inclusions.*.geometry' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return $this->saveZone($request, $zone);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('ServiceZone update failed', [
                'zone_id'    => $id,
                'zone_name'  => $request->name,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile() . ':' . $e->getLine(),
                'components' => count($request->components ?? []),
                'exclusions' => count($request->exclusions ?? []),
                'inclusions' => count($request->inclusions ?? []),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update zone: ' . $this->safeErrorMessage($e),
            ], 500);
        }
    }

    // ─── Core save logic (shared by store + update) ──────────────────────

    protected function saveZone(Request $request, ?ServiceZone $existing = null): JsonResponse
    {
        return DB::transaction(function () use ($request, $existing) {
            if ($existing) {
                $zone = $existing;
                $zone->update([
                    'name'         => $request->name,
                    'country_code' => strtoupper($request->country_code),
                    'state_code'   => $request->state_code ? strtoupper($request->state_code) : null,
                    'is_active'    => filter_var($request->is_active ?? true, FILTER_VALIDATE_BOOLEAN),
                    'priority'     => (int) ($request->priority ?? 0),
                ]);
                $zone->components()->delete();
                $zone->zoneExclusions()->delete();
                $zone->zoneInclusions()->delete();
            } else {
                $zone = ServiceZone::create([
                    'name'                => $request->name,
                    'country_code'        => strtoupper($request->country_code),
                    'state_code'          => $request->state_code ? strtoupper($request->state_code) : null,
                    'source'              => 'manual',
                    'is_active'           => filter_var($request->is_active ?? true, FILTER_VALIDATE_BOOLEAN),
                    'priority'            => (int) ($request->priority ?? 0),
                    'boundary'            => null,
                    'exclusions'          => null,
                    'inclusions_override' => null,
                ]);
            }

            Log::info('ServiceZone save: processing components', [
                'zone_name'       => $request->name,
                'components'      => count($request->components),
                'exclusions'      => count($request->exclusions ?? []),
                'inclusions'      => count($request->inclusions ?? []),
                'geometry_types'  => collect($request->components)->pluck('geometry.type')->unique()->values()->all(),
            ]);

            $errors = [];

            foreach ($request->components as $i => $comp) {
                $geoType = $comp['geometry']['type'] ?? 'unknown';
                try {
                    $geometry = $this->geo->normalizeGeoJsonToMultiPolygon($comp['geometry']);
                    $this->geo->assertSizeLimit($geometry, "Component \"{$comp['label']}\"");
                } catch (\InvalidArgumentException $e) {
                    $errors[] = "Boundary component \"{$comp['label']}\" ({$geoType}): {$e->getMessage()}";
                    continue;
                }
                ServiceZoneComponent::create([
                    'service_zone_id' => $zone->id,
                    'component_type'  => $comp['component_type'],
                    'label'           => $comp['label'],
                    'source'          => $comp['source'] ?? 'nominatim',
                    'geometry'        => $geometry,
                ]);
            }

            foreach ($request->exclusions ?? [] as $i => $ex) {
                $geoType = $ex['geometry']['type'] ?? 'unknown';
                try {
                    $geometry = $this->geo->normalizeGeoJsonToMultiPolygon($ex['geometry']);
                    $this->geo->assertSizeLimit($geometry, "Exclusion \"{$ex['label']}\"");
                } catch (\InvalidArgumentException $e) {
                    $errors[] = "Exclusion \"{$ex['label']}\" ({$geoType}): {$e->getMessage()}";
                    continue;
                }
                ServiceZoneExclusion::create([
                    'service_zone_id' => $zone->id,
                    'label'           => $ex['label'],
                    'geometry'        => $geometry,
                ]);
            }

            foreach ($request->inclusions ?? [] as $i => $inc) {
                $geoType = $inc['geometry']['type'] ?? 'unknown';
                try {
                    $geometry = $this->geo->normalizeGeoJsonToMultiPolygon($inc['geometry']);
                    $this->geo->assertSizeLimit($geometry, "Override \"{$inc['label']}\"");
                } catch (\InvalidArgumentException $e) {
                    $errors[] = "Override \"{$inc['label']}\" ({$geoType}): {$e->getMessage()}";
                    continue;
                }
                ServiceZoneInclusion::create([
                    'service_zone_id' => $zone->id,
                    'label'           => $inc['label'],
                    'geometry'        => $geometry,
                ]);
            }

            // Check we got at least one valid boundary component
            $zone->load(['components', 'zoneExclusions', 'zoneInclusions']);

            if ($zone->components->isEmpty()) {
                throw new \InvalidArgumentException(
                    'No valid boundary components could be saved. '
                    . (!empty($errors) ? implode(' | ', $errors) : 'All geometries were invalid.')
                );
            }

            $zone->recomputeBoundary();
            $zone->recomputeExclusions();
            $zone->recomputeInclusions();

            $this->geo->assertSizeLimit($zone->boundary ?? [], 'Combined zone boundary');

            $zone->save();

            $response = [
                'success'  => true,
                'zone_id'  => $zone->id,
                'redirect' => route('admin.service-zone.index'),
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);
        });
    }

    // ─── Delete ──────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $this->authorize('service_zone_delete');
        ServiceZone::findOrFail($id)->delete();
        return redirect()->route('admin.service-zone.index')->with('success', 'Service zone deleted.');
    }

    // ─── Toggle Status ───────────────────────────────────────────────────

    public function toggleStatus(Request $request)
    {
        $zone = ServiceZone::findOrFail($request->id);
        $zone->update(['is_active' => !$zone->is_active]);
        return back()->with('success', 'Status updated.');
    }

    // ─── Zone Pricing ─────────────────────────────────────────────────────

    public function pricingEdit(string $id)
    {
        $this->authorize('service_zone_edit');
        $zone = ServiceZone::findOrFail($id);
        $rules = $zone->effectivePricingRules();
        $defaults = ServiceZone::DEFAULT_PRICING_RULES;
        return view('zonemanagement::admin.service-zone.pricing', compact('zone', 'rules', 'defaults'));
    }

    public function pricingUpdate(Request $request, string $id): JsonResponse
    {
        $this->authorize('service_zone_edit');
        $zone = ServiceZone::findOrFail($id);

        $validator = Validator::make($request->all(), ServiceZone::PRICING_RULES_VALIDATION);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Only store values that differ from defaults
        $incoming = $request->only(array_keys(ServiceZone::DEFAULT_PRICING_RULES));
        $rules = [];
        foreach ($incoming as $key => $val) {
            if ($val !== null && $val !== '') {
                $rules[$key] = is_numeric($val) ? (str_contains((string) $val, '.') ? (float) $val : (int) $val) : $val;
            }
        }

        // Cross-field: max_fare_cents >= min_fare_cents
        $effective = array_merge(ServiceZone::DEFAULT_PRICING_RULES, $rules);
        if ($effective['max_fare_cents'] < $effective['min_fare_cents']) {
            return response()->json([
                'success' => false,
                'message' => 'Max fare must be >= min fare.',
            ], 422);
        }

        $zone->update(['pricing_rules' => $rules]);

        Log::info('Zone pricing updated', [
            'zone_id'   => $zone->id,
            'zone_name' => $zone->name,
            'rules'     => $rules,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Zone pricing updated.',
            'redirect' => route('admin.service-zone.index'),
        ]);
    }

    // ─── Boundary Lookup (AJAX) ──────────────────────────────────────────

    public function lookupBoundary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'     => 'required|string|min:2|max:100',
            'type'  => 'nullable|in:city,county,zip,state',
            'state' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->geo->lookupBoundary(
                $request->q,
                $request->type ?? 'city',
                $request->state
            );
        } catch (\Throwable $e) {
            Log::warning('Boundary lookup failed', ['q' => $request->q, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Boundary lookup failed: ' . $e->getMessage(),
            ], 500);
        }

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No boundary found for "' . $request->q . '". Try a more specific search or different type.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    // ─── Test Contains (AJAX) ────────────────────────────────────────────

    public function testContains(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $zone = $this->geo->findZoneForPoint((float) $request->lat, (float) $request->lng);

        return response()->json([
            'success'   => true,
            'inside'    => (bool) $zone,
            'zone_id'   => $zone?->id,
            'zone_name' => $zone?->name,
        ]);
    }

    // ─── File import (advanced option) ───────────────────────────────────

    public function importBoundary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file'  => 'required|file|mimes:json,geojson,txt',
            'layer' => 'required|in:boundary,exclusions,inclusions',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $raw = json_decode(file_get_contents($request->file('file')->getRealPath()), true);
        if (!$raw) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON.'], 422);
        }

        try {
            $multiPolygon = $this->geo->normalizeGeoJsonToMultiPolygon($raw);
            $this->geo->assertSizeLimit($multiPolygon);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success'       => true,
            'geojson'       => $multiPolygon,
            'polygon_count' => count($multiPolygon['coordinates']),
        ]);
    }

    // ─── Driver Assignment ────────────────────────────────────────────────

    public function searchDrivers(Request $request, string $id): JsonResponse
    {
        $this->authorize('service_zone_edit');
        ServiceZone::findOrFail($id);

        $q = $request->get('q', '');
        $drivers = User::where('user_type', DRIVER)
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'first_name', 'last_name', 'phone', 'email')
            ->limit(20)
            ->get()
            ->map(function ($d) {
                return [
                    'id'    => $d->id,
                    'name'  => trim($d->first_name . ' ' . $d->last_name),
                    'phone' => $d->phone,
                    'email' => $d->email,
                ];
            });

        return response()->json(['success' => true, 'drivers' => $drivers]);
    }

    public function syncDrivers(Request $request, string $id): JsonResponse
    {
        $this->authorize('service_zone_edit');
        $zone = ServiceZone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'driver_ids'   => 'present|array',
            'driver_ids.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $driverIds = $request->input('driver_ids', []);

        if (!empty($driverIds)) {
            $validCount = User::where('user_type', DRIVER)->whereIn('id', $driverIds)->count();
            if ($validCount !== count($driverIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more IDs are not valid drivers.',
                ], 422);
            }
        }

        $zone->drivers()->sync($driverIds);

        Log::info('Zone drivers updated', [
            'zone_id'      => $zone->id,
            'driver_count' => count($driverIds),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Drivers updated (' . count($driverIds) . ' assigned).',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    protected function getMapKey(): ?string
    {
        if ($key = env('GOOGLE_MAPS_API_KEY')) {
            return $key;
        }

        try {
            $config = businessConfig('google_map_api');
            return $config?->value['map_api_key'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function safeErrorMessage(\Throwable $e): string
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'SQLSTATE')) {
            if (str_contains($msg, 'cannot be null')) {
                return 'Database rejected a NULL value. This is a bug — please report it.';
            }
            return 'Database error. Check server logs for details.';
        }
        return $msg;
    }
}
