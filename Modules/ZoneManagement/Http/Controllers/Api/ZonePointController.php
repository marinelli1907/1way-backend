<?php

namespace Modules\ZoneManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\ZoneManagement\Entities\Zone;

/**
 * Part B — Point-in-Polygon API
 *
 * Endpoints:
 *   POST /api/v1/zone/point-in-zone         → {lat, lng} → zone for that point
 *   POST /api/v1/zone/trip-zones            → {pickup_lat, pickup_lng, dropoff_lat, dropoff_lng} → {pickup_zone, dropoff_zone}
 */
class ZonePointController extends Controller
{
    /**
     * POST /api/v1/zone/point-in-zone
     *
     * Body: { lat: float, lng: float }
     * Response: { zone: {...} } or { zone: null }
     */
    public function pointInZone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $zone = $this->findZoneForPoint((float) $data['lat'], (float) $data['lng']);

        return response()->json([
            'zone' => $zone ? [
                'id'                 => $zone->id,
                'name'               => $zone->name,
                'readable_id'        => $zone->readable_id,
                'pricing_multiplier' => $zone->pricing_multiplier,
                'extra_fare_status'  => $zone->extra_fare_status,
                'extra_fare_fee'     => $zone->extra_fare_fee,
            ] : null,
        ]);
    }

    /**
     * POST /api/v1/zone/trip-zones
     *
     * Body: { pickup_lat, pickup_lng, dropoff_lat, dropoff_lng }
     * Response: { pickup_zone: {...}|null, dropoff_zone: {...}|null }
     */
    public function tripZones(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pickup_lat'   => 'required|numeric|between:-90,90',
            'pickup_lng'   => 'required|numeric|between:-180,180',
            'dropoff_lat'  => 'required|numeric|between:-90,90',
            'dropoff_lng'  => 'required|numeric|between:-180,180',
        ]);

        $pickupZone  = $this->findZoneForPoint($data['pickup_lat'],  $data['pickup_lng']);
        $dropoffZone = $this->findZoneForPoint($data['dropoff_lat'], $data['dropoff_lng']);

        $toArray = fn($z) => $z ? [
            'id'                 => $z->id,
            'name'               => $z->name,
            'readable_id'        => $z->readable_id,
            'pricing_multiplier' => $z->pricing_multiplier,
            'extra_fare_status'  => $z->extra_fare_status,
            'extra_fare_fee'     => $z->extra_fare_fee,
        ] : null;

        return response()->json([
            'pickup_zone'  => $toArray($pickupZone),
            'dropoff_zone' => $toArray($dropoffZone),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Find the first active zone whose polygon contains the given lat/lng.
     *
     * Uses MySQL/PostGIS ST_Contains if the spatial extension is available.
     * Falls back to pure-PHP point-in-polygon (Ray Casting) if not.
     */
    private function findZoneForPoint(float $lat, float $lng): ?Zone
    {
        // Try DB spatial first (fast)
        try {
            $zone = Zone::ofStatus(1)
                ->whereRaw(
                    'ST_Contains(coordinates, ST_GeomFromText(?))',
                    ["POINT($lng $lat)"]   // WKT: longitude first, then latitude
                )
                ->first();

            if ($zone) {
                return $zone;
            }
        } catch (\Throwable) {
            // Spatial extension not available — fall through to PHP fallback
        }

        // PHP fallback — Ray-Casting algorithm
        $zones = Zone::ofStatus(1)->get();
        foreach ($zones as $zone) {
            if ($this->phpPointInPolygon($lat, $lng, $zone)) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Pure-PHP Ray-Casting point-in-polygon check.
     * Works on the Zone's Polygon spatial object.
     */
    private function phpPointInPolygon(float $lat, float $lng, Zone $zone): bool
    {
        try {
            // Decode the spatial polygon to an array of [lat, lng] pairs
            $geoJson  = json_decode($zone->coordinates->toJson(), true);
            // GeoJSON Polygon coordinates[0] = outer ring, each element = [lng, lat]
            $ring     = $geoJson['coordinates'][0] ?? [];

            $inside   = false;
            $n        = count($ring);

            for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
                $xi = $ring[$i][0]; // lng
                $yi = $ring[$i][1]; // lat
                $xj = $ring[$j][0];
                $yj = $ring[$j][1];

                // Check if horizontal ray from point crosses this edge
                if ((($yi > $lat) !== ($yj > $lat))
                    && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                    $inside = ! $inside;
                }
            }

            return $inside;
        } catch (\Throwable) {
            return false;
        }
    }
}
