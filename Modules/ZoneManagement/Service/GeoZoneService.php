<?php

namespace Modules\ZoneManagement\Service;

use Illuminate\Support\Facades\Cache;
use Modules\ZoneManagement\Entities\ServiceZone;

class GeoZoneService
{
    const MAX_BOUNDARY_BYTES = 8 * 1024 * 1024; // 8 MB

    // ─── Normalization ───────────────────────────────────────────────────

    /**
     * Normalize any supported GeoJSON to a MultiPolygon.
     *
     * Supported inputs:
     *   - Polygon
     *   - MultiPolygon
     *   - Feature wrapping a Polygon/MultiPolygon/GeometryCollection
     *   - FeatureCollection (merges all polygon geometries)
     *   - GeometryCollection (extracts Polygon/MultiPolygon children)
     *
     * Throws InvalidArgumentException if no polygon data can be extracted.
     */
    public function normalizeGeoJsonToMultiPolygon(array $geojson): array
    {
        $coords = $this->collectPolygonCoords($geojson);

        if (empty($coords)) {
            $type = $geojson['type'] ?? 'unknown';
            throw new \InvalidArgumentException(
                "Boundary must contain at least one Polygon or MultiPolygon. Got: {$type} with no usable polygon data."
            );
        }

        return [
            'type'        => 'MultiPolygon',
            'coordinates' => $coords,
        ];
    }

    /**
     * Alias kept for backward compatibility with file-import code.
     */
    public function normalizeFeatureCollectionToMultiPolygon(array $geojson): array
    {
        return $this->normalizeGeoJsonToMultiPolygon($geojson);
    }

    /**
     * Recursively collect all Polygon coordinate arrays from any GeoJSON structure.
     * Returns an array of polygon coordinate sets suitable for MultiPolygon.coordinates.
     */
    protected function collectPolygonCoords(array $geojson): array
    {
        $type = $geojson['type'] ?? null;
        $coords = [];

        switch ($type) {
            case 'MultiPolygon':
                foreach ($geojson['coordinates'] ?? [] as $poly) {
                    $coords[] = $poly;
                }
                break;

            case 'Polygon':
                if (!empty($geojson['coordinates'])) {
                    $coords[] = $geojson['coordinates'];
                }
                break;

            case 'GeometryCollection':
                foreach ($geojson['geometries'] ?? [] as $child) {
                    $coords = array_merge($coords, $this->collectPolygonCoords($child));
                }
                break;

            case 'Feature':
                $geometry = $geojson['geometry'] ?? null;
                if (is_array($geometry)) {
                    $coords = $this->collectPolygonCoords($geometry);
                }
                break;

            case 'FeatureCollection':
                foreach ($geojson['features'] ?? [] as $feature) {
                    $coords = array_merge($coords, $this->collectPolygonCoords($feature));
                }
                break;

            // Point, LineString, MultiPoint, MultiLineString — silently skip
            default:
                break;
        }

        return $coords;
    }

    // ─── Size guard ──────────────────────────────────────────────────────

    /**
     * Check that a GeoJSON structure doesn't exceed the size limit when encoded.
     * Throws InvalidArgumentException if too large.
     */
    public function assertSizeLimit(array $geojson, string $label = 'Boundary'): void
    {
        $size = strlen(json_encode($geojson));
        if ($size > self::MAX_BOUNDARY_BYTES) {
            $mb = round($size / 1024 / 1024, 1);
            throw new \InvalidArgumentException(
                "{$label} too large ({$mb} MB). Use smaller components or import simplified GeoJSON. Max: 8 MB."
            );
        }
    }

    // ─── Union (concat all component polygons) ───────────────────────────

    public function unionMultiPolygons(array $multiPolygons): array
    {
        $allCoords = [];
        foreach ($multiPolygons as $mp) {
            if (($mp['type'] ?? '') === 'MultiPolygon') {
                foreach ($mp['coordinates'] as $poly) {
                    $allCoords[] = $poly;
                }
            }
        }

        return [
            'type'        => 'MultiPolygon',
            'coordinates' => $allCoords,
        ];
    }

    // ─── Point-in-polygon ────────────────────────────────────────────────

    public function pointInRing(float $lat, float $lng, array $ring): bool
    {
        $n = count($ring);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][1];
            $yi = $ring[$i][0];
            $xj = $ring[$j][1];
            $yj = $ring[$j][0];

            if (($yi > $lng) !== ($yj > $lng)
                && $lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi
            ) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function pointInPolygon(float $lat, float $lng, array $polygonCoords): bool
    {
        if (empty($polygonCoords)) {
            return false;
        }

        if (!$this->pointInRing($lat, $lng, $polygonCoords[0])) {
            return false;
        }

        for ($h = 1; $h < count($polygonCoords); $h++) {
            if ($this->pointInRing($lat, $lng, $polygonCoords[$h])) {
                return false;
            }
        }

        return true;
    }

