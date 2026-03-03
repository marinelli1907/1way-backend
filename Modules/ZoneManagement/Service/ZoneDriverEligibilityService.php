<?php

namespace Modules\ZoneManagement\Service;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\ZoneManagement\Entities\ServiceZone;

class ZoneDriverEligibilityService
{
    protected GeoZoneService $geoService;

    public function __construct(GeoZoneService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Resolve the Service Zone for a pickup point (or null if no zone matches).
     */
    public function getZoneFromPickup(float $lat, float $lng): ?ServiceZone
    {
        return $this->geoService->findZoneForPoint($lat, $lng);
    }

    /**
     * Get eligible driver user IDs for a pickup point.
     *
     * Returns [zone, driverIds]:
     *   - zone is the matched ServiceZone (or null if none)
     *   - driverIds is an array of UUIDs to restrict to (or null if no restriction)
     *
     * Throws HttpResponseException(409) when a zone has zero assigned drivers.
     * Throws HttpResponseException(422) when zones are enforced and pickup is outside all zones.
     */
    public function resolveEligibility(float $lat, float $lng, ?string $endpointPath = null): array
    {
        $zone = $this->getZoneFromPickup($lat, $lng);

        if (!$zone && $this->geoService->isEnforcementEnabled()) {
            throw new HttpResponseException(response()->json([
                'message'       => 'Outside service area. We do not currently serve this pickup location.',
                'response_code' => 'outside_service_area',
            ], 422));
        }

        if (!$zone) {
            return ['zone' => null, 'driver_ids' => null];
        }

        $assigned = $zone->drivers()
            ->wherePivot('is_active', true)
            ->where('users.is_active', true)
            ->where('users.user_type', DRIVER)
            ->pluck('users.id')
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();

        if (empty($assigned)) {
            Log::info('[ZoneDriverEligibility] blocked — no drivers', [
                'zone_id'   => $zone->id,
                'zone_name' => $zone->name,
                'pickup'    => round($lat, 2) . ',' . round($lng, 2),
                'endpoint'  => $endpointPath ?? request()->path(),
            ]);

            throw new HttpResponseException(response()->json([
                'message'       => 'No drivers available in this zone right now.',
                'response_code' => 'no_drivers_in_zone',
                'zone_id'       => $zone->id,
                'zone_name'     => $zone->name,
            ], 409));
        }

        return ['zone' => $zone, 'driver_ids' => $assigned];
    }

    /**
     * Convenience wrapper — resolves zone and returns driver IDs,
     * or null when no zone restriction applies.
     */
    public function getEligibleDriverIds(float $lat, float $lng): ?array
    {
        $result = $this->resolveEligibility($lat, $lng);
        return $result['driver_ids'];
    }
}
