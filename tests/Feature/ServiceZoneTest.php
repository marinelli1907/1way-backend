<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ZoneManagement\Entities\ServiceZone;
use Modules\ZoneManagement\Entities\ServiceZoneComponent;
use Modules\ZoneManagement\Entities\ServiceZoneExclusion;
use Modules\ZoneManagement\Entities\ServiceZoneInclusion;
use Modules\ZoneManagement\Service\GeoZoneService;
use Tests\TestCase;

class ServiceZoneTest extends TestCase
{
    private GeoZoneService $geo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geo = app(GeoZoneService::class);
    }

    // ─── Test 1: Boundary lookup returns GeoJSON ─────────────────────────

    public function test_boundary_lookup_returns_geojson_for_known_place(): void
    {
        $result = $this->geo->lookupBoundary('Richmond Heights', 'city', 'OH');

        if ($result === null) {
            $this->markTestSkipped('Nominatim returned no result (rate limit or network issue).');
        }

        $this->assertNotNull($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('geojson', $result);
        $this->assertEquals('MultiPolygon', $result['geojson']['type']);
        $this->assertNotEmpty($result['geojson']['coordinates']);
    }

    // ─── Test 2: 3-layer containment logic ───────────────────────────────

    public function test_zone_contains_with_exclusions_and_inclusions(): void
    {
        // Create a large square zone covering a test area
        // Boundary: large box around (41.0 to 42.0 lat, -82.0 to -81.0 lng)
        $boundary = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[
                    [-82.0, 41.0],
                    [-81.0, 41.0],
                    [-81.0, 42.0],
                    [-82.0, 42.0],
                    [-82.0, 41.0],
                ]],
            ],
        ];

        // Exclusion: small box inside (41.4 to 41.6, -81.6 to -81.4)
        $exclusion = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[
                    [-81.6, 41.4],
                    [-81.4, 41.4],
                    [-81.4, 41.6],
                    [-81.6, 41.6],
                    [-81.6, 41.4],
                ]],
            ],
        ];

        // Inclusion override: tiny box inside the exclusion (41.48 to 41.52, -81.52 to -81.48)
        $inclusion = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[
                    [-81.52, 41.48],
                    [-81.48, 41.48],
                    [-81.48, 41.52],
                    [-81.52, 41.52],
                    [-81.52, 41.48],
                ]],
            ],
        ];

        $zone = ServiceZone::create([
            'name'                => 'Test Zone 3-Layer',
            'country_code'        => 'US',
            'state_code'          => 'OH',
            'source'              => 'manual',
            'boundary'            => $boundary,
            'exclusions'          => $exclusion,
            'inclusions_override' => $inclusion,
            'is_active'           => true,
            'priority'            => 10,
        ]);

        // Point inside boundary, outside exclusion -> true
        $this->assertTrue(
            $this->geo->contains(41.2, -81.8, $zone),
            'Point inside boundary and outside exclusion should be inside'
        );

        // Point outside boundary entirely -> false
        $this->assertFalse(
            $this->geo->contains(40.0, -80.0, $zone),
            'Point outside boundary should be outside'
        );

        // Point inside exclusion, outside inclusion override -> false
        $this->assertFalse(
            $this->geo->contains(41.45, -81.55, $zone),
            'Point inside exclusion but outside override should be outside'
        );

        // Point inside inclusion override (inside exclusion) -> true
        $this->assertTrue(
            $this->geo->contains(41.50, -81.50, $zone),
            'Point inside inclusion override should be inside (overrides exclusion)'
        );

        // findZoneForPoint should return this zone for a point inside
        $found = $this->geo->findZoneForPoint(41.2, -81.8);
        $this->assertNotNull($found);
        $this->assertEquals($zone->id, $found->id);

        // findZoneForPoint should return null for a point outside
        $notFound = $this->geo->findZoneForPoint(40.0, -80.0);
        $this->assertNull($notFound);

        // Cleanup
        $zone->delete();
    }

    // ─── Test 3: API endpoint ────────────────────────────────────────────

    public function test_api_zones_contains_endpoint(): void
    {
        $boundary = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[
                    [-82.0, 41.0],
                    [-81.0, 41.0],
                    [-81.0, 42.0],
                    [-82.0, 42.0],
                    [-82.0, 41.0],
                ]],
            ],
        ];

        $zone = ServiceZone::create([
            'name'         => 'API Test Zone',
            'country_code' => 'US',
            'source'       => 'manual',
            'boundary'     => $boundary,
            'is_active'    => true,
            'priority'     => 10,
        ]);

        $response = $this->getJson('/api/zones/contains?lat=41.5&lng=-81.5');
        $response->assertOk();
        $response->assertJson([
            'inside'    => true,
            'zone_id'   => $zone->id,
            'zone_name' => 'API Test Zone',
        ]);

        $response2 = $this->getJson('/api/zones/contains?lat=30.0&lng=-70.0');
        $response2->assertOk();
        $response2->assertJson(['inside' => false]);

        $zone->delete();
    }

    // ─── Test 4: Normalize GeoJSON ───────────────────────────────────────

    public function test_normalize_polygon_to_multi_polygon(): void
    {
        $polygon = [
            'type' => 'Polygon',
            'coordinates' => [[[-80, 25], [-79, 25], [-79, 26], [-80, 26], [-80, 25]]],
        ];

        $result = $this->geo->normalizeGeoJsonToMultiPolygon($polygon);
        $this->assertEquals('MultiPolygon', $result['type']);
        $this->assertCount(1, $result['coordinates']);
    }

    public function test_normalize_feature_collection(): void
    {
        $fc = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [[[-80, 25], [-79, 25], [-79, 26], [-80, 26], [-80, 25]]],
                    ],
                ],
            ],
        ];

        $result = $this->geo->normalizeGeoJsonToMultiPolygon($fc);
        $this->assertEquals('MultiPolygon', $result['type']);
    }

    // ─── Test 6: GeometryCollection normalization ────────────────────────

    public function test_normalize_geometry_collection(): void
    {
        $gc = [
            'type' => 'GeometryCollection',
            'geometries' => [
                [
                    'type' => 'Polygon',
                    'coordinates' => [[[-80, 25], [-79, 25], [-79, 26], [-80, 26], [-80, 25]]],
                ],
                [
                    'type' => 'LineString',
                    'coordinates' => [[-80, 25], [-79, 26]],
                ],
                [
                    'type' => 'MultiPolygon',
                    'coordinates' => [
                        [[[-78, 25], [-77, 25], [-77, 26], [-78, 26], [-78, 25]]],
                    ],
                ],
            ],
        ];

        $result = $this->geo->normalizeGeoJsonToMultiPolygon($gc);
        $this->assertEquals('MultiPolygon', $result['type']);
        $this->assertCount(2, $result['coordinates'], 'Should extract 2 polygons, skipping LineString');
    }

    public function test_normalize_linestring_only_throws(): void
    {
        $lineString = [
            'type' => 'LineString',
            'coordinates' => [[-80, 25], [-79, 26]],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Boundary must contain at least one Polygon');
        $this->geo->normalizeGeoJsonToMultiPolygon($lineString);
    }

    // ─── Test 7: Size guard ──────────────────────────────────────────────

    public function test_size_guard_throws_on_oversized_geojson(): void
    {
        $ring = [];
        for ($i = 0; $i < 400000; $i++) {
            $ring[] = [-80.0 + ($i * 0.000001), 25.0 + ($i * 0.000001)];
        }
        $ring[] = $ring[0];

        $huge = ['type' => 'MultiPolygon', 'coordinates' => [[$ring]]];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('too large');
        $this->geo->assertSizeLimit($huge);
    }
}