    public function pointInMultiPolygon(float $lat, float $lng, array $multiPolygon): bool
    {
        foreach ($multiPolygon['coordinates'] ?? [] as $polygonCoords) {
            if ($this->pointInPolygon($lat, $lng, $polygonCoords)) {
                return true;
            }
        }

        return false;
    }

    // ─── 3-layer containment ─────────────────────────────────────────────

    /**
     * inside boundary AND (NOT in exclusions OR in inclusions_override)
     */
    public function contains(float $lat, float $lng, ServiceZone $zone): bool
    {
        $boundary = $zone->boundary;
        if (!$boundary || empty($boundary['coordinates'] ?? [])) {
            return false;
        }

        if (!$this->pointInMultiPolygon($lat, $lng, $boundary)) {
            return false;
        }

        $exclusions = $zone->exclusions;
        if ($exclusions && !empty($exclusions['coordinates'] ?? [])) {
            if ($this->pointInMultiPolygon($lat, $lng, $exclusions)) {
                $inclusions = $zone->inclusions_override;
                if ($inclusions && !empty($inclusions['coordinates'] ?? [])) {
                    if ($this->pointInMultiPolygon($lat, $lng, $inclusions)) {
                        return true;
                    }
                }
                return false;
            }
        }

        return true;
    }

    public function findZoneForPoint(float $lat, float $lng): ?ServiceZone
    {
        $zones = ServiceZone::active()->byPriority()->get();

        foreach ($zones as $zone) {
            if ($this->contains($lat, $lng, $zone)) {
                return $zone;
            }
        }

        return null;
    }

    public function isEnforcementEnabled(): bool
    {
        return filter_var(env('ZONES_ENFORCED', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Return drivers assigned to a zone (hard restriction — no fallback).
     * Returns empty collection when zone has no assigned drivers.
     */
    public function getEligibleDriversForZone(string $zoneId): \Illuminate\Support\Collection
    {
        $zone = ServiceZone::with('drivers')->find($zoneId);
        if (!$zone) {
            return collect();
        }

        return $zone->drivers()
            ->wherePivot('is_active', true)
            ->where('users.is_active', true)
            ->where('users.user_type', DRIVER)
            ->get();
    }

    // ─── Validation ──────────────────────────────────────────────────────

    public function validateGeoJson(array $geojson, string $label = 'boundary'): array
    {
        $errors = [];

        try {
            $mp = $this->normalizeGeoJsonToMultiPolygon($geojson);
        } catch (\InvalidArgumentException $e) {
            return ['valid' => false, 'errors' => [$e->getMessage()], 'warnings' => []];
        }

        if (empty($mp['coordinates'])) {
            $errors[] = "{$label} has no polygon coordinates.";
        }

        return ['valid' => empty($errors), 'errors' => $errors, 'warnings' => []];
    }

    // ─── Boundary lookup via Nominatim ───────────────────────────────────

    public function lookupBoundary(string $query, string $type = 'city', ?string $state = null): ?array
    {
        $cacheKey = 'boundary_lookup:' . md5("{$query}|{$type}|{$state}");

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $type, $state) {
            return $this->fetchBoundaryFromNominatim($query, $type, $state);
        });
    }

    protected function fetchBoundaryFromNominatim(string $query, string $type, ?string $state): ?array
    {
        $searchQuery = $query;
        if ($state) {
            $searchQuery .= ", {$state}";
        }
        $searchQuery .= ', United States';

        $params = [
            'q'               => $searchQuery,
            'format'          => 'json',
            'polygon_geojson' => 1,
            'limit'           => 5,
            'countrycodes'    => 'us',
        ];

        if ($type === 'zip') {
            $params = [
                'postalcode'      => $query,
                'format'          => 'json',
                'polygon_geojson' => 1,
                'limit'           => 5,
                'countrycodes'    => 'us',
            ];
        }

        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: 1WayRide-Admin/1.0 (admin@1wayride.com)',
                'Accept: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $results = json_decode($response, true);
        if (!is_array($results) || empty($results)) {
            return null;
        }

        foreach ($results as $result) {
            $geojson = $result['geojson'] ?? null;
            if (!$geojson || !is_array($geojson)) {
                continue;
            }

            try {
                $multiPolygon = $this->normalizeGeoJsonToMultiPolygon($geojson);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            if (empty($multiPolygon['coordinates'])) {
                continue;
            }

            return [
                'name'          => $result['display_name'] ?? $query,
                'short_name'    => $query . ($state ? ", {$state}" : ''),
                'type'          => $type,
                'osm_type'      => $result['osm_type'] ?? null,
                'osm_id'        => $result['osm_id'] ?? null,
                'lat'           => (float) ($result['lat'] ?? 0),
                'lng'           => (float) ($result['lon'] ?? 0),
                'geojson'       => $multiPolygon,
            ];
        }

        return null;
    }
}
